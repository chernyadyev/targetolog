<script>
$(function () {
    $("#d").datepicker(
        {
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd.mm.yy",
        showButtonPanel: true,
        minDate: "01.01.2015",
        maxDate: 0,
		onSelect: function(dateText) {
        	$("#d1").datepicker('option', 'minDate', dateText);
	    }
        });
    $("#d1").datepicker(
        {
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd.mm.yy",
        showButtonPanel: true,
        minDate: "01.01.2015",
        maxDate: 0,
		onSelect: function(dateText) {
        	$("#d").datepicker('option', 'maxDate', dateText);
	    }
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
            <form id="FDem" action="main.php?m=post_auditoriya" method="post" class="form-horizontal">
              <div class="box-body">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Собирать обсуждения из:</label>
                  <div class="col-sm-8">
                    <div class="checkbox"><label><input onchange="if (this.checked) {$('#group').hide(); $('#post').show();}" name="type_f" type="radio" checked value="0"> ссылок на обсуждения</label></div>
                    <div class="checkbox"><label><input onchange="if (this.checked) {$('#post').hide(); $('#group').show();}" name="type_f" type="radio" value="1"> групп</label></div>
					<p class="help-block">Отметьте откуда будет происходить сбор подписчиков из обсуждений.</p>
                  </div>
                </div>
                <div id="post" class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">URL постов:</label>
                  <div class="col-sm-8">
                    <textarea class="form-control" name="data_ids" placeholder="https://vk.com/topic-16167985_22393098" rows=10 cols=20 wrap="off"></textarea>
					<p class="help-block">Скопируйте URL постов аудиторию которых нужно собрать.</p>
                  </div>
                </div>
                <div id="group" style="display: none;" class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">ID(s) групп:</label>
                  <div class="col-sm-8">
                    <textarea class="form-control" name="data_ids_2" placeholder="https://vk.com/club16167985" rows=10 cols=20 wrap="off"></textarea>
					<p class="help-block">Скопируйте URL постов аудиторию которых нужно собрать.</p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Время сообщений:</label>
                  <div class="col-sm-4">
					<div class="input-group">
                      <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                      </div>
                    <input type="text" name="date_s" id="d" value="<?=date('d.m.Y', time());?>" class="form-control pull-right">
                    </div>
					<p class="help-block">с</p>
                  </div>
                  <div class="col-sm-4">
					<div class="input-group">
                      <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                      </div>
                    <input type="text" name="date_f" id="d1" value="<?=date('d.m.Y', time());?>" class="form-control pull-right">
                    </div>
					<p class="help-block">по</p>
                  </div>
                  <div class="col-sm-2"></div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Мин. сообщений в теме:</label>
                  <div class="col-sm-8">
                    <input type="input" class="form-control" name="min_comment" placeholder="1">
					<p class="help-block">Минимум сообщений в теме обсуждений.</p>
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
		  	url: "/modules/get_obsujdeniya.php",
			data: {
				data_ids: $('textarea[name=data_ids]').val(),
				data_ids_2: $('textarea[name=data_ids_2]').val(),
				date_s: $('input[name=date_s]').val(),
				date_f: $('input[name=date_f]').val(),
				type_f: $('input[name=type_f]:checked').val(),
				do: "get_active"
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