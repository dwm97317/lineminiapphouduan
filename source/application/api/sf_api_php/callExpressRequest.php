<?php
header("Content-type: text/html; charset=utf-8");
/**
 * @author dawawa
 * @copyright 2020
 */

$partnerID = "Y7hHmkqf";//此处替换为您在丰桥平台获取的顾客编码
$checkword="ehpA2SxjBT0M0QY74VktZbPSx41xO9gt";//此处替换为您在丰桥平台获取的校验码

//----以下为请求服务接口和消息内容---
//$serviceCode = "EXP_RECE_CREATE_ORDER";
//$file = './callExpressRequest/01.order.json';//下订单

//$serviceCode = "EXP_RECE_SEARCH_ORDER_RESP";
//$file = './callExpressRequest/02.order.query.json';//订单结果查询

//$serviceCode = "EXP_RECE_UPDATE_ORDER";
//$file = './callExpressRequest/03.order.confirm.json';//订单确认取消

//$serviceCode = "EXP_RECE_FILTER_ORDER_BSP";
//$file = './callExpressRequest/04.order.filter.json';//订单筛选	

//$serviceCode = "EXP_RECE_SEARCH_ROUTES";
//$file = './callExpressRequest/05_route_query_by_MailNo.json';//路由查询-通过运单号
//$file = './callExpressRequest/05_route_query_by_OrderNo.json';//路由查询-通过订单号 


//$serviceCode = "EXP_RECE_GET_SUB_MAILNO";
//$file = './callExpressRequest/07.sub.mailno.json';//子单号申请


// $serviceCode = "EXP_RECE_QUERY_SFWAYBILL";
// $file = './callExpressRequest/09.waybills_fee.json';//清单运费查询

// $serviceCode = "EXP_RECE_REGISTER_ROUTE";
// $file = './callExpressRequest/12.register_route.json';//路由注册

// $serviceCode = "EXP_RECE_CREATE_REVERSE_ORDER";
// $file = './callExpressRequest/13.reverse_order.json';//退货下单

// $serviceCode = "EXP_RECE_CANCEL_REVERSE_ORDER";
// $file = './callExpressRequest/14.cancel_reverse_order.json';//退货消单

// $serviceCode = "EXP_RECE_DELIVERY_NOTICE";
// $file = './callExpressRequest/15.delivery_notice.json';//派件通知

// $serviceCode = "EXP_RECE_REGISTER_WAYBILL_PICTURE";
// $file = './callExpressRequest/16.register_waybill_picture.json';//图片注册及推送
	
// $serviceCode = "EXP_RECE_WANTED_INTERCEPT";
// $file = './callExpressRequest/18.wanted_intercept.json';//截单转寄
 
// $serviceCode = "EXP_RECE_QUERY_DELIVERTM";
// $file = './callExpressRequest/19.query_delivertm.json';//派件通知

// $serviceCode = "COM_RECE_CLOUD_PRINT_WAYBILLS";
// $file = './callExpressRequest/20.cloud_print_waybills.json';//云打印面单打印

// $serviceCode = "EXP_RECE_UPLOAD_ROUTE";
// $file = './callExpressRequest/21.upload_route.json';//路由上传

// $serviceCode = "EXP_RECE_SEARCH_PROMITM";
// $file = './callExpressRequest/22.search_promitm.json';//预计派送时间

// $serviceCode = "EXP_EXCE_CHECK_PICKUP_TIME";
// $file = './callExpressRequest/23.check_pickup_time.json';//揽件服务时间

$serviceCode = "EXP_RECE_VALIDATE_WAYBILLNO";
$file = './callExpressRequest/24.validate_waybillno.json';//运单号合法性校验


$msgData = file_get_contents($file);//读取文件内容

//获取UUID
function create_uuid() {
    $chars = md5(uniqid(mt_rand(), true));
    $uuid = substr ( $chars, 0, 8 ) . '-'
        . substr ( $chars, 8, 4 ) . '-'
        . substr ( $chars, 12, 4 ) . '-'
        . substr ( $chars, 16, 4 ) . '-'
        . substr ( $chars, 20, 12 );
    return $uuid ;
}
$requestID = create_uuid();

//获取时间戳
$timestamp = time();

//通过MD5和BASE64生成数字签名
$msgDigest = base64_encode(md5((urlencode($msgData .$timestamp. $checkword)), TRUE));

//POST
function send_post($url, $post_data) {
     
    $postdata = http_build_query($post_data);
    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type:application/x-www-form-urlencoded;charset=utf-8',
            'content' => $postdata,
            'timeout' => 15 * 60 // 超时时间（单位:s）
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    return $result;
}

//发送参数
$post_data = array(
    'partnerID' => $partnerID,
    'requestID' => $requestID,
    'serviceCode' => $serviceCode,
    'timestamp' => $timestamp,
    'msgDigest' => $msgDigest,
    'msgData' => $msgData
);

//沙箱环境的地址
$CALL_URL_BOX = "http://sfapi-sbox.sf-express.com/std/service";
//生产环境的地址
$CALL_URL_PROD = "https://sfapi.sf-express.com/std/service";

$resultCont = send_post($CALL_URL_BOX, $post_data); //沙盒环境

print_r(json_decode($resultCont)); //提示重复下单请修改json文件内对应orderid参数

?>