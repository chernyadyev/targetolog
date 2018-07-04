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

	if ($_POST['do'] == 'get_p'){
		$_POST['group_id'] = !empty($_POST['group_id']) ? $_POST['group_id'] : 'https://vk.com/roomstory';
		$_POST['group_id'] = preg_replace('@(http|https)://vk.com/@', '', $_POST['group_id']);
		$_POST['group_id'] = preg_replace('@public@', '', $_POST['group_id']);

		$gr_ids = explode("\n", $_POST['group_id']);
		$gr_count = sizeof($gr_ids);
		$j = 0;

		$query_count = ceil(ceil($gr_count / 500) / 25);
		$users_part = array_chunk($gr_ids, 500, true);
    	$gr_info = array();

		foreach ($users_part as $user_ids_500){
			$user_ids_str = implode(',', $user_ids_500);
			$vk_result_id = json_decode(vk_query('https://api.vk.com/method/groups.getById', array(
				'group_ids' => $user_ids_str,
				'fields' => 'contacts',
	   			'access_token' => TOKEN,
				'v' => '5.37'
			)), true);

		if ($query_count > 1){
           	if (!isset($vk_result_id['response']))
           		continue;
		} elseif ($query_count == 1) {
           	if (!isset($vk_result_id['response'])) show_error('<b>Ошибка:</b> группа или группы не найдены!');
		}

		if (sizeof($vk_result_id['response']) > 0){
			foreach ($vk_result_id['response'] as $gr){				if (!empty($gr['contacts'])){					$gr_info[$gr['screen_name']] = '';

					foreach ($gr['contacts'] as $cont_desc){
						if (!empty($cont_desc['user_id'])){
							if (!empty($cont_desc['desc']))
								$d = ' ('.$cont_desc['desc'].')';
							else $d = '';
							$gr_info[$gr['screen_name']]['user_id'][] = '<a target="_blank" href="https://vk.com/id'.$cont_desc['user_id'].'">'.$cont_desc['user_id'].'</a>'.$d;
							$gr_info[$gr['screen_name']]['uid'][] = $cont_desc['user_id'];
						} else if (!empty($cont_desc['desc']))
							$gr_info[$gr['screen_name']]['desc'][] = $cont_desc['desc'];
						if (!empty($cont_desc['phone']))
							$gr_info[$gr['screen_name']]['phone'][] = $cont_desc['phone'];
						if (!empty($cont_desc['email']))
							$gr_info[$gr['screen_name']]['email'][] = $cont_desc['email'];
					}
				}
			}
		}

			$progress = round(($j/$gr_count)*100,2);
		    if ($progress > 100) $progress = 100;
			file_put_contents($f_progress, 'анализ групп: '.$progress);
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
            <th>ID группы</th>
            <th>Пользователи</th>
            <th>E-mail</th>
            <th>Телефоны</th>
            <th>Описание</th>
        </tr>
    </thead>
	<tbody>
<?
	$save = false; $all_id = null;
	foreach ($gr_info as $gid => $desc){		$save = true;
		foreach ($desc['uid'] as $uid)
			$all_id[] = $uid."\r\n";
?>
		<tr>
			<td><a target="_blank" href="http://vk.com/<?=$gid?>"><?=$gid?></a></td>
			<td><?if (!(empty($desc['user_id']))) echo implode('<br />', $desc['user_id'])?></td>
			<td><?if (!(empty($desc['email']))) echo implode('<br />', $desc['email'])?></td>
			<td><?if (!(empty($desc['phone']))) echo implode('<br />', $desc['phone'])?></td>
			<td><?if (!(empty($desc['desc']))) echo implode('<br />', $desc['desc'])?></td>
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
	if (sizeof($all_id) > 0){
		if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156')))
		mkdir($_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156'));
		$f_export = $_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156').'/export.txt';
		$members_all_str = implode("\r\n", $all_id);
		file_put_contents($f_export, $members_all_str);
		$save = true;
	}
	report_do('adm_contact', $save, $members_all_str);
	}
?>