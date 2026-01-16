<?php
namespace app\common\library;
/**
 * Created by PhpStorm.
 * User: jifei
 * Date: 15/6/25
 * Time: 下午2:26
 */
class Analysis
{
    
    private static $app = null;
    private static $resource_url = null;
    private static $response = null;
    /** 
     * 支持解析链接
     */
    private static $support = [
       'detail.tmall.com',
       'item.taobao.com',
       'item.jd.com',
       're.1688.com',
    ];
    
    private static $cookie = [
       'tmall' => 'cna=wH27F8+y6B0CAatx//fbaQTH; lid=shepen1991; miid=485238281247407163; cq=ccp%3D0; hng=CN%7Czh-CN%7CCNY%7C156; dnk=shepen1991; uc1=cookie21=VFC%2FuZ9aiKIc&pas=0&cookie16=Vq8l%2BKCLySLZMFWHxqs8fwqnEw%3D%3D&existShop=false&cookie15=U%2BGCWk%2F75gdr5Q%3D%3D&cookie14=UoewAwrSrRM1ng%3D%3D; uc3=nk2=EFYxzFZcID6Dqw%3D%3D&vt3=F8dCvU16MC4CRvUmRy0%3D&lg2=W5iHLLyFOGW7aA%3D%3D&id2=UU6jW71KDeq5mA%3D%3D; tracknick=shepen1991; uc4=id4=0%40U2xuAXhNcSACzoh8f4F5ERnW0Deg&nk4=0%40Eo9P5P6bBZm4GX2XQU2OjcI6qZPE; lgc=shepen1991; cookie2=1ce29624a50da08d5d7bbe9e5e886612; sgcookie=E100GA5nQe5tg6z10IbwnVKdFx3mqaIqW1hrbTZX7foXZvUpa4NIhY8lUIG7812bCp9zFtMoTWA1SPl4VEiNgCcLg4f3fZhZ6ZBS2fFiwI0R1EGXCjuaHjPosFaZ9boP7sOp; cancelledSubSites=empty; t=701e1454d26740b915c9c425f17913f0; csg=0d3f1bf2; enc=q8p8WY4gl%2BuHmk1etatF2PappTQ5PkWmQoEwbbU8Nw9kTR7yYoj3TpLd0IgHIlanBe7%2FylQ%2F9lIQEbh%2FjwucLA%3D%3D; _tb_token_=75ba3ee7ae533; xlly_s=1; pnm_cku822=098%23E1hv%2FQvUvbpvUpCkvvvvvjiWR2Mhzjlbn25vtjD2PmPh6jEhPLswsjrRR2zO6jiWRL9CvvpvvhCvvvhvC9mvphvvvb9Cvm9vvhCvvvvvvvvvBGwvvUjZvvCj1Qvvv3QvvhNjvvvmmvvvBGwvvvUUuvhvmvvvpLYo0sJokvhvC9hvpyPyzv9CvhAvx0DijfmAdXk4jomxfXkwd3wAxYexRfeAHVDHD70OV8TJEcqhl8gcnfwiBTmUhXp7%2B3%2Butj7J%2Bu0OaAirD40OV8gaWXxre4TJ%2B3%2Bu29hvCPMMvvmevpvhvvCCBv%3D%3D; tfstk=cdk5BRxajab5TvOFU0tVYP_-OwehZOS_AQaoVXc98yfy-ri5i5Wa5U-itweUvo1..; l=eBag6udeqZT3Rs22BOfZourza779jIRAguPzaNbMiOCP9ZfeWUUNW6Lx3oYwCnGVh6hJR3lFyc9kBeYBqIY75O95a6Fy_Ckmn; isg=BGpqxJUM_9XyIU8N9b7W0bqeu9AM2-41k-VkHfQjFb1EJwrh3GhPRLtVs1M712bN',    
    ];
    
    public $pro = [
        'title' => '',
        'marker_price',
        'price',
        'describe',
        'spec',
    ];
     
    // 检查是否支持解析 
    public  function check($url){
        $urls = parse_url($url);
        if (!in_array($urls['host'],self::$support)){
            self::$error = '暂不支持该url链接';
            return false;
        }
        
        $s = explode('.',$urls['host']);
        self::$app = $s[1];
        self::$resource_url = $url;
        return true;
    }
    
    public static function getSSLPage($url,$type='get',$arr = '') {
        $ch = curl_init();
        $headers = array(
            "cache-control: no-cache",
            "Referer:www.baidu.com"
        );
        $user_agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.100 Safari/537.36";
        curl_setopt($ch, CURLOPT_COOKIE,self::$cookie[self::$app]);
        curl_setopt($ch, CURLOPT_URL, $url); //设置访问的地址
        curl_setopt($ch, CURLOPT_USERAGENT,$user_agent);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch, CURLOPT_HEADER, 1 );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //获取的信息返回
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, false); // 启用时会将头文件的信息作为数据流输出。
        curl_setopt($ch, CURLOPT_NOBODY, false); // 启用时将不对HTML中的BODY部分进行输出。
        curl_setopt($ch, CURLINFO_HEADER_OUT, true); // 启用时追踪句柄的请求字符串。
        curl_setopt($ch, CURLOPT_ENCODING, "");
        if ($type == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
        }
        $output = curl_exec($ch); 
        $meta = curl_getinfo($ch);
        $request_header = $meta['request_header'];
        file_put_contents('_html.txt',$output);
        if (curl_error($ch)) {
            return curl_error($ch);
        }
        return $output;
    }
    
    // 打开天猫详情页
    public function getTmallPage(){
        
    }
    
    // 检测页面是否可访问
    public static function initCheck(){
        
        self::$response = self::getSSLPage(self::$resource_url);
        dump(self::$response); die;
        if(self::$response){
           return true;    
        }
        return false;
    }
    
    // 开始解析
    public static function init(){
        if(!self::initCheck()){
           self::$error = '页面无法访问,请确认链接是否能打开';  
           return false;   
        }
        
        dump(self::$response);
    }
}