<?php

    $investors = getInvestors();
    $trades = [];
    $investorCount = count($investors);

    for ($i = 0; $i < $investorCount; ++$i) {
        echo "$i / $investorCount\n";
        $localTrades = getTrades($investors[$i]);
        $trades = array_merge($trades, $localTrades);
        sleep(30);
    }

    print_r($trades);

    function getTrades($user) {
        $trades = [];
        $output = shell_exec("curl -s 'https://www.etoro.com/sapi/trade-data-real/history/public/credit/flat?CID=" . $user['CustomerId'] . "&ItemsPerPage=30&PageNumber=1&StartTime=2019-01-21T23:00:00.000Z&client_request_id=8b85be5c-022b-4538-a508-15eea2bd8d09' -H 'authority: www.etoro.com' -H 'pragma: no-cache' -H 'cache-control: no-cache' -H 'accept: application/json, text/plain, */*' -H 'x-csrf-token: ZFQiHqN02X9H3Zg-d50Ddw__' -H 'accounttype: Real' -H 'applicationidentifier: ReToro' -H 'applicationversion: 209.0.1' -H 'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36' -H 'sec-fetch-site: same-origin' -H 'sec-fetch-mode: cors' -H 'referer: https://www.etoro.com/people/mikkos/portfolio/history' -H 'accept-encoding: gzip, deflate, br' -H 'accept-language: pt-PT,pt;q=0.9,en-US;q=0.8,en;q=0.7,sv;q=0.6' -H 'cookie: ASP.NET_SessionId=mpbfppwdrbwgjbj23rqu4cwt; TS01b45bb7=01f1b32d7e47e298bb36ca2927bde3ae4787be9d4677d1f8d581850f5cfaa0f131033ce20ae13069c6d63dace5ecbeeab63431ef44; visid_incap_20269=HVFlKpy6RTyuR0YKBm/xLhrQ510AAAAAQUIPAAAAAABEC5E00T0H5vPJOVRE5OZa; nlbi_20269=0v0JNGPg6iUGiMq3Gx3A0gAAAAAVvpXJRW73a1Tz4Vuybstn; nlbi_20269_1746065=h4D5D3SUXg0seIx7Gx3A0gAAAAAR2xQBIJPns1E8upn/kOSV; _ga=GA1.2.321380744.1575473179; _gcl_au=1.1.617336414.1575473179; _fbp=fb.1.1575473179635.1944494105; eToroLocale=en-gb; G_ENABLED_IDPS=google; AffiliateWizAffiliateID=AffiliateID=70284&ClickBannerID=0&SubAffiliateID=NOR_Brand_EN_70284|AG_70190696767|KW_Etoro|MT_p&Custom=&ClickDateTime=12/6/2019 12:11:49 PM&UserUniqueIdentifier=eeaf7891-8825-4480-af33-3943bbc01c2b; Stickiness=D|XepFi|XepFi; _gac_UA-2056847-65=1.1575634310.EAIaIQobChMIgoeamf-g5gIVxYayCh22tAC-EAAYASAAEgIqTPD_BwE; _gcl_aw=GCL.1575634310.EAIaIQobChMIgoeamf-g5gIVxYayCh22tAC-EAAYASAAEgIqTPD_BwE; liveagent_oref=; liveagent_sid=97dceca3-16d9-4a79-8308-9412c0bd76c2; liveagent_vc=2; liveagent_ptid=97dceca3-16d9-4a79-8308-9412c0bd76c2; _dcmn_p=FinvY2lkPVdMUWEzVjNxU1MwMEUwV0RBUEE; _dcmn_p=FinvY2lkPVdMUWEzVjNxU1MwMEUwV0RBUEE; _dcmn_p=FinvY2lkPVdMUWEzVjNxU1MwMEUwV0RBUEE; visid_incap_1964444=JkwHChwlRDav2s+zpVndWHyO6l0AAAAAQUIPAAAAAACdtA0AUClvFpUXxkpQ3Wve; G_AUTHUSER_H=0; incap_ses_631_20269=8FfeTYhO536yLA8adcTBCLvrJl4AAAAAC4kdmUZMoiYNlkXltpzSFQ==; _gid=GA1.2.112373921.1579609039; hp_preferences=%7B%22locale%22%3A%22en-gb%22%7D; etoro_first_page=https%3A//www.etoro.com/; incap_ses_630_20269=f0ZXU2bQ1gR5cyNz9ja+CPTsJl4AAAAAgIMvmvTlYxkuz0T8fnDGMw==; incap_ses_721_20269=RjiEWqwSdnVVyMtu2oIBCsLzJl4AAAAA+4Qr8osFXdkaEyjzoXLkkQ==; incap_ses_276_20269=10JGSRBN6Tf/6B8DS47UA8qDJ14AAAAAlC/DFRgzvZ0c5/yEOgCsCg==; TMIS2=9a74f8b353780f2fbe59d8dc1d9cd901437be0b823f8ee60d0ab36370d3d1bca6b59c5cda64f5474b5f7308366f5b09986ce3b7aa411ad764a396068b7b0929af2ec06e743fbd900c767679f59919fe650d52e7fc9124a3a739f27b376286e2c5277aac9f267deda2741c20e3dd078aee8997330a3c44284082324735f1e8bc2ca; TS01047baf=01d53e58188d58456c958b349d478083f3aa5ef8570b0541768693f788c435597a8e2cc098ac7dc95abbd4cb1b7fbecd58836cdd7c; mp_dbbd7bd9566da85f012f7ca5d8c6c944_mixpanel=%7B%22distinct_id%22%3A%201814080%2C%22%24device_id%22%3A%20%2216ed184f97f13-099a817d1c7df1-3960720f-13c680-16ed184f980e1f%22%2C%22%24initial_referrer%22%3A%20%22https%3A%2F%2Fwww.etoro.com%2F%3Fgclid%3DEAIaIQobChMIvO6a96ac5gIVT4uyCh28aQrFEAAYASAAEgKU8vD_BwE%26utm_medium%3DSEM%26utm_source%3D70284%26utm_content%3D0%26utm_serial%3DNOR_Brand_EN_70284%7CAG_70190696767%7CKW_%2520etoro%7CMT_b%26utm_campaign%3DNOR_Brand_EN_70284%7CAG_70190696767%7CKW_%2520etoro%7CMT_b%26utm_term%3D%26gclid%3DEAIaIQobChMIvO6a96ac5gIVT4uyCh28aQrFEAAYASAAEgKU8vD_BwE%22%2C%22%24initial_referring_domain%22%3A%20%22www.etoro.com%22%2C%22%24user_id%22%3A%201814080%2C%22KYCExperiment333%22%3A%20%22AfterDeposit%22%7D; _gat_UA-2056847-65=1; _gat=1' --compressed");

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

        print_r($trades);
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
