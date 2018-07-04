<?
	header('Content-Type: text/html; charset=UTF-8');
	session_start();

	if ($_POST['do'] == 'get_active'){
		if (!empty($_FILES['base_a']['tmp_name']) && !empty($_FILES['base_b']['tmp_name'])){			$f_data_a = file_get_contents($_FILES['base_a']['tmp_name']);
			$f_data_b = file_get_contents($_FILES['base_b']['tmp_name']);

			$f_data_a = preg_replace('@(http|https)://vk.com/@', '', $f_data_a);
			$f_data_a_arr = explode("\r", $f_data_a);

			$f_data_b = preg_replace('@(http|https)://vk.com/@', '', $f_data_b);
			$f_data_b_arr = explode("\r", $f_data_b);

			if ($_POST['doit'] == 'minus'){
				if (is_array($f_data_a_arr) && is_array($f_data_b_arr))					$base_new  = array_diff($f_data_a_arr, $f_data_b_arr);
			}
			if ($_POST['doit'] == 'peresek'){
				if (is_array($f_data_a_arr) && is_array($f_data_b_arr))
					$base_new  = array_intersect($f_data_a_arr, $f_data_b_arr);
			}
			if ($_POST['doit'] == 'union'){
				if (is_array($f_data_a_arr) && is_array($f_data_b_arr))
					$base_new  = array_unique(array_merge($f_data_a_arr, $f_data_b_arr));
			}
		}
	echo '<script>$("#progress").hide();</script>';
?>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-solid">
				<div class="box-header with-border">
					<h3 class="box-title">Результаты:</h3>
				</div>
				<div class="box-body text-center">
					<a href="/export.php?f=<?=md5($_SESSION['user']['id'].'Jnsb2156')?>">Экспорт ID</a>
				</div>
			</div>
		</div>
	</div>
<?
	if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156')))
		mkdir($_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156'));
		$f_export = $_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156').'/export.txt';
		if (is_array($base_new))
			file_put_contents($f_export, implode("\r\n", $base_new));
	}
?>
</section>