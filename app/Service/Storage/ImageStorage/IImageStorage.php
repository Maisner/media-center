<?php

namespace MediaCenter\Storage\ImageStorage;

use MediaCenter\Image\Image;
use Nette\Utils\ArrayList;

interface IImageStorage {

	/**
	 * @param \SplFileInfo $fileInfo
	 * @return Image|null
	 */
	public function getImage(\SplFileInfo $fileInfo);

	/**
	 * @param ArrayList $fileInfoList
	 * @return ArrayList|Image[]
	 */
	public function findImages(ArrayList $fileInfoList);

	/**
	 * @param Image $image
	 * @return Image|null
	 */
	public function storeImage(Image $image);
}