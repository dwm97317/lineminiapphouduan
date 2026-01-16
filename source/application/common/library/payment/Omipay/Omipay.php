<?php
namespace app\common\library\payment\Omipay;

use app\common\library\payment\Omipay\config;
use app\common\model\Setting as SettingModel;
use app\api\model\Wxapp as WxappModel;
use app\common\enum\order\PayType as PayTypeEnum;
use app\common\enum\OrderType as OrderTypeEnum;
use app\common\exception\BaseException;
use think\Request;


class Omipay extends config{
    
    public $_config = [];
    
    public function __construct($config=false){
        parent::__construct();
        $this->_config = (new config())->_default();
        $this->config = $config;
       
        $this->config !== false && $this->setting = SettingModel::getItem('paytype')['omipay'];
        $this->config !== false && $this->setConfig($this->config['mchid'], $this->config['apikey']);
        $this->config !== false && $this->loadConfig();
        $this->config !== false && $this->check();
    }
    
    // 订单模型
    private $modelClass = [
        OrderTypeEnum::MASTER => 'app\api\service\order\PaySuccess',
        OrderTypeEnum::SHARING => 'app\api\service\sharing\order\PaySuccess',
        OrderTypeEnum::RECHARGE => 'app\api\service\recharge\PaySuccess',
        OrderTypeEnum::TRAN => 'app\api\service\package\PaySuccess',
    ];
    
    // 读取数据配置
    public function loadConfig(){
        $this->_config['apikey'] = $this->setting['apikey'];
        $this->_config['mid'] = $this->setting ['mid'];
        $this->_config['is_open'] = $this->setting ['is_open'];
        $this->_config['currency'] = $this->setting ['currency'];
    }
    
    // 检测相关参数
    public function check(){
        if (!$this->_config['is_open']){
            echo '请到支付设置中开启Omipay支付功能';
            die;
        }
        if ($this->_config['mid']=='' || $this->_config['apikey']==''){
            echo '请到支付设置中配置完Omipay支付';
            die;
        }
        
    }
    
    // 汉特小程序支付
    public function unifiedorder($order_no,$amount,$openid,$user,$orderType,$body='',$note=''){
        $api = $this->_config['gateway'].'/api/v2/MakeAppletOrder';
        $header = [
           "Content-Type:application/json"
        ];
        $method = "GET";
        $pricemode = SettingModel::getItem('store')['price_mode']['mode'];
        // 读取小程序信息
        $body = [
           'order_name' => "集运订单".$order_no,
           'out_order_no'=> $order_no,
           'amount'  => (int)round(($amount * 100)),
           'currency' => $this->_config['currency'],
           'platform'=>'WECHATPAY',
           'notify_url'  =>  base_url() . 'omipay.php',  // 异步通知地址
           'customer_id' => $openid,
           'app_id'       => $this->config['app_id'],
           'm_number'=>$this->setting['mid'],
           'timestamp'=>$this->get_msectime(),
           'nonce_str'=>$this->getRandom(32),
        ];
        if($orderType==OrderTypeEnum::RECHARGE){
            $body['notify_url'] =  base_url() . 'reomipay.php';  // 异步通知地址
        }
        $body['sign'] = $this->signature($body['timestamp'],$body['nonce_str']);
        $url = $api.'?'. $this->toUrlParams($body);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$".$this->_config['gateway'], "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }


        // $result = $this->curl($api,$body,$header);
        $result = curl_exec($curl);
          
        if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == '200') {
           list($header, $bodys) = explode("\r\n\r\n", $result, 2);
        }
        $prepay = json_decode($bodys,true);
        // 请求失败
        if ($prepay['return_code'] === 'FAIL') {
            throw new BaseException(['msg' => "OMIPAY支付：{$prepay['return_code']}", 'code' => -10]);
        }
    //   dump($prepay);die;
        $per = explode('=',$prepay['package']);
        // 生成 nonce_str 供前端使用
        return [
            'prepay_id' => $per[1],
            'nonceStr' =>  $prepay['nonceStr'],
            'timeStamp' => $prepay['timeStamp'],
            'paySign' => $prepay['paySign'],
        ];
    }
   
    function curl($url, $data = [],$header)
    {
        // 处理get数据
        if (!empty($data)) {
            $url = $url . '?' . http_build_query($data);
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    } 
    
    
    function curlPost($url,$data,$header)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }


 /**
     * 支付成功异步通知
     * @throws BaseException
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    public function notify()
    {
        $data =input();
        log_write($data);
        $model = $this->getOrderModel($data['out_order_no'],OrderTypeEnum::TRAN);
        // 订单信息
        $order = $model->getOrderInfo();
        log_write($order);
        empty($order) && $this->returnCode(false, '订单不存在');
        
        
        // 订单支付成功业务处理
        $status = $model->onPaySuccess(PayTypeEnum::OMIPAY, $data);
        if ($status == false) {
            $this->returnCode(false, $model->getError());
        }
        // 返回状态
        $this->returnCode(true, 'OK');
    }
    
    /**
     * 支付成功异步通知
     * @throws BaseException
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    public function renotify()
    {
        $data =input();
     
        $model = $this->getOrderModel($data['out_order_no'],OrderTypeEnum::RECHARGE);
     
        // 订单信息
        $order = $model->getOrderInfo();
        log_write($order);
        empty($order) && $this->returnCode(false, '订单不存在');
  
        
        // 订单支付成功业务处理
        $status = $model->onPaySuccess(PayTypeEnum::OMIPAY, $data);
        if ($status == false) {
            $this->returnCode(false, $model->getError());
        }
        // 返回状态
        $this->returnCode(true, 'OK');
    }
    
        /**
     * 返回状态给微信服务器
     * @param boolean $returnCode
     * @param string $msg
     */
    private function returnCode($returnCode = true, $msg = null)
    {
        // 返回状态
        $return = [
            'return_code' => $returnCode ? 'SUCCESS' : 'FAIL',
            'return_msg' => $msg ?: 'OK',
        ];
        // 记录日志
        log_write([
            'describe' => '返回微信支付状态',
            'data' => $return
        ]);
        die($this->toXml($return));
    }

    /**
     * 输出xml字符
     * @param $values
     * @return bool|string
     */
    private function toXml($values)
    {
        if (!is_array($values)
            || count($values) <= 0
        ) {
            return false;
        }

        $xml = "<xml>";
        foreach ($values as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 实例化订单模型 (根据attach判断)
     * @param $orderNo
     * @param null $attach
     * @return mixed
     */
    private function getOrderModel($orderNo,$orderType)
    {
        // 判断订单类型返回对应的订单模型
        $model = $this->modelClass[$orderType];
        return new $model($orderNo);
    }

    /**
     * 将xml转为array
     * @param $xml
     * @return mixed
     */
    private function fromXml($xml)
    {
        // 禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }
    
    /**
     * 生成paySign
     * @param $nonceStr
     * @param $prepay_id
     * @param $timeStamp
     * @return string
     */
    private function makePaySign($nonceStr, $prepay_id, $timeStamp)
    {
        $data = [
            'appId' => $this->appId,
            'nonceStr' => $nonceStr,
            'prepay_id' => 'prepay_id='.$prepay_id,
            'signType' => 'MD5',
            'timeStamp' => $timeStamp,
        ];
        // 签名步骤一：按字典序排序参数
        ksort($data);
        $string = $this->toUrlParams($data);
        // 签名步骤二：在string后加入KEY
        $string = $string . '&key=' . $this->setting['apikey'];
        // 签名步骤三：MD5加密
        $string = md5($string);
        // 签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }
    

    
    function getRandom($param){
        $str="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $key = "";
        for($i=0;$i<$param;$i++)
        {
            $key .= $str{mt_rand(0,32)};    //生成php随机数
        }
        return $key;
    }
    
    /**
     * 模拟POST请求
     * @param $url
     * @param array $data
     * @param bool $useCert
     * @param array $sslCert
     * @return mixed
     */
    protected function post($url, $data = [], $header = [], $useCert = false, $sslCert = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        if ($header)
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        if ($useCert == true) {
            // 设置证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($curl, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLCERT, $sslCert['certPem']);
            curl_setopt($curl, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLKEY, $sslCert['keyPem']);
        }
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
    
     /**
     * 生成签名
     * @param $values
     * @return string 本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    private function signature($timeStamp,$nonce_str)
    {
        $string = $this->setting['mid'].'&'.$timeStamp.'&'.$nonce_str.'&'.$this->setting['apikey'];
        $result = strtoupper(md5($string));
        return $result;
    }

    /**
     * 格式化参数格式化成url参数
     * @param $values
     * @return string
     */
    private function toUrlParams($values)
    {
        $buff = '';
        foreach ($values as $k => $v) {
            if ($k != 'sign_type' && $v != '' && !is_array($v)) {
                $buff .= $k . '=' . $v . '&';
            }
        }
        return trim($buff, '&');
    }
    
    private function get_msectime() {
        list($msec, $sec) = explode(' ', microtime());
        $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;

    }
    
    function str_to_utf8 ($str = '') {
        $current_encode = mb_detect_encoding($str, array("ASCII","GB2312","GBK",'BIG5','UTF-8')); 
        $encoded_str = mb_convert_encoding($str, 'UTF-8', $current_encode);
        return $encoded_str;
    }
}