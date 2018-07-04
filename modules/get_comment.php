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

if ($_POST['do'] == 'get_comment'){
	$user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : 'https://vk.com/prinum';
	$gr_url = !empty($_POST['gr_id']) ? $_POST['gr_id'] : 'https://vk.com/lifenews_ru';
    $date_min = isset($_POST['date_s']) ? strtotime($_POST['date_s']) : 0;
    $date_max = isset($_POST['date_f']) ? strtotime($_POST['date_f']) : 0;

	$user_id = preg_replace('@(http|https)://vk.com/@', '', $user_id);

	$group_id = preg_replace('@(http|https)://vk.com/@', '', $gr_url);
	$group_id = preg_replace('@public@', '', $group_id);

	$vk_result_id = json_decode(vk_query('https://api.vk.com/method/users.get', array(
		'user_ids' => $user_id,
		'fields' => 'followers_count',
		'access_token' => TOKEN,
		'v' => '5.37'
	)), true);

    if (!isset($vk_result_id['response'][0])) show_error('<b>Ошибка:</b> пользователь не найден!');
    $user_id = $vk_result_id['response'][0]['id'];

	$result = json_decode(vk_query('https://api.vk.com/method/groups.getById', array(
		'group_ids' => $group_id,
		'fields' => 'members_count',
		'access_token' => TOKEN
	)), true);

	if (!isset($result['response'])) show_error('<b>Ошибка:</b> группа или группы не найдены!');

	$group_vk_array = $result['response'][0];
	$group_vk_id = $group_vk_array['gid'];

	$query = '
		var wall = API.wall.get({"owner_id": -'.$group_vk_id.', "offset": 0, "count": 100, "filter": "owner"});
		var wall_items_count = wall.count;
		var wall_post_id = wall.items@.id;
		var wall_post_date = wall.items@.date;

		var offset = 100;
		while (offset < 2500 && (offset < wall_items_count)){
			wall = API.wall.get({"owner_id": -'.$group_vk_id.', "offset": offset, "count": 100, "filter": "owner"});
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

	file_put_contents($f_progress, 'получение постов...');

	$post_arr = new Judy(Judy::INT_TO_MIXED);

	if (isset($result['response'])){
		if (is_array($result['response']['ids']))
		foreach ($result['response']['ids'] as $k => $id){
			$p_time = $result['response']['dates'][$k]-3600;
           	if ($p_time > $date_min && $p_time < $date_max){
				$post_arr[] = $id;
			}
		}
	}

	$users_comments = array();

	$query_count = $post_arr->count();
	$i = 0;

	if ($query_count > 0)
	foreach ($post_arr as $p_id){
		$query = '
			var comment = API.wall.getComments({"post_id":'.$p_id.',"offset":0,"owner_id":-'.$group_vk_id.',"need_likes":1,"count":100});
			var i = 0;
			var count = comment.count;
			var user_ids;
			var offset = 100;

			if (count > 0) {
				user_ids = comment.items;
				while (i < 25 && offset < count){
					user_ids = user_ids + API.wall.getComments({"post_id":'.$p_id.',"offset": offset,"owner_id":-'.$group_vk_id.',"need_likes":1,"count":100}).items;
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
		usleep(250000);

		if (isset($result['response'])){
			foreach ($result['response'] as $comment){
				if ($comment['from_id'] == $user_id)
					$users_comments[] = $comment;
			}
		}

		$progress = round((($i+1)/$query_count)*100,2);
        if ($progress > 100) $progress = 100;
		file_put_contents($f_progress, 'сбор комментариев: '.$progress);
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
				    	<th>Дата комментария</th>
						<th>Топик</th>
						<th>Комментарий</th>
						<th>Лайки</th>
					</tr>
				    </thead>
					<tbody>
<?
	if (sizeof($users_comments) > 0){
		$save = true;
		foreach ($users_comments as $uc){
			if (!isset($uc['likes']['count']))
				$uc['likes']['count'] = 0;
?>
					<tr>
						<td><?=date('d.m.Y H:i',$uc['date']);?></td>
						<td><a target="_blank" href="https://vk.com/feed?w=wall-<?=$group_vk_id;?>_<?=$uc['id'];?>">wall-<?=$group_vk_id;?>_<?=$uc['id'];?></a></td>
						<td><?=$uc['text'];?></td>
						<td><?=$uc['likes']['count'];?></td>
					</tr>
<?
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
	report_do('get_comment', $save);
}
?>