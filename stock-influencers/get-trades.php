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
            $unixTime = strtotime($trade['OpenDateTime']);
            $date = new DateTime($trade['OpenDateTime']);
            $date->setTime(0,0,0);
            $time = $date->getTimestamp();
            $trades[] = array(
//                'Date' => $trade['OpenDateTime'],
                'OpenDateTime' => $time,
                'CID' => $trade['CID'],
                'InstrumentID' => $trade['InstrumentID'],
                'IsBuy' => $trade['IsBuy'] == '1' ? 'TRUE' : 'FALSE',
                'OpenRate' => $trade['OpenRate']
            );
        }

//        print_r($trades);
        return $trades;
    }

    function getInvestors() {
        $investors = [];

        $json = file_get_contents('https://www.etoro.com/sapi/rankings/rankings/?activeweeksmin=40&blocked=false&bonusonly=false&client_request_id=cd8928c2-7fc8-4291-913f-0633e12c32bf&copyblock=false&copyinvestmentpctmax=5&copytradespctmax=5&dailyddmin=-7&gainmax=80&gainmin=5&hasavatar=true&istestaccount=false&lastactivitymax=31&maxmonthlyriskscoremax=4&maxmonthlyriskscoremin=1&optin=true&page=1&pagesize=200&period=OneYearAgo&profitablemonthspctmin=55&profitableweekspctmin=55&sort=-weeklydd&tradesmin=25&verified=true&weeklyddmin=-14');

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
