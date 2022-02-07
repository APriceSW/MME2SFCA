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
    <link rel="stylesheet" type="text/css" href="FCAStyle.css">
    <link rel="stylesheet" type="text/css" href="stylesheets/SetCalcType.css">
</head>

<?php
set_time_limit(0);

ini_set('MAX_EXECUTION_TIME', -1);
ini_set('display_errors', true);

ini_set('xdebug.var_display_max_depth', 50); 
ini_set('xdebug.var_display_max_children', 512); 

require_once 'db_connect.php';
$db_connection = new Postgres();

$tableName = $_POST["selectODTable"];
$SchemaName = "ODmatrices";
list($sport,$type,$areatype1,$areatype2, $areatype3, $catchment) = explode('_', $tableName);

$supplyTable = $sport . "_" . $type;
$demandTable = $areatype1 . "_" . $areatype2 . "_" . $areatype3;

$measurementtype = $_POST["opttype"];

?>
<body>


<!-- Page title and navigation -->
<div class="container-fluid text-center" Style="background-color: #4b6da3; color: white; padding-top: 15px; padding-bottom: 15px; width: 100%; ">

    <h1>Accessibility Calculator</h1>

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

    <div class="innerborder">
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
    </div>

</div>
<div class="col-sm-12 text-center">
    <form name="form1" id="form1" action="" method="post">
        <div class="row">
                <div class="col-sm-6 text-center" Style="height: 100%">
                    <div class="row">
                        <div class ="innerborder" Style="height: 300px; width: 100%">
                            <p><small>Select the modes of transport you wish to include in the accessibility calculation.</small></p>
                            <div class="container" Style="height: 100%"><br>
                                <div class="row" Style="height: 100%">
                                    <div class="col-sm text-center">
                                        Private
                                        <h1><i class="fas fa-car"></i></h1>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="carswitch">
                                            <label class="custom-control-label" for="carswitch"></label>
                                        </div>
                                    </div>
                                    <div class="col-sm text-center">
                                        Public
                                        <h1><i class="fas fa-bus-alt"></i></h1>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="busswitch" checked>
                                            <label class="custom-control-label" for="busswitch"></label>
                                        </div>
                                    </div>
                                    <div class="col-sm text-center">
                                        Bicycle
                                        <h1><i class="fas fa-bicycle"></i></h1>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="bikeswitch">
                                            <label class="custom-control-label" for="bikeswitch"></label>
                                        </div>
                                    </div>
                                    <div class="col-sm text-center">
                                        Walking
                                        <h1><i class="fas fa-walking"></i></h1>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="walkingswitch">
                                            <label class="custom-control-label" for="walkingswitch"></label>
                                        </div>
                                    </div>
                                    <br><br>
                                </div>
                                <br><br>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                            <div class ="innerborder" Style="height: 140px; Width: 100%">
                                <div id="ifDistance">
                                    <div class="slidecontainer">
                                        <label for="myRange" class="float-left">500m</label>
                                        <label for="myRange" class="float-right">25,000m</label>
                                        <input type="range" min="500" max="25000" value="10000" id="distanceRange" class="sliderthree" step="100">
                                        <p>Catchment size: <span id="distancedemo"></span>m</p>
                                    </div>
                                </div>

                                <div id="ifTime" >
                                    <div class="slidecontainer">
                                        <label for="timeRange" class="float-left">5 Minutes</label>
                                        <label for="timeRange" class="float-right">600 Minutes</label>
                                        <input type="range" min="5" max="600" value="25" id="timeRange" class="sliderthree" step="5">
                                    
    <p>Catchment size: <span id="timedemo"></span> mins</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
                <div class="col-sm-6 text-center" style="padding-right: inherit;">
                    <div class ="innerborder" Style="height: 450px; width 100%;">
							<div id="splitoption">
							<!-- development for local splits post Thesis viva. -->
								Local Modal Split
								<input type="radio" name="modalsplitoptions" value="localmodalsplit">
								
								Global Modal Split
								<input type="radio" name="modalsplitoptions" value="globalmodalsplit"  checked>
							</div>
                        <div class="slidecontainer">
                            Bus<br>
                            <input name="percentagesliders" id="busRange" class="slidertwo" type="range" min="0" max="100" value="0" onchange="updateBusValue(this.value)" />
                            <br><span id="busper">0</span>%</p>
                            Car<br>
                            <input name="percentagesliders" id="carRange" class="slidertwo" type="range" min="0" max="100" value="0" onchange="updateCarValue(this.value)" disabled/>
                            <br><span id="carper">0</span>%</p>
                            Bike<br>
                            <input name="percentagesliders" id="bikeRange" class="slidertwo" type="range" min="0" max="100" value="0" onchange="updateBikeValue(this.value)" disabled/>
                            <br><span id="bikeper">0</span>%</p>
                            Walk<br>
                            <input name="percentagesliders" id="walkRange" class="slidertwo" type="range" min="0" max="100" value="0" onchange="updateWalkValue(this.value)" disabled/>
                            <br><span id="walkper">0</span>%</p>

                            <div>total % of population left: <strong id="total">0</strong></div>
                        </div>
                    </div>
                </div>
        </div>

        <div class="row">
            <div class="col-sm-6 ">
                <div class ="innerborder" style="width: 100%;height: 200px;margin-right: 0px;margin-left: inherit;width100%: ;">
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-6 text-center " style="text-align: center; width: 100%;">
                                <label class="form-check-label">
                                    <input type="radio" name="optradio" value="Linear Decay" checked>
                                    <img src="images/lineardecay.png" style="max-height: 30%; max-width: 30%;">
                                </label>
                                Linear Decay
                            </div>
                            <div class="col-sm-6 text-center">
                                <label class="form-check-label">
                                    <input type="radio" name="optradio" value="No Decay">
                                    <img src="images/nodecay.png" style="max-height: 30%; max-width: 30%;">
                                </label>
                                No Decay
                            </div>
                            <br><br>
                        </div>
                        <br><br>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 text-center">
                <div class ="innerborder" Style="height: 200px; width 100%;  ">
                    <button type="button" class="buttonr" onclick="build_query(this)" style="vertical-align:middle"><span>Submit </span></button>
                </div>
            </div>
        </div>


    </form>
</div>

<p id="demo"></p>


<!-- The Modal -->
<div id="myModal" class="modal">

    <!-- Modal content -->
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>You have successfully calculated the accessibility for <?php echo $sport . " " . $type;?><br>
            using the population demand - <?php echo $areatype3;?>" </p>
            <button type="button" class="buttonr" onclick="document.location='SetMapOutput.php'" style="vertical-align:middle"><span>View Maps </span></button>
    </div>

</div>


</body>
</html>

<!-- Javascript for the page controls -->
<script>

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

		//block or unblock inputs depending on measurement type selected. 
        let Type = '<?php echo($measurementtype); ?>';

        if (Type == "duration") {
            document.getElementById('ifDistance').style.display = 'none';
            document.getElementById('ifTime').style.display = 'block';
        } else {
            document.getElementById('ifDistance').style.display = 'block';
            document.getElementById('ifTime').style.display = 'none';
        }


		//set a max total for percentage to 100. 
        var maxTotal = 100,
            inputs = [].slice.call(document.getElementsByClassName('slidertwo')),
            getTotal = function () {
                var sum = 0;
                inputs.forEach(function (input) {
                    sum += parseInt(input.value, 10);
                });
                return sum;
            },
			//when maximum reached stop it from increasing. 
            maxReached = function (e) {
                var sum = getTotal(), target;
                if (sum > maxTotal) {
                    target = e.target;
                    target.value = target.value - (sum - maxTotal);
                    //change value to 100 minus the total to show remainder  
                    document.getElementById('total').innerHTML = 100 - getTotal();
                    e.preventDefault();
                    return false;
                }
                // next line is just for demonstrational purposes (set to 100).
                document.getElementById('total').innerHTML = 100 - getTotal();
                document.getElementById('busper').innerHTML = document.getElementById('busRange').value;
                document.getElementById('carper').innerHTML = document.getElementById('carsRange').value;
                document.getElementById('bikeper').innerHTML = document.getElementById('bikeRange').value;
                document.getElementById('walkper').innerHTML = document.getElementById('walkRange').value;

                return true;
            };


	//Set the modal split labels to values. 
    inputs.forEach( function(input){
        input.addEventListener('input', maxReached );
    });

    function updateBusValue(val){
        document.getElementById("busper").innerHTML = val;
    }

    function updateCarValue(val){
        document.getElementById("carper").innerHTML = val;
    }

    function updateBikeValue(val){
        document.getElementById("bikeper").innerHTML = val;
    }

    function updateWalkValue(val){
        document.getElementById("walkper").innerHTML = val;
    }

    var timeslider = document.getElementById("timeRange");
    var timeoutput = document.getElementById("timedemo");
	// Display the default slider value
    timeoutput.innerHTML = timeslider.value;

    // Update the current slider value (each time you drag the slider handle)
    timeslider.oninput = function() {
        timeoutput.innerHTML = this.value;
    }


    var distanceslider = document.getElementById("distanceRange");
    var distanceoutput = document.getElementById("distancedemo");
	// Display the default slider value
    distanceoutput.innerHTML = distanceslider.value; 

    // Update the current slider value (each time you drag the slider handle)
        distanceslider.oninput = function() {
            distanceoutput.innerHTML = this.value;
    }

    let supplySchema = "supplytables";
    let demandSchema = "demandtables";
    let ODMatrixschema = "ODmatrices";
    //Function to disable the drop down box until it is ready to be used / loaded.
    function disable() {
        document.getElementById("select2").disabled=true;
    }

    //Function to build the string.
    function build_query(sender){

        //Input values from drop down boxes, text boxes etc.
        let ODTable = '<?php echo ($tableName); ?>';
        let SupTable = '<?php echo ($supplyTable); ?>';
        let DemTable = '<?php echo ($demandTable); ?>';
        let lbl = document.getElementById('demo');
        let MeasurementType = '<?php echo($measurementtype); ?>';
        let TimeCatchmentSize = document.getElementById('timeRange').value;
        let DistanceCatchmentSize = document.getElementById('distanceRange').value;
        let BusPercentage = document.getElementById('busRange').value;
        let CarPercentage = document.getElementById('carRange').value;
        let BikePercentage = document.getElementById('bikeRange').value;
        let WalkPercentage = document.getElementById('walkRange').value;
        let DecayType = $('input[name=optradio]:checked').val();
		let ModalSplitOption = $('input[name=modalsplitoptions]:checked').val();

        lbl.innerHTML = '<br><span style="text-transform:capitalize;">' + 'Calculating FCA Scores..' +
            '</span>';
        $.get('calculateMMFCA.php', {SelectedSplitOption: ModalSplitOption, ODTableName: ODTable, SUPTableName: SupTable, DemTableName: DemTable,
            TimeCatchmentSizeValue: TimeCatchmentSize, DistanceCatchmentSizeValue: DistanceCatchmentSize, DecayTypeValue: DecayType,
            BusPercentageValue: BusPercentage, CarPercentageValue: CarPercentage, BikePercentageValue: BikePercentage, WalkPercentageValue: WalkPercentage, MeasurementTypeValue: MeasurementType }).done(function (data) {



            //webmapinfo.append(data);
            document.getElementById("myModal").style.display = "block";

            if (data.status === true){

                //$(sender).text('Finished Calculating');
                lbl.innerHTML += '<br><span style="text-transform:capitalize;color: green">' + 'Finished Calculating' +
                    '</span>';

            }
            else
                $(sender).text(data.message);
        }).fail(function (data){
            //$(sender).text('Error Calculating');
            lbl.innerHTML += '<br><span style="text-transform:capitalize;color: darkred">' + 'Error Calculating' +
                '</span>';
        }).always(function (data){
        });
    }

	//Automatically set the bus to unlocked. 
    $('#busswitch').click(function() {
        $('#busRange').attr('disabled',! this.checked)
        $('#busRange').val(0);
        $('#bus')
        let bustotal = document.getElementById('busRange').value;
        let cartotal = document.getElementById('carsRange').value;
        let biketotal = document.getElementById('bikeRange').value;
        let walktotal =  document.getElementById('walkRange').value;

        let total = bustotal + cartotal + biketotal + walktotal;
        window.alert(total);
        document.getElementById('total').innerHTML = 100 - total;

    });

    $('#carswitch').click(function() {
        $('#carRange').attr('disabled',! this.checked)
        $('#carRange').val(0);
    });

    $('#bikeswitch').click(function() {
        $('#bikeRange').attr('disabled',! this.checked)
        $('#bikeRange').val(0);
    });

    $('#walkingswitch').click(function() {
        $('#walkRange').attr('disabled',! this.checked)
        $('#walkRange').val(0);
    });



</script>