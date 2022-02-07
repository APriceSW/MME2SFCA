<?php
ini_set('display_errors', true);
require_once 'db_connect.php';
$db_connection = new Postgres();

$public_data = array('status'=>false,'message'=>'Unknown Error');

//Truncate the journey times and distances. 
$public_data['status'] = ($db_connection->truncate_record());
if ($public_data['status'] === true)
    unset($public_data['message']);

header('Content-Type: application/json');
echo json_encode($public_data);