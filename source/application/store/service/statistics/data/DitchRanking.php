<?php

namespace app\store\service\statistics\data;

use app\common\service\Basics as BasicsService;
use app\store\model\OrderGoods as OrderGoodsModel;
use app\store\model\Ditch as DitchModel;
use app\store\model\Inpack as InpackModel;
use app\common\enum\order\Status as OrderStatusEnum;
use app\common\enum\order\PayStatus as OrderPayStatusEnum;

/**
 * 数据统计-商品销售榜
 * Class GoodsRanking
 * @package app\store\service\statistics\data
 */
class DitchRanking extends BasicsService
{
    /**
     * 商品销售榜
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrderDitchRanking()
    {
        $InpackModel = new InpackModel;
        return (new DitchModel)->alias('co')
            ->field(['co.ditch_id','co.ditch_name','inpack.real_payment',
                'SUM(inpack.real_payment) AS sales_volume',
                'COUNT(co.ditch_id) AS total_sales_num',
            ])
            ->join('inpack', 'inpack.t_number = co.ditch_id')
            // ->where('inpack.is_pay', '=',1)
            ->where('inpack.is_delete', '=',0)
            ->group('co.ditch_id, co.ditch_name')
            // order：此处按总销售额排序，如需按销量改为total_sales_num
            ->order(['sales_volume' => 'DESC'])
            // ->limit(10)
            ->select()->each(function($item,$key) use($InpackModel){
                $item['total_exced'] = $InpackModel->where('t_number',$item['ditch_id'])->where('is_exceed',1)->where('is_delete',0)->count(); //超时订单
                $item['exced_ratio'] = number_format($item['total_exced']/$item['total_sales_num'],4)*100 .'%';
            });
    }

}