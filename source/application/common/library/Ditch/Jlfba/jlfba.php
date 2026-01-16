<?php
namespace app\common\library\Ditch\Jlfba;

class jlfba{
    
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
        $baseurl = 'http://ywjl.rtb56.com/webservice/PublicService.asmx/ServiceInterfaceUTF8';
        // 参数设置
        $postData = [
            'appToken' => $this->config['token'],
            'appKey' => $this->config['key'],
            'serviceMethod' => 'gettrack',
            'paramsJson' => json_encode(['tracking_number'=>$express_no])
        ];
        $result = curlPost($baseurl, http_build_query($postData));
        $express = json_decode($result, true);
                // dump($express);die; 
        if ($express['success']==0) {
            $this->error = isset($express['cnmessage']) ? $express['cnmessage'] : '查询失败';
            return [];
        }         
        if($express['data'][0]['details']){
            foreach ($express['data'][0]['details'] as $v){
                $loglist[] = [
                  'logistics_describe' => $v['track_description'], 
                  'status_cn' => $v['track_location'],
                  'created_time' =>$v['track_occur_date'],
                ];
          }
        }
        // 记录错误信息

        

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
    
}