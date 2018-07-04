<?
	header('Content-Disposition: attachment; filename=export.txt');
	header('Content-Type: text/plain; charset=utf-8');
	$f = $_GET['f'];
	$t = isset($_GET['t']) ? (int)$_GET['t'] : null;

	if (empty($t)){		if (file_exists($_SERVER['DOCUMENT_ROOT'].'/export/'.$f.'/export.txt'))
			$data = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/export/'.$f.'/export.txt');
		else $data = null;
	}
	else {		if ($t == 1)
			$data = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/export/'.$f.'/export_id.txt');
		if ($t == 2)
			$data = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/export/'.$f.'/export_url.txt');
	}
	echo $data;
	exit;
?>