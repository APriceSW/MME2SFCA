<!DOCTYPE html>
<html>
<head>
<!-- Includes for the style sheets and other important extensions -->
<link rel="stylesheet" type="text/css" href="FCAStyle.css">
<script src="/staff/leaflet-dev/jquery-3.4.1.min.js"></script>

    <title>Journey Calc</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <link rel="stylesheet" type="text/css" href="stylesheets/JourneyCalc.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>

<?php
set_time_limit(0);

ini_set('MAX_EXECUTION_TIME', -1);
ini_set('display_errors', true);

ini_set('xdebug.var_display_max_depth', 50); //avoids '...'
ini_set('xdebug.var_display_max_children', 512); //avoids 'more elements...'

require_once 'db_connect.php';
$db_connection = new Postgres();

$tableName = $_POST["selectedtable"];
$JourneyList = $db_connection->get_OD_matrix($tableName);
$SchemaName = "ODmatrices";
$tables = $db_connection->get_tables($SchemaName);
$filter = $db_connection->truncate_records();
$ODtables = $db_connection->get_tables_in_date_order_recent();
?>


<!-- Page title and navigation -->
<div class="container-fluid text-center" Style="background-color: #4b6da3; color: white; padding-top: 15px; padding-bottom: 15px; width: 100%; ">
    <h1>Journey Calculator</h1>
</div>
<ul >
    <li><a class="active" href="index.php" ><img src="menuicons/home-colour.png" width="25" height="25"> Home</a></li>
    <li><a class="active" href="FacilityViewer.php" ><img src="menuicons/map-colour.png" width="25" height="25"> View Facilties</a></li>
    <li><a class="active" href="SetMMTable.php"><img src="menuicons/calculator-colour.png" width="25" height="25"> Accessibility Calculator</a></li>
    <li><a class="active" href="SetMapOutput.php"><img src="menuicons/results-colour.png" width="25" height="25"> View Results</a></li>

    <li style="float:right"><a href="index.php"><img src="menuicons/help.png" width="25" height="25"> Help</a></li>
    <li style="float:right"><a class="ODMatrixBuilder.php" href="index.php"><img src="menuicons/settings-colour.png" width="25" height="25"> Setup</a></li>
</ul>
<!-- Page title and navigation -->



<div class="container-fluid">
    <div class="row">

        <div class="col-sm-12 text-center">
            <h1 style="font-size:20px;color:black;text-align: center;">The following calculation will be performed with these parameters</h1>

                <table style="width:100%; height:20px; text-align:center;"  class="paleBlueRows">
                    <tr>
                        <th>Facility Type</th>
                        <th>Facility Table Version</th>
                        <th>Facility version Date</th>
                        <th>Geographic population level</th>
                        <th>Travel tolerance</th>
                        <th>Transport Network Build Date</th>
                        <th>Age Groups included</th>
                        <th>Date and Time Generated</th>
                    </tr>
                        <?php list($facilityType1,$facilityType2,$demandType1,$demandType2, $demandType3, $CatchmentSize) = explode('_', $tableName);?>
                        <tr >
                            <td ><?=$facilityType1 . ' ' . $facilityType2;?></td>
                            <td><?php $supplyversion = $db_connection->get_supply_version($tableName);?></td>
                            <td><?php $supplytabledate = $db_connection->get_supply_date($tableName);?></td>
                            <td ><?=$demandType1 . ' ' . $demandType2 . ' ' . $demandType3;?></td>
                            <td><?=$CatchmentSize . "m";?></td>
                            <td><?php $roadnetworkdate = $db_connection->get_roadnetwork_date($tableName);?></td>
                            <td><?php $agesgroups = $db_connection->get_population_ages($tableName);?></td>
                            <td><?php $tablecreationdate = $db_connection->get_datetime($tableName);?></td>
                        </tr>
                </table>

<!-- Journey query activation -->
<button hidden id="filterdistances" onclick="document.getElementById('filterpublic').click()" />Filter Calculating</button>
<button id="publicCalc" class="buttonr" onclick="document.getElementById('journey_0').click()" />Start Calculating</button>
<button hidden id="privCalc" onclick="document.getElementById('private_journey_0').click()" />Start Calculating Public</button>
<button hidden id="bikeCalc" onclick="document.getElementById('bike_journey_0').click()" />Start Calculating Bike</button>
<button hidden id="walkCalc" onclick="document.getElementById('walk_journey_0').click()" />Start Calculating walking</button>

<!-- Progress bars for each mode of transport -->
            <h1 style="font-size:20px">Public Transport Distance Progress:</h1>
            <div style=>Complete: <span id="complete">0</span> Remaining:<span id="remaining">0</span> Percent:<span id="percent">0%</span></div>
                <div id="publicProgress">
                    <div id="publicBar"></div>
                </div>

            <h1 style="font-size:20px">Private Transport Distance Progress:</h1>
            <div >Complete: <span id="priv_complete">0</span> Remaining:<span id="priv_remaining">0</span> Percent:<span id="priv_percent">0%</span></div>
                <div id="myProgress">
                    <div id="myBar"></div>
                </div>

            <h1 style="font-size:20px">Cycling Distance Progress:</h1>
                <div >Complete: <span id="bike_complete">0</span> Remaining:<span id="bike_remaining">0</span> Percent:<span id="bike_percent">0%</span></div>
                <div id="bikeProgress">
                    <div id="bikeBar"></div>
                </div>

            <h1 style="font-size:20px">Walking Distance Progress:</h1>
                <div >Complete: <span id="walk_complete">0</span> Remaining:<span id="walk_remaining">0</span> Percent:<span id="walk_percent">0%</span></div>
                <div id="walkingProgress">
                    <div id="walkingBar"></div>
                </div>

		<!-- Filtering public transport button and information. -->
		<button hidden class="button" id="filterpublic" onclick="filter_distances(this)">Filter Distances</button>
		<h1 style="font-size:20px">Public Transport Filtering Progress:</h1>
		<div id="filterspace" class="pageform"></div>

		<!-- Joining distances to OD matrix activation and information.  -->
		<button hidden class="button" id="joindistances" onclick="join_tables(this) ">Add Results to OD Matrix</button>
		<h1 style="font-size:20px">Distances and Times Joining Progress:</h1>
		<div id="joiningspace" class="pageform"></div>
        <br><br><br><br>

        </div>
    </div>
</div>
<!--Buttons for public transport-->
<table hidden>
    <thead>
    <tr>
        <th>Start Lat</th>
        <th>Start Lon</th>
        <th>End Lat</th>
        <th>End Lon</th>
        <th>Options</th>
    </tr>
    </thead>
    <tbody>

    <?php foreach ($JourneyList as $key=>$journey):?>
        <tr >
            <td ><?=$journey['start_lat'];?></td>
            <td><?=$journey['start_lon'];?></td>
            <td><?=$journey['end_lat'];?></td>
            <td><?=$journey['end_lon'];?></td>
            <td><button id='journey_<?=intval($key);?>' class='pending journey' onclick="get_public_results(this,'<?=$journey['start_lat'];?>','<?=$journey['start_lon'];?>','<?=$journey['end_lat'];?>','<?=$journey['end_lon'];?>')" disabled="disabled">Generate</button></td>

        </tr>
    <?php endforeach; ?>

    </tbody>
</table name = "table1">


<!--Buttons for private transport-->
<table hidden>
    <thead>
    <tr>
        <th>Start Lat</th>
        <th>Start Lon</th>
        <th>End Lat</th>
        <th>End Lon</th>
        <th>Options</th>
    </tr>
    </thead>
    <tbody>

    <?php foreach ($JourneyList as $key=>$journey):?>
        <tr >
            <td ><?=$journey['start_lat'];?></td>
            <td><?=$journey['start_lon'];?></td>
            <td><?=$journey['end_lat'];?></td>
            <td><?=$journey['end_lon'];?></td>
            <td><button id='private_journey_<?=intval($key);?>' class='pending journey' onclick="get_results(this,'<?=$journey['start_lat'];?>','<?=$journey['start_lon'];?>','<?=$journey['end_lat'];?>','<?=$journey['end_lon'];?>')" disabled="disabled">Generate</button></td>

        </tr>
    <?php endforeach; ?>

    </tbody>
</table hidden name = "table2">


<!--Buttons for Cycling -->
<table hidden >
    <thead>
    <tr>
        <th>Start Lat</th>
        <th>Start Lon</th>
        <th>End Lat</th>
        <th>End Lon</th>
        <th>Options</th>
    </tr>
    </thead>
    <tbody>

    <?php foreach ($JourneyList as $key=>$journey):?>
        <tr >
            <td ><?=$journey['start_lat'];?></td>
            <td><?=$journey['start_lon'];?></td>
            <td><?=$journey['end_lat'];?></td>
            <td><?=$journey['end_lon'];?></td>
            <td><button id='bike_journey_<?=intval($key);?>' class='pending journey' onclick="get_bike_results(this,'<?=$journey['start_lat'];?>','<?=$journey['start_lon'];?>','<?=$journey['end_lat'];?>','<?=$journey['end_lon'];?>')" disabled="disabled">Generate</button></td>

        </tr>
    <?php endforeach; ?>

    </tbody>
</table hidden name = "table3">


<!--Buttons for Walking -->
<table hidden >
    <thead>
    <tr>
        <th>Start Lat</th>
        <th>Start Lon</th>
        <th>End Lat</th>
        <th>End Lon</th>
        <th>Options</th>
    </tr>
    </thead>
    <tbody>

    <?php foreach ($JourneyList as $key=>$journey):?>
        <tr >
            <td ><?=$journey['start_lat'];?></td>
            <td><?=$journey['start_lon'];?></td>
            <td><?=$journey['end_lat'];?></td>
            <td><?=$journey['end_lon'];?></td>
            <td><button id='walk_journey_<?=intval($key);?>' class='pending journey' onclick="get_walk_results(this,'<?=$journey['start_lat'];?>','<?=$journey['start_lon'];?>','<?=$journey['end_lat'];?>','<?=$journey['end_lon'];?>')" disabled="disabled">Generate</button></td>

        </tr>
    <?php endforeach; ?>

    </tbody>
</table hidden name = "table4">

<!--Buttons deleting cache -->
<button hidden onclick="delete_cache(this)">Delete Cache</button>


<!-- The Modal -->
<div id="myModal" class="modal">

    <!-- Modal content -->
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>Distances for each mode of transport, to each facility have successfully been calculated<br>
            Click next to move on to accessibility calculation.</p>
        <button type="button" class="buttonr" onclick="document.location='SetMMTable.php'" style="vertical-align:middle"><span>Calculate Accessibility </span></button>
    </div>

</div>

</body>
</html>

<script>
    $('.journey').attr('disabled', false);
    //document.write(nextMode);

    // Get the modal
    var modal = document.getElementById("myModal");

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];


    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

	//Set the threads for each mode of transport for more than one to be run at once. 
    let max_threads = 5;
    let threads = [];
    let priv_max_threads = 10;
    let priv_threads = [];
    let bike_max_threads = 5;
    let bike_threads = [];
    let walk_max_threads = 5;
    let walk_threads = [];

	//Get the results for public transport using start coordinates and end coorinates from input buttons. 
	/**
    * @param string $start_lat
    * @param string $start_lon
    * @param string $end_lat
    * @param string $end_lon
    *
    */
    function get_public_results(sender,start_lat,start_lon,end_lat,end_lon){

		//Set the transport type. 
        let transport_type = "public";
        if (threads.length <= max_threads) {
			//prepare the data for POST
            let post_data = {
                transport_type: transport_type,
                start_lat: start_lat,
                start_lon: start_lon,
                end_lat: end_lat,
                end_lon: end_lon
            };

			//Get button id (hidden but used during development). 
            let id = sender.getAttribute('id');
            threads.push(id);
            $(sender).text('Pending').removeClass('pending');

			//POST data to query PHP file with parameters set in post_data. 
            $.post('query.php', post_data).done(function (data) {
                if (data.status === true) {
					//If returns a true message from query PHP file, change message.
                    $(sender).text(data.message);
                } else
                    $(sender).text(data.message);
            }).fail(function (data) {
				//If error set value to 'error'
                $(sender).text('Error');
            }).always(function (data) {
				//Disable the button input. 
                $(sender).attr('disabled', true);

                id = sender.getAttribute('id');
                Array.prototype.remove = function() {
                    var what, a = arguments, L = a.length, ax;
                    while (L && this.length) {
                        what = a[--L];
                        while ((ax = this.indexOf(what)) !== -1) {
                            this.splice(ax, 1);
                        }
                    }
                    return this;
                };
                threads.remove(id);

				//Update calculation progress for detail labels. 
                let complete = document.getElementById('complete');
                complete.textContent = parseInt(complete.textContent)+1;
                let remaining = document.getElementById('remaining');
                let percent = document.getElementById('percent');
                let elem = document.getElementById("publicBar");

                let $table = null;
                let $pending = null;
                $table = $(sender).closest('table');
                $pending = $table.find('.pending');
                remaining.textContent = ($pending.length);
				//Update progress bar. 

                percent.textContent = ((parseFloat(complete.textContent) / parseFloat(parseInt(complete.textContent)+parseInt(remaining.textContent))) * 100).toFixed(2) + '%';
                elem.style.width = ((parseFloat(complete.textContent) / parseFloat(parseInt(complete.textContent)+parseInt(remaining.textContent))) * 100).toFixed(2) + '%';
                //Update threads
				for (let i = 0; i <= max_threads - threads.length; i++) {
                    $table = $(sender).closest('table');
                    $pending = $table.find('.pending');
                    if (threads.length <= max_threads && $pending.length) {
                        id = $pending.get(0).getAttribute('id');
                        threads.push(id);
                        $pending.first().text('Pending').removeClass('pending').click();
                    }else{
                        document.getElementById('privCalc').click();
                        //document.getElementById('filterpublic').click(); --Uncomment to finish transport calculation early before moving onto next mode. 
                    }
                }
            });

        }

    }

	//Get the results for private transport using start coordinates and end coorinates from input buttons. 
	/**
    * @param string $start_lat
    * @param string $start_lon
    * @param string $end_lat
    * @param string $end_lon
    *
    */
    function get_results(sender,start_lat,start_lon,end_lat,end_lon){
        //Set the transport type. 
		let transport_type = "private";
        if (priv_threads.length <= priv_max_threads) {
			//prepare the data for POST
            let priv_post_data = {
                transport_type: transport_type,
                start_lat: start_lat,
                start_lon: start_lon,
                end_lat: end_lat,
                end_lon: end_lon
            };

			//Get button id (hidden but used during development). 
            let priv_id = sender.getAttribute('id');
            priv_threads.push(priv_id);
            $(sender).text('Pending').removeClass('pending');

			//POST data to query PHP file with parameters set in post_data. 	
            $.post('query.php', priv_post_data).done(function (data) {
                if (data.status === true) {
					//If returns a true message from query PHP file, change message.
                    $(sender).text(data.message);
                } else
                    $(sender).text(data.message);
            }).fail(function (data) {
				//If error set value to 'error'
                $(sender).text('Error');
            }).always(function (data) {
				//Disable the button input. 
                $(sender).attr('disabled', true);

                priv_id = sender.getAttribute('id');
                Array.prototype.remove = function() {
                    var what, a = arguments, L = a.length, ax;
                    while (L && this.length) {
                        what = a[--L];
                        while ((ax = this.indexOf(what)) !== -1) {
                            this.splice(ax, 1);
                        }
                    }
                    return this;
                };
                priv_threads.remove(priv_id);

				//Update calculation progress for detail labels. 
                let priv_complete = document.getElementById('priv_complete');
                priv_complete.textContent = parseInt(priv_complete.textContent)+1;
                let priv_remaining = document.getElementById('priv_remaining');
                let priv_percent = document.getElementById('priv_percent');
                let elem = document.getElementById("myBar");

                let $priv_table = null;
                let $priv_pending = null;
                $priv_table = $(sender).closest('table');
                $priv_pending = $priv_table.find('.pending');
                priv_remaining.textContent = ($priv_pending.length);
                priv_percent.textContent = ((parseFloat(priv_complete.textContent) / parseFloat(parseInt(priv_complete.textContent)+parseInt(priv_remaining.textContent))) * 100).toFixed(2) + '%';
                elem.style.width = ((parseFloat(priv_complete.textContent) / parseFloat(parseInt(priv_complete.textContent)+parseInt(priv_remaining.textContent))) * 100).toFixed(2) + '%';
                //Update threads
				for (let i = 0; i <= priv_max_threads - priv_threads.length; i++) {
                    $priv_table = $(sender).closest('table');
                    $priv_pending = $priv_table.find('.pending');
                    if (priv_threads.length <= priv_max_threads && $priv_pending.length) {
                        priv_id = $priv_pending.get(0).getAttribute('id');
                        priv_threads.push(priv_id);
                        $priv_pending.first().text('Pending').removeClass('pending').click();
                    }else {
                        document.getElementById('bikeCalc').click();
                        //document.getElementById('filterpublic').click(); --Uncomment to finish transport calculation early before moving onto next mode. 
                    }
                }
            });
        }
    }

	//Get the results for cycling using start coordinates and end coorinates from input buttons. 
	/**
    * @param string $start_lat
    * @param string $start_lon
    * @param string $end_lat
    * @param string $end_lon
    *
    */
    function get_bike_results(sender,start_lat,start_lon,end_lat,end_lon){
		
		//Set the transport type. 
        let transport_type = "cycling";
        if (bike_threads.length <= bike_max_threads) {
			//prepare the data for POST
            let bike_post_data = {
                transport_type: transport_type,
                start_lat: start_lat,
                start_lon: start_lon,
                end_lat: end_lat,
                end_lon: end_lon
            };

			//Get button id (hidden but used during development). 
            let bike_id = sender.getAttribute('id');
            bike_threads.push(bike_id);
            $(sender).text('Pending').removeClass('pending');

			//POST data to query PHP file with parameters set in post_data. 
            $.post('query.php', bike_post_data).done(function (data) {
                if (data.status === true) {
					//If returns a true message from query PHP file, change message.
                    $(sender).text(data.message);
                } else
                    $(sender).text(data.message);
            }).fail(function (data) {
				//If error set value to 'error'
                $(sender).text('Error');
            }).always(function (data) {
				//Disable the button input. 
                $(sender).attr('disabled', true);

                bike_id = sender.getAttribute('id');
                Array.prototype.remove = function() {
                    var what, a = arguments, L = a.length, ax;
                    while (L && this.length) {
                        what = a[--L];
                        while ((ax = this.indexOf(what)) !== -1) {
                            this.splice(ax, 1);
                        }
                    }
                    return this;
                };
                bike_threads.remove(bike_id);

				//Update calculation progress for detail labels. 
                let bike_complete = document.getElementById('bike_complete');
                bike_complete.textContent = parseInt(bike_complete.textContent)+1;
                let bike_remaining = document.getElementById('bike_remaining');
                let bike_percent = document.getElementById('bike_percent');
                let elem = document.getElementById("bikeBar");

                let $bike_table = null;
                let $bike_pending = null;
                $bike_table = $(sender).closest('table');
                $bike_pending = $bike_table.find('.pending');
                bike_remaining.textContent = ($bike_pending.length);
				//Update progress bar. 
				
                bike_percent.textContent = ((parseFloat(bike_complete.textContent) / parseFloat(parseInt(bike_complete.textContent)+parseInt(bike_remaining.textContent))) * 100).toFixed(2) + '%';
                elem.style.width = ((parseFloat(bike_complete.textContent) / parseFloat(parseInt(bike_complete.textContent)+parseInt(bike_remaining.textContent))) * 100).toFixed(2) + '%';
                //Update threads
				for (let i = 0; i <= bike_max_threads - bike_threads.length; i++) {
                    $bike_table = $(sender).closest('table');
                    $bike_pending = $bike_table.find('.pending');
                    if (bike_threads.length <= bike_max_threads && $bike_pending.length) {
                        bike_id = $bike_pending.get(0).getAttribute('id');
                        bike_threads.push(bike_id);
                        $bike_pending.first().text('Pending').removeClass('pending').click();
                    }else {
                        document.getElementById('walkCalc').click();
                    }
                }
            });
        }
    }

	//Get the results for walking using start coordinates and end coorinates from input buttons. 
	/**
    * @param string $start_lat
    * @param string $start_lon
    * @param string $end_lat
    * @param string $end_lon
    *
    */
    function get_walk_results(sender,start_lat,start_lon,end_lat,end_lon){
        let transport_type = "walking";		
		//Set the transport type. 
        if (walk_threads.length <= walk_max_threads) {
			//prepare the data for POST
            let walk_post_data = {
                transport_type: transport_type,
                start_lat: start_lat,
                start_lon: start_lon,
                end_lat: end_lat,
                end_lon: end_lon
            };

			//Get button id (hidden but used during development). 
            let walk_id = sender.getAttribute('id');
            walk_threads.push(walk_id);
            $(sender).text('Pending').removeClass('pending');
			
			//POST data to query PHP file with parameters set in post_data. 
            $.post('query.php', walk_post_data).done(function (data) {
                if (data.status === true) {
					//If returns a true message from query PHP file, change message.
                    $(sender).text(data.message);
                } else
                    $(sender).text(data.message);
            }).fail(function (data) {
				//If error set value to 'error'
                $(sender).text('Error');
            }).always(function (data) {
				//Disable the button input. 
                $(sender).attr('disabled', true);

                walk_id = sender.getAttribute('id');
                Array.prototype.remove = function() {
                    var what, a = arguments, L = a.length, ax;
                    while (L && this.length) {
                        what = a[--L];
                        while ((ax = this.indexOf(what)) !== -1) {
                            this.splice(ax, 1);
                        }
                    }
                    return this;
                };
                walk_threads.remove(walk_id);

				//Update calculation progress for detail labels. 
                let walk_complete = document.getElementById('walk_complete');
                walk_complete.textContent = parseInt(walk_complete.textContent)+1;
                let walk_remaining = document.getElementById('walk_remaining');
                let walk_percent = document.getElementById('walk_percent');
                let elem = document.getElementById("walkingBar");

                let $walk_table = null;
                let $walk_pending = null;
                $walk_table = $(sender).closest('table');
                $walk_pending = $walk_table.find('.pending');
                walk_remaining.textContent = ($walk_pending.length);
				//Update progress bar. 
				
                walk_percent.textContent = ((parseFloat(walk_complete.textContent) / parseFloat(parseInt(walk_complete.textContent)+parseInt(walk_remaining.textContent))) * 100).toFixed(2) + '%';
                elem.style.width = ((parseFloat(walk_complete.textContent) / parseFloat(parseInt(walk_complete.textContent)+parseInt(walk_remaining.textContent))) * 100).toFixed(2) + '%';
				//Update threads
                for (let i = 0; i <= walk_max_threads - walk_threads.length; i++) {
                    $walk_table = $(sender).closest('table');
                    $walk_pending = $walk_table.find('.pending');
                    if (walk_threads.length <= walk_max_threads && $walk_pending.length) {
                        walk_id = $walk_pending.get(0).getAttribute('id');
                        walk_threads.push(walk_id);
                        $walk_pending.first().text('Pending').removeClass('pending').click();
                    }else {
                        document.getElementById('filterpublic').click();
                    }
                }
            });
        }
    }
    let ODSchema = "ODmatrices";
    //Function to get the tables from the postgres database.
	
    function get_tables(sender) {
        //ajax get to send information to the php file 'get_tables.php'

        $.get('get_tables.php', {Schema: ODSchema} ).done(function (data) {
            console.log(data.results);
            //For each of the database tables retrieved, add it to the input for the drop down box.
            for (let result in data.results) {
                if (data.results.hasOwnProperty(result)) {
                    let value = (data.results[result]);
                    let option = document.createElement('option');
                    option.id = result;
                    option.textContent = value;
                    sender.appendChild(option);
                }
            }
            sender.focus();
            //document.getElementById("mySelect").disabled=false;
        }).fail(function (data) {

        });
    }

	//Function to delete the cache holding the progress of the journey times already calculated. 
    function delete_cache(sender){
        $(sender).text('Pending Deletion');
        $.get('delete_cache.php').done(function(data){
            if (data.status === true){
                $(sender).text('Deleted');
            }
            else
                $(sender).text(data.message);
        }).fail(function (data){
            $(sender).text('Error Deleting');
        }).always(function (data){
        });

        $('.journey').attr('disabled', false).text('Generate').addClass('pending');
        let complete = document.getElementById('complete');
        complete.textContent = '0';
        let remaining = document.getElementById('remaining');
        remaining.textContent = '0';
        let percent = document.getElementById('percent');
        percent.textContent = '0%';
        let priv_complete = document.getElementById('priv_complete');
        priv_complete.textContent = '0';
        let priv_remaining = document.getElementById('priv_remaining');
        priv_remaining.textContent = '0';
        let priv_percent = document.getElementById('priv_percent');
        priv_percent.textContent = '0%';
    }

	//Filter distances for public transport to shortest distance. 
    function filter_distances(sender){
        $(sender).text('Filtering Distances');
        document.getElementById("filterspace").textContent = 'Filtering Distances...';
        $.get('filter_distances.php').done(function(data){
            if (data.status === true){
                $(sender).text('Filtered');
                document.getElementById("filterspace").textContent = 'Filtering Distances...' + 'The public transport distances have been successfully filtered to the lowest distance for each OD.';
                document.getElementById('joindistances').click();
            }
            else
                $(sender).text(data.message);
        }).fail(function (data){
            $(sender).text('Error Filtering');
            document.getElementById("filterspace").textContent = 'Distances not filtered: ERROR.';
        }).always(function (data){
        });
    }

	//Join the times and distances to the OD matrix
    function join_tables(sender){

        let ODMatrix = '<?php echo ($tableName); ?>';
        $(sender).text('Joining Generated Distances to OD Matrix to' + '<?php echo ($tableName); ?>');
        $.get('join_distances.php', {od_matrix: ODMatrix }).done(function(data){
            if (data.status === true){
                document.getElementById("joiningspace").textContent = 'Distances Joined to Matrix';

                //webmapinfo.append(data);
                document.getElementById("myModal").style.display = "block";
            }
            else
                $(sender).text(data.message);
        }).fail(function (data){
            document.getElementById("joiningspace").textContent;
        }).always(function (data){
        });
    }

</script>
