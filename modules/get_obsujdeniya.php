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

	if (empty($_POST['data_ids']) && empty($_POST['data_ids_2']) && $_POST['type_f'] == 0)
		$_POST['data_ids'] = 'https://vk.com/topic-16167985_22393098';
	if (empty($_POST['data_ids']) && empty($_POST['data_ids_2']) && $_POST['type_f'] == 1)
		$_POST['data_ids_2'] = 'https://vk.com/club16167985';

	$type_f = isset($_POST['type_f']) ? (int)$_POST['type_f'] : 0;

	if (!empty($_POST['data_ids']) && $type_f == 0){
		$post_data = $_POST['data_ids'];
	}
	if (!empty($_POST['data_ids_2']) && $type_f == 1){
		$post_data = $_POST['data_ids_2'];
	}

if ($_POST['do'] == 'get_active'){
    $date_min = isset($_POST['date_s']) ? strtotime($_POST['date_s']) : 0;
    $date_max = isset($_POST['date_f']) ? strtotime($_POST['date_f']) : 0;
    $no_data =  isset($_POST['no_data']) ? 1 : 0;
	$min_comment = isset($_POST['min_comment']) ? (int)$_POST['min_comment'] : 1;


	if ($type_f == 1){
		$post_data = preg_replace('@(http|https)://vk.com/@', '', $post_data);
	}
	$data_ids = explode("\n", $post_data);
	$query_count = sizeof($data_ids);

	$i = 0;

	// Массив топиков
	$topic_items = array();

	if ($type_f == 0) {
		// Заданы темы обсуждений
		foreach ($data_ids as $topics){
			preg_match('@topic-(.*)_(.*)@', $topics, $matches);

			$group_id = $matches[1];
			$topc_id = $matches[2];

           	$topic_items[] = array('group_id' => $group_id, 'topic_id' => $topc_id);
		}
	} elseif ($type_f == 1) {
		// Заданы группы, брать все темы обсуждений

		foreach ($data_ids as $group_id){
            if (empty($group_id)) continue;
			$vk_result_id = json_decode(vk_query('https://api.vk.com/method/groups.getById ', array(
				'group_ids' => $group_id,
				'fields' => 'id, screen_name',
	   			'access_token' => TOKEN,
				'v' => '5.37'
			)), true);


			if ($query_count > 1){
            	if (!isset($vk_result_id['response']))
            		continue;
			} elseif ($query_count == 1) {
            	if (!isset($vk_result_id['response'])) show_error('<b>Ошибка:</b> группа или группы не найдены!');
			}

			$group_id = $vk_result_id['response'][0]['id'];
			$time_stamp = time();
			usleep(500000);

			$query = '
				var topic = API.board.getTopics({"group_id": '.$group_id.', "offset": 0, "count": 100});
				var topic_count = topic.count;

				var topic_items = [];
				topic_items = topic.items;

				var i = 0;
				var offset = 100;
				while (i < 25 && offset <= topic_count){
					topic_items = topic_items + API.board.getTopics({"group_id": '.$group_id.', "offset": offset, "count": 100}).items;
					offset = offset + 100;
					i = i + 1;
				}

				return topic_items;
			';

			$result = json_decode(vk_query('https://api.vk.com/method/execute', array(
				'access_token' => TOKEN,
				'code' => "$query",
				'v' => '5.37'
			)), true);

			foreach ($result['response'] as $topic_item){
            	if ($topic_item['comments'] >= $min_comment){
                	$topic_items[] = array('group_id' => $group_id, 'topic_id' => $topic_item['id']);
            	}
			}

			$progress = round((($i+1)/$query_count)*100,2);
	        if ($progress > 100) $progress = 100;
			file_put_contents($f_progress, 'сбор топиков: '.$progress);

			$i++;
			usleep(250000);
		}
	}

	$query_count_topic = sizeof($topic_items);
	$i = 0;
	$users_ids = array();

	// Сбор комментариев и их авторов из топиков
	foreach ($topic_items as $topic){
		$result = json_decode(vk_query('https://api.vk.com/method/board.getComments', array(
			'access_token' => TOKEN,
			'group_id' => $topic['group_id'],
			'topic_id' => $topic['topic_id'],
			'count' => 100,
			'v' => '5.37'
		)), true);
		if (isset($result['response']))
	        $comment_count = $result['response']['count'];
	    else $comment_count = 0;

		if ($comment_count > $min_comment){
		    foreach ($result['response']['items'] as $c){
		    	$p_time = $c['date']-3600;
		    	if ($no_data) {
	        		$users_ids[] = $c['from_id'];
		    	} elseif ($p_time > $date_min && $p_time < $date_max){
	        		$users_ids[] = $c['from_id'];
	        	}
		    };
		    if ($comment_count > 100){
				$query_count = ceil(ceil($comment_count / 100) / 25);
				$offset_s = 100;
				for ($j=0; $j<$query_count; $j++){
					$query = '
					var offset = '.$offset_s.';
					var i = 0;
					var comment = [];
					var comments = [];

					comment = API.board.getComments({"group_id": '.$topic['group_id'].', "topic_id": '.$topic['topic_id'].', "count": 100, "offset":offset});
					comments = comment.items;
					offset = offset + 100;

					while (i < 25 && offset <= comment.count){
						comments = comments + API.board.getComments({"group_id": '.$topic['group_id'].', "topic_id": '.$topic['topic_id'].', "count": 100, "offset":offset}).items;
						offset = offset + 100;
						i = i + 1;
					}

					return comments;
					';

	            	$offset_s += 2500;

					$result = json_decode(vk_query('https://api.vk.com/method/execute', array(
						'access_token' => TOKEN,
						'code' => "$query",
						'v' => '5.37'
					)), true);

					usleep(500000);

					if (isset($result['response']))
				    foreach ($result['response'] as $c){
				    	$p_time = $c['date']-3600;
				    	if ($no_data) {
			        		$users_ids[] = $c['from_id'];
				    	} elseif ($p_time > $date_min && $p_time < $date_max){
			        		$users_ids[] = $c['from_id'];
			        	}
				    };
				}
		    }
		}

		$progress = round((($i+1)/$query_count_topic)*100,2);
	    if ($progress > 100) $progress = 100;
		file_put_contents($f_progress, 'сбор комментариев: '.$progress);

		$i++;
		usleep(250000);
	}

	echo '<script>$("#progress").hide();</script>';
?>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-solid">
				<div class="box-header with-border">
					<h3 class="box-title">Результаты:</h3>
				</div>
				<div class="box-body text-center">
				Найдено: <?=sizeof($users_ids);?> подписчиков<br />
				<?if (sizeof($users_ids) > 0){?><div style="margin-bottom: 10px;" align="center"><a href="/export.php?f=<?=md5($_SESSION['user']['id'].'Jnsb2156')?>">Сохранить результаты</a></div><?}?>
				</div>
			</div>
		</div>
	</div>
<?
	$save = false;
	if (isset($users_ids)){
		if (sizeof($users_ids)){
			$save = true;
			$uids_data = null;
			foreach ($users_ids as $uids){
				$uids_data[] = $uids."\r\n";
			}
			$uids_data_str = implode('', array_unique($uids_data));
		}
	}
?>
</section>
<?
	if (!empty($uids_data_str)){
		if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156')))
		mkdir($_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156'));
		$f_export = $_SERVER['DOCUMENT_ROOT'].'/export/'.md5($_SESSION['user']['id'].'Jnsb2156').'/export.txt';
		file_put_contents($f_export, $uids_data_str);
	} else $uids_data_str = null;

	report_do('get_obsujdeniya', $save, $uids_data_str);
}
?>