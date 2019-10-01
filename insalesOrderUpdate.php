<?php
set_time_limit ( 0 );
header ( 'Content-Type: text/html; charset=utf-8' );

require_once ( "insalesApi.php" );
require "vendor/autoload.php";

$urlRetail = "https://example.retailcrm.ru";
$apiKey = "flaU5ZvRPAk7pMhUQvib7CLsO2kHClRW";
$data = $_GET[ 'externalId' ];

$id = $data;
$retail = new \RetailCrm\ApiClient(
    $urlRetail ,
    $apiKey ,
    \RetailCrm\ApiClient::V5
);
$insales = new\retail\insalesApi( 'a641b9ec9b15868ee3206a667cab1321' , '06314e0ebaac09a6fd78b1985e16a0d8' );

$urlapi = "https://example.retailcrm.ru/api/v5/orders/$id?apiKey=flaU5ZvRPAk7pMhUQvib7CLsO2kHClRW";
$username = 'example@example';
$password = 'F1o55yi9';
$ch = curl_init ();
$curl_options = [
    CURLOPT_URL => $urlapi ,
    CURLOPT_RETURNTRANSFER => true ,
    CURLOPT_USERPWD => "$username:$password" ,
    CURLOPT_HTTPAUTH => CURLAUTH_BASIC ,
    CURLOPT_HTTPHEADER => array ( 'Content-Type: application/json' ) ,
];


curl_setopt_array ( $ch , $curl_options );
$output = curl_exec ( $ch );
curl_close ( $ch );

$order = json_decode ( $output , true )[ 'order' ];
$order_changes = [];
$order_changes[ 'status' ] = $order[ 'status' ];
$order_changes[ 'payment_status' ] = reset ( $order[ 'payments' ] )[ 'status' ];
$order_changes[ 'total_sum' ] = $order[ "totalSumm" ];

$out = $insales -> getOrder ( $id );


$insales_change = [];
$out[ 'fulfillment-status' ] = $order[ 'status' ];
$out[ "financial-status" ] = reset ( $order[ 'payments' ] )[ 'status' ];
$out[ "order-lines" ][ "order-line" ][ "full-total-price" ] = sprintf ( "%.1f" , $order[ "totalSumm" ] );


/**
 * Изменяет Статус Оплаты
 * @param $namePayStatus
 * @return string
 */
function changePayStatus ( $namePayStatus )
{
    $xml = '';
    $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<order>';
    $xml .= '<financial-status>' . $namePayStatus . '</financial-status>';
    $xml .= '</order>';
    file_put_contents ( "testPayChange.log" , print_r ( $xml , true ) , FILE_APPEND );
    return $xml;
}

/**
 * Изменяет Статус
 * @param $nameStatus
 * @return string
 */
function changeStatus ( $nameStatus )
{
    $xml = '';
    $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<order>';
    $xml .= '<fulfillment-status>' . $nameStatus . '</fulfillment-status>';
    $xml .= '</order>';
    return $xml;
}


/**
 * Изменяет Сумму и Количество Товара
 * @param $id
 * @param $price
 * @param $quantity
 * @return string
 */
function changeSum ( $id , $price , $quantity )
{
    $xml = '';
    $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<order>';
    $xml .= '<order-lines-attributes type="array">';
    $xml .= '<order-lines-attribute>';
    $xml .= '<id type="integer">' . $id . '</id>';
    $xml .= '<sale-price type="integer">' . $price . '</sale-price>';
    $xml .= '<quantity type="integer">' . $quantity . '</quantity>';
    $xml .= '</order-lines-attribute>';
    $xml .= '</order-lines-attributes>';
    $xml .= '</order>';

    return $xml;
}

/**
 * Изменяет Кастомный Статус
 * @param $id
 * @param $nameStatus
 * @return mixed
 */
function customStatusChange ( $id , $nameStatus )
{
    //Custom Status Name
    $xml = '';
    $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<order>';
    $xml .= '<custom-status-permalink>' . $nameStatus . '</custom-status-permalink>';
    $xml .= '</order>';

    //id
    $urlapi = "http://myshop-kj459.myinsales.ru/admin/orders/$id.xml";
    $username = 'a641b9ec9b15868ee3206a667cab1321';
    $password = '06314e0ebaac09a6fd78b1985e16a0d8';
    $ch = curl_init ();
    $curl_options = [
        CURLOPT_URL => $urlapi ,
        CURLOPT_RETURNTRANSFER => true ,
        CURLOPT_USERPWD => "$username:$password" ,
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC ,
        CURLOPT_HTTPHEADER => array ( 'Content-Type: application/xml' ) ,
        CURLOPT_CUSTOMREQUEST => 'PUT' ,
        CURLOPT_POSTFIELDS => $xml
    ];
    curl_setopt_array ( $ch , $curl_options );
    $insale_output = curl_exec ( $ch );
    curl_close ( $ch );

    return ( $insale_output );
}

//Изменение Статуса Оплаты
file_put_contents ( "testPayChangeId.log" , print_r ( $id , true ) , FILE_APPEND );
$insales -> changeOrder ( $id , changePayStatus ( reset ( $order[ 'payments' ] )[ 'status' ] ) );

//Изменение Статуса
$insales_status = [
    'new' => 'new'
    , 'sogl' => 'approved'
    , 'availability-confirmed' => 'approved'
    , 'send-to-delivery' => 'approved'
    , 'prepayed' => 'approved'
    , 'assembling' => 'approved'
    , 'redirect' => 'dispatched'
    , 'complete' => 'delivered'
    , 'cancel-other' => 'declined'
    , 'return' => 'returned'
];
$insales_custom_status = [
    'sogl' => 'soglasovan'
    , 'prepayed' => 'komplektuetsya'
    , 'assembling' => 'sobran'
];
if ( array_key_exists ( $order[ 'status' ] , $insales_status ) ) {
    $req = $insales -> changeOrder ( $id , changeStatus ( $insales_status[ $order[ 'status' ] ] ) );
    file_put_contents ( "testPayChangeReq.log" , print_r ( $req , true ) , FILE_APPEND );
}
if ( array_key_exists ( $order[ 'status' ] , $insales_custom_status ) ) {
    $req = $insales -> changeOrder ( $id , customStatusChange ( $id , $insales_custom_status[ $order[ 'status' ] ] ) );
    file_put_contents ( "testPayChangeReq.log" , print_r ( $req , true ) , FILE_APPEND );
}


file_put_contents ( "testOrderLines.log" , print_r ( $out , true ) , FILE_APPEND );
//Изменение Суммы
foreach ( $out[ "order-lines" ][ "order-line" ] as $key => $out_item ) {
    if ( !is_array ( $out[ "order-lines" ][ "order-line" ][ $key ] ) )
        continue;
    $out_id[ $out[ "order-lines" ][ "order-line" ][ $key ][ "variant-id" ] ] = $out[ "order-lines" ][ "order-line" ][ $key ][ "id" ];

    foreach ( $order[ 'items' ] as $value ) {
        if ( $value[ 'offer' ][ "externalId" ] == $out[ "order-lines" ][ "order-line" ][ $key ][ "variant-id" ] ) {
            $req = $insales -> changeOrder ( $id ,
                changeSum ( $out_id[ $value[ 'offer' ][ "externalId" ] ] , $value[ "initialPrice" ] , $value[ "quantity" ] )
            );
            file_put_contents ( "testPayChangeReq.log" , print_r ( $req , true ) , FILE_APPEND );
        }
    }
//    if ( !is_numeric ( $key ) ) {
//        $out_id[ $out[ "order-lines" ][ "order-line" ][ "variant-id" ] ] = $out[ "order-lines" ][ "order-line" ][ "id" ];
//
//        foreach ( $order[ 'items' ] as $value ) {
//            if ( $value[ 'offer' ][ "externalId" ] == $out[ "order-lines" ][ "order-line" ][ "variant-id" ] ) {
//                $req = $insales->changeOrder ( $id ,
//                    changeSum ( $out_id[ $value[ 'offer' ][ "externalId" ] ] , $value[ "initialPrice" ] , $value[ "quantity" ] )
//                );
//            }
//        }
//    }
}


/**
 * Создание товара
 * @param $id
 * @param $externalId
 * @param $quantity
 */
function create ( $id , $externalId , $quantity )
{
    //externalId
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<order>';
    $xml .= '<order-lines-attributes type="array">';
    $xml .= '<order-lines-attribute>';
    $xml .= '<variant-id type="integer">' . $externalId . '</variant-id>';
    $xml .= '<quantity type="integer">' . $quantity . '</quantity>'; //!!!!!!!!!!!!!!TUT
    $xml .= '</order-lines-attribute>';
    $xml .= '</order-lines-attributes>';
    $xml .= '</order>';

    //поменять 20553929 на $id
    $urlapi = "http://myshop-kj459.myinsales.ru/admin/orders/$id.xml";
    $username = 'a641b9ec9b15868ee3206a667cab1321';
    $password = '06314e0ebaac09a6fd78b1985e16a0d8';
    $ch = curl_init ();
    $curl_options = [
        CURLOPT_URL => $urlapi ,
        CURLOPT_RETURNTRANSFER => true ,
        CURLOPT_USERPWD => "$username:$password" ,
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC ,
        CURLOPT_HTTPHEADER => array ( 'Content-Type: application/xml' ) ,
        CURLOPT_CUSTOMREQUEST => 'PUT' ,
        CURLOPT_POSTFIELDS => $xml
    ];


    curl_setopt_array ( $ch , $curl_options );
    $insale_output = curl_exec ( $ch );
    curl_close ( $ch );
}

/**
 * Удаление Товара
 * @param $id
 * @param $orderLineId
 * @return mixed
 */
function delete ( $id , $orderLineId )
{
    //externalId
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<order>';
    $xml .= '<order-lines-attributes type="array">';
    $xml .= '<order-lines-attribute>';
    $xml .= '<id type="integer">' . $orderLineId . '</id>';
    $xml .= '<_destroy type="boolean">true</_destroy>';
    $xml .= '</order-lines-attribute>';
    $xml .= '</order-lines-attributes>';
    $xml .= '</order>';

    //id
    $urlapi = "http://myshop-kj459.myinsales.ru/admin/orders/$id.xml";
    $username = 'a641b9ec9b15868ee3206a667cab1321';
    $password = '06314e0ebaac09a6fd78b1985e16a0d8';
    $ch = curl_init ();
    $curl_options = [
        CURLOPT_URL => $urlapi ,
        CURLOPT_RETURNTRANSFER => true ,
        CURLOPT_USERPWD => "$username:$password" ,
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC ,
        CURLOPT_HTTPHEADER => array ( 'Content-Type: application/xml' ) ,
        CURLOPT_CUSTOMREQUEST => 'PUT' ,
        CURLOPT_POSTFIELDS => $xml
    ];
    curl_setopt_array ( $ch , $curl_options );
    $insale_output = curl_exec ( $ch );
    curl_close ( $ch );

    return ( $insale_output );
}


$ms_item_arr = [];
foreach ( $order[ 'items' ] as $value ) {
    $ms_item_arr[] = $value[ 'offer' ][ "externalId" ];
}


$insale_order_arr = [];
////Создаем массив для сравнения товар между Retail и InSales
foreach ( $out[ "order-lines" ][ 'order-line' ] as $key => $out_item ) {
    for ( $i = 0 ; $i < count ( $out[ "order-lines" ][ 'order-line' ] ) ; $i ++ ) {
        if ( array_key_exists ( $i , $out[ "order-lines" ][ 'order-line' ] ) ) {
            $insale_order_arr[ $out[ "order-lines" ][ 'order-line' ][ $i ][ "variant-id" ] ] = $out[ "order-lines" ][ 'order-line' ][ $i ][ "variant-id" ];
            $insale_order_arr1[ $out[ "order-lines" ][ 'order-line' ][ $i ][ "id" ] ] = $out[ "order-lines" ][ 'order-line' ][ $i ][ "variant-id" ];
        }
    }
    if ( !is_array ( $out[ "order-lines" ][ "order-line" ][ $key ] ) )
        continue;
    if ( strlen ( strval ( $out[ "order-lines" ][ "order-line" ][ $key ][ "variant-id" ] ) ) == 9 ) {
        $insale_order_arr[ $out[ "order-lines" ][ "order-line" ][ $key ][ "variant-id" ] ] = $out[ "order-lines" ][ "order-line" ][ $key ][ "variant-id" ];
        $insale_order_arr1[ $out[ "order-lines" ][ "order-line" ][ $key ][ "id" ] ] = $out[ "order-lines" ][ "order-line" ][ $key ][ "variant-id" ];
    }
}

$result = [];
$result1 = false;
foreach ( $ms_item_arr as $value ) {
    if ( !in_array ( $value , $insale_order_arr ) ) {
        $arr[ $value ] = $value;
        $result = array_unique ( $arr );
    }
}

foreach ( $insale_order_arr1 as $key => $value ) {
    if ( !in_array ( $value , $ms_item_arr ) ) {
        $arr1[ $key ] = $key;
        $result1 = array_unique ( $arr1 );
    }
}


////Создаем товар если его нету в списке Insales
foreach ( $result as $key => $externalId ) {
    foreach ( $order[ 'items' ] as $value ) {
        if ( $externalId == $value[ 'offer' ][ "externalId" ] ) {
            $quantity = (int) $value[ "quantity" ];
            break;
        }
    }
    create ( $id , $externalId , $quantity );

}

////Удаляем товар если его нету в списке Retail
if ( $result1 ) {
    foreach ( $result1 as $key => $orderLineId ) {
        delete ( $id , $orderLineId );
    }
}



