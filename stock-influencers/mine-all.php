<?php
    $host = 'http://localhost:7000';
    $timeWindows = array(1, 3, 7, 14, 28, 56);

    for ($instrumentId = 1; $instrumentId < 30; ++$instrumentId) {
        for ($j = 0; $j < count($timeWindows); ++$j) {
            $timeWindow = $timeWindows[$j];
            $name = "i{$instrumentId}-{$timeWindow}d";

            echo "> $name\n";

            $ret = generateDataset($instrumentId, $timeWindow);

            if ($ret === FALSE) {
                continue;
            }

            loadDataset($name);
            mineDataset();

            do {
                $isMining = isMining();
                sleep(1);
            }
            while($isMining === TRUE);

            saveSession();
        }
    }

    function isMining() {
        global $host;
        $status = file_get_contents("{$host}/mine/status/1");
        $status = json_decode($status, true);
        $keys = array_keys($status);
        $keyCount = count($keys);
        $completeCount = 0;
        for ($i = 0; $i < $keyCount; ++$i) {
            $statusItem = $status[$keys[$i]];
            if ($statusItem['statusLabel'] === 'DONE') {
                $completeCount++;
            }
        }
        print_r("  Mining: $completeCount / $keyCount\n");
        return $keyCount === 0 || $completeCount < $keyCount;
    }

    function saveSession() {
        global $host;
        $opts = array('http' => array('method'  => 'POST'));
        $context  = stream_context_create($opts);
        file_get_contents("{$host}/save/session/1", false, $context);
    }

    function mineDataset() {
        global $host;
        shell_exec("curl -s '{$host}/dataset/mine/1' -H 'Accept: application/json, text/plain, */*' -H 'Content-Type: application/json' --data-binary '{\"targetFeature\":\"gains\",\"featureBlacklist\":[\"time\"],\"groupCount\":{}}' --compressed");
    }

    // TODO: check error generating dataset
    function generateDataset($instrumentId, $timeWindow) {
        shell_exec("php get-dataset.php {$instrumentId} {$timeWindow} > etoro.csv");
        return TRUE;
    }

    function loadDataset($name) {
        global $host;
        $opts = array('http' => array('method'  => 'POST'));
        $context  = stream_context_create($opts);
        file_get_contents("{$host}/dataset/load/1?name={$name}", false, $context);
    }

 ?>
