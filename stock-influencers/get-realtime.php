<?php

    $datasetPath = 'etoro.csv';
    $header = fgets(fopen($datasetPath, 'r'));
    $header = trim(preg_replace('/\s\s+/', ' ', $header));

    $cache = array();
    $features = explode(',', $header);
    $input = array();
    $featureCount = count($features);

    for ($i = 0; $i < $featureCount; ++$i) {
        $feature = $features[$i];

        echo "$i / $featureCount -> $feature\n";

        if ($feature === 'time') {
            $dateTime = gmdate("Y-m-d\TH:i:s\Z");
            $today = getUnixTime($dateTime);
            //$input[$feature] = $today;
            $input[$feature][$today] = true;
        } else if ($feature === 'gains') {
            //$input[$feature] = '-';
            $input[$feature]['-'] = true;
        } else {
            //$input[$feature] = checkPortfolio($cache, $feature, $i);
            $input[$feature][checkPortfolio($cache, $feature, $i)] = true;
        }
        print_r($input);
        sleep(3);
    }

    echo json_encode($input);

    function checkPortfolio(&$cache, $feature, $curlIndex) {
        $parts = explode('@', $feature);
        $userId = str_replace('U', '', $parts[0]);
        $instrumentId = str_replace('I', '', $parts[1]);

        include "curls_realtime.php";

        echo "$userId -> $instrumentId\n";
        //print_r($cache);

        if (isset($cache[$userId])) {
            return getAction($cache[$userId], $instrumentId);
        }

        $json = shell_exec($requests[$curlIndex % count($requests)]);

        $actions = json_decode($json, true);
        $cache[$userId] = $actions;
        return getAction($cache[$userId], $instrumentId);
    }

    function getAction($actions, $instrumentId) {
        $filtered = array_filter($actions['AggregatedPositions'], function($v, $k) use($instrumentId) {
            return $v['InstrumentID'] == $instrumentId;
        }, ARRAY_FILTER_USE_BOTH);
        $filtered = array_values($filtered);
        if (count($filtered) > 0) {
            return $filtered[0]['Direction'] == 'Buy' ? 'TRUE' : 'FALSE';
        }
        return '?';
    }

    function getUnixTime($dateTime) {
        $date = new DateTime($dateTime);
        $date->setTime(0,0,0);
        $time = $date->getTimestamp();
        return $time;
    }
?>
