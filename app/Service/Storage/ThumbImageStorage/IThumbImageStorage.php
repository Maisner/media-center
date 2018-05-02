<?php

namespace MediaCenter\Storage\ThumbImageStorage;

use MediaCenter\Image\ThumbImage;
use Nette\Utils\ArrayList;

interface IThumbImageStorage {

	/**
	 * @param \SplFileInfo $fileInfo
	 * @return ThumbImage|null
	 */
	public function getThumbImage(\SplFileInfo $fileInfo);

	/**
	 * @param ArrayList $fileInfoList
	 * @return ArrayList|ThumbImage[]
	 */
	public function findThumbImages(ArrayList $fileInfoList);

	/**
	 * @param ThumbImage $thumbImage
	 * @return ThumbImage|null
	 */
	public function storeThumbImage(ThumbImage $thumbImage);
}