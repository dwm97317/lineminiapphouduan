<?php

// 应用公共函数库文件

use think\Request;
use think\Log;
use think\Cache;
use app\api\model\Wxapp;
use app\common\library\InviteCode;
use app\api\model\dealer\Setting as DealerSetting;
use PHPMailer\PHPMailer\PHPMailer as Email;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use app\api\model\Setting as SettingModel;
use app\api\model\Line;
use app\api\model\Inpack;
use app\api\model\User;
use app\store\model\user\UserLine;
use app\store\model\LineService;

    require (VENDOR_PATH.'phpmailer/phpmailer/src/PHPMailer.php');
    require (VENDOR_PATH.'phpmailer/phpmailer/src/SMTP.php');
    require (VENDOR_PATH.'phpmailer/phpmailer/src/POP3.php');
    require (VENDOR_PATH.'phpmailer/phpmailer/src/Exception.php');

// require 'PHPMailer/PHPMailer.php';
// require 'PHPMailer/SMTP.php';

function ifData($field,$data){
    return isset($data[$field])?$data[$field]:'';
}

/**

* 字符串加密、解密函数

* @param string $string 字符串

* @param string $operation ENCODE为加密，DECODE为解密，可选参数，默认为ENCODE，

* @param string $key 密钥：数字、字母、下划线

* @param int $expiry 过期时间

* @return string

*/

function str_crypt($string, $operation = "ENCODE", $key = "235568787rioituyewiweu", $expiry = 0)

{

$key_length = 4;

$key = md5($key != "" ? $key : KEY);

$fixedkey = md5($key);

$egiskeys = md5(substr($fixedkey, 16, 16));

$runtokey = $key_length ? ($operation == "ENCODE" ? substr(md5(microtime(true)), -$key_length) : substr($string, 0, $key_length)) : "";

$keys = md5(substr($runtokey, 0, 16) . substr($fixedkey, 0, 16) . substr($runtokey, 16) . substr($fixedkey, 16));

$string = $operation == "ENCODE" ? sprintf("%010d", $expiry ? $expiry + time() : 0) . substr(md5($string . $egiskeys), 0, 16) . $string : base64_decode(substr($string, $key_length));

$result = "";

$string_length = strlen($string);

for ($i = 0; $i < $string_length; $i++) {

$result .= chr(ord($string{$i}) ^ ord($keys{$i % 32}));

}

if ($operation == "ENCODE") {

return $runtokey . str_replace("=", "", base64_encode($result));

} else {

if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $egiskeys), 0, 16)) {

return substr($result, 26);

} else {

return "";

}

}

}

/********************************************************************* 
 函数名称:encrypt 
 函数作用:加密解密字符串 
 使用方法: 
 加密  :encrypt('str','E','nowamagic'); 
 解密  :encrypt('被加密过的字符串','D','nowamagic'); 
 参数说明: 
 $string :需要加密解密的字符串 
 $operation:判断是加密还是解密:E:加密 D:解密 
 $key  :加密的钥匙(密匙); 
*********************************************************************/ 
 function encrypt($string,$operation="E",$key='') 
 { 
  if ($operation=='E'){
      $wxappId = $string;
  }else{
      $wxappId = $operation=='D'? Cache::get($string):config('wxapp_id');
  }
  $key=Cache::get($wxappId.'_en_key')?Cache::get($wxappId.'_en_key'):config('en_key');
  $key_length=strlen($key); 
  $string=$operation=='D'?base64_decode($string):substr(md5($string.$key),0,8).$string; 
  $string_length=strlen($string); 
  $rndkey=$box=array(); 
  $result=''; 
  for($i=0;$i<=255;$i++) 
  { 
   $rndkey[$i]=ord($key[$i%$key_length]); 
   $box[$i]=$i; 
  } 
  for($j=$i=0;$i<256;$i++) 
  { 
   $j=($j+$box[$i]+$rndkey[$i])%256; 
   $tmp=$box[$i]; 
   $box[$i]=$box[$j]; 
   $box[$j]=$tmp; 
  } 
  for($a=$j=$i=0;$i<$string_length;$i++) 
  { 
   $a=($a+1)%256; 
   $j=($j+$box[$a])%256; 
   $tmp=$box[$a]; 
   $box[$a]=$box[$j]; 
   $box[$j]=$tmp; 
   $result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256])); 
  } 
  if($operation=='D') { 
   if(substr($result,0,8)==substr(md5(substr($result,8).$key),0,8)) 
   { 
    return substr($result,8); 
   } 
   else 
   { 
    return''; 
   } 
  } 
  else 
  { 
   return str_replace('=','',base64_encode($result)); 
  } 
 } 

//获取增值服务的费用
function getServiceFree($services_require,$oWeigth,$long){
    $LineService = new LineService;
    $services_require = explode(',',$services_require);
    $free = 0;
    foreach ($services_require as $value){
        $lineser = $LineService->detail($value);
        //重量模式
        if($lineser['type']==10){
            $rule = json_decode($lineser['rule'],true);
            foreach ($rule as $key=>$v){
                if($oWeigth>=$v['weight'][0] && $oWeigth<$v['weight'][1]){
                    $free += $v['weight_price'];
                }
            }
        }
        //长度模式
        if($lineser['type']==20){
            $rule = json_decode($lineser['rule'],true);
            foreach ($rule as $key=>$v){
                if($long>=$v['weight'][0] && $long<$v['weight'][1]){
                    $free += $v['weight_price'];
                }
            }
        }
        
    }
    return $free;
}
 
/**
 * 打印调试函数
 * @param $content
 * @param $is_die
 */
function pre($content, $is_die = true)
{
    header('Content-type: text/html; charset=utf-8');
    echo '<pre>' . print_r($content, true);
    $is_die && die();
}

// 分享码生成 11;
function createCode($id){
   $Iv = new InviteCode();
   $code = $Iv->id2Code($id);
   return $code;
}
function parseJson($str){
    $str_p = explode(',',$str);
    $json = [];
    foreach($str_p as $v){
        $_strp = explode('-',$v);
        $json[] = [
           $_strp[0] => $_strp[1],    
        ];
    }
    return json_encode($json);
}
/**
 * 隐藏手机号中间四位 13012345678 -> 130****5678
 * @param string $mobile 手机号
 * @return string
 */
function hide_mobile(string $mobile): string
{
    return substr_replace($mobile, '****', 3, 4);
}
/**
 * 驼峰命名转下划线命名
 * @param $str
 * @return string
 */
function toUnderScore($str)
{
    $dstr = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
        return '_' . strtolower($matchs[0]);
    }, $str);
    return trim(preg_replace('/_{2,}/', '_', $dstr), '_');
}

function getTime(){
   return date("Y-m-d H:i:s",time());
}

/**
 * 生成密码hash值
 * @param $password
 * @return string
 */
function yoshop_hash($password)
{
    return md5(md5($password) . 'yoshop_salt_SmTRx');
}

/**
 * 获取当前域名及根路径
 * @return string
 */
function base_url()
{
    static $baseUrl = '';
    if (empty($baseUrl)) {
        $request = Request::instance();
        $subDir = str_replace('\\', '/', dirname($request->server('PHP_SELF')));
        $baseUrl = $request->scheme() . '://' . $request->host() . $subDir . ($subDir === '/' ? '' : '/');
    }
    return $baseUrl;
}

function makeTree($arr,$id='id',$parent_id='parent_id',$child='child'){
  $refer = array();
  $tree = array();
  foreach($arr as $k => $v){
    $refer[$v[$id]] = & $arr[$k]; //创建主键的数组引用
  }
  foreach($arr as $k => $v){
    $pid = $v[$parent_id];  //获取当前分类的父级id
    if($pid == 0){
      $tree[] = & $arr[$k];  //顶级栏目
    }else{
      if(isset($refer[$pid])){
        $refer[$pid][$child][] = & $arr[$k]; //如果存在父级栏目，则添加进父级栏目的子栏目数组中
      }
    }
  }
  return $tree;
}



function createNewOrderSn($default,$xuhao=0,$createSnfistword='XS',$user_id=0,$shop_alias_name='CK',$country=0){
    $orderno = '';
    // dump($default);die;
    foreach ($default as $val){
        switch ($val) {
            case 10:
                $orderno = $orderno.time();
                break;
            case 20:
                $orderno = $orderno.date("Ymd",time());
                break;
            case 30:
                $orderno = $orderno.date("ymd",time());
                break;
            case 40:
                $orderno = $orderno.date("YmdHis",time());
                break;
            case 50:
                $orderno = $orderno.$user_id;
                break;
            case 60:
                $orderno = $orderno.$country;
                break;
            case 70:
                $orderno = $orderno.$shop_alias_name;
                break;
            // case 80:
            //     $orderno = $orderno.$city;
            //     break;
            case 90:
                $orderno = $orderno.$createSnfistword;
                break;
            case 100:
                $orderno = $orderno.$xuhao;
                break;
            case 110:
                $orderno = $orderno.rand(10000,99999);
                break;
            default:
                $orderno = $orderno.date("ymd",time());
                break;
     }
    }
    // 10 =>"时间戳1688197248",
    // 20 =>"年月日20230101",
    // 30 =>"(缩)年月日230101",
    // 40 =>"年月日时分秒20230101213030",
    // 50 =>"用户ID",
    // 60 =>"目的地ID",
    // 70 =>"仓库简称(CTO)",
    // 80 =>"城市编号(15)",
    // 90 =>"自定义字母(XS)",
    // 100 =>"自定序号(001-100)",
    // 110 =>"随机5位数(10000-99999)",
  return $orderno;
}


// 生成订单号
function createSn(){

    $order_id_main = date('YmdHis') . rand(10000000,99999999);
    $order_id_len = strlen($order_id_main);
    $order_id_sum = 0;
    for($i=0; $i<$order_id_len; $i++){

        $order_id_sum += (int)(substr($order_id_main,$i,1));

    }
    $osn = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);
    return $osn;

}

// 生成订单号
function createSnByUserIdCid($user_id,$country_id){

    $osn = date('YmdHis') .'-'. $user_id.'-' . $country_id;
    return $osn;

}

// 生成订单号
function createOrderSn(){

    $order_id_main = 'HT'.date('YmdHis') . rand(1000,9999);
    $order_id_len = strlen($order_id_main);
    $order_id_sum = 0;
    for($i=0; $i<$order_id_len; $i++){
        $order_id_sum += (int)(substr($order_id_main,$i,1));
    }
    $osn = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);
    return $osn;

}

// 生成订单号
function createYysn(){

    $order_id_main =  'YY'.time(). rand(100,999);
    $order_id_len = strlen($order_id_main);
    $order_id_sum = 0;
    for($i=0; $i<$order_id_len; $i++){

        $order_id_sum += (int)(substr($order_id_main,$i,1));

    }
    $osn = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);
    return $osn;
}

// 生成PC端订单号
function createPCsn(){

    $order_id_main =  'PC'.time(). rand(100,999);
    $order_id_len = strlen($order_id_main);
    $order_id_sum = 0;
    for($i=0; $i<$order_id_len; $i++){

        $order_id_sum += (int)(substr($order_id_main,$i,1));

    }
    $osn = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);
    return $osn;
}

// 生成仓管录单订单号
function createJysn(){

    $order_id_main =  'JY'.time(). rand(100,999);
    $order_id_len = strlen($order_id_main);
    $order_id_sum = 0;
    for($i=0; $i<$order_id_len; $i++){

        $order_id_sum += (int)(substr($order_id_main,$i,1));

    }
    $osn = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);
    return $osn;
}



// 隐藏部分字符串
function func_substr_replace($str, $replacement = '*', $start = 1, $length = 3){
    $len = mb_strlen($str,'utf-8');
    if ($len > intval($start+$length)) {
        $str1 = mb_substr($str,0,$start,'utf-8');
        $str2 = mb_substr($str,intval($start+$length),NULL,'utf-8');
    } else {
        $str1 = mb_substr($str,0,1,'utf-8');
        $str2 = mb_substr($str,$len-1,1,'utf-8');   
        $length = $len - 2;       
    }
    $new_str = $str1;
    for ($i = 0; $i < 7; $i++) {
        $new_str .= $replacement;
    }
    $new_str .= substr($str2,4);
    return $new_str;
}

/**
 * 写入日志 (使用tp自带驱动记录到runtime目录中)
 * @param $value
 * @param string $type
 */
function log_write($value, $type = 'yoshop-info')
{
    $msg = is_string($value) ? $value : var_export($value, true);
    Log::record($msg, $type);
}

/**
 * curl请求指定url (get)
 * @param $url
 * @param array $data
 * @return mixed
 */
function curl($url, $data = [])
{
    // 处理get数据
    if (!empty($data)) {
        $url = $url . '?' . http_build_query($data);
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}

/**
 * curl请求指定url (post)
 * @param $url
 * @param array $data
 * @return mixed
 */
function curlPost($url, $data = [])
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);

    // 如果是数组，转换为 application/x-www-form-urlencoded 格式
    if (is_array($data)) {
        $data = http_build_query($data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    }

    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

if (!function_exists('array_column')) {
    /**
     * array_column 兼容低版本php
     * (PHP < 5.5.0)
     * @param $array
     * @param $columnKey
     * @param null $indexKey
     * @return array
     */
    function array_column($array, $columnKey, $indexKey = null)
    {
        $result = array();
        foreach ($array as $subArray) {
            if (is_null($indexKey) && array_key_exists($columnKey, $subArray)) {
                $result[] = is_object($subArray) ? $subArray->$columnKey : $subArray[$columnKey];
            } elseif (array_key_exists($indexKey, $subArray)) {
                if (is_null($columnKey)) {
                    $index = is_object($subArray) ? $subArray->$indexKey : $subArray[$indexKey];
                    $result[$index] = $subArray;
                } elseif (array_key_exists($columnKey, $subArray)) {
                    $index = is_object($subArray) ? $subArray->$indexKey : $subArray[$indexKey];
                    $result[$index] = is_object($subArray) ? $subArray->$columnKey : $subArray[$columnKey];
                }
            }
        }
        return $result;
    }
}

/**
 * 多维数组合并
 * @param $array1
 * @param $array2
 * @return array
 */
function array_merge_multiple($array1, $array2)
{
    $merge = $array1 + $array2;
    $data = [];
    foreach ($merge as $key => $val) {
        if (
            isset($array1[$key])
            && is_array($array1[$key])
            && isset($array2[$key])
            && is_array($array2[$key])
        ) {
            $data[$key] = array_merge_multiple($array1[$key], $array2[$key]);
        } else {
            $data[$key] = isset($array2[$key]) ? $array2[$key] : $array1[$key];
        }
    }
    return $data;
}

/**
 * 二维数组排序
 * @param $arr
 * @param $keys
 * @param bool $desc
 * @return mixed
 */
function array_sort($arr, $keys, $desc = false)
{
    $key_value = $new_array = array();
    foreach ($arr as $k => $v) {
        $key_value[$k] = $v[$keys];
    }
    if ($desc) {
        arsort($key_value);
    } else {
        asort($key_value);
    }
    reset($key_value);
    foreach ($key_value as $k => $v) {
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}

// base64 转图片文件
function base64ToFile($file_data){
    $upload_result = array('status' => true, 'msg'=>'','err_info'=>'');
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $file_data, $result)) {
    //处理base64字符串
    $img_base64 = str_replace($result[1], '', $file_data);
    $img_base64 = str_replace('=', '', $img_base64);
    $source_img = base64_decode($img_base64);
    //判断文件大小
    $file_size = 1024;
    //上传目录
    $basedir = './uploads';
    //后缀
    $img_suffix = $result[2];//文件后缀
    //文件名
    // $filename = uniqid();//文件名
    $filename = date('YmdHis',time());//文件名
    //文件完整路径
    $filepath = $basedir . "/" . $filename . "." . $img_suffix;
    //目录若果不存在,则创建目录
    if(!is_dir($basedir)){
    mkdir($basedir);
    chmod($basedir,0777);
    }
    //上传文件
    try {
    file_put_contents($filepath, $source_img);
    $filepath = substr($filepath, 1);
    $upload_result = array('code' => true, 'msg'=>'上传成功','err_info'=>'','path'=>'https://'.$_SERVER['HTTP_HOST'].$filepath);
    return $upload_result;
    } catch (Exception $e) {
    $upload_result = array('code' => false, 'msg'=>'上传失败','err_info'=>$e->getMessage());
    return $upload_result;
    }
    // if (file_put_contents($filepath, base64_decode(str_replace($result[1], '', $file_data)))) {
    // //$size = getimagesize($filepath);
    // $filepath = substr($filepath, 1);
    // //$arr['filepath'] = $filepath;
    // //$arr['size'] = $size[3];
    // return $filepath;
    // }else{
    // return false;
    // }
    }else{
    $upload_result = array('code' => false, 'msg'=>'上传失败','err_info'=>'请携带base64字符串的前缀');
    return $upload_result;
    }
}

/**
 * 数据导出到excel(csv文件)
 * @param $fileName
 * @param array $tileArray
 * @param array $dataArray
 */
function export_excel($fileName, $tileArray = [], $dataArray = [])
{
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', 0);
    ob_end_clean();
    ob_start();
    header("Content-Type: text/csv");
    header("Content-Disposition:filename=" . $fileName);
    $fp = fopen('php://output', 'w');
    fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));// 转码 防止乱码(比如微信昵称)
    fputcsv($fp, $tileArray);
    $index = 0;
    foreach ($dataArray as $item) {
        if ($index == 1000) {
            $index = 0;
            ob_flush();
            flush();
        }
        $index++;
        fputcsv($fp, $item);
    }
    ob_flush();
    flush();
    ob_end_clean();
}

function getPlatform()
{
    static $value=null;
     //从header中获取channel
     empty($value)&&$value=request()->header('platform');
     //调试模式下可通过param中获取
     if(empty($value)){
       $value=request()->param('platform');
     }
    return $value;
}
/**
 * 获取全局唯一标识符
 * @param bool $trim
 * @return string
 */
function get_guid_v4(bool $trim = true): string
{
    // Windows
    if (function_exists('com_create_guid') === true) {
        $charid = com_create_guid();
        return $trim == true ? trim($charid, '{}') : $charid;
    }
    // OSX/Linux
    if (function_exists('openssl_random_pseudo_bytes') === true) {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    // Fallback (PHP 4.2+)
    mt_srand(intval((double)microtime() * 10000));
    $charid = strtolower(md5(uniqid((string)rand(), true)));
    $hyphen = chr(45);                  // "-"
    $lbrace = $trim ? "" : chr(123);    // "{"
    $rbrace = $trim ? "" : chr(125);    // "}"
    return $lbrace .
        substr($charid, 0, 8) . $hyphen .
        substr($charid, 8, 4) . $hyphen .
        substr($charid, 12, 4) . $hyphen .
        substr($charid, 16, 4) . $hyphen .
        substr($charid, 20, 12) .
        $rbrace;
}

/**
 * 隐藏敏感字符
 * @param $value
 * @return string
 */
function substr_cut($value)
{
    $strlen = mb_strlen($value, 'utf-8');
    if ($strlen <= 1) return $value;
    $firstStr = mb_substr($value, 0, 1, 'utf-8');
    $lastStr = mb_substr($value, -1, 1, 'utf-8');
    return $strlen == 2 ? $firstStr . str_repeat('*', $strlen - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
}

/**
 * 获取当前系统版本号
 * @return mixed|null
 * @throws Exception
 */
function get_version()
{
    static $version = null;
    if ($version) {
        return $version;
    }
    $file = dirname(ROOT_PATH) . '/version.json';
    if (!file_exists($file)) {
        throw new Exception('version.json not found');
    }
    $version = json_decode(file_get_contents($file), true);
    if (!is_array($version)) {
        throw new Exception('version cannot be decoded');
    }
    return $version['version'];
}

/**
 * 获取全局唯一标识符
 * @param bool $trim
 * @return string
 */
function getGuidV4($trim = true)
{
    // Windows
    if (function_exists('com_create_guid') === true) {
        $charid = com_create_guid();
        return $trim == true ? trim($charid, '{}') : $charid;
    }
    // OSX/Linux
    if (function_exists('openssl_random_pseudo_bytes') === true) {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    // Fallback (PHP 4.2+)
    mt_srand((double)microtime() * 10000);
    $charid = strtolower(md5(uniqid(rand(), true)));
    $hyphen = chr(45);                  // "-"
    $lbrace = $trim ? "" : chr(123);    // "{"
    $rbrace = $trim ? "" : chr(125);    // "}"
    $guidv4 = $lbrace .
        substr($charid, 0, 8) . $hyphen .
        substr($charid, 8, 4) . $hyphen .
        substr($charid, 12, 4) . $hyphen .
        substr($charid, 16, 4) . $hyphen .
        substr($charid, 20, 12) .
        $rbrace;
    return $guidv4;
}

/**
 * 时间戳转换日期
 * @param $timeStamp
 * @return false|string
 */
function format_time($timeStamp)
{
    return date('Y-m-d H:i:s', $timeStamp);
}


function getFileData($path){
    if (!file_exists($path)){
        return [];
    }
    $data = file_get_contents($path);
    $data = json_decode($data,true);
    return $data;
}


function getFileDataForLang($path){
    if (!file_exists($path)){
        return [];
    }
    $data = file_get_contents($path);
    $data = json_decode($data);
    return $data;
}

/**
 * 左侧填充0
 * @param $value
 * @param int $padLength
 * @return string
 */
function pad_left($value, $padLength = 2)
{
    return \str_pad($value, $padLength, "0", STR_PAD_LEFT);
}

/**
 * 过滤emoji表情
 * @param $text
 * @return null|string|string[]
 */
function filter_emoji($text)
{
    // 此处的preg_replace用于过滤emoji表情
    // 如需支持emoji表情, 需将mysql的编码改为utf8mb4
    return preg_replace('/[\xf0-\xf7].{3}/', '', $text);
}

/**
 * 根据指定长度截取字符串
 * @param $str
 * @param int $length
 * @return bool|string
 */
function str_substr($str, $length = 30)
{
    if (strlen($str) > $length) {
        $str = mb_substr($str, 0, $length);
    }
    return $str;
}

// 创建货位码
function createQrcodeCode($prefix){
    $numbers = range (1,50);

    //shuffle 将数组顺序随即打乱
    
    shuffle ($numbers);
    
    //array_slice 取该数组中的某一段
    
    $num=6;
    
    $result = array_slice($numbers,0,$num);
    $code = '';
    foreach ($result as $v){
        $code.= $v;
    }
    return $prefix.$code.=rand(000,999);
}

  // 分享码合成
 function createShareImage($qrcode){

    $default_bg = '/'; // 默认分享背景图
    // 读取 后台 分享设置
    $setting = (new DealerSetting())->getDealerSetting('qrcode');
    if ($setting['values']['backdrop']){
        $default_bg = $setting['values']['backdrop']['src'];
    }
    $qrcodeConfig = $setting['values']['qrcode'];
        // dump($qrcodeConfig['style'] =="circle");die;
     // 读取背景图 资源
    list($bg_img_weigth,$bg_img_height) = getimagesize($default_bg);
    $width = $bg_img_weigth; // 宽
    $height = $bg_img_height; // 高
    $sourceImage = imagecreatetruecolor($width,$height);
    // 填充 白色 垫底
    imagecolorallocate($sourceImage,255,255,255);
    $shareImg = createImageFromFile($default_bg);
    imagecopyresized($sourceImage, $shareImg, 0,0 , 0, 0, $width,$height, $bg_img_weigth, $bg_img_height);
    // 生成一个 二维码 遮罩 
    $qrcode_layer_width = $qrcodeConfig['width'];
    $qrcode_layer_height = $qrcodeConfig['width'];

    // 读取二维码数据
    list($qrcode_width,$qrcode_height) = getimagesize($qrcode);
    if($qrcodeConfig['style'] =="circle"){
        $qsource = get_radius_image($qrcode,$qrcode_width/2);
    }else{
        $qsource =  createImageFromFile($qrcode);
    }


    imagecopyresized($sourceImage, $qsource,$qrcodeConfig['left']*$width/375,$qrcodeConfig['top']*$width/375, 0, 0, $qrcodeConfig['width']*$width/375,$qrcodeConfig['width']*$width/375, $qrcode_width, $qrcode_height);
    $file_path = 'uploads/shareImages/'.date("Ymd").'/';
    $file_name=md5(microtime()).'.png';
    if(!file_exists($file_path)){
        mkdir($file_path,0755,true);
    }
    imagepng ($sourceImage,$file_path . $file_name,3);
    imagedestroy($sourceImage);
    return $file_path.$file_name;
 }
 
/**
 * 将图片转为圆角图片
 * @param string $image_path 图片路径,生成的圆角图片会覆盖传入的图片
 * @param integer $radius 圆角曲值
 * @return array 返回值 ['status' => '状态码,1-成功,0-失败', 'msg' => '返回消息', 'image_path' => '圆角图片路径']
 */
function get_radius_image($image_path, $radius = 15)
{
    try {
        if (empty($image_path) || !file_exists($image_path)) {
            throw new Exception('图片路径为空或图片不存在');
        }
        $info = getimagesize($image_path);
        $w = $info[0];
        $h = $info[1];
        switch ($info['mime']) {
            case 'image/jpeg':
                $src = imagecreatefromjpeg($image_path);
                break;
            case 'image/gif':
                $src = imagecreatefromgif($image_path);
                break;
            case 'image/png':
                $src = imagecreatefrompng($image_path);
                break;
            default:
                // 如需其他类型可自己扩展
                throw new Exception('图片类型仅支持: jpeg,gif,png');
        }
        $q = 10;
        $radius *= $q;
        do {
            $r = rand(0, 255);
            $g = rand(0, 255);
            $b = rand(0, 255);
        } while (imagecolorexact($src, $r, $g, $b) < 0);
        $nw = $w * $q;
        $nh = $h * $q;
        $img = imagecreatetruecolor($nw, $nh);
        $alphacolor = imagecolorallocatealpha($img, $r, $g, $b, 127);
        imagealphablending($img, false);
        imagesavealpha($img, true);
        imagefilledrectangle($img, 0, 0, $nw, $nh, $alphacolor);
        imagefill($img, 0, 0, $alphacolor);
        imagecopyresampled($img, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagearc($img, $radius - 1, $radius - 1, $radius * 2, $radius * 2, 180, 270, $alphacolor);
        imagefilltoborder($img, 0, 0, $alphacolor, $alphacolor);
        imagearc($img, $nw - $radius, $radius - 1, $radius * 2, $radius * 2, 270, 0, $alphacolor);
        imagefilltoborder($img, $nw - 1, 0, $alphacolor, $alphacolor);
        imagearc($img, $radius - 1, $nh - $radius, $radius * 2, $radius * 2, 90, 180, $alphacolor);
        imagefilltoborder($img, 0, $nh - 1, $alphacolor, $alphacolor);
        imagearc($img, $nw - $radius, $nh - $radius, $radius * 2, $radius * 2, 0, 90, $alphacolor);
        imagefilltoborder($img, $nw - 1, $nh - 1, $alphacolor, $alphacolor);
        imagealphablending($img, true);
        imagecolortransparent($img, $alphacolor);
        $dest = imagecreatetruecolor($w, $h);
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
        imagefilledrectangle($dest, 0, 0, $w, $h, $alphacolor);
        imagecopyresampled($dest, $img, 0, 0, 0, 0, $w, $h, $nw, $nh);
        imagedestroy($src);
        imagedestroy($img);
        return $dest;
    } catch (\Exception $e) {
        return false;
    }
}
 
    /**
     * 圆角矩形
     * @param $imageObj
     * @param $arcRec_SX 开始点X坐标
     * @param $arcRec_SY 开始点Y坐标
     * @param $arcRec_EX 结束点X坐标
     * @param $arcRec_EY 结束点Y坐标
     * @param $redius 圆角半径
     * @param $color 颜色
     */
    function arcRec($imageObj, $arcRec_SX, $arcRec_SY, $arcRec_EX, $arcRec_EY, $redius, $color)
    {
        $arcRec_W = $arcRec_EX - $arcRec_SX;
        $arcRec_H = $arcRec_EY - $arcRec_SY;
        imagefilledrectangle($imageObj, $arcRec_SX + $redius, $arcRec_SY, $arcRec_SX + ($arcRec_W - $redius), $arcRec_SY + $redius, $color);        //矩形一
        imagefilledrectangle($imageObj, $arcRec_SX, $arcRec_SY + $redius, $arcRec_SX + $arcRec_W, $arcRec_SY + ($arcRec_H - ($redius * 1)), $color);//矩形二
        imagefilledrectangle($imageObj, $arcRec_SX + $redius, $arcRec_SY + ($arcRec_H - ($redius * 1)), $arcRec_SX + ($arcRec_W - ($redius * 1)), $arcRec_SY + $arcRec_H, $color);//矩形三
        imagefilledarc($imageObj, $arcRec_SX + $redius, $arcRec_SY + $redius, $redius * 2, $redius * 2, 180, 270, $color, IMG_ARC_PIE);   //四分之一圆 - 左上
        imagefilledarc($imageObj, $arcRec_SX + ($arcRec_W - $redius), $arcRec_SY + $redius, $redius * 2, $redius * 2, 270, 360, $color, IMG_ARC_PIE);   //四分之一圆 - 右上
        imagefilledarc($imageObj, $arcRec_SX + $redius, $arcRec_SY + ($arcRec_H - $redius), $redius * 2, $redius * 2, 90, 180, $color, IMG_ARC_PIE);   //四分之一圆 - 左下
        imagefilledarc($imageObj, $arcRec_SX + ($arcRec_W - $redius), $arcRec_SY + ($arcRec_H - $redius), $redius * 2, $redius * 2, 0, 90, $color, IMG_ARC_PIE);   //四分之一圆 - 右下
    }
 
/**
 * 获取微信小程序Access_token
 * @param  str  appid
 * @param  str  appsecret
 * @return array 成功code=200 返回access_token  否则返回errcode 和 errmsg
 */
function getWcAccess_token($appid,$appsecret){
    $Access_token = \think\Cache::get($appid.'_access_token');
    if($Access_token){
        $res['code'] = 200;
        $res['access_token'] = $Access_token;
    }else{
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
        $re = makeRequest($url);

        if($re['code']==200){
            $result = json_decode($re['result'],true);
            $res['code'] = $re['code'];
            $res['access_token'] = $result['access_token'];
            $res['expires_in'] = $result['expires_in'];
            \think\Cache::set($appid.'_access_token',$result['access_token'],$result['expires_in']);
        }else{
            $result = json_decode($re['result'],true);
            $res['code'] = $re['code'];
            $res['errcode'] = $result['errcode'];
            $res['errmsg'] = $result['errmsg'];
        }
    }
    return $res;
}

function generate_password( $length = 8 ) { 
// 密码字符集，可任意添加你需要的字符 
$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; 
$password = ''; 
for ( $i = 0; $i < $length; $i++ ) 
{ 
// 这里提供两种字符获取方式 
// 第一种是使用 substr 截取$chars中的任意一位字符； 
// 第二种是取字符数组 $chars 的任意元素 
// $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1); 
$password .= $chars[ mt_rand(0, strlen($chars) - 1) ]; 
} 
return $password; 
} 


function getWxCodeByMemberId($code,$user_id){
        $system = Wxapp::getWxappCache();
        
        $access_token = Cache::get($system['app_id'].'@access_token');
//   dump($access_token);die;
        if (!$access_token){
            $access_token=getWcAccess_token($system['app_id'],$system['app_secret']);
            Cache::set($system['app_id'].'@access_token',$access_token['access_token'],6000);
            $access_token = $access_token['access_token'];
        }
           
        $url="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token;
           
        $data=[
            'scene'=>'code='.$code.'&user_id='.$user_id,
            'page'=>'pages/my/my',
        ];
        $data=json_encode($data);
        $res = makeRequest($url,$data)['result'];
        //  dump($res);die;
        if(!json_decode($res)){
            $file_path= "uploads/share/".date('Ymd');
            if(!file_exists($file_path)){
                mkdir($file_path,0755,true);
            }
            $path =$file_path.'/'.$user_id.'_share_image'.'.jpg';
            file_put_contents($path,$res);
            $return = [
                'code' => 1,
                'msg' => '图片生成成功',
                'data' => $path,
            ];
            Cache::set('wx_code_goods_id'.$user_id,$path,time()+7200);
            return $return;;
        }else{
            $return = [
                'code' => 0,
                'msg' => '图片生成失败',
                'data' => null
            ];
            Cache::set($system['app_id'].'wxAccess_token','',time()+7200);
            return $return;
        }
    }
    
/**
 * 发起http请求
 * @param string $url 访问路径
 * @param array $params 参数，该数组多于1个，表示为POST
 * @param int $expire 请求超时时间
 * @param array $extend 请求伪造包头参数
 * @param string $hostIp HOST的地址
 * @return array    返回的为一个请求状态，一个内容
 */
function makeRequest($url, $params = array(), $expire = 0, $extend = array(), $hostIp = '')
{
    if (empty($url)) {
        return array('code' => '100');
    }

    $_curl = curl_init();
    $_header = array(
        'Accept-Language: zh-CN',
        'Connection: Keep-Alive',
        'Cache-Control: no-cache'
    );
    // 方便直接访问要设置host的地址
    if (!empty($hostIp)) {
        $urlInfo = parse_url($url);
        if (empty($urlInfo['host'])) {
            $urlInfo['host'] = substr(DOMAIN, 7, -1);
            $url = "http://{$hostIp}{$url}";
        } else {
            $url = str_replace($urlInfo['host'], $hostIp, $url);
        }
        $_header[] = "Host: {$urlInfo['host']}";
    }

    // 只要第二个参数传了值之后，就是POST的
    if (!empty($params)) {
        curl_setopt($_curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($_curl, CURLOPT_POST, true);
    }

    if (substr($url, 0, 8) == 'https://') {
        curl_setopt($_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($_curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    curl_setopt($_curl, CURLOPT_URL, $url);
    curl_setopt($_curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($_curl, CURLOPT_USERAGENT, 'API PHP CURL');
    curl_setopt($_curl, CURLOPT_HTTPHEADER, $_header);

    if ($expire > 0) {
        curl_setopt($_curl, CURLOPT_TIMEOUT, $expire); // 处理超时时间
        curl_setopt($_curl, CURLOPT_CONNECTTIMEOUT, $expire); // 建立连接超时时间
    }

    // 额外的配置
    if (!empty($extend)) {
        curl_setopt_array($_curl, $extend);
    }

    $result['result'] = curl_exec($_curl);
    $result['code'] = curl_getinfo($_curl, CURLINFO_HTTP_CODE);
    $result['info'] = curl_getinfo($_curl);
    if ($result['result'] === false) {
        $result['result'] = curl_error($_curl);
        $result['code'] = -curl_errno($_curl);
    }

    curl_close($_curl);
    return $result;
}


 /**
 * 从图片文件创建Image资源
 * @param $file 图片文件，支持url
 * @return bool|resource    成功返回图片image资源，失败返回false
 */
function createImageFromFile($file){
    if(preg_match('/^http(s)?:\\/\\/.+/',$file)){
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $file); //设置需要获取的URL
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);//设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //支持https
        curl_exec($ch);//执行curl会话
        $http_code = curl_getinfo($ch);//获取curl连接资源句柄信息
        curl_close($ch);//关闭资源连接
        if ($http_code['http_code'] == 200) {
            $theImgType = explode('/',$http_code['content_type']);
            if($theImgType[0] == 'image'){
                $fileSuffix = $theImgType[1];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }else{
        $file=str_replace('\\','/',$file);
        $info=getimagesize($file);
        $theImgType = explode('/',$info['mime']);
        if($theImgType[0] == 'image'){
            $fileSuffix = $theImgType[1];
        }else{
            return false;
        }
    }

    if(!$fileSuffix) return false;

    switch ($fileSuffix){
        case 'jpeg':
            $theImage = @imagecreatefromjpeg($file);
            break;
        case 'jpg':
            $theImage = @imagecreatefromjpeg($file);
            break;
        case 'png':
            $theImage = @imagecreatefrompng($file);
            break;
        case 'gif':
            $theImage = @imagecreatefromgif($file);
            break;
        default:
            $theImage = @imagecreatefromstring(file_get_contents($file));
            break;
    }

    return $theImage;
}

 /**
     * 生成二维码图片（可生成带logo的二维码）
     *
     * @param string $data 二维码内容
     *         示例数据：http://www.tf4.cn或weixin://wxpay/bizpayurl?pr=0tELnh9
     * @param string $saveDir 保存路径名（示例:Qrcode）
     * @param string $logo 图片logo路径
     *         示例数据：./Public/Default/logo.jpg
     *         注意事项：1、前面记得带点（.）；2、建议图片Logo正方形，且为jpg格式图片；3、图片大小建议为xx*xx
     * 
     * 注意：一般用于生成带logo的二维码
     * 
     * @return
     */
    function createQrcode($data,$saveDir="Qrcode",$logo = "")
    {
        $rootPath = 'uploads/';
        $path = $saveDir.'/'.date("Y-m-d").'/';
        $fileName = uniqid();
        if (!is_dir($rootPath.$path))
        {
            mkdir($rootPath.$path,0777,true);
        }
    
        $originalUrl = $path.$fileName.'.png';
        require('../source/application/common/library/phpqrcode/phpqrcode.php');
        
        $object = new \QRcode();
        $errorCorrectionLevel = 'L';    //容错级别
        $matrixPointSize = 20;            //生成图片大小（这个值可以通过参数传进来判断）
        $object->png($data,$rootPath.$originalUrl,$errorCorrectionLevel, $matrixPointSize, 2);
 
        //判断是否生成带logo的二维码
        if(file_exists($logo))
        {
            $QR = imagecreatefromstring(file_get_contents($rootPath.$originalUrl));        //目标图象连接资源。
            $logo = imagecreatefromstring(file_get_contents($logo));    //源图象连接资源。
            
            $QR_width = imagesx($QR)*2;            //二维码图片宽度
            $QR_height = imagesy($QR)*2;            //二维码图片高度
            $logo_width = imagesx($logo);        //logo图片宽度
            $logo_height = imagesy($logo);        //logo图片高度
            $logo_qr_width = $QR_width / 4;       //组合之后logo的宽度(占二维码的1/5)
            $scale = $logo_width/$logo_qr_width;       //logo的宽度缩放比(本身宽度/组合后的宽度)
            $logo_qr_height = $logo_height/$scale;  //组合之后logo的高度
            $from_width = ($QR_width - $logo_qr_width) / 2;   //组合之后logo左上角所在坐标点
            
            //重新组合图片并调整大小
            //imagecopyresampled() 将一幅图像(源图象)中的一块正方形区域拷贝到另一个图像中
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,$logo_qr_height, $logo_width, $logo_height);
            
            //输出图片
            imagepng($QR, $rootPath.$originalUrl);
            imagedestroy($QR);
            imagedestroy($logo);
        }
        
        $result['errcode'] = 0;
        $result['errmsg'] = 'ok';
        $result['data'] = $rootPath.$originalUrl;
        return $result;
    }
    
    // 判断手机号格式
function is_mobile($phone_number){
     //@2017-11-25 14:25:45 https://zhidao.baidu.com/question/1822455991691849548.html
        //中国联通号码：130、131、132、145（无线上网卡）、155、156、185（iPhone5上市后开放）、186、176（4G号段）、175（2015年9月10日正式启用，暂只对北京、上海和广东投放办理）,166,146
        //中国移动号码：134、135、136、137、138、139、147（无线上网卡）、148、150、151、152、157、158、159、178、182、183、184、187、188、198
        //中国电信号码：133、153、180、181、189、177、173、149、199
        $g = "/^1[34578]\d{9}$/";
        $g2 = "/^19[89]\d{8}$/";
        $g3 = "/^166\d{8}$/";
        if(preg_match($g, $phone_number)){
            return true;
        }else  if(preg_match($g2, $phone_number)){
            return true;
        }else if(preg_match($g3, $phone_number)){
            return true;
        }
        return false;
}

/**
 * 系统邮件发送函数
 * @param string $tomail 接收邮件者邮箱
 * @param string $name 接收邮件者名称
 * @param string $subject 邮件主题
 * @param string $body 邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 */
function send_mail($tomail, $name, $subject = '', $body = '', $attachment = null, $setting) {
    $mail = new Email();         //实例化PHPMailer对象
    $mail->CharSet = 'UTF-8';           //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();                    // 设定使用SMTP服务
    $mail->SMTPDebug = 0;               // SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
    $mail->SMTPAuth = true;             // 启用 SMTP 验证功能
    $mail->SMTPSecure = 'ssl';          // 使用安全协议
    $mail->Host = "smtp.qq.com"; // SMTP 服务器
    $mail->Port = 465;                  // SMTP服务器的端口号
    $mail->Username = $setting['Username'];    // SMTP服务器用户名
    $mail->Password = $setting['Password'];     // SMTP服务器密码
    $mail->SetFrom($setting['Username'], $setting['replyName']);
    $replyEmail = $setting['replyEmail'];                   //留空则为发件人EMAIL
    $replyName = $setting['replyName'];                    //回复名称（留空则为发件人名称）
    $mail->AddReplyTo($replyEmail, $replyName);
    $mail->Subject = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($tomail, $name);
    if (is_array($attachment)) { // 添加附件
        foreach ($attachment as $file) {
            is_file($file) && $mail->AddAttachment($file);
        }
    }
    return $mail->Send();
}


   /**
    * 选定路线运费查询
    * 最全运费查询
    */
 function getfree($data){
       $length = $data['length'];
       $width  = $data['width'];
       $height = $data['height'];
       $weigth = $data['weight'];
       $country = isset( $data['country'])? $data['country']:"";
       $wxappId = isset($data['wxapp_id'])?$data['wxapp_id']:'';
       $line_id = $data['line_id'];
       $weigthV = 0; //初始化为0
       $weigthV_fi =0;
       $setting = SettingModel::getItem('store',$wxappId);

  
       //校验长宽高是否存在
       if(!empty($width) && !empty($height) && !empty($length)){
         // 计算体积重 6000计算方式
         $weigthV = round(($length*$width*$height)/6000,2); 
         $weigthV_fi = round(($length*$width*$height)/5000,2); 
       }
       
       //这里用于计算路线国家的匹配度
        $line = (new Line())->where(['id' => $line_id])->select();
       //还需要计算重量范围是否符合，物品属性是否匹配
        $lines =[];
        $k = 0;
        foreach ($line as $key => $value) {
            //根据体积重的模式，5000 or 6000设置对应的计算方式
            if($value['volumeweight']==6000){
                // 取两者中 较重者 
               $oWeigth = $weigthV>$weigth?$weigthV:$weigth;  
            }else{
              // 取两者中 较重者 
               $oWeigth = $weigthV_fi>$weigth?$weigthV_fi:$weigth; 
            }
           if(isset($value['max_weight']) && isset($value['weight_min'])){
             if(($oWeigth < $value['weight_min']) || ($oWeigth > $value['max_weight'])){
                 continue;
             }
           }
           
           if($setting['is_discount']==1){
            // $this->user = $this->getUser();
            $UserLine =  (new UserLine());
            $linedata= $UserLine->where('user_id',$this->user['user_id'])->where('line_id',$value['id'])->find();
                if($linedata){
                   $value['discount']  = $linedata['discount'];
                }else{
                   $value['discount'] =1;
                }
            }else{
                $value['discount'] =1;
            }
           $lines[$k] =$value;
           $k = $k+1;
        }
        
    
        //对剩余符合条件的路线进行计算费用；
       foreach ($lines as $key => $value) {
           $lines[$key]['predict'] = [
              'weight' => $oWeigth,
              'price' => '包裹重量超限',
           ]; 
           
            //关税和增值服务费用
           $otherfree = $value['tariff']+$value['service_route'];
           $reprice=0;
           switch ($value['free_mode']) {
             case '1':
               $free_rule = json_decode($value['free_rule'],true);
               $size = sizeof($free_rule);    
               if(($oWeigth>= $free_rule[0]['weight'][0]) && ($oWeigth<= $free_rule[$size-1]['weight'][1])){
   
                  foreach ($free_rule as $k => $v) {
                      if ($oWeigth>$v['weight'][1]){
                            $reprice += ($v['weight'][1] - $v['weight'][0])*$v['weight_price'];
                            continue;
                      }else{
                           $reprice += ($oWeigth - $v['weight'][0])*$v['weight_price'];
                           break;
                      }
                  }
                  $lines[$key]['sortprice'] =($reprice+ $free_rule[0]['weight_price']*$free_rule[0]['weight'][0]+$otherfree)*$value['discount'];
                  $lines[$key]['predict'] = [
                    'weight' => $oWeigth,
                    'price' => ($reprice+ $free_rule[0]['weight_price']*$free_rule[0]['weight'][0]+$otherfree)*$value['discount'],
                    'rule' => $free_rule
                  ];         
               }else{
                    break;
               }

               break;
             case '2':
                 //首重价格+续重价格*（总重-首重）
               $free_rule = json_decode($value['free_rule'],true);
               foreach ($free_rule as $k => $v) {
                          $lines[$key]['sortprice'] =($v['first_price']+ ceil((($oWeigth-$v['first_weight'])/$v['next_weight']))*$v['next_price'] + $otherfree)*$value['discount'];
                          $lines[$key]['predict'] = [
                              'weight' => $oWeigth,
                              'price' => ($v['first_price']+ ceil((($oWeigth-$v['first_weight'])/$v['next_weight']))*$v['next_price'] + $otherfree)*$value['discount'],
                              'rule' => $v
                          ];   
               }
               break;
              case '3':
                $free_rule = json_decode($value['free_rule'],true);
               foreach ($free_rule as $k => $v) {
                   if ($oWeigth >= $v['weight'][0]){
                      if (isset($v['weight'][1]) && $oWeigth<$v['weight'][1]){
                          $lines[$key]['sortprice'] =($oWeigth*$v['weight_price'] + $otherfree)*$value['discount'] ;
                          $lines[$key]['predict'] = [
                              'weight' => $oWeigth,
                              'price' => ($oWeigth*$v['weight_price'] + $otherfree)*$value['discount'],
                              'rule' => $v
                          ];   
                      }
                   }
               }
               break;
             default:
               # code...
               break;
           }
       }
      $mapsort = [10=>'desc',20=>'asc',30=>'nat'];
      $mapmode = [10=>'sortprice',20=>'sort',30=>'id'];
      $sortedAccounts = $this->list_sort_by($lines, $mapmode[$setting['sort_mode']], $mapsort[$setting['is_sort']]);
       return $this->renderSuccess($sortedAccounts);
    }

    /**
    * 不确定路线查询运费
    * 最全运费查询
    */
 function getpackfree($id){
    $Inpack = new Inpack();
    $Line = new Line();
    $User = new User;
    //获取集运单
    $packData = $Inpack->getDetails($id,$field=[]);

     //重量
    $weight = $packData['length'] * $packData['width'] * $packData['height'] / $packData['line']['volumeweight'];
    if(!empty($packData['length']) && !empty($packData['width']) && !empty($packData['height']) && $packData['line']['volumeweight_type']==20){
        $weight = round(($packData['weight'] + (($packData['length']*$packData['width']*$packData['height'])/$packData['line']['volumeweight'] - $packData['weight'])*$packData['line']['bubble_weight']/100),2);
    }
    // dump($weight);die;
    $oWeigth = $weight - $packData['weight'] > 0 ? $weight:$packData['weight'];
    
    //总费用=路线规则中的费用+路线的增值服务费+服务项目的费用；
    $data = [
        'sortprice'=>0    
    ];
    $reprice = 0;
    $free_rule = json_decode($packData['line']['free_rule'],true);//运费规则
    // $otherfree = $packData['line']['service_route']; //路线的增值服务费用；
    $long = max($packData['length'],$packData['width'],$packData['height']);
    $otherfree = getServiceFree($packData['line']['services_require'],$oWeigth,$long);
    
    $setting = SettingModel::getItem('store',$packData['wxapp_id']);
    if($setting['is_discount']==1){
        $userData = $User::detail(['user_id'=>$packData['member_id']]);
      
        $UserLine =  (new UserLine());
        $linedata= $UserLine->where('user_id',$userData['user_id'])->where('line_id',$packData['line_id'])->find();
            if($linedata){
              $discount  = $linedata['discount'];
            }else{
                if(isset($userData['grade']['equity']['discount']) && $userData['grade']['status']==1){
                   $discount = isset($userData['grade']['equity']['discount'])?($userData['grade']['equity']['discount']/10):1;  
                }else{
                   $discount = 1;
                }
            }
        }else{
            $discount = isset($userData['grade']['equity']['discount'])?($userData['grade']['equity']['discount']/10):1;
        }
    
     switch ($packData['line']['free_mode']) {
             case '1':
               
               $size = sizeof($free_rule);    
               if(($oWeigth>= $free_rule[0]['weight'][0]) && ($oWeigth<= $free_rule[$size-1]['weight'][1])){
   
                  foreach ($free_rule as $k => $v) {
                      if ($oWeigth>$v['weight'][1]){
                            $reprice += ($v['weight'][1] - $v['weight'][0])*$v['weight_price'];
                            continue;
                      }else{
                           $reprice += ($oWeigth - $v['weight'][0])*$v['weight_price'];
                           break;
                      }
                  }
                  $data['sortprice'] = ($reprice+ $free_rule[0]['weight_price']*$free_rule[0]['weight'][0]+$otherfree)*$discount;
                  $data['predict'] = [
                    'weight' => $oWeigth,
                    'price' => ($reprice+ $free_rule[0]['weight_price']*$free_rule[0]['weight'][0]+$otherfree)*$discount,
                    'rule' => $free_rule
                  ];         
               }else{
                    $data['sortprice'] = $free_rule[0]['weight_price']+$otherfree;
                    $data['predict'] = [
                    'weight' => $oWeigth,
                    'price' =>  $free_rule[0]['weight_price']+$otherfree,
                    'rule' => $free_rule
                  ];        
                    break;
               }

               break;
             case '2':
                 //首重价格+续重价格*（总重-首重）

               foreach ($free_rule as $k => $v) {
                          $data['sortprice'] =($v['first_price']+ ceil((($oWeigth-$v['first_weight'])/$v['next_weight']))*$v['next_price'] + $otherfree)*$discount;
                          $data['predict'] = [
                              'weight' => $oWeigth,
                              'price' => ($v['first_price']+ ceil((($oWeigth-$v['first_weight'])/$v['next_weight']))*$v['next_price'] + $otherfree)*$discount,
                              'rule' => $v
                          ];   
               }
               break;
              case '3':

               foreach ($free_rule as $k => $v) {
                   if ($oWeigth >= $v['weight'][0]){
                      if (isset($v['weight'][1]) && $oWeigth<$v['weight'][1]){
                          $data['sortprice'] = ($oWeigth*$v['weight_price'] + $otherfree)*$discount ;
                          $data['predict'] = [
                              'weight' => $oWeigth,
                              'price' => ($oWeigth*$v['weight_price'] + $otherfree)*$discount,
                              'rule' => $v
                          ];   
                      }
                   }
               }
               break;
               
                case '4':
               foreach ($free_rule as $k => $v) {
                   if ($oWeigth > $v['weight'][0]){
                      if (isset($v['weight'][1]) && $oWeigth<=$v['weight'][1]){
                          $data['sortprice'] = ($v['weight_price']*ceil($oWeigth/$v['weight_unit']) + $otherfree)*$discount ;
                          $data['predict'] = [
                              'weight' => $oWeigth,
                              'price' => ($v['weight_price']*ceil($oWeigth/$v['weight_unit']) + $otherfree)*$discount,
                              'rule' => $v
                          ];   
                      }
                   }
               }
               break;
             default:
               # code...
               break;
           }
        //服务项目的费用
        $packfree = 0;
        if(!empty($packData['inpackservice'])){
            foreach ($packData['inpackservice'] as $key => $va){
                if($va['service']['type']==0){
                      $packfree += $va['service']['price'] * $va['service_sum'];
                }else{
                      $packfree += $data['sortprice'] * $va['service']['percentage']/100 * $va['service_sum'];
                }
            }
        }
        
        //  dump($data);die;  
        
        $packD['volume'] = $weight; //体积重
        $packD['cale_weight'] = $oWeigth; //计费重量
        $packD['other_free'] = $otherfree + $packData['other_free'];
        $packD['pack_free'] = $packfree;
        $packD['free'] = $data['sortprice'];
        $resin = $Inpack->where('id',$id)->update($packD);
        if(!$resin){
             return false;
        }
        return true;
 }  
  
    
    /**
    * 不确定路线查询运费
    * 最全运费查询
    */
 function getsearchfree($data){
       $length = $data['length']?$data['length']:0;
       $width  = $data['width']?$data['width']:0;
       $height = $data['height']?$data['height']:0;
       $weigth = $data['weight']?$data['weight']:0;
       $country = isset( $data['country'])?$data['country']:"";
       $wxappId = isset($data['wxapp_id'])?$data['wxapp_id']:'';
       $weigthV = $data['weigthV']?$data['weigthV']:0; //初始化为0
       $weigthV_fi = $data['weigthV']?$data['weigthV']:0;
       $setting = SettingModel::getItem('store',$wxappId);

       //这里用于计算路线国家的匹配度
        $line = (new Line())->with(['image'])->where('FIND_IN_SET(:id,countrys)', ['id' => $country])->select();
       //校验长宽高是否存在
       if(!empty($width) && !empty($height) && !empty($length)){
         // 计算体积重 6000计算方式
         $weigthV = round(($length*$width*$height)/6000,2); 
         $weigthV_fi = round(($length*$width*$height)/5000,2); 
       }else{
          $weigthV = $weigthV_fi = $weigthV;
       }

       //还需要计算重量范围是否符合，物品属性是否匹配
        $lines =[];
        $k = 0;
        foreach ($line as $key => $value) {
            //根据体积重的模式，5000 or 6000设置对应的计算方式
            if($value['volumeweight']==6000){
                // 取两者中 较重者 
               $oWeigth = $weigthV>$weigth?$weigthV:$weigth;  
            }else{
              // 取两者中 较重者 
               $oWeigth = $weigthV_fi>$weigth?$weigthV_fi:$weigth; 
            }
           if(isset($value['max_weight']) && isset($value['weight_min'])){
             if(($oWeigth < $value['weight_min']) || ($oWeigth > $value['max_weight'])){
                 continue;
             }
           }
           
           $lines[$k] =$value;
           $k = $k+1;
        }
        
    
        //对剩余符合条件的路线进行计算费用；
       foreach ($lines as $key => $value) {
           $lines[$key]['predict'] = [
              'weight' => $oWeigth,
              'price' => '包裹重量超限',
           ]; 
           $lines[$key]['sortprice'] = 0; 
            //关税和增值服务费用
           $otherfree = 0;
           $reprice=0;
           switch ($value['free_mode']) {
             case '1':
               $free_rule = json_decode($value['free_rule'],true);
               $size = sizeof($free_rule);    
               if(($oWeigth>= $free_rule[0]['weight'][0]) && ($oWeigth<= $free_rule[$size-1]['weight'][1])){
   
                  foreach ($free_rule as $k => $v) {
                      if ($oWeigth>$v['weight'][1]){
                            $reprice += ($v['weight'][1] - $v['weight'][0])*$v['weight_price'];
                            continue;
                      }else{
                           $reprice += ($oWeigth - $v['weight'][0])*$v['weight_price'];
                           break;
                      }
                  }
                  $lines[$key]['sortprice'] =$reprice+ $free_rule[0]['weight_price']*$free_rule[0]['weight'][0]+$otherfree;
                  $lines[$key]['predict'] = [
                    'weight' => $oWeigth,
                    'price' => $reprice+ $free_rule[0]['weight_price']*$free_rule[0]['weight'][0]+$otherfree,
                    'rule' => $free_rule
                  ];         
               }else{
                    break;
               }

               break;
             case '2':
                 //首重价格+续重价格*（总重-首重）
               $free_rule = json_decode($value['free_rule'],true);
               foreach ($free_rule as $k => $v) {
                          $lines[$key]['sortprice'] =$v['first_price']+ ceil((($oWeigth-$v['first_weight'])/$v['next_weight']))*$v['next_price'] + $otherfree;
                          $lines[$key]['predict'] = [
                              'weight' => $oWeigth,
                              'price' => $v['first_price']+ ceil((($oWeigth-$v['first_weight'])/$v['next_weight']))*$v['next_price'] + $otherfree,
                              'rule' => $v
                          ];   
               }
               break;
              case '3':
               $free_rule = json_decode($value['free_rule'],true);
               foreach ($free_rule as $k => $v) {
                   if ($oWeigth >= $v['weight'][0]){
                      if (isset($v['weight'][1]) && $oWeigth<=$v['weight'][1]){
                          $lines[$key]['sortprice'] = $v['weight_price'] + $otherfree ;
                          $lines[$key]['predict'] = [
                              'weight' => $oWeigth,
                              'price' => $v['weight_price'] + $otherfree,
                              'rule' => $v
                          ];   
                      }
                   }
               }
               break;
               
              case '4':
               $free_rule = json_decode($value['free_rule'],true);
            //   dump($free_rule);
               foreach ($free_rule as $k => $v) {
                   if ($oWeigth > $v['weight'][0]){
                      if (isset($v['weight'][1]) && $oWeigth<=$v['weight'][1]){
                          $lines[$key]['sortprice'] = $v['weight_price']*ceil($oWeigth/$v['weight_unit']) + $otherfree ;
                          $lines[$key]['predict'] = [
                              'weight' => $oWeigth,
                              'price' => $v['weight_price']*ceil($oWeigth/$v['weight_unit']) + $otherfree ,
                              'rule' => $v
                          ];   
                      }
                   }
               }
               break;
             default:
               # code...
               break;
           }
       }
      $mapsort = [10=>'desc',20=>'asc',30=>'nat'];
      $mapmode = [10=>'sortprice',20=>'sort',30=>'id'];  
    //   dump($lines);die;
      $sortedAccounts = list_sort_by($lines, $mapmode[$setting['sort_mode']], $mapsort[$setting['is_sort']]);
         
      return $sortedAccounts;
    }
    
     /**
	 * 对查询结果集进行排序
	 * @access public
	 * @param array $list 查询结果
	 * @param string $field 排序的字段名
	 * @param array $sortby 排序类型
	 * asc正向排序 desc逆向排序 nat自然排序
	 * @return array
	 */
	 function list_sort_by($list, $field, $sortby = 'asc'){
	    if (is_array($list)) {
	        $refer = $resultSet = array();
	        foreach ($list as $i => $data)
	            $refer[$i] = $data[$field];
	        switch ($sortby) {
	            case 'asc': // 正向排序
	                asort($refer);
	                break;
	            case 'desc':// 逆向排序
	                arsort($refer);
	                break;
	            case 'nat': // 自然排序
	                natcasesort($refer);
	                break;
	        }
	        foreach ($refer as $key => $val)
	            $resultSet[] = &$list[$key];
	        return $resultSet;
	    }
	    return false;
	}
    
    /**
 * 将非GBK字符集的编码转为GBK
 *
 * @param mixed $mixed 源数据
 *
 * @return mixed GBK格式数据
 */
function charsetToGBK($mixed)
{
    if (is_array($mixed)) {
        foreach ($mixed as $k => $v) {
            if (is_array($v)) {
                $mixed[$k] = charsetToGBK($v);
            } else {
                $encode = mb_detect_encoding($v, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
                if ($encode == 'UTF-8') {
                    $mixed[$k] = iconv('UTF-8', 'GBK', $v);
                }
            }
        }
    } else {
        $encode = mb_detect_encoding($mixed, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
        if ($encode == 'UTF-8') {
            $mixed = iconv('UTF-8', 'GBK', $mixed);
        }
    }
    return $mixed;
}

/**
 * 将非UTF-8字符集的编码转为UTF-8
 *
 * @param mixed $mixed 源数据
 *
 * @return mixed utf-8格式数据
 */
function charsetToUTF8($mixed)
{
    if (is_array($mixed)) {
        foreach ($mixed as $k => $v) {
            if (is_array($v)) {
                $mixed[$k] = charsetToUTF8($v);
            } else {
                $encode = mb_detect_encoding($v, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
                if ($encode == 'EUC-CN') {
                    $mixed[$k] = iconv('GBK', 'UTF-8', $v);
                }
            }
        }
    } else {
        $encode = mb_detect_encoding($mixed, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
        if ($encode == 'EUC-CN') {
            $mixed = iconv('GBK', 'UTF-8', $mixed);
        }
    }
    return $mixed;
}

/**字符串加解密
 * @param $string 要加密/解密字符串
 * @param string $operation DECODE：解密 ENCODE：加密
 * @param string $key   秘钥
 * @param int $expiry   密文有效期（秒）
 * @return string
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
    $ckey_length = 4;
 
    // 密匙
    $key = md5($key ? $key : config('en_key'));
    
    // 密匙a会参与加解密
    $keya = md5(substr($key, 0, 16));
    // 密匙b会用来做数据完整性验证
    $keyb = md5(substr($key, 16, 16));
    // 密匙c用于变化生成的密文
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length):
        substr(md5(microtime()), -$ckey_length)) : '';
    // 参与运算的密匙
    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);
    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
//解密时会通过这个密匙验证数据完整性
    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :
        sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    // 产生密匙簿
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    // 核心加解密部分
    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        // 从密匙簿得出密匙进行异或，再转成字符
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if($operation == 'DECODE') {
        // 验证数据有效性，请看未加密明文的格式
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&
            substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}

// 链接生成函数
function urlCreate($path,$params=[]){
    $paramsstr = http_build_query($params);
    $wxappStr = $_SERVER['QUERY_STRING'];
    $wxappArr = [];
    $wxappArr_temp = explode('&',$wxappStr);
    foreach ($wxappArr_temp as $v){
         $v1 = explode('=',$v);
         $wxappArr[$v1[0]] = $v1[1];
    }
    $wxappId = encrypt($wxappArr['wxappid'],'D');
    
    return $paramsstr?$path.'?'.$paramsstr.'&wxappid='.encrypt($wxappId,'E'):$path.'?wxappid='.encrypt($wxappId,'E');
    
}

/**
 * 获取runtime根目录路径
 * @return string
 */
function runtime_root_path(): string
{
    return dirname(RUNTIME_PATH) . DIRECTORY_SEPARATOR;
}


