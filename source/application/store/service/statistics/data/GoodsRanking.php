<?php

namespace app\store\service\statistics\data;

use app\common\service\Basics as BasicsService;
use app\store\model\OrderGoods as OrderGoodsModel;
use app\store\model\Line as LineModel;
use app\common\enum\order\Status as OrderStatusEnum;
use app\common\enum\order\PayStatus as OrderPayStatusEnum;

/**
 * 数据统计-商品销售榜
 * Class GoodsRanking
 * @package app\store\service\statistics\data
 */
class GoodsRanking extends BasicsService
{
    /**
     * 商品销售榜
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsRanking()
    {
        return (new LineModel)->alias('line')
            ->field(['line.id','line.name','inpack.real_payment',
                'SUM(real_payment) AS sales_volume',
                'COUNT(line_id) AS total_sales_num',
            ])
            ->join('inpack', 'inpack.line_id = line.id')
            ->where('inpack.is_pay', '=',1)
            ->where('inpack.is_delete', '=',0)
            ->group('line.id, line.name')
            // order：此处按总销售额排序，如需按销量改为total_sales_num
            ->order(['sales_volume' => 'DESC'])
            ->limit(10)
            ->select();
    }

}