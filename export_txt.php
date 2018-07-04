<?
	session_start();

	header('Content-Disposition: attachment; filename=export.txt');
	header('Content-Type: text/plain; charset=utf-8');
	$f = $_GET['f'];

	$data =  file_get_contents($_SERVER['DOCUMENT_ROOT'].'/reports/'.$_SESSION['user']['id'].'/'.$f.'.txt');
	echo $data;
	exit;
?>