<?php
    $host = 'http://localhost:7000';
    $timeWindows = array(7, 14, 28);
    $sessions = array(
        "iota-i100018",
        "eos-i100022",
        "bcash-i100002",
        "ripple-i100003",
        "bitcoin-i100000",
        "litecoin-i100005",
        "gold-i18",
        "usa500-i27",
        "oil-i17",
        "eu50-i43",
        "nasdaq100-i28",
        "netflix-i1127",
        "germany30-i32",
        "google-i1002"
    );

    for ($i = 0; $i < count($sessions); ++$i) {
        for($j = 0; $j < count($timeWindows); ++$j) {
            $session = "{$sessions[$i]}-{$timeWindows[$j]}d";
            loadSession($session);
            $input = getRealtimePortfolios();
            $prediction = getPrediction($input);
            echo $session . ' -> ' . json_encode($prediction) . "\n";
        }
    }

    function loadSession($session) {
        global $host;
        $opts = array('http' => array('method'  => 'POST'));
        $context  = stream_context_create($opts);
        file_get_contents("{$host}/load/session/1?name={$session}", false, $context);
    }

    function getRealtimePortfolios() {
        $input = @shell_exec('php get-realtime.php');
        $input = json_decode($input, true);
        $input = array('blacklist' => [], 'seeds' => $input);
        $input = json_encode($input);
        return $input;
    }

    function getPrediction($input) {
        global $host;
        $prediction = @shell_exec("curl -s '{$host}/simulate/1' -H 'Content-Type: application/json' -H 'Accept: application/json, text/plain, */*' --data-binary '$input' --compressed");
        $prediction = json_decode($prediction, true);
        return $prediction;
    }

?>
