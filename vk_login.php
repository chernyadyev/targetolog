<?
	header('Content-Type: text/html; charset=UTF-8');
	session_start();

	define('APP_ID', '***');
	define('APP_SECRET', '***');
	define('REDIRECT_URI', 'http://targetolog.com/vk_login.php');

	if ($_REQUEST['do'] == 'login'){
		if (!empty($_SESSION['uid']))
			$user_token = @file_get_contents($_SERVER['DOCUMENT_ROOT'].'/token/'.$_SESSION['uid'].'/token.txt');

		if (empty($user_token)){
	    	vk_get_token_p1();
	  	} else {
	  		$token = $user_token;
	  		$_SESSION['token'] = $token;
	    	header('Location: /main.php');
	 	}
	} else if (isset($_GET['code'])){
    	if (vk_get_token_p2()){
	    	header('Location: /main.php');
    	}
	}

	function vk_get_token_p1(){
    	$url = 'http://oauth.vk.com/authorize';

	    $params = array(
    	    'client_id' => APP_ID,
        	'redirect_uri' => REDIRECT_URI,
        	'scope' => 'offline',
   		    'response_type' => 'code'
	    );

    	$link = $url.'?'.urldecode(http_build_query($params));
    	header("Location: $link");
    	exit;
	}

	function vk_get_token_p2(){
	    $params = array(
    	    'client_id' => APP_ID,
       		'client_secret' => APP_SECRET,
    	    'code' => $_GET['code'],
   		    'redirect_uri' => REDIRECT_URI
	    );

   		$token = json_decode(file_get_contents('https://oauth.vk.com/access_token' . '?' . urldecode(http_build_query($params))), true);

	    if (isset($token['access_token'])){
	    	$_SESSION['uid'] = $token['user_id'];

    	    $token = $token['access_token'];
  	  		$_SESSION['token'] = $token;
			if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/token/'.$_SESSION['uid'])){
				mkdir($_SERVER['DOCUMENT_ROOT'].'/token/'.$_SESSION['uid']);
			}
    	    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/token/'.$_SESSION['uid'].'/token.txt', $token);
			return true;
    	} else return false;
	}
?>