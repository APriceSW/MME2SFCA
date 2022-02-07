<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>

    <!-- Leaflet Links -->
    <link rel="stylesheet" href="./leaflet/leaflet.css" />
    <script src="./leaflet/leaflet.js"></script>

	<!-- Webmap Styling -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="stylesheets/WebMap.css">
    <script src="basemaps.js"></script>
    
    <!--jQuery library-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>


    <title>Accessibility Results</title>
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
$tableName = $_POST["SelectMapOutput"];

//Get map inforamtion. 
list($decay_part1,$decay_part2,$catchementsize,$measurementtype, $modaltype) = explode('_', $tableName);
$publictransport = $decay_part1 . "_" . $decay_part2  . "_" .  $catchementsize  . "_public_" .  $measurementtype . "_" . $modaltype;
$privatetransport = $decay_part1 . "_" . $decay_part2  . "_" .  $catchementsize  . "_private_" .  $measurementtype . "_" . $modaltype;
$biketransport = $decay_part1 . "_" . $decay_part2  . "_" .  $catchementsize  . "_bike_" .  $measurementtype . "_" . $modaltype;
$walktransport = $decay_part1 . "_" . $decay_part2  . "_" .  $catchementsize  . "_walk_" .  $measurementtype . "_" . $modaltype;
$legendValues = $db_connection->get_legend_values($tableName, $publictransport, $privatetransport, $biketransport, $walktransport);
$facilityreturn = $db_connection->get_facility_type_return($tableName);
$facilitytablename = "supplytables." . str_replace(" ", "_", $facilityreturn);
?>

<!-- Page title and navigation -->
<div class="container-fluid text-center" Style="background-color: #4b6da3; color: white; padding-top: 15px; padding-bottom: 15px; width: 100%; ">

    <h1>Accessibility Results</h1>

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

<div class="container-fluid ">
    <div class="row ">
        <div class="col-sm-4">

            <div class="piechartpanel">
                <h2 style="text-align:center;">Calculation Information</h2>
                <p class="text-center">Facility Type</p>
                <p class="text-center"><small><?php $facility = $db_connection->get_facility_type($tableName);?></small></p>
                <hr>
                <p class="text-center">Calculation Type</p>
                <p class="text-center"><small><?=$measurementtype ;?></small></p>
                <hr>
                <p class="text-center">Catchment Size</p>
                <p class="text-center"><small><?=$catchementsize ;?></small></p>
                <hr>
                <p class="text-center">Age Groups</p>
                <p class="text-center"><small><?php $agesgroups = $db_connection->get_calc_ages($tableName);?></small></p>
                <hr>
                <p class="text-center">Population percentages</p>
                <div id="canvas-holder">
                    <canvas id="myPieChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-sm-8">
            <div class="mappanel">
                <div id="mapid"></div>
        </div>
    </div>
</div>
</div>

<div class="container-fluid ">
    <div class="row ">
        <div class="col-sm-4">

            <div class="piechartpanel">
                <h2 style="text-align:center;">Version Information</h2>
                <p class="text-center">Facility Table Version:</p>
                <p class="text-center"><small><?php $facility = $db_connection->get_calculation_supply_version($tableName);?></small></p>
                <hr>
                <p class="text-center">Facility Table Date:</p>
                <p class="text-center"><small><?php $facility = $db_connection->get_calculation_supply_date($tableName);?></small></small></p>
                <hr>
                <p class="text-center">Road network Date:</p>
                <p class="text-center"><small><?php $facility = $db_connection->get_calculation_network_build_date($tableName);?></small></small></p>
                <hr>
                <p class="text-justify"><small>
                        The facility table version is a number allocation to record the state in changes to the facility supply table overtime.
                        The facility table date is to keep track of when this number was allocated, in order to view how recently the facility data was updated.
                    </small></p>
                <p class="text-justify"><small>
                        <br>The road network is when the public transport time tables and roads were updated on the journey calculator. It is recommended to
                        update this frequently, as the bus timetables and road closures may effect results.
                    </small></p>
                <div id="canvas-holder">
                    <canvas id="myPieChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-sm-8">
            <div class="mappanel">
            </div>
        </div>
    </div>
</div>
<script>

	//Get the modal split percentage values. 
    var privateper = parseInt('<?php $privateper = $db_connection->get_private_per($tableName);?>', 10);
    var publicper = parseInt('<?php $publicper = $db_connection->get_public_per($tableName);?>', 10);
    var bikeper = parseInt('<?php $bikeper = $db_connection->get_bike_per($tableName);?>', 10);
    var walkper = parseInt('<?php $walkper = $db_connection->get_walk_per($tableName);?>', 10);


	//Create a pie chart. 
    var ctx = document.getElementById('myPieChart').getContext('2d');
    var chart = new Chart(ctx, {
        // The type of chart
        type: 'pie',
        // The data for our dataset
        data: {
            labels: ['Private', 'Public', 'Cycling', 'Walking'],
            datasets: [{
                label: '% of Population for each mode of transport',
                backgroundColor: ["#3799cf", "#00a250","#ba3c27","#e6e80d"],
                data: [privateper, publicper,bikeper, walkper
                ]
            }]
        },

        // Configuration options go here
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });



	//Create map instance. 
    var map;
	
    var polydbtable = 'webmap.' + '<?php echo $tableName;?>';
    var dbsupply = '<?php echo $facilitytablename?>';
    var columns = ['<?php echo $privatetransport;?>', '<?php echo $publictransport;?>', '<?php echo $biketransport;?>', '<?php echo $walktransport;?>'];
    
	//Get facility column names. 
	var facilitycolumns = ["id", "sitenm", "club", "capacity"];

	//Set the map information. Create map point at location etc.
    map = L.map('mapid', {center: [52.5, -3.5],
        zoom: 7, minZoom: 7, maxZoom: 12});
    map.setMaxBounds(map.getBounds());
    map.setZoom(8);

	//Set a basemap layer. 
    var basemap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    });

	//Alternative basemap layer. 
    var Stamen_TerrainBackground = L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/terrain-background/{z}/{x}/{y}{r}.{ext}', {
        attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        subdomains: 'abcd',
        ext: 'png'
    }).addTo(map);

	//Create dictionary of basemaps. 
    let baseMaps = {
        "OpenStreeMap": basemap,
        "Stamen Terrain no text": Stamen_TerrainBackground,
        "Stamen Terrain": Stamen_Terrain
    };

	//Add layer controls to map. 
    var layerControl = L.control.layers(baseMaps).addTo(map);

    //add features using data pulled from POSTGIS
    var facilitylocations = getFacilityData(dbsupply);
    var fcascores = getData(polydbtable);

	//Generate a legend for the map at the bottom right. 
    var legend = L.control({position: 'bottomright'});

	//Set the breakpoints for the legend. 
    legend.onAdd = function (map) {

        var div = L.DomUtil.create('div', 'info legend'),
            grades = [0.0001, <?php echo round($legendValues[0], 6);?>, <?php echo round($legendValues[2], 6);?>, <?php echo round($legendValues[3], 6);?>],
            labels = ['<strong> <?php echo $facility = $db_connection->get_facility_type($tableName);?> <br>Per 1000 Persons </strong>'];
        div.innerHTML +=
            labels.push('<i style="background:' + getColor(grades[i] + 0.0001) + '"></i> '+
                '0');
        // loop through our density intervals and generate a label with a colored square for each interval
        for (var i = 0; i < grades.length; i++) {
            div.innerHTML +=
                labels.push('<br><i style="background:' + getColor(grades[i] + 0.00001) + '"></i> ' +
                    grades[i] + (grades[i + 1] ? ' &ndash; ' + grades[i + 1] : '+'));
        }
        div.innerHTML = labels.join('<br>');
        return div;
    };

	//Add the legend. 
    legend.addTo(map);

	//Get polygon data from POSTGIS. 
    function getData(polydbtable){
        $.ajax({
            url: "getMapLayers.php",
            data: {
                table: polydbtable,
                fields: columns
            },
            success: function(data){
                mapData(data);
            }
        })
    };

	//Get POINT data from POSTGIS. 
    function getFacilityData(dbsupply){
        $.ajax({
            url: "getMapLayers.php",
            data: {
                table: dbsupply,
                fields: facilitycolumns
            },
            success: function(data){
                mapPointData(data, dbsupply);
            }
        })
    };

	//Set the colours for the legend. (These are white to yellow to red). 
    function getColor(d) {
        return d > <?php echo $legendValues[3]?> ? '#800026' :
            d > <?php echo $legendValues[1];?>  ? '#E31A1C' :
                d > <?php echo $legendValues[0];?>  ? '#FD8D3C' :
                    d > 0  ? '#FED976' :
                        d = 0 ? '#FFFFFF' :
                            '#FFFFFF';
    }

	//Set the private transport layer properties. 
    function LSOAprivate(feature) {
        return {
            fillColor: getColor(feature.properties.<?php echo $privatetransport;?>),
            weight: 0.5,
            opacity: 0.5,
            color: 'white',
            dashArray: '3',
            fillOpacity: 0.7
        };
    }
	//Set the public transport layer properties. 
    function LSOApublic(feature) {
        return {
            fillColor: getColor(feature.properties.<?php echo $publictransport;?>),
            weight: 0.5,
            opacity: 0.5,
            color: 'white',
            dashArray: '3',
            fillOpacity: 0.7
        };
    }
	//Set the cycling layer properties. 
    function bikeStyle(feature) {
        return {
            fillColor: getColor(feature.properties.<?php echo $biketransport;?>),
            weight: 0.5,
            opacity: 0.5,
            color: 'white',
            dashArray: '3',
            fillOpacity: 0.7
        };
    }

	//Set the walking layer properties. 
    function walkStyle(feature) {
        return {
            fillColor: getColor(feature.properties.<?php echo $walktransport;?>),
            weight: 0.5,
            opacity: 0.5,
            color: 'white',
            dashArray: '3',
            fillOpacity: 0.7
        };
    }

	//Get the map data. 
    function mapData(data){

        //begin to create a geojson container object
        var geojson = {
            "type": "FeatureCollection",
            "features": []
        };

        //split returned PHP data into records
        var datalines = data.split("!! ;");
        datalines.pop();

        //build geojson features
        datalines.forEach(function(dline){
            try {
                //split records into attribute values and geometry
                dataelements = dline.split("!! ");

                //create a feature object, complete with geometry details
                var feature = {
                    "type": "Feature",
                    "properties": {}, //properties object container
                    "geometry": JSON.parse(dataelements[columns.length]) //parse geometry
                };

                //and fill in its properties and values
                for (var i=0; i<columns.length; i++){
                    feature.properties[columns[i]] = dataelements[i];
                };
                geojson.features.push(feature);
            }
            catch(err) {
                ;
            }

            geojson.features.push(feature);
        });


        //make a Leaflet map layer from GeoJSON data
        var privateLayer = L.geoJson(geojson, {style: LSOAprivate,
            //add a pop-up
            onEachFeature: function (feature, layer) {
                var html = "FCA Scores: <br/>";
                var i = 0;
                for (prop in feature.properties){

                    if(i == 0){
                        var labeltype = "Private: "
                    }else if ( i == 1){
                        var labeltype = "Public: "
                    }else if ( i == 2){
                        var labeltype = "Cycling: "
                    }else if ( i == 3){
                        var labeltype = "Walking: "
                    }
                    html += labeltype + feature.properties[prop]+"<br>";
                    i++;
                };
                layer.bindPopup(html);
            }
        });


        //map.addLayer(privateLayer);

        //make a Leaflet map layer from GeoJSON data
        var publicLayer = L.geoJson(geojson, {style: LSOApublic,
            //add a pop-up
            onEachFeature: function (feature, layer) {
                var html = "FCA Scores: <br/>";
                var i = 0;
                for (prop in feature.properties){

                    if(i == 0){
                        var labeltype = "Private: "
                    }else if ( i == 1){
                        var labeltype = "Public: "
                    }else if ( i == 2){
                        var labeltype = "Cycling: "
                    }else if ( i == 3){
                        var labeltype = "Walking: "
                    }
                    html += labeltype + feature.properties[prop]+"<br>";
                    i++;
                };
                layer.bindPopup(html);
            }
        });

        //make a Leaflet map layer from GeoJSON data
        var bikeLayer = L.geoJson(geojson, {style: bikeStyle,
            //add a pop-up
            onEachFeature: function (feature, layer) {
                var html = "FCA Scores: <br/>";
                var i = 0;
                for (prop in feature.properties) {

                    if (i == 0) {
                        var labeltype = "Private: "
                    } else if (i == 1) {
                        var labeltype = "Public: "
                    } else if (i == 2) {
                        var labeltype = "Cycling: "
                    } else if (i == 3) {
                        var labeltype = "Walking: "
                    }
                    html += labeltype + feature.properties[prop] + "<br>";
                    i++;
                };
                layer.bindPopup(html);
            }
        });

        //make a Leaflet map layer from GeoJSON data
        var walkLayer = L.geoJson(geojson, {style: walkStyle,
            //add a pop-up
            onEachFeature: function (feature, layer) {
                var html = "FCA Scores: <br/>";
                var i = 0;
                for (prop in feature.properties) {

                    if (i == 0) {
                        var labeltype = "Private: "
                    } else if (i == 1) {
                        var labeltype = "Public: "
                    } else if (i == 2) {
                        var labeltype = "Cycling: "
                    } else if (i == 3) {
                        var labeltype = "Walking: "
                    }
                    html += labeltype + feature.properties[prop] + "<br>";
                    i++;
                };
                layer.bindPopup(html);
            }
        });

		//Add the layers to the layer controls with names. 
        map.addLayer(publicLayer);
        layerControl.addOverlay(privateLayer, "Private FCA Scores");
        layerControl.addOverlay(publicLayer, "Public FCA Scores");
        layerControl.addOverlay(bikeLayer, "Bike FCA Scores");
        layerControl.addOverlay(walkLayer, "Walk FCA Scores");


    };

    function mapPointData(data, dbsupply){

        //begin to create a geojson container object
        var geojson = {
            "type": "FeatureCollection",
            "features": []
        };

        var supplytablestyle = get_marker_style(dbsupply);

        //split returned PHP data into records
        var datalines = data.split("!! ;");
        datalines.pop();

        //build geojson features
        datalines.forEach(function(dline){

            try {
                //split records into attribute values and geometry
                dataelements = dline.split("!! ");

                //create a feature object, complete with geometry details
                var feature = {
                    "type": "Feature",
                    "properties": {}, //properties object container
                    "geometry": JSON.parse(dataelements[columns.length]) //parse geometry
                };

                //and fill in its properties and values
                for (var i=0; i<columns.length; i++){
                    feature.properties[columns[i]] = dataelements[i];
                };

                geojson.features.push(feature);
            }
            catch(err) {
                ;
            }

            geojson.features.push(feature);
        });

        //styling options for markers
        var stlye1 = {
            radius: 8,
            fillColor: "#ffff00",	color: "#000",
            weight: 1, opacity: 1, fillOpacity: 0.7
        };
        var style2 = {
            radius: 6,
            fillColor: "#0078ff",	color: "#000",
            weight: 1,	opacity: 1,	fillOpacity: 0.7
        };


        //make a Leaflet map layer from GeoJSON data
        var facilities = L.geoJson(geojson, {

            //modify the symbolism
            pointToLayer: function (feature, latlng) {
                return L.marker(latlng, {icon: supplytablestyle});
            },
            //add a pop-up
            onEachFeature: function (feature, layer) {

                var html = "Facility:<br/>";
                for (prop in feature.properties){

                    html += feature.properties[prop]+"<br>";

                };
                layer.bindPopup(html);
            }
        });

        map.addLayer(facilities);

        layerControl.addOverlay(facilities, '<?php echo $facilityreturn?>');

    };

	//Get marker styles - based on image and image size. 
    function get_marker_style(facilitytype){
        if(facilitytype == "supplytables.gymnastics_facilities"){
            var setMarkerStyle = L.icon({
                iconUrl: 'markers/big-gymnastics.png',
                iconSize:     [25, 41], // size of the icon
                iconAnchor:   [12, 41], // point of the icon which will correspond to marker's location
                popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
            });
        }

        else if(facilitytype == "supplytables.grass_pitches"){
            var setMarkerStyle = L.icon({
                iconUrl: 'markers/big-football.png',
                iconSize:     [25, 41], // size of the icon
                iconAnchor:   [12, 41], // point of the icon which will correspond to marker's location
                popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
            });
        }
        else if(facilitytype == "supplytables.hockey_pitches"){
            var setMarkerStyle = L.icon({
                iconUrl: 'markers/big-hockey.png',
                iconSize:     [25, 41], // size of the icon
                iconAnchor:   [12, 41], // point of the icon which will correspond to marker's location
                popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
            });
        }
        else if(facilitytype == "supplytables.swimming_pools"){
            var setMarkerStyle = L.icon({
                iconUrl: 'markers/big-swimming.png',
                iconSize:     [25, 41], // size of the icon
                iconAnchor:   [12, 41], // point of the icon which will correspond to marker's location
                popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
            });
        }
        else if(facilitytype == "supplytables.fitness_suites"){
            var setMarkerStyle = L.icon({
                iconUrl: 'markers/big-fitness.png',
                iconSize:     [25, 41], // size of the icon
                iconAnchor:   [12, 41], // point of the icon which will correspond to marker's location
                popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
            });
        }
        else if(facilitytype == "supplytables.tennis_courts"){
            var setMarkerStyle = L.icon({
                iconUrl: 'markers/big-tennis.png',
                iconSize:     [25, 41], // size of the icon
                iconAnchor:   [12, 41], // point of the icon which will correspond to marker's location
                popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
            });
        }
        else {
            var setMarkerStyle = L.icon({
                iconUrl: 'markers/big-football.png',
                iconSize:     [25, 41], // size of the icon
                iconAnchor:   [12, 41], // point of the icon which will correspond to marker's location
                popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
            });
        }
        return setMarkerStyle;

    }

</script>
</body>
</html>