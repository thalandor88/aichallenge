<?php
require_once('mysql_login.php');
require_once('server_info.php');

if($_GET['api_create_key'] != $server_info["api_create_key"]){
  header('HTTP/1.0 401 Unauthorized');
  die();
}

$ip = $_SERVER['REMOTE_ADDR'];
$check_sql = "select api_key from worker where ip_address = '".$ip."';";
$check_result = mysqli_query($db_link, $check_sql);
if ($check_result && mysqli_num_rows($check_result) != 0){
  $row = mysqli_fetch_row($check_result);
  $new_key = $row[0];
} else {
  $new_key = md5(uniqid(null,true).rand());

  $insert_sql = "insert into worker SET api_key = '".mysqli_real_escape_string($db_link, $new_key)."', ip_address = '".$ip."';";
  $success = mysqli_query($db_link, $insert_sql);
  if(!$success){
    echo("# ".mysqli_error($db_link));
    die('#Failed to create worker key');
  }
}

$api_url = "http://".$_SERVER['SERVER_NAME'] . dirname($_SERVER['SCRIPT_NAME']);

?>
curl '<?php echo $api_url?>worker_init.py' > /root/worker_init.py
python /root/worker_init.py <?php echo $api_url.' '.$new_key ?> $@

