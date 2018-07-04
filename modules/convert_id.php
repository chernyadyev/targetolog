<?
header('Content-Type: text/html; charset=UTF-8');

session_start();
set_time_limit(0);

if (!empty($_SESSION['token']))
	define('TOKEN', $_SESSION['token']);
else die('Необходима авторизация.');

require_once($_SERVER['DOCUMENT_ROOT'].'/core/vk.api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/mysql.inc.php');

if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/progress/'.$_SESSION['user']['id']))
	mkdir($_SERVER['DOCUMENT_ROOT'].'/progress/'.$_SESSION['user']['id']);
$f_progress = $_SERVER['DOCUMENT_ROOT'].'/progress/'.$_SESSION['user']['id'].'/fp.dat';

$gr_url = isset($_POST['gr_url']) ? $_POST['gr_url'] : null;
$type_f = isset($_POST['type_f']) ? (int)$_POST['type_f'] : 0;

function no_gr($var){
	if ($var > 0) return 1;
		else return 0;
}
if ($_POST['do'] == 'get_active'){
	$date = isset($_POST['date']) ? $_POST['date'] : 0;

	$_POST['group_id'] = !empty($_POST['group_id']) ? $_POST['group_id'] : 'https://vk.com/prinum';
	$_POST['group_id'] = preg_replace('@(http|https)://vk.com/@', '', $_POST['group_id']);
	$gr_ids = explode("\n", $_POST['group_id']);
	$gr_count = sizeof($gr_ids);
	$i = 0;

	if ($type_f == 0){
		$query_count = ceil(ceil($gr_count / 1000) / 25);
		$users_part = array_chunk($gr_ids, 1000, true);
	    $user_connections = array();

		foreach ($users_part as $user_ids_1000){
			$user_ids_str = implode(',', $user_ids_1000);
			$vk_result_id = json_decode(vk_query('https://api.vk.com/method/users.get', array(
				'user_ids' => $user_ids_str,
				'fields' => 'id, screen_name',
	   			'access_token' => TOKEN,
				'v' => '5.37'
			)), true);

			foreach ($vk_result_id['response'] as $user){
            	$data_id[] = $user['id'];
            	$data_url[] = 'https://vk.com/'.$user['screen_name'];
			}

			$progress = round((($i+1)/$query_count)*100,2);
	        if ($progress > 100) $progress = 100;
			file_put_contents($f_progress, 'анализ пользователей: '.$progress);
			$i++;
		}
	} else {		$query_count = ceil(ceil($gr_count / 500) / 25);
		$users_part = array_chunk($gr_ids, 500, true);
	    $user_connections = array();

		foreach ($users_part as $user_ids_500){
			$user_ids_str = implode(',', $user_ids_500);
			$vk_result_id = json_decode(vk_query('https://api.vk.com/method/groups.getById ', array(
				'group_ids' => $user_ids_str,
				'fields' => 'id, screen_name',
	   			'access_token' => TOKEN,
				'v' => '5.37'
			)), true);


			if (isset($vk_result_id['response']))
			if (sizeof($vk_result_id['response']) > 0){
				foreach ($vk_result_id['response'] as $user){
    	        	$data_id[] = $user['id'];
        	    	$data_url[] = 'https://vk.com/'.$user['screen_name'];
				}
			}

			$progress = round((($i+1)/$query_count)*100,2);
	        if ($progress > 100) $progress = 100;
			file_put_contents($f_progress, 'анализ пользователей: '.$progress);
			$i++;
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
				  	<a href="/export.php?t=1&f=<?=md5($_SESSION['user']['id'].'Jnsb2156')?>">Сохранить ID</a><br />
				  	<a href="/export.php?t=2&f=<?=md5($_SESSION['user']['id'].'Jnsb2156')?>">Сохранить URL</a>
				</div>
			</div>
		</div>
	</div>
</section>
<?

if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156')))
	mkdir($_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156'));

	$f_export_id = $_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156').'/export_id.txt';
	$f_export_url = $_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156').'/export_url.txt';


if (isset($data_id))
if (is_array($data_id)){
	file_put_contents($f_export_id, implode("\r\n", $data_id));
}

if (isset($data_url))
if (is_array($data_url)){
	file_put_contents($f_export_url, implode("\r\n", $data_url));
}

}
?>