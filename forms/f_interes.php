<section class="content">
      <div class="row">
       <div class="col-md-12">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Параметры</h3>
            </div><!-- /.box-header -->
            <!-- form start -->
            <form id="FDem" action="main.php?m=analytics" method="post" class="form-horizontal">
              <div class="box-body">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">URL пользователя:</label>
                  <div class="col-sm-8">
                    <input type="input" class="form-control" name="user_id" placeholder="https://vk.com/prinum">
					<p class="help-block">Скопируйте URL пользователя для которого необходимо провести анализ.</p>
                  </div>
                </div>
              </div><!-- /.box-body -->
              <div class="box-footer">
				<input name="do" type="hidden" value="get_dem">
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
		  	url: "/modules/interes.php",
			data: {
				user_id: $('input[name=user_id]').val(),
				do: "get_dem"
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
		});
		return true;
	}

	function timer_update(){
		$.get('/progress/<?=$_SESSION['uid']?>/fp.dat?r='+getRandomArbitary(0,99999), function( data ) {
			$('#p').html(data+'%');
		});
	}
</script>