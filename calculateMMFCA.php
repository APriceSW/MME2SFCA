<?php
ini_set('display_errors', true);
$public_data = array('status'=>false, 'message'=>'Unknown Error', 'tablename'=>'no table', 'catchment'=>'0');
require_once 'db_connect.php';
$db_connection = new Postgres();

//Set variables to inputs from calculation page. 
$ODTable = $_GET['ODTableName'];
$SupTable = $_GET['SUPTableName'];
$DemTable = $_GET['DemTableName'];
$MeasurementType = $_GET['MeasurementTypeValue'];
$TimeCatchmentSize = $_GET['TimeCatchmentSizeValue'];
$DistanceCatchmentSize = $_GET['DistanceCatchmentSizeValue'];
if ($MeasurementType == "duration" ){
    $CatchmentSize = $TimeCatchmentSize;
} else{
    $CatchmentSize = $DistanceCatchmentSize;
}
$DecayType = $_GET['DecayTypeValue'];
$BusPercentage = $_GET['BusPercentageValue'];
$CarPercentage = $_GET['CarPercentageValue'];
$BikePercentage = $_GET['BikePercentageValue'];
$WalkPercentage = $_GET['WalkPercentageValue'];
$SplitOption = $_GET['SelectedSplitOption'];

//Generate column names for scores in demand table. 
$decaynospace = preg_replace("/[\s-]+/", "_", $DecayType);
$FCAPublicResultName =  strtolower($decaynospace . "_" . $CatchmentSize . "_public_" . $MeasurementType . "_MM");
$FCAPrivateResultName =  strtolower($decaynospace . "_" . $CatchmentSize . "_private_" . $MeasurementType . "_MM");
$FCABikeResultName =  strtolower($decaynospace . "_" . $CatchmentSize . "_bike_" . $MeasurementType . "_MM");
$FCAWalkResultName =  strtolower($decaynospace . "_" . $CatchmentSize . "_walk_" . $MeasurementType . "_MM");
$CalculationName = strtolower($decaynospace . "_" . $CatchmentSize . "_" . $MeasurementType . "_MM");

//Set the decay function. 
$public_data['status'] = $db_connection->set_decay($SupTable, $DecayType);

//Exectue MME2SFCA step 1 calculation. 
$public_data['status'] = $db_connection->MM_FCA_step_one($SupTable, $ODTable, $CatchmentSize, $DecayType, $BusPercentage, $CarPercentage, $BikePercentage, $WalkPercentage, $MeasurementType, $SplitOption);

//Execute MME2SFCA setp 2 calculation.
$public_data['status'] = $db_connection->MM_FCA_step_two($SupTable, $ODTable, $CatchmentSize, $DemTable, $FCAPublicResultName, $FCAPrivateResultName, $FCABikeResultName, $FCAWalkResultName, $DecayType, $MeasurementType);

//Add the scores to the demand areas. 
$public_data['status'] = $db_connection->MM_add_fca_to_demand($DemTable, $FCAPublicResultName, $FCAPrivateResultName, $FCABikeResultName, $FCAWalkResultName);

//Add the demand areas scores to the represented polygons. 
$public_data['status'] = $db_connection->add_to_polygon($CalculationName, $DemTable, $FCAPublicResultName, $FCAPrivateResultName, $FCABikeResultName, $FCAWalkResultName);

//Update the calculation details table with new calculation. 
$public_data['status'] = $db_connection->create_calculation_details($CalculationName, $ODTable, $MeasurementType, $CarPercentage, $BusPercentage, $BikePercentage, $WalkPercentage, $CatchmentSize, $DecayType);

//for testing remove when finished. 
ob_clean();


//Check if the status is successful if it is get rid of any default error messages
if ($public_data['status'] === true)
    unset($public_data['message']);
    unset($public_data['tablename']);
$public_data['status'] = true;
$public_data['tablename'] = $CalculationName;
$public_data['catchment'] = $CatchmentSize;

header('Content-Type: application/json');
echo json_encode($public_data);
