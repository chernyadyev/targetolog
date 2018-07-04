<?
	session_start();
	unset($_SESSION);
	session_destroy();
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: /");
	exit();
?>