<form class='lm_search_box' method='get' action='subseries.php'>
	Radar:<input type='text' name='radar' size='20' class='lm_search_input'>
  	Season:<input type='text' name='season'  size='20' class='lm_search_input'>
	Date_from:<input type='text' name='from'  size='20' class='lm_search_input_date' id='datepicker'>
	Date_to:<input type='text' name='to'  size='20' class='lm_search_input_date'>
	<input type='submit' value='Submit' class='lm_search_button'>
	<input type='hidden' name='action' value='search'>
</form>
<link rel='stylesheet' href='//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css'>
<script src='//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js'></script>
<script src='//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js'></script>
<script>
$(function() {

	// note how field class names are prefixed with 'lm_'
	$('.lm_search_input_date').datepicker();

	// non US date example dd/mm/yy and week starting on monday(1) instead of sunday(0)
	// make sure lazy mofo class members for date_out and datetime_out correspond with your local date format
	//$('input.lm_create_date').datepicker({ dateFormat: 'dd/mm/yy', firstDay: 1 });


});
</script>
