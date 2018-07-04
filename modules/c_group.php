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

if ($_POST['do'] == 'get_ca'){
	$gr_url = !empty($_POST['gr_url']) ? $_POST['gr_url'] : 'https://vk.com/roomstory';
	$max_p = !empty((int)$_POST['max_p']) ? (int)$_POST['max_p'] : 30000;
	$min_p = !empty((int)$_POST['min_p']) ? (int)$_POST['min_p'] : 100;
	$show_r = !empty((int)$_POST['show_r']) ? (int)$_POST['show_r'] : 500;
	if ($show_r > 5000) $show_r = 5000;

	$min_ca = !empty($_POST['min_ca']) ? (int)$_POST['min_ca'] : 5;
	if ($min_ca < 3) $min_ca = 3;

	$group_id = preg_replace('@(http|https)://vk.com/@', '', $gr_url);
	$group_id = preg_replace('@public@', '', $group_id);

	$result = json_decode(vk_query('https://api.vk.com/method/groups.getById', array(
		'group_id' => $group_id,
		'fields' => 'members_count',
		'access_token' => TOKEN
	)), true);

	if (!isset($result['response'])) show_error('<b>Ошибка:</b> группа или группы не найдены!');
	$group_vk_array = $result['response'][0];

	$group_vk_id = $group_vk_array['gid'];

	if (!isset($group_vk_array['members_count']))
		show_error('<b>Ошибка:</b> размер группы получить неудалось!');

	$group_vk_count = $group_vk_array['members_count'];

	if ($group_vk_count > 50000) show_error('<b>Ошибка:</b> размер группы не должен превышать 50000 подписчиков!');

	$query_count = ceil(ceil($group_vk_count / 1000) / 25);
	$members_all = new Judy(Judy::INT_TO_MIXED);

	for ($i=0; $i<$query_count; $i++){
		$time_stamp = time();

		$offset_out = $i*25000;
  		$query = "
  			var members = API.groups.getMembers({\"group_id\": $group_vk_id, \"count\":1000, \"offset\": $offset_out}).items;
  			var offset = 1000;
  			while (offset < 25000 && (offset+$offset_out < $group_vk_count)){
				members = members + API.groups.getMembers({\"group_id\": $group_vk_id, \"count\":1000, \"offset\": ($offset_out + offset)}).items;
				offset = offset + 1000;
  			};
  			return members;
  		";
		$result = json_decode(vk_query('https://api.vk.com/method/execute', array(
			'access_token' => TOKEN,
			'code' => "$query",
			'v' => '5.37'
		)), true);

		if (isset($result['response'])){
			if (is_array($result['response']))
				foreach ($result['response'] as $uid){
					$members_all[] = $uid;
				}
		}

		$progress = round((($i+1)/$query_count)*100,2);
        if ($progress > 100) $progress = 100;
		file_put_contents($f_progress, 'сбор подписчиков: '.$progress);
	}

	$i = $j = 0;
   	$u_ids = array();

	$gr_all = new Judy(Judy::INT_TO_MIXED);

	$j_all = ceil($members_all->count() / 25);

	foreach ($members_all as $u_id){
		if ($i >= 24){
        	$time_stamp = time();

        	$u_ids_str = implode(',', $u_ids);
			$query = '
				var user_ids = ['.$u_ids_str.'];
				var user_groups = [];

				var i = 0;
				while (i < user_ids.length){
					user_groups = user_groups + API.groups.get({"user_id": user_ids[i], "extended": "0", "count": "25"}).items;
					i = i + 1;
				}

				return user_groups;';

			$result = json_decode(vk_query('https://api.vk.com/method/execute', array(
				'access_token' => TOKEN,
				'code' => "$query",
				'v' => '5.37'
			)), true);

			usleep(250000);

			if (isset($result['response'])){
				if (!is_array($result['response'])){
					$result['response'] = explode(',', $result['response']);
				}
				if (is_array($result['response'])){
					foreach ($result['response'] as $g_id){
						if (!isset($gr_all[$g_id]))
							$gr_all[$g_id] = 1;
						else
							$gr_all[$g_id] = $gr_all[$g_id] + 1;
					}
				}
			}

        	$u_ids = array();
        	$i = 0;
			$j++;

			$progress = round(($j/$j_all)*100,2);

	        if ($progress > 100) $progress = 100;
			file_put_contents($f_progress, 'анализ подписчиков: '.$progress);
		}
		$u_ids[] = $u_id;
		$i++;
	}

	//echo 'Памяти использовано после анализа подписчиков: '.round(memory_get_usage()/1000000,2)."\n";

	if (!empty($u_ids)){
       	$u_ids_str = implode(',', $u_ids);
       	//user_groups = user_groups + API.users.getSubscriptions({"user_id": user_ids[i], "extended": "0"}).groups.items;
		$query = '
			var user_ids = ['.$u_ids_str.'];
			var user_groups = [];

			var i = 0;
			while (i < user_ids.length){
				user_groups = user_groups + API.groups.get({"user_id": user_ids[i], "extended": "0", "count": "25"}).items;
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

			foreach ($result['response'] as $g_id){
			if (!isset($gr_all[$g_id]))
				$gr_all[$g_id] = 1;
			else
				$gr_all[$g_id] = $gr_all[$g_id] + 1;
			}
		}
	}

	// сортироть массив!!!!!!!
    //arsort($gr_all);

	$member_array_ca = array();
	foreach ($gr_all as $gr_id => $c){
		if ($c >= $min_ca){
        	$member_array_ca[$gr_id] = $c;
		}
	}

	arsort($member_array_ca);

	$top_gr_ids = array_keys($member_array_ca);
	$gr_part = array_chunk($top_gr_ids, 500, true);
	$gr_all_info = array();


    if (sizeof($member_array_ca) > 0){
		$i = $j = 0;
		$j_all = ceil(sizeof($gr_part) / 10);
   		$gr_ids_25 = array();

   		foreach ($gr_part as $part){
    	$u_ids_str = implode(',', $part);
    	if ($i >= 9){
			$time_step = time();
			$u_ids_str_a = implode(',', $gr_ids_25);
			$query = '
				var user_ids = ['.$u_ids_str_a.'];
				var user_groups = [];

				var i = 0;
				while (i < user_ids.length){
					user_groups = user_groups + API.groups.getById({"group_ids": user_ids[i], "fields": "members_count"});
					i = i + 1;
				}

				return user_groups;';

			$result = json_decode(vk_query('https://api.vk.com/method/execute', array(
				'access_token' => TOKEN,
				'code' => "$query",
				'v' => '5.37'
			)), true);

			if (isset($result['response'])){
				if (is_array($result['response']))
					$gr_all_info = array_merge($gr_all_info, $result['response']);
			}

			$i = 0;
       		$gr_ids_25 = array();
       		$j++;

			$progress = round(($j/$j_all)*100,2);
            if ($progress > 100) $progress = 100;
			file_put_contents($f_progress, 'получение инф. о группах: '.$progress);
    	}
			$gr_ids_25[] = '['.$u_ids_str.']';
    		$i++;
		}
	}

	if (isset($gr_ids_25))
	if (sizeof($gr_ids_25) > 0){
		$u_ids_str_a = implode(',', $gr_ids_25);
		$query = '
			var user_ids = ['.$u_ids_str_a.'];
			var user_groups = [];

			var i = 0;
			while (i < user_ids.length){
				user_groups = user_groups + API.groups.getById({"group_ids": user_ids[i], "fields": "members_count"});
				i = i + 1;
			}

			return user_groups;';

		$result = json_decode(vk_query('https://api.vk.com/method/execute', array(
			'access_token' => TOKEN,
			'code' => "$query",
			'v' => '5.37'
		)), true);

		if (isset($result['response'])){
			if (is_array($result['response']))
				$gr_all_info = array_merge($gr_all_info, $result['response']);
		}

		file_put_contents($f_progress, 'получение инф. о группах: '.$progress);
	}

	$gr_print = array();
	if (is_array($gr_all_info)){
		foreach ($gr_all_info as $g){
			if (isset($g['members_count']))
	    	    if ($g['members_count'] >= $min_p && $g['members_count'] <= $max_p)
					$gr_print[] = $g;
		}

		if (sizeof($gr_print) > 0)
			$gr_print = array_slice($gr_print, 0, $show_r);
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
			          	<th>ID группы</th>
			          	<th>Имя</th>
			          	<th>Название</th>
			          	<th>Подписчиков</th>
			          	<th>ЦА (абс.)</th>
			          	<th>ЦА (%)</th>
                      </tr>
                    </thead>
	<tbody>
<?
	$gr_ids = null;
	foreach ($gr_print as $g){
		if ($g['members_count'] > 0)
			$p = round(($gr_all[$g['id']]/$g['members_count'])*100,2);
		else $p = 0;

		if (isset($gr_all[$g['id']])){
			echo '<tr><td>'.$g['id'].'</td><td><a target="_blank" href="https://vk.com/'.$g['screen_name'].'">'.$g['name'].'</a></td><td>'.$g['screen_name'].'</td><td>'.$g['members_count'].'</td><td>'.$gr_all[$g['id']].'</td><td>'.$p.'%</td></tr>';
			$gr_ids .= $g['screen_name']."\r\n";
		}
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
	if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156')))
		mkdir($_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156'));
	$f_export = $_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156').'/export.txt';
	file_put_contents($f_export, $gr_ids);

	if (!empty($gr_ids)) $save = true;
	else $save = false;
	report_do('c_group', $save, $gr_ids);
}
?>
