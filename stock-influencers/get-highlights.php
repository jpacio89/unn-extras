<?php
    $host = 'http://localhost:7000';
    $sessions = getSavedSessions();
    $insights = array();

    for ($i = 0; $i < count($sessions); ++$i) {
        $session = $sessions[$i];
        loadSession($session);
        $input = getRealtimePortfolios();
        $prediction = getPrediction($input);
        if ($prediction['status'] === 500) {
            continue;
        }
        $sessionParts = explode('-', $session);
        $insights[$sessionParts[0]][$sessionParts[1]] = $prediction;
        file_put_contents('digest/insights.json', json_encode($insights));
        
        echo $session . ' -> ' . json_encode($prediction) . "\n";
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

    function getSavedSessions() {
        global $host;
        $sessions = file_get_contents("{$host}/list/saved/sessions");
        $sessions = json_decode($sessions, true);
        return $sessions;
    }

?>
