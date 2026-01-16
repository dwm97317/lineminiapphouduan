<?php

namespace app\web\controller;

use app\web\model\User as UserModel;
use app\web\model\Wxapp as WxappModel;
use app\common\exception\BaseException;
use app\common\model\UploadFile;
use app\web\model\Setting;
use think\Session;
use app\web\model\SiteSms;

/**
 * API控制器基类
 * Class BaseController
 * @package app\store\controller
 */
class Controller extends \think\Controller
{
    const JSON_SUCCESS_STATUS = 1;
    const JSON_ERROR_STATUS = 0;

    /* @ver $wxapp_id 小程序id */
    protected $wxapp_id;
    
    protected $routeUri = '';
    /** @var array $store 用户登录信息 */
    protected $user;
    /** @var array $allowAllAction 登录验证白名单 */
    protected $allowAllAction = [
        // 登录页面
        'passport/login',
        'track/search'
    ];
    /* @var array $notLayoutAction 无需全局layout */
    protected $notLayoutAction = [
        // 登录页面
        'passport/login',
        'track/search'
    ];
    /**
     * API基类初始化
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        $this->user = Session::get('yoshop_user');
        // 当前小程序id
      
        $wxapp_id_en = str_replace(' ','+',$this->request->get('wxappid'));
        //  dump(encrypt($wxapp_id_en,'D'));die;
        if (!$wxapp_id = encrypt($wxapp_id_en,'D')){
             throw new BaseException(['msg' => '无效wxapp_id']);   
        }
        // dump($wxapp_id);die;
        $this->wxapp_id = $wxapp_id;
        $this->getRouteinfo();
        // 验证当前小程序状态
        $this->checkWxapp();
        // 判断当前控制器 是否是login
        $controller=request()->controller();
        // dump($this->notLayoutAction);die;
        if (!in_array($this->routeUri, $this->notLayoutAction)){
           $this->checkLogin();
        }
        $this->layout();
    }
    
    /**
     * 解析当前路由参数 （分组名称、控制器名称、方法名）
     */
    protected function getRouteinfo()
    {
        // 控制器名称
        $this->controller = toUnderScore($this->request->controller());
        // 方法名称
        $this->action = $this->request->action();
        // 控制器分组 (用于定义所属模块)
        $groupstr = strstr($this->controller, '.', true);
        $this->group = $groupstr !== false ? $groupstr : $this->controller;
        // 当前uri
        $this->routeUri = $this->controller . '/' . $this->action;
    }


    /**
     * 获取当前小程序ID
     * @return mixed
     * @throws BaseException
     */
    private function getWxappId()
    {
        if (!$wxapp_id = $this->request->param('wxapp_id')) {
            throw new BaseException(['msg' => '缺少必要的参数：wxapp_id']);
        }
        return $wxapp_id;
    }
    
     /**
     * 验证登录状态
     * @return bool
     */
    private function checkLogin()
    {
        // 验证当前请求是否在白名单
        if (in_array($this->routeUri, $this->allowAllAction)) {
            return true;
        }
        // 验证登录状态
        if (empty($this->user)
            || (int)$this->user['is_login'] !== 1
        ) {
            $this->redirect(urlCreate('/index.php/web/passport/login'));
            return false;
        }
        return true;
    }

    /**
     * 验证当前小程序状态
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    private function checkWxapp()
    {
        $wxapp = WxappModel::find($this->wxapp_id);
        if (empty($wxapp)) {
            throw new BaseException(['msg' => '当前小程序信息不存在']);
        }
        if ($wxapp['is_recycle'] || $wxapp['is_delete']) {
            throw new BaseException(['msg' => '当前小程序已删除']);
        }
    }
    
    public function layout(){
         // 读取菜单
         $values = Setting::getItem('store');
         $menu = $this->getMenu();
         $SiteSms = new SiteSms;
         $list = $SiteSms->getList(['member_id'=>$this->user['user']['user_id'],'is_read'=>0]);
        //  dump($list);die;
         $current_path = '/web/'.strtolower($this->request->controller()).'/'.$this->request->action();
         $ctr = $this->getParent($current_path,$menu);
         $this->assign([
                'menu' => $menu,
                '__TEMPLATE__' => APP_PATH.'web/view/', // 当前指向的公共模板
                'store_url' => url('/web'),              // 后台模块url
                'user' => $this->user,
                'ctr_path' => $current_path,
                'ctr' =>  $menu[$ctr]['parent'],
                'storetitle' => $values['name'],
                'message'=> $list
            ]);
    }
    
    public function getParent($current_path,$menu){
        $_index = 0;
        foreach ($menu as $k => $v){
            $url = substr($v['url'],0,strpos($v['url'],'?'));
            if (isset($v['child']) && $v['child']){
                foreach ($v['child'] as $val){
                     $url = substr($val['url'],0,strpos($val['url'],'?'));
                     if ($current_path==$url){
                         $_index = $k;
                     }
                }
            }else{
                if ($url==$current_path){
                    $_index = $k;
                }
            }
        }
        return $_index;
    }
    
    public function getMenu(){
        $file_config = APP_PATH.'/web/menu.php';
        $menu = include($file_config);
        return $menu;
    }

    public function withImageById($data,$field,$name=null){
      $image = $name?$name:'image';
      if (isset($data[0][$field])){
          foreach ($data as $k => $v){
              if ($v[$field]){
                  $res = UploadFile::getFileName($v[$field]);
                  if ($res)
                      $data[$k][$image] = $res;
              }   
          }
          return $data;
      }else{
         $res = UploadFile::getFileName($data[$field]);
         if ($res) {
            $data[$image] = $res;
         }
         return $data;
      }  
    }
    
    /**
     * 获取当前用户信息
     * @param bool $is_force
     * @return UserModel|bool|null
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    protected function getUser($is_force = true)
    {
        if (!$token = $this->request->param('token')) {
            $is_force && $this->throwError('缺少必要的参数：token', -1);
            return false;
        }
        if (!$user = UserModel::getUser($token)) {
            $is_force && $this->throwError('没有找到用户信息', -1);
            return false;
        }
        return $user;
    }

    /**
     * 输出错误信息
     * @param int $code
     * @param $msg
     * @throws BaseException
     */
    protected function throwError($msg, $code = 0)
    {
        throw new BaseException(['code' => $code, 'msg' => $msg]);
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return array
     */
    protected function renderJson($code = self::JSON_SUCCESS_STATUS, $msg = '', $data = [])
    {
        return compact('code', 'msg', 'data');
    }

    /**
     * 返回操作成功json
     * @param array $data
     * @param string|array $msg
     * @return array
     */
    protected function renderSuccess($data = [], $msg = 'success')
    {
        return $this->renderJson(self::JSON_SUCCESS_STATUS, $msg, $data);
    }

    /**
     * 返回操作失败json
     * @param string $msg
     * @param array $data
     * @return array
     */
    protected function renderError($msg = 'error', $data = [])
    {
        return $this->renderJson(self::JSON_ERROR_STATUS, $msg, $data);
    }

    /**
     * 获取post数据 (数组)
     * @param $key
     * @return mixed
     */
    protected function postData($key = null)
    {
        return $this->request->post(is_null($key) ? '' : $key . '/a');
    }

}
