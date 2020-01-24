<?php

    $datasetPath = 'etoro-bitcoin.csv';
    $header = fgets(fopen($datasetPath, 'r'));

    $cache = array();
    $features = explode(',', $header);
    $input = array();
    $featureCount = count($features);

    for ($i = 0; $i < $featureCount; ++$i) {
        $feature = $features[$i];

        echo "$i / $featureCount -> $feature\n";

        if ($feature === 'time') {
            $dateTime = gmdate("Y-m-d\TH:i:s\Z");
            $today = getUnixTime($dateTime);
            $input[$feature] = $today;
        } else if ($feature === 'gains') {
            $input[$feature] = '-';
        } else {
            $input[$feature] = checkPortfolio($cache, $feature);
            sleep(15);
        }
        print_r($input);
    }

    echo json_encode($input);

    function checkPortfolio(&$cache, $feature) {
        $parts = explode('@', $feature);
        $userId = str_replace('U', '', $parts[0]);
        $instrumentId = str_replace('I', '', $parts[1]);

        echo "$userId -> $instrumentId\n";
        //print_r($cache);

        if (isset($cache[$userId])) {
            return getAction($cache[$userId], $instrumentId);
        }

        // $json = file_get_contents('https://www.etoro.com/sapi/trade-data-real/live/public/portfolios?cid=' . $userId);
        $json = shell_exec("curl -s 'https://www.etoro.com/sapi/trade-data-real/live/public/portfolios?cid=" . $userId . "&client_request_id=3bd5cee0-c50d-4468-990e-de2419c95f2e' -H 'authority: www.etoro.com' -H 'pragma: no-cache' -H 'cache-control: no-cache' -H 'accept: application/json, text/plain, */*' -H 'x-csrf-token: ZFQiHqN02X9H3Zg-d50Ddw__' -H 'accounttype: Real' -H 'applicationidentifier: ReToro' -H 'applicationversion: 210.0.1' -H 'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36' -H 'sec-fetch-site: same-origin' -H 'sec-fetch-mode: cors' -H 'referer: https://www.etoro.com/people/renrenjinrong/portfolio' -H 'accept-encoding: gzip, deflate, br' -H 'accept-language: pt-PT,pt;q=0.9,en-US;q=0.8,en;q=0.7,sv;q=0.6' -H 'cookie: ASP.NET_SessionId=mpbfppwdrbwgjbj23rqu4cwt; TS01b45bb7=01f1b32d7e47e298bb36ca2927bde3ae4787be9d4677d1f8d581850f5cfaa0f131033ce20ae13069c6d63dace5ecbeeab63431ef44; visid_incap_20269=HVFlKpy6RTyuR0YKBm/xLhrQ510AAAAAQUIPAAAAAABEC5E00T0H5vPJOVRE5OZa; nlbi_20269=0v0JNGPg6iUGiMq3Gx3A0gAAAAAVvpXJRW73a1Tz4Vuybstn; nlbi_20269_1746065=h4D5D3SUXg0seIx7Gx3A0gAAAAAR2xQBIJPns1E8upn/kOSV; _ga=GA1.2.321380744.1575473179; _gcl_au=1.1.617336414.1575473179; _fbp=fb.1.1575473179635.1944494105; eToroLocale=en-gb; G_ENABLED_IDPS=google; AffiliateWizAffiliateID=AffiliateID=70284&ClickBannerID=0&SubAffiliateID=NOR_Brand_EN_70284|AG_70190696767|KW_Etoro|MT_p&Custom=&ClickDateTime=12/6/2019 12:11:49 PM&UserUniqueIdentifier=eeaf7891-8825-4480-af33-3943bbc01c2b; Stickiness=D|XepFi|XepFi; _gac_UA-2056847-65=1.1575634310.EAIaIQobChMIgoeamf-g5gIVxYayCh22tAC-EAAYASAAEgIqTPD_BwE; _gcl_aw=GCL.1575634310.EAIaIQobChMIgoeamf-g5gIVxYayCh22tAC-EAAYASAAEgIqTPD_BwE; liveagent_oref=; liveagent_sid=97dceca3-16d9-4a79-8308-9412c0bd76c2; liveagent_vc=2; liveagent_ptid=97dceca3-16d9-4a79-8308-9412c0bd76c2; _dcmn_p=FinvY2lkPVdMUWEzVjNxU1MwMEUwV0RBUEE; _dcmn_p=FinvY2lkPVdMUWEzVjNxU1MwMEUwV0RBUEE; _dcmn_p=FinvY2lkPVdMUWEzVjNxU1MwMEUwV0RBUEE; visid_incap_1964444=JkwHChwlRDav2s+zpVndWHyO6l0AAAAAQUIPAAAAAACdtA0AUClvFpUXxkpQ3Wve; G_AUTHUSER_H=0; incap_ses_631_20269=8FfeTYhO536yLA8adcTBCLvrJl4AAAAAC4kdmUZMoiYNlkXltpzSFQ==; _gid=GA1.2.112373921.1579609039; hp_preferences=%7B%22locale%22%3A%22en-gb%22%7D; etoro_first_page=https%3A//www.etoro.com/; incap_ses_721_20269=PAOzQ4AcJ3wCoIXB3IIBCiv3Kl4AAAAAVFoOil9p3ERyQRJWH5NOPg==; incap_ses_630_20269=rq4uZpMuGizacPlz9ja+CMH8Kl4AAAAAqNvHRmCu2dYaLVfNRfXWiQ==; ab_nav_bar_cookie=v3; user_regulation_id=1; incap_ses_276_20269=ufxtTYFgoiGOg+EDS47UAwpeK14AAAAALES1+KM6Cs8Yea9ZuZSWTw==; mp_dbbd7bd9566da85f012f7ca5d8c6c944_mixpanel=%7B%22distinct_id%22%3A%201814080%2C%22%24device_id%22%3A%20%2216ed184f97f13-099a817d1c7df1-3960720f-13c680-16ed184f980e1f%22%2C%22%24initial_referrer%22%3A%20%22https%3A%2F%2Fwww.etoro.com%2F%3Fgclid%3DEAIaIQobChMIvO6a96ac5gIVT4uyCh28aQrFEAAYASAAEgKU8vD_BwE%26utm_medium%3DSEM%26utm_source%3D70284%26utm_content%3D0%26utm_serial%3DNOR_Brand_EN_70284%7CAG_70190696767%7CKW_%2520etoro%7CMT_b%26utm_campaign%3DNOR_Brand_EN_70284%7CAG_70190696767%7CKW_%2520etoro%7CMT_b%26utm_term%3D%26gclid%3DEAIaIQobChMIvO6a96ac5gIVT4uyCh28aQrFEAAYASAAEgKU8vD_BwE%22%2C%22%24initial_referring_domain%22%3A%20%22www.etoro.com%22%2C%22%24user_id%22%3A%201814080%2C%22KYCExperiment333%22%3A%20%22AfterDeposit%22%7D; TMIS2=9a74f2a102375b68ab5acc9b53dbc65d0e20a7fb2fe9a337d0e1246c5a7e14cf68439ec2a3104971b4fb319b72e0f1c089923e76f21bac26413a6c6ce4a78894b3e64dff5bebda02da78679c458a98fa4fd32c68c71c013b22d060a7642a7a3a1034b281e43199d3241695562ad162ecbd836467a2ca1a8e0f7122775a1bd7d1ca; _gat_UA-2056847-65=1; TS01047baf=01d53e58181e8d357aaa1395cf94dcff4626d5ef58fa25d608d2571cce84e80188333c97eb904ece18f7ce06cb4c8dbf0c0ac80e34; _gat=1' --compressed");

        // echo $json;

        $actions = json_decode($json, true);
        $cache[$userId] = $actions;
        return getAction($cache[$userId], $instrumentId);
    }

    function getAction($actions, $instrumentId) {
        $filtered = array_filter($actions['AggregatedPositions'], function($v, $k) use($instrumentId) {
            return $v['InstrumentID'] == $instrumentId;
        }, ARRAY_FILTER_USE_BOTH);
        $filtered = array_values($filtered);
        if (count($filtered) > 0) {
            return $filtered[0]['Direction'] == 'Buy' ? 'TRUE' : 'FALSE';
        }
        return '?';
    }

    function getUnixTime($dateTime) {
        $date = new DateTime($dateTime);
        $date->setTime(0,0,0);
        $time = $date->getTimestamp();
        return $time;
    }
?>
