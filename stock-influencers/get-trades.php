<?php
    $summary = array();

    for ($page = 1; $page < 5; ++$page) {
        $investors = getInvestors($page);
        $investorCount = count($investors);

        for ($i = 0; $i < $investorCount; ++$i) {
            echo "$page -> $i / $investorCount\n";
            $investor = $investors[$i];
            $investorId = $investor['CustomerId'];
            $trades = getTrades($investor, $i);
            $summary[] = array(
                "investor" => $investorId,
                "counts"   => getSummary($trades)
            );
            // print_r($trades);
            file_put_contents('data/trades-'.$investorId.'.investor.json', json_encode($trades));
            file_put_contents('data/trades.summary.json', json_encode($summary));
            sleep(10);
        }
    }


    function getSummary($trades) {
        $vals = array();
        for ($i = 0; $i < count($trades); ++$i) {
            $trade = $trades[$i];
            $vals[$trade['InstrumentID']]++;
        }
        return $vals;
    }

    function getTrades($user, $curlIndex = 0) {
        global $cookie;
        $trades = [];

        include "curls.php";

        // echo "> " . $requests[$curlIndex % count($requests)];

        $output = @shell_exec($requests[$curlIndex % count($requests)]);

        //$parts = explode('var model = ', $output);
        //$parts = explode("txt =", $parts[1]);
        //$output = substr(trim($parts[0]), 0, -1);

        $json = @json_decode($output, true);

        if (!isset($json['PublicHistoryPositions'])) {
            echo "[WARNING] Empty trades @User = ". $user['CustomerId'] .": $output\n";
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

    function getInvestors($page = 1) {
        $investors = [];

        //$json = file_get_contents('https://www.etoro.com/sapi/rankings/rankings/?activeweeksmin=40&blocked=false&bonusonly=false&client_request_id=cd8928c2-7fc8-4291-913f-0633e12c32bf&copyblock=false&copyinvestmentpctmax=5&copytradespctmax=5&dailyddmin=-7&gainmax=80&gainmin=5&hasavatar=true&istestaccount=false&lastactivitymax=31&maxmonthlyriskscoremax=4&maxmonthlyriskscoremin=1&optin=true&page=1&pagesize=200&period=OneYearAgo&profitablemonthspctmin=55&profitableweekspctmin=55&sort=-weeklydd&tradesmin=25&verified=true&weeklyddmin=-14');
        $json = file_get_contents('https://www.etoro.com/sapi/rankings/rankings/?blocked=false&bonusonly=false&client_request_id=35bd969d-650e-40a1-b082-9970109dc318&copyblock=false&istestaccount=false&lastactivitymax=30&optin=true&page='.$page.'&pagesize=200&period=OneYearAgo&sort=-gain&tradesmin=500&verified=true');

        $json = json_decode($json, true);

        if (!isset($json['Items'])) {
            echo "[WARNING] Empty investors\n";
            return [];
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
