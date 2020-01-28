<?php

    $investors = getInvestors();
    $trades = [];
    $investorCount = count($investors);

    for ($i = 0; $i < $investorCount; ++$i) {
        echo "$i / $investorCount\n";
        $localTrades = getTrades($investors[$i], $i);
        $trades = array_merge($trades, $localTrades);
        sleep(30);
    }

    print_r($trades);

    file_put_contents('data/trades.json', json_encode($trades));

    function getTrades($user, $curlIndex = 0) {
        global $cookie;
        $trades = [];

        /*$cookies = @shell_exec("curl --silent --output /dev/null --cookie-jar - 'https://www.etoro.com/people/olivierdanvel/portfolio/history'");
        $cookies = extractCookies($cookies);
        $cook = [];
        for($i = 0; $i < count($cookies); ++$i) {
            $cook[] = $cookies[$i]['name']."=".$cookies[$i]['value'];
        }
        $cookie = implode('; ', $cook);*/
        /*if ($curlIndex % 10 === 0) {
            $cookie = @shell_exec('node get-etoro-cookie.js');
        }*/

        //echo $cookie."\n";
        include "curls.php";
        // print_r($cookies);

        $output = @shell_exec($requests[$curlIndex % count($requests)]);

        $parts = explode('var model = ', $output);
        $parts = explode("txt =", $parts[1]);
        $output = substr(trim($parts[0]), 0, -1);

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
        $json = file_get_contents('https://www.etoro.com/sapi/rankings/rankings/?blocked=false&bonusonly=false&client_request_id=35bd969d-650e-40a1-b082-9970109dc318&copyblock=false&istestaccount=false&lastactivitymax=30&optin=true&page=1&pagesize=100&period=OneYearAgo&sort=-gain&tradesmin=500&verified=true');

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

    function extractCookies($string) {
        $lines = explode(PHP_EOL, $string);

        foreach ($lines as $line) {

            $cookie = array();

            // detect httponly cookies and remove #HttpOnly prefix
            if (substr($line, 0, 10) == '#HttpOnly_') {
                $line = substr($line, 10);
                $cookie['httponly'] = true;
            } else {
                $cookie['httponly'] = false;
            }

            // we only care for valid cookie def lines
            if( strlen( $line ) > 0 && $line[0] != '#' && substr_count($line, "\t") == 6) {

                // get tokens in an array
                $tokens = explode("\t", $line);

                // trim the tokens
                $tokens = array_map('trim', $tokens);

                // Extract the data
                $cookie['domain'] = $tokens[0]; // The domain that created AND can read the variable.
                $cookie['flag'] = $tokens[1];   // A TRUE/FALSE value indicating if all machines within a given domain can access the variable.
                $cookie['path'] = $tokens[2];   // The path within the domain that the variable is valid for.
                $cookie['secure'] = $tokens[3]; // A TRUE/FALSE value indicating if a secure connection with the domain is needed to access the variable.

                $cookie['expiration-epoch'] = $tokens[4];  // The UNIX time that the variable will expire on.
                $cookie['name'] = urldecode($tokens[5]);   // The name of the variable.
                $cookie['value'] = urldecode($tokens[6]);  // The value of the variable.

                // Convert date to a readable format
                $cookie['expiration'] = date('Y-m-d h:i:s', $tokens[4]);

                // Record the cookie.
                $cookies[] = $cookie;
            }
        }

        return $cookies;
    }

?>
