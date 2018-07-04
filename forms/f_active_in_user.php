<script>
$(function () {
    $("#d").datepicker(
        {
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd.mm.yy",
        showButtonPanel: true,
        minDate: "01.01.2015",
        maxDate: 0
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
            <form id="FDem" action="main.php?m=active_in_user" method="post" class="form-horizontal">
              <div class="box-body">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">URL пользователей:</label>
                  <div class="col-sm-8">
                    <textarea class="form-control" name="group_id" placeholder="https://vk.com/prinum" rows=10 cols=20 wrap="off"></textarea>
					<p class="help-block">Скопируйте URL пользователей активности на чьих страницах нужно собрать.</p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Начиная с даты:</label>
                  <div class="col-sm-8">
					<div class="input-group">
                      <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                      </div>
                      <input type="text" name="date" id="d" value="<?=date('d.m.Y', time());?>" class="form-control pull-right">
                    </div>
					<p class="help-block">Укажите с какой даты собирать активности.</p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Собирать:</label>
                  <div class="col-sm-8">
                    <div class="checkbox"><label><input checked name="ch_like" type="checkbox" value="1"> лайки</label></div>
                    <div class="checkbox"><label><input name="ch_repost" type="checkbox" value="1"> репосты</label></div>
                    <div class="checkbox"><label><input name="ch_comment" type="checkbox" value="1"> комментарии</label></div>
					<p class="help-block">Отметьте какие активности собирать.</p>
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
		  	url: "/modules/active_in_user.php",
			data: {
				group_id: $('textarea[name=group_id]').val(),
				date: $('input[name=date]').val(),
				ch_like: $('input[name=ch_like]').is(':checked'),
				ch_repost: $('input[name=ch_repost]').is(':checked'),
				ch_comment: $('input[name=ch_comment]').is(':checked'),
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