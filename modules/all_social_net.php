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

function no_gr($var){
	if ($var > 0) return 1;
		else return 0;
}

if ($_POST['do'] == 'get_active'){

	$ch_skype = ($_POST['skype'] == 'true') ? 1 : 0;
	$ch_facebook = ($_POST['facebook'] == 'true') ? 1 : 0;
	$ch_twitter = ($_POST['twitter'] == 'true') ? 1 : 0;
	$ch_livejournal = ($_POST['livejournal'] == 'true') ? 1 : 0;
	$ch_instagram = ($_POST['instagram'] == 'true') ? 1 : 0;

	$_POST['group_id'] = !empty($_POST['group_id']) ? $_POST['group_id'] : 'https://vk.com/prinum';
	$_POST['group_id'] = preg_replace('@(http|https)://vk.com/@', '', $_POST['group_id']);
	$gr_ids = explode("\n", $_POST['group_id']);
	$gr_count = sizeof($gr_ids);
	$i = 0;

	$query_count = ceil(ceil($gr_count / 1000) / 25);
	$users_part = array_chunk($gr_ids, 1000, true);
    $user_connections = array();

	foreach ($users_part as $user_ids_1000){		$user_ids_str = implode(',', $user_ids_1000);
		$vk_result_id = json_decode(vk_query('https://api.vk.com/method/users.get', array(
			'user_ids' => $user_ids_str,
			'fields' => 'connections',
   			'access_token' => TOKEN,
			'v' => '5.37'
		)), true);

		if (isset($vk_result_id['response']))
		if (sizeof($vk_result_id['response']) > 0){
			foreach ($vk_result_id['response'] as $user){
				if (isset($user['skype']) && $ch_skype)					$user_connections[$user['id']]['skype'] = $user['skype'];
				if (isset($user['facebook']) && $ch_facebook)
					$user_connections[$user['id']]['facebook'] = $user['facebook'];
				if (isset($user['twitter']) && $ch_twitter)
					$user_connections[$user['id']]['twitter'] = $user['twitter'];
				if (isset($user['livejournal']) && $ch_livejournal)
					$user_connections[$user['id']]['livejournal'] = $user['livejournal'];
				if (isset($user['instagram']) && $ch_instagram)
					$user_connections[$user['id']]['instagram'] = $user['instagram'];
			}
		}

		$progress = round((($i+1)/$query_count)*100,2);
        if ($progress > 100) $progress = 100;
		file_put_contents($f_progress, 'анализ пользователей: '.$progress);
		$i++;
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
            <th>skype</th>
            <th>facebook</th>
            <th>twitter</th>
            <th>livejournal</th>
            <th>instagram</th>
        </tr>
    </thead>
	<tbody>
<?
	$save = false;
	foreach ($user_connections as $uid => $data){		$save = true;
		echo '<tr><td>'.$uid.'</td>';
		echo '<td>'.(isset($data['skype']) ? $data['skype'] : '').'</td>';
		echo '<td>'.(isset($data['facebook']) ? $data['facebook'] : '').'</td>';
		echo '<td>'.(isset($data['twitter']) ? $data['twitter'] : '').'</td>';
		echo '<td>'.(isset($data['livejournal']) ? $data['livejournal'] : '').'</td>';
		echo '<td>'.(isset($data['instagram']) ? $data['instagram'] : '').'</td>';
		echo '</tr>';
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
	report_do('all_social_net', $save);
}
?>