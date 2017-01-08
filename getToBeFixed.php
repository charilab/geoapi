<?php
/*
Parameters:
    kind: {convenience|signals|toilets}
    x, y, distance: 中心点指定（距離の単位はメートル）
    xmin, ymin, xmax, ymax: 範囲指定（中心点指定と組み合わせた場合はこちらが優先）
    mode: {geojson|gpx}
*/

include 'overpass.php';
include 'ov_tobefixed.php';

header("Access-Control-Allow-Origin: *");
date_default_timezone_set('Asia/Tokyo');
mb_language("uni");
mb_internal_encoding("UTF-8");
mb_http_input("auto");
mb_http_output("UTF-8");

$lon = (isset($_GET['x'])) ? $_GET['x'] : 138.909589;
$lat = (isset($_GET['y'])) ? $_GET['y'] : 35.126847;
$distance = (isset($_GET['distance'])) ? $_GET['distance'] : 200.0;
$minLon = (isset($_GET['xmin'])) ? $_GET['xmin'] : 138.5;
$minLat = (isset($_GET['ymin'])) ? $_GET['ymin'] : 34.6;
$maxLon = (isset($_GET['xmax'])) ? $_GET['xmax'] : 139.2;
$maxLat = (isset($_GET['ymax'])) ? $_GET['ymax'] : 35.4;
$limit  = (isset($_GET['limit'])) ? $_GET['limit'] : NULL;
$mode  = (isset($_GET['mode'])) ? $_GET['mode'] : 'geojson';

if (isset($_GET['kind'])) {
    $kind = $_GET['kind'];
} else if ($argc > 1) {
    $kind = $argv[1];
} else {
    $kind = "convenience";
}

//
// Convert from simpleXML to GPX
//
function xml2gpx_one($xml, $kind) {
    $nodes = $xml->xpath('//node');
    foreach ($nodes as $i => $node) {
        $coords = sprintf("lat=\"%s\" lon=\"%s\"", $node['lat'], $node['lon']);
        printf("  <wpt {$coords}>\n");
        switch($kind) {
            case 'convenience':
                printf("    <name>コンビニ</name>\n");
                break;
            case 'toilets':
                printf("    <name>トイレ</name>\n");
                break;
            case 'signals':
                printf("    <name>信号機</name>\n");
                break;
        }
        printf("    <desc></desc>\n");
        printf("    <cmt></cmt>\n");
        printf("  </wpt>\n");
    }
    return count($nodes);
}

function xml2gpx($data, $kind) {
    $num=0;
    print("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
    print("<gpx version=\"1.0\">\n");
    foreach ($data as $i => $xml) {
        if (count($xml->node) > 0) {
            $num += xml2gpx_one($xml, $kind);
        }
    }
    print("</gpx>\n");
}

$data=array();

if ($minLon == NULL) {
    $result = get_nodes_by_center($kind, $lat, $lon, $distance);
} else {
    $result = get_nodes_by_box($kind, $minLat, $minLon, $maxLat, $maxLon);
}

if ($result != NULL) {
    array_push($data, $result);
    switch($mode) {
        case "geojson":
            print xml2geoJson($data, false);
            break;
        case "gpx":
            print xml2gpx($data, $kind);
            break;
        case "raw":
        default:
            printf("%s is not supported.\n", $mode);
    }
} else {
    printf("Unknown kind: %s\n", $kind);
}
?>
