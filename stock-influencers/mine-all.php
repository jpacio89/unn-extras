<?php
    $host = 'http://localhost:7000';
    $timeWindows = array(3, 5, 7, 10, 14, 28, 56);
    // [1, 73]
    // [91, 101]
    // [1001, 1056]
    // [1100, 1149]
    // [1200, 1388]
    // [100000, 100032]
    // NOT 20
    $range = array(100000, 100032);

    for ($instrumentId = $range[0]; $instrumentId <= $range[1]; ++$instrumentId) {
        for ($j = 0; $j < count($timeWindows); ++$j) {
            $timeWindow = $timeWindows[$j];
            $name = "i{$instrumentId}-{$timeWindow}d";

            echo "> $name\n";

            $ret = generateDataset($instrumentId, $timeWindow);

            if ($ret === FALSE) {
                continue;
            }

            loadDataset($name);
            sleep(30);
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

    function generateDataset($instrumentId, $timeWindow) {
        shell_exec("php get-dataset.php {$instrumentId} {$timeWindow} > etoro.csv");
        $dataset = file_get_contents('etoro.csv');
        if (strpos($dataset, 'VOID_DATASET') !== FALSE) {
            return FALSE;
        }
        return TRUE;
    }

    function loadDataset($name) {
        global $host;
        $opts = array('http' => array('method'  => 'POST'));
        $context  = stream_context_create($opts);
        file_get_contents("{$host}/dataset/load/1?name={$name}", false, $context);
    }

 ?>
