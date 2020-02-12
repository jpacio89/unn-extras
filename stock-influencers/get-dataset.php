<?php
    ini_set('memory_limit', '2048M');

    // $INSTRUMENT_INDEX = 0;
    // AUDUSD = 7
    // Bitcoin = 100000
    // AMD = 1832
    // Google = 1002
    // Apple = 1001
    // USDJPY = 5
    // USDCAD = 4
    // Microsoft = 1004
    // Amazon = 1005
    // Oil = 17
    // Gold = 18
    // Wheat = 97
    // EURUSD = 1
    // GER30 = 32
    // SPX500 = 27
    // Tesla = 1111
    // GBPUSD = 2
    // Natural Gas = 22
    // Intel = 1021
    // Twitter = 1153
    // IBM = 1020
    // NVIDIA = 1137
    // Aluminium = 99
    // USDCHF = 6
    // ESP35 = 34
    // FRA40 = 31
    // UK100 = 30
    // NASDAQ 100 = 28
    // JPN225 = 36
    // EU50 = 43
    // HKG50 = 38
    // DJ30 = 29
    // Dollar = 25
    // AUS 200 = 33
    // silver = 19
    // Netflix = 1127
    // CISCO = 1013
    // AutoDesk = 1134
    // Walt Disney = 1016
    // Uber = 1186
    // Ericsson = 2231
    // Facebook = 1003
    // JPMorgan = 1023
    // Nike = 1042
    // Oracle = 1135
    // 3M = 1026
    // GE = 1017
    // Snap = 1979
    // Coca-cola = 1024
    // EURCAD = 13
    // EURGBP = 8
    // EURCHF = 9
    // NZDUSD = 3
    $INSTRUMENT_ETORO_ID = 100000;
    $USER_COUNT = 100;
    $PROFIT_TIME_LINE = 86400 * 14;

    $summary = file_get_contents('data/trades.summary.json');
    $summary = json_decode($summary, true);

    $users = getByInstrument($summary, $INSTRUMENT_ETORO_ID);
    $users = array_slice($users, 0, $USER_COUNT);
//    print_r($users);

    $candles = getCandles($INSTRUMENT_ETORO_ID);
    $intruments = [$INSTRUMENT_ETORO_ID];

    $rows = [];
    $header = ['time'];

    for ($j = 0; $j < count($users); ++$j) {
        $user = $users[$j];
        for ($k = 0; $k < count($intruments); ++$k) {
            $instrument = $intruments[$k];
            $header[] = "U$user@I$instrument";
        }
    }

    $header[] = 'gains';
    echo csvstr($header)."\n";

    $today = strtotime('today midnight');
    $startTime = $today - 86400 * 365;

    for ($i = $startTime; $i < $today; $i += 86400) {
        $time = $i;
        $row = [$time];

        for ($j = 0; $j < count($users); ++$j) {
            $user = $users[$j];
            $trades = file_get_contents('data/trades-'.$user.'.investor.json');
            $trades = json_decode($trades, true);
            $tradeMap = groupTrades($trades);

            for ($k = 0; $k < count($intruments); ++$k) {
                // echo $user;
                $instrument = $intruments[$k];
                $isBuy = $tradeMap[$time][$user][$instrument]['IsBuy'];
                if ($isBuy != 'TRUE' && $isBuy != 'FALSE') {
                    $isBuy = '?';
                }
                $row[] = $isBuy;
            }
        }

        // TODO: try nearby days because the selected day could be in the weekend
        if (!$candles[$time] || !$candles[$time + $PROFIT_TIME_LINE]) {
            continue;
        }

        $price0 = $candles[$time]['Close'];
        $price1 = $candles[$time + $PROFIT_TIME_LINE]['Close'];
        $diff = round(($price1 - $price0) * 100 / $price0, 2);

        if ($diff > 1) {
            $row[] = 'UP';
        } else if ($diff < -1) {
            $row[] = 'DOWN';
        } else {
            $row[] = '-';
        }
        // $row[] = $diff;

        $rows[] = $row;
        echo csvstr($row)."\n";
    }

    // print_r($rows);


    function get($trades, $selector) {
        $vals = array();
        for ($i = 0; $i < count($trades); ++$i) {
            $trade = $trades[$i];
            $vals[$trade[$selector]]++;
        }

        arsort($vals);
        // print_r($vals);

        $keys = array_keys($vals);

        return $keys;
    }

    function getByInstrument($summary, $instrumentId) {
        usort($summary, function($a, $b) use ($instrumentId) {
            return $b['counts'][$instrumentId] - $a['counts'][$instrumentId];
        });

        $sortedUsers = array_map(function($entry) use ($instrumentId) {
            return $entry['investor'];
            //return [$entry['investor'], $entry['counts'][$instrumentId]];
        }, $summary);

        return $sortedUsers;
    }

    function groupTrades($trades) {
        $tradesMap = array();
        $tradeCount = count($trades);
        for ($i = 0; $i < count($trades); ++$i) {
            // echo "$i / $tradeCount\n";
            $trade = $trades[$i];
            for ($time = $trade['OpenDateTime']; $time < $trade['CloseDateTime']; $time += 86400) {
                // echo "\t$time\n";
                $tradesMap[$time][$trade['CID']][$trade['InstrumentID']] = $trade;
            }
        }
        return $tradesMap;
    }

    function csvstr($fields)
    {
        $f = fopen('php://memory', 'r+');
        if (fputcsv($f, $fields) === false) {
            return false;
        }
        rewind($f);
        $csv_line = stream_get_contents($f);
        return rtrim($csv_line);
    }

    function getCandles($instrument) {
        $json = file_get_contents("https://www.etoro.com/sapi/candles/candles/desc.json/OneDay/2000/$instrument");
        $json = json_decode($json, true);
    //    print_r($json);
        $priceMap = array();
        for ($i = 0; $i < count($json['Candles'][0]['Candles']); ++$i) {
            $candle = $json['Candles'][0]['Candles'][$i];
            $fromDate = $candle['FromDate'];
            $fromDateUnix = strtotime($candle['FromDate']);
            $priceMap[$fromDateUnix] = $candle;
        }

//        print_r($priceMap);
        return $priceMap;
    }
?>
