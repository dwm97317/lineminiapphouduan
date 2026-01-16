<?php
namespace app\common\library\Ditch;

class kingtrans{
    
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
        // $baseurl = 'http://hx155.kingtrans.cn/PostInterfaceService?method=searchTrack';
        $baseurl = $this->config['apiurl'];
        // 参数设置
        $Verify =[
          'Clientid' => $this->config['key'],
          'Token' => $this->config['token'],      
        ];
        $Datas = [
            'TrackNumber' => $express_no
        ];
        
        $data = [
            'Verify' => $Verify,
            'Datas' => [$Datas]
        ];


        $result = $this->curlPost($baseurl,json_encode($data));
        //   dump($result);die;    
        $express = json_decode($result, true);
                //  dump($express);die;    
        if ($express['statusCode']=='error') {
            $this->error = isset($express['cnmessage']) ? $express['cnmessage'] : '查询失败';
            return [];
        }         
        if($express['returnDatas'][0]['items']){
            foreach ($express['returnDatas'][0]['items'] as $v){
                $loglist[] = [
                  'logistics_describe' => $v['info'], 
                  'status_cn' => $v['location'],
                  'created_time' =>$v['dateTime'],
                ];
          }
        }
        // 记录错误信息

        

        return $loglist;
    }

/**
 * curl请求指定url (post)
 * @param $url
 * @param array $data
 * @return mixed
 */
protected function curlPost($url,$data)
  {
    $ch = curl_init($url);
 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
 
    return $result;
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