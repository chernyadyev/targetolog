<?
	header('Content-Type: text/html; charset=UTF-8');

	session_start();
	set_time_limit(0);

	if (!empty($_SESSION['token']))
		define('TOKEN', $_SESSION['token']);
	else die('Необходима авторизация.');

	require_once($_SERVER['DOCUMENT_ROOT'].'/core/vk.api.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/mysql.inc.php');
 	ob_start();

	if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/progress/'.$_SESSION['user']['id']))
	mkdir($_SERVER['DOCUMENT_ROOT'].'/progress/'.$_SESSION['user']['id']);
	$f_progress = $_SERVER['DOCUMENT_ROOT'].'/progress/'.$_SESSION['user']['id'].'/fp.dat';

	$type_f = isset($_POST['type_f']) ? (int)$_POST['type_f'] : 0;

	$country_id = isset($_POST['country_id']) ? (int)$_POST['country_id'] : 0;
	$city_id = isset($_POST['city_id']) ? (int)$_POST['city_id'] : 0;

	list($d_s, $m_s) = isset($_POST['d_start']) ? explode('.', $_POST['d_start']) : 0;
	list($d_f, $m_f) = isset($_POST['d_finish']) ? explode('.', $_POST['d_finish']) : 0;

if ($_POST['do'] == 'get_active'){
	$members_all = array();

    if ($type_f == 0){
		$_POST['users_ids'] = !empty($_POST['users_ids']) ? $_POST['users_ids'] : 'https://vk.com/roomstory';
		$_POST['users_ids'] = preg_replace('@(http|https)://vk.com/@', '', $_POST['users_ids']);
		$gr_ids = explode("\n", $_POST['users_ids']);
		$gr_count = sizeof($gr_ids);

		$j = 1;
		$members_all = array();

		foreach ($gr_ids as $group_id){
			$time_stamp = time();
			$group_id = preg_replace('@(http|https)://vk.com/@', '', $group_id);

			$result = json_decode(vk_query('https://api.vk.com/method/groups.getById', array(
				'group_ids' => $group_id,
				'fields' => 'members_count',
				'access_token' => TOKEN
			)), true);

		    if (!isset($result['response'][0])) continue;
			$group_vk_array = $result['response'][0];

			$group_vk_id = $group_vk_array['gid'];
			if (!isset($group_vk_array['members_count'])) continue;
			$group_vk_count = $group_vk_array['members_count'];

			$query_count = ceil(ceil($group_vk_count / 1000) / 25);

			for ($i=0; $i<$query_count; $i++){
				$offset_out = $i*25000;
		  		$query = "
	  				var members = API.groups.getMembers({\"group_id\": $group_vk_id, \"fields\": \"bdate, city, country\", \"count\":1000, \"offset\": $offset_out}).items;
	  				var offset = 1000;
	  				while (offset < 25000 && (offset+$offset_out < $group_vk_count)){
						members = members + API.groups.getMembers({\"group_id\": $group_vk_id, \"fields\": \"bdate, city, country\", \"count\":1000, \"offset\": ($offset_out + offset)}).items;
						offset = offset + 1000;
	  				};
	  				return members;
		  		";
				$result = json_decode(vk_query('https://api.vk.com/method/execute', array(
					'access_token' => TOKEN,
					'code' => "$query",
					'v' => '5.37'
				)), true);

				if (isset($result['response']))
				foreach ($result['response'] as $user){
					if (!empty($user['bdate'])){
					$b = explode('.', $user['bdate']);
					$d = $b[0]; $m = $b[1];

					if (!isset($user['country']))
						$user['country']['id'] = 0;

					if (($d >= $d_s && $d <= $d_f) && ($m >= $m_s && $m <= $m_f)){
                    	if ($country_id > 0){
                    		if ($country_id == $user['country']['id']){
	                    		if ($city_id > 0){
	                    			if (isset($user['city']['id'])){
			                    		if ($city_id == $user['city']['id'])
			                    			$members_all[] = $user['id'];
			             			}
        	            		} else $members_all[] = $user['id'];
        	           		}
                    	} else $members_all[] = $user['id'];
					}

					}
				}
			}

			if ($gr_count > 0)
			$progress = round((($j+1)/$gr_count)*100,2);
		    if ($progress > 100) $progress = 100;
			file_put_contents($f_progress, 'анализ пользователей: '.$progress);
			$j++;
        }
	}

	if ($type_f == 1){
		$f_data = file_get_contents($_FILES['base_files']['tmp_name']);
		$f_data = preg_replace('@(http|https)://vk.com/@', '', $f_data);
		$gr_ids = explode("\r\n", $f_data);

		$gr_count = sizeof($gr_ids);
		$i = 0;

		$query_count = ceil(ceil($gr_count / 1000) / 25);

		$users_part = array_chunk($gr_ids, 1000, true);
	    $user_connections = array();

		$j = 1;

		foreach ($users_part as $user_ids_1000){
			$user_ids_str = implode(',', $user_ids_1000);
			$vk_result_id = json_decode(vk_query('https://api.vk.com/method/users.get', array(
				'user_ids' => $user_ids_str,
				'fields' => 'bdate, city, country',
	   			'access_token' => TOKEN,
				'v' => '5.37'
			)), true);

			if (isset($result['response']))
			foreach ($result['response'] as $user){
				if (!empty($user['bdate'])){
				$b = explode('.', $user['bdate']);
				$d = $b[0]; $m = $b[1];
				if (($d >= $d_s && $d <= $d_f) && ($m >= $m_s && $m <= $m_f)){
                   	if ($country_id > 0){
                   		if ($country_id == $user['country']['id']){
                    		if ($city_id > 0){
	                    		if ($city_id == $user['city']['id'])
		                    		$members_all[] = $user['id'];
       	            		} else $members_all[] = $user['id'];
       	           		}
                   	} else $members_all[] = $user['id'];
				}
				}
			}

			$progress = round((($j+1)/$query_count)*100,2);
	        if ($progress > 100) $progress = 100;
			file_put_contents($f_progress, 'анализ пользователей: '.$progress);
			$j++;
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
				Найдено: <?=sizeof($members_all);?> посетителей<br />
				<div style="margin-bottom: 10px;" align="right"><a href="/export.php?f=<?=md5($_SESSION['user']['id'].'Jnsb2156')?>">Экспорт ID</a></div>
				</div>
			</div>
		</div>
	</div>
</section>
<?
	$save = false;
	$members_all_str = null;

	if (sizeof($members_all) > 0){
		if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156')))
		mkdir($_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156'));
		$f_export = $_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156').'/export.txt';
		$members_all_str = implode("\r\n", $members_all);
		file_put_contents($f_export, $members_all_str);
		$save = true;
	}

	report_do('beasdey', $save, $members_all_str);
}
?>