<?php

namespace MediaCenter\Image;

use Nette\Utils\DateTime;

class Image {

	/** @var string */
	private $originalFilePath;

	/** @var string */
	private $content;

	/** @var DateTime */
	private $date;

	/**
	 * Image constructor.
	 * @param string   $originalFilePath
	 * @param string   $content
	 * @param DateTime $date
	 */
	public function __construct($originalFilePath, $content, DateTime $date) {
		$this->originalFilePath = $originalFilePath;
		$this->content = $content;
		$this->date = $date;
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
	public function getContent() {
		return $this->content;
	}

	/**
	 * @return DateTime
	 */
	public function getDate() {
		return $this->date;
	}

}