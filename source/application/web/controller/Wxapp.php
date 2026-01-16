<?php
namespace app\web\controller;

use app\web\model\Wxapp as WxappModel;
use app\web\model\WxappHelp;

/**
 * 微信小程序
 * Class Wxapp
 * @package app\web\controller
 */
class Wxapp extends Controller
{
    /**
     * 小程序基础信息
     * @return array
     */
    public function base()
    {
//        $wxapp = WxappModel::getWxappCache();
        return $this->renderSuccess([]);
    }

    /**
     * 帮助中心
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function help()
    {
        $model = new WxappHelp;
        $list = $model->getList();
        return $this->renderSuccess(compact('list'));
    }

}
