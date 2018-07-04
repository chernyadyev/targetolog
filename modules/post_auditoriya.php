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

	$ch_like = ($_POST['ch_like'] == 'true') ? 1 : 0;
	$ch_repost = ($_POST['ch_repost'] == 'true') ? 1 : 0;
	$ch_comment = ($_POST['ch_comment'] == 'true') ? 1 : 0;
	$date = !empty($_POST['date']) ? $_POST['date'] : 0;

	$_POST['post_id'] = !empty($_POST['post_id']) ? $_POST['post_id'] : 'https://vk.com/brutalengineer?w=wall-31969346_1306416';

	$post_ids = explode("\n", $_POST['post_id']);

	$query_count = sizeof($post_ids);
	$i = 0;

	$users_ids['like'] = $users_ids['repost'] = $users_ids['comments'] = array();

	foreach ($post_ids as $gr_url){
		preg_match('@wall-(.*)_(.*)@', $gr_url, $matches);
		if (!isset($matches[1])) continue;

		$group_vk_id = $matches[1];
		$p_id = $matches[2];

		$time_stamp = time();
		if ($ch_like == 1){
			$query = '
				var like = API.likes.getList({"owner_id":-'.$group_vk_id.',"item_id":'.$p_id.',"offset":0,"filter":"likes","count":1000,"type":"post"});
				var i = 0;
				var count = like.count;
				var offset = 1000;
				var user_ids = [];

				if (count > 0){
					user_ids = like.items;
					while (i < 25 && offset < count){
						user_ids = user_ids + API.likes.getList({"owner_id":-'.$group_vk_id.',"item_id":'.$p_id.',"offset":offset,"filter":"likes","count":1000,"type":"post"}).items;
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
				var like = API.likes.getList({"owner_id":-'.$group_vk_id.',"item_id":'.$p_id.',"offset":0,"filter":"copies","count":1000,"type":"post"});
				var i = 0;
				var count = like.count;
				var offset = 1000;
				var user_ids = [];

                if (count > 0){
					user_ids = like.items;
					while (i < 25 && offset < count){
						user_ids = user_ids + API.likes.getList({"owner_id":-'.$group_vk_id.',"item_id":'.$p_id.',"offset":offset,"filter":"copies","count":1000,"type":"post"}).items;
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
				var comment = API.wall.getComments({"post_id":'.$p_id.',"offset":0,"owner_id":-'.$group_vk_id.',"count":100});
				var i = 0;
				var count = comment.count;
				var user_ids;
				var offset = 100;

				if (count > 0) {
					user_ids = comment.items@.from_id;
					while (i < 25 && offset < count){
						user_ids = user_ids + API.wall.getComments({"post_id":'.$p_id.',"offset":offset,"owner_id":-'.$group_vk_id.',"count":100}).items@.from_id;
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
		$time_step = time() - $time_stamp;
		$time_all = $time_step*($query_count-($i+1));

        $time_str = date('H:i:s', mktime(0, 0, $time_all));
        if ($progress > 100) $progress = 100;
		file_put_contents($f_progress, 'сбор активностей: '.$progress);

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
	$uids_data = null;
	$save = false;
	if (sizeof($users_ids['comments']) > 0 || sizeof($users_ids['repost']) > 0 || sizeof($users_ids['like']) > 0){		$save = true;
		foreach ($users_ids as $type => $data){
			foreach ($data as $uids){
				echo '<tr><td>'.$uids.'</td><td>'.$type.'</td></tr>';
				$uids_data[] = $uids."\r\n";
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
	$uids_data_str = null;
	if (!empty($uids_data_str)){
		if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156')))
		mkdir($_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156'));
		$f_export = $_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156').'/export.txt';
		file_put_contents($f_export, $uids_data_str);
	}
	report_do('post_auditoriya', $save, $uids_data_str);
}
?>