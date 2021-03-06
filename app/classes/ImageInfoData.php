<?php

namespace MediaCenter\Image;

use Nette\Utils\DateTime;

class ImageInfoData {

	const EXIF_DATA_DISALLOWED_EXTENSION = [
		'png'
	];

	/** @var string */
	private $filePath;

	/** @var array */
	private $exifData;

	/** @var string */
	private $extension;

	/** @var DateTime */
	private $createdDate;

	/** @var int|null */
	private $orientation;

	/**
	 * ImageInfoData constructor.
	 * @param \SplFileInfo $fileInfo
	 */
	public function __construct(\SplFileInfo $fileInfo) {
		$this->filePath = $fileInfo->getRealPath();
		$this->extension = $fileInfo->getExtension();

		//exif data
		if (!in_array($this->extension, self::EXIF_DATA_DISALLOWED_EXTENSION, FALSE)) {
			$this->exifData = @exif_read_data($fileInfo->getRealPath());

			$this->createdDate = isset($this->exifData['DateTimeOriginal'])
				? new DateTime($this->exifData['DateTimeOriginal'])
				: (new DateTime())->setTimestamp(fileatime($fileInfo->getRealPath()));

			if (isset($this->exifData['Orientation']) && \is_numeric($this->exifData['Orientation'])) {
				$this->orientation = (int)$this->exifData['Orientation'];
			}
		}

		if ($this->createdDate === NULL) {
			$this->createdDate = new DateTime();
			$this->createdDate->setTimestamp(fileatime($fileInfo->getRealPath()));
		}
	}

	/**
	 * @return string
	 */
	public function getFilePath() {
		return $this->filePath;
	}

	/**
	 * @return string
	 */
	public function getExtension() {
		return $this->extension;
	}

	/**
	 * @return DateTime
	 */
	public function getCreatedDate() {
		return $this->createdDate;
	}

	/**
	 * @return int|null
	 */
	public function getOrientation() {
		return $this->orientation;
	}

	/**
	 * @return bool
	 */
	public function hasOrientation() {
		return $this->orientation !== NULL;
	}
}