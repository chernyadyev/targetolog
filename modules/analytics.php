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

	$gr_url = !empty($_POST['gr_url']) ? $_POST['gr_url'] : 'https://vk.com/roomstory';
	if ($_POST['do'] == 'get_dem'){
	$group_id = preg_replace('@(http|https)://vk.com/@', '', $gr_url);
	$group_id = preg_replace('@public@', '', $group_id);

	$result = json_decode(vk_query('https://api.vk.com/method/groups.getById', array(
		'group_id' => $group_id,
		'fields' => 'members_count',
		'access_token' => TOKEN
	)), true);

	if (!isset($result['response'])) show_error('<b>Ошибка:</b> группа не найдена!');

	if (empty($result['error']['error_code'])){
		$group_vk_array = $result['response'][0];

		$group_vk_id = $group_vk_array['gid'];
		$group_vk_count = $group_vk_array['members_count'];
		if ($group_vk_count > 750000) show_error('<b>Ошибка:</b> размер группы не должен превышать 550000 подписчиков!');

		$query_count = ceil(ceil($group_vk_count / 1000) / 10);

		$count_return = $online = $online_mobile = $aktive = $all_city = $all_contry = $all_f = 0;
		$sex['m'] = $sex['w'] = 0;
		$city_a = $contry_a = array();

		for ($i=0; $i<$query_count; $i++){
		$time_stamp = time();
		$offset_out = $i*10000;
  		$query = "
  			var members = API.groups.getMembers({\"group_id\": $group_vk_id, \"count\":1000, \"fields\": \"sex, bdate, city, country, last_seen, online, online_mobile\", \"offset\": $offset_out}).items;
  			var offset = 1000;
  			while (offset < 10000 && (offset+$offset_out < $group_vk_count)){
				members = members + API.groups.getMembers({\"group_id\": $group_vk_id, \"count\":1000, \"fields\": \"sex, bdate, city, country, last_seen, online, online_mobile\",\"offset\": ($offset_out + offset)}).items;
				offset = offset + 1000;
  			};
  			return members;
  		";

		$result = json_decode(vk_query('https://api.vk.com/method/execute', array(
			'access_token' => TOKEN,
			'code' => "$query",
			'v' => '5.37'
		)), true);

		if (isset($result['response']))
        foreach ($result['response'] as $m){
        	if (!empty($m['online'])) $online++;
        	if (!empty($m['online_mobile'])) $online_mobile++;

			if (isset($m['city']))
        	if (!empty($m['city']['title'])){
        		if (isset($city_a[$m['city']['title']]))
					$city_a[$m['city']['title']]++;
				else
	        		$city_a[$m['city']['title']] = 1;
				$all_city++;
			}

			if (!empty($m['last_seen']['time']) && (time() - $m['last_seen']['time'] <= 86400)){
               	$aktive++;
			}

			if (isset($m['country']))
        	if (!empty($m['country']['title'])){
				if (isset($contry_a[$m['country']['title']]))
					$contry_a[$m['country']['title']]++;
				else
					$contry_a[$m['country']['title']] = 1;
				$all_contry++;
			}

			if (isset($m['bdate'])){
           	$y = explode('.', $m['bdate']);
            if (sizeof($y) == 3){
	            if ($m['sex'] == 1){
		            $sex['w']++;
		            $sex_key = 'w';
		   		} else if ($m['sex'] == 2){
	        	    $sex['m']++;
		            $sex_key = 'm';
	       		}

	           	$age = date('Y', time()) - $y[2];
				if ($age < 18){
	            	@$b[$sex_key]['0-18']++;
	       		} elseif ($age >= 18 && $age < 21){
	            	@$b[$sex_key]['18-21']++;
	       		} elseif ($age >= 21 && $age < 24){
	            	@$b[$sex_key]['21-24']++;
	       		} elseif ($age >= 24 && $age < 27){
	            	@$b[$sex_key]['24-27']++;
	       		} elseif ($age >= 27 && $age < 30){
	            	@$b[$sex_key]['27-30']++;
	       		} elseif ($age >= 30 && $age < 35){
	            	@$b[$sex_key]['30-35']++;
	       		} elseif ($age >= 35 && $age < 45){
	            	@$b[$sex_key]['35-45']++;
	       		} elseif ($age >= 45){
	            	@$b[$sex_key]['45-99']++;
	       		}
	  				$count_return++;
	           }
	        	}
		    }

			$progress = round((($i+1)/$query_count)*100,2);
			file_put_contents($f_progress, 'сбор подписчиков: '.$progress);
		}

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
<?
	if ($group_vk_count > 0){
	    echo '<div align="center"><div align="left" style="width:100%">';
	    echo 'Анализируемая группа: <a target="_blank" href="'.$gr_url.'">'.$group_id.'</a><br />';
	    echo 'Всего профилей: '.$group_vk_count;
	    echo '</div></div>';

		echo '<table cellpadding="0" cellspacing="0" align="center" width="100%">';
		echo '<tr><td valign="top" width="50%">';

		if (isset($b['w']))
			ksort($b['w']);

		if (isset($b['m']))
		ksort($b['m']);

		$data_str = $s_w = $s_m = null;

		foreach ($b['w'] as $key=>$v){
			$v_w = $v;
			if (isset($b['m'][$key]))
				$v_m = $b['m'][$key];
			else $v_m = 0;
			$v_all = $v_m + $v_w;
			$p_w = round(($v_w/$count_return)*100,2);
			$p_m = round(($v_m/$count_return)*100,2);
			$p_all = round(($v_all/$count_return)*100,2);
			$v_m_m = round(($p_m/20), 2);
			$v_w_m = round(($p_w/20), 2);
			$v_all_m = round(($p_all/20), 2);
			$data_str .= '<tr><td>'.$key.'</td><td style="background: rgba(89,125,163,'.$v_m_m.');">'.$p_m.'% ('.$v_m.')</td><td style="background: rgba(89,125,163,'.$v_w_m.');">'.$p_w.'% ('.$v_w.')</td><td style="background: rgba(89,125,163,'.$v_all_m.');">'.$p_all.'% ('.$v_all.')</td></tr>';
			$s_w .= "$p_w, ";
			$s_m .= "$p_m, ";
		}
		if ($count_return > 0){
			$w = round(($sex['w']/$count_return)*100,2);
			$m = round(($sex['m']/$count_return)*100,2);
		} else $w = $m = 0;

		echo '<p><b>Демография</b></p><canvas align="center" id="canvas" width="480"></canvas>
		<div class="sub_menu">
			<a onclick="del_gr(1);" href="javascript://">Только мужчины</a>
			<a onclick="del_gr(0);" href="javascript://">Только женщины</a>
			<a onclick="clean_gr(0);" href="javascript://">Сбросить</a>
		</div><br />';
		echo '<table class="t_data" cellpadding="0" cellspacing="0" width="100%" border="1"><tr><td><b>Возраст</b></td><td><b>Мужчины ('.$m.'%)</b></td><td><b>Женщины ('.$w.'%)</b></td><td><b>Всего</b></td></tr>';
        echo $data_str;
		echo '</table>';
	    echo '<p style="font-size:12px">(*) профилей с возрастом: '.$count_return.'</p>';
		echo '</td><td width="20"></td><td valign="top" align="left">';
?>
	<p><b>ГЕО (Страны)</b></p>
	<table class="t_data" cellpadding="0" cellspacing="0" width="100%" border="1"><tr><td width="175"><b>Страна</b></td><td><b>%</b></td></tr>
	<?
		arsort($contry_a);
		$contry_a = array_slice($contry_a, 0, 10);
		$p_all = 0;
		foreach ($contry_a as $contry_name => $count){
			$p = round((($count/$all_contry)*100),2);
			if ($p > 1){
				echo '<tr><td>'.$contry_name.'</td><td>'.$p.'% ('.$count.')</td></tr>';
				$p_all += $p;
			}
		}
		echo '<tr><td>Остальное</td><td>'.(100-$p_all).'%</td></tr>';
		echo '</table>';
	?>
	<p><b>ГЕО (города)</b></p>
	<table class="t_data" cellpadding="0" cellspacing="0" width="100%" border="1"><tr><td width="175"><b>Город</b></td><td><b>%</b></td></tr>
	<?
		arsort($city_a);
		$city_a = array_slice($city_a, 0, 10);
		$p_all = 0;
		foreach ($city_a as $city_name => $count){
			$p = round((($count/$all_city)*100),2);
			if ($p > 1){
				echo '<tr><td>'.$city_name.'</td><td>'.$p.'% ('.$count.')</td></tr>';
				$p_all += $p;
			}
		}
		echo '<tr><td>Остальное</td><td>'.(100-$p_all).'%</td></tr>';
		echo '</table>';
	?>
	<p><b>Активность аудитории</b></p>
	<p>На момент анализа online: <?=$online?> (из них с мобильных устройств: <?=$online_mobile?>)
	<br />
	Активная аудитория ВКонтакте: <?=$aktive?></p>
	</td></tr>
	</table>
<?
 } else echo 'Группа не найдена';
?>
				</div>
			</div>
		</div>
	</div>
</section>

	<script>
	var data = [
		{
			label: "мужчины",
			fillColor : "rgba(89,125,163,0.5)",
			strokeColor : "rgba(89,125,163,0.8)",
			highlightFill: "rgba(89,125,163,0.75)",
			highlightStroke: "rgba(89,125,163,1)",
			data : [<?=$s_m?>]
		},
		{
			label: "женщины",
			fillColor : "rgba(133,175,208,0.5)",
			strokeColor : "rgba(133,175,208,0.8)",
			highlightFill: "rgba(133,175,208,0.75)",
			highlightStroke: "rgba(133,175,208,1)",
			data : [<?=$s_w?>]
		}
	];
	var barChartData = {
		labels : ["до 18","от 18 до 21","от 21 до 24","от 24 до 27","от 27 до 30","от 30 до 35","от 35 до 45","от 45"],
		datasets : data
	}
	function set_clean(){
		barChartData.datasets = [];
		for (var key in data) {
			barChartData.datasets[key] = data[key];
		}
	}
	function del_gr(item){
		set_clean();
    	barChartData.datasets.splice(item, 1);
    	gr_update();
	}
	function clean_gr(){
		set_clean();
    	gr_update();
	}
	function gr_update(){
		var ctx = document.getElementById("canvas").getContext("2d");
		window.myBar.destroy();
		window.myBar = new Chart(ctx).Bar(barChartData, {
			responsive : true,
			multiTooltipTemplate: "<%= datasetLabel %> - <%= value %>%"
		});
	}
	</script>
<?
	report_do('analytics', true);
	}
?>