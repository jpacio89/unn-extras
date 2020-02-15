<?php

    $features = file_get_contents('http://localhost:7000/session/features/1');
    $features = json_decode($features, true);
    // print_r($features);

    $cache = array();
    $input = array();
    $featureCount = count($features);

    for ($i = 0; $i < $featureCount; ++$i) {
        $feature = $features[$i];

        // echo "$i / $featureCount -> $feature\n";

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
            $v = checkPortfolio($cache, $feature, $i);
            if ($v === FALSE) {
                $v = '?';
            }
            $input[$feature][$v] = true;
        }
        //print_r($input);
    }

    echo json_encode($input);

    function checkPortfolio(&$cache, $feature, $curlIndex) {
        $parts = explode('@', $feature);
        $userId = str_replace('U', '', $parts[0]);
        $instrumentId = str_replace('I', '', $parts[1]);

        //echo "$userId -> $instrumentId\n";
        //print_r($cache);

        if (isset($cache[$userId])) {
            return getAction($cache[$userId], $instrumentId);
        }

        $portfolio = @file_get_contents('data/portfolio-'.$userId.'.investor.json');
        $portfolio = json_decode($portfolio, true);

        if (!isset($portfolio['data'])) {
            return false;
        }

        $cache[$userId] = $portfolio['data'];
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
