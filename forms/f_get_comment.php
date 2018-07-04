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
            </div>
            <form id="FDem" action="main.php?m=post_auditoriya" method="post" class="form-horizontal">
              <div class="box-body">

                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">URL (ID) пользователя:</label>
                  <div class="col-sm-8">
                    <input type="input" class="form-control" name="user_id" placeholder="https://vk.com/prinum">
					<p class="help-block">Скопируйте сюда URL страницы пользователя, чьи комментарии нужно найти.</p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">URL (ID) группы:</label>
                  <div class="col-sm-8">
                    <input type="input" class="form-control" name="gr_id" placeholder="https://vk.com/lifenews_ru">
					<p class="help-block">Скопируйте сюда URL группы, где нужно искать комментарии.</p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Даты комментариев:</label>
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

              </div>
              <div class="box-footer">
				<input name="do" type="hidden" value="get_active">
                <button onclick="send_forma(); return false;" type="submit" class="btn btn-info pull-right">Отправить</button>
              </div>
            </form>
          </div>
		</div>
	</div>
</section>
<script>
	function send_forma(){
		var timer;

		$.ajax({
		    type: 'POST',
		  	url: "/modules/get_comment.php",
			data: {
				gr_id: $('input[name=gr_id]').val(),
				user_id: $('input[name=user_id]').val(),
				date_s: $('input[name=date_s]').val(),
				date_f: $('input[name=date_f]').val(),
				do: "get_comment"
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
				"aaSorting": [ [3,'desc'] ]
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