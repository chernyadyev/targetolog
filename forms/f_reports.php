<?
	$date_f = isset($_POST['date_f']) ? $_POST['date_f'] : date('d.m.Y', time());
?>
<script>
function get_city(country_id){
	$.get("/ajax/get_city.ajax.php?country_id="+country_id, function( data ) {
		$("#city_sel").html( data );
	});
}
$(function () {
    $("#d1").datepicker(
        {
        changeMonth: true,
        changeYear: false,
        dateFormat: "dd.mm.yy",
        showButtonPanel: true
        });
});
</script>
<?
	require_once($_SERVER['DOCUMENT_ROOT'].'/mysql.inc.php');
	$rep_name = array (
		'c_group' => 'поиск похожих групп',
		'audience' => 'поиск целевой аудитории',
		'activnosti' => 'активности',
		'best_p' =>	'популярные люди',
		'active_in_gr' => 'стена',
		'post_auditoriya' => 'аудитория постов',
		'get_obsujdeniya' => 'обсуждения',
		'analytics' => 'аналитика',
		'popular_post' => 'популярные посты',
		'adm_contact' => 'контакты администрации',
		'active_in_user' =>	'активности на странице',
		'interes' => 'интересы',
		'all_social_net' => 'другие соц. сети',
		'get_frend_follower' => 'друзья и подписчики',
		'user_pary' => 'пары',
		'beasdey' => 'дни рождения',
		'get_comment' => 'комментарии'
	);
?>
<section class="content">
      <div class="row">
       <div class="col-md-12">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Параметры</h3>
            </div><!-- /.box-header -->
            <form id="FDem" action="main.php?m=reports" method="post" class="form-horizontal">
              <div class="box-body">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Показать отчеты за дату:</label>
                  <div class="col-sm-8">
					<div class="input-group">
                      <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                      </div>
                    <input type="text" name="date_f" id="d1" value="<?=$date_f;?>" class="form-control pull-right">
                    </div>
					<p class="help-block">Выберите за какую дату показывать отчеты.</p>
                  </div>
                </div>
              </div><!-- /.box-body -->
              <div class="box-footer">
                <button type="submit" class="btn btn-info pull-right">Отправить</button>
              </div><!-- /.box-footer -->
            </form>

          </div><!-- /.box -->
		</div>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-solid">
				<div class="box-header with-border">
					<h3 class="box-title">Выберите отчет:</h3>
				</div>
				<div class="box-body text-left">

<?
	$sql = 'SELECT * FROM `tlog_reports` WHERE `uid` = '.$_SESSION['user']['id'].' AND DATE_FORMAT(FROM_UNIXTIME(`time`), "%d.%m.%Y") = \''.$date_f.'\' ORDER BY `time` DESC';
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0){
	echo '<ul>';
	while ($row = mysql_fetch_array($result)){
?>
		<li><a href="/main.php?m=s_report&page=<?=$row['fname'];?>">Отчет за <?=date('d.m.Y H:i',$row['time']);?> &mdash; <?=$rep_name[$row['type']]?></a></li>
<?
	}
	echo '</ul>';
	} else echo 'Отчетов нет.';
?>

				</div>
			</div>
		</div>
	</div>
</section>
