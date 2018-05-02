<?php

namespace App\Service;

use MediaCenter\FileSystem;
use MediaCenter\Image\ImageInfoData;
use MediaCenter\Image\ThumbImage;
use MediaCenter\Storage\ImageInfoStorage\IImageInfoStorage;
use MediaCenter\Storage\ImageStorage\IImageStorage;
use MediaCenter\Storage\ThumbImageStorage\IThumbImageStorage;
use Nette\Utils\ArrayList;
use Nette\Utils\Image;
use Nette\Utils\Random;

class MediaService {

	const THUMB_IMAGE_SQUARE_SIZE = 150;

	const IMAGE_WIDTH = 1920;

	const THUMB_IMAGE_JPEG_QUALITY = 100;

	const IMAGE_JPEG_QUALITY = 70;

	const IMAGE_FILES = [
		'*.jpeg',
		'*.jpg',
		'*.png'
	];

	/** @var FileSystem */
	private $fileSystem;

	/** @var IImageInfoStorage */
	private $imageInfoStorage;

	/** @var IThumbImageStorage */
	private $thumbImageStorage;

	/** @var IImageStorage */
	private $imageStorage;

	/**
	 * MediaService constructor.
	 * @param FileSystem         $fileSystem
	 * @param IImageInfoStorage  $imageInfoStorage
	 * @param IThumbImageStorage $thumbImageStorage
	 * @param IImageStorage      $imageStorage
	 */
	public function __construct(
		FileSystem $fileSystem,
		IImageInfoStorage $imageInfoStorage,
		IThumbImageStorage $thumbImageStorage,
		IImageStorage $imageStorage
	) {
		$this->fileSystem = $fileSystem;
		$this->imageInfoStorage = $imageInfoStorage;
		$this->thumbImageStorage = $thumbImageStorage;
		$this->imageStorage = $imageStorage;
	}

	/**
	 * @param string|null $dir
	 * @param bool        $withSubdirs
	 * @param bool        $sortDateDesc
	 * @return ArrayList|\SplFileInfo[]
	 */
	public function findImageFiles($dir = NULL, $withSubdirs = TRUE, $sortDateDesc = TRUE) {
		$files = $this->fileSystem->findFiles($dir, self::IMAGE_FILES, $withSubdirs);

		$temp = [];

		foreach ($files as $fileInfo) {
			$imageData = $this->imageInfoStorage->getImageInfo($fileInfo);

			$temp[$imageData->getCreatedDate()->getTimestamp() . Random::generate(6, '0-9')] = $fileInfo;
		}

		if ($sortDateDesc) {
			krsort($temp, SORT_NUMERIC); //new to old
		} else {
			ksort($temp, SORT_NUMERIC); //old to new
		}

		$result = new ArrayList();

		foreach ($temp as $fileInfo) {
			$result[] = $fileInfo;
		}

		return $result;
	}

	/**
	 * @param ArrayList|\SplFileInfo[] $fileInfoList
	 * @return ArrayList|ThumbImage[]
	 */
	public function findThumbImagesFromStorage(ArrayList $fileInfoList) {
		return $this->thumbImageStorage->findThumbImages($fileInfoList);
	}

	/**
	 * @param \SplFileInfo $fileInfo
	 * @return \MediaCenter\Image\Image|mixed|null
	 * @throws \Nette\Utils\UnknownImageFileException
	 */
	public function getImage(\SplFileInfo $fileInfo) {
		$image = $this->imageStorage->getImage($fileInfo);

		if ($image) {
			return $image;
		}

		$image = $this->imageFactory($fileInfo);

		return $this->imageStorage->storeImage($image);
	}

	/**
	 * @param ArrayList|\SplFileInfo[] $fileInfoList
	 * @return ArrayList|\MediaCenter\Image\Image[]
	 */
	public function findImages(ArrayList $fileInfoList) {
		return $this->imageStorage->findImages($fileInfoList);
	}

	/**
	 * @param \SplFileInfo $fileInfo
	 * @return ThumbImage
	 * @throws \Nette\Utils\UnknownImageFileException
	 */
	public function thumbImageFactory(\SplFileInfo $fileInfo) {
		/** @var Image $thumb */
		$thumb = Image::fromFile($fileInfo->getRealPath());

		$thumb->resize(self::THUMB_IMAGE_SQUARE_SIZE, self::THUMB_IMAGE_SQUARE_SIZE, Image::EXACT);
		$imageData = new ImageInfoData($fileInfo);

		return new ThumbImage(
			$fileInfo->getRealPath(),
			$this->getImageDataStream($thumb),
			$imageData->getCreatedDate()
		);
	}

	/**
	 * @param \SplFileInfo $fileInfo
	 * @return \MediaCenter\Image\Image
	 * @throws \Nette\Utils\UnknownImageFileException
	 */
	public function imageFactory(\SplFileInfo $fileInfo) {
		$image = Image::fromFile($fileInfo->getRealPath());
		$imageData = new ImageInfoData($fileInfo);
		$image->resize(self::IMAGE_WIDTH, NULL, Image::FILL | Image::SHRINK_ONLY);

		return new \MediaCenter\Image\Image(
			$fileInfo->getRealPath(),
			$image->toString(Image::JPEG, self::IMAGE_JPEG_QUALITY),
			$imageData->getCreatedDate()
		);
	}

	/**
	 * Datestream to use in img html tag
	 *
	 * @param Image $image
	 * @return string
	 */
	protected function getImageDataStream(Image $image) {
		$type = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $image);

		return 'data:' . ($type ? "$type;" : '') . 'base64,' . base64_encode(
				$image->toString(Image::JPEG, self::THUMB_IMAGE_JPEG_QUALITY)
			);
	}
}