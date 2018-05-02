<?php

namespace MediaCenter;

use Nette\InvalidArgumentException;
use Nette\Utils\ArrayList;
use Nette\Utils\Finder;

class FileSystem {

	/** @var string */
	private $mediaRootDir;

	/**
	 * FileSystem constructor.
	 * @param $mediaRootDir
	 */
	public function __construct($mediaRootDir) {
		$this->mediaRootDir = $mediaRootDir;
	}

	/**
	 * @return string
	 */
	public function getMediaRootDir() {
		return $this->mediaRootDir;
	}

	/**
	 * @param string|null $dir
	 * @param array       $fileExtensions
	 * @param bool        $withSubdirs
	 * @return ArrayList|\SplFileInfo[]
	 * @throws \Nette\InvalidArgumentException
	 */
	public function findFiles($dir = NULL, array $fileExtensions, $withSubdirs = TRUE) {
		$folder = $this->getAbsoluteMediaDir($dir);

		$finder = Finder::findFiles($fileExtensions);
		if ($withSubdirs) {
			$finder->from($folder);
		} else {
			$finder->in($folder);
		}

		$files = new ArrayList();

		/** @var \SplFileInfo $fileInfo */
		foreach ($finder->getIterator() as $fileInfo) {
			$files[] = $fileInfo;
		}

		return $files;
	}

	/**
	 * @param string|null $dir
	 * @return array|array[]
	 * @throws \Nette\InvalidArgumentException
	 */
	public function findSubdir($dir) {
		$dir = $this->getAbsoluteMediaDir($dir);
		$subdirs = [];

		if ($dh = opendir($dir)) {
			while (($item = readdir($dh)) !== FALSE) {
				if ($item === '.' || $item === '..' || !is_dir($dir . DIRECTORY_SEPARATOR . $item)) {
					continue;
				}

				$subdirs[] = $item;
			}
			closedir($dh);
		}

		$result = [];
		foreach ($subdirs as $key => $subdir) {
			$path = str_replace($this->mediaRootDir . DIRECTORY_SEPARATOR, '', $dir . DIRECTORY_SEPARATOR . $subdir);

			$res = [];
			foreach (explode(DIRECTORY_SEPARATOR, $path) as $part) {
				$part = trim($part);
				if ($part === '') {
					continue;
				}

				$res[] = $part;
			}

			$result[$subdir] = $res;
		}

		return $result;
	}

	/**
	 * @param string $dir
	 * @return string
	 * @throws \Nette\InvalidArgumentException
	 */
	protected function getAbsoluteMediaDir($dir) {
		$folder = $this->mediaRootDir . DIRECTORY_SEPARATOR . $dir;

		if (!is_dir($folder)) {
			throw new InvalidArgumentException("Dir {$folder} not exists");
		}

		return $folder;
	}
}