<?php

namespace MediaCenter;

use App\Service\MediaService;
use MediaCenter\Storage\ImageStorage\ImageCacheStorage;
use MediaCenter\Storage\ThumbImageStorage\ThumbImageCacheStorage;
use Nette\Utils\ArrayList;

class ImageCacheGenerator {

	const DEFAULT_STEP = 10;

	/** @var MediaService */
	private $mediaService;

	/** @var ThumbImageCacheStorage */
	private $thumbImageCacheStorage;

	/** @var ImageCacheStorage */
	private $imageCacheStorage;

	/**
	 * CacheGenerator constructor.
	 * @param MediaService           $mediaService
	 * @param ThumbImageCacheStorage $thumbImageCacheStorage
	 * @param ImageCacheStorage      $imageCacheStorage
	 */
	public function __construct(
		MediaService $mediaService,
		ThumbImageCacheStorage $thumbImageCacheStorage,
		ImageCacheStorage $imageCacheStorage
	) {
		$this->mediaService = $mediaService;
		$this->thumbImageCacheStorage = $thumbImageCacheStorage;
		$this->imageCacheStorage = $imageCacheStorage;
	}

	/**
	 * @param ArrayList|\SplFileInfo[] $imageFileList
	 * @param int                      $step
	 * @return bool
	 * @throws \Nette\Utils\UnknownImageFileException
	 * @throws \Throwable
	 */
	public function generate(ArrayList $imageFileList, $step = self::DEFAULT_STEP) {
		$counter = 0;
		foreach ($imageFileList as $fileInfo) {
			$thumbImage = $this->thumbImageCacheStorage->getThumbImage($fileInfo);

			if ($thumbImage !== NULL) {
				continue;
			}

			$this->generateThumbImageCache($fileInfo);
			$this->generateImageCache($fileInfo);

			$counter++;

			if ($counter >= $step) {
				break;
			}
		}

		return TRUE;
	}

	/**
	 * @param \SplFileInfo $fileInfo
	 * @return bool
	 * @throws \Nette\Utils\UnknownImageFileException
	 * @throws \Throwable
	 */
	protected function generateThumbImageCache(\SplFileInfo $fileInfo) {
		$thumbImage = $this->mediaService->thumbImageFactory($fileInfo);
		$this->thumbImageCacheStorage->storeThumbImage($thumbImage);

		return TRUE;
	}

	/**
	 * @param \SplFileInfo $fileInfo
	 * @return bool
	 * @throws \Nette\Utils\UnknownImageFileException
	 * @throws \Throwable
	 */
	protected function generateImageCache(\SplFileInfo $fileInfo) {
		$image = $this->mediaService->imageFactory($fileInfo);
		$this->imageCacheStorage->storeImage($image);

		return TRUE;
	}
}