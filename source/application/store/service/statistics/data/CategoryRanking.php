<?php

namespace app\store\service\statistics\data;

use app\common\service\Basics as BasicsService;
use app\store\model\OrderGoods as OrderGoodsModel;
use app\store\model\Category as CategoryModel;
use app\common\enum\order\Status as OrderStatusEnum;
use app\common\enum\order\PayStatus as OrderPayStatusEnum;

/**
 * 数据统计-商品销售榜
 * Class GoodsRanking
 * @package app\store\service\statistics\data
 */
class CategoryRanking extends BasicsService
{
    /**
     * 商品销售榜
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCategoryRanking()
    {
        return (new CategoryModel)->alias('ca')
            ->field(['ca.category_id','ca.name','pa.class_id','ca.category_id','pa.all_price',
                'SUM(pa.all_price) AS sales_volume',
                'COUNT(pa.class_id) AS total_sales_num',
            ])
            ->join('PackageItem pa','pa.class_id = ca.category_id')
            // ->join('inpack', 'inpack.address_id = ad.address_id')
            // ->where('inpack.is_pay', '=',1)
            // ->where('inpack.is_delete', '=',0)
            ->group('ca.category_id, ca.name')
            // order：此处按总销售额排序，如需按销量改为total_sales_num
            ->order(['total_sales_num' => 'DESC'])
            // ->limit(10)
            ->select();
    }

}