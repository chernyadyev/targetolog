<section class="content">
      <div class="row">
       <div class="col-md-12">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Параметры</h3>
            </div><!-- /.box-header -->
            <!-- form start -->
            <form enctype="multipart/form-data" id="FDem" action="main.php?m=get_frend_follower" method="post" class="form-horizontal">
              <div class="box-body">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">База А:</label>
                  <div class="col-sm-8">
					<input name="base_a" type="file" value="">
					<p class="help-block">База пользователей А.</p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">База Б:</label>
                  <div class="col-sm-8">
					<input name="base_b" type="file" value="">
					<p class="help-block">База пользователей Б.</p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Тип конвертации:</label>
                  <div class="col-sm-8">
                    <div class="checkbox"><label><input checked name="doit" type="radio" value="minus"> Убрать из А пользователей Б</label></div>
                    <div class="checkbox"><label><input name="doit" type="radio" value="peresek"> Оставить пересекающихся</label></div>
                    <div class="checkbox"><label><input name="doit" type="radio" value="union"> Объединить базы</label></div>
					<p class="help-block">Отметьте кого нужно конвертировать.</p>
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
	    formData.append( 'base_a', $('input[name=base_a]')[0].files[0] );
	    formData.append( 'base_b', $('input[name=base_b]')[0].files[0] );
		formData.append( 'doit', $('input[name=doit]:checked').val() );
	    formData.append( 'do', 'get_active' );

		$.ajax({
		    type: 'POST',
		  	url: "/modules/base_do.php",
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