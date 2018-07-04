<?
	function show_error($error_txt){?>
<script>$("#progress").hide();</script><section class="content">
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