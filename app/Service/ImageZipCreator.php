<?php

namespace MediaCenter;

use App\Service\MediaService;
use Nette\Utils\ArrayList;
use Nette\Utils\DateTime;
use ZipArchive;

class ImageZipCreator {

	/** @var string */
	private $zipDir;

	/** @var MediaService */
	private $mediaService;

	/**
	 * ImageZipCreator constructor.
	 * @param string       $zipDir
	 * @param MediaService $mediaService
	 */
	public function __construct($zipDir, MediaService $mediaService) {
		$this->zipDir = $zipDir;
		$this->mediaService = $mediaService;
	}

	/**
	 * @param ArrayList|\SplFileInfo[] $fileInfoList
	 * @return null|string
	 * @throws \Nette\Utils\ImageException
	 */
	public function createZip(ArrayList $fileInfoList) {
		if (!$fileInfoList->count()) {
			return NULL;
		}
		$zip = new \ZipArchive();

		$now = new DateTime();
		$zipFilename = "{$this->zipDir}" . DIRECTORY_SEPARATOR . "images_{$now->format('YmdHis')}.zip";

		if ($zip->open($zipFilename, ZipArchive::CREATE) === TRUE) {
			foreach ($this->mediaService->findImages($fileInfoList) as $image) {
				$zip->addFromString(basename($image->getOriginalFilePath()), $image->getContent());
			}
			$zip->close();
		} else {
			return NULL;
		}

		return $zipFilename;
	}


}