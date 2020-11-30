<?php
function files($type, $dir) {
  $result = [];
  foreach (scandir($dir) as $key => $value) {
    $filename['fullname'] = $dir . DIRECTORY_SEPARATOR . $value;
    switch ($type) {
      case 'dir':
        if (!is_dir($filename['fullname']) || $value === '.' || $value === '..') continue 2;
        break;
      
      case 'file':
        if (!is_file($filename['fullname'])) continue 2;
        break;
    }
    $filename['name'] = basename($filename['fullname']);

    $result[] = $filename;
  } return $result;
}
if ($_POST) {
  if ($_POST['ls'] ==='ls') {
    foreach (files('dir', $_POST['dir']) as $key => $value) { ?>
      <tr>
        <td>
          <?= $value['name'] ?>
        </td>
      </tr>
    <?php }
    foreach (files('file', $_POST['dir']) as $key => $value) { ?>
      <tr>
        <td>
          <?= $value['name'] ?>
        </td>
      </tr>
    <?php }
  }
    else {}
}
