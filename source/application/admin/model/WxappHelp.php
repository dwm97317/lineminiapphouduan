<?php

namespace app\admin\model;

use app\common\model\WxappHelp as WxappHelpModel;

/**
 * 小程序帮助中心
 * Class WxappHelp
 * @package app\admin\model
 */
class WxappHelp extends WxappHelpModel
{
    /**
     * 新增默认帮助
     * @param $wxapp_id
     * @return false|int
     */
    public function insertDefault($wxapp_id)
    {
        return $this->save([
            'title' => '什么是集运？',
            'content' => '集运，顾名思义就是多个包裹集中进行运输的过程。具体通俗一点就是，不管你在哪个购物平台或者厂家，哪个时间段哪个地方采买，可以让货运公司统一发往国内的集运仓地址，然后由仓库人员把你购买的商品打包成一个包裹，将东西发往你所在的国家，这样可以最大化的降低运输成本。',
            'sort' => 100,
            'wxapp_id' => $wxapp_id
        ]);
    }

}
