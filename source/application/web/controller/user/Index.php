<?php
/*
 * @Author: your name
 * @Date: 2022-04-27 10:21:00
 * @LastEditTime: 2022-04-27 11:07:31
 * @LastEditors: your name
 * @Description: 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 * @FilePath: \zhuanyun\source\application\web\controller\user\Index.php
 */

namespace app\web\controller\user;

use app\web\controller\Controller;
use app\web\model\User as UserModel;
use app\web\model\Order as OrderModel;
use app\web\model\Setting as SettingModel;

/**
 * 个人中心主页
 * Class Index
 * @package app\web\controller\user
 */
class Index extends Controller
{
    /**
     * 获取当前用户信息
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function detail()
    {
        // 当前用户信息
        $user = $this->getUser(false);
        // 订单总数
        $model = new OrderModel;
        return $this->renderSuccess([
            'userInfo' => $user,
            'orderCount' => [
                'payment' => $model->getCount($user, 'payment'),
                'received' => $model->getCount($user, 'received'),
                'comment' => $model->getCount($user, 'comment'),
            ],
            'setting' => [
                'points_name' => SettingModel::getPointsName(),
            ],
            'menus' => (new UserModel)->getMenus()   // 个人中心菜单列表
        ]);
    }

}
