<?php

namespace App\Manager;

/**
 * Class FlightsManager
 */
class FlightsManager
{
    public function __construct()
    {
    }

    public function getAirports(string $q) :array
    {
        $post =  http_build_query([
            'q' => $q,
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://airadvisor.com/en/ajax/searchAirports',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Encoding: gzip, deflate',
                'Accept-Language: en-US;q=0.8,en;q=0.7,es;q=0.6,uk;q=0.5',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Cookie: session_id=1693906220; PHPSESSID=79oegj0obi8ch91af5qg79rkto; _gcl_au=1.1.475526558.1693906220; cwv_fv=1; airadvisor-_zldp=vCKMmr7CnywcZZXliu8lzG4F6l3N%2FC6%2Bkl9rmKYUasenD5%2BoUpolfqTE4yYPMK8cUWfhjajgG6o%3D; user-currency=4a54608b6df43abb28be18de02b53f8762475e75~eur; _gid=GA1.2.1393046071.1696054410; _hjSessionUser_3265994=eyJpZCI6ImRlYTRhY2M5LTZmZGYtNTA5OS04YTVjLWNjZDVlZTkzZTQxOCIsImNyZWF0ZWQiOjE2OTM5MDYyMjA5ODcsImV4aXN0aW5nIjp0cnVlfQ==; optimize_uuid=076530fdf7af5611d56ef8a2330effd8f9ad07b1488c297eae; airadvisor-_zldt=15a37da2-a90a-4db2-9c48-1962370058bb-0; session_id=1696069013; _ga=GA1.1.46544640.1693906221; _ga_TXTVZEE1JT=GS1.2.1696069014.4.1.1696070126.14.0.0; _ga_7QWZKHGVQ4=GS1.2.1696069014.4.1.1696070126.14.0.0; _uetsid=7a109f705f5811ee87b4239ac1a9f94c; _uetvid=d5590a204bce11ee8af44d5c96687bcb; _ga_JKB11P8KE6=GS1.1.1696143810.6.0.1696143819.51.0.0',
                'Origin: https://airadvisor.com',
                'Referer: https://airadvisor.com/en',
                'Sec-Ch-Ua: "Google Chrome";v="117", "Not;A=Brand";v="8", "Chromium";v="117"',
                'Sec-Ch-Ua-Mobile: ?0',
                'Sec-Ch-Ua-Platform: "macOS"',
                'Sec-Fetch-Dest: empty',
                'Sec-Fetch-Mode: cors',
                'Sec-Fetch-Site: same-origin',
                'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36',
                'X-Requested-With: XMLHttpRequest',
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        if ($response && mb_strpos($response, "\x1f\x8b\x08") === 0) {
            $response = gzdecode($response);
        }

        curl_close($curl);

        if ($err) {
            throw new \Exception('cURL Error #: ' . $err);
        }

        $response = @json_decode($response, true);

        $result = [];

        if (!empty($response['result'])) {
            foreach ($response['result'] as $item) {
                $result[] = [
                    'id'   => $item['text'],
                    'text' => $item['text'],
                ];
            }
        }

        return $result;
    }

    public function checkFlight(string $airport, string $flight, int $delay, bool $fog) :string
    {
        // need to use some API for check data by fog
        $wasFog = random_int(1,10) >= 7;
        $delayChance = random_int(1,10) >= 3;

        // need to use some API for check data by delay
        $wasDelayMin = $delayChance ? random_int(1,60) : ($wasFog ? random_int(1,60) : 0);
        $departure = $departureFact = strtotime('-' . random_int(1, 14) . ' day -' . random_int(1, 24) * 60 * 60 . ' minutes');
        $departureFact += $wasDelayMin * 60;
        $compensation = $wasDelayMin >= 30;

        // need to use some API for check data by flight departure
        $departureTime = date('Y-m-d H:i', $departure);
        $departureFactTime = date('Y-m-d H:i', $departureFact);

        $response = "Airport: {$airport}<br>"
            . "Flight number: {$flight}<br>"
            . "Departure: {$departureTime} <br>"
            . "Departure in fact: {$departureFactTime} " . ($wasDelayMin ? "<span class='delay-info'>(Departure delay {$wasDelayMin} minutes)</span>" : '') . "<br>"
            . ($delay && $wasDelayMin && $delay > $wasDelayMin ? "<span class='delay-info'>The flight delay was not as big as you said.</span><br>" : '')
            . ($delay && !$wasDelayMin ? "<span class='delay-info'>You claim that there was a flight delay, but <b>according to our data there was no delay.</b></span><br>" : '')
            . (!$fog && $wasFog ? "<span class='delay-info'>You claim that there was no fog, but <b>according to our data there was fog.</b></span><br>" : '')
            . ($fog && !$wasFog ? "<span class='compensation-success'>You say that there was fog (possibly by mistake), but according to our data there was no fog.</span><br>" : '')
            . ($compensation ?
                ($wasFog ?
                    "<span class='delay-info'>We can`t to start compensation process, because at that time there was fog at the airport.</span>"
                    :
                    "<span class='compensation-success'>We can try to start compensation process, because delay more than 30 minutes or more...</span>"
                )
                :
                (!$wasDelayMin ?
                    "<span class='delay-info'><b>This flight departed on schedule.</b> So we can`t to start compensation process...</span>"
                    :
                    "<span class='delay-info'>We can`t to start compensation process, because the delay was less than 30 minutes" . ($wasFog ? " and there was fog at the airport." : '') . ".</span>"
                )
            )
        ;

        return $response;
    }
}
