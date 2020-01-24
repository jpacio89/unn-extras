<?php
    $INST_COUNT = 1;
    $USER_COUNT = 50;
    $PROFIT_TIME_LINE = 86400 * 7;
    $INSTRUMENT_INDEX = 1;

    $json = file_get_contents('data/trades.json');
    $trades = json_decode($json, true);

    $tradeMap = groupTrades($trades);

    // print_r($trades);

    $users = get($trades, 'CID');
    $intruments = get($trades, 'InstrumentID');
    $times = get($trades, 'OpenDateTime');
    rsort($times);

    $candles = getCandles($intruments[$INSTRUMENT_INDEX]);

    // print_r($intruments[$INSTRUMENT_INDEX]);

    $users = array_slice($users, 0, $USER_COUNT);
    $intruments = array_slice($intruments, $INSTRUMENT_INDEX, $INST_COUNT);

    // print_r($users);
    // print_r($intruments);
    // print_r($times);

    // print_r($tradeMap);
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

    for ($i = 0; $i < count($times); ++$i) {
        $time = $times[$i];
        $row = [$time];
        for ($j = 0; $j < count($users); ++$j) {
            $user = $users[$j];
            for ($k = 0; $k < count($intruments); ++$k) {
                // echo $user;
                $instrument = $intruments[$k];
                $isBuy = $tradeMap[$time][$user][$instrument]['IsBuy'];
                if ($isBuy != 'TRUE' && $isBuy != 'FALSE') {
                    // $x = mt_rand(0,2);
                    // $isBuy = $x == 0 ? 'N/A' : 'Unknown';
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
