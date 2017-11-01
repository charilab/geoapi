<?php
include 'settings.php';

function getAltitude($points) {
    // initialize
    $descspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("file", "/dev/null", "a")
    );
    $cwd = '/tmp';
    $env = array('LD_LIBRARY_PATH' => LD_LIBRARY_PATH,
        'GETALT_TOPO_FILE' => GETALT_TOPO_FILE);
    $process = proc_open(TOOL_PATH.'/getalt', $descspec, $pipes, $cwd, $env);
    if (!is_resource($process)) {
        return null;
    }

    // convert
    foreach ($points as $p) {
        $loc = sprintf("%f %f\n", $p['lat'], $p['lng']);
        fwrite($pipes[0], $loc);
    }
    fclose($pipes[0]);

    for($i=0; !feof($pipes[1]); $i++) {
        $line = fgets($pipes[1]);
        if ($line == "") {
            break;
        }
        $r = explode(" ", $line);
        $points[$i]['alt'] = $r[2];
    };
    fclose($pipes[1]);

    // finalize
    proc_close($process);

    return $points;
}
?>
