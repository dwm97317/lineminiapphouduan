<?php
namespace app\common\library\payment\HantePay;
use app\common\library\payment\HantePay\config;
use app\common\model\Setting as SettingModel;
use app\api\model\Wxapp as WxappModel;
use app\common\enum\order\PayType as PayTypeEnum;
use app\common\enum\OrderType as OrderTypeEnum;
use app\common\exception\BaseException;


class hantePay extends config{
    
    public $_config = [];
    
    public function __construct($config=false){
        parent::__construct();
        $this->_config = (new config())->_default();
        $this->config = $config;
        $this->config !== false && $this->setConfig($this->config['app_id'], $this->config['app_secret']);
        $this->loadConfig();
        $this->check();
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
        $config = SettingModel::getItem('paytype');
        if ($config){
            $this->_config['key'] = $config['Hantepay']['apikey'];
            $this->_config['code'] = $config['Hantepay']['store_no'];
            $this->_config['mch_code'] = $config['Hantepay']['merchant_no'];
            $this->_config['is_open'] = $config['Hantepay']['is_open'];
        }
        
    }
    
    // 检测相关参数
    public function check(){
        if (!$this->_config['is_open']){
            echo '请到支付设置中开启汉特支付功能';
            die;
        }
        if ($this->_config['code']=='' || $this->_config['mch_code']=='' || $this->_config['key']==''){
            echo '请到支付设置中配置完汉特支付';
            die;
        }
        
    }
    
    // 汉特小程序支付
    public function unifiedorder($order_no,$amount,$openid,$user,$orderType,$body='',$note=''){
        $api = $this->_config['gateway'].'/v2/gateway/micropay';
        // dump($order_no);die;
        $time = time();
        $header = [
           "Accept:application/json",
           "Content-Type:application/json"
        ];
        $pricemode = SettingModel::getItem('store')['price_mode']['mode'];
        // 读取小程序信息
        $body = [
           'merchant_no' => $this->_config['mch_code'],
           'store_no'    => $this->_config['code'],
           'sign_type'   => 'MD5',
           'nonce_str'   => $this->getRandom(32),
           'time'        => $this->get_msectime(),
           'out_trade_no'=> $order_no,
           'rmb_amount'  => (int)round(($amount * 100)),
           'currency'    => 'USD',
           'payment_method'=>'wechatpay',
           'notify_url'  => base_url() . '/index.php?s=/api/page/notify&wxapp_id='.$user['wxapp_id'],
           'customer_id' => $openid,
           'appId'       => $this->appId,
           'body'        => $body?$body:'支付数据体',
           'note'        => $orderType,       
        ];
        if($pricemode==20){
          $body['amount'] = (int)round(($amount * 100));
          unset($body['rmb_amount']);
        }
        $body['signature'] = $this->signature($body);
        $result = $this->post($api,json_encode($body),$header);
        
        $prepay = ((array)json_decode($result));
        
        // 请求失败
        if ($prepay['return_code'] === 'FAIL') {
            throw new BaseException(['msg' => "汉特支付：{$prepay['return_msg']}", 'code' => -10]);
        }
        if ($prepay['result_code'] === 'FAIL') {
            throw new BaseException(['msg' => "汉特支付：{$prepay['err_code_des']}", 'code' => -10]);
        }
        if ($prepay['return_code'] == 'error') {
            throw new BaseException(['msg' => "汉特支付：{$prepay['return_msg']}", 'code' => -10]);
        }
       
        $per = explode('=',$prepay['data']->wechat_package);
        // 生成 nonce_str 供前端使用
        return [
            'prepay_id' => $per[1],
            'nonceStr' =>  $prepay['data']->nonce_str,
            'timeStamp' => $prepay['data']->time_stamp,
            'paySign' => $prepay['data']->pay_sign,
        ];
    }


 /**
     * 支付成功异步通知
     * @throws BaseException
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    public function notify()
    {
        if (!$xml = file_get_contents('php://input')) {
            $this->returnCode(false, 'Not found DATA');
        }
        $xml = explode('&',$xml);
        foreach ($xml as $key => $val){
            $ex = explode('=',$val);
            $data[$ex[0]] = $ex[1];
        }
        // 实例化订单模型
      
        $model = $this->getOrderModel($data['trade_no'],$data['note']);
        // 订单信息
        $order = $model->getOrderInfo();
        log_write($order);
        empty($order) && $this->returnCode(false, '订单不存在');
        
        
        // 订单支付成功业务处理
        $status = $model->onPaySuccess(PayTypeEnum::HANTEPAY, $data);
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
        $string = $string . '&key=' . $this->config['apikey'];
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
    private function signature($values)
    {
        //签名步骤一：按字典序排序参数
        ksort($values);
      
        $string = $this->toUrlParams($values);
        //签名步骤二：在string后加入KEY
        $string = $string . '&'.$this->_config['key'];
        //签名步骤三：MD5加密
        $string = md5($string);
        
        return $string;
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