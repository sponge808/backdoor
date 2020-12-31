<?php
/**************webKiller*****************
[+] Author: p4ny
[+] 版本: v2.0
[+] License: GPL-2
请自行判断、审核、对比原文件。           
*/
define('PASSWORD', '123123');
error_reporting(E_ERROR);
ini_set('max_execution_time',20000);
ini_set('memory_limit','512M');
header("content-Type: text/html; charset=utf-8");
if($_GET['action']=='delself'){
$url = $_SERVER['PHP_SELF'];
$file= substr( $url , strrpos($url , '/')+1 );
if (file_exists("./".$file)){
      @unlink ("./".$file);
}
}
if(!isset($_COOKIE['rabbitx']) || $_COOKIE['rabbitx'] != md5(PASSWORD)) {
    if($_SERVER['REQUEST_METHOD']=='GET'){
echo '<html>
<head><title>safety certificate</title>
</head>
<style>
table{font-size:9pt;}
</style>
<body><br>
<form method="post" action="">
<table border="0" cellpadding="3" cellspacing="1" align="center" width="300" bgcolor="#3399CC">
<tr height="25" bgcolor="#E7E7E7"><td colspan="2"><b>WebKiller--</b>verification</td></tr>
<tr height="25" bgcolor="#e7f7f7" ><td align="right">Password：</td><td><input type="text" name="pwd"></td></tr>';
echo '<tr height="25" bgcolor="#e7f7f7" ><td></td><td><input type="submit" name="login_submit" value="enter">';
echo "<font color=red>".$str."</font>";
echo '</td></tr>
</table>
</form>
</body>
</html>';
die();
}
else {
        if (isset($_POST['pwd']) && $_POST['pwd'] == PASSWORD){
            $mypwd = md5(PASSWORD);
            setcookie('rabbitx', $mypwd);
            echo "<script>document.cookie='rabbitx=".$mypwd."';window.location.href='';</script>";
            die();
        } else {
            $str="wrong password!";
echo '<html>
<head><title>safety certificate</title>
</head>
<style>
table{font-size:9pt;}
</style>
<body><br>
<form method="post" action="">
<table border="0" cellpadding="3" cellspacing="1" align="center" width="300" bgcolor="#3399CC">
<tr height="25" bgcolor="#E7E7E7"><td colspan="2"><b>WebKiller--</b>verification</td></tr>
<tr height="25" bgcolor="#e7f7f7" ><td align="right">Password：</td><td><input type="text" name="pwd"></td></tr>';
echo '<tr height="25" bgcolor="#e7f7f7" ><td></td><td><input type="submit" name="login_submit" value="enter">';
echo " <font color=red>".$str."</font>";
echo '</td></tr>
</table>
</form>
</body>
</html>';
            die();
        }
    }
}
//特征库
$shellLib = array(
    '/function\_exists\s*\(\s*[\'|\"](popen|exec|proc\_open|system|passthru)+[\'|\"]\s*\)/i',
    '/(exec|shell\_exec|system|passthru)+\s*\(\s*\$\_(\w+)\[(.*)\]\s*\)/i',
    '/((udp|tcp)\:\/\/(.*)\;)+/i',
    '/preg\_replace\s*\((.*)\/e(.*)\,\s*\$\_(.*)\,(.*)\)/i',
    '/preg\_replace\s*\((.*)\(base64\_decode\(\$/i',
    '/(eval|assert|include|require|include\_once|require\_once)+\s*\(\s*(base64\_decode|str\_rot13|gz(\w+)|file\_(\w+)\_contents|(.*)php\:\/\/input)+/i',
    '/(eval|assert|include|require|include\_once|require\_once|array\_map|array\_walk)+\s*\(\s*\$\_(GET|POST|REQUEST|COOKIE|SERVER|SESSION)+\[(.*)\]\s*\)/i',
    '/eval\s*\(\s*\(\s*\$\$(\w+)/i',
    '/chr(99).chr(104).chr(114)/i',
    '/(include|require|include\_once|require\_once)+\s*\(\s*[\'|\"](\w+)\.(jpg|gif|ico|bmp|png|txt|zip|rar|htm|css|js)+[\'|\"]\s*\)/i',
    '/\$\_(\w+)(.*)(eval|assert|include|require|include\_once|require\_once)+\s*\(\s*\$(\w+)\s*\)/i',
    '/\(\s*\$\_FILES\[(.*)\]\[(.*)\]\s*\,\s*\$\_(GET|POST|REQUEST|FILES)+\[(.*)\]\[(.*)\]\s*\)/i',
    '/(fopen|fwrite|fputs|file\_put\_contents)+\s*\((.*)\$\_(GET|POST|REQUEST|COOKIE|SERVER)+\[(.*)\](.*)\)/i',
    '/echo\s*curl\_exec\s*\(\s*\$(\w+)\s*\)/i',
    '/new com\s*\(\s*[\'|\"]shell(.*)[\'|\"]\s*\)/i',
    '/(eval|assert).([\s\S]*).ob_start(\s|\/\*.*?\*\/)*\(\s*.*?\s*\)/i',
    '/\$(.*)\s*\((.*)\/e(.*)\,\s*\$\_(.*)\,(.*)\)/i',
    '/\$\_\=(.*)\$\_/i',
    '/\$\_(GET|POST|REQUEST|COOKIE|SERVER)+\[(.*)\]\(\s*\$(.*)\)/i',
    '/\$(\w+)\s*\(\s*\$\_(GET|POST|REQUEST|COOKIE|SERVER)+\[(.*)\]\s*\)/i',
    '/\$(\w+)\s*\(\s*\$\{(.*)\}/i',
    '/\$(\w+)\s*\(\s*chr\(\d+\)/i',
    '/(phpspy|4ngel|wofeiwo|c99shell|webshell|php_nst|reDuh|tools88\.com|silic)/i',
    '/\$[\w-_\'\\[\\]{}\.\$\*/|]+(\s|\/\*.*?\*\/)*\(.*?\)',
    '/create_function/i',
    '/chr\\([^\\)]+\\).+chr\\([^\\)]+\\)/i',
    '/array_map\(\"a/i',
    '/\\$\_\=\"\"\;/i',
    '/array_map(\s|\/\*.*?\*\/)*\(\s*.*?\s*\)/i',
    '/array_map\(\'a/i',
    '/(php_valueauto_prepend_file|php_valueauto_append_file)/i', //.htaccess插马特征
    '/SetHandlerapplication\/x-httpd-php/i', //.htaccess插马特征
    '/.*?(b).*?(a).*?(s).*?(e).*?(6).*?(4).*?(_).*?(d).*?(e).*?(c).*?(d).*?(e)[\w\s]*(\"|\')/i',//weevely加密特征
    '/ReflectionFunction\((\$_(GET|POST)|\"SYSTEM)/i',
    '/\\$[a-z]+=\\$[a-z]+\(\'\',\\$[a-z]+\(\\$[a-z]+\("[a-z]+","",\\$[a-z]+\.\\$[a-z]+\.\\$[a-z]+\.\\$[a-z]+\)\)\);/i',
    '/assert.*(\$_POST|\$_REQUEST|\$_GET)/i'
);
function shellScan($fileexs,$dir,$shellLib){
    if(($handle = @opendir($dir)) == false) 
        return false;
    while ( false !== ( $filename = readdir ( $handle ))) {
        if($filename == '.' || $filename == '..') continue;
        $filepath = $dir.$filename;
        if(is_dir($filepath)){
            if(is_readable($filepath)) 
                shellScan($fileexs,$filepath.'/',$shellLib);
        }
            elseif(strpos($filename,';') > -1 || strpos($filename,'%00') > -1 || strpos($filename,'/') > -1) {
                        echo '<tr class="danger">
                        <td>
                            Parsing vulnerability
                        </td>
                        <td>
                        '.$filepath.'
                        </td>
                        </tr>';
                        flush();
                        ob_flush();
        }
        else{
            if(!preg_match($fileexs,$filename)) continue;
            if(filesize($filepath) > 10000000) continue;
            $fp = fopen($filepath,'r');
            $code = fread($fp,filesize($filepath));
            fclose($fp);
            if(empty($code)) continue;
            foreach($shellLib as $matche) {
                $array = array();
                preg_match($matche,$code,$array);
                if(!$array) continue;
                if(strpos($array[0],"\x24\x74\x68\x69\x73\x2d\x3e")) continue;
                $len = strlen($array[0]);
                if($len >= 5 && $len < 200) {
                    echo '
                    <tr class="danger">
                        <td>
                            '.htmlspecialchars($array[0]).'
                        </td>
                        <td>
                            '.$filepath.'
                        </td>
                    </tr>';
                    //echo '特征 <input type="text" style="width:218px;" value="'.htmlspecialchars($array[0]).'"> '.$filepath.'<div></div>';
                    flush(); ob_flush(); break;
                }
            }
            unset($code,$array);
        }
      }
    closedir($handle);
    return true;
}
function setdir($str) {
    $order=array('\\','//','//');
    $replace=array('/','/','/');
    return str_replace($order,$replace,rtrim($str)); 
}
?>
<!DOCTYPE html>
<html>
<head>
<script src="http://www.knownsec.com/static/js/jquery-1.6.4.min.js"></script>
</head>
<body>
<div class="main">
    <div class="hero-unit">
        <h2 class="title">webKiller V 2.0</h2>
        <div class="check">
            <a id='logout' class="btn btn-primary" onclick="this.innerText='Logging out..';logout()">Logout</a>
            <a id='del' class="btn btn-primary" value="delself" onclick="this.innerText='Destroying..';getLabelsGet()">Delself</a>
        </div>
    </div>
    <div class="content">
        <table>
            <thead>
            <tr> 
                <div id='scanmod'>
                    <form  id="scan" method="post" action="">
                        Detection path：
                        <input type="text" id="chk_dir" name="dir" value="<?php echo($_POST['dir'] ? setdir($_POST['dir'].'/') : setdir($_SERVER['DOCUMENT_ROOT'].'/'));?>"/> Do not fill in the directory where this document is located
                        <br />
                        File extension:
                        <input type="text" id="file_types" name="fileexs" value=".php|.inc|.phtml"/> File type, such as：php,inc
                        <br /><br/>
                        <input class="btn btn-success" style="width:100px;" type="submit" value="Scan" onclick="this.value='Scanning...'"/>
                    </form><button class="btn btn-success" style="width:100px;" onclick="clera();">Reset</button>
                </div>
            </tr>
            </thead><br/>
        </table>
        <?php
        ?>
        <table id="result" class="table table-striped table-condensed" >
            <thead id="theadtitle" style="display:none">
                <tr>
                    <th>feature</th>
                    <th>path</th>
                </tr>
            </thead>
        <?php
            if(file_exists($_POST['dir']) && $_POST['fileexs']) {
            $dir = setdir($_POST['dir'].'/');
            $fileexs = '/('.str_replace('.','\\.',$_POST['fileexs']).')/i';
            echo '<script>document.getElementById("theadtitle").style.display="";</script>';
            $result = shellScan($fileexs,$dir,$shellLib) ? '<div></div>Scan completed' : '<div></div>Scan interrupted';
            echo '</tbody>
            </table>';
            }
        ?>
    </div>
<script>
    function logout(){
        document.cookie='rabbitx=0';
        document.cookie='flag=0';
        location.reload();
    }
    function clera(){
        document.getElementById("file_types").value=".php|.inc|.phtml"; 
        document.getElementById("chk_dir").value="<?php echo($_POST['dir'] ? setdir($_POST['dir'].'/') : setdir($_SERVER['DOCUMENT_ROOT'].'/'));?>";
    }
    </script>
<script type="text/javascript">
    var xmlHttp;
    function GetXmlHttpObject(){
        if (window.XMLHttpRequest){
          xmlhttp=new XMLHttpRequest();
        }else{
          xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        return xmlhttp;
    }
    
    function getLabelsGet(){
        xmlHttp=GetXmlHttpObject();
        if (xmlHttp==null){
            alert('Your browser does not support AJAX!');
            return;
        }
        var id = 'delself';
        if( confirm('Delself ?') ){
        var url="<?php echo $_SERVER['PHP_SELF'];?>?action="+id;
        xmlHttp.open("GET",url);
        xmlHttp.onreadystatechange=getOkGet;
        xmlHttp.send();
        location.reload();
        }
    }
                       
    function getOkGet(){
        if(xmlHttp.readyState==1||xmlHttp.readyState==2||xmlHttp.readyState==3){                
        }
        if (xmlHttp.readyState==4 && xmlHttp.status==200){
            var d= xmlHttp.responseText;
        }
    }
</script>
</html>
