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

if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/progress/'.$_SESSION['user']['id'])){
	mkdir($_SERVER['DOCUMENT_ROOT'].'/progress/'.$_SESSION['user']['id']);
}
$f_progress = $_SERVER['DOCUMENT_ROOT'].'/progress/'.$_SESSION['user']['id'].'/fp.dat';

function no_gr($var){
	if ($var > 0) return 1;
		else return 0;
}
if ($_POST['do'] == 'get_active'){

	$_POST['group_id'] = !empty($_POST['group_id']) ? $_POST['group_id'] : 'https://vk.com/prinum';
	$type_f = isset($_POST['type_f']) ? (int)$_POST['type_f'] : 0;
	$date = isset($_POST['date']) ? $_POST['date'] : 0;

	$gr_ids = explode("\n", $_POST['group_id']);
	$gr_count = sizeof($gr_ids);
	$j = 1;

	$u_fr = $follower_all = $user_ids = array();
	$i = $j = 0;
	foreach ($gr_ids as $gr_url){

		$user_id = preg_replace('@(http|https)://vk.com/@', '', $gr_url);
		$vk_result_id = json_decode(vk_query('https://api.vk.com/method/users.get', array(
			'user_ids' => $user_id,
			'fields' => 'followers_count',
			'access_token' => TOKEN,
			'v' => '5.37'
		)), true);

	    if (!isset($vk_result_id['response'][0])) continue;

	    $user_id = $vk_result_id['response'][0]['id'];
    	$user_followers_count = $vk_result_id['response'][0]['followers_count'];

		$time_stamp = time();		if ($type_f == 0 || $type_f == 2){
			if ($i >= 24){
                $user_ids_str = implode(',', $user_ids);
                $query = '
                	var user_ids = ['.$user_ids_str.'];
                	var i = 0;
                	var frend = [];

                	while (i < user_ids.length){
                		frend = frend + API.friends.get({"user_id": user_ids[i]}).items;
	                	i = i + 1;
                	}

                	return frend;';

				$result = json_decode(vk_query('https://api.vk.com/method/execute', array(
					'access_token' => TOKEN,
					'code' => "$query",
					'v' => '5.37'
				)), true);

				$u_fr = array_merge($u_fr, $result['response']);

            	$i = 0;
            	$user_ids = array();
                }

				$user_ids[] = $user_id;
				$i++;

		if (!empty($user_ids)){
	        $user_ids_str = implode(',', $user_ids);
	        $query = '
	        	var user_ids = ['.$user_ids_str.'];
	        	var i = 0;
	        	var frend = [];

	        	while (i < user_ids.length){
	        		frend = frend + API.friends.get({"user_id": user_ids[i]}).items;
	         		i = i + 1;
	        	}

	        	return frend;';

			$result = json_decode(vk_query('https://api.vk.com/method/execute', array(
				'access_token' => TOKEN,
				'code' => "$query",
				'v' => '5.37'
			)), true);

			$u_fr = array_merge($u_fr, $result['response']);
		}

	}

		if ($type_f == 1 || $type_f == 2){			$followers_count = ceil(ceil($user_followers_count / 1000) / 25);
			for ($i=0; $i<$followers_count; $i++){
				$time_stamp = time();

				$offset_out = $i*25000;
		  		$query = "
		  			var members = API.users.getFollowers({\"user_id\": $user_id, \"count\":1000, \"offset\": $offset_out}).items;
		  			var offset = 1000;
		  			while (offset < 25000 && (offset+$offset_out < $followers_count)){
						members = members + API.groups.getMembers({\"user_id\": $user_id, \"count\":1000, \"offset\": ($offset_out + offset)}).items;
						offset = offset + 1000;
		  			};
		  			return members;
		  		";
				$result = json_decode(vk_query('https://api.vk.com/method/execute', array(
					'access_token' => TOKEN,
					'code' => "$query",
					'v' => '5.37'
				)), true);

				$follower_all = array_merge($follower_all, $result['response']);

				$progress = round((($i+1)/$followers_count)*100,2);
				$time_step = time() - $time_stamp;
				$time_all = $time_step*($followers_count-($i+1));

		        $time_str = date('H:i:s', mktime(0, 0, $time_all));
		        if ($progress > 100) $progress = 100;
				file_put_contents($f_progress, 'сбор подписчиков: '.$progress);
			}
		}
	}
	echo '<script>$("#progress").hide();</script>';
?>
<section class="content">
	<div class="row">
		<div class="col-md-12">
            	<div class="box">
                <div class="box-header with-border">
                  <h3 class="box-title">Результаты:</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
				  <div style="margin-bottom: 10px;" align="right"><a href="/export.php?f=<?=md5($_SESSION['user']['id'].'Jnsb2156')?>">Экспорт ID</a></div>
                  <table id="ca_data" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Тип пользователя</th>
        </tr>
    </thead>
	<tbody>
<?
	$uids_data = null;
	$save = false;
	if (sizeof($u_fr) > 0)
	foreach ($u_fr as $uids){		$save = true;
		echo '<tr><td>'.$uids.'</td><td>друг</td></tr>';
		$uids_data[] = $uids."\r\n";
	}
	if (sizeof($follower_all) > 0)
	foreach ($follower_all as $uids){		$save = true;
		echo '<tr><td>'.$uids.'</td><td>подписчик</td></tr>';
		$uids_data[] = $uids."\r\n";
	}

	if (sizeof($uids_data) > 0)
	$uids_data_str = implode('', array_unique($uids_data));
?>
		</div><!-- /.box-body -->
	</div><!-- /.box -->
</div><!-- /.box-body -->
</div><!-- /.box -->
</section>
<?
	if (!empty($uids_data_str)){
		if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156')))
		mkdir($_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156'));
		$f_export = $_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156').'/export.txt';
		file_put_contents($f_export, $uids_data_str);
	}
	report_do('get_frend_follower', $save, $uids_data_str);
}
?>