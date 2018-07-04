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
					<p class="help-block">Скопируйте URL пользователей аккаунты соц. сетей которых нужно собрать.</p>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-4 control-label">Собирать соц. сети:</label>
                  <div class="col-sm-8">
                    <div class="checkbox"><label><input checked name="skype" type="checkbox" value="ON"> skype</label></div>
                    <div class="checkbox"><label><input checked name="facebook" type="checkbox" value="ON"> facebook</label></div>
                    <div class="checkbox"><label><input checked name="twitter" type="checkbox" value="ON"> twitter</label></div>
                    <div class="checkbox"><label><input checked name="livejournal" type="checkbox" value="ON"> livejournal</label></div>
                    <div class="checkbox"><label><input checked name="instagram" type="checkbox" value="ON"> instagram</label></div>
					<p class="help-block">Отметьте аккаунты из каких соци. сетей нужно собирать.</p>
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
		  	url: "/modules/all_social_net.php",
			data: {
				group_id: $('textarea[name=group_id]').val(),
				skype: $('input[name=skype]').is(':checked'),
				facebook: $('input[name=facebook]').is(':checked'),
				twitter: $('input[name=twitter]').is(':checked'),
				livejournal: $('input[name=livejournal]').is(':checked'),
				instagram: $('input[name=instagram]').is(':checked'),
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