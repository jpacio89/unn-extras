<?php

    $investors = getInvestors();
    $trades = [];
    $investorCount = count($investors);

    for ($i = 0; $i < $investorCount; ++$i) {
        echo "$i / $investorCount\n";
        $localTrades = getTrades($investors[$i], $i);
        $trades = array_merge($trades, $localTrades);
        sleep(3);
    }

    print_r($trades);

    file_put_contents('data/trades.json', json_encode($trades));

    function getTrades($user, $curlIndex = 0) {
        include "curls.php";

        $trades = [];
        $output = shell_exec($requests[$curlIndex % count($requests)]);
        $json = @json_decode($output, true);

        if (!isset($json['PublicHistoryPositions'])) {
            echo "[WARNING] Empty trades: $output\n";
            return $trades;
        }

        for ($i = 0; $i < count($json['PublicHistoryPositions']); ++$i) {
            $trade = $json['PublicHistoryPositions'][$i];
            $openTime = getUnixTime($trade['OpenDateTime']);
            $closeTime = getUnixTime($trade['CloseDateTime']);
            $trades[] = array(
//                'Date' => $trade['OpenDateTime'],
                'OpenDateTime' => $openTime,
                'CloseDateTime' => $closeTime,
                'CID' => $trade['CID'],
                'InstrumentID' => $trade['InstrumentID'],
                'IsBuy' => $trade['IsBuy'] == '1' ? 'TRUE' : 'FALSE',
                'OpenRate' => $trade['OpenRate'],
                'CloseRate' => $trade['CloseRate']
            );
        }

//        print_r($trades);
        return $trades;
    }

    function getUnixTime($dateTime) {
        $date = new DateTime($dateTime);
        $date->setTime(0,0,0);
        $time = $date->getTimestamp();
        return $time;
    }

    function getInvestors() {
        $investors = [];

        //$json = file_get_contents('https://www.etoro.com/sapi/rankings/rankings/?activeweeksmin=40&blocked=false&bonusonly=false&client_request_id=cd8928c2-7fc8-4291-913f-0633e12c32bf&copyblock=false&copyinvestmentpctmax=5&copytradespctmax=5&dailyddmin=-7&gainmax=80&gainmin=5&hasavatar=true&istestaccount=false&lastactivitymax=31&maxmonthlyriskscoremax=4&maxmonthlyriskscoremin=1&optin=true&page=1&pagesize=200&period=OneYearAgo&profitablemonthspctmin=55&profitableweekspctmin=55&sort=-weeklydd&tradesmin=25&verified=true&weeklyddmin=-14');
        $json = file_get_contents('https://www.etoro.com/sapi/rankings/rankings/?activeweeksmax=52&activeweeksmin=39&blocked=false&bonusonly=false&client_request_id=a1644c51-8eed-4c8d-ae1c-a205f047fdc5&copyblock=false&istestaccount=false&optin=true&page=1&pagesize=100&period=OneYearAgo&sort=-weeklydd&tradesmin=100');
        $json = json_decode($json, true);

        if (!isset($json['Items'])) {
            return $investors;
        }

        for ($i = 0; $i < count($json['Items']); ++$i) {
            $user = $json['Items'][$i];
            $investors[] = array(
                'CustomerId' => $user['CustomerId'],
                'UserName' => $user['UserName']
            );
        }

        // print_r($investors);

        return $investors;
    }

?>
