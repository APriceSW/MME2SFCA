<!DOCTYPE html>
<html>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<link rel="stylesheet" type="text/css" href="FCAStyle.css">
<link rel="stylesheet" type="text/css" href="stylesheets/setmmtable.css">
<?php

#Help guide: http://dev.opentripplanner.org/apidoc/1.0.0/resource_IndexAPI.html
set_time_limit(0);

ini_set('MAX_EXECUTION_TIME', -1);
ini_set('display_errors', true);

ini_set('xdebug.var_display_max_depth', 50); //avoids '...'
ini_set('xdebug.var_display_max_children', 512); //avoids 'more elements...'

require_once 'db_connect.php';
$db_connection = new Postgres();


$SchemaName = "ODmatrices";
$tableList = $db_connection->get_tables($SchemaName);?>
<body>

<!-- Page title and navigation -->
<div class="container-fluid text-center" Style="background-color: #4b6da3; color: white; padding-top: 15px; padding-bottom: 15px; width: 100%; ">

    <h1>Select parameters for the accessibility calculation</h1>

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


<form action="SetCalcType.php" method="post">
    <div class="col-sm-12 text-center">
        <br>
        <table style="width:100%" class="paleBlueRows">
            <tr>
                <th>FullTableName</th>
                <th>Facility Type</th>
                <th>Facility Table Version</th>
                <th>Facility version Date</th>
                <th>Geographic population level</th>
                <th>Travel tolerance</th>
                <th>Transport Network Build Date</th>
                <th>Age Groups included</th>
                <th>Date and Time Generated</th>
                <th>Select</th>
            </tr>
            <?php foreach ($tableList as $key=>$table):?>
                <?php list($facilityType1,$facilityType2,$demandType1,$demandType2, $demandType3, $CatchmentSize) = explode('_', $table);
                ?>
                <tr >
                    <td><?php echo $table;?></td>
                    <td ><?=$facilityType1 . ' ' . $facilityType2;?></td>
                    <td><?php $supplyversion = $db_connection->get_supply_version($table);?></td>
                    <td><?php $supplytabledate = $db_connection->get_supply_date($table);?></td>
                    <td ><?=$demandType1 . ' ' . $demandType2 . ' ' . $demandType3;?></td>
                    <td><?=$CatchmentSize;?></td>
                    <td><?php $roadnetworkdate = $db_connection->get_roadnetwork_date($table);?></td>
                    <td><?php $agesgroups = $db_connection->get_population_ages($table);?></td>
                    <td><?php $tablecreationdate = $db_connection->get_datetime($table);?></td>
                    <td ><input type="radio" name="selectODTable" value='<?php echo $table ?>' >
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <br>

    <div class="col-sm-12 text-center" >
        <div class="row">
            <div class="col-sm-6 text-center">
                <div class="innerborder" Style="background-color: #4b6da3; color: white;">
                    <h4>Time</h4>
                </div>
            </div>
            <div class="col-sm-6 text-center">
                <div class="innerborder" Style="background-color: #4b6da3; color: white; ">
                    <h4>Distance</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="measurement-selector">
        <div class="col-sm-12 text-center">
            <div class="row">
                <div class="col-sm-6 text-center">
                    <div class="innerborderb">
                    <input id="time" type="radio" name="opttype" value="duration" checked/>
                    <label class="selected-measurement time" for="time"></label>
                    </div>
                </div>
                <div class="col-sm-6 text-center">
                    <div class="innerborderb">
                    <input id="distance" type="radio" name="opttype" value="distance" />
                    <label class="selected-measurement distance"for="distance"></label>
                    </div>
                </div>
            </div>
        </div>
    </div>
<br>
    <div class="col-sm-12 text-center">
        <button type="submit" class="buttonr" onclick="" style="vertical-align:middle"><span>Submit </span></button>
    </div>
</form>
</div>
</div>




</body>
<script>
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

                    $('#myTable tr:last').after('<tr>' + option + '</tr><tr>...</tr>');
                }


            }
            sender.focus();
            //document.getElementById("mySelect").disabled=false;
        }).fail(function (data) {

        });
    }
</script>