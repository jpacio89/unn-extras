<?php
    $cookies = file_get_contents('data/cookies.txt');
    $TMIS2 = explode("\n", $cookies);
    $requests = [];
    for ($i = 0; $i < count($TMIS2) - 1; ++$i) {
        $requests[] = "curl -s 'https://www.etoro.com/sapi/trade-data-real/live/public/portfolios?cid=" . $userId . "&client_request_id=a823c24e-5083-4655-bc6e-56c2aec8e429' -H 'accept: application/json, text/plain, */*' -H 'cookie: TMIS2=" . $TMIS2[$i] . ";' --compressed";
    }
?>
