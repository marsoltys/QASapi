<?php
/**
 * Created by PhpStorm.
 * User: soltm
 * Date: 11/08/2015
 * Time: 14:09
 */

////  Allow crosdomain requests  ////
header("Access-Control-Allow-Origin: *");

/// Sanitize $_POST
$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);


/**
 * Class QAS
 */
class QAS {

    /**
     * @var SoapClient PHP SoapClient class
     */
    public $client;

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
        $result = "";

        try {
            $result = $this->client->DoSearch([
                'Engine' => [
                    '_' => $engine,
                    'Flatten' => true,
                    'Intensity' => "Exact",
                    //  'Threshold' => '5',
                    //  'Timeout'=>'',
                ],
                'Country' => 'GBR',
                'Search' => $search
            ]);

            if(is_object($result->QAPicklist->PicklistEntry) && $result->QAPicklist->PicklistEntry->CanStep){
                return $this->refine($result->QAPicklist->PicklistEntry->Moniker);
            }

        }catch (SoapFault $fault){
            $this->soapError($fault);
        }


        return $this->format($result);
    }

    public function refine ($moniker, $refinement="") {

        $result = "";

        try {
            $result = $this->client->DoRefine([
                'Moniker'=>$moniker,
                'Refinement'=>$refinement
            ]);
        }catch (SoapFault $fault){
            $this->soapError($fault);
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

        if($this->strip) {
            $data = $data->QAPicklist->PicklistEntry;
            if (is_object($data)) {
                $data = $data->Picklist;
            }
        }

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
        $html = "Results: <br>";

        if(!$this->strip){
            $data = $data->QAPicklist->PicklistEntry;
            if(is_object($data)) {

                return $data->Picklist;
            }
        }


        foreach($data as $row) {

            $html .= "<div class='address_row'>";
            $lines = explode(",", $row->Picklist);
            $length = count($lines);

            $class = $row->CanStep?'refineQAS':'';

            $html .= "<span class='{$class}' id='".$row->Moniker."'>";

            foreach($lines as $key => $line){
                $html .= "<span class='line_$key'>";
                $html .= trim($line);
                $html .= $length == $key + 1 ? "" : ", ";
                $html .= "</span>";
            }
            $html .= "</span></div>";
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

    private function soapError($fault){
        if($this->returnType == "html"){
            $details = str_replace(" ", "&nbsp;", json_encode($fault->detail, JSON_PRETTY_PRINT));
            $details = str_replace("\n", "<br>", $details);
            echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring}, <br /> Details:  {$details})";
        }else{
            echo json_encode($fault);
        }
        exit();
    }
}

$qas = new QAS();

if(!empty($_POST['Moniker'])) {

    $qas->returnType = $_POST['type'];
    echo $qas->refine($_POST['Moniker']);
}
else if(!empty($_POST)){
    if(!empty($_POST['type'])) {
        $qas->returnType = $_POST['type'];
        unset ($_POST['type']);
    }
    $qas->strip = false;

    echo $qas->search($_POST);
}
