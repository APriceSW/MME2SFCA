<?php
ini_set('display_errors', true);
//Preconfigure the variables
$public_data = array('status' => false, 'message' => 'Unknown Error');

//Include the database object file and create an instance
require_once 'db_connect.php';
$db_connection = new Postgres();

$SchemaName = $_GET['Schema'];

$tables = $db_connection->get_tables($SchemaName);

if ($tables !== false) {
    unset($public_data['message']);
    $public_data['status'] = true;
    $public_data['results'] = $tables;
}

header('Content-Type: application/json');
echo json_encode($public_data);
