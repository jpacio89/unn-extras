<?php
    ini_set('memory_limit','512M');
    $INST_COUNT = 1;
    $USER_COUNT = 200;

    $json = file_get_contents('data/trades.json');
    $trades = json_decode($json, true);

    $tradeMap = groupTrades($trades);

    // print_r($trades);

    $users = get($trades, 'CID');
    $intruments = get($trades, 'InstrumentID');
    $times = get($trades, 'OpenDateTime');

    $users = array_slice($users, 0, $USER_COUNT);
    $intruments = array_slice($intruments, 0, $INST_COUNT);

    $candles = getCandles($intruments[0]);

    // print_r($users);
    // print_r($intruments);
    // print_r($times);

    // print_r($tradeMap);
    $rows = [];

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
                    $isBuy = 'N/A';
                }
                $row[] = $isBuy;
            }
        }

        $price0 = $candles[$time]['High'];
        $price1 = $candles[$time + 86400]['Close'];
        $diff = round(($price1 - $price0) * 100 / $price0, 2);
        $row[] = $diff;

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
        for ($i = 0; $i < count($trades); ++$i) {
            $trade = $trades[$i];
            $tradesMap[$trade['OpenDateTime']/(1)][$trade['CID']][$trade['InstrumentID']] = $trade;
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
