<?php
function get_bike($desc) {
    $query = sprintf("(way(%s)[highway=cycleway];node(w)->.x;);out;", $desc);
    $result = call_overpass($query);

    return $result;
}

function get_nobike($desc) {
    $query = sprintf("(way(%s)[bicycle=no];node(w)->.x;);out;", $desc);
    $result = call_overpass($query);

    return $result;
}

function get_nodes($kind, $desc) {
    switch($kind) {
    case "convenience":
        $query = sprintf("node(%s)[shop=convenience];out;", $desc);
        $nodes = call_overpass($query);
        break;
    case "pass":
        $query = sprintf("node(%s)[mountain_pass=yes];out;", $desc);
        $nodes = call_overpass($query);
        break;
    case "signals":
        $query = sprintf("node(%s)[highway=traffic_signals];out;", $desc);
        $nodes = call_overpass($query);
        break;
    case "toilets":
        $query = sprintf("node(%s)[amenity=toilets];out;", $desc);
        $nodes = call_overpass($query);
        break;
    case "spa":
        $query = sprintf("node(%s)[amenity=spa];out;", $desc);
        $query = sprintf("%snode(%s)[amenity=public_bath];out;", $query, $desc);
        $nodes = call_overpass($query);
        break;
    case "bicycle":
        $query = sprintf("node(%s)[shop=bicycle];out;", $desc);
        $query = sprintf("%snode(%s)[shop=sports];out;", $query, $desc);
        $nodes = call_overpass($query);
        break;
    case "services":
        $query = sprintf("node(%s)[highway=services];out;", $desc);
        $nodes = call_overpass($query);
        break;
    case "viewpoint":
        $query = sprintf("node(%s)[tourism=viewpoint];out;", $desc);
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
        $ways = get_bike($desc);
        break;
    case "nobike":
        $ways = get_nobike($desc);
        break;
    default:
        $ways = null;
    }
    return $ways;
}
?>
