<?php

namespace MediaCenter\Storage\ImageStorage;

use MediaCenter\Image\Image;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Utils\ArrayList;

class ImageCacheStorage implements IImageStorage {

	const CACHE_NAMESPACE = 'image';

	/** @var Cache */
	protected $cache;

	/**
	 * ImageCacheStorage constructor.
	 * @param IStorage $cacheStorage
	 */
	public function __construct(IStorage $cacheStorage) {
		$this->cache = new Cache($cacheStorage, self::CACHE_NAMESPACE);
	}

	/**
	 * @param \SplFileInfo $fileInfo
	 * @return Image|null
	 */
	public function getImage(\SplFileInfo $fileInfo) {
		return $this->cache->load($fileInfo->getRealPath()) ? : NULL;
	}

	/**
	 * @param ArrayList $fileInfoList
	 * @return ArrayList|Image[]
	 */
	public function findImages(ArrayList $fileInfoList) {
		$result = new ArrayList();

		foreach ($fileInfoList as $fileInfo) {
			$image = $this->getImage($fileInfo);

			if ($image !== NULL) {
				$result[] = $image;
			}
		}

		return $result;
	}

	/**
	 * @param Image $image
	 * @return Image|null
	 * @throws \Throwable
	 */
	public function storeImage(Image $image) {
		return $this->cache->save($image->getOriginalFilePath(), $image);
	}
}