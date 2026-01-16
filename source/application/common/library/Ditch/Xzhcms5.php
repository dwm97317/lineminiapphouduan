<?php
namespace app\common\library\Ditch;

class Xzhcms5{
    
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
        $baseurl = $this->config['apiurl'];
        $Verify =[
          'shipment_id' => "",
          'client_reference' =>$express_no ,      
        ];
        
        $header = [
            "Authorization:"."Bearer ".$this->config['token'],
            "Content-Type:application/json",
            "Accept:application/json",
        ];
       
        $data = [
            'shipment' => $Verify
        ];
        // 参数设置
        $result = $this->post($baseurl,json_encode($data),$header);
        $express = json_decode($result,true);
        // dump($express);die;
        if ($express['status']==0) {
            $this->error = isset($express['info']) ? $express['info'] : '查询失败';
            return [];
        }  
        $loglist = [];
        
        if(count($express['data']['shipment']['traces'])>0){
            foreach ($express['data']['shipment']['traces'] as $v){
                $loglist[] = [
                  'logistics_describe' => $v['info'], 
                  'status_cn' => '',
                  'created_time' =>date("Y-m-d H:i:s",$v['time']),
                ];
          }
        }
        
        // 记录错误信息
        return $loglist;
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
    
    function curlRequest($url, $header,$data = '') {
	$return = array('state' => 0, 'message' => '', 'result' => '', 'errNo' => 0);
	try {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		//设置超时时间
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);
		//API返回数据
		$apiResult = curl_exec($ch);

		$errNo = curl_errno($ch);
		if ($errNo) {
			//返回错误码
			$return['errNo'] = $errNo;
			$errorStr = curl_error($ch);
			switch ((int)$errNo) {
				case 6: //避免一直发邮件 URL报错
					break;
				case 7: //无法通过 connect() 连接至主机或代理服务器
					break;
				case 28: //超时
					break;
				case 56: //接收网络数据失败
					break;
				default:
					break;
			}
			throw new Exception($errorStr);
		}
			
		curl_close($ch);
		$return['state'] = 1;
		//返回数据
		$return['result'] = $apiResult;
	} catch (Exception $e) {
		$return['state'] = 0;
		$return['message'] = $e->getMessage();
	}
	return $return;
}

    /**
     * 返回错误信息
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
    
}