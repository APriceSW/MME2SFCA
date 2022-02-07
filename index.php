<!DOCTYPE html>
<html>
<!-- Includes for the style sheets and other important extensions -->
<link rel="stylesheet" type="text/css" href="FCAStyle.css">
<script src="/staff/leaflet-dev/jquery-3.4.1.min.js"></script>
<title>home</title>
<meta charset="utf-8">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous"></head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<link rel="stylesheet" type="text/css" href="FCAStyle.css">
<style>
    section {
    }

    label {
        display: block;
        padding: 5px 10px;
        margin: 5px 0;
        font: 14px/20px Arial, sans-serif;
        background-color: #ccc;
        border-radius: 7px;

    &:hover {
         background-color: gold;
         cursor: pointer;
     }
    }

    input[type="checkbox"].a + label {
        position: relative;
        top: 1px;
    }

    input[type="checkbox"]a:checked + label {
        background-color: lightgreen;
    }

    /* The switch - the box around the slider */
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
        float:right;
    }

    /* Hide default HTML checkbox */
    .switch input {display:none;}

    /* The slider */
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input.default:checked + .slider {
        background-color: #444;
    }
    input.primary:checked + .slider {
        background-color: #2196F3;
    }
    input.success:checked + .slider {
        background-color: #8bc34a;
    }
    input.info:checked + .slider {
        background-color: #3de0f5;
    }
    input.warning:checked + .slider {
        background-color: #FFC107;
    }
    input.danger:checked + .slider {
        background-color: #f44336;
    }

    input:focus + .slider {
        box-shadow: 0 0 1px #2196F3;
    }

    input:checked + .slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }
</style>
<style>

    .slidecontainer {
        width: 100%;
    }

    .slidertwo {
        -webkit-appearance: none;
        width: 100%;
        height: 25px;
        background: #d3d3d3;
        outline: none;
        opacity: 0.7;
        -webkit-transition: .2s;
        transition: opacity .2s;
    }

    .sliderthree {
        -webkit-appearance: none;
        width: 25%;
        height: 100%;
        background: #d3d3d3;
        outline: none;
        opacity: 0.7;
        -webkit-transition: .2s;
        transition: opacity .2s;
    }

    .slidertwo:hover {
        opacity: 1;
    }

    .slidertwo::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 25px;
        height: 25px;
        background: #4CAF50;
        cursor: pointer;
    }

    .slidertwo::-moz-range-thumb {
        width: 25px;
        height: 25px;
        background: #4CAF50;
        cursor: pointer;
    }

</style>
<style>

    .border {
        margin: 6px;
    }

    .innerborder{
        margin-top:10px;
        border: #cdcdcd medium solid;
        border-radius: 10px;
        -moz-border-radius: 10px;
        -webkit-border-radius: 10px;
        padding: 6px;
    }
    .button {
        background-color: #4b6da3; /* blue */
        border: none;
        color: white;
        padding: 15px 32px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        width: 100%;
    }

    .leftpanel{
        border-style: solid;
        border-left-style: none;
        border-bottom-style: none;
        border-right-style: none;
        border-top-style: none;
        border-left-width: 15px;
        margin-left: 15px;
        margin-right: 5px;
        margin-top: 20px;
        margin-bottom: 20px;
        padding: 5%;
        min-height: 600px;
        min-width: 500px;
        box-shadow: 1px 3px 5px #888888;
    }
    .mapimagepanel{
        border-style: solid;
        border-left-style: none;
        border-bottom-style: none;
        border-right-style: none;
        border-top-style: none;
        border-left-width: 15px;
        margin-left: 5px;
        margin-right: 15px;
        margin-top: 20px;
        margin-bottom: 20px;
        padding: 1%;
        min-height: 800px;
        box-shadow: 1px 3px 5px #888888;
    }

    .buttonr {
        display: inline-block;
        border-radius: 4px;
        background-color: #f4511e;
        border: none;
        color: #FFFFFF;
        text-align: center;
        font-size: 28px;
        padding: 20px;
        width: 200px;
        transition: all 0.5s;
        cursor: pointer;
        margin: 5px;
    }

    .buttonr span {
        cursor: pointer;
        display: inline-block;
        position: relative;
        transition: 0.5s;
    }

    .buttonr span:after {
        content: '\00bb';
        position: absolute;
        opacity: 0;
        top: 0;
        right: -20px;
        transition: 0.5s;
    }

    .buttonr:hover span {
        padding-right: 25px;
    }

    .buttonr:hover span:after {
        opacity: 1;
        right: 0;
    }
    .hide{
        display:none;
    }
</style>
<style>

    div.container {
        text-align: center;
    }
</style>
<style>
    [slider] {
        position: relative;
        height: 14px;
        border-radius: 10px;
        text-align: left;
        margin: 45px 0 10px 0;
    }

    [slider] > div {
        position: absolute;
        left: 13px;
        right: 15px;
        height: 14px;
    }

    [slider] > div > [inverse-left] {
        position: absolute;
        left: 0;
        height: 14px;
        border-radius: 10px;
        background-color: #CCC;
        margin: 0 7px;
    }

    [slider] > div > [inverse-right] {
        position: absolute;
        right: 0;
        height: 14px;
        border-radius: 10px;
        background-color: #CCC;
        margin: 0 7px;
    }

    [slider] > div > [range] {
        position: absolute;
        left: 0;
        height: 14px;
        border-radius: 14px;
        background-color: #1ABC9C;
    }

    [slider] > div > [thumb] {
        position: absolute;
        top: -7px;
        z-index: 2;
        height: 28px;
        width: 28px;
        text-align: left;
        margin-left: -11px;
        cursor: pointer;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.4);
        background-color: #FFF;
        border-radius: 50%;
        outline: none;
    }

    [slider] > input[type=range] {
        position: absolute;
        pointer-events: none;
        -webkit-appearance: none;
        z-index: 3;
        height: 14px;
        top: -2px;
        width: 100%;
        -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";
        filter: alpha(opacity=0);
        -moz-opacity: 0;
        -khtml-opacity: 0;
        opacity: 0;
    }

    div[slider] > input[type=range]::-ms-track {
        -webkit-appearance: none;
        background: transparent;
        color: transparent;
    }

    div[slider] > input[type=range]::-moz-range-track {
        -moz-appearance: none;
        background: transparent;
        color: transparent;
    }

    div[slider] > input[type=range]:focus::-webkit-slider-runnable-track {
        background: transparent;
        border: transparent;
    }

    div[slider] > input[type=range]:focus {
        outline: none;
    }

    div[slider] > input[type=range]::-ms-thumb {
        pointer-events: all;
        width: 28px;
        height: 28px;
        border-radius: 0px;
        border: 0 none;
        background: red;
    }

    div[slider] > input[type=range]::-moz-range-thumb {
        pointer-events: all;
        width: 28px;
        height: 28px;
        border-radius: 0px;
        border: 0 none;
        background: red;
    }

    div[slider] > input[type=range]::-webkit-slider-thumb {
        pointer-events: all;
        width: 28px;
        height: 28px;
        border-radius: 0px;
        border: 0 none;
        background: red;
        -webkit-appearance: none;
    }

    div[slider] > input[type=range]::-ms-fill-lower {
        background: transparent;
        border: 0 none;
    }

    div[slider] > input[type=range]::-ms-fill-upper {
        background: transparent;
        border: 0 none;
    }

    div[slider] > input[type=range]::-ms-tooltip {
        display: none;
    }

    [slider] > div > [sign] {
        opacity: 0;
        position: absolute;
        margin-left: -11px;
        top: -39px;
        z-index:3;
        background-color: #1ABC9C;
        color: #fff;
        width: 28px;
        height: 28px;
        border-radius: 28px;
        -webkit-border-radius: 28px;
        align-items: center;
        -webkit-justify-content: center;
        justify-content: center;
        text-align: center;
    }

    [slider] > div > [sign]:after {
        position: absolute;
        content: '';
        left: 0;
        border-radius: 16px;
        top: 19px;
        border-left: 14px solid transparent;
        border-right: 14px solid transparent;
        border-top-width: 16px;
        border-top-style: solid;
        border-top-color: #1ABC9C;
    }

    [slider] > div > [sign] > span {
        font-size: 12px;
        font-weight: 700;
        line-height: 28px;
    }

    [slider]:hover > div > [sign] {
        opacity: 1;
    }
</style>
<style>
    html, body{
        width:100%;
        height:100%:
        padding:0px;
        margin-right: 0px;
    }

    /* The Modal (background) */
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1; /* Sit on top */
        padding-top: 100px; /* Location of the box */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0,0,0); /* Fallback color */
        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    }

    /* Modal Content */
    .modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
    }

    /* The Close Button */
    .close {
        color: #aaaaaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }
</style>
<style>
    ul {
        list-style-type: none;
        margin: 0;
        padding: 0;
        overflow: hidden;
        background-color: #38527a;
    }

    li {
        float: left;
    }

    li a {
        display: block;
        color: white;
        text-align: center;
        padding: 14px 16px;
        text-decoration: none;    }

    li a:hover {
        background-color: #253650;
        color: white;
        text-decoration: none;
    }
    .footer {
        position: fixed;
        left: 0;
        bottom: 0;
        width: 100%;
        background-color: white;
        border-style: solid;
        border-width: 5px 0px 0px 0px;
        border-color:#38527a;
        color: white;
        text-align: center;
        padding: 10px;
    }
    .homebutton {
        background-color: #38527a;
        border: none;
        color: white;
        padding: 15px 32px;
        height: 150px;
        width: 450px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 32px;
        margin: 4px 2px;
        cursor: pointer;
        transition-duration: 0.4s;
        border: 2px solid #fff;
        box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2), 0 6px 20px 0 rgba(0,0,0,0.19);
    }
    .homebutton:hover {
        background-color: white;
        color: #38527a;
        border: 2px solid #38527a;
    }
</style>


</head>
<body>

<div class="container-fluid text-center" Style="background-color: #4b6da3; color: white; padding-top: 15px; padding-bottom: 15px; width: 100%; ">

    <h1>Home</h1>

</div>
<ul >
    <li><a class="active" href="index.php" ><img src="menuicons/home-colour.png" width="25" height="25"> Home</a></li>
    <li><a class="active" href="FacilityViewer.php" ><img src="menuicons/map-colour.png" width="25" height="25"> View Facilties</a></li>
    <li><a class="active" href="SetMMTable.php"><img src="menuicons/calculator-colour.png" width="25" height="25"> Accessibility Calculator</a></li>
    <li><a class="active" href="SetMapOutput.php"><img src="menuicons/results-colour.png" width="25" height="25"> View Results</a></li>

    <li style="float:right"><a href="index.php"><img src="menuicons/help.png" width="25" height="25"> Help</a></li>
    <li style="float:right"><a class="ODMatrixBuilder.php" href="index.php"><img src="menuicons/settings-colour.png" width="25" height="25"> Setup</a></li>
</ul>

<button type="button" class="homebutton" onclick="location.href='FacilityViewer.php';"><img src="menuicons/map-colour.png" width="70" height="100">  View Facilities</button>
<button type="button" class="homebutton" onclick="location.href='SetMMTable.php';"><img src="menuicons/calculator-colour.png" width="100" height="100">  Calculate </button>
<button type="button" class="homebutton" onclick="location.href='SetMapOutput.php';"><img src="menuicons/results-colour.png" width="100" height="100">  View Results</button>
<button type="button" class="homebutton" onclick="location.href='ODMatrixBuilder.php';"><img src="menuicons/settings-colour.png" width="100" height="100">  Setup</button>
<button type="button" class="homebutton" onclick="location.href='index.php';"><img src="menuicons/help.png" width="100" height="100">  Help</button>






</body>

<div class="footer">
    <div class = "logo"><img src="logos/sportwales-logo.png" style="float:right; height:100px; padding-right: 10px;" ></div>
    <div class = "logo"><img src="logos/esf-logo.jpg" style="float:right; height:100px; padding-right: 10px;" ></div>
    <div class = "logo"><img src="logos/kess-logo.jpg" style="float:right; height:100px; padding-right: 10px;" ></div>
    <div class = "logo"><img src="logos/uni-logo.jpg" style="float:right; height:100px; padding-right: 10px;" ></div>
</div>
</html>