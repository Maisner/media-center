<?php

namespace MediaCenter\Storage\ThumbImageStorage;

use MediaCenter\Image\ThumbImage;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Utils\ArrayList;

class ThumbImageCacheStorage implements IThumbImageStorage {

	const CACHE_NAMESPACE = 'image-thumb';

	/** @var Cache */
	protected $cache;

	/**
	 * ThumbImageCacheStorage constructor.
	 * @param IStorage $cacheStorage
	 */
	public function __construct(IStorage $cacheStorage) {
		$this->cache = new Cache($cacheStorage, self::CACHE_NAMESPACE);
	}

	/**
	 * @param \SplFileInfo $fileInfo
	 * @return ThumbImage|null
	 */
	public function getThumbImage(\SplFileInfo $fileInfo) {
		return $this->cache->load($fileInfo->getRealPath()) ? : NULL;
	}

	/**
	 * @param ArrayList $fileInfoList
	 * @return ArrayList|ThumbImage[]
	 */
	public function findThumbImages(ArrayList $fileInfoList) {
		$result = new ArrayList();

		foreach ($fileInfoList as $fileInfo) {
			$thumbImage = $this->getThumbImage($fileInfo);

			if ($thumbImage !== NULL) {
				$result[] = $thumbImage;
			}
		}

		return $result;
	}

	/**
	 * @param ThumbImage $thumbImage
	 * @return ThumbImage|null
	 * @throws \Throwable
	 */
	public function storeThumbImage(ThumbImage $thumbImage) {
		return $this->cache->save($thumbImage->getOriginalFilePath(), $thumbImage);
	}
}