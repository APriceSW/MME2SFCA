<?php

ini_set('display_errors', true);
//Preconfigure the variables
$public_data = array('status'=>false, 'message'=>'Unknown Error');

//Include the database object file and create an instance
require_once 'db_connect.php';
$db_connection = new Postgres();

//Execute filter distances function.
$public_data['status'] = $db_connection->filter_shortest();

//Check if the status is successful if it is get rid of any default error messages
if ($public_data['status'] === true)
    unset($public_data['message']);

header('Content-Type: application/json');
echo json_encode($public_data);


