<?php
namespace app\common\library\Ditch;
use think\Cache;
class Yidida{
    
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
        $tokenurl = $this->config['apiurl']."itdida-api/login";
        
        //获取token的接口
        // https://yc.itdida.com/itdida-api/login
         if (!$ntoken = Cache::get('Yidida_' . $this->config['key'])) {
            $header = [
                "Authorization:"."Bearer ".$this->config['token'],
            ];
            $data = ['password'=>$this->config['token'],'username'=>$this->config['key']];
            $newtoken = $this->post($tokenurl,$data,$header);
            $newtoken = json_decode($newtoken,true);
            if($newtoken['statusCode']==200){
                $ntoken = $newtoken['data'];
            }
            Cache::tag('cache')->set('Yidida_' . $this->config['key'], $ntoken);
        }
        $queryPieceDetail = $this->config['apiurl']."itdida-api/queryTracks";
        $header = ["Authorization:"."Bearer ".$ntoken];
        $danhaos = ['no'=>$express_no,'isTime'=>true];
        // 参数设置
        $result = curl($queryPieceDetail,$danhaos);
        $express = json_decode($result,true);
   
        if ($express['statusCode']!=200) {
            $this->error = isset($express['data'][0]['errorMsg']) ? $express['data'][0]['errorMsg'] : '查询失败';
            return [];
        }  
        
        $loglist = [];
        // dump($express['data'][0]['trackList']);die;
        if(count($express['data'][0]['trackList'])>0){
            foreach ($express['data'][0]['trackList'] as $v){
                $loglist[] = [
                  'logistics_describe' => $v['desc'], 
                  'status_cn' => $v['localtion'],
                  'created_time' =>$v['time'],
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
    
    function curlRequest($url, $data = '',$header=[]) {
	$return = array('state' => 0, 'message' => '', 'result' => '', 'errNo' => 0);
	try {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
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