<?php

namespace App\Presenters;

use App\Service\MediaService;
use MediaCenter\FileSystem;
use MediaCenter\Image\ThumbImage;
use MediaCenter\ImageCacheGenerator;
use MediaCenter\ImageZipCreator;
use Nette;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\ArrayList;
use Nette\Utils\Image as NetteImage;


class HomepagePresenter extends Nette\Application\UI\Presenter {

	/** @var string @persistent */
	public $dir;

	/** @var MediaService @inject */
	public $mediaService;

	/** @var FileSystem @inject */
	public $fileSystem;

	/** @var ImageZipCreator @inject */
	public $imageZipCreator;

	/** @var ImageCacheGenerator @inject */
	public $imageCacheGenerator;

	/** @var ArrayList|ThumbImage[] */
	protected $thumbImages;

	public function actionGenerateThumbs() {
		$imageFiles = $this->mediaService->findImageFiles();

		$this->imageCacheGenerator->generate($imageFiles);
		$this->terminate();
	}

	/**
	 * @param int $page
	 */
	public function actionDefault($page = 1) {
		$this->dir = empty($this->dir) ? NULL : $this->dir;
		$files = $this->mediaService->findImageFiles($this->dir);

		$paginator = new Nette\Utils\Paginator();
		$paginator->setItemCount($files->count());
		$paginator->setItemsPerPage(60);
		$paginator->setPage($page);

		$tempResult = $this->paginateFiles(
			$files->getIterator()->getArrayCopy(),
			$paginator->getLength(),
			$paginator->getOffset()
		);

		$fileInfoList = new ArrayList();
		/** @var \SplFileInfo $fileInfo */
		foreach ($tempResult as $fileInfo) {
			$fileInfoList[] = $fileInfo;
		}

		$this->thumbImages = $this->mediaService->findThumbImagesFromStorage($fileInfoList);

		$this->getTemplate()->paginator = $paginator;
	}

	public function renderDefault() {
		$this->getTemplate()->thumbImages = $this->thumbImages;
		$this->getTemplate()->subdirs = $this->fileSystem->findSubdir($this->dir);
		$this->getTemplate()->actualDir = $this->dir;
		$this->getTemplate()->actualDirArray = explode(DIRECTORY_SEPARATOR, $this->dir);
	}

	/**
	 * @param string $path
	 * @param int    $quality
	 * @param bool   $show
	 * @throws BadRequestException
	 * @throws Nette\Application\AbortException
	 * @throws Nette\Utils\ImageException
	 */
	public function renderShow($path, $quality = 100, $show = FALSE) {
		$fileInfo = new \SplFileInfo($path);
		if (!$fileInfo) {
			throw new BadRequestException();
		}

		$image = $this->mediaService->getImage($fileInfo);

		if ($show) {
			Nette\Utils\Image::fromString($image->getContent())->send(Nette\Utils\Image::JPEG, $quality);
		}

		$tempImagePath = WWW_DIR . '/temp-image.jpg';
		NetteImage::fromString($image->getContent())->save($tempImagePath, $quality, NetteImage::JPEG);

		$this->sendResponse(new FileResponse($tempImagePath, basename($image->getOriginalFilePath())));
	}

	/**
	 * @throws Nette\Application\AbortException
	 */
	public function handleDirBack() {
		$explodes = explode(DIRECTORY_SEPARATOR, $this->dir);
		unset($explodes[count($explodes) - 1]);

		$dir = implode(DIRECTORY_SEPARATOR, $explodes);

		$this->redirect('this', ['dir' => $dir]);
	}

	/**
	 * @param array $files
	 * @param int   $limit
	 * @param int   $offset
	 * @return array
	 */
	protected function paginateFiles(array $files, $limit, $offset) {
		return array_slice($files, $offset, $limit);
	}

	/**
	 * @return Form
	 */
	protected function createComponentDownloadForm() {
		$items = [];
		foreach ($this->thumbImages as $thumbImage) {
			$items[$thumbImage->getIdentificator()] = $thumbImage->getOriginalFilePath();
		}

		$form = new Form();
		$form->addCheckboxList('files', 'Soubory', $items);
		$form->addSubmit('submit', 'Stáhnout soubory');
		$form->onSuccess[] = function (Form $form, ArrayHash $values) {
			$fileInfoList = new ArrayList();
			foreach ($values->files as $fileIdentifier) {
				$fileInfoList[] = new \SplFileInfo(ThumbImage::decodeIdentifier($fileIdentifier));
			}

			if (!$fileInfoList->count()) {
				$this->flashMessage('Vyberte nějaké soubory', 'danger');

				$this->redirect('this');
			}

			$filename = $this->imageZipCreator->createZip($fileInfoList);

			if (!$filename) {
				$this->flashMessage('Vygenerování ZIP souboru se nezdařilo', 'danger');

				$this->redirect('this');
			}

			$form->reset();

			$this->sendResponse(new FileResponse($filename));
		};

		return $form;
	}
}
