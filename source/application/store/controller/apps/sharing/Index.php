<?php
namespace app\store\controller\apps\sharp;
use app\store\controller\Controller;
use app\store\model\sharing\Setting;
/**
 * 拼单管理控制器
 * Class Active
 * @package app\store\controller\apps\sharing
 */
class Index extends Controller
{
      // 拼团设置
      public function setting(){
          $detail = Setting::getSetting();
          if (!$this->request->isAjax()){
              return $this->fetch('setting',compact('details'));
          }
          $data = $this->postData('sharp');
      }
}