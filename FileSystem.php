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

	public function root()
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
class Tools extends FileSystem
{

	function __construct(protected $path)
	{
		
	}

	public function Zip($source)
	{
	    $result = (@opendir($source) === false ? false : true);
	    $rootPath = realpath($source);
	    $zip = new ZipArchive();
	    $zipfilename = date("d-m-Y") . "-" . basename($source) . ".zip";
	    $zip->open($zipfilename, ZipArchive::CREATE | ZipArchive::OVERWRITE );

	    if ($result !== false) {
	        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);
	         
	        foreach ($files as $name => $file) {
	            if (!$file->isDir()) {
	                $filePath = $file->getRealPath();
	                $relativePath = substr($filePath, strlen($rootPath) + 1);
	                $zip->addFile($filePath, $relativePath);
	            }
	        }
	        $zip->close();
	        return TRUE;
	    } else {
	    	if ($this->isFile($source)) {
	    		$zip->addFromString(basename($source), file_get_contents($source));
	    	}
	    }
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

	public function move($destinantion)
	{
		if (file_exists($this->filename)) {
			$this->renames($destinantion . DIRECTORY_SEPARATOR . $this->filename);
		} else {
			print("Nonfounf");
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
/**
 * 
 */
class multiUpload extends FileSystem
{
	protected $getErrorUpload;
	
	function __construct(protected $source, protected $pathUpload = null,)
	{
		parent::__construct(getcwd());
	}

	public function pathUpload($pathUpload)
	{
		return $this->pathUpload = $pathUpload;
	}

	public function Upload()
	{
		$files = count($this->source['tmp_name']);
		for ($i=0; $i < $files ; $i++) { 
			copy($this->source['tmp_name'][$i], $this->pathUpload . DIRECTORY_SEPARATOR . $this->source['name'][$i]);
		}
	}

	public function getInfoUpload()
	{
		$json[] = array(
			"fileName" => $this->source['name'],
			"fileSize" => $this->source['size'],
			"filePath" => $this->pathUpload
		);
		$json_arr = json_encode($json);
		return json_decode($json_arr);
	}
}

// $FileSystem = new FileSystem(getcwd());

// $Tools = new Tools(getcwd());

// if (isset($_POST['sub'])) {
// 	$Upload = new multiUpload($_FILES['file']);
// 	$Upload->pathUpload($_POST['dir']);
// 	$Upload->Upload();
// 	$Upload->getInfoUpload();

// }
?>
<!-- <form method="post" enctype="multipart/form-data">
	<input type="file" name="file[]" multiple>
	<input type="hidden" name="dir" value="<?= $Tools->getPath() ?>/img">
	<input type="submit" name="sub">
</form> -->

