<?
	$conn = mysql_connect('127.0.0.1', '***', '***');
	mysql_select_db('targetolog');

	function report_do($type, $save, $export_f=null){
		$html = ob_get_contents();

		if ($save) {
			if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/reports/'.$_SESSION['user']['id']))
				mkdir($_SERVER['DOCUMENT_ROOT'].'/reports/'.$_SESSION['user']['id']);
			$fname = md5(rand(0,99999).$type);

			$f_export_html = $_SERVER['DOCUMENT_ROOT'].'/reports/'.$_SESSION['user']['id'].'/'.$fname.'.html';
			$f_export_txt = $_SERVER['DOCUMENT_ROOT'].'/reports/'.$_SESSION['user']['id'].'/'.$fname.'.txt';

			$html = preg_replace('@(export.php\?f=(.*)")@', 'export_txt.php?f='.$fname.'"', $html);

			file_put_contents($f_export_html, $html);
			if (!empty($export_f))
				file_put_contents($f_export_txt, $export_f);

			$uid = $_SESSION['user']['id'];
			$time = time();

			$sql = "INSERT INTO `tlog_reports` (`id`, `uid`, `time`, `fname`, `type`) VALUES (NULL, $uid, $time, '$fname', '$type')";
			mysql_query($sql);
		}

		ob_end_flush();
	}

?>