<?php

namespace app\api\controller;

use app\api\model\SiteSms as SiteSmsModel;

/**
 * 站内信控制器
 * Class Sitesms
 * @package app\api\controller
 */
class Sitesms extends Controller
{
    /**
     * 获取站内信列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists()
    {
        $user = $this->getUser();
        $model = new SiteSmsModel;
        $list = $model->getList([
            'member_id' => $user['user_id']
        ]);
        
        return $this->renderSuccess('', '', compact('list'));
    }

    /**
     * 获取站内信详情
     * @return array
     * @throws \think\exception\DbException
     */
    public function detail()
    {
        $user = $this->getUser();
        $id = $this->request->param('id');
        
        if (empty($id)) {
            return $this->renderError('参数错误');
        }
        
        $model = new SiteSmsModel;
        $detail = $model->details($id);
        
        if (!$detail || $detail['user_id'] != $user['user_id']) {
            return $this->renderError('消息不存在');
        }
        
        return $this->renderSuccess('', '', compact('detail'));
    }

    /**
     * 标记单条消息为已读
     * @return array
     */
    public function read()
    {
        $user = $this->getUser();
        $id = $this->request->param('id');
        
        if (empty($id)) {
            return $this->renderError('参数错误');
        }
        
        $model = new SiteSmsModel;
        $message = $model->where([
            'id' => $id,
            'user_id' => $user['user_id']
        ])->find();
        
        if (!$message) {
            return $this->renderError('消息不存在');
        }
        
        $message->save([
            'is_read' => 1,
            'updated_time' => date('Y-m-d H:i:s')
        ]);
        
        return $this->renderSuccess('标记成功');
    }

    /**
     * 标记全部消息为已读
     * @return array
     */
    public function readAll()
    {
        $user = $this->getUser();
        $model = new SiteSmsModel;
        $model->where([
            'user_id' => $user['user_id'],
            'is_read' => 0
        ])->update([
            'is_read' => 1,
            'updated_time' => date('Y-m-d H:i:s')
        ]);
        
        return $this->renderSuccess('全部标记成功');
    }

    /**
     * 获取未读消息数量
     * @return array
     */
    public function unreadCount()
    {
        $user = $this->getUser();
        $model = new SiteSmsModel;
        $count = $model->where([
            'user_id' => $user['user_id'],
            'is_read' => 0
        ])->count();
        
        return $this->renderSuccess('', '', ['count' => $count]);
    }
}
