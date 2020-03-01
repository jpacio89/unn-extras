<?php
    $USER_COUNT = 100;
    $userRanking = getUserRanking();
    $picks = array();
    $fixtures = array();
    for ($i = 0; $i < $USER_COUNT; ++$i) {
        echo "$i / $USER_COUNT\n";
        $results = getMatches($userRanking[$i]);
        for($j = 0; $j < count($results); ++$j) {
            $picks[$results[$j]['MatchId']][$userRanking[$i]] = $results[$j]['Pick'];
            if ($i > 0 && $results[$j]['Fixture'] !== $fixtures[$results[$j]['MatchId']]) {
                echo "Inconsistent fixture... ".$results[$j]['Fixture']." vs ".$fixtures[$results[$j]['MatchId']]." @ match ".$results[$j]['MatchId']."\n";
                if ($results[$j]['Fixture'] != '?') {
                    echo "Fixing...\n";
                    $fixtures[$results[$j]['MatchId']] = $results[$j]['Fixture'];
                }
            } else {
                $fixtures[$results[$j]['MatchId']] = $results[$j]['Fixture'];
            }
        }
        $json = array(
            'Fixtures' => $fixtures,
            'Picks' => $picks,
        );
        file_put_contents('data/raw.json', json_encode($json));
        sleep(5);
    }

    //print_r($picks);

    $fixturesArray = array_keys($fixtures);

    $header = ['MatchId', 'Fixture'];

    for ($j = 0; $j < $USER_COUNT; ++$j) {
        $header[] = "U".$userRanking[$j];
    }

    $csv = csvstr($header);
    file_put_contents('data/superbru.csv', "$csv\n");

    for ($i = 0; $i < count($fixturesArray); ++$i) {
        $row = [$fixturesArray[$i]];
        $row[] = $fixtures[$fixturesArray[$i]];
        for ($j = 0; $j < $USER_COUNT; ++$j) {
            $pick = $picks[$fixturesArray[$i]][$userRanking[$j]];
            $row[] = $pick != '' ? $pick : '?';
        }
        $csv = csvstr($row);
        file_put_contents('data/superbru.csv', "$csv\n", FILE_APPEND);
    }

    // INFO: get ranking - Premier League
    function getUserRanking() {
        $content = shell_exec("curl -s 'https://www.superbru.com/premierleague_predictor/ajax/write_tournament_leaderboard.php?r=28&s=1&e=250&_=1583019798295' -H 'Connection: keep-alive' -H 'Pragma: no-cache' -H 'Cache-Control: no-cache' -H 'Upgrade-Insecure-Requests: 1' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'Sec-Fetch-Dest: document' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9' -H 'Sec-Fetch-Site: none' -H 'Sec-Fetch-Mode: navigate' -H 'Sec-Fetch-User: ?1' -H 'Accept-Language: pt-PT,pt;q=0.9,en-US;q=0.8,en;q=0.7,sv;q=0.6' -H 'Cookie: PHPSESSID=8k467f5lh46i7qemrtnbsudik0; Superbru_edition=2; _fbp=fb.1.1582936511261.121799545; _ga=GA1.2.1004681880.1582936511; _gid=GA1.2.559868545.1582936511; sb_cookies=y; fbm_6483758771=base_domain=.superbru.com; sb_ses=wymD3lS70ze2TNeqWW2iJMxe%2BFdk9emZXB0qyt0VhfrqhJus9FhTVY9kmUE%3D; sb_per=wymb3li300r4F82%2BLDa1BONoznZP5tv%2BKAwcnMZIrKHBhv7ftRECBKVaz1UWbdF2S%2BCeBQ%3D%3D; X-Mapping-fjhppofk=5DD0FD83DABDD0F4E7D64A0BADCF5FDE; sb_dob_check=iT7Pgg%2FviCe%2FVw%3D%3D' --compressed");
        $parts = explode("onclick='bru.player.", $content);
        $userIds = [];
        // TODO: check why starts in 1 and not 0
        for ($i = 1; $i < count($parts); ++$i) {
            list($tournamentId, $playerId) = sscanf($parts[$i], "showProfile(%d,%d)");
            $userIds[] = $playerId;
        }
        return $userIds;
    }

    function getMatches($playerId) {
        // echo $playerId."\n";
        $content = shell_exec("curl -s 'https://www.superbru.com/premierleague_predictor/ajax/write_perf_tipping_overview.php?player_id=" . $playerId . "&_=1583063336582' -H 'Connection: keep-alive' -H 'Pragma: no-cache' -H 'Cache-Control: no-cache' -H 'Upgrade-Insecure-Requests: 1' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36' -H 'Sec-Fetch-Dest: document' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9' -H 'Sec-Fetch-Site: none' -H 'Sec-Fetch-Mode: navigate' -H 'Sec-Fetch-User: ?1' -H 'Accept-Language: pt-PT,pt;q=0.9,en-US;q=0.8,en;q=0.7,sv;q=0.6' -H 'Cookie: PHPSESSID=8k467f5lh46i7qemrtnbsudik0; Superbru_edition=2; _fbp=fb.1.1582936511261.121799545; _ga=GA1.2.1004681880.1582936511; _gid=GA1.2.559868545.1582936511; sb_cookies=y; fbm_6483758771=base_domain=.superbru.com; sb_ses=wymD3lS70ze2TNeqWW2iJMxe%2BFdk9emZXB0qyt0VhfrqhJus9FhTVY9kmUE%3D; sb_per=wymb3li300r4F82%2BLDa1BONoznZP5tv%2BKAwcnMZIrKHBhv7ftRECBKVaz1UWbdF2S%2BCeBQ%3D%3D; X-Mapping-fjhppofk=5DD0FD83DABDD0F4E7D64A0BADCF5FDE; sb_dob_check=iT7Pgg%2FviCe%2FVw%3D%3D' --compressed");
        $parts = explode("data-brutip='<b>", $content);
        $matches = [];
        for ($i = 1; $i < count($parts); ++$i) {
            $parts2 = explode("<\/table>", $parts[$i]);
            $matchRaw = $parts2[0];
            $matches[] = getDatasetRow($matchRaw);
            //echo "\n\n";
        }
        return $matches;
    }

    function getDatasetRow($content) {
        $matchId = getMatchId($content);
        $fixture = getFixture($content);
        $pick = getPick($content);
        $outcome = getMatchOutcome($fixture);
        $prediction = getMatchOutcome($pick);

        /*echo "Match = $matchId\n";
        echo "Fixture = $fixture\n";
        echo "Pick = $pick\n";
        echo "Result = $outcome\n";
        echo "Prediction = $prediction\n";*/

        return array(
            'MatchId' => $matchId,
            'Fixture' => $outcome,
            'Pick'    => $prediction
        );
    }

    function getMatchId($content) {
        $parts = explode('Match ', $content);
        $parts = explode('<\/div>', $parts[1]);
        $match = $parts[0];
        return $match;
    }

    function getFixture($content) {
        $parts = explode('<td>Fixture:<\/td><td>', $content);
        $parts = explode('<\/td>', $parts[1]);
        $fixture = $parts[0];
        return $fixture;
    }

    function getPick($content) {
        $parts = explode('Your pick:&nbsp;&nbsp;<\/td><td>', $content);
        $parts = explode('<\/td>', $parts[1]);
        $pick = $parts[0];
        return $pick;
    }

    function getMatchOutcome($match) {
        $parts = explode(' - ', $match);
        $p2 = explode(' ', $parts[0]);
        $scoreHome = $p2[count($p2)-1];
        $p3 = explode(' ', $parts[1]);
        $scoreAway = $p3[0];

        //echo "$scoreHome\n";
        // echo "$scoreAway\n";

        if ($scoreHome == $scoreAway && $scoreHome != '') {
            return 'Draw';
        } else if ($scoreHome > $scoreAway) {
            return 'HomeWin';
        } else if ($scoreHome < $scoreAway) {
            return 'HomeLose';
        }

        // echo "$match\n";
        return '?';
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
?>
