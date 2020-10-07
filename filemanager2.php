<style type="text/css">
	body {
		overflow: hidden;
	}
	.files {
		overflow: auto;
		border-radius:20px;
		background: #fff;
		padding:15px;
		box-shadow: 0 0 3px rgba(0,0,0,0.16), 0 3px 6px rgba(0,0,0,0.23);
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
        min-width:90px;
        display: inline-block;
    }
    .block .date .dir-perms,
    .block .date .file-perms {
        min-width:100px;
        display: inline-block;
    }
    .block .date .dir-time,
    .block .date .file-time {
        min-width:150px;
        display: inline-block;
    }
    .block .date .dir-owner,
    .block .date .file-owner {
        min-width:100px;
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
    	box-shadow: 0 0 3px rgba(0,0,0,0.16), 0 3px 6px rgba(0,0,0,0.23);
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
    	background: green;
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
    	background: red;
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
    .span {
    	position: absolute;
    	padding:5px;
    	margin-right:-20px;
    	background: pink;
    	display: inline-block;
    }
</style>
<?php
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
		$filename['name'] = basename($filename['fullname']);

		$array[] = $filename; 
	} return $array;
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
	if(!empty($letters)) {
		print "<div class='span'>Detected Drives :</div> {$letters}<br>";
	}
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
	chdir($directory);
}
?>
<div class="cwd">
	<div class="disk">
		<span><?= disk() ?></span>
	</div>
	<div class="pwdd">
		Current Dir : <?= pwd() ?>
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
							//size
						</div>
						<div class="dir-perms">
							//perms
						</div>
						<div class="dir-time">
							//time
						</div>
						<div class="dir-owner">
							//owner
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
					<img src="https://image.flaticon.com/icons/svg/833/833524.svg">
				</div>
				<div class="name">
					<?= $file['name'] ?>
					<div class="date">
						<div class="file-size">
							//size
						</div>
						<div class="file-perms">
							//perms
						</div>
						<div class="file-time">
							//time
						</div>
						<div class="file-owner">
							//owner
						</div>
					</div>
				</div>
			</a>
		</div>
	<?php }
?>
</div>
