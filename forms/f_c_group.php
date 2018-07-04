<section class="content">
      <div class="row">
       <div class="col-md-12">
          <div class="box box-info">
           <div class="box-header with-border">
              <h3 class="box-title">Параметры</h3>
            </div><!-- /.box-header -->
            <!-- form start -->
            <form id="FDem" action="main.php?m=c_group" method="post" class="form-horizontal">
              <div class="box-body">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">URL группы:</label>
                  <div class="col-sm-8">
                    <input type="input" class="form-control" name="gr_url" placeholder="https://vk.com/roomstory">
					<p class="help-block">Скопируйте URL группы, на которую подписана Ваша ЦА, рекомендуется найти активную группу с ежедневными постами и аудиторией не более 2000-3000 человек. Оптимально, чтобы посты группы лайкались и комментировались.</p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Подписчики:</label>
                  <div class="col-sm-4">
                    <input type="input" class="form-control" name="min_p" placeholder="100">
					<p class="help-block">от (в результаты поиска не будут включены группы содержашие подписчиков меньше, чем указано), рекомендуется указывать от 100 подписчиков.</p>
                  </div>
                  <div class="col-sm-4">
					<input type="input" class="form-control" name="max_p" placeholder="30000">
					<p class="help-block">до (в результаты поиска не будут включены группы содержашие подписчиков больше, чем указано), рекомендуется указывать до 30000 подписчиков. (мах &mdash; 50000)</p>
                  </div>
                  <div class="col-sm-2"></div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Минимум подписчиков ЦА:</label>
                  <div class="col-sm-8">
                    <input type="input" class="form-control" name="min_ca" placeholder="5">
					<p class="help-block">В результаты поиска не будут включены группы содержащие меньше, чем указано подписчиков из целевой группы. (min &mdash; 3)</p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Показывать групп:</label>
                  <div class="col-sm-8">
                    <input type="input" class="form-control" name="show_r" placeholder="500">
					<p class="help-block">Укажите, сколько групп будет показано в результатах поиска. (мах &mdash; 5000)</p>
                  </div>
                </div>
              </div><!-- /.box-body -->
              <div class="box-footer">
				<input name="do" type="hidden" value="get_ca">
                <button onclick="send_forma(); return false;" type="submit" class="btn btn-info pull-right">Отправить</button>
              </div><!-- /.box-footer -->
            </form>
          </div><!-- /.box -->
		</div>
	</div>
</section>
<script>
	function send_forma(){		var timer;
		$.ajax({
		    type: 'POST',
		  	url: "/modules/c_group.php",
			data: {
				gr_url: $('input[name=gr_url]').val(),
				max_p: $('input[name=max_p]').val(),
				min_p: $('input[name=min_p]').val(),
				min_ca: $('input[name=min_ca]').val(),
				show_r: $('input[name=show_r]').val(),
				do: "get_ca"
			},
			beforeSend: function(){
				$('#data_result').html('');				$('#progress').show();
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
		        },
			    "aaSorting": [ [4,'desc'] ]
			});
		});
		return true;
	}
	function timer_update(){		$.get('/progress/<?=$_SESSION['uid']?>/fp.dat?r='+getRandomArbitary(0,99999), function( data ) {
			$('#p').html(data+'%');
		});
	}
</script>