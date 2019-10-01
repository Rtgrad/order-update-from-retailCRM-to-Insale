<?php
/**
 * Copyright Maxim Bykovskiy © 2018.
 */

/**
 * Created by PhpStorm.
 * User: Asus
 * Date: 13.06.2018
 * Time: 10:09
 */

namespace retail;

class insalesApi
{
    private $login;
    private $pass;
    private $url = 'http://myshop-kj459.myinsales.ru/admin/';

    public function __construct($login, $pass)
    {
        $this->login = $login;
        $this->pass = $pass;
    }

    private function array_to_xml(array $arr, \SimpleXMLElement $xml) {
        foreach ($arr as $k => $v) {

            $attrArr = array();
            $kArray = explode(' ',$k);
            $tag = array_shift($kArray);

            if (count($kArray) > 0) {
                foreach($kArray as $attrValue) {
                    $attrArr[] = explode('=',$attrValue);
                }
            }

            if (is_array($v)) {
                if (is_numeric($k)) {
                    $this->array_to_xml($v, $xml);
                } else {
                    $child = $xml->addChild($tag);
                    if (isset($attrArr)) {
                        foreach($attrArr as $attrArrV) {
                            $child->addAttribute($attrArrV[0],$attrArrV[1]);
                        }
                    }
                    $this->array_to_xml($v, $child);
                }
            } else {
                $child = $xml->addChild($tag, $v);
                if (isset($attrArr)) {
                    foreach($attrArr as $attrArrV) {
                        $child->addAttribute($attrArrV[0],$attrArrV[1]);
                    }
                }
            }
        }
        return $xml;
    }

    private function xml_to_array($xmlstr) {
        $array = json_decode(json_encode((array)simplexml_load_string($xmlstr)),true);
        return $array;
    }

    private function get($link, $json = false, $sleep = 50000) {
        usleep($sleep);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_USERPWD, $this->login . ":" . $this->pass);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        try {
            if($json == true) {
                $out = json_decode(curl_exec($curl));
                return $out;
            } else {
                $out = curl_exec($curl);
                return $out;
            }
        }
        catch(Exception $e) {
            return 'Ошибка соединения';
        }
    }

    private function post($link, $array, $json = false, $sleep = 50000) {
        usleep($sleep);
       // $url = 'http://'.$this->login.':'.$this->pass.$link;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_USERPWD, $this->login . ":" . $this->pass);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $array);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/xml'
        ));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        try {
            if($json == true) {
                $out = json_decode(curl_exec($curl));
                return $out;
            } else {
                $out = curl_exec($curl);
                return $out;
            }
        }
        catch(Exception $e) {
            return 'Ошибка соединения';
        }
    }

    private function put($link, $xml, $json = false, $sleep = 50000) {
        usleep($sleep);
       // $url = 'http://'.$this->login.':'.$this->pass.$link;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curl, CURLOPT_USERPWD, $this->login . ":" . $this->pass);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/xml'
        ));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        try {
            if($json == true) {
                $out = json_decode(curl_exec($curl));
                return $out;
            } else {
                $out = curl_exec($curl);
                return $out;
            }
        }
        catch(Exception $e) {
            return 'Ошибка соединения';
        }
    }

    public function getOrder($id) {
        //$xml = $this->array_to_xml(['customer' => $mass], new \SimpleXMLElement('<operation/>'))->asXML();

        $url = $this->url . 'orders/' . $id . '.xml';

        $res = $this->get($url);

        return $this->xml_to_array($res);
    }

    public function getProduct($i) {
        //$xml = $this->array_to_xml(['customer' => $mass], new \SimpleXMLElement('<operation/>'))->asXML();
        //echo time() . "\r\n";
        //echo date("Y-m-d H:i:s O", $time);
        $url = $this->url .'products.xml?page=' .$i ;//?page=' . $i ; //. http_build_query(['updated_since' => date("Y-m-d H:i:s O", $time)]);
        $res = $this->get($url);

        return  $this->xml_to_array($res);
    }

    public function oneProduct($id) {
        //$xml = $this->array_to_xml(['customer' => $mass], new \SimpleXMLElement('<operation/>'))->asXML();

        $url = $this->url . 'products/' . $id . '.xml';

        $res = $this->get($url);

        return $this->xml_to_array($res);
    }
    public function getHook() {
        //$xml = $this->array_to_xml(['customer' => $mass], new \SimpleXMLElement('<operation/>'))->asXML();

        $url = $this->url . 'webhooks.xml';

        $res = $this->get($url);

        return $this->xml_to_array($res);
    }
    public function getCategories() {
        //$xml = $this->array_to_xml(['customer' => $mass], new \SimpleXMLElement('<operation/>'))->asXML();
        //echo time() . "\r\n";
        //echo date("Y-m-d H:i:s O", $time);
        $url = $this->url . 'categories.xml' ;//. http_build_query(['updated_since' => date("Y-m-d H:i:s O", $time)]);
        $res = $this->get($url);

        return  $this->xml_to_array($res);
    }
    public function postHook($array){
        $url = $this->url . 'webhook.xml' ;//. http_build_query(['updated_since' => date("Y-m-d H:i:s O", $time)]);
        $res = $this->post($url,$array);

        return $this->xml_to_array($res);
    }
    public function changeOrder($id,$xml){
        $url = $this->url . '/orders/' . $id . '.xml';
        $res = $this->put($url,$xml);

        return $res;//$this->xml_to_array($res);
    }
}