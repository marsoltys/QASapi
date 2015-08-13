<?php
/**
 * Created by PhpStorm.
 * User: soltm
 * Date: 11/08/2015
 * Time: 14:09
 */

//// !!!!!!!   Allow crosdomain requests !!!!!!! ////
header("Access-Control-Allow-Origin: *");

/**
 * Class QAS
 */
class QAS {

    /**
     * @var SoapClient PHP SoapClient class
     */
    private $client;

    /**
     * @var string response type - options: json | html | plain
     */
    public $returnType = "json";

    /**
     * @var bool strip response only to relevant information (list of adressess, address details, etc.)
     */
    public $strip = true;

    /**
     * @param $wsdl string[optional] WSDL file url
     */
    function __construct($wsdl = "http://vsvr-app539.euser.eroot.eadidom.com:2026/proweb.wsdl") {
        $this->client = new SoapClient($wsdl);
    }

    /**
     * Performs search
     * @param string $searchTerm address string to search
     * @param string $engine - options Singleline | Typedown | Verification | Keyfinder
     * @return mixed|string
     */
    public function search($searchTerm, $engine = 'Singleline') {

        $search = $this->formatSearchQuery($searchTerm);

        var_dump($search);

        $result =  $this->client->DoSearch([
            'Engine' =>   [
                '_' => $engine,
                'Flatten' =>false,
                'Intensity' => "Exact",
                //  'Threshold' => '5',
                //  'Timeout'=>'',
            ],
            'Country'=> 'GBR',
            'Search' => $search
        ]);

        if($this->strip) {
            $result = $result->QAPicklist->PicklistEntry;
            if(is_object($result)) {
                return $result->Picklist;
            }
        }

        return $this->format($result);
    }

    public function refine ($moniker, $refinement) {
        $result = $this->client->DoRefine([
            'Moniker'=>$moniker,
            'Refinement'=>$refinement
        ]);

        if($this->strip) {
            $result = $result->QAPicklist->PicklistEntry;
            if(is_object($result)) {
                return $result->Picklist;
            }
        }

        return $this->format($result);
    }

    /**
     * @param string $moniker
     * @param string $layout
     * @return mixed|string
     */
    public function getAddressDetails($moniker, $layout = '< Default >') {
        $result =   $this->client->DoGetAddress([
            'Layout'=> $layout,
            'Moniker' => $moniker
        ]);

        return $this->format($result);
    }

    /**
     * @param array|object $data object data returned by SOAP request
     * @return mixed|string
     */
    private function format($data){

        switch (strtolower($this->returnType)) {

            case 'plain':
                header('Content-Type: application/xml; charset=utf-8');
                return $data;
                break;

            case 'json':
                header('Content-Type: application/json');
                return $this->convertToJson($data);
                break;

            default:
            case 'html':
                header('Content-Type: text/html');
                return $this->convertToHtml($data);
                break;
        }
    }

    /**
     * @param array|object $data SOAP response data
     * @return mixed|string
     */
    private function convertToJson($data){

        $json = json_encode($data);

        return $json;
    }

    /**
     * @param array|object $data SOAP response data
     * @return string html formatted data string
     */
    private function convertToHtml($data){

        $html = "";

        if(!$this->strip){
            $data = $data->QAPicklist->PicklistEntry;
            if(is_object($data)) {

                return $data->Picklist." -- ". $data->Moniker;
            }
        }

        foreach($data as $row) {
            $html .= "<div class='address_row'>";
            $lines = explode(",", $row->PartialAddress);
            var_dump($row);
            $length = count($lines);

            $html .= "<a href='//". $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."?Moniker=".$row->Moniker."'>";

            foreach($lines as $key => $line){
                $html .= "<span class='line_$key'>";
                $html .= trim($line);
                $html .= $length == $key + 1 ? "" : ", ";
                $html .= "</span>";
            }
            $html .= "</a></div>";
        }

        return $html;
    }

    private function formatSearchQuery($query) {
        if(is_array($query)) {
            $query = array_filter($query);
            return implode(", ", $query);
        }

        return $query;
    }
}


$qas = new QAS();

if(!empty($_POST)){
    if(!empty($_POST['type'])) {
        $qas->returnType = $_POST['type'];
        unset ($_POST['type']);
    }
    $qas->strip = false;

    /// do search ->do refinement (if !FullAddress -> get Picklist -> do refinement)

    echo $qas->refine("0aIGBRDAzeBwEAAgAAAAEurLs4h7AAAAAAAAAA", "");

    //echo $qas->search($_POST);
}else if(!empty($_GET['Moniker'])) {
    echo $qas->getAddressDetails($_GET['Moniker']);
}