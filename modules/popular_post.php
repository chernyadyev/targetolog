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
	$_POST['group_id'] = !empty($_POST['group_id']) ? $_POST['group_id'] : 'https://vk.com/roomstory';
	$date = isset($_POST['date']) ? $_POST['date'] : date("d.m.Y", time());;

	$gr_ids = explode("\n", $_POST['group_id']);
	$query_count = sizeof($gr_ids);
	$j = 1;

	$post_arr = array();

	foreach ($gr_ids as $gr_url){
		$group_id = preg_replace('@(http|https)://vk.com/@', '', $gr_url);
		$group_id = preg_replace('@public@', '', $group_id);

		$result = json_decode(vk_query('https://api.vk.com/method/groups.getById', array(
			'group_ids' => $group_id,
			'fields' => 'members_count',
			'access_token' => TOKEN
		)), true);

		if ($query_count > 1){
           	if (!isset($result['response']))
           		continue;
		} elseif ($query_count == 1) {
           	if (!isset($result['response'])) show_error('<b>Ошибка:</b> группа или группы не найдены!');
		}

		$group_vk_array = $result['response'][0];

		$group_vk_id = $group_vk_array['gid'];

		$query = '
			var wall = API.wall.get({"owner_id": -'.$group_vk_id.', "offset": 0, "count": 100, "filter": "owner"});
			var wall_items_count = wall.count;

			var wall_items = [];
			wall_items = wall.items;

			var offset = 100;
			while (offset < 2500 && (offset < wall_items_count)){
				wall_items = wall_items + API.wall.get({"owner_id": -'.$group_vk_id.', "offset": offset, "count": 100, "filter": "owner"}).items;
				offset = offset + 100;
			}

			return wall;';

		$result = json_decode(vk_query('https://api.vk.com/method/execute', array(
			'access_token' => TOKEN,
			'code' => "$query",
			'v' => '5.37'
		)), true);


		$date_f = strtotime($date.'00:00');

		$i = 1;
		if (isset($result['response']['items']))
		if (sizeof($result['response']['items']) > 0){
			foreach ($result['response']['items'] as $post_item){
	            if ($date_f < ($post_item['date']-3600)){
            		$post_arr[] = array('gr_id' => $group_vk_id, 'post_id' => $post_item['id'], 'like' => $post_item['likes']['count'], 'repost' => $post_item['reposts']['count'], 'comment' => $post_item['comments']['count']);
				}
				$i++;
			}
		}

		$progress = round(($j/$query_count)*100,2);
        if ($progress > 100) $progress = 100;
		file_put_contents($f_progress, 'сбор постов: '.$progress);

		$j++;
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
            <th>Лайки</th>
            <th>Репосты</th>
            <th>Комментарии</th>
        </tr>
    </thead>
	<tbody>
<?
	foreach ($post_arr as $post){
		$save = true;
		echo '<tr><td><a target="_blank" href="https://vk.com/wall-'.$post['gr_id'].'_'.$post['post_id'].'">'.$post['gr_id'].'-'.$post['post_id'].'</a></td><td>'.$post['like'].'</td><td>'.$post['repost'].'</td><td>'.$post['comment'].'</td></tr>';
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
	report_do('popular_post', $save);
}
?>