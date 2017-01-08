<?php
include 'settings.php';

//
// functions for Overpass API
//
function call_overpass_http($query) {
    $endpoint = OVERPASS_URL;
    $context = stream_context_create(['http' => [
        'method'  => 'POST',
        'header' => ['Content-Type: application/x-www-form-urlencoded'],
        'content' => 'data=' . urlencode($query),
    ]]);

    libxml_set_streams_context($context);
    $start = microtime(true);
    $result = simplexml_load_file($endpoint);
    return $result;
}

function call_overpass_direct($query) {
    $descspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("file", "/dev/null", "a")
    );

    $cwd = '/tmp';
    $env = null;

    $start = microtime(true);
    $process = proc_open(TOOL_PATH.'/osm3s_query --quiet --db-dir='.OVERPASS_DB, $descspec, $pipes, $cwd, $env);

    if (is_resource($process)) {
        fwrite($pipes[0], $query);
        fclose($pipes[0]);

        $result = simplexml_load_string(stream_get_contents($pipes[1]));
        fclose($pipes[1]);

        proc_close($process);
    }
    return $result;
}

function call_overpass($query) {
    if (defined('OVERPASS_URL')) {
        return call_overpass_http($query);
    } else {
        return call_overpass_direct($query);
    }
}

//
// Convert from simpleXML to geoJSON
//
function xml2geoJson_one($xml, $with_alt) {
    $flag=1;
    $nodes = $xml->xpath('//node');
    foreach ($nodes as $i => $node) {
        if ($flag == 1) {
            $flag = 0;
        } else {
            print(",\n");
        }
        if ($with_alt) {
            $points = array(
                array("lat" => $node['lat'], "lng" => $node['lon'])
            );
            $r = getAltitude($points);
            $coords = sprintf("[%s, %s, %f]", $node['lon'], $node['lat'], $r[0]['alt']);
        } else {
            $coords = sprintf("[%s, %s, -9999.9]", $node['lon'], $node['lat']);
        }
        echo <<< EOM
{   
    "type": "Feature",
    "geometry": {
        "type": "Point",
        "coordinates": {$coords}
    },
    "id": "{$node['id']}",
    "properties": {

EOM;
        $flag1 = 1;
        $tags = $node->xpath('tag');
        foreach ($tags as $j => $tag) {
            if ($flag1 == 1) {
                $flag1 = 0;
            } else {
                print(",\n");
            }
            $value = str_replace("\"", "\\\"", $tag['v']);
            printf("        \"%s\": \"%s\"", $tag['k'], $value);
        }
        printf("\n    }\n}");
    }
    return count($nodes);
}

function xml2geoJson($data, $with_alt) {
    $num=0;
    $flag=1;
    print("{ \"type\": \"FeatureCollection\",\n");
    print("\"features\": [\n");
    foreach ($data as $i => $xml) {
        if (count($xml->node) > 0) {
            if ($flag == 1) {
                $flag = 0;
            } else {
                print(",\n");
            }
            $num += xml2geoJson_one($xml, $with_alt);
        }
    }
    print("\n]\n");
    print("}\n");
}

function get_nodes_by_center($kind, $lat, $lon, $around) {
    $desc = sprintf("around:%f,%f,%f", $around, $lat, $lon);
    return get_nodes($kind, $desc);
}

function get_nodes_by_box($kind, $ymin, $xmin, $ymax, $xmax) {
    $desc = sprintf("%f,%f,%f,%f", $ymin, $xmin, $ymax, $xmax);
    return get_nodes($kind, $desc);
}

function get_ways_by_center($kind, $lat, $lon, $around) {
    $desc = sprintf("around:%f,%f,%f", $around, $lat, $lon);
    return get_ways($kind, $desc);
}

function get_ways_by_box($kind, $ymin, $xmin, $ymax, $xmax) {
    $desc = sprintf("%f,%f,%f,%f", $ymin, $xmin, $ymax, $xmax);
    return get_ways($kind, $desc);
}
?>
