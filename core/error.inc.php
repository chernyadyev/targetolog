<?
	function show_error($error_txt){
<script>$("#progress").hide();</script>
	<div class="row">
		<div class="col-md-12">

           	<div class="box">
                <div class="box-body">
                <?=$error_txt?>
                </div>
            </div>

		</div>
	</div>
</section>
<?
	die();
	}
?>