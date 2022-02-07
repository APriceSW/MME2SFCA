<?php
require_once 'db_connect.php';
$db_connection = new Postgres();

//Response information. 
$public_data = array('status'=>false,'message'=>'Unknown Error');

//Check if the variables have been passed through including:
//-- Transport type
//-- Starting location lat
//-- Starting location long
//-- End location lat
//-- Eng location lon
if (isset($_POST['transport_type'],$_POST['start_lat'],$_POST['start_lon'],$_POST['end_lat'],$_POST['end_lon'])) {
	// Set variables to those values. 
    $public_data['transport_type'] = $transport_type = $_POST['transport_type'];
    $public_data['start_lat'] = $start_lat = filter_var($_POST['start_lat'], FILTER_VALIDATE_FLOAT);
    $public_data['start_lon'] = $start_lon = filter_var($_POST['start_lon'], FILTER_VALIDATE_FLOAT);
    $public_data['end_lat'] = $end_lat = filter_var($_POST['end_lat'], FILTER_VALIDATE_FLOAT);
    $public_data['end_lon'] = $end_lon = filter_var($_POST['end_lon'], FILTER_VALIDATE_FLOAT);

	/*
	Set the url to the location of the OpenTripPlanner server. If on the same machine change to:
	localhost:8082 but this can be configued to other ports when initiating OTP. 
	*/
    $url = "/* inset address and port for OTP here*/";
	
	//Build full URL to query REST API based on inputs. 
    $results = build_query_otp($transport_type, $db_connection, $url, $start_lat , $start_lon , $end_lat, $end_lon);

	//Check to see if result contained a journey. 
    if (!empty($results)) {
        $public_data['status'] = $results['status'];
		//Check number of results returned. 
        if (isset($results['value']['plan'],$results['value']['plan']['itineraries']))
            $public_data['message'] = count($results['value']['plan']['itineraries']) . ' result(s) returned';
        elseif (isset($results['message']))
			//State error. 
            $public_data['message'] = $results['message'];
        elseif (is_array($results))
			//Respond with number of journies returned. 
            $public_data['message'] = count($results['value']) . ' cached result(s) returned';
    }
}
else{
	//If variables missing from input then return error message. 
    $public_data['message'] = 'Required Variables Missing';
}

header('Content-Type: application/json');
echo json_encode($public_data);
/**
 * @param Postgres $db_connection
 * @param string $url
 * @param double $start_lat
 * @param double $start_lon
 * @param double $end_lat
 * @param double $end_lon
 * @return bool|mixed|null
 */
function build_query_otp($transport_type, $db_connection, $url, $start_lat , $start_lon , $end_lat, $end_lon)
{
    $records = $db_connection->get_records($transport_type, $start_lat,$start_lon,$end_lat,$end_lon);
    if (count($records) !== 0)
        return array('status'=>true, 'value'=>$records);

    $JourneyEndCoord = $end_lat . "," . $end_lon;
    $JourneyStartCoord = $start_lat . "," . $start_lon;

	/*
	To be replaced with builder or factory pattern. 
	Currently only has 4 modes and the times and dates were fixed. 
	Ideally this should be generated with additional inputs. 
	Included examples of changing the routing graph if need to have more than one set of transport/road network dataset. 
	*/
    if ($transport_type == "public"){
        //$query = '/otp/routers/default/plan?fromPlace=' . $JourneyStartCoord . '&toPlace=' . $JourneyEndCoord . '&arriveBy=11:00am&date=08-29-2021&mode=TRANSIT,WALK&maxWalkDistance=400';
        $query = '/otp/routers/onlybus/plan?fromPlace=' . $JourneyStartCoord . '&toPlace=' . $JourneyEndCoord . '&arriveBy=11:00am&date=08-24-2021&mode=TRANSIT,WALK&maxWalkDistance=400';
        //$query = '/otp/routers/original/plan?fromPlace=' . $JourneyStartCoord . '&toPlace=' . $JourneyEndCoord . '&arriveBy=11:00am&date=08-20-2019&mode=TRANSIT,WALK&maxWalkDistance=400';
    } elseif($transport_type == "private"){
        //$query = '/otp/routers/default/plan?fromPlace=' . $JourneyStartCoord . '&toPlace=' . $JourneyEndCoord . '&time=11:00am&date=08-29-2021&mode=CAR';
        $query = '/otp/routers/onlybus/plan?fromPlace=' . $JourneyStartCoord . '&toPlace=' . $JourneyEndCoord . '&time=11:00am&date=08-24-2021&mode=CAR';
        //$query = '/otp/routers/original/plan?fromPlace=' . $JourneyStartCoord . '&toPlace=' . $JourneyEndCoord . '&time=11:00am&date=08-20-2019&mode=CAR';
    }elseif($transport_type == "cycling"){
        //$query = '/otp/routers/default/plan?fromPlace=' . $JourneyStartCoord . '&toPlace=' . $JourneyEndCoord . '&time=11:00am&date=08-29-2021&mode=BICYCLE';
        $query = '/otp/routers/onlybus/plan?fromPlace=' . $JourneyStartCoord . '&toPlace=' . $JourneyEndCoord . '&time=11:00am&date=08-24-2021&mode=BICYCLE';
        //$query = '/otp/routers/original/plan?fromPlace=' . $JourneyStartCoord . '&toPlace=' . $JourneyEndCoord . '&time=11:00am&date=08-20-2019&mode=BICYCLE';
    }elseif($transport_type == "walking"){
        //$query = '/otp/routers/default/plan?fromPlace=' . $JourneyStartCoord . '&toPlace=' . $JourneyEndCoord . '&time=11:00am&date=08-29-2021&mode=WALK';
        $query = '/otp/routers/onlybus/plan?fromPlace=' . $JourneyStartCoord . '&toPlace=' . $JourneyEndCoord . '&time=11:00am&date=08-24-2021&mode=WALK';
        //$query = '/otp/routers/original/plan?fromPlace=' . $JourneyStartCoord . '&toPlace=' . $JourneyEndCoord . '&time=11:00am&date=08-20-2019&mode=WALK';
		
    }else{
		//If incorrect transport selected then state no query. 
        echo "No query";
    }
	
	//Set the result to query response. 
    $result = run_query_otp($url, $query);
	
	//Set a return variable NULL default incase response data incorrect. 
    $return = null;
    if (isset($result)) {
        $resultarray = isset($result) ? $result : [];

		//if no path found result in no path found. 
        if (isset($resultarray['error'], $resultarray['error']['noPath']) && $resultarray['error']['noPath'])
            return array('status'=>false, 'message'=>'No Path Found');

		//Select route information. 
        $routeinfo = [$resultarray['requestParameters']['date'], $resultarray['requestParameters']['mode'], $resultarray['plan']['date'], $resultarray['requestParameters']['toPlace']];

		
        $totalDistances = array();
        $minimumDistance = PHP_FLOAT_MAX;
        $minimumIndex = -1;
        $maximumDistance = 0;
        $maximumIndex = -1;

        foreach ($resultarray['plan']['itineraries'] as $key => $itinerary) {
			//Fill journey related variabels with itinerary details. 
			//convert journey times to minutes and convert transit times to minutes. 
            $totalDistances[$key] = 0;
            $duration = $itinerary['duration'] / 60;
            $transit_time = $itinerary['transitTime'] / 60;
            $waiting_time = $itinerary['waitingTime'];
            $transfers = $itinerary['transfers'];

			//Sum the journey distances into a total distance value. 
            foreach ($itinerary['legs'] as $leg) {
                $distance = $leg['distance'];
                $totalDistances[$key] += $distance;
            }
            
			//Insert data into related journey distance and time tables. 
            $db_connection->insert_record($transport_type, $start_lat , $start_lon , $end_lat, $end_lon,  round($totalDistances[$key]), $duration, $transit_time, $waiting_time, $transfers);

			
            if ($totalDistances[$key] > $maximumDistance) {
                $maximumDistance = $totalDistances[$key];
                $maximumIndex = $key;

            }
            if ($totalDistances[$key] < $minimumDistance) {
                $minimumDistance = $totalDistances[$key];
                $minimumIndex = $key;
            }
        }
    }

	//Return true status 
    return array('status'=>true, 'value'=>$return);
}

//Query the rest API and cURL the response. 
/**
 * @param string $url
 * @param string $query
**/
function run_query_otp($url, $query)
{
	//Initialise cURL session. 
    $ch = curl_init();
	//Set options. 
    curl_setopt($ch, CURLOPT_URL, $url . $query);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, -1);
    
	//Execute session and set variable to content of query. 
	$contents = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error_code = curl_errno($ch);
    $error_message = curl_error($ch);

	//Close cURL session. 
    curl_close($ch);

	//Set variable to response, decoding cURL response from JSON format to variable. 
    $result = json_decode($contents, true);
	
	//Return the journey info. 
    return $result;
}

