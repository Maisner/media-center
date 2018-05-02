<?php

namespace MediaCenter\Storage\ImageInfoStorage;

use MediaCenter\Image\ImageInfoData;

interface IImageInfoStorage {

	/**
	 * @param \SplFileInfo $fileInfo
	 * @return ImageInfoData
	 */
	public function getImageInfo(\SplFileInfo $fileInfo);

	/**
	 * @param ImageInfoData $imageInfoData
	 * @return ImageInfoData|null
	 */
	public function storeImageInfo(ImageInfoData $imageInfoData);
}