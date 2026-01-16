<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">批次下的订单列表
                    <small class="tipssmall">(提示：只要客户没有付款，不管支付模式是货到付款还是立即支付，都可以在此列表中看到)</small>
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
                                            <input type="text" class="am-form-field" name="order_sn"
                                                   placeholder="请输入平台订单号" value="<?= $request->get('order_sn') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl" style="padding-bottom:1px;">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="t_order_sn"
                                                   placeholder="请输入转运单号" value="<?= $request->get('t_order_sn') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl" style="padding-bottom:1px;">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="user_code"
                                                   placeholder="请输入用户编号" value="<?= $request->get('user_code') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input autocomplete="off" type="text" class="am-form-field" name="search"
                                                   placeholder="请输入用户昵称或ID" value="<?= $request->get('search') ?>">
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
                        <!--<button type="button" id="j-upstatus" class="am-btn am-btn-secondary am-radius">状态变更</button>-->
                        <!--<button type="button" id="j-hedan" class="am-btn am-btn-danger am-radius">合并订单</button>-->
                        <!--<button type="button" id="j-wuliu" class="am-btn am-btn-warning am-radius">批量更新订单动态</button>-->
                        <!--<button type="button" id="j-batch-print" class="am-btn am-btn-warning am-radius">批量打印面单</button>-->
                        <button type="button" id="j-export" class="am-btn am-btn-warning am-radius"><i class="iconfont icon-daochu"></i> 导出</button>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th><input id="checkAll" type="checkbox"></th>
                                <th>平台订单号</th>
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
                            <?php if (count($list)>0): foreach ($list as $item): ?>
                            <?php $status = [1=>'待查验',2=>'待发货',3=>'待发货','4'=>'待发货','5'=>'待发货','6'=>'已发货','7'=>'已收货','8'=>'已完成','-1'=>'问题件']; ?>
                            <?php $paytime_status = [ 1=>'已支付',2=>'未支付'] ; ?>
                                <tr>
                                    <td class="am-text-middle">
                                       <input name="checkIds" type="checkbox" value="<?= $item['id'] ?>"> 
                                    </td>
                                    <td class="am-text-middle">
                                        <?= $item['order_sn'] ?><br>
                                    </td>
                                    <td class="am-text-middle">承运商:<?= $item['t_name'] ?></br>转运单号:<?= $item['t_order_sn'] ?></br>线路:<?= $item['line']['name'] ?></br>运往仓库:<?= $item['storage']['shop_name'] ?></td>
                                    <td class="am-text-middle">
                                        用户ID:<?= $item['member_id']; ?> 用户Code:<?= $item['user']['user_code']; ?></br>
                                        收件人:<?= $item['address']['name'] ?>
                                        <span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['name'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        
                                        电话:<?= $item['address']['phone'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['phone'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php if ($set['is_identitycard']==1): ?> 
                                        身份证:<?= $item['address']['identitycard'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['identitycard'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        <?php if ($set['is_clearancecode']==1): ?> 
                                        通关代码:<?= $item['address']['clearancecode'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['clearancecode'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        地址:寄往国家：<?= $item['address']['country'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['country'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        
                                        <?php if ($set['is_province']==1): ?> 
                                        省/州：<?= $item['address']['province'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['province'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        <?php if ($set['is_city']==1): ?> 
                                        市：<?= $item['address']['city'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['city'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        <!--区：<?= $item['address']['region']=='0'?'未填':$item['address']['region']?></br>-->
                                        <?php if ($set['is_street']==1): ?>
                                        街道：<?= $item['address']['street']=='0'?'未填':$item['address']['street']?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['street'];?>" onclick="copyUrl2(this)">[复制]</span>
                                        <?php endif ;?>
                                        <?php if ($set['is_door']==1): ?> 
                                        门牌：<?= $item['address']['door'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['door'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                         <?php endif ;?>
                                        <?php if ($set['is_detail']==1): ?> 
                                        详细地址：<?= $item['address']['detail'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['detail'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        <?php if ($set['is_code']==1): ?> 
                                        邮编：<?= $item['address']['code']==0?'未填': $item['address']['code']?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['code'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        <?php if ($set['is_email']==1): ?> 
                                        邮箱：<?= $item['address']['email']==0?'未填':$item['address']['email'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['email'];?>" onclick="copyUrl2(this)">[复制]</span>
                                        <?php endif ;?>
                                    </td>
                                    <td class="am-text-middle">
                                        基础线路费用:<?= $item['free'] ?></br>
                                        包装费:<?= $item['pack_free'] ?></br>
                                        其他费用:<?= $item['other_free'] ?>
                                    </td>
                  
                                    <td class="am-text-middle">
                                        实际重量(Kg):<?= $item['weight'] ?></br>
                                        体积重量(Kg):<?= $item['volume'] ?></br>
                                        计费重量(Kg):<?= $item['cale_weight'] ?></br></br>
                               
                                        <a href="<?= url('store/trOrder/package', ['id' => $item['id']]) ?>">查看包裹明细</a>
                                    </td>
                                    <td class="am-text-middle">
                                        申请打包: <?= $item['created_time'] ?> </br>
                                        支付时间: <?= $item['pay_time'] ?> </br>
 
                                        <?= $item['pick_time'] ?> </br> 
                                        <?php if ($item['status']==4 && !$item['unpack_time']): ?>打包进度：<span style="color:#ff6666;">已打包[<?= $item['inpack']?>]</span> <?php else:?>打包完成: <?php endif; ?> <?= $item['unpack_time'] ?> 
                                    </td>
                                    
                                    <td class="am-text-middle">
                                        <span class="am-badge <?= $item['is_pay']==1?'am-badge-success':'am-badge-danger'?>">
                                            <?= $paytime_status[$item['is_pay']];?>
                                        </span><br>
                                        <span class="am-badge <?= $item['pay_type']==1?'am-badge-warning':'am-badge-primary'?>">
                                            <?= $item['pay_type']==1?'货到付款':'付款发货';?>
                                        </span>
                                    </td>
                                    <td class="am-text-middle"><?= $status[$item['status']] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            
                                            <a href="<?= url('store/trOrder/edit', ['id' => $item['id']]) ?>">
                                                <i class="am-icon-pencil"></i> 编辑
                                            </a>
                                            <a href="<?= url('store/trOrder/orderdetail', ['id' => $item['id']]) ?>">
                                                <i class="iconfont icon-xiangqing"></i> 详情
                                            </a>
                                            <a href="javascript:void(0);"
                                               class="item-delete tpl-table-black-operation-del"
                                               data-id="<?= $item['id'] ?>">
                                                <i class="am-icon-trash"></i> 删除
                                            </a>
                                        </div>
                                        <div class="tpl-table-black-operation" style="margin-top:10px">
                                            <a href="javascript:void(0);" data-id="<?= $item['id'] ?>" class="j-express">
                                                <i class="iconfont icon-dayinji_o"></i> 打印面单
                                            </a>
                                            <?php if (in_array($item['status'],[2,3,4,5])): ?>
                                             <a href="<?= url('store/trOrder/delivery', ['id' => $item['id']]) ?>">
                                                <i class="am-icon-pencil"></i> 发货
                                            </a>
                                            <?php endif ;?>
                                            <?php if (in_array($item['status'],[1,2,3,4,5,6])): ?>
                                             <a href="javascript:void(0);" 
                                                  class="item-yichu tpl-table-black-operation-del"
                                                data-id="<?= $item['id'] ?>">
                                                <i class="iconfont icon-daochu"></i> 移出批次
                                            </a>
                                            <?php endif ;?>
                                            <?php if ($item['status']>=6): ?>
                                            <a href="<?= url('store/trOrder/logistics', ['id' => $item['id']]) ?>">
                                                <i class="iconfont icon-guojiwuliu"></i> 物流更新
                                            </a>
                                            <?php endif ;?>
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
                                             <a class='tpl-table-black-operation-green j-label' href="javascript:void(0);" data-id="<?= $item['id'] ?>">
                                                <i class="iconfont icon-biaoqian"></i> 打印标签
                                            </a>
                                             <a class='tpl-table-black-operation-green j-changeaddress' href="javascript:void(0);" data-user_id="<?= $item['member_id'] ?>" data-id="<?= $item['id'] ?>">
                                                <i class="iconfont icon-dizhi"></i> 变更地址
                                            </a>
                                         </div>
                          
                                    </td>
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
                        <div class="am-fr"> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= count($list) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script id="tpl-user-item" type="text/template">
    {{ each $data }}
    <div class="file-item">
        <a href="{{ $value.avatarUrl }}" title="{{ $value.nickName }} (ID:{{ $value.user_id }})" target="_blank">
            <img src="{{ $value.avatarUrl }}">
        </a>
        <input type="hidden" name="clerk[user_id]" value="{{ $value.user_id }}">
    </div>
    {{ /each }}
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
<script id="tpl-shenhe" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        审核状态
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                          <div class="am-form-group">
                             <label class="am-radio-inline">
                                <input type="radio" name="verify[status]" value="1" data-am-ucheck checked
                                       >
                                    审核通过
                                </label>
                                <label class="am-radio-inline">
                                    <input type="radio" name="verify[status]" value="9" data-am-ucheck>
                                    拒绝通过
                                </label>
                           </div>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        备注
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                         <textarea name="verify[reason]" placeholder="拒绝时此项必填"></textarea> 
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
    $(function () {
        $('#datetimepicker').datetimepicker({
          format: 'yyyy-mm-dd hh:ii'
        });
        
        
        $('#datetimepicker').datetimepicker().on('changeDate', function(ev){
            // $('#datetimepicker').datetimepicker('hide');
          });
    });
     
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
                        title: '电子面单打印预览'
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
        
        
        $(".j-label").on('click',function(){
           var data = $(this).data();
           console.log(6543578)
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
         * 移出订单
         */
        $('.item-yichu').on('click', function () {
            var $tabs, data = $(this).data();
            console.log(data.id);
            var hedanurl = "<?= url('store/batch/yichu') ?>";
            // data.selectCount = selectIds.length;
            layer.confirm('请确定是否从改批次中移出该订单', {title: '移出订单'}
                    , function (index) {
                        $.post(hedanurl, data, function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                        layer.close(index);
                    });
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
         * 审核拼团订单
         */
        $('.j-shenhe').on('click', function () {
            var $tabs, data = $(this).data();
            $.showModal({
                title: '批量更新订单动态'
                , area: '460px'
                , content: template('tpl-shenhe', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('apps.sharing.order/verify') ?>',
                        data: {
                            verify:data
                        }
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

