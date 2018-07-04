<section class="content">
      <div class="row">
       <div class="col-md-12">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Параметры</h3>
            </div><!-- /.box-header -->
            <!-- form start -->
            <form id="FDem" action="main.php?m=best_p" method="post" class="form-horizontal">
              <div class="box-body">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">URL групп:</label>
                  <div class="col-sm-8">
                    <input type="input" class="form-control" name="gr_url" placeholder="https://vk.com/roomstory">
					<p class="help-block">Скопируйте URL группы, где нужно найти популярных, среди аудитории выбранной группы, подписчиков.</p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Тип поиска:</label>
                  <div class="col-sm-8">
                    <div class="checkbox"><label><input checked name="type_f" type="radio" value="0"> по друзьям</label></div>
                    <div class="checkbox"><label><input name="type_f" type="radio" value="1"> по подписчикам</label></div>
                    <div class="checkbox"><label><input name="type_f" type="radio" value="2"> по друзьям и подписчикам</label></div>
					<p class="help-block">Отметьте где искать целевую аудиторию: в друзьях, подписчиках или везде.</p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Показывать результатов:</label>
                  <div class="col-sm-8">
                    <input type="input" class="form-control" name="max_p" placeholder="500">
					<p class="help-block">Сколько популярных подписчиков целевой группы показывать в результатах поиска.</p>
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

		$.ajax({
		    type: 'POST',
		  	url: "/modules/best_p.php",
			data: {
				gr_url: $('input[name=gr_url]').val(),
				type_f: $('input[name=type_f]:checked').val(),
				max_p: $('input[name=max_p]').val(),
				do: "get_f"
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
			    "aaSorting": [ [4,'desc'] ]
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