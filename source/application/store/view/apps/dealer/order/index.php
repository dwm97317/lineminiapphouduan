<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">分销订单</div>
                </div>
                <?php $status = [-1=>'问题件',1=>'未入库',2=>'已入库',3=>'已拣货上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成']; ?>
                <?php $orderstatus = [''=>'',1=>'待查验',2=>'待发货',3=>'待发货','4'=>'待发货','5'=>'待发货','6'=>'已发货','7'=>'已到货','8'=>'已完成','-1'=>'问题件']; ?>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <input type="hidden" name="user_id" value="<?= $request->get('user_id') ?>">
                            <div class="am-u-sm-12 am-u-md-9 am-u-sm-push-3">
                                <div class="am fr">
                                    <div class="am-form-group am-fl">
                                        <select name="is_settled"
                                                data-am-selected="{btnSize: 'sm', placeholder: '是否结算佣金'}">
                                            <option value=""></option>
                                            <option value="-1" <?= $request->get('is_settled') == '-1' ? 'selected' : '' ?>>
                                                全部
                                            </option>
                                            <option value="0" <?= $request->get('is_settled') === '0' ? 'selected' : '' ?>>
                                                未结算
                                            </option>
                                            <option value="1" <?= $request->get('is_settled') == '1' ? 'selected' : '' ?>>
                                                已结算
                                            </option>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl" style="width: 80px;">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="order-list am-scrollable-horizontal am-u-sm-12 am-margin-top-xs">
                        <table width="100%" class="am-table am-table-centered
                        am-text-nowrap am-margin-bottom-xs">
                            <thead>
                            <tr>
                                <th width="30%" class="goods-detail">订单信息</th>
                                <th>买家</th>
                                <th>交易状态</th>
                                <th>佣金结算</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list->toArray()['data'] as $order): ?>
                                <tr class="order-empty">
                                    <td colspan="6"></td>
                                </tr>
                                <tr>
                                    <td class="am-text-middle am-text-left" colspan="6">
                                        <span class="am-margin-right-lg">
                                            <a href="<?= url('store/trOrder/orderdetail', ['id' => $order['order_id']]) ?>">
                                                集运单号：<?= $order['inpack']['order_sn'] ?></a>
                                            </span>
                                        <span class="am-margin-right-lg"> <?= $order['create_time'] ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="am-text-middle am-text-left" colspan="1">
                                        <span class="am-margin-right-lg">实际支付：<?= $order['inpack']['real_payment'] ?></span>
                                    </td> 
                                    <td class="am-text-middle am-text-left" colspan="1">
                                        <span class="am-margin-right-lg">订单用户编号： <?= $order['inpack']['user']['user_code'] ?></span>
                                    </td>
                                    
                                    <td class="am-text-middle am-text-left" colspan="1">
                                        <span class="am-margin-right-lg">订单状态： <?=  $orderstatus[$order['inpack']['status']] ; ?></span>
                                        
                                    </td> 
                                    <td class="am-text-middle am-text-left" colspan="1">
                                        <span class="am-margin-right-lg">是否结算： <?= $order['is_settled']==1?'已结算':'未结算' ?></span>
                                    </td> 
                                </tr>
                                <tr>
                                    <td class="am-text-middle am-text-left" colspan="6">
                                        <div class="dealer am-cf">
                                            <?php if ($order['first_user_id'] > 0): ?>
                                                <div class="dealer-item am-fl am-margin-right-xl">
                                                    <p>
                                                        <span class="am-text-right">一级分销商：</span>
                                                        <?php if($set['usercode_mode']['is_show']!=1) :?>
                                                            <span><?= $order['dealer_first']['user']['nickName'] ?>(ID: <?= $order['dealer_first']['user_id'] ?>)</span>
                                                        <?php endif;?>
                                                        
                                                        <?php if($set['usercode_mode']['is_show']!=0) :?>
                                                            <span><?= $order['dealer_first']['user']['nickName'] ?>(code: <?= $order['dealer_first']['user']['user_code'] ?>)</span>
                                                        <?php endif;?>
                                                        
                                                    </p>
                                                    <p>
                                                        <span class="am-text-right">分销佣金：</span>
                                                        <span class="x-color-red">￥<?= $order['first_money'] ?></span>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($order['second_user_id'] > 0): ?>
                                                <div class="dealer-item am-fl am-margin-right-xl">
                                                    <p>
                                                        <span class="am-text-right">二级分销商：</span>
                                                        <?php if($set['usercode_mode']['is_show']!=1) :?>
                                                            <span><?= $order['dealer_second']['user']['nickName'] ?>(ID: <?= $order['dealer_second']['user_id'] ?>)</span>
                                                        <?php endif;?>
                                                        
                                                        <?php if($set['usercode_mode']['is_show']!=0) :?>
                                                            <span><?= $order['dealer_second']['user']['nickName'] ?>(code: <?= $order['dealer_second']['user']['user_code'] ?>)</span>
                                                        <?php endif;?>
                                                    </p>
                                                    <p>
                                                        <span class="am-text-right">分销佣金：</span>
                                                        <span class="x-color-red">￥<?= $order['second_money'] ?></span>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($order['third_user_id'] > 0): ?>
                                                <div class="dealer-item am-fl am-margin-right-xl">
                                                    <p>
                                                        <span class="am-text-right">三级分销商：</span>
                                                        <?php if($set['usercode_mode']['is_show']!=1) :?>
                                                            <span><?= $order['dealer_third']['user']['nickName'] ?>(ID: <?= $order['dealer_third']['user_id'] ?>)</span>
                                                        <?php endif;?>
                                                        
                                                        <?php if($set['usercode_mode']['is_show']!=0) :?>
                                                            <span><?= $order['dealer_third']['user']['nickName'] ?>(code: <?= $order['dealer_third']['user']['user_code'] ?>)</span>
                                                        <?php endif;?>
                                                    </p>
                                                    <p>
                                                        <span class="am-text-right">分销佣金：</span>
                                                        <span class="x-color-red">￥<?= $order['third_money'] ?></span>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="6" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $list->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

