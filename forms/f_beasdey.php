<?
	require_once($_SERVER['DOCUMENT_ROOT'].'/core/vk.api.php');
?>
<script>
function get_city(country_id){	$.get("/ajax/get_city.ajax.php?country_id="+country_id, function( data ) {
		$("#city_sel").html( data );
	});
}
$(function () {
    $("#d").datepicker(
        {
        changeMonth: true,
        changeYear: false,
        dateFormat: "dd.mm",
        showButtonPanel: true
        });
    $("#d1").datepicker(
        {
        changeMonth: true,
        changeYear: false,
        dateFormat: "dd.mm",
        showButtonPanel: true
        });
});
</script>

<section class="content">
      <div class="row">
       <div class="col-md-12">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Параметры</h3>
            </div><!-- /.box-header -->
            <!-- form start -->
            <form enctype="multipart/form-data" id="FDem" action="#" method="post" class="form-horizontal">
              <div class="box-body">
                <div id="post" class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">URL групп:</label>
                  <div class="col-sm-8">
                    <textarea class="form-control" name="users_ids" placeholder="https://vk.com/roomstory" rows=10 cols=20 wrap="off"></textarea>
					<p class="help-block">Скопируйте URL групп у подписчиков которых нужно дни рождений собрать.</p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">или базы:</label>
                  <div class="col-sm-8">
					<input name="base_files" type="file" value="">
					<p class="help-block">Собирать пары.</p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Собирать дни рождения для пользователей из:</label>
                  <div class="col-sm-8">
                    <div class="checkbox"><label><input name="type_f" type="radio" checked value="0"> формы URL</label></div>
                    <div class="checkbox"><label><input name="type_f" type="radio" value="1"> базе</label></div>
					<p class="help-block">Отметьте откуда будет происходить дней рождений.</p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Дата дня рождения:</label>
                  <div class="col-sm-4">
					<div class="input-group">
                      <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                      </div>
                    <input type="text" name="date_s" id="d" value="<?=date('d.m', time());?>" class="form-control pull-right">
                    </div>
					<p class="help-block">с</p>
                  </div>
                  <div class="col-sm-4">
					<div class="input-group">
                      <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                      </div>
                    <input type="text" name="date_f" id="d1" value="<?=date('d.m', time());?>" class="form-control pull-right">
                    </div>
					<p class="help-block">по</p>
                  </div>
                  <div class="col-sm-2"></div>
                </div>
<?
			$vk_result_id = json_decode(vk_query('https://api.vk.com/method/database.getCountries', array(
				'need_all' => 0,
				'count' => 1000,
	   			'access_token' => TOKEN,
				'v' => '5.37'
			)), true);
?>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Страна:</label>
                  <div class="col-sm-8">
             		<select class="form-control" onchange="get_city(this.value);" size="1" name="country_id">
					<option value="0">Все страны</option>
					<?
					foreach ($vk_result_id['response']['items'] as $country){
						echo '<option value="'.$country['id'].'">'.$country['title'].'</option>';
					}
					?>
					</select>
					<p class="help-block">Собирать пары.</p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Города:</label>
                  <div class="col-sm-8">
					<div id="city_sel">
					<select class="form-control" size="1" name="city_id">
						<option value="0">Все города</option>
					</select>
					</div>
					<p class="help-block">Собирать пары.</p>
                  </div>
                </div>
              </div><!-- /.box-body -->
              <div class="box-footer">
				<input name="do" type="hidden" value="get_active">
                <button onclick="send_forma(); return false;" type="submit" class="btn btn-info pull-right">Отправить</button>
              </div><!-- /.box-footer -->
            </form>
          </div><!-- /.box -->
		</div>
	</div>
</section>
<script>
	function send_forma(){
		var timer;

	   	var formData = new FormData();
	    formData.append( 'base_files', $('input[name=base_files]')[0].files[0] );
	    formData.append( 'users_ids', $('textarea[name=users_ids]').val() );
	    formData.append( 'd_start', $('input[name=date_s]').val() );
	    formData.append( 'd_finish', $('input[name=date_f]').val() );
	    formData.append( 'country_id', $('select[name=country_id]').val() );
	    formData.append( 'city_id', $('select[name=city_id]').val() );
	    formData.append( 'do', 'get_active' );

		$.ajax({
		    type: 'POST',
		  	url: "/modules/beasdey.php",
		  	enctype: 'multipart/form-data',
			data: formData,
			processData: false,
			contentType: false,
			beforeSend: function(){
				$('#data_result').html('');
				$('#progress').show();
				timer = setInterval(timer_update, 1000);
			},
  			async: true
		}).done(function( data_result ) {
			clearTimeout(timer);
		    $('#data_result').html( data_result );
			$('#ca_data').DataTable({
    			"pageLength": 50,
          		dom: 'T<"clear">lfrtip',
        		"tableTools": {
		            "sSwfPath": "/js/dataTables/tabletools/swf/copy_csv_xls.swf",
		            "aButtons": ['copy', {"sExtends": "xls", "sButtonText": "CSV"}]
		        }
			});
		});
		return true;
	}
	function timer_update(){
		$.get('/progress/<?=$_SESSION['uid']?>/fp.dat?r='+getRandomArbitary(0,99999), function( data ) {
			$('#p').html(data+'%');
		});
	}
</script>