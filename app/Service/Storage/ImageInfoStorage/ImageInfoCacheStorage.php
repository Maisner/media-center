<?php

namespace MediaCenter\Storage\ImageInfoStorage;

use MediaCenter\Image\ImageInfoData;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;

class ImageInfoCacheStorage implements IImageInfoStorage {

	const CACHE_NAMESPACE = 'image-info';

	/** @var Cache */
	protected $cache;

	/**
	 * ImageInfoCacheStorage constructor.
	 * @param IStorage $cacheStorage
	 */
	public function __construct(IStorage $cacheStorage) {
		$this->cache = new Cache($cacheStorage, self::CACHE_NAMESPACE);
	}

	/**
	 * @param \SplFileInfo $fileInfo
	 * @return ImageInfoData
	 * @throws \Throwable
	 */
	public function getImageInfo(\SplFileInfo $fileInfo) {
		return $this->cache->load(
			$fileInfo->getRealPath(),
			function () use ($fileInfo) {
				return $this->storeImageInfo(new ImageInfoData($fileInfo));
			}
		);
	}

	/**
	 * @param ImageInfoData $imageInfoData
	 * @return ImageInfoData|null
	 * @throws \Throwable
	 */
	public function storeImageInfo(ImageInfoData $imageInfoData) {
		return $this->cache->save($imageInfoData->getFilePath(), $imageInfoData);
	}
}