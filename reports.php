<?
$fname = isset($_GET['page']) ? $_GET['page'] : null;
if (!empty($fname)){
	<a href="/main.php?m=reports">&larr; История запростов</a>
<?
?>
<script>
$( document ).ready(function() {
$('#ca_data').DataTable({
	"pageLength": 50,
    dom: 'T<"clear">lfrtip',
    "tableTools": {
    "sSwfPath": "/js/dataTables/tabletools/swf/copy_csv_xls.swf",
    "aButtons": ['copy', {"sExtends": "xls", "sButtonText": "CSV"}]
    }
});
});
</script>
<?}?>