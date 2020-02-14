<?php
    $summary = file_get_contents('data/trades.summary.json');
    $summary = json_decode($summary, true);

    $investorIds = array_map(function($entry) {
        return $entry['investor'];
    }, $summary);
    $investorCount = count($investorIds);

    for ($i = 0; $i < $investorCount; ++$i) {
        $investorId = $investorIds[$i];
        $portfolio = getPortfolio($investorId, $i);
        if (!isset($portfolio['AggregatedPositions'])) {
            echo "$i / $investorCount -> Portfolio: $investorId\n";
            echo "|Warning| Invalid response from server...\n";
            sleep(10);
            continue;
        }
        $tradeCount = count($portfolio['AggregatedPositions']);
        echo "$i / $investorCount -> Portfolio: $investorId, Trades: $tradeCount\n";
        $wrapper = array(
            'created_time' => time(),
            'data' => $portfolio
        );

        file_put_contents('data/portfolio-'.$investorId.'.investor.json', json_encode($wrapper));
        sleep(10);
    }

    function getPortfolio($userId, $curlIndex) {
        include "curls_realtime.php";
        $json = shell_exec($requests[$curlIndex % count($requests)]);
        $actions = json_decode($json, true);
        return $actions;
    }
?>
