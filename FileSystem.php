<?php

class FileSystem
{
	protected $path;
	protected $result = null;

	function __construct($path) {
		$this->path = $path;
	}

	public function MySelf()
	{
		return $_SERVER['PHP_SELF'];
	}

	public function homeRoot()
	{
		return $_SERVER['DOCUMENT_ROOT'];
	}

	public function getPath()
	{
		return $this->path;
	}

	public function isDir($dir)
	{
		return  is_dir($dir);
	}

	public function isFile($file)
	{
		return is_file($file);
	}

	public function scDir()
	{
		return scandir($this->path);
	}

	public function cd($directory)
	{
		return chdir($directory);
	}

	public function getExtension($filename)
	{
		return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	}

	public function FileRecursive($dir, &$results = array())
	{
		$files = scandir($dir);
	    foreach($files as $key => $value){
	        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
	        if($this->isDir($path) == false) {
	            $results[] = $path;
	        }
	        else if($value != "." && $value != "..") {
	            $this->FileRecursive($path, $results);
	            if($this->isFile($path) == false) {
	                $results[] = $path;
	            }   
	        }
	    }
	    return $results;
	}

	public function reWrite($dir, $extension, $data = null)
	{
		if ($this->isWritable($dir)) {
			foreach ($this->FileRecursive($dir) as $key => $value) {
				switch ($this->getExtension($value)) {
					case $extension:
						if (preg_match('/' . basename($value) . "$/i", $this->MySelf(), $matches) == 0) {
							return $value;
						}
						break;
				}
			}
		}
	}

	public function Dir()
	{
		$this->result = [];
		foreach ($this->scDir() as $key => $value) {
			$filename = [
				"pathName" 		=> $this->path . DIRECTORY_SEPARATOR . $value,
				"singlePath"	=> $value
			];
			if (!$this->isDir($filename['pathName']) || $value === '.' || $value === '..') continue;
			$this->result[] = $filename;
		} return $this->result;
	}

	public function File()
	{
		$this->result = [];
		foreach ($this->scDir() as $key => $value) {
			$filename = [
				"pathName" 		=> $this->path . DIRECTORY_SEPARATOR . $value,
				"singlePath"	=> $value
			];
			if (!$this->isFile($filename['pathName']) || $value === '.' || $value === '..') continue;
			$this->result[] = $filename;
		} return $this->result;
	}

}

/**
 * 
 */
class Action extends FileSystem
{
	protected $resource;
	protected $mode;
	protected $modes = [
		'readOnly'        => 'r',
        'readWrite'       => 'r+',
        'writeOnly'       => 'w',
        'writeMaster'     => 'w+',
        'writeAppend'     => 'a',
        'readWriteAppend' => 'a+',
	];
	
	function __construct(protected $filename,) 
	{
		parent::__construct(getcwd());
	}

	public function download()
	{
		if ($this->isFile($this->filename)) {
			header("Content-Type: application/octet-stream");
			header('Content-Transfer-Encoding: binary');
			header("Content-length: ".filesize($this->filename));
			header("Cache-Control: no-cache");
			header("Pragma: no-cache");
			header("Content-disposition: attachment; filename=\"".basename($this->filename)."\";");

			$handle = fopen($this->filename, "rb");
			while (!feof($handle)) {
				print(fread($handle, 1024*8));
				@ob_flush();
				@flush();
			}
			fclose($handle);
		}
	}

	public function delete()
	{
		if ($this->isDir($this->filename)) {
			foreach ($this->scDir() as $key => $value) {
				if ($value != "." && $value != '..') {
					if ($this->isDir($this->filename)) {
						$this->delete($this->filename . DIRECTORY_SEPARATOR . $value);
					} else {
						unlink($this->filename . DIRECTORY_SEPARATOR . $value);
					}
				}
			}
			if (@rmdir($this->filename)) {
				return true;
			} else {
				return false;
			}
		} else {
			if (@unlink($this->filename)) {
				return true;
			} else {
				return false;
			}
		}
	}
	public function renames($newname)
	{
		return rename($this->filename, $this->getPath() . DIRECTORY_SEPARATOR . $newname);
	}

	public function open($mode)
	{
		if (!empty(trim($this->filename))) {
			$this->resource = fopen($this->filename, $this->modes[$mode]);
			return $this->resource;
		}
	}

	public function read()
	{
		return htmlspecialchars(file_get_contents($this->filename));
	}

	public function write($data)
	{
		return (!empty($data)) ? fwrite($this->resource, $data) : false;
	}
}	
