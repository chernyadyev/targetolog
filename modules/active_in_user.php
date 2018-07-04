<?
header('Content-Type: text/html; charset=UTF-8');

session_start();
set_time_limit(0);

if (!empty($_SESSION['token']))
	define('TOKEN', $_SESSION['token']);
else die('Необходима авторизация.');

require_once($_SERVER['DOCUMENT_ROOT'].'/core/vk.api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/core/error.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/mysql.inc.php');
ob_start();

if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/progress/'.$_SESSION['user']['id']))
	mkdir($_SERVER['DOCUMENT_ROOT'].'/progress/'.$_SESSION['user']['id']);

$f_progress = $_SERVER['DOCUMENT_ROOT'].'/progress/'.$_SESSION['user']['id'].'/fp.dat';

function no_gr($var){
	if ($var > 0) return 1;
		else return 0;
}
if ($_POST['do'] == 'get_active'){

	$ch_like = ($_POST['ch_like'] == 'true') ? 1 : 0;
	$ch_repost = ($_POST['ch_repost'] == 'true') ? 1 : 0;
	$ch_comment = ($_POST['ch_comment'] == 'true') ? 1 : 0;
	$date = !empty($_POST['date']) ? $_POST['date'] : 0;

	if ($ch_like == 0 && $ch_repost == 0 && $ch_comment == 0){
    	show_error('<b>Ошибка:</b> не выбраны типы активностей!');
	}

	$_POST['group_id'] = !empty($_POST['group_id']) ? $_POST['group_id'] : 'https://vk.com/prinum';
	$date = isset($_POST['date']) ? $_POST['date'] : 0;

	$gr_ids = explode("\n", $_POST['group_id']);
	$gr_count = sizeof($gr_ids);
	$j = 1;

	$users_ids['like'] = $users_ids['repost'] = $users_ids['comments'] = array();

	foreach ($gr_ids as $gr_url){
		$user_id = preg_replace('@(http|https)://vk.com/@', '', $gr_url);
		$vk_result_id = json_decode(vk_query('https://api.vk.com/method/users.get', array(
			'user_ids' => $user_id,
			'fields' => 'sex, bdate, city, country, photo_100, last_seen, counters',
   			'access_token' => TOKEN,
			'v' => '5.37'
		)),true);

		if (!isset($vk_result_id['response'])){
			continue;
		}

		if (!isset($vk_result_id['response'][0])){
			continue;
		}

	    $user_arr = $vk_result_id['response'][0];
	    $user_id = $user_arr['id'];

		usleep(750000);

		$query = '
			var wall = API.wall.get({"owner_id": '.$user_id.', "offset": 0, "count": 100, "filter": "owner"});
			var wall_items_count = wall.count;
			var wall_post_id = wall.items@.id;
			var wall_post_date = wall.items@.date;

			var offset = 100;
			while (offset < 2500 && (offset < wall_items_count)){
				wall = API.wall.get({"owner_id": '.$user_id.', "offset": offset, "count": 100, "filter": "owner"});
				wall_post_id = wall_post_id + wall.items@.id;
				wall_post_date = wall_post_date + wall.items@.date;

				offset = offset + 100;
			}

			return {"ids":wall_post_id, "dates":wall_post_date};';

		$result = json_decode(vk_query('https://api.vk.com/method/execute', array(
			'access_token' => TOKEN,
			'code' => "$query",
			'v' => '5.37'
		)), true);

		usleep(500000);

		file_put_contents($f_progress, 'получение постов...');

		$post_arr = array();

		$date_f = strtotime($date.'00:00');

		$i = 1;
		if (isset($result['response']))
		foreach ($result['response']['ids'] as $k => $id){
            if ($date_f < ($result['response']['dates'][$k]-3600)){
				$post_arr[] = $id;
			}
			$i++;
		}

		$query_count = sizeof($post_arr);
		$i = 0;
		foreach ($post_arr as $p_id){
			$time_stamp = time();
			if ($ch_like == 1){
				$query = '
					var like = API.likes.getList({"owner_id":'.$user_id.',"item_id":'.$p_id.',"offset":0,"filter":"likes","count":1000,"type":"post"});
					var i = 0;
					var count = like.count;
					var offset = 1000;
					var user_ids = [];

					if (count > 0){
						user_ids = like.items;
						while (i < 25 && offset < count){
							user_ids = user_ids + API.likes.getList({"owner_id":'.$user_id.',"item_id":'.$p_id.',"offset":offset,"filter":"likes","count":1000,"type":"post"}).items;
							i = i + 1;
							offset = offset + 1000;
						}
					}
					return user_ids;';
				$result = json_decode(vk_query('https://api.vk.com/method/execute', array(
					'access_token' => TOKEN,
					'code' => "$query",
					'v' => '5.37'
				)), true);
				if (isset($result['response']))
					$users_ids['like'] = array_merge($users_ids['like'], $result['response']);
           }
			if ($ch_repost == 1){
				$query = '
					var like = API.likes.getList({"owner_id":'.$user_id.',"item_id":'.$p_id.',"offset":0,"filter":"copies","count":1000,"type":"post"});
					var i = 0;
					var count = like.count;
					var offset = 1000;
					var user_ids = [];

	                if (count > 0){
						user_ids = like.items;
						while (i < 25 && offset < count){
							user_ids = user_ids + API.likes.getList({"owner_id":'.$user_id.',"item_id":'.$p_id.',"offset":offset,"filter":"copies","count":1000,"type":"post"}).items;
							i = i + 1;
							offset = offset + 1000;
						}
					}
					return user_ids;';
				$result = json_decode(vk_query('https://api.vk.com/method/execute', array(
					'access_token' => TOKEN,
					'code' => "$query",
					'v' => '5.37'
				)), true);
				if (isset($result['response']))
					$users_ids['repost'] = array_merge($users_ids['repost'], $result['response']);
            }
			if ($ch_comment == 1){
				$query = '
					var comment = API.wall.getComments({"post_id":'.$p_id.',"offset":0,"owner_id":'.$user_id.',"count":100});
					var i = 0;
					var count = comment.count;
					var user_ids;
					var offset = 100;

					if (count > 0) {
						user_ids = comment.items@.from_id;
						while (i < 25 && offset < count){
							user_ids = user_ids + API.wall.getComments({"post_id":'.$p_id.',"offset":offset,"owner_id":'.$user_id.',"count":100}).items@.from_id;
							i = i + 1;
							offset = offset + 100;
						}
					}
					return user_ids;';
				$result = json_decode(vk_query('https://api.vk.com/method/execute', array(
					'access_token' => TOKEN,
					'code' => "$query",
					'v' => '5.37'
				)), true);
				if (isset($result['response'])){
					$result['response'] = array_filter($result['response'], "no_gr");
					$users_ids['comments'] = array_merge($users_ids['comments'], $result['response']);
				}
			}

			$progress = round((($i+1)/$query_count)*100,2);
	        if ($progress > 100) $progress = 100;
	   		file_put_contents($f_progress, 'пользователь: '.$j.' из '.$gr_count.', сбор активностей: '.$progress);
			$i++;
			usleep(500000);
		}
		$j++;
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
            <th>Тип активности</th>
        </tr>
    </thead>
	<tbody>
<?
	$save = false;
	$uids_data = null;
	$uids_data_str = null;

	if (sizeof($users_ids['comments']) > 0 || sizeof($users_ids['repost']) > 0 || sizeof($users_ids['like']) > 0){
		foreach ($users_ids as $type => $data){
			foreach ($data as $uids){
				$save = true;
				echo '<tr><td>'.$uids.'</td><td>'.$type.'</td></tr>';
				$uids_data[] = $uids."\r";
			}
		}
		$uids_data_str = implode('', array_unique($uids_data));
	}
?>
	</tbody>
	</table>

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
	report_do('active_in_user', $save, $uids_data_str);
}
?>