<?php
/*
-- sample sql script to populate database for demo

create table if not exists country
( country_id int unsigned not null auto_increment primary key
, country_name varchar(255)
) character set utf8 collate utf8_general_ci;

insert into country(country_name) values ('Canada'), ('United States'), ('Mexico');

create table if not exists market
( market_id int unsigned not null auto_increment primary key
, market_name varchar(255)
, photo varchar(255)
, contact_email varchar(255)
, country_id int unsigned
, is_active tinyint(1)
, create_date date
, notes text
) character set utf8 collate utf8_general_ci;

insert into market(market_name, contact_email, country_id, is_active, create_date, notes) values
('Great North', 'jane@superco.com', 1, 1, curdate(), 'nothing new'),
('The Middle', 'sue@superco.com', 2, null, '2001-01-01', 'these are notes'),
('Latin Market', 'john@superco.com', 1, 1, '1999-10-31', 'expanding soon');

*/

error_reporting(E_ALL);

// speed things up with gzip plus ob_start() is required for csv export
if(!ob_start('ob_gzhandler'))
	ob_start();

header('Content-Type: text/html; charset=utf-8');

include('lazy_mofo.php');

echo "
<!DOCTYPE html>
<html>
<head>
	<meta charset='UTF-8'>
	<link rel='stylesheet' type='text/css' href='style.css'>
</head>
<body>
<link rel='stylesheet' href='//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css'>
<script src='//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js'></script>
<script src='//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js'></script>
<script>
$(function() {

	// note how field class names are prefixed with 'lm_'
	$('.lm_search_input_date').datepicker();
	$('.lm_date').datepicker();

	// non US date example dd/mm/yy and week starting on monday(1) instead of sunday(0)
	// make sure lazy mofo class members for date_out and datetime_out correspond with your local date format
	//$('input.lm_create_date').datepicker({ dateFormat: 'dd/mm/yy', firstDay: 1 });


});
</script>


";


// enter your database host, name, username, and password
include('local.db.php');


// connect with pdo
try {
	$dbh = new PDO("mysql:host=$db_host;dbname=$db_name;", $db_user, $db_pass);
}
catch(PDOException $e) {
	die('pdo connection error: ' . $e->getMessage());
}


// create LM object, pass in PDO connection
$lm = new lazy_mofo($dbh);


// table name for updates, inserts and deletes
$lm->table = 'screening_master';


// identity / primary key for table
$lm->identity_name = 'id_screen';


// optional, make friendly names for fields
#$lm->rename['country_id'] = 'Country';


// optional, define input controls on the form
#$lm->form_input_control['photo'] = '--image';
#$lm->form_input_control['is_active'] = "select 1, 'Yes' union select 0, 'No' union select 2, 'Maybe'; --radio";
#$lm->form_input_control['country_id'] = 'select country_id, country_name from country; --select';


// optional, define editable input controls on the grid
$lm->grid_input_control['status'] = '--text';
#$lm->grid_input_control['contamination_type'] = '--text';
#$lm->grid_input_control['target_id'] = '--text';
$lm->grid_input_control['screener'] = '--text';
#$lm->grid_input_control['surface_wind'] = '--text';
#lm->grid_input_control['wind_direction'] = '--text';
#$lm->grid_input_control['approximate_sampling_time'] = '--text';
#$lm->grid_input_control['target_speed'] = '--text';
#$lm->grid_input_control['ground_heading'] = '--text';
$lm->grid_input_control['download'] = '--checkbox';
#$lm->grid_input_control['comments'] = '--text';


// optional, define output control on the grid
$lm->form_input_control['download'] = '--checkbox';
$lm->form_input_control['comments'] = '--textarea';
#$lm->grid_output_control['contact_email'] = '--email'; // make email clickable
#$lm->grid_output_control['photo'] = '--image'; // image clickable


// new in version >= 2015-02-27 all searches have to be done manually
$lm->grid_show_search_box = true;

$_new_search1 = $lm->clean_out(@$_REQUEST['_new_search1']);
$_new_search2 = $lm->clean_out(@$_REQUEST['_new_search2']);
$_new_search3 = $lm->clean_out(@$_REQUEST['_new_search3']);
$_new_search4 = $lm->clean_out(@$_REQUEST['_new_search4']);

// define our own search form with two inputs instead of the default one
$lm->grid_search_box = "
<form class='lm_search_box'>
	Radar:<input type='text' name='_new_search1' value='$_new_search1' size='20' class='lm_search_input'>
	Season:<input type='text' name='_new_search4' value='$_new_search4' size='20' class='lm_search_input'>
	Date_from:<input type='text' name='_new_search2' value='$_new_search2' size='20' class='lm_search_input_date' id='datepicker'>
	Date_to:<input type='text' name='_new_search3' value='$_new_search3' size='20' class='lm_search_input_date'>
	<input type='submit' value='Search' class='lm_search_button'>
	<input type='hidden' name='action' value='search'>
</form>

";

$lm->query_string_list = "_new_search1,_new_search2,_new_search3,_new_search4"; // add variable names to querystring so search is perserved when paging, sorting, and editing.

// optional, query for grid(). LAST COLUMN MUST BE THE IDENTITY for [edit] and [delete] links to appear
$lm->grid_sql = "
select
  r.id_screen,
  r.radar,
  r.date,
  r.season,
  r.status,
  r.contamination_type,
  r.target_id,
  r.screener,
  r.surface_wind,
  r.wind_direction,
  r.approximate_sampling_time,
  r.target_speed,
  r.ground_heading,
  r.comments,
  r.download,
  r.downloaded,
  r.modified,
	r.id_screen
from screening_master r
where upper(coalesce(r.radar, '')) like upper(:_new_search1)
and r.date>= str_to_date(:_new_search2,'%m/%d/%Y')
and r.date<= str_to_date(:_new_search3,'%m/%d/%Y')
and upper(coalesce(r.season, '')) like upper(:_new_search4)
order by r.radar,r.date desc
";
//echo "test".is_null(@$_REQUEST['_new_search2'],'01/01/1970');
$str1=strlen(trim(@$_REQUEST['_new_search2']))>0 ? trim(@$_REQUEST['_new_search2']) : '01/01/1970';
//echo "str1".$str1;
$str2=strlen(trim(@$_REQUEST['_new_search3']))>0 ? trim(@$_REQUEST['_new_search3']) : '12/12/2050';
//echo "str2".$str2;
$lm->grid_sql_param[':_new_search1'] = '%' . trim(@$_REQUEST['_new_search1']) . '%';
$lm->grid_sql_param[':_new_search4'] = '%' . trim(@$_REQUEST['_new_search4']) . '%';
$lm->grid_sql_param[':_new_search2'] = $str1;
$lm->grid_sql_param[':_new_search3'] = $str2;

//echo $lm->grid_sql;
// optional, define what is displayed on edit form. identity id must be passed in also.
if(null!=(@$_REQUEST[$lm->identity_name])){
$lm->form_sql = "
select
  r.id_screen,
  r.season,
  r.status,
  r.contamination_type,
  r.target_id,
  r.screener,
  r.surface_wind,
  r.wind_direction,
  r.approximate_sampling_time,
  r.target_speed,
  r.ground_heading,
  r.comments,
  r.download
from  screening_master r
where r.id_screen = :id_screen
";
}
else{
	$lm->form_sql = "
	select
	id_screen,
	radar,
	date,
	season,
	  status,
	  contamination_type,
	  target_id,
	  screener,
	  surface_wind,
	  wind_direction,
	  approximate_sampling_time,
	  target_speed,
	  ground_heading,
	  comments,
	  download
	from  screening_master
	where id_screen = :id_screen
	";
}

$lm->form_sql_param[":$lm->identity_name"] = @$_REQUEST[$lm->identity_name];


// optional, validation. input:  regular expression (with slashes), error message, tip/placeholder
// first element can also be a user function or 'email'
#$lm->on_insert_validate['market_name'] = array('/.+/', 'Missing Market Name', 'this is required');
#$lm->on_insert_validate['contact_email'] = array('email', 'Invalid Email', 'this is optional', true);


// copy validation rules to update - same rules
#S$lm->on_update_validate = $lm->on_insert_validate;


// use the lm controller
$lm->run();


echo "</body></html>";
