<section class="content">
      <div class="row">
       <div class="col-md-12">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Параметры</h3>
            </div><!-- /.box-header -->
            <!-- form start -->
            <form id="FDem" action="main.php?m=get_frend_follower" method="post" class="form-horizontal">
              <div class="box-body">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">URL пользователя:</label>
                  <div class="col-sm-8">
                    <input type="input" class="form-control" name="group_id" placeholder="https://vk.com/prinum">
					<p class="help-block">У кого собирать друзей и подписчиков.</p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Собирать:</label>
                  <div class="col-sm-8">
                    <div class="checkbox"><label><input checked name="type_f" type="radio" value="0"> друзей</label></div>
                    <div class="checkbox"><label><input name="type_f" type="radio" value="1"> подписчиков</label></div>
                    <div class="checkbox"><label><input name="type_f" type="radio" value="2"> друзей и подписчиков</label></div>
					<p class="help-block">Отметьте кого нужно собрать.</p>
                  </div>
                </div>
              </div><!-- /.box-body -->
              <div class="box-footer">
				<input name="do" type="hidden" value="get_active">
                <button onclick="send_forma(); return false;" type="submit" class="btn btn-info pull-right">Искать популярных</button>
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
		  	url: "/modules/get_frend_follower.php",
			data: {
				group_id: $('input[name=group_id]').val(),
				type_f: $('input[name=type_f]:checked').val(),
				max_p: $('input[name=max_p]').val(),
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