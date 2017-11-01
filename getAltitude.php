<?php
include 'libAltitude.php';

date_default_timezone_set('Asia/Tokyo');
mb_language("uni");
mb_internal_encoding("UTF-8");
mb_http_input("auto");
mb_http_output("UTF-8");

$points = $_POST['latLng'];

$results = getAltitude($points);

$coords="";
$flag = 1;
foreach($results as $p) {
    if ($flag == 1) {
        if ($p['alt'] > -1000.0) {
            $flag = 0;
            $coords = sprintf("[%f, %f, %f]", $p['lng'], $p['lat'], $p['alt']);
        }
    } else {
        if ($p['alt'] > -1000.0) {
            $coords = sprintf("%s,[%f, %f, %f]", $coords, $p['lng'], $p['lat'], $p['alt']);
        }
    }
}

echo <<< EOM
{
    "name": "Altitude information",
    "type": "FeatureCollection",
    "features": [{
        "type": "Feature",
        "geometry": {
            "type": "LineString",
            "coordinates": [ {$coords} ]
        },
        "properties": null
    }]
}
EOM;
?>
