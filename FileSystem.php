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
		return str_replace('\\', DIRECTORY_SEPARATOR, $this->path);
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

	public static function cd($directory)
	{
		return chdir($directory);
	}

	public function getExtension($filename)
	{
		return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	}

	public function getIcon($filename, $type)
	{
		switch ($type) {
			case 'dir':
				return "https://image.flaticon.com/icons/svg/715/715676.svg";
				break;
			
			case 'file':
				switch ($this->getExtension($filename)) {
		            case 'php1':
		            case 'php2':
		            case 'php3':
		            case 'php4':
		            case 'php5':
		            case 'php6':
		            case 'phtml':
		            case 'php':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306154.svg';
						break;
		            case 'html':
		            case 'htm':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306098.svg';
						break;
		            case 'css':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306041.svg';
						break;
		            case 'js':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306122.svg';
						break;
		            case 'json':
		            	return 'https://image.flaticon.com/icons/svg/136/136525.svg';
						break;
		            case 'xml':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306209.svg';
						break;
		            case 'py':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2721/2721287.svg';
						break;
		            case 'zip':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306214.svg';
						break;
		            case 'rar':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306170.svg';
						break;
		            case 'htaccess':
		            	return 'https://image.flaticon.com/icons/png/128/1720/1720444.png';
						break;
		            case 'txt':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306185.svg';
						break;
		            case 'ini':
		            	return 'https://image.flaticon.com/icons/svg/1126/1126890.svg';
						break;
		            case 'mp3':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306139.svg';
						break;
		            case 'mp4':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306142.svg';
						break;
		            case 'log':
		            case 'log1':
		            case 'log2':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306124.svg';
						break;
		            case 'psd':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306166.svg';
						break;
		            case 'dat':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306050.svg';
						break;
		            case 'exe':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306085.svg';
						break;
		            case 'apk':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306016.svg';
						break;
		            case 'yaml':
		            	return 'https://cdn1.iconfinder.com/data/icons/hawcons/32/698694-icon-103-document-file-yml-512.png';break;
		            case 'xlsx':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306200.svg';
						break;
		            case 'bak':
		            	return 'https://image.flaticon.com/icons/svg/2125/2125736.svg';
						break;
		            case 'ico':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306102.svg';
						break;
		            case 'png':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306156.svg';
						break;
		            case 'jpg':
		            case 'webp':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306117.svg';
						break;
		            case 'jpeg':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306114.svg';
						break;
		            case 'svg':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306179.svg';
						break;
		            case 'gif':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306094.svg';
						break;
		            case 'pdf':
		            	return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306145.svg';
						break;
		            case 'asp':
		            	return "https://www.flaticon.com/svg/static/icons/svg/2306/2306019.svg";
						break;
		            case 'doc':
		            	return "https://www.flaticon.com/svg/static/icons/svg/2306/2306060.svg";
						break;
		            case 'docx':
		            	return "https://www.flaticon.com/svg/static/icons/svg/2306/2306065.svg";
						break;
		            case 'otf':
		            	return "https://www.flaticon.com/svg/static/icons/svg/2306/2306149.svg";
						break;
		            case 'ttf':
		            	return "https://www.flaticon.com/svg/static/icons/svg/2306/2306182.svg";
						break;
		            case 'wav':
		            	return "https://www.flaticon.com/svg/static/icons/svg/2306/2306188.svg";
						break;
		            case 'sql':
		            	return "https://www.flaticon.com/svg/static/icons/svg/2306/2306173.svg";
						break;
		            case 'csv':
		            	return "https://www.flaticon.com/svg/static/icons/svg/2306/2306046.svg";
						break;
		            case 'bat':
		            	return "https://www.flaticon.com/svg/static/icons/svg/2306/2306025.svg";
						break;
		            default:
		            	return 'https://image.flaticon.com/icons/svg/833/833524.svg';
						break;
        		}
				break;
		}
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

	public function perms($filename)
	{
		$perms = @fileperms($filename);

		switch ($perms & 0xF000) {
		    case 0xC000: // socket
		        $info = 's';
		        break;
		    case 0xA000: // symbolic link
		        $info = 'l';
		        break;
		    case 0x8000: // regular
		        $info = 'r';
		        break;
		    case 0x6000: // block special
		        $info = 'b';
		        break;
		    case 0x4000: // directory
		        $info = 'd';
		        break;
		    case 0x2000: // character special
		        $info = 'c';
		        break;
		    case 0x1000: // FIFO pipe
		        $info = 'p';
		        break;
		    default: // unknown
		        $info = 'u';
		}

		// Owner
		$info .= (($perms & 0x0100) ? 'r' : '-');
		$info .= (($perms & 0x0080) ? 'w' : '-');
		$info .= (($perms & 0x0040) ?
		            (($perms & 0x0800) ? 's' : 'x' ) :
		            (($perms & 0x0800) ? 'S' : '-'));

		// Group
		$info .= (($perms & 0x0020) ? 'r' : '-');
		$info .= (($perms & 0x0010) ? 'w' : '-');
		$info .= (($perms & 0x0008) ?
		            (($perms & 0x0400) ? 's' : 'x' ) :
		            (($perms & 0x0400) ? 'S' : '-'));

		// World
		$info .= (($perms & 0x0004) ? 'r' : '-');
		$info .= (($perms & 0x0002) ? 'w' : '-');
		$info .= (($perms & 0x0001) ?
		            (($perms & 0x0200) ? 't' : 'x' ) :
		            (($perms & 0x0200) ? 'T' : '-'));

		return $info;
	}

	public function getFileSize($filename, $digits = 2)
	{
		if ($this->isFile($filename)) {
			$filePath = $filename;
			if (!realpath($filePath)) {
				$filePath = $this->root() . $filePath;
			}

			$fileSize = filesize($filePath);
			$frmtSize = array("TB ", "GB ", "MB ", "KB ", "B ");
			$total	  = count($frmtSize);

			while ($total -- && $fileSize > 1024) {
				$fileSize /= 1024;
			} return round($fileSize, $digits) . " " . $frmtSize[$total];
		} return false;
	}

	public function countDir($directory)
	{
		$dir = @opendir($directory);
	    $c = 0;
	    while (($file = readdir($dir)) !== false)
	        if (!in_array($file, array('.', '..')))
	            $c++;
	    closedir($dir);
	    return $c;
	}

	public function pwd() {
		$dir = preg_split("/(\\\|\/)/", getcwd());
		?>
		<nav aria-label="breadcrumb">
	  		<ol class="breadcrumb">
	  			<?php
	  			foreach ($dir as $key => $value) {
	  				if($value=='' && $key==0) {
	  					echo '<li class="breadcrumb-item"><a href="?x=/">/</a></li>';
	  				}
	  				if($value == '') { 
	  					continue;
	  				}
	  				echo '<li class="breadcrumb-item"><a href="?x=';
	  				for ($i = 0; $i <= $key; $i++) {
	  					echo $dir[$i]; 
	  					if($i != $key) {
	  						echo '/';
	  					}
	  				}
	  				print('">'.$value.'</a></li>');
	  			}
	  			?>
			</ol>
		</nav>
		<?php
	}


	public function getFileTime($filename)
	{
		return date("d-m-Y H:i:s", filemtime($filename));
	}

	public function Dir()
	{
		$this->result = [];
		foreach ($this->scDir() as $key => $value) {
			$filename = [
				"pathName" 		=> $this->path . DIRECTORY_SEPARATOR . $value,
				"singlePath"	=> $value,
				"filePerms"		=> $this->perms($value),
				"fileTime"		=> $this->getFileTime($value),
				"getSize"		=> $this->isDir($value) ? $this->countDir($value) : false,
				"modeChmod" 	=> substr(sprintf("%o", fileperms($value)), -4),
				"getIcon"		=> $this->isDir($value) ? $this->getIcon($value, "dir") : false
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
				"singlePath"	=> $value,
				"filePerms"		=> $this->perms($value),
				"fileTime"		=> $this->getFileTime($value),
				"getSize"		=> $this->isFile($value) ? $this->getFileSize($value) : false,
				"modeChmod" 	=> substr(sprintf("%o", fileperms($value)), -4),
				"getIcon"		=> $this->isFile($value) ? $this->getIcon($value, "file") : false
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

	public function chmods($mode)
	{
		if (file_exists($this->filename)) {
			chmod($this->filename, $mode);
		} else {
			return false;
		}
	}

	public function move($destinantion)
	{
		if (file_exists($this->filename)) {
			$this->renames($destinantion . DIRECTORY_SEPARATOR . $this->filename);
		} else {
			return false;
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
			$data = copy($this->source['tmp_name'][$i], $this->pathUpload . DIRECTORY_SEPARATOR . $this->source['name'][$i]);
			if ($data) {
				$dataJson = array(
					"fileName" => $this->source['name'][$i],
					"fileSize" => $this->source['size'][$i],
					"fileType" => $this->source['type'][$i],
					"filePath" => $this->pathUpload
				);
				print json_encode($dataJson);
			} else {
				print json_encode($this->source['error'][$i]);
			}
		}
	}
}
