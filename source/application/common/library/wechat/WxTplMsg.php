<?php

namespace app\common\library\wechat;

/**
 * 微信模板消息
 * Class WxTplMsg
 * @package app\common\library\wechat
 */
class WxTplMsg extends WxBase
{
    /**
     * 发送模板消息
     * @param array $param
     * @return bool
     * @throws \app\common\exception\BaseException
     */
    public function sendWxTemplateMessage($param)
    {
        // 微信接口url
        $accessToken = $this->getAccessTokenForWxOpen();
        // $accessToken = $this->getStableAccessToken();
        //   dump($accessToken);die;
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$accessToken}";
        // 构建请求
        $params = [
            'touser' => $param['touser'],
            'template_id' => $param['template_id'],
            'appid' => $this->appWxappid,
            'url' => $param['url'],
            'miniprogram' => [
                'appid' => $this->appId,
                'pagepath' =>$param['miniprogram']['pagepath']
            ],
            'data' =>$this->createData($param['data']),
        ];
        $result = $this->post($url, $this->jsonEncode($params));
      
        // 记录日志
        $this->doLogs(['describe' => '发送模板消息', 'url' => $url, 'params' => $params, 'result' => $result]);
        // 返回结果
        $response = $this->jsonDecode($result);
        if (!isset($response['errcode'])) {
            $this->error = 'not found errcode';
            return false;
        }
        if ($response['errcode'] != 0) {
            $this->error = $response['errmsg'];
            return false;
        }
        return true;
    }
    
     /**
     * 发送模板消息,H5方案；
     * @param array $param
     * @return bool
     * @throws \app\common\exception\BaseException
     */
    public function sendWxTemplateMessageForH5($param)
    {
        // 微信接口url
        // dump($param);die;
        $accessToken = $this->getAccessTokenForH5();
        // $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/uniform_send?access_token={$accessToken}";
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$accessToken}";
        // 构建请求
        $params = [
            'touser' => $param['touser'],
            'template_id' => $param['template_id'],
            'data' =>$this->createData($param['data']),
        ];
         
        $result = $this->post($url, $this->jsonEncode($params));

        // 记录日志
        $this->doLogs(['describe' => '发送模板消息', 'url' => $url, 'params' => $params, 'result' => $result]);
        // 返回结果
        $response = $this->jsonDecode($result);
        if (!isset($response['errcode'])) {
            $this->error = 'not found errcode';
            return false;
        }
        if ($response['errcode'] != 0) {
            $this->error = $response['errmsg'];
            return false;
        }
        return true;
    }

    /**
     * 生成关键字数据
     * @param $data
     * @return array
     */
    private function createData($data)
    {
        $params = [];
      
        foreach ($data as $key => $value) {

            $params[$key] = [
                'value' => $value['value'],
                // 'color' => '#333333'
            ];
        }
        return $params;
    }

}