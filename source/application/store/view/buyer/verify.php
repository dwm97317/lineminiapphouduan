<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">代购单列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-12">
                                <div class="am">
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="start_time"
                                               class="am-form-field"
                                               value="<?= $request->get('start_time') ?>" placeholder="请选择起始日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="end_time"
                                               class="am-form-field"
                                               value="<?= $request->get('end_time') ?>" placeholder="请选择截止日期"
                                               data-am-datepicker>
                                    </div>
                                     
                                     <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="express_num"
                                                   placeholder="请输入代购单号" value="<?= $request->get('express_num') ?>">
                                        </div>
                                    </div>
                                     <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="batch"
                                                   placeholder="请输入批次号" value="<?= $request->get('batch') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="search"
                                                   placeholder="请输入用户昵称/ID" value="<?= $request->get('search') ?>">
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
                     <div class="am-form-group am-fl">
                           <div class="page_toolbar am-margin-bottom-xs am-cf" style="margin-bottom:20px; margin-left:15px;">
                            <button type="button" id="j-upstatus" class="am-btn am-btn-success am-radius">状态变更</button>
                            <button type="button" id="j-refund" class="am-btn am-btn-success am-radius">批量退款</button>
                          </div>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th><input id="checkAll" type="checkbox" ></th>
                                <th>代购单ID</th>
                                <th>代购单号/批次号</th>
                                <th>用户信息</th>  
                                <th>代购信息</th>
                                <th>费用清单</th>
                                <th>实付金额</th>
                                <th>物流信息</th>
                                <!--<th>审核反馈-用户备注-用户反馈</th>-->
                                <th>状态</th>
                                <th>时间</th>
                            </tr>
                            </thead>
                            <tbody id="body">
                             <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <?php $status = ['-1'=>'已取消',1=>'待审核',2=>'待支付',3=>'待购买',4=>'已购买',5=>'已确认',8=>'已退款']; ?>
                                <tr>
                                    <td>
                                       <input name="checkIds" type="checkbox" value="<?= $item['b_order_id'] ?>" > 
                                    </td>
                                    <td><?= $item['b_order_id'] ?></td>
                                    <td><?= $item['order_sn'] ?></br><span style="color:#ff6600;"><?= $item['batch'] ?></span></td>
                                    <th><?= $item['member']['nickName'] ?> <br />[UID] <?= $item['member_id'] ?></th>  
                                    <td width='200'>购买链接 :<a href="<?= $item['url'] ?>" target="_blank">点击链接购买 </a></br> 
                                                    购买平台 :<?= $item['palform'] ?></br> 
                                                    规格型号：<?= $item['spec'] ?></td>
                                     <td>商品单价：<?= $item['price'] ?> <br/> 
                                        快递费：<?= $item['free'] ?> <br/> 
                                        代购数量：<?= $item['num'] ?> 
                                         </td>
                                    <td>商品金额：<?= $item['real_payment'];?> <br/>
                                        服务费：  <?= $item['service_free'] ?>
                                    </td>
                                    <td><?= $item['reason']??'' ?> - <?= $item['remark']??'' ?> - <?= $item['feedback']??'' ?></td>
                                     <td><?= $status[$item['status']];?><?php if($item['status']==-1 || $item['status']==8): ?><br/><span style="color:#ff6600;">已退款(不含服务费)<?=$item['total_free']; ?>元 - 已退服务费 <?=$item['refund_service']; ?>元</span><?php endif; ?></td>
                                    <td>预报时间:<?= $item['created_time'] ?></br>更新时间:<?= $item['updated_time'] ?></td>
                                    <td class="am-text-middle">
                                        <?php if (($item['status']==-1 || $item['status'] == 8) && $item['rufund_step']==2): ?>
                                            <a href="<?= url('store/buyer/refund_service', ['id' => $item['b_order_id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 退服务费
                                                        </a>
                                        <?php endif ;?>                
                                        
                                          <a href="<?= url('store/buyer/detail', ['id' => $item['b_order_id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 详情
                                                        </a>
                                    
                                        <a href="<?= url('store/buyer/edit', ['id' => $item['b_order_id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 编辑
                                                        </a>
                                                
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
<script id="tpl-status" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
               <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择代购订单
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                       <p class='am-form-static'> 共选中 {{selectCount}} 代购订单</p>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择状态
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                          <select name="order[status]"
                                data-am-selected="{btnSize: 'sm', placeholder: '请选择线路'}">
                               <option value="2">已确认</option>
                               <option value="-1">未确认</option>
                        </select>
                        <div class="help-block" style="color:red;">
                                        <small>本退款（款项包含 代购费 （物品价值）+ 运费 ）；服务费另行处理</small>
                                </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>
<script id="tpl-refund" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
               <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择代购订单
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                       <p class='am-form-static'> 共选中 {{selectCount}} 代购订单</p>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        确定退款
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                          <select name="order[status]"
                                data-am-selected="{btnSize: 'sm', placeholder: '请选择线路'}">
                               <option value="1">已确认</option>
                               <option value="0">未确认</option>
                        </select>
                        <div class="help-block" style="color:red!important;">
                                        <small>本退款（款项包含 代购费 （物品价值）+ 运费 ）；服务费另行处理</small>
                                </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
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
      
        
          /**
         * 注册操作事件
         * @type {jQuery|HTMLElement}
         */
        var $dropdown = $('.j-opSelect');
        $dropdown.dropdown();
    });
    
    /**
      * 修改入库状态
      */
    $('#j-upstatus').on('click', function () {
        var $tabs, data = $(this).data();
        var selectIds = checker.getCheckSelect();
        if (selectIds.length==0){
            dialog.toast('warn','请先选择包裹');
            return;
        }
        data.selectId = selectIds.join(',');
        data.selectCount = selectIds.length;
        $.showModal({
            title: '修改入库状态'
            , area: '460px'
            , content: template('tpl-status', data)
            , uCheck: true
            , success: function ($content) {
            }
            , yes: function ($content) {
                $content.find('form').myAjaxSubmit({
                    url: '<?= url('store/buyer/upsatatus') ?>',
                    data: {
                        selectIds:data.selectId
                    }
                });
                return true;
            }
        });
    });
    
    /**
      * 修改入库状态
      */
    $('#j-refund').on('click', function () {
        var $tabs, data = $(this).data();
        var selectIds = checker.getCheckSelect();
        if (selectIds.length==0){
            dialog.toast('warn','请先选择订单');
            return;
        }
        data.selectId = selectIds.join(',');
        data.selectCount = selectIds.length;
        $.showModal({
            title: '批量退款'
            , area: '460px'
            , content: template('tpl-refund', data)
            , uCheck: true
            , success: function ($content) {
            }
            , yes: function ($content) {
                $content.find('form').myAjaxSubmit({
                    url: '<?= url('store/buyer/refund') ?>',
                    data: {
                        selectIds:data.selectId
                    }
                });
                return true;
            }
        });
    });
        
    
    function doSelectUser(){
           var $userList = $('.user-list');
            $.selectData({
                title: '选择用户',
                uri: 'user/lists',
                dataIndex: 'user_id',
                done: function (data) {
                    var user = [data[0]];
                    $userList.html(template('tpl-user-item', user));
                }
            });
    }
</script>

