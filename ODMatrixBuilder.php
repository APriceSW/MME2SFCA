<!DOCTYPE html>
<html>
<!-- Includes for the style sheets and other important extensions -->
<link rel="stylesheet" type="text/css" href="FCAStyle.css">
<script src="/staff/leaflet-dev/jquery-3.4.1.min.js"></script>
<title>Origin-To_Destination Matrix Builder</title>
<meta charset="utf-8">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous"></head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">

<link rel="stylesheet" type="text/css" href="systemStyles.css">
<link rel="stylesheet" type="text/css" href="stylesheets/ODMatrixBuilder.css">




</head>
<body>
<!-- Php for stopping the page from refreshing -->
<?php
set_time_limit(0);
ini_set('MAX_EXECUTION_TIME', -1);
ini_set('display_errors', true);
ini_set('xdebug.var_display_max_depth', 50); //avoids '...'
ini_set('xdebug.var_display_max_children', 512); //avoids 'more elements...'

require_once 'db_connect.php';
$db_connection = new Postgres();

//$SchemaName = "supplytables";
//$tableList = $db_connection->get_tables($SchemaName);
$supplytablesdetails = $db_connection->get_supply_in_date_order_recent();
?>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>




<div class="container-fluid page-title text-center ">
    <h1 class="stroke">SERVICE SUPPLY SET-UP</h1>
</div>
<ul >
    <li><a class="active" href="index.php" ><img src="menuicons/home-colour.png" width="25" height="25"> Home</a></li>
    <li><a class="active" href="FacilityViewer.php" ><img src="menuicons/map-colour.png" width="25" height="25"> View Facilties</a></li>
    <li><a class="active" href="SetMMTable.php"><img src="menuicons/calculator-colour.png" width="25" height="25"> Accessibility Calculator</a></li>
    <li><a class="active" href="SetMapOutput.php"><img src="menuicons/results-colour.png" width="25" height="25"> View Results</a></li>

    <li style="float:right"><a href="index.php"><img src="menuicons/help.png" width="25" height="25"> Help</a></li>
    <li style="float:right"><a class="ODMatrixBuilder.php" href="index.php"><img src="menuicons/settings-colour.png" width="25" height="25"> Setup</a></li>
</ul>

</div>

<form name="form2" id="form2" action="" method="post">
<div class="container-fluid">
    <div class="row">

        <div class="col-sm-6 text-center" id="facilityselecting">
            <div class="leftpanel">
                <h3>Select service supply facilities</h3>
                <div class="row">
                    <div class="container h-100">
                        <div class="row" style="height:40%;">
                            <div style="overflow-y:auto; height:400px; width: 100%;border: 2px solid #013320;">
								<table id="servicesupplies" name="servicesupplies"  class="table">
									<tr>
										<th rowspan="1">Service Type</th>
										<th rowspan="1">Category</th>
										<th rowspan="1">No. of Services</th>
										<th rowspan="1">Version Date</th>
										<th rowspan="1">Select</th>
									</tr>
									<?php $supplychecker = 1; ?>
									<?php foreach ($supplytablesdetails as $key=>$table):?>
			
									<tr >
										<td><?php list($service_name,$service_type) = explode('_', $table);echo $service_name . " " . $service_type ?></td>
										<td><?php $icon = $db_connection->get_icon($table); echo '<img src="icons/' . $icon . '" width="40" height="40">' ?></td>
										<td><?php $tableservicecount = $db_connection->get_supply_count($table);?></td>
										<td><?php $tablecreationdate = $db_connection->get_supplytable_datetime($table);?></td>
										<td ><input type="radio" name="facilityselect" value="<?php echo $table ?>" <?php if($supplychecker == 1){echo "checked";}?>>
									</tr>
									<?php $supplychecker++; ?>
									<?php endforeach; ?>
								</table >	
							</div> 
                        </div>
                        <br>


    <hr>
                        <div class="row">

                                <div class="col-sm text-center h-100">
                                    <div id="demandselecting">
                                        <h3>Select a populated geographic area</h3>
                                        <body onLoad="preLoad()">
                                        <input type="radio" name="optionsRadios" onClick="im('a1');" value="oa_populated_centroids" checked>
                                        <label for="OA">Output Areas</label><br>
                                        <input type="radio" name="optionsRadios" onClick="im('a2');" value="lsoa_populated_centroids">
                                        <label for="LSOA">Lower Super Output Areas</label><br>
                                        <input type="radio" name="optionsRadios" onClick="im('a3');" value="msoa_populated_centroids">
                                        <label for="MLOA">Middle Super Output Areas</label><br>
                                    </div>
                                </div>
                        </div>
                   <hr>
                        <div class = "row">

                                <div class="col-sm-12 text-center h-100">
                                <h3>Select travel tolerance</h3>

                                    <div class="slidecontainer">
                                        <label for="myRange" class="float-left">1km</label>
                                        <label for="myRange" class="float-right">25km</label>
                                        <input type="range" min="400" max="50000" value="10000" id="myRange" class="slidertwo" step="100">
                                        <p>Catchment size: <span id="demo"></span>m</p>
                                        <script>
                                            var slider = document.getElementById("myRange");
                                            var output = document.getElementById("demo");
                                            output.innerHTML = slider.value; // Display the default slider value

                                            // Update the current slider value (each time you drag the slider handle)
                                            slider.oninput = function() {
                                                output.innerHTML = this.value;
                                            }
                                        </script>
                                    </div>
                                </div>

                        </div>
                    </div>
                </div>
            </div>
            <script>

                function preLoad() {
                    a1 = new Image; a1.src = 'Output Areas Wales.png';
                    a2 = new Image; a2.src = 'Lower Super Output Areas Wales.png';
                    a3 = new Image; a3.src = 'Mid output areas.png';
                }
                function im(image) {
                    document.getElementById(image[0]).src = eval(image + ".src")
                }

            </script>

        </div>

        <div class="col-sm-6 text-center">

            <div class="mapimagepanel">
                <div class="row">
                <img id ="a" src="Output Areas Wales.png" alt="5 Terre" style="width: 100%; min-height: 700px; max-height: 750px;">
                <div class="container">
                    <p id="cont">
                        Output Areas
                    </p>
                    <hr>
                    <script>
                        $('input[name="optionsRadios"]').on('change', function(){
                            if ($(this).val()=='oa_populated_centroids') {

                                //change to "show update"
                                $("#cont").text("Output Area");

                            } else  if ($(this).val()=='lsoa_populated_centroids'){

                                $("#cont").text("Lower Super Output Areas");
                            }
                            else if ($(this).val()=='msoa_populated_centroids'){
                                $("#cont").text("Middle Super Output Areas");
                            }
                        });
                    </script>
                </div>

                <div class="row">
                    <div class="col-sm-12 text-center h-100">
                            <h3>Select the population age range</h3>
                            <div class="form-check">
                                <section>

                                    <label for=age0to4"">
                                        <input id ="age0to4" type="checkbox" class="a" value="age0to4"/>
                                        0 to 4
                                    </label>
                                    <label  for="age5to59">
                                        <input id="age5to9" type="checkbox" class="a" value="age5to9"/>
                                        5 to 9
                                    </label>
                                    <label  for="age10to16">
                                        <input id="age10to16" type="checkbox" class="a" value="age10to16"/>
                                        10 to 16
                                    </label>
                                    <label  for="age17to18">
                                        <input id="age17to18" type="checkbox" class="a" value="age17to18"/>
                                        17 to 18
                                    </label>
                                    <label for="age19to24">
                                        <input id="age19to24" type="checkbox" class="a" value="age19to24"/>
                                        19 to 24
                                    </label>
                                    <label  for="age25to44">
                                        <input id="age25to44" type="checkbox" class="a" value="age25to44"/>
                                        25 to 44
                                    </label>
                                    <label for="age45to64">
                                        <input id="age45to64" type="checkbox" class="a" value="age45to64"/>
                                        45 to 64
                                    </label>
                                    <label for="age65plus">
                                        <input id="age65plus" type="checkbox" class="a" value="age65plus"/>
                                        65 +
                                    </label>

                                </section>
                            </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>



<div class="container-fluid">
    <div class="row">

        <div class="col-sm-12 text-center">

            <button type="button" class="buttonr" onclick="build_query()">Submit</button>

        </div>
    </div>
</div>

</form>

</div>
<div hidden class="outputform">
        <div class="outputtop">
            <h1 style="font-size:30px">Output</h1>
        </div>
        <div class="pageform">
            <p id="demo2"></p>
        </div>
</div>

<!-- The Modal -->
<div id="myModal" class="modal">

    <!-- Modal content -->
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>You have successfully created the set up table<br>
        Click next to move on to the distance calculation stage of the set up.</p>
        <button type="button" class="buttonr" onclick="document.location='SetODTable.php'" style="vertical-align:middle"><span>Calculate Distances </span></button>
    </div>

</div>


				 
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

    $("input").on("click", function(){
        if ( $(this).attr("type") === "radio" ) {
            $(this).parent().siblings().removeClass("isSelected");
        }
        $(this).parent().toggleClass("isSelected");
    });

    //Function to build the string.
    function build_query(sender){
        let lbl = document.getElementById('demo2');
        //Input values from drop down boxes, text boxes etc.
        let sldValue = document.getElementById('myRange').value;

        let SUPTable = $('#facilityselecting input:radio:checked').val();
        let SUPId = "id";
        let SUPGeom = "geom";
        let SUPVol = "capacity";

        let DEMTable = $('#demandselecting input:radio:checked').val();
        let DEMId = "id";
        let DEMGeom = "geom";
        let DEMVol = "totalpop";

        let ageRanges = $('.a:checkbox:checked').map(function() {
            return this.value;
        }).get();

        console.log(ageRanges);
        let agerangelist = ageRanges.toString();

        //let agerangestring = agerangelist.split(",").join(" + ");

        lbl.innerHTML = '<br><span style="text-transform:capitalize;">' + 'Generating OD Matrix Table as ..     ' + SUPTable + '_' + DEMTable + '_' + sldValue
            '</span>';
        $.get('ODMatrixTable.php', {sldVal: sldValue, SupTable: SUPTable, SupId: SUPId, SupGeom: SUPGeom, SupVol: SUPVol, DemTable: DEMTable, DemId: DEMId, DemGeom: DEMGeom, DemVol: DEMVol, AgeRanges: agerangelist}).done(function (data) {
            if (data.status === true){
                //$(sender).text('Finished Calculating');

                //webmapinfo.append(data);
                document.getElementById("myModal").style.display = "block";
                lbl.innerHTML += '<br><span style="text-transform:capitalize;color: green">' + 'Finished Creating OD Matrix' +
                    '</span>';
            }
            else
                $(sender).text(data.message);
        }).fail(function (data){
            //$(sender).text('Error Calculating');
            lbl.innerHTML += '<br><span style="text-transform:capitalize;color: darkred">' + 'Error Creating Table' +
                '</span>';
        }).always(function (data){
        });
    }

</script>

<script>
    $(document).ready(function(){
        $('[data-toggle="popover"]').popover();
    });

</script>


</body>

</html>