<?php
ini_set('display_errors', true);
//Preconfigure the variables
$public_data = array('status'=>false, 'message'=>'Unknown Error');

//Include the database object file and create an instance
require_once 'db_connect.php';
$db_connection = new Postgres();

$table = $_GET['table'];
$fields = $_GET['fields'];

$fieldstr = "";
//Multiply by 1000 as its per 1000 persons (more readable than 2.0338423e05 etc). 
foreach ($fields as $i => $field){
    $fieldstr = $fieldstr . "$field*1000, ";
}

//add on the geometry field, to be returned as geojson in WGS84
$fieldstr = $fieldstr . "ST_AsGeoJSON(ST_Transform(geom,4326),4)";


//construct final SQL select statement
$sql = "SELECT $fieldstr FROM $table";

//echo $sql;
$public_data['status'] = $db_connection->get_mapLayers($sql);

//Check if the status is successful if it is get rid of any default error messages
if ($public_data['status'] === true)
    unset($public_data['message']);
    unset($public_data['tablename']);

//header('Content-Type: application/json');
//echo json_encode($public_data);
