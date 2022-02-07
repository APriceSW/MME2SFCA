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
<link rel="stylesheet" type="text/css" href="stylesheets/SetMapOutput.css">

<?php
set_time_limit(0);
ini_set('MAX_EXECUTION_TIME', -1);
ini_set('display_errors', true);
ini_set('xdebug.var_display_max_depth', 50); //avoids '...'
ini_set('xdebug.var_display_max_children', 512); //avoids 'more elements...'

require_once 'db_connect.php';
$db_connection = new Postgres();

$SchemaName = "webmap";
$tableList = $db_connection->get_tables($SchemaName);
$ODtables = $db_connection->get_maps_in_date_order_recent();

?>
<body>

<!-- Page title and navigation -->
<div class="container-fluid text-center" Style="background-color: #4b6da3; color: white; padding-top: 15px; padding-bottom: 15px; width: 100%; ">

    <h1>Select the accessibility calculation results to be displayed </h1>

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

<form action="WebMap.php" method="post">
    <div class="col-sm-12 text-center">
        <br>
        <table style="width:100%" class="paleBlueRows">
            <tr>
                <th rowspan="2" style="background: #4B6DA3; color: #FFFFFF;"  >Facility Type</th>
                <th rowspan="2" style="background: #4B6DA3; color: #FFFFFF;"  >Decay Type</th>
                <th rowspan="2" style="background: #4B6DA3; color: #FFFFFF;" >Measurement Type</th>
                <th rowspan="2" style="background: #4B6DA3; color: #FFFFFF;" >Travel Tolerance</th>


                <th rowspan="2" style="background: #4B6DA3; color: #FFFFFF;">Age groups</th>
                <th colspan="4" style="background: #4B6DA3; color: #FFFFFF;">Population Percentages
                <th rowspan="2" style="background: #4B6DA3; color: #FFFFFF;" >Select</th>
                </th>
            </tr>
            <th style="background: #4B6DA3; color: #FFFFFF;" >Private</th>
            <th style="background: #4B6DA3; color: #FFFFFF;" >Public</th>
            <th style="background: #4B6DA3; color: #FFFFFF;" >Bike</th>
            <th style="background: #4B6DA3; color: #FFFFFF;" >Walking</th>
            </tr>

            <?php foreach ($tableList as $key=>$table):?>
                <?php //list($decay_part1,$decay_part2,$catchementsize,$measurementtype, $modaltype, $additional) = explode('_', $table);?>
                <?php list($decay_part1,$decay_part2,$catchementsize,$measurementtype, $modaltype) = explode('_', $table);?>
                <tr >
                    <td><?=$facilitytype = $db_connection->get_facility_type($table);?></td>
                    <td ><?=$decay_part1 . ' ' . $decay_part2;?></td>
                    <td ><?=$facilitytype = $db_connection->get_catchment_size($table, $measurementtype);?></td>
                    <td ><?=$measurementtype ;?></td>
                    <td><?php $agesgroups = $db_connection->get_calc_ages($table);?></td>
                    <td><?php $agesgroups = $db_connection->get_private_per($table);?></td>
                    <td><?php $agesgroups = $db_connection->get_public_per($table);?></td>
                    <td><?php $agesgroups = $db_connection->get_bike_per($table);?></td>
                    <td><?php $agesgroups = $db_connection->get_walk_per($table);?></td>
                    <td ><input type="radio" name="SelectMapOutput" value='<?php echo $table ?>' >
                </tr>
            <?php endforeach; ?>
        </table>
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
    let ODSchema = "webmap";
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