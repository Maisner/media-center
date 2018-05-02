<?php

namespace MediaCenter\Image;

use Nette\Utils\DateTime;

class ThumbImage {

	/** @var string */
	private $identificator;

	/** @var string */
	private $originalFilePath;

	/** @var string */
	private $dataStream;

	/** @var DateTime */
	private $date;

	/**
	 * Image constructor.
	 * @param string   $originalFilePath
	 * @param string   $dataStream
	 * @param DateTime $date
	 */
	public function __construct($originalFilePath, $dataStream, DateTime $date) {
		$this->identificator = self::encodeIdentifier($originalFilePath);
		$this->originalFilePath = $originalFilePath;
		$this->dataStream = $dataStream;
		$this->date = $date;
	}

	/**
	 * @return string
	 */
	public function getIdentificator() {
		return $this->identificator;
	}

	/**
	 * @return string
	 */
	public function getOriginalFilePath() {
		return $this->originalFilePath;
	}

	/**
	 * @return string
	 */
	public function getDataStream() {
		return $this->dataStream;
	}

	/**
	 * @return DateTime
	 */
	public function getDate() {
		return $this->date;
	}

	/**
	 * @param string $filePath
	 * @return string
	 */
	public static function encodeIdentifier($filePath) {
		return base64_encode($filePath);
	}

	/**
	 * @param string $base64String
	 * @return null|string
	 */
	public static function decodeIdentifier($base64String) {
		return base64_decode($base64String) ? : NULL;
	}

}