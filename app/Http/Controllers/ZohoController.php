<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ZohoController extends Controller
{
    private $zohoAuthToken = '1000.466f7e11a961134a82e7ff761aadc1ce.32be6bf6e2370631e20923291bad86e3';

    public function index ()
    {
        if (isset($_POST['create_deal']) && !empty(trim($_POST['name']))) {
            $this->createDeals(trim($_POST['name']));
        }

        return view('zoho_deals', ['title'=>'Создать задачу']);
    }

    private function createDeals ($name)
    {
        $url = "https://www.zohoapis.com/crm/v2/Deals";

        $recordObject = array();
        $recordObject["Deal_Name"]=$name;
        $recordObject["Account_Name"]="King (Sample)";
        $recordObject["Closing_Date"]="2020-08-16";
        $recordObject["State"]="20:Требуется анализ";

        $response = $this->requestCurl($url, $recordObject);

        dump($response['headerMap']);
        dump($response['jsonResponse']);
        dump($response['http_code']);
    }

    private function createTasks ()
    {
        $url = "https://www.zohoapis.com/crm/v2/Tasks";

        $recordObject = array();
        //$recordObject["Modified_By"]="aleksandr112a";
        //$recordObject["Closed_Time"]="2021-08-19T14:00:00+05:30";
        //$recordObject["Who_Id"]="";
        $recordObject["Created_By"]="aleksandr112a";
        $recordObject["Description"]="test";
        $recordObject["Owner"]="aleksandr112a";
        $recordObject["What_Id"]="TEST_Company_777";
        //$recordObject["Recurring_Activity"]='';
        $recordObject["Priority"]="Высокая";
        //$recordObject["Due_Date"]="2021-04-18";
        $recordObject["Status"]="Не запущена";
        $recordObject["Subject"]="Встреча 7";

        $response = $this->requestCurl($url, $recordObject);

        dump($response['headerMap']);
        dump($response['jsonResponse']);
        dump($response['http_code']);
    }

    private function requestCurl ($url, $data, $method = 'POST')
    {
        $curl_pointer = curl_init();

        $curl_options = array();
        $curl_options[CURLOPT_URL] =$url;
        $curl_options[CURLOPT_RETURNTRANSFER] = true;
        $curl_options[CURLOPT_HEADER] = 1;
        $curl_options[CURLOPT_CUSTOMREQUEST] = $method;

        $requestBody = array("data" => array($data));
        $curl_options[CURLOPT_POSTFIELDS]= json_encode($requestBody);

        $headersArray = array();
        $headersArray[] = "Authorization". ":" . "Zoho-oauthtoken " . $this->zohoAuthToken;

        $curl_options[CURLOPT_HTTPHEADER] = $headersArray;

        curl_setopt_array($curl_pointer, $curl_options);

        $result = curl_exec($curl_pointer);
        $responseInfo = curl_getinfo($curl_pointer);
        curl_close($curl_pointer);

        list ($headers, $content) = explode("\r\n\r\n", $result, 2);
        if(strpos($headers," 100 Continue")!==false){
            list( $headers, $content) = explode( "\r\n\r\n", $content , 2);
        }
        $headerArray = (explode("\r\n", $headers, 50));
        $headerMap = array();
        foreach ($headerArray as $key) {
            if (strpos($key, ":") != false) {
                $firstHalf = substr($key, 0, strpos($key, ":"));
                $secondHalf = substr($key, strpos($key, ":") + 1);
                $headerMap[$firstHalf] = trim($secondHalf);
            }
        }
        $jsonResponse = json_decode($content, true);
        if ($jsonResponse == null && $responseInfo['http_code'] != 204) {
            list ($headers, $content) = explode("\r\n\r\n", $content, 2);
            $jsonResponse = json_decode($content, true);
        }

        return array(
            'headerMap' => $headerMap,
            'jsonResponse' => $jsonResponse,
            'http_code' => $responseInfo['http_code']
        );
    }

}
