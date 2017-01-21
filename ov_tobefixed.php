<?php

function build_query_convenience($desc) {
    $invalid_convenience_names = array(
        'セブンイレブン', '7', '[Ss]even', 'セブン・イレブン',
        '[Ll]awson', 'LAWSON',
        '[Ff]amily',
        'Mini Stop', 'MINI STOP', '[Mm]ini[Ss]top', 'Mini-Stop',
        '[Cc]ircle',
        '[Ss]unkus', 'Thankusu', 'SUNKUS',
        'ローソン100', 'ローソンショップ100',
        'ThreeF',
        '[Ss]eico',
        'Daily',
        'Coco'
    );

    $q="";
    foreach($invalid_convenience_names as $name) {
        if ($desc) {
            $q = sprintf("%snode(%s)[shop=convenience]['name'~'^%s'];out meta;\n", $q, $desc, $name);
        } else {
            $q = sprintf("%snode[shop=convenience]['name'~'^%s'];\n", $q, $name);
        }
    }
    return $q;
}

function get_nodes($kind, $desc) {
    switch($kind) {
    case "convenience":
        $query = build_query_convenience($desc);
        $nodes = call_overpass($query);
        break;
    case "signals":
        $query = sprintf("node(%s)[highway=traffic_signals]['name'!~'.']['noname'!~'.'];out meta;", $desc);
        $nodes = call_overpass($query);
        break;
    case "toilets":
        $query = sprintf("node(%s)[amenity=toilets]['wheelchair'!~'.'];out meta;", $desc);
        $nodes = call_overpass($query);
        break;
    default:
        $nodes = null;
    }
    return $nodes;
}

function get_ways($kind, $desc) {
    switch($kind) {
    case "bike":
        $ways = null;
        break;
    case "nobike":
        $ways = null;
        break;
    default:
        $ways = null;
    }
    return $ways;
}
?>
