<?
	header('Content-Type: text/html; charset=UTF-8');
	session_start();
	set_time_limit(0);

	require_once($_SERVER['DOCUMENT_ROOT'].'/core/vk.api.php');

	if (!empty($_SESSION['token']))
		define('TOKEN', $_SESSION['token']);
	else die('Необходима авторизация.');

	$vk_result_id = json_decode(vk_query('https://api.vk.com/method/database.getCities', array(
		'country_id' => (int)$_GET['country_id'],
		'need_all' => 0,
		'count' => 1000,
 		'access_token' => TOKEN,
		'v' => '5.37'
	)), true);

	echo '<select class="form-control" size="1" name="city_id">
			<option value="0">Все города</option>';
	foreach ($vk_result_id['response']['items'] as $city){
		echo '<option value="'.$city['id'].'">'.$city['title'].'</option>';
	}
	echo '</select>';
?>