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
                  <label for="inputEmail3" class="col-sm-4 control-label">URL групп<br />или короткие имена:</label>
                  <div class="col-sm-8">
                    <textarea class="form-control" name="group_id" placeholder="https://vk.com/roomstory" rows=10 cols=20 wrap="off"></textarea>
					<p class="help-block">Скопируйте URL групп, подписчиков которых нужно собрать. Каждую группу указывайте с новой строки.</p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Пересечение в группах (от):</label>
                  <div class="col-sm-8">
					<input class="form-control" name="min_p" type="text" placeholder="1">
					<p class="help-block">Укажите во скольких группах одновременно должы быть подписчики (чтобы просто собрать подписчиков, укажите 1).</p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Пересечение в группах (до):</label>
                  <div class="col-sm-8">
					<input class="form-control" name="max_p" type="text" placeholder="0">
					<p class="help-block">Для получения всех подписчиков - оставьте поле пустым.</p>
                  </div>
                </div>
              </div><!-- /.box-body -->
              <div class="box-footer">
				<input name="do" type="hidden" value="get_ca">
                <button onclick="send_forma(); return false;" type="submit" class="btn btn-info pull-right">Отправить</button>
				<p class="help-block">Максимальный размер собранной целевой аудитории: 1.500.000 человек</p>
              </div><!-- /.box-footer -->
            </form>
          </div><!-- /.box -->
		</div>
	</div>
</section>
<script>
	function send_forma(){
		var timer;

		$.ajax({
		    type: 'POST',
		  	url: "/modules/audience.php",
			data: {
				group_id: $('textarea[name=group_id]').val(),
				min_p: $('input[name=min_p]').val(),
				max_p: $('input[name=max_p]').val(),
				do: "get_p"
			},
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
		        },
			    "aaSorting": [ [1,'desc'] ]
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