<?php
namespace app\common\library\http;

class Curl {
    
       //发送一个常规的Get请求
    public static function get($url,$params=[],$headers = [],$cookieFile=''){
        $default_header = [
            'User-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.88 Safari/537.36',
        ];
        if ($headers){
            $headers = array_merge($default_header,$headers);
        }
        // 防止CURL执行超时
        set_time_limit(0);

        if (!empty($params)){
            $url = $url.'?'.http_build_query($params);
        }

        // 初始化一个新会话
        $ch = curl_init();

        // 设置要求请的url
        curl_setopt($ch, CURLOPT_URL, $url);

        // 是否验证SSL证书
        // 一般不验证 ( 默认为验证 需设置fasle关闭 )
        // 如果设置false报错 尝试改为 0
        // 某些CURL版本不只true和fasle两种状态 可能是0,1,2...等
        // 如果选择验证证书 将参数设置为ture或1
        // 然后在使用CURLOPT_CAPATH和CURLOPT_CAINFO设置证书信息
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        // 验证证书中域名是否与当前域名匹配 和上面参数配合使用
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_ENCODING,'gzip,deflate,br');
        curl_setopt($ch, CURLOPT_COOKIE,$cookieFile);
        // 是否将数据已返回值形式返回
        // 1 返回数据
        // 0 直接输出数据 帮你写了: echo $output;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //模拟的header头
        // 执行CURL请求
        $output = curl_exec($ch);
        // 关闭CURL资源
        curl_close($ch);

        // 输出返回信息
        return $output;
    }

    // 发送一个常规的Post请求
    public static function post($url, $post_data = array(), $header = "", $timeout = 5, $data_type = "json",$is_cert=false){
        $default_header = [
            'User-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.88 Safari/537.36',
        ];
        if ($header){
            $header = array_merge($default_header,$header);
        }
        //支持json数据数据提交
        // if($data_type == 'json'){
        //     $post_string = json_encode($post_data);
        // }elseif($data_type == 'array') {
        //     $post_string = $post_data;
        // }elseif(is_array($post_data)){
        //     $post_string = http_build_query($post_data, '', '&');
        // }
        $ch = curl_init();    // 启动一个CURL会话
        if ($is_cert){

            curl_setopt($ch, CURLOPT_SSLCERT, './cert/apiclient_cert.pem');//证书位置
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');//CURLOPT_SSLKEY中规定的私钥的加密类型
            curl_setopt($ch, CURLOPT_SSLKEY, './cert/apiclient_key.pem');//证书位置
        }

        curl_setopt($ch, CURLOPT_URL, $url);     // 要访问的地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // 对认证证书来源的检查   // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
        if (isset($_SERVER['HTTP_USER_AGENT']))
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        //curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($ch, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);     // Post提交的数据包
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);     // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     // 获取的信息以文件流的形式返回
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //模拟的header头
        $result = curl_exec($ch);
        // 打印请求的header信息
        //$a = curl_getinfo($ch);
        //var_dump($a);
        curl_close($ch);
        return $result;
    }
}