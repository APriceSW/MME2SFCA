<?php
ini_set('display_errors', true);
$public_data = array('status'=>false, 'message'=>'Unknown Error');
require_once 'db_connect.php';
$db_connection = new Postgres();

$od_matrix = $_GET['od_matrix'];

//Execute function to join the distances and times to OD matrix. 
$public_data['status'] = $db_connection->join_tables($od_matrix);


//Check if the status is successful if it is get rid of any default error messages
if ($public_data['status'] !== false)
    unset($public_data['message']);
$public_data['status'] = true;


header('Content-Type: application/json');
echo json_encode($public_data);
