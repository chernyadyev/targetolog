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

if ($_POST['do'] == 'get_f'){
	global $type_f;

	$gr_url = !empty($_POST['gr_url']) ? $_POST['gr_url'] : 'https://vk.com/roomstory';
	$type_f = !empty($_POST['type_f']) ? (int)$_POST['type_f'] : 0;
	$group_id = preg_replace('@(http|https)://vk.com/@', '', $gr_url);
	$max_p = !empty((int)$_POST['max_p']) ? (int)$_POST['max_p'] : 500;

	$result = json_decode(vk_query('https://api.vk.com/method/groups.getById', array(
		'group_id' => $group_id,
		'fields' => 'members_count',
		'access_token' => TOKEN
	)), true);

	if (!isset($result['response'])) show_error('<b>Ошибка:</b> группа или группы не найдены!');
	$group_vk_array = $result['response'][0];

	$group_vk_id = $group_vk_array['gid'];
	$group_vk_count = $group_vk_array['members_count'];

	$query_count = ceil(ceil($group_vk_count / 1000) / 25);
	$members_all = array();

	for ($i=0; $i<$query_count; $i++){
		$time_stamp = time();

		$offset_out = $i*25000;
  		$query = "
  			var members = API.groups.getMembers({\"group_id\": $group_vk_id, \"count\":1000, \"fields\":\"followers_count\", \"offset\": $offset_out});
  			var offset = 1000;
  			while (offset < 25000 && (offset+$offset_out < $group_vk_count)){
				members.push(API.groups.getMembers({\"group_id\": $group_vk_id, \"count\":1000, \"fields\":\"followers_count\",  \"offset\": ($offset_out + offset)}));
				offset = offset + 1000;
  			};
  			return members;
  		";

		$result = json_decode(vk_query('https://api.vk.com/method/execute', array(
			'access_token' => TOKEN,
			'code' => "$query",
			'v' => '5.37'
		)), true);

		if (isset($result['response']['items']))
		foreach ($result['response']['items'] as $u_d){
			if (!isset($u_d['deactivated'])){
				$members_all[$u_d['id']]['frends_count'] = 0;
				$members_all[$u_d['id']]['followers_count'] = 0;
				$members_all[$u_d['id']]['ca_frends_count'] = 0;
				$members_all[$u_d['id']]['ca_follower_count'] = 0;

	        	$members_all[$u_d['id']] = array('name' => $u_d['first_name'].' '.$u_d['last_name'], 'followers_count' => $u_d['followers_count']);
	      	}
		}

		$progress = round((($i+1)/$query_count)*100,2);
		$time_step = time() - $time_stamp;
		$time_all = $time_step*($query_count-($i+1));

        $time_str = date('H:i:s', mktime(0, 0, $time_all));
        if ($progress > 100) $progress = 100;
		file_put_contents($f_progress, 'сбор подписчиков: '.$progress);
	}

	$all_members_id = array_keys($members_all);
	$j_all = ceil(sizeof($members_all) / 25);

	// Получить друзей ЦА
	if ($type_f == 0 || $type_f == 2){
		$i = $j = 0;
		foreach ($members_all as $user_id => $user){
			$time_stamp = time();
			if ($i >= 24){
                $user_ids_str = implode(',', $user_ids);
                $query = '
                	var user_ids = ['.$user_ids_str.'];
                	var i = 0;
                	var frends = [];
                	var frend = [];
                	var frend_count = 0;

                	while (i < user_ids.length){
                		frend = API.friends.get({"user_id": user_ids[i]}).items;
                		frend_count = frend.length;
                		frends.push({"user_id": user_ids[i], "frends_count":frend_count, "frends": frend});
	                	i = i + 1;
                	}

                	return frends;';

				$result = json_decode(vk_query('https://api.vk.com/method/execute', array(
					'access_token' => TOKEN,
					'code' => "$query",
					'v' => '5.37'
				)), true);

				if (isset($result['response']))
				foreach ($result['response'] as $u_fr){
                	$members_all[$u_fr['user_id']]['frends_count'] = $u_fr['frends_count'];
                	if (is_array($u_fr['frends'])){
	                	$fr_ca = sizeof(array_intersect($u_fr['frends'], $all_members_id));
	                	$members_all[$u_fr['user_id']]['ca_frends_count'] = $fr_ca;
	              	}
				}

            	$i = 0;
            	$user_ids = array();

				$j++;
				$progress = round(($j/$j_all)*100,2);
				$time_step = time() - $time_stamp;
				$time_all = $time_step*($j_all-($j+1));

		        $time_str = date('H:i:s', mktime(0, 0, $time_all));

		        if ($progress > 100) $progress = 100;
				file_put_contents($f_progress, 'получение друзей: '.$progress);
			}
			$user_ids[] = $user_id;
			$i++;
		}
	}

	if ($type_f == 1 || $type_f == 2){
		$i = $j = 0;
		$f_all = array();

		foreach ($members_all as $user_id => $user){
			$time_stamp = time();
			if ($i >= 24){
                $user_ids_str = implode(',', $user_ids);

				$query = '
					var user_ids = ['.$user_ids_str.'];
					var user_groups = "";

					var i = 0;
					while (i < user_ids.length){
						user_groups = user_groups + API.users.getSubscriptions({"user_id": user_ids[i], "extended": "0"}).users.items;
						i = i + 1;
					}

					return user_groups;';

				$result = json_decode(vk_query('https://api.vk.com/method/execute', array(
					'access_token' => TOKEN,
					'code' => "$query",
					'v' => '5.37'
				)), true);

				if (isset($result['response'])){
					if (!is_array($result['response']))
						$result['response'] = explode(',', $result['response']);

					$f_all = array_merge($f_all, $result['response']);
				}

            	$i = 0;
            	$user_ids = array();

				$j++;
				$progress = round(($j/$j_all)*100,2);
				$time_step = time() - $time_stamp;
				$time_all = $time_step*($j_all-($j+1));

		        $time_str = date('H:i:s', mktime(0, 0, $time_all));

		        if ($progress > 100) $progress = 100;
				file_put_contents($f_progress, 'получение подписчиков: '.$progress);
			}
			$user_ids[] = $user_id;
			$i++;
		}

		$f_all_ca = array_intersect($f_all, $all_members_id);
		$top_ca_f = array_count_values($f_all_ca);

	    foreach ($top_ca_f as $uid => $count){
        	$members_all[$uid]['ca_follower_count'] = $count;
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
                  <table id="ca_data" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Имя</th>
            <th>Друзей</th>
            <th>Подписчиков</th>
            <th>Друзей ЦА</th>
            <th>Подписчиков ЦА</th>
        </tr>
    </thead>
	<tbody>
<?
	function cmp($a, $b) {
		global $type_f;

		if ($type_f == 0 || $type_f == 2){
			$a = isset($a["ca_frends_count"]) ? $a["ca_frends_count"] : 0;
			$b = isset($b["ca_frends_count"]) ? $b["ca_frends_count"] : 0;
		}
		else {
			$a = isset($a["ca_follower_count"]) ? $a["ca_follower_count"] : 0;
			$b = isset($b["ca_follower_count"]) ? $b["ca_follower_count"] : 0;
		}

	    if ($a == $b) {
    	    return 0;
    	}
	    return ($a > $b) ? -1 : 1;
	}

	uasort($members_all, "cmp");

	$members_all = array_slice($members_all, 0, $max_p, true);
	$save = false;

	foreach ($members_all as $uid => $u_data){
	$save = true;
?>
	<tr>
		<td><?=$uid?></td>
		<td><a target="_blank" href="https://vk.com/id<?=$uid?>"><?=$u_data['name']?></a></td>
		<td><?=(!empty($u_data['frends_count'])) ? $u_data['frends_count'] : 0;?></td>
		<td><?=(!empty($u_data['followers_count'])) ? $u_data['followers_count'] : 0;?></td>
		<td><?=(!empty($u_data['ca_frends_count'])) ? $u_data['ca_frends_count'] : 0;?></td>
		<td><?=(!empty($u_data['ca_follower_count'])) ? $u_data['ca_follower_count'] : 0;?></td>
	</tr>
<?
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
	report_do('best_p', $save);
}
?>