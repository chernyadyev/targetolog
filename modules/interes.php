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

	$subject = array (
		'69' => 'Спорт и здоровье',
		'70' => 'Фитнес',
		'71' => 'Спортивное питание',
		'36' => 'Футбол',
		'72' => 'Workout',
  		'74' => 'Спорт (другое)',
  		'73' => 'Спорт (похудение)',
  		'75' => 'Природа и путешествия',
  		'17' => 'Путешествия',
  		'45' => 'Природа',
  		'76' => 'Животные',
  		'63' => 'Девушки',
  		'9' => 'Эротика/порно',
  		'39' => 'Красивые девушки',
  		'61' => 'Развлечения',
  		'5' => 'Кинофильмы',
  		'7' => 'Музыка',
  		'48' => 'Видео',
  		'43' => 'Ужасы',
  		'41' => 'Ностальгия',
  		'49' => 'Телевидение',
  		'57' => 'Мультфильмы',
  		'37' => 'Игры',
  		'53' => 'Аниме/хентай',
  		'64' => 'Авто/мото',
  		'44' => 'Мото',
 		'12' => 'Автомобили',
  		'68' => 'Женские сообщества',
  		'13' => 'Красота',
  		'15' => 'Диеты',
  		'10' => 'Кулинария',
  		'113' => 'Мысли/цитаты',
  		'77' => 'Бизнес',
  		'79' => 'Стартапы',
  		'78' => 'Финансы',
  		'26' => 'Мотивация',
  		'80' => 'Знакомства и общение',
  		'81' => 'Знакомства',
  		'47' => 'Предложенные новости',
  		'82' => 'Семья и дом',
  		'38' => 'Родительские сообщества',
  		'84' => 'Обустройство и ремонт',
  		'83' => 'Дети',
  		'46' => 'Свадебные сообщества',
  		'40' => 'Отношения',
  		'85' => 'Отдых',
  		'86' => 'Увлечения/хобби',
  		'87' => 'Активный отдых',
  		'88' => 'Handmade',
  		'89' => 'Искусство, дизайн',
  		'90' => 'Архитектура',
  		'22' => 'Дизайн',
  		'19' => 'Фотография',
  		'21' => 'Интерьер',
  		'91' => 'Ландшафтный дизайн',
  		'16' => 'Картинки',
  		'92' => 'Литература и поэзия',
  		'20' => 'Книги',
  		'58' => 'Стихи',
  		'93' => 'Наука и образование',
  		'35' => 'Наука',
  		'34' => 'Познавательно',
  		'29' => 'Иностранные языки',
  		'31' => 'Технологии',
  		'94' => 'Техника и IT',
  		'23' => 'Гаджеты',
  		'95' => 'Софт',
  		'96' => 'Мобильная связь, интернет',
  		'97' => 'Электроника, бытовая техника',
  		'98' => 'Компьютеры',
  		'99' => 'Общество',
  		'56' => 'Профессии',
  		'100' => 'Политика',
  		'101' => 'Геополитика, экономика',
  		'55' => 'Религия',
  		'102' => 'Философия и эзотерика',
  		'54' => 'Имена',
  		'25' => 'Образ жизни',
  		'30' => 'Гороскопы',
  		'103' => 'Товары и услуги',
  		'24' => 'Магазины',
  		'104' => 'Компании',
  		'105' => 'Недвижимость (работа)',
  		'107' => 'Туризм (работа)',
  		'106' => 'Страхование (работа)',
  		'112' => 'Другие услуги (работа)',
  		'108' => 'СМИ, реклама и PR',
  		'6' => 'СМИ',
  		'33' => 'Знаменитости',
  		'32' => 'Реклама',
  		'109' => 'Региональные сообщества',
  		'27' => 'Регионы',
  		'50' => 'Россия',
  		'59' => 'Украина',
  		'110' => 'Кавказ',
  		'114' => 'Казахстан',
  		'4' => 'Мода',
  		'60' => 'Юмор',
  		'52' => 'Молодежные сообщества'
	);

	$user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : 'https://vk.com/prinum';

	if (!empty($user_id)){
		$user_id = preg_replace('@(http|https)://vk.com/@', '', $user_id);

		$vk_result_id = json_decode(vk_query('https://api.vk.com/method/users.get', array(
			'user_ids' => $user_id,
			'fields' => 'sex, bdate, city, country, photo_100, last_seen, counters',
   			'access_token' => TOKEN,
			'v' => '5.37'
		)));

		if (!isset($vk_result_id->response[0])) show_error('<b>Ошибка:</b> пользователь не найден!');

	    $user_arr = $vk_result_id->response[0];

	    $user_id = $user_arr->id;
	    $user_name = $user_arr->last_name.' '.$user_arr->first_name;

		$vk_result = vk_query('https://api.vk.com/method/users.getSubscriptions', array(
			'user_id' => $user_id,
			'extended' => 0,
			'offset' => 0,
			'count' => 200,
			'v' => '5.37',
			'access_token' => TOKEN
		));

		$group_info_arr = json_decode($vk_result);

		if (empty($vk_result))
			die('Ошибка ответа от VK.API');

		$gr_id = null;

    	if (sizeof($group_info_arr->response->groups->items) > 0){
			foreach ($group_info_arr->response->groups->items as $gid){
				$gr_id .= $gid.',';
			}
			$gr_id = rtrim($gr_id, ',');

			$group_count = $group_info_arr->response->groups->count;
		}

		if ($group_count > 0){

			$sql = "SELECT COUNT(*) AS `cnt` FROM `groups_full` WHERE `vk_id` IN ($gr_id)";
			$result = mysql_query($sql);
			list($gr_found) = mysql_fetch_array($result);

			$sql = "SELECT `g`.`g_id`, COUNT(*) AS `cnt`, ROUND((COUNT(*)/$group_count)*100, 2) AS `interes`
				FROM `groups_subject` AS `g`
				WHERE `g`.`vk_id` IN ($gr_id)
				GROUP BY `g`.`g_id`
				HAVING `interes` > 5
				ORDER BY `interes` DESC";

			$result = mysql_query($sql);
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
			echo '<table cellpadding="0" cellspacing="0" align="center" width="980">';
			echo '<tr><td valign="top" width="480">';
?>
			<table cellpadding="0" cellspacing="0" align="center" width="480">
			<tr>
			<td valign="top" width="125" align="left">
			<img src="<?=$user_arr->photo_100;?>" width="100" height="100" border="0">
			</td><td valign="top">
<?
  			echo "<a target='_blank' href='https://vk.com/id$user_id'>$user_name</a><br />";
  			echo '<p>ID: '.$user_id.'<br />';
  			echo 'Пол: '.($user_arr->sex == 1 ? 'женский' : 'мужской').'<br />';
			if (isset($user_arr->bdate)){
	          	$y = explode('.', $user_arr->bdate);
	            if (sizeof($y) == 3){
        	    	$y_now = date('Y', time());
    	        	echo 'Год рождения: '.$y[2].'<br />';
	            }
            }
            if (!empty($user_arr->country->title) && !empty($user_arr->city->title))
	  			echo $user_arr->country->title.', '.$user_arr->city->title.'<br />';
            if (!empty($user_arr->last_seen))
            	echo 'Заходил(а): '.date('d.m.Y H:i', $user_arr->last_seen->time).'<br />';
            echo '</p>';

			echo '<p>Друзей: '.$user_arr->counters->friends.'<br />';
			echo 'Подписчиков: '.$user_arr->counters->followers.'</p>';

			echo '<p>Фотоальбомов: '.$user_arr->counters->albums.'<br />';
			echo 'Видеозаписей: '.$user_arr->counters->videos.'<br />';
			echo 'Аудиозаписей: '.$user_arr->counters->audios.'<br />';
			echo 'Фотографий: '.$user_arr->counters->photos.'<br />';
			echo 'Заметок: '.$user_arr->counters->notes.'</p>';
?>
			</td></tr>
			</table>
<?
			echo '</td><td valign="top">';
			echo 'Найдено групп <b>'.$gr_found.'</b> из <b>'.$group_count.'</b><br />';
			echo 'Найдено групп: <b>'.round(($gr_found/$group_count)*100,2).'%</b><br /><br />';

			echo '<table class="t_data" cellpadding="0" cellspacing="0" border="1" width="450">';
			echo '<tr><td width="250"><b>Интерес</b></td><td><b>Заинтересованность</b></td></tr>';
			$all_interes = null;
			while ($row = mysql_fetch_array($result)){
				if (isset($subject[$row['g_id']])){
					$all_interes[$row['g_id']] = $row['cnt'];
					if (isset($all_interes['group_count']))
						$all_interes['group_count'] += $gr_found;
					else
						$all_interes['group_count'] = 0;
					echo '<tr><td>'.$subject[$row['g_id']].'</td><td>'.$row['interes'].'% ('.$row['cnt'].')</td></tr>';
				}
			}
			echo '</table>';
			echo '</td></tr></table>';
			//print_r($all_interes);
		} else {
			echo 'У пользователя нет доступных сообществ.<br />';
		}
?>
				</div>
			</div>
		</div>
	</div>
<script>$("#progress").hide();</script>
</section>
<?
	report_do('interes', true);
	}
?>