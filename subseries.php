<?php
include('local.db.php');

$radar= trim($_GET['radar']);
$season= trim($_GET['season']);
$from= trim($_GET['from']);
$to= trim($_GET['to']);
//echo $radar.$season.$from.$to;
// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$from_d=strtotime($from);
$to_d=strtotime($to);
while (strtotime($from) <= strtotime($to)) {
               //echo "$from\n";
               $from = date ("Y-m-d", strtotime("+1 day", strtotime($from)));
               $sql = "INSERT INTO screening_master (radar, season, date)
               VALUES ('".$radar."','". $season."','".  $from."')";
               //echo $sql;
               if (mysqli_query($conn, $sql)) {
                   //echo "New record created successfully";
               } else {
                   echo "Error: " . $sql . "<br>" . mysqli_error($conn);
               }
}
echo "<a href='screen.php'> Back to screen</a>";

mysqli_close($conn);
?>
