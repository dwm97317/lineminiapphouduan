<?php
namespace app\common\library\payment\HantePay;
// 默认参数配置

class config {
    
    protected $appId;
    protected $appSecret;
    protected $appWxappid;
    protected $error;
    
    public function _default(){
        return $config = [
            'gateway'  =>'https://gateway.hantepay.com',
            'key'      =>'',
            'code'     =>'',
            'mch_code' => '',
        ];
    }
    
    /**
     * 构造函数
     * WxBase constructor.
     * @param $appId
     * @param $appSecret
     */
    public function __construct($appId = null, $appSecret = null,$appWxappid =null)
    {
        $this->setConfig($appId, $appSecret,$appWxappid);
    }
    
    protected function setConfig($appId = null, $appSecret = null,$appWxappid=null)
    {
        !empty($appId) && $this->appId = $appId;
        !empty($appSecret) && $this->appSecret = $appSecret;
        !empty($appWxappid) && $this->appWxappid = $appWxappid;
    }
    
}