<?php
namespace app\common\library\Ditch;

class Hualei{
    
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
        $baseurl = $this->config['apiurl'].$express_no;
        // 参数设置
        $result = $this->curlRequest($baseurl, '');
          
        //  dump($baseurl);die;    
         
        if ($result['state']==0) {
            $this->error = isset($express['cnmessage']) ? $express['cnmessage'] : '查询失败';
            return [];
        }  
        $express = json_decode($result['result'],true);
        $loglist = [];
        //  dump($express);die;    
        if($express['0']['ack']=='true'){
            foreach ($express[0]['data'][0]['trackDetails'] as $v){
                $loglist[] = [
                  'logistics_describe' => $v['track_content'], 
                  'status_cn' => $v['business_id'],
                  'created_time' =>$v['track_date'],
                ];
          }
        }
        
        // 记录错误信息
        return $loglist;
    }
    
    function curlRequest($url, $data = '') {
	$return = array('state' => 0, 'message' => '', 'result' => '', 'errNo' => 0);
	try {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
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