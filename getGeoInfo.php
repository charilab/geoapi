<?php
/*
Parameters:
    kind: {convenience|pass|signals|toilets|spa|bicycle|services|viewpoint}
    x, y, distance: 中心点指定（距離の単位はメートル）
    xmin, ymin, xmax, ymax: 範囲指定（中心点指定と組み合わせた場合はこちらが優先）
    mode: {geojson|gpx}
*/

include 'overpass.php';
include 'ov_charilog.php';

header("Access-Control-Allow-Origin: *");
date_default_timezone_set('Asia/Tokyo');
mb_language("uni");
mb_internal_encoding("UTF-8");
mb_http_input("auto");
mb_http_output("UTF-8");

$lon = (isset($_GET['x'])) ? $_GET['x'] : 138.909589;
$lat = (isset($_GET['y'])) ? $_GET['y'] : 35.126847;
$distance = (isset($_GET['distance'])) ? $_GET['distance'] : 200.0;
$minLon = (isset($_GET['xmin'])) ? $_GET['xmin'] : NULL;
$minLat = (isset($_GET['ymin'])) ? $_GET['ymin'] : NULL;
$maxLon = (isset($_GET['xmax'])) ? $_GET['xmax'] : NULL;
$maxLat = (isset($_GET['ymax'])) ? $_GET['ymax'] : NULL;
$limit  = (isset($_GET['limit'])) ? $_GET['limit'] : NULL;

if (isset($_GET['kind'])) {
    $kind = $_GET['kind'];
} else if ($argc > 1) {
    $kind = $argv[1];
} else {
    $kind = "convenience";
}

$data=array();

if ($minLon == NULL) {
    $result = get_nodes_by_center($kind, $lat, $lon, $distance);
} else {
    $result = get_nodes_by_box($kind, $minLat, $minLon, $maxLat, $maxLon);
}
if ($result != NULL) {
    array_push($data, $result);
    print xml2geoJson($data, false);
} else {
    printf("Unknow kind: %s\n", $kind);
}
?>
