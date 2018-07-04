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
	$_POST['group_id'] = !empty($_POST['group_id']) ? $_POST['group_id'] : 'https://vk.com/prinum';
	$date = isset($_POST['date']) ? $_POST['date'] : 0;

	$_POST['group_id'] = preg_replace('@(http|https)://vk.com/@', '', $_POST['group_id']);
	$gr_ids = explode("\n", $_POST['group_id']);
	$gr_count = sizeof($gr_ids);
	$i = 0;

	$query_count = ceil(ceil($gr_count / 1000) / 25);
	$users_part = array_chunk($gr_ids, 1000, true);
    $user_connections = array();

	$r_txt = array(
	    1 => 'не женат/не замужем',
    	2 => 'есть друг/есть подруга',
	    3 => 'помолвлен/помолвлена',
    	4 => 'женат/замужем',
	    5 => 'всё сложно',
	    6 => 'в активном поиске',
	    7 => 'влюблён/влюблена',
	    0 => 'не указано'
	    );

	foreach ($users_part as $user_ids_1000){
		$user_ids_str = implode(',', $user_ids_1000);
		$vk_result_id = json_decode(vk_query('https://api.vk.com/method/users.get', array(
			'user_ids' => $user_ids_str,
			'fields' => 'relation',
   			'access_token' => TOKEN,
			'v' => '5.37'
		)), true);

	    if (!isset($vk_result_id['response'])) continue;

		if (sizeof($vk_result_id['response']) > 0){
			foreach ($vk_result_id['response'] as $user){
				if (!empty($user['relation_partner'])){        	   		$user_connections[$user['id']] = $user['relation_partner'];
           			$user_connections[$user['id']]['relation'] = $r_txt[$user['relation']];
				}
			}
		}

		$progress = round((($i+1)/$query_count)*100,2);
        if ($progress > 100) $progress = 100;
		file_put_contents($f_progress, 'анализ пользователей: '.$progress);

		$i++;
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
<table id="ca_data" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Тип связи</th>
            <th>ID-пары</th>
            <th>ФИО</th>
        </tr>
    </thead>
	<tbody>
<?
	if (isset($user_connections)){		if (sizeof($user_connections) > 0){
			foreach ($user_connections as $uid => $data){
				echo '<tr><td>'.$uid.'</td>';
				echo '<td>'.$data['id'].'</td>';
				echo '<td>'.$data['relation'].'</td>';
				echo '<td><a target="_blank" href="https://vk.com/id'.$data['id'].'">'.$data['first_name'].' '.$data['last_name'].'</a></td>';
				echo '</tr>';
			}
			$save = true;
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
	report_do('user_pary', $save);
}
?>