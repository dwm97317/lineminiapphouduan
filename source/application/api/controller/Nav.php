<?php

namespace app\api\controller;

use app\api\model\WxappNavLink;


/**
 * 导航列表
 * Class nav
 * @package app\api\controller
 */
class Nav extends Controller
{
    /**
     * 导航列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists()
    {
        $model = new WxappNavLink;
        $list = $model->getList();
        foreach ($list as $k =>$v){
            $list[$k]['nav_link'] = html_entity_decode($v['nav_link']);
        }
        return $this->renderSuccess(compact('list'));
    }

}