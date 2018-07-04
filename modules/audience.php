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

	$top_ca = new Judy(Judy::INT_TO_MIXED);

	if ($_POST['do'] == 'get_p'){
		$min_p = !empty((int)$_POST['min_p']) ? (int)$_POST['min_p'] : 1;
		$max_p = !empty((int)$_POST['max_p']) ? (int)$_POST['max_p'] : 999999;

		if ($min_p < 1) $min_p = 1;

		$_POST['group_id'] = !empty($_POST['group_id']) ? $_POST['group_id'] : 'https://vk.com/roomstory';
		$gr_ids = explode("\n", $_POST['group_id']);

		$gr_count = sizeof($gr_ids);

		$j = 1;
		$members_all = array();

		foreach ($gr_ids as $group_id){
			$time_stamp = time();
			$group_id = preg_replace('@(http|https)://vk.com/@', '', $group_id);
			$group_id = preg_replace('@public@', '', $group_id);

			$result = json_decode(vk_query('https://api.vk.com/method/groups.getById', array(
				'group_ids' => $group_id,
				'fields' => 'members_count',
				'access_token' => TOKEN
			)), true);

			if ($gr_count > 1){
            	if (!isset($result['response']))
            		continue;
			} elseif ($gr_count == 1) {
            	if (!isset($result['response'])) show_error('<b>Ошибка:</b> группа или группы не найдены!');
			}

			$group_vk_array = $result['response'][0];

			$group_vk_id = $group_vk_array['gid'];

			if (!isset($group_vk_array['members_count']))
				continue;
			$group_vk_count = $group_vk_array['members_count'];

			$query_count = ceil(ceil($group_vk_count / 1000) / 25);

			for ($i=0; $i<$query_count; $i++){
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

				$progress = round(($i/$query_count)*100,2);
		        if ($progress > 100) $progress = 100;
				file_put_contents($f_progress, 'сбор подписчиков: '.$progress);
				usleep(500000);

				if (isset($result['response']) && $top_ca->count() < 1500000){
					foreach ($result['response'] as $uid){
						if (isset($top_ca[$uid]))
							$top_ca[$uid] = $top_ca[$uid] + 1;
						else
							$top_ca[$uid] = 1;
					}
				}
			}

				$progress = round(($j/$gr_count)*100,2);
		        if ($progress > 100) $progress = 100;
				file_put_contents($f_progress, 'анализ групп: '.$progress);
				$j++;
				usleep(250000);
		}
        $p_ids = null;

		if ($top_ca->count() > 0){
			if ($min_p > 1){
				foreach ($top_ca as $gr_id => $c){
					if ($c >= $min_p && $c < $max_p){
						$top_ca_o[$gr_id] = $c;
						$p_ids .= $gr_id."\r\n";
					}
				}
				if (isset($top_ca_o)) arsort($top_ca_o);
			} else {
				foreach ($top_ca as $gr_id => $c){
					$p_ids .= $gr_id."\r\n";
				}
			}
		}
		echo '<script>$("#progress").hide();</script>';
		$save = false;
?>
<section class="content">
	<div class="row">
		<div class="col-md-12">
            	<div class="box">
                <div class="box-header with-border">
                  <h3 class="box-title">Результаты:</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
<?if ($min_p == 1){
	$save = true;
?>
	<div align="center">Найдено <?=sizeof($top_ca)?> подписчиков</div>
	<div style="margin-bottom: 10px;" align="center"><a href="/export.php?f=<?=md5($_SESSION['user']['id'].'Jnsb2156')?>">Сохранить результат</a></div>
<?} else {
	if (isset($top_ca_o)){
?>
<div style="margin-bottom: 10px;" align="right"><a href="/export.php?f=<?=md5($_SESSION['user']['id'].'Jnsb2156')?>">Экспорт ID</a></div>
  <table id="ca_data" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID пользователя</th>
            <th>Входит в группы</th>
        </tr>
    </thead>
	<tbody>
<?
	$i = 0;
	foreach ($top_ca_o as $pid => $p_count){
		$save = true;
		if ($i < 3000){
?>
		<tr><td><a target="_blank" href="http://vk.com/id<?=$pid?>">id<?=$pid?></a></td><td><?=$p_count?></td></tr>
<?
		}
		$i++;
	}
?>
	</tbody>
	</table>
	<?}else{?>
		<div align="center">Найдено 0 подписчиков</div>
	<?}?>
<?}?>
		</div><!-- /.box-body -->
	</div><!-- /.box -->
</div><!-- /.box-body -->
</div><!-- /.box -->
</section>
<?
	if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156')))
	mkdir($_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156'));
	$f_export = $_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156').'/export.txt';
	file_put_contents($f_export, $p_ids);
	}
	report_do('audience', $save, $p_ids);
?>