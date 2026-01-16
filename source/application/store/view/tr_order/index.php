<style>
    tbody tr:nth-child(2n){
        background: #fff !important;
        color:#ff6666;
    }
    /*.am-table-striped>tbody>tr:nth-child(odd)>td, .am-table-striped>tbody>tr:nth-child(odd)>th{*/
    /*    background: #f9f9f9 !important;*/
    /*}*/
</style>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">订单列表
                    <?php if($dataType=='verify'): ?>
                    <small class="tipssmall">(提示：用户提交打包或仓管提交打包后，会在待查验状态，完成包裹的拆包并打包后，将包裹状态改为完成查验，订单将进入待发货阶段)</small>
                    <?php endif ;?>
                    <?php if($dataType=='pay'): ?>
                    <small class="tipssmall">(提示：只要客户没有付款，不管支付模式是货到付款还是立即支付，都可以在此列表中看到)</small>
                    <?php endif ;?>
                    </div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-12">
                                <div class="am fl">
                                    <div class="am-form-group am-fl">
                                        <select name="limitnum"
                                                data-am-selected="{btnSize: 'sm', placeholder: '显示条数'}">
                                            <option value="15">显示15条</option>
                                            <option value="30">显示30条</option>
                                            <option value="50">显示50条</option>
                                            <option value="100">显示100条</option>
                                            <option value="200">显示200条</option>
                                            <option value="500">显示500条</option>
                                        </select>
                                    </div>
                                    <?php if($dataType=='all'): ?>
                                    <div class="am-form-group am-fl">
                                        <?php $extractStatus = $request->get('status'); ?>
                                        <select name="status"
                                                data-am-selected="{btnSize: 'sm', placeholder: '包裹状态'}">
                                            <option value=""></option>
                                            <option value="-1,1,2,3,4,5,6,7,8,9"
                                                <?= $extractStatus === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <option value="1"
                                                <?= $extractStatus === '1' ? 'selected' : '' ?>>待查验
                                            </option>
                                            <!--1 状态 1 待查验 2 待支付 3 已支付 4 拣货中 5 已打包  6已发货 7 已到货 8 已完成  9已取消-->
                                            <option value="2"
                                                <?= $extractStatus === '2' ? 'selected' : '' ?>>待支付
                                            </option>
                                            <option value="3"
                                                <?= $extractStatus === '3' ? 'selected' : '' ?>>已支付
                                            </option>
                                            <option value="4"
                                                <?= $extractStatus === '4' ? 'selected' : '' ?>>拣货中
                                            </option>
                                            <option value="5"
                                                <?= $extractStatus === '5' ? 'selected' : '' ?>>已打包
                                            </option>
                                            <option value="6"
                                                <?= $extractStatus === '6' ? 'selected' : '' ?>>已发货
                                            </option>
                                            <option value="7"
                                                <?= $extractStatus === '7' ? 'selected' : '' ?>>已到货
                                            </option>
                                            <option value="8"
                                                <?= $extractStatus === '8' ? 'selected' : '' ?>>已完成
                                            </option>
                                            <option value="9"
                                                <?= $extractStatus === '9' ? 'selected' : '' ?>>已取消
                                            </option>
                                        </select>
                                    </div>
                                    <?php endif;?>
                                    <?php if ($store['user']['is_super']==1): ?>
                                    <div class="am-form-group am-fl">
                                        <?php $extractShopId = $request->get('extract_shop_id'); ?>
                                        <select name="extract_shop_id"
                                                data-am-selected="{btnSize: 'sm', placeholder: '仓库名称'}">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $extractShopId === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php if (isset($shopList)): foreach ($shopList as $item): ?>
                                                <option value="<?= $item['shop_id'] ?>"
                                                    <?= $item['shop_id'] == $extractShopId ? 'selected' : '' ?>><?= $item['shop_name'] ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <?php endif;?>
                                    <div class="am-form-group am-fl">
                                        <?php $extractlineid = $request->get('line_id'); ?>
                                        <select name="line_id"
                                                data-am-selected="{btnSize: 'sm', placeholder: '路线名称'}">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $extractlineid === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php if (isset($lineList)): foreach ($lineList as $item): ?>
                                                <option value="<?= $item['id'] ?>"
                                                    <?= $item['id'] == $extractlineid ? 'selected' : '' ?>><?= $item['name'] ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <?php $extractserviceid = $request->get('service_id'); ?>
                                        <select name="service_id"
                                                data-am-selected="{btnSize: 'sm', placeholder: '专属客服'}">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $extractserviceid === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php if (isset($servicelist)): foreach ($servicelist as $item): ?>
                                                <option value="<?= $item['clerk_id'] ?>"
                                                    <?= $item['clerk_id'] == $extractserviceid ? 'selected' : '' ?>><?= $item['real_name'] ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input style="padding:6px 5px;" type="text" name="start_time"
                                               class="am-form-field"
                                               value="<?= $request->get('start_time') ?>" placeholder="请选择起始日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input style="padding:6px 5px;" type="text" name="end_time"
                                               class="am-form-field"
                                               value="<?= $request->get('end_time') ?>" placeholder="请选择截止日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group am-fl" style="padding-bottom:1px;">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <textarea cols="200" rows="2"  class="am-form-field" name="tr_number"
                                                   placeholder="可输入多个发货单号,按换车换行" value="<?= $request->get('tr_number') ?>"></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input style="width:250px;" type="text" class="am-form-field" name="order_sn"
                                                   placeholder="请输入平台订单号或转运单号" value="<?= $request->get('order_sn') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input style="width:250px;" type="text" class="am-form-field" name="batch_no"
                                                   placeholder="请输入批次号或提单号" value="<?= $request->get('batch_no') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input autocomplete="off" type="text" class="am-form-field" name="search"
                                                   placeholder="请输入用户昵称或ID或用户编号" value="<?= $request->get('search') ?>">
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
                   <div class="page_toolbar am-margin-bottom-xs am-cf" style="margin-bottom:20px; margin-left:15px;">
                        <?php if (checkPrivilege('package.index/changeuser')): ?>
                        <button type="button" id="j-upuser" class="am-btn am-btn-success am-radius"><i class="iconfont icon-yonghu "></i> 修改所属用户</button>
                        <?php endif;?>
                        <!--状态变更-->
                        <?php if (checkPrivilege('tr_order/upsatatus')): ?>
                        <button type="button" id="j-upstatus" class="am-btn am-btn-secondary am-radius"><i class="iconfont icon-755danzi"></i> 状态变更</button>
                        <?php endif;?>
                        
                        <!--合并订单-->
                        <?php if (checkPrivilege('tr_order/hedan')): ?>
                        <?php if($dataType=='verify' || $dataType=='all'): ?>
                        <button type="button" id="j-hedan" class="am-btn am-btn-danger am-radius"><i class="iconfont  icon-hebing"></i> 合并订单</button>
                        <?php endif;endif;?>
                        
                        <!--批量更新订单动态-->
                        <?php if (checkPrivilege('tr_order/alllogistics')): ?>
                        <button type="button" id="j-wuliu" class="am-btn am-btn-warning am-radius"><i class="iconfont icon-guojiwuliu"></i> 批量更新订单动态</button>
                        <?php endif;?>
                        
                        <!--批量打印面单-->
                        <?php if (checkPrivilege('tr_order/expressbillbatch')): ?>
                        <button type="button" id="j-batch-print" class="am-btn am-btn-warning am-radius"><i class="iconfont icon-dayinji_o"></i> 批量打印面单</button>
                        <?php endif;?>
                        
                        <!--加入拼团-->
                        <?php if (checkPrivilege('tr_order/pintuan')): ?>
                        <button type="button" id="j-pintuan" class="am-btn am-btn-success am-radius"><i class="iconfont icon-pintuan"></i> 加入拼团</button>
                        <?php endif;?>
                        
                        <!--加入批次-->
                        <?php if (checkPrivilege('batch/addtobatch')): ?>
                        <button type="button" id="j-batch" class="am-btn am-btn-secondary am-radius"><i class="iconfont icon-pintuan"></i> 加入批次</button>
                        <?php endif;?>
                        
                        <!--导出-->
                        <?php if (checkPrivilege('tr_order/loaddingoutexcel')): ?>
                        <button type="button" id="j-export" class="am-btn am-btn-warning am-radius"><i class="iconfont icon-daochu"></i> 导出订单</button>
                        <?php endif;?>
                        
                        <!--导出分成清单-->
                        <?php if (checkPrivilege('tr_order/exportinpack')): ?>
                        <?php if($dataType=='complete'): ?>
                        <button type="button" id="j-exportInpack" class="am-btn am-btn-success am-radius"><i class="iconfont icon-daochujiesuan"></i>导出分成清单</button>
                        <?php endif;?>
                        <?php endif;?>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th><input id="checkAll" type="checkbox"></th>
                                <th>单号信息</th>
                                <th>转运信息</th>
                                <th>收货信息</th>
                                <th>费用信息</th>
                                <th>包裹信息</th>
                                <th>时间</th>
                                <th>支付状态</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody id="body">
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                            <?php $status = [1=>'待查验',2=>'待发货',3=>'待发货','4'=>'待发货','5'=>'待发货','6'=>'已发货','7'=>'已到货','8'=>'已完成','-1'=>'问题件']; ?>
                            <?php $paytime_status = [ 1=>'已支付',2=>'未支付'] ; ?>
                        
                                <tr>
                                    <td class="am-text-middle">
                                       <input name="checkIds" type="checkbox" value="<?= $item['id'] ?>"> 
                                    </td>
                                    <td class="am-text-middle">
                                        <span style="cursor:pointer" text="<?= $item['order_sn'] ?>" onclick="copyUrl2(this)"><?= $item['order_sn'] ?></span></br>
                                        <?php if ($item['inpack_type']==1): ?> 
                                        <span class="am-badge am-badge-secondary">拼团订单</span>
                                        <?php endif ;?>
                                        <?php if ($item['batch_id']): ?> 
                                        批次号：<?= $item['batch']['batch_name'] ?><br>
                                        提单号/装箱号：<?= $item['batch']['batch_no'] ?><br>
                                        <?php endif ;?>
                                    </td>
                                    <td class="am-text-middle">
                                        <?php if (!empty($item['user']['service'])): ?> 
                                        专属客服：<?= $item['user']['service']['real_name']; ?></br>
                                        <?php endif ;?>
                                        承运商:
                                        <span style="cursor:pointer" text="<?= $item['t_name'] ?>" onclick="copyUrl2(this)"><?= $item['t_name'] ?></span></br>
                                        <?php if (!empty($item['t_order_sn'])): ?> 
                                        国际单号:
                                        <span style="cursor:pointer" text="<?= $item['t_order_sn'] ?>" onclick="copyUrl2(this)"><?= $item['t_order_sn'] ?></span></br>
                                        <?php endif ;?>
                                        <?php if (!empty($item['t2_order_sn'])): ?> 
                                        <span style="color:#ff6666;">转单承运商:
                                        <span style="cursor:pointer" text="<?= $item['t2_name'] ?>" onclick="copyUrl2(this)"><?= $item['t2_name'] ?></span></span></br>
                                        <span style="color:#ff6666;">转单单号:
                                        <span style="cursor:pointer" text="<?= $item['t2_order_sn'] ?>" onclick="copyUrl2(this)"><?= $item['t2_order_sn'] ?></span></span></br>
                                        <?php endif ;?>
                                        线路:
                                        <span style="cursor:pointer" text="<?= $item['line']['name'] ?>" onclick="copyUrl2(this)"><?= $item['line']['name'] ?></span></br>
                                        寄件仓库:
                                        <span style="cursor:pointer" text="<?= $item['storage']['shop_name'] ?>" onclick="copyUrl2(this)"><?= $item['storage']['shop_name'] ?></span></br>
                                        取件仓库:
                                        <span style="cursor:pointer" text="<?= $item['shop']['shop_name'] ?>" onclick="copyUrl2(this)"><?= $item['shop']['shop_name'] ?></span></br>
                                    </td>
                                    <td class="am-text-middle">
                                        用户昵称:<span style="color:#56a6ed;cursor:pointer"><?= $item['nickName']; ?></span>
                                        <span style="color:#ff6666;cursor:pointer" text="<?= $item['nickName']; ?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php if($set['usercode_mode']['is_show']!=1) :?>
                                        用户ID:<?= $item['member_id']; ?></br>
                                        <?php endif;?>
                                        <?php if($set['usercode_mode']['is_show']!=0) :?>
                                        用户Code:<?= $item['user']['user_code']; ?>
                                        <span style="color:#ff6666;cursor:pointer" text="<?= $item['user']['user_code']; ?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif;?>
                                        收件人:<?= $item['address']['name'] ?>
                                        <span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['name'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        
                                        电话:<?= $item['address']['phone'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['phone'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php if ($set['address_setting']['is_identitycard']==1): ?> 
                                        身份证:<?= $item['address']['identitycard'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['identitycard'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        <?php if ($set['address_setting']['is_clearancecode']==1): ?> 
                                        通关代码:<?= $item['address']['clearancecode'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['clearancecode'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        地址:国家/地区：<?= $item['address']['country'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['country'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        
                                        <?php if ($set['address_setting']['is_province']==1): ?> 
                                        省/州：<?= $item['address']['province'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['province'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        <?php if ($set['address_setting']['is_city']==1): ?> 
                                        市：<?= $item['address']['city'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['city'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        <!--区：<?= $item['address']['region']=='0'?'未填':$item['address']['region']?></br>-->
                                         <?php if ($set['address_setting']['is_street']==1): ?>
                                         街道：<?= !isset($item['address']['street']) || $item['address']['street']==''?'未填':$item['address']['street']?><span style="color:#ff6666;cursor:pointer" text="<?= !isset($item['address']['street']) ? '' : $item['address']['street'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                         <?php endif ;?>
                                        <?php if ($set['address_setting']['is_door']==1): ?> 
                                        门牌：<?= $item['address']['door'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['door'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                         <?php endif ;?>
                                        <?php if ($set['address_setting']['is_detail']==1): ?> 
                                        详细地址：<?= $item['address']['detail'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['detail'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        <?php if ($set['address_setting']['is_code']==1): ?> 
                                        邮编：<?= $item['address']['code']==''?'未填': $item['address']['code']?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['code'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        <?php if ($set['address_setting']['is_email']==1): ?> 
                                        邮箱：<?= !isset($item['address']['email'])?'未填':$item['address']['email'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['email'];?>" onclick="copyUrl2(this)">[复制]</span>
                                        <?php endif ;?>
                                    </td>
                                    <td class="am-text-middle">
                                        基础线路费用:<span style="color:#ff6666;cursor:pointer" text="<?= $item['free'] ?>" onclick="copyUrl2(this)"><?= $item['free'] ?></span></br>
                                        
                                        包装费:<span style="color:#ff6666;cursor:pointer" text="<?= $item['pack_free'] ?>" onclick="copyUrl2(this)"><?= $item['pack_free'] ?></span></br>

                                        其他费用:<span style="color:#ff6666;cursor:pointer" text="<?= $item['other_free'] ?>" onclick="copyUrl2(this)"><?= $item['other_free'] ?></span></br>
                                        
                                        优惠金额：<span style="color:#ff6666;cursor:pointer" text="<?= $item['user_coupon_money'] ?>" onclick="copyUrl2(this)"><?= $item['user_coupon_money']; ?></span></br>
                                        
                                        费用合计:<span style="color:#ff6666;cursor:pointer" text="<?= $item['free'] + $item['pack_free'] + $item['other_free'] ?>" onclick="copyUrl2(this)"><?= $item['free'] + $item['pack_free'] + $item['other_free'] ?></span></br>
                                        
                                        <?php if ($item['is_pay']==1): ?>
                                        实际支付：<span style="color:#ff6666;cursor:pointer" text="<?= $item['real_payment'] ?>" onclick="copyUrl2(this)"><?= $item['real_payment']; ?></span></br></br>
                                        <?php endif ;?>
                                        
                                        <?php if (isset($userclient['packit']['is_waitreceivedmoney']) && $userclient['packit']['is_waitreceivedmoney']==1): ?> 
                                        代收款:<span style="color:#ff6666;cursor:pointer" text="<?= $item['waitreceivedmoney'] ?>" onclick="copyUrl2(this)"><?= $item['waitreceivedmoney'] ?></span></br></br>
                                        <?php endif ;?>
                                        <?php if ($item['usercoupon']): ?>
                                        优惠券:<span style="color:#ff6666;cursor:pointer" text="<?= $item['usercoupon']['name'] ?>" onclick="copyUrl2(this)"><?= $item['usercoupon']['name'] ?></span></br>
                                        
                                        <?php endif ;?>
                                        
                                    </td>
                  
                                    <td class="am-text-middle">
                                        实际重量(<?= $set['weight_mode']['unit'] ?>):<?= $item['weight'] ?></br>
                                        体积重量(<?= $set['weight_mode']['unit'] ?>):<?= $item['volume'] ?></br>
                                        计费重量(<?= $set['weight_mode']['unit'] ?>):<?= $item['cale_weight'] ?></br></br>
                                        共有 <?= $item['num'] ?> 个包裹 </br>
                                        <a href="<?= url('store/trOrder/package', ['id' => $item['id']]) ?>">查看包裹明细</a>
                                    </td>
                            
                                    <td class="am-text-middle">
                                        提交打包：<?= $item['created_time'] ?> </br>
                                        
                                        <?php if ($item['is_pay']==1): ?>
                                        支付时间：<?= $item['pay_time'] ?> </br>
                                        <?php endif; ?>
                                        
                                        <?php if ($item['status']==7): ?>
                                        到货时间：<?= $item['shoprk_time'] ?> </br>
                                        <?php endif; ?>
                                        
                                        <?php if ($item['status']==8): ?>
                                        签收时间：<?= $item['receipt_time'] ?> </br>
                                        <?php endif; ?>
                                         
                                        <?php if ($item['status']==8 && $item['is_settled']==1): ?>
                                        结算时间：<?= $item['settle_time'] ?> </br>
                                        <?php endif; ?>
                                        
                                        <?php if ($item['status']==5 && $item['pick_time'] ): ?>
                                        打包完成：<?= $item['pick_time'] ?> </br>
                                        <?php endif; ?>
                                        
                                    </td>
                                    
                                    <td class="am-text-middle">
                                        <span class="am-badge <?= $item['is_pay']==1?'am-badge-success':'am-badge-danger'?>">
                                            <?= $paytime_status[$item['is_pay']];?>
                                        </span><br>
                                        
                                        <?php if ($item['is_pay']==1): ?>
                                        <span class="am-badge <?= $item['is_pay']==1?'am-badge-success':'am-badge-danger'?>">
                                            <?= $item['is_pay_type']['text'];?>
                                        </span><br>
                                        <?php endif; ?>
                                        
                                        <span class="am-badge <?= $item['pay_type']==1?'am-badge-warning':'am-badge-primary'?>">
                                            <?= $item['pay_type']==1?'货到付款':'付款发货';?>
                                        </span>
                                    </td>
                                    <td class="am-text-middle"><?= $status[$item['status']] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <!--编辑-->
                                            <?php if (checkPrivilege('tr_order/edit')): ?>
                                            <a href="<?= url('store/trOrder/edit', ['id' => $item['id']]) ?>">
                                                <i class="am-icon-pencil"></i> 编辑
                                            </a>
                                            <?php endif; ?>
                                            <!--详情-->
                                            <?php if (checkPrivilege('tr_order/orderdetail')): ?>
                                            <a href="<?= url('store/trOrder/orderdetail', ['id' => $item['id']]) ?>">
                                                <i class="iconfont icon-xiangqing"></i> 详情
                                            </a>
                                            <?php endif; ?>
                                            <!--删除-->
                                            <?php if (checkPrivilege('tr_order/orderdelete')): ?>
                                            <a href="javascript:void(0);"
                                               class="item-delete tpl-table-black-operation-del"
                                               data-id="<?= $item['id'] ?>">
                                                <i class="am-icon-trash"></i> 删除
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                        <div class="tpl-table-black-operation" style="margin-top:10px">
                                            <?php if (checkPrivilege('tr_order/deliverysave')): ?>
                                            <?php if ((in_array($item['status'],[2,3,4,5]) && $item['is_pay']==1) || $item['pay_type']==1): ?>
                                             <a href="<?= url('store/trOrder/delivery', ['id' => $item['id']]) ?>">
                                                <i class="iconfont icon-baoguo_fahuo_o"></i> 发货
                                            </a>
                                            <?php endif ;?>
                                            <?php endif ;?>
                                            <!--打印面单-->
                                            <?php if (checkPrivilege('tr_order/expressbill')): ?>
                                            <?php if ($item['status']>5): ?>
                                            <a href="javascript:void(0);" data-id="<?= $item['id'] ?>" class="j-express">
                                                <i class="iconfont icon-dayinji_o"></i> 打印面单
                                            </a>
                                            <?php endif ;endif;?>
                                            <!--物流更新-->
                                            <?php if (checkPrivilege('tr_order/logistics')): ?>
                                            <?php if ($item['status']>=6): ?>
                                            <a href="<?= url('store/trOrder/logistics', ['id' => $item['id']]) ?>">
                                                <i class="iconfont icon-755danzi"></i> 物流更新
                                            </a>
                                            <?php endif ;endif;?>
                                            <?php if ($item['status'] ==-1): ?>
                                            <a href="<?= url('store/trOrder/logistics', ['id' => $item['id']]) ?>">
                                                <i class="am-icon-pencil"></i> 退款
                                            </a>
                                            <a href="<?= url('store/trOrder/logistics', ['id' => $item['id']]) ?>">
                                                <i class="am-icon-pencil"></i> 退货处理
                                            </a>
                                            <?php endif ;?>
                                         </div>
                                         <div class="tpl-table-black-operation" style="margin-top:10px">
                                            <!--打印标签-->
                                            <?php if (checkPrivilege('tr_order/expresslabel')): ?>
                                            <a class='tpl-table-black-operation-green j-label' href="javascript:void(0);" data-id="<?= $item['id'] ?>">
                                                <i class="iconfont icon-biaoqian"></i> 打印标签
                                            </a>
                                            <?php endif ;?>
                                            
                                            <!--变更地址-->
                                            <?php if (checkPrivilege('tr_order/updateaddress')): ?>
                                             <a class='tpl-table-black-operation-green j-changeaddress' href="javascript:void(0);" data-user_id="<?= $item['member_id'] ?>" data-id="<?= $item['id'] ?>">
                                                <i class="iconfont icon-dizhi"></i> 变更地址
                                            </a>
                                            <?php endif ;?>
                                         </div>
                                         <div class="tpl-table-black-operation" style="margin-top:10px">
                                            <!--打印账单-->
                                            <?php if (checkPrivilege('tr_order/freelistlabel')): ?>
                                            <?php if ($item['status']>=2): ?>
                                             <a class='tpl-table-black-operation-warning j-freelist' href="javascript:void(0);" data-id="<?= $item['id'] ?>">
                                                <i class="iconfont icon-biaoqian"></i> 打印账单
                                            </a>
                                            <?php endif ;?>
                                            <?php endif ;?>
                                            <!--转单-->
                                            <?php if (checkPrivilege('tr_order/changesn')): ?>
                                            <?php if (in_array($item['status'],[6,7,8])): ?>
                                             <a href="<?= url('store/trOrder/changesn', ['id' => $item['id']]) ?>">
                                                <i class="iconfont icon-baoguo_fahuo_o"></i> 转单
                                            </a>
                                            <?php endif ;?>
                                            <?php endif ;?>
                                            <?php if (checkPrivilege('tr_order/payyue') && $item['is_pay'] ==2) : ?>
                                            <a class='tpl-table-black-operation-warning j-payyue' href="javascript:void(0);" data-balance="0" data-price="0" data-name="<?= $item['nickName'] ?>" data-user_id="<?= $item['member_id'] ?>" data-id="<?= $item['id'] ?>">
                                                <i class="iconfont icon-dizhi"></i> 余额扣除
                                            </a>
                                             <?php endif ;?>
                                         </div>
                                         <div class="tpl-table-black-operation" style="margin-top:10px">
                                            <?php if (checkPrivilege('tr_order/cashforprice') && $item['is_pay'] ==2) : ?>
                                            <a class='tpl-table-black-operation-warning j-payxianjin' href="javascript:void(0);" data-balance="0" data-price="0" data-name="<?= $item['nickName'] ?>" data-user_id="<?= $item['member_id'] ?>" data-id="<?= $item['id'] ?>">
                                                <i class="iconfont icon-dizhi"></i> 现金收款
                                            </a>
                                            <?php endif ;?>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="11" class="am-text-left"><?= $item['remark']?$item['remark']:'请输入订单备注' ?> 
                                    <a class="j-audit" data-id="<?= $item['id'] ?>" data-remark="<?= $item['remark'] ?>" href="javascript:void(0);"><i class="am-icon-pencil"></i>
                                    </a></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="11" class="am-text-center">暂无记录</td>
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
<script id="tpl-dealer-apply" type="text/template">
    <div class="am-padding-top-sm">
        <form class="form-dealer-apply am-form tpl-form-line-form" method="post"
              action="<?= url('store/trOrder/changeRemark') ?>">
            <input type="hidden" name="id" value="{{ id }}">
            <div class="am-form-group">
                <label class="am-u-sm-3 am-form-label"> 备注信息 </label>
                <div class="am-u-sm-9">
                    <input type="text" class="tpl-form-input" name="remark" placeholder="请填写备注"
                           value="{{ remark }}">
                </div>
            </div>
        </form>
    </div>
</script>
<script id="tpl-user-item" type="text/template">
    {{ each $data }}
    <div class="file-item">
        <a href="{{ $value.avatarUrl }}" title="{{ $value.nickName }} (ID:{{ $value.user_id }})" target="_blank">
            <img src="{{ $value.avatarUrl }}">
        </a>
        <input type="hidden" name="user_id" value="{{ $value.user_id }}">
    </div>
    {{ /each }}
</script>
<script id="tpl-grade" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
               <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择包裹数量
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                       <p class='am-form-static'> 共选中 {{ selectCount }} 包裹</p>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择用户
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                         <div class="widget-become-goods am-form-file am-margin-top-xs">
                                        <button type="button"
                                                class="j-selectUser upload-file am-btn am-btn-secondary am-radius" onclick="doSelectUser()">
                                            <i class="am-icon-cloud-upload"></i> 选择用户
                                        </button>
                                        <div class="user-list uploader-list am-cf">
                                        </div>
                                        <div class="am-block">
                                            <small>选择后不可更改</small>
                                        </div>
                                    </div>
                    </div>
                </div>
                
            </div>
        </form>
    </div>
</script>
<script id="tpl-status" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
               <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择包裹数量
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                       <p class='am-form-static'> 共选中 {{ selectCount }} 包裹</p>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择状态
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                          <select name="pack[status]"
                                data-am-selected="{btnSize: 'sm', placeholder: '请选择线路'}">
                               
                               <option value="6">已发货</option>
                               <option value="7">已到货</option>
                               <option value="8">已完成</option>
                               <option value="5">回退到待发货</option>
                        </select>
                    </div>
                </div>
                
            </div>
        </form>
    </div>
</script>

<script id="tpl-wuliu" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
               <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择包裹数量
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                       <p class='am-form-static'> 共选中 {{ selectCount }} 包裹</p>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        输入物流状态
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text" name="logistics_describe" value="">
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择物流时间
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text"  name="created_time" placeholder="请选择起始日期" value="<?php echo date("Y-m-d H:i:s",time()) ?>" id="datetimepicker" class="am-form-field">
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>
<script id="tpl-shelf" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
               <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择集运单数
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <p class='am-form-static'> 共选中 {{ selectCount }} 订单</p>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择拼团
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                          <select name="pintuan_id"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" onchange="getSelectData(this)" data-select_type='shelf'>
                                        <option value="">请选择</option>
                                        <?php if (isset($pintuanlist) && !$pintuanlist->isEmpty()):
                                            foreach ($pintuanlist as $item): ?>
                                                <option value="<?= $item['order_id'] ?>"><?= $item['title'] ?> - <?= $item['user']['nickName'] ?></option>
                                            <?php endforeach; endif; ?>
                                    </select>
                    </div>
                </div>

            </div>
        </form>
    </div>
</script>
<script id="tpl-batch" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
               <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择集运单数
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <p class='am-form-static'> 共选中 {{ selectCount }} 订单</p>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择批次
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                          <select name="batch_id"
                                data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" onchange="getSelectData(this)" data-select_type='shelf'>
                            <option value="">请选择</option>
                            <?php if (isset($batchlist) && !$batchlist->isEmpty()):
                                foreach ($batchlist as $item): ?>
                                    <option value="<?= $item['batch_id'] ?>"><?= $item['batch_name'] ?> - <?= $item['batch_no'] ?></option>
                                <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>

            </div>
        </form>
    </div>
</script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
     function doSelectUser(){
           var $userList = $('.user-list');
            $.selectData({
                title: '选择用户',
                uri: 'user/lists',
                dataIndex: 'user_id',
                done: function (data) {
                    var user = [data[0]];
                    console.log(user,98999);
                    $userList.html(template('tpl-user-item', user));
                }
            });
    }
    
    $(function () {
        
        /**
         * 修改会员
         */
        $('#j-upuser').on('click', function () {
             var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
                layer.alert('请先选择包裹', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            $.showModal({
                title: '修改会员'
                , area: '460px'
                , content: template('tpl-grade', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('/store/tr_Order/changeUser') ?>',
                        data: {selectIds:data.selectId}
                    });
                    return true;
                }
            });
        });
        
        
        $('#datetimepicker').datetimepicker({
          format: 'yyyy-mm-dd hh:ii'
        });
        
        
        $('#datetimepicker').datetimepicker().on('changeDate', function(ev){
            // $('#datetimepicker').datetimepicker('hide');
          });
          
          
        /**
         * 审核操作
         */
        $('.j-audit').click(function () {
            var $this = $(this);
            layer.open({
                type: 1
                , title: '修改备注信息'
                , area: '500px'
                , offset: 'auto'
                , anim: 1
                , closeBtn: 1
                , shade: 0.3
                , btn: ['确定', '取消']
                , content: template('tpl-dealer-apply', $this.data())
                , success: function (layero) {
                    // 注册radio组件
                    layero.find('input[type=radio]').uCheck();
                }
                , yes: function (index, layero) {
                    // 表单提交
                    layero.find('.form-dealer-apply').ajaxSubmit({
                        type: 'post',
                        dataType: 'json',
                        success: function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        }
                    });
                    layer.close(index);
                }
            });
        });
    });
     
</script>
<script id="tpl-errors" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-4 am-form-label form-require">
                        用户信息
                    </label>
                    <div class="am-u-sm-8">
                       <p class='am-form-static'>{{ name }}</p>
                    </div>
                </div>
               <div class="am-form-group">
                    <label class="am-u-sm-4 am-form-label form-require">
                        订单信息
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                       <p class='am-form-static'>用户余额：{{ balance }} / 订单金额：{{ price }}</p>
                    </div>
                </div>
                
            </div>
        </form>
    </div>
</script>
<script id="tpl-xianjin" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-4 am-form-label form-require">
                        用户信息
                    </label>
                    <div class="am-u-sm-8">
                       <p class='am-form-static'>{{ name }}</p>
                    </div>
                </div>
               <div class="am-form-group">
                    <label class="am-u-sm-4 am-form-label form-require">
                        订单信息
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                       <p class='am-form-static'>订单金额：{{ price }}</p>
                    </div>
                </div>
                
            </div>
        </form>
    </div>
</script>
<script>
    var _render = false;
    var getSelectData = function(_this){
        if (_render){
            return 
        }
        var sType = _this.getAttribute('data-select_type');
        var api_group = {'shelf':'<?= url('store/shelf_manager.index/getShelf')?>','shelf_unit':'<?= url('store/shelf_manager.index/getshelf_unit')?>'};
        if (sType=='shelf'){
            var $selected = $('#select-shelf');
            var data = {'shop_id':_this.value}
        }
        if (sType=='shelf_unit'){
            var $selected = $('#select_shelf_unit');
            var data = {'shelf_id':_this.value}
        }
        
        $.ajax({
            type:"GET",
            url:api_group[sType],
            data:data,
            dataType:'json',
            success:function(res){
                var _data = res.msg.data;
                if (sType=='shelf'){
                    for (var i=0;i<_data.length;i++){
                        // _html += '<option value="">'+_data[i]['shelf_name']+'</option>';
                        $selected.append('<option value="' + _data[i]['id'] +'">' + _data[i]['shelf_name'] + '</option>');
                    }
                }else{
                    console.log(444);
                    for (var i=0;i<_data.length;i++){
                        // _html += '<option value="">'+_data[i]['shelf_name']+'</option>';
                        $selected.append('<option value="' + _data[i]['shelf_unit_id'] +'">' +_data[i]['shelf_unit_floor']+ '层'+ _data[i]['shelf_unit_no'] + '号</option>');
                    }
                }
                _render = true;
                setTimeout(function() {
                    _render = false;
                }, 10);
            }
        })
    }

    $(function () {
       checker = {
          num:0, 
          check:[],
          init:function(){
              this.check = document.getElementById('body').getElementsByTagName('input');
              this.num = this.check.length;
              this.bindEvent();
          },
          bindEvent:function(){
              var that = this;
              for(var i=0; i< this.check.length; i++){
                  this.check[i].onclick = function(){
                       var _check = that.isFullCheck();
                       if (_check){
                           document.getElementById('checkAll').checked = 'checked';
                       }else{
                           document.getElementById('checkAll').checked = '';
                       }
                  }
              }
              
              var  allCheck = document.getElementById('checkAll');
              allCheck.onclick = function(){
                  if (this.checked){
                      that.setFullCheck();
                  }else{
                      that.setFullCheck('');
                  }
              }
              
          },
          setFullCheck:function(checked='checked'){
             for (var ik =0; ik<this.num; ik++){
                  this.check[ik].checked = checked; 
              } 
          },
          isFullCheck:function(){
              var hasCheck = 0;
              for (var k =0; k<this.num; k++){
                   if (this.check[k].checked){
                       hasCheck++;
                   }
              }
              return hasCheck==this.num?true:false;
          },
          getCheckSelect:function(){
              var selectIds = [];
              for (var i=0;i<this.check.length;i++){
                    if (this.check[i].checked){
                       selectIds.push(this.check[i].value);
                    }
              }
              return selectIds;
          }
       }
       
       checker.init();
        // 删除元素
        var url = "<?= url('store/trOrder/orderdelete') ?>";
        $('.item-delete').delete('id', url);

        /**
         * 注册操作事件
         * @type {jQuery|HTMLElement}
         */
        var $dropdown = $('.j-opSelect');
        $dropdown.dropdown();
        
        
        //余额抵扣集运
        $('.j-payyue').click(function (e) {
            var data = $(this).data();
            var user_id = $(this).data().user_id;
            var id=  $(this).data().id;
            
            if(!user_id){
                layer.alert('用户信息有误', {icon: 5});
                return false;
            }
            // data.balance = user_id;
            $.post('store/tr_Order/balanceAndPrice',{id:id,user_id:user_id}, function (result) {
                if(result.code == 1 ){
                    data.balance = result.data.balance;
                    data.price = result.data.price;
                    $.showModal({
                        title: '余额抵扣'
                        , area: '460px'
                        , content: template('tpl-errors', data)
                        , uCheck: true
                        , success: function ($content) {
                        }
                        , yes: function ($content) {
                            $.post('store/tr_Order/payyue',{id:id,user_id:user_id}, function (result) {
                                result.code === 1 ? $.show_success(result.msg, result.url)
                                    : $.show_error(result.msg);
                            });
                        }
                    });
                }else{
                  $.show_error(result.msg);   
                }
            });
        });
        
         //现金抵扣集运
        $('.j-payxianjin').click(function (e) {
            var data = $(this).data();
            var user_id = $(this).data().user_id;
            var id=  $(this).data().id;
            
            if(!user_id){
                layer.alert('用户信息有误', {icon: 5});
                return false;
            }
            
            $.post('store/tr_Order/balanceAndPrice',{id:id,user_id:user_id}, function (result) {
                if(result.code == 1 ){
                    data.balance = result.data.balance;
                    data.price = result.data.price;
                    $.showModal({
                        title: '现金收款'
                        , area: '460px'
                        , content: template('tpl-xianjin', data)
                        , uCheck: true
                        , success: function ($content) {
                        }
                        , yes: function ($content) {
                            $.post('store/tr_Order/cashforPrice',{id:id,user_id:user_id}, function (result) {
                                result.code === 1 ? $.show_success(result.msg, result.url)
                                    : $.show_error(result.msg);
                            });
                        }
                    });
                }else{
                  $.show_error(result.msg);   
                }
            });

        });
        
        
        
         /**
         * 加入批次
         */
        $('#j-batch').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
                layer.alert('请先选择集运单', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            console.log(data.selectId)
            $.showModal({
                title: '将订单加入到批次中'
                , area: '460px'
                , content: template('tpl-batch', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('store/batch/addtobatch') ?>',
                        data: {selectIds:data.selectId},
                    });
                    return true;
                }
            });
        });

            
        // 变更地址
        $('.j-changeaddress').click(function (e) {
            var user_id = $(this).data().user_id;
            if(!user_id){
                layer.alert('用户信息有误', {icon: 5});
                return false;
            }
            var id=  $(this).data().id;
            $.selectData({
                title: '变更地址',
                uri: 'Address/AddressList'+'/user_id/'+user_id,
                dataIndex: 'address_id',
                done: function (list) {
                    var data = {};
                    var select_ids = [];
                    if (list.length>1){
                        layer.alert('只能勾选一个', {icon: 5});
                        return;
                    }
                    console.log(list);
                    // 请求服务器修改地址
                    $.ajax({
                        type:"POST",
                        url:'<?= url('store/trOrder/updateAddress') ?>',
                        data:{id:id,address_id:list[0]["address_id"]},
                        dataType:"JSON",
                        success:function(result){
                           window.location.reload(true);
                        }
                    })
                    
                }
            });
        });
            
            	/**
				 * 导出包裹
				 */
				$('#j-export').on('click', function() {
					var $tabs, data = $(this).data();
					var selectIds = checker.getCheckSelect();
					var serializeObj = {};
					var fordata = $(".toolbar-form").serializeArray().forEach(function(item) {
						if (item.name != 's') {
							serializeObj[item.name] = item.value;
						}

					});
					if (isEmpty(serializeObj['search']) && selectIds.length == 0) {
						layer.alert('请先选择包裹或者搜索后再点导出', {
							icon: 5
						});
						return;
					}
					$.ajax({
						type: 'post',
						url: "<?= url('store/trOrder/loaddingOutExcel') ?>",
						data: {
							selectId: selectIds,
							seach: serializeObj
						},
						dataType: "json",
						success: function(res) {
							if (res.code == 1) {
								console.log(res.url.file_name);
								var a = document.createElement('a');
								document.body.appendChild(a);
								a.href = res.url.file_name;
								a.click();
							}
						}
					})
				});
        
        
                /**
				 * 导出集运结算单
				 */
				$('#j-exportInpack').on('click', function() {
					var $tabs, data = $(this).data();
					var selectIds = checker.getCheckSelect();
					var serializeObj = {};
					var fordata = $(".toolbar-form").serializeArray().forEach(function(item) {
						if (item.name != 's') {
							serializeObj[item.name] = item.value;
						}

					});
					if (isEmpty(serializeObj['search']) && selectIds.length == 0) {
						layer.alert('请先选择订单或者搜索后再点导出', {
							icon: 5
						});
						return;
					}
					$.ajax({
						type: 'post',
						url: "<?= url('store/trOrder/exportInpack') ?>",
						data: {
							selectId: selectIds,
							seach: serializeObj
						},
						dataType: "json",
						success: function(res) {
							if (res.code == 1) {
								console.log(res.url.file_name);
								var a = document.createElement('a');
								document.body.appendChild(a);
								a.href = res.url.file_name;
								a.click();
							}
						}
					})
				});
        
        
        /**
         * 修改入库状态
         */
        $('#j-upstatus').on('click', function(){
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
                layer.alert('请先选择集运单', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            $.showModal({
                title: '入库状态'
                , area: '460px'
                , content: template('tpl-status', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('store/trOrder/upsatatus') ?>',
                        data: {
                            selectIds:data.selectId
                        }
                    });
                    return true;
                }
            });
        });
       
        $(".j-express").on('click',function(){
           var data = $(this).data();
           $.ajax({
               url:'<?= url('store/trOrder/expressBill') ?>',
               type:"get",
               data:{id:data['id']},
               success:function(result){
                   console.log(result);

                    if(result.code ===0){
                       layer.alert(result.msg, {icon: 5});
                       return; 
                    }
                   
                   $.showModal({
                        title: '标签打印预览'
                        , area: '600px,700px'
                        , content: result
                        , success: function ($content) {
                            console.log($content)
                             
                        }
                        , yes: function ($content) {
                            PrintDiv(result)
                        }
                    });
               }
               
           })
        }); 
        
        //打印账单
        $(".j-freelist").on('click',function(){
           var data = $(this).data();
           $.ajax({
               url:'<?= url('store/trOrder/freelistLabel') ?>',
               type:"get",
               data:{id:data['id']},
               success:function(result){
                   console.log(result);
                    if(result.code ===0){
                       layer.alert(result.msg, {icon: 5});
                       return; 
                    }
                   
                  $.showModal({
                        title: '账单打印预览'
                        , area: '600px,700px'
                        , content: result
                        , success: function ($content) {
                            console.log($content)
                             
                        }
                        , yes: function ($content) {
                            PrintDiv(result)
                        }
                    });
               }
               
           })
        });
        
        //打印标签
         $(".j-label").on('click',function(){
           var data = $(this).data();
        //   console.log(6543578)
           $.ajax({
               url:'<?= url('store/trOrder/expressLabel') ?>',
               type:"get",
               data:{id:data['id']},
               success:function(result){
                   console.log(result);

                    if(result.code ===0){
                       layer.alert(result.msg, {icon: 5});
                       return; 
                    }
                   
                   $.showModal({
                        title: '标签打印预览'
                        , area: '600px,700px'
                        , content: result
                        , success: function ($content) {
                            console.log($content)
                             
                        }
                        , yes: function ($content) {
                            PrintDiv(result)
                        }
                    });
               }
               
           })
        }); 
 
        
        function PrintDiv(content) {
            var win = window.open("");
            win.document.write('<html><head></head><body>'
                + content + '</body>'
                + "</html>");
            win.document.close();
            //Chrome
            if (navigator.userAgent.indexOf("Chrome") != -1) {
                win.onload = function () {
                    win.document.execCommand('print');
                    win.close();
                }
            }
            //Firefox
            else {
                win.print();
                win.close();
            }
        }
        
        /**
         * 批量手动更新物流信息
         */
        $('#j-wuliu').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
               layer.alert('请先选择集运单', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            $.showModal({
                title: '批量更新订单动态'
                , area: '460px'
                , content: template('tpl-wuliu', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('store/trOrder/alllogistics') ?>',
                        data: {
                            selectIds:data.selectId
                        }
                    });
                    return true;
                }
            });
            
        $('#datetimepicker').datetimepicker({
          format: 'yyyy-mm-dd hh:ii'
        });
        
        
        $('#datetimepicker').datetimepicker().on('changeDate', function(ev){
            $('#datetimepicker').datetimepicker('hide');
          });
            
        });
        
        /**
         * 批量手动更新物流信息
         */
        $('#j-batch-print').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
               layer.alert('请先选择集运单', {icon: 5});
                return;
            }
            // 请求服务器生成pdf地址
            $.ajax({
                type:"POST",
                url:'<?= url('store/trOrder/expressBillbatch') ?>',
                data:{selectIds:selectIds},
                dataType:"JSON",
                success:function(result){
                    console.log(result,'2222');
                }
            })
            
        });
        
        /**
         * 合并订单
         */
        $('#j-hedan').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            var hedanurl = "<?= url('store/trOrder/hedan') ?>";
            if (selectIds.length==0){
                layer.alert('请先选择集运单', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            // data.selectCount = selectIds.length;
            layer.confirm('请确定是否合并订单，请选择同一个用户进行合单！不同用户敬请期待拼邮功能开发(*^_^*)', {title: '合并订单'}
                    , function (index) {
                        $.post(hedanurl, data.selectId, function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                        layer.close(index);
                    });
        });
        
        
        
         /**
         * 修改包裹位置
         */
        $('#j-pintuan').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
                layer.alert('请先选择集运单', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            console.log(data.selectId)
            $.showModal({
                title: '修改包裹位置'
                , area: '460px'
                , content: template('tpl-shelf', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('store/trOrder/pintuan') ?>',
                        data: {selectIds:data.selectId},
                    });
                    return true;
                }
            });
        });

        
        
        		function isEmpty(val) {
					let valType = Object.prototype.toString.call(val);
					let isEmpty = false;
					switch (valType) {
						case "[object Undefined]":
						case "[object Null]":
							isEmpty = true;
							break;
						case "[object Array]":
						case "[object String]":
							try {
								isEmpty = val + "" === "null" || val + "" === "undefined" || val.length <= 0 || val.split(
									"").length <= 0 ? true : false;
							} catch (error) {
								isEmpty = false;
							};
							break;
						case "[object Object]":
							try {
								let temp = JSON.stringify(val);
								isEmpty = temp + "" === "null" || temp + "" === "undefined" || temp === "{}" ? true :
								false;
							} catch (error) {
								isEmpty = false;
							}
							break;
						case "[object Number]":
							isEmpty = val + "" === "NaN" || val + "" === "Infinity" ? true : false;
							break;
						default:
							isEmpty = false;
							break;
					}
					return isEmpty;
				};
		
        
      
    });
    
     
</script>

