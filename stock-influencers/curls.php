<?php
    $cookies = file_get_contents('data/cookies.txt');
    $TMIS2 = explode("\n", $cookies);

    $requests = [];

    for ($i = 0; $i < count($TMIS2) - 1; ++$i) {
        $requests[] = "curl -s 'https://www.etoro.com/sapi/trade-data-real/history/public/credit/flat?CID=" . $user['CustomerId'] . "&ItemsPerPage=5000&PageNumber=1&StartTime=2018-01-25T23:00:00.000Z&client_request_id=3b4d9cab-06ec-4699-98d2-aa3f9290555c' -H 'accept: application/json, text/plain, */*' -H 'cookie: TMIS2=" . $TMIS2[$i] . ";' --compressed";
    }
?>
