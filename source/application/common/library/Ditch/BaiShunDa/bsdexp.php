<?php
namespace app\common\library\Ditch\BaiShunDa;
use think\Cache;

class bsdexp{
    
    private $config;
    /* @var string $error 错误信息 */
    private $error;

    /**
     * 构造方法
     * WxPay constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * 执行查询
     * @param $express_code
     * @param $express_no
     * @return bool
     */
    public function query($express_no)
    {
            
        // 缓存索引
        $baseurl = 'http://api.bsdexp.com/';
        // 参数设置
        $header = [
            "Content-Type:application/json",
            "Token:{$this->config['token']}"
        ];
        // dump($express_no);die;
        $data = [
            'num'  => $express_no
        ];
        // 请求快递api
        $url = $baseurl.'/api/Express/Order/Tracking';
        $result = $this->post($url, json_encode($data),$header);
//   dump($result);die;
        $express = json_decode($result, true);
      
        if($express['Ack']){
            foreach ($express['data']['events'] as $v){
                $loglist[] = [
                  'logistics_describe' => $v['description'], 
                  'status_cn' => $v['location'],
                  'created_time' =>$v['datetime'],
                ];
          }
        }
        // 记录错误信息
   
        if ( !$express['Ack']) {
            $this->error = isset($express['Errors']['Message']) ? $express['Errors']['Message'] : '查询失败';
            return false;
        }
       
        return $loglist;
    }

    /**
     * 返回错误信息
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
    
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
    
}