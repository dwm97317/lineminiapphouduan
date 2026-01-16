<?php
namespace app\common\library\Ditch;

class Aolian{
    
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
          'code' => $this->config['key'],
          'token' => $this->config['token'],      
        ];
      
        $data = [
            'authorization' => $Verify,
            'datas'=> ['waybillnumber'=> [$express_no]]
        ];
        //   dump(json_encode($data));die; 
        // 参数设置
        $result = $this->http_post($baseurl,json_encode($data));
        $express = json_decode($result,true);
        // dump($express);die;
        if ($express['code']==0) {
            $this->error = isset($express['data'][0]['errormsg']) ? $express['data'][0]['errormsg'] : '查询失败';
            return [];
        }  
        $loglist = [];
   
        if(count($express['data'][0]['trackItems'])>0){
            foreach ($express['data'][0]['trackItems'] as $v){
                $loglist[] = [
                  'logistics_describe' => $v['info'], 
                  'status_cn' => $v['location'],
                  'created_time' =>$v['trackdate'],
                ];
          }
        }
        
        // 记录错误信息
        return $loglist;
    }
    
function http_post($url, $data_string) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-AjaxPro-Method:ShowList',
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data_string))
    );
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

    
    function curlRequest($url,$data = '') {
	$return = array('state' => 0, 'message' => '', 'result' => '', 'errNo' => 0);
	try {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER,array(
			'Accept-Language: zh-cn',
			'Connection: Keep-Alive',
			'Cache-Control: no-cache',
			'Content-type: application/x-www-form-urlencoded;charset=UTF-8'));
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