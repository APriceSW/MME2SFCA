<?php
ini_set('display_errors', true);
$public_data = array('status'=>false, 'message'=>'Unknown Error');

//Connect to the database
require_once 'db_connect.php';
$db_connection = new Postgres();

//Set variables to values passed from the OD Matrix Builder Page. 
$sldValue = $_GET['sldVal'];
$SUPTable = $_GET['SupTable'];
$SUPId = $_GET['SupId'];
$SUPGeom = $_GET['SupGeom'];
$SUPVol = $_GET['SupVol'];
$DEMTable = $_GET['DemTable'];
$DEMId = $_GET['DemId'];
$DEMGeom = $_GET['DemGeom'];
$DEMVol = $_GET['DemVol'];
$AGERanges = $_GET['AgeRanges'];

//Create an OD matrix using the parameters set by the user. 
$public_data['status'] = $db_connection->create_OD($sldValue, $SUPTable, $SUPId, $SUPGeom, $SUPVol, $DEMTable, $DEMId, $DEMGeom, $DEMVol, $AGERanges);

//Check if the status is successful if it is get rid of any default error messages
if ($public_data['status'] === true)
    unset($public_data['message']);
    $public_data['status'] = true;

//ob_clean();
header('Content-Type: application/json');
echo json_encode($public_data);
