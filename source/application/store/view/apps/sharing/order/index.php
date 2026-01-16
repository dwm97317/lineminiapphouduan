<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">拼团列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-12">
                                <div class="am fl">
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="order_sn"
                                                   placeholder="请输入单号" value="<?= $request->get('order_sn') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <?php $extractStatus = $request->get('status'); ?>
                                        <select name="status"
                                                data-am-selected="{btnSize: 'sm', placeholder: '订单状态'}">
                                            <option value=""></option>
                                            <option value="-1,1,2,3,4,5,6,7,8,9"
                                                <?= $extractStatus === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <option value="1"
                                                <?= $extractStatus === '1' ? 'selected' : '' ?>>开团中
                                            </option>
                                            <!--1 状态 1 待查验 2 待支付 3 已支付 4 拣货中 5 已打包  6已发货 7 已到货 8 已完成  9已取消-->
                                            <option value="2"
                                                <?= $extractStatus === '2' ? 'selected' : '' ?>>待查验
                                            </option>
                                            <option value="3"
                                                <?= $extractStatus === '3' ? 'selected' : '' ?>>待打包
                                            </option>
                                            <option value="4"
                                                <?= $extractStatus === '4' ? 'selected' : '' ?>>待付款
                                            </option>
                                            <option value="5"
                                                <?= $extractStatus === '5' ? 'selected' : '' ?>>待发货
                                            </option>
                                            <option value="6"
                                                <?= $extractStatus === '6' ? 'selected' : '' ?>>已发货
                                            </option>
                                            <option value="7"
                                                <?= $extractStatus === '7' ? 'selected' : '' ?>>已完成
                                            </option>
                                            <option value="8"
                                                <?= $extractStatus === '8' ? 'selected' : '' ?>>已取消
                                            </option>
                                        </select>
                                    </div>
                                    
                                    <div class="am-form-group am-fl">
                                        <?php $extractShopId = $request->get('extract_shop_id'); ?>
                                        <select name="extract_shop_id"
                                                data-am-selected="{btnSize: 'sm', placeholder: '仓库名称'}">
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
                                            <input type="text" class="am-form-field" name="user_id"
                                                   placeholder="请输入用户ID" value="<?= $request->get('user_id') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
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
                   <div class="page_toolbar am-margin-bottom-xs am-cf" style="margin-bottom:20px; margin-left:15px;">
                            <div class="am-btn-group am-btn-group-xs">
                            <?php if (checkPrivilege('apps.sharing.order/add')): ?>
                                <a class="am-btn am-btn-default am-btn-success" href="<?= url('store/apps.sharing.order/add') ?>">
                                    <span class="am-icon-plus"></span> 新增</a>
                            <?php endif; ?>
                            </div>
                            <?php if (checkPrivilege('apps.sharing.order/changestatus')): ?>
                            <button type="button" id="j-wuliu" class="am-btn am-btn-warning am-radius"> <i class="iconfont icon-guojiwuliu"></i>  更新拼团订单动态</button>
                            <?php endif; ?>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th><input id="checkAll" type="checkbox"></th>
                                <th>拼团订单号</th>
                                <th>团长</th>
                                <th>转运信息</th>
                                <th>国家</th>
                                <th>拼团地址</th>
                                <th>状态</th>
                                <th>时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody id="body">
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                            <?php $status = [1=>'开团中',2=>'待查验',3=>'待打包','4'=>'待付款','5'=>'待发货','6'=>'已发货','7'=>'已完成','8'=>'已取消','-1'=>'已取消']; ?>
                                <tr>
                                    <td class="am-text-middle">
                                       <input name="checkIds" type="checkbox" value="<?= $item['order_id'] ?>"> 
                                    </td>
                                    <td class="am-text-middle">
                                        <?= $item['order_sn'] ?><br>
                                        <?php if ($item['inpack_id']!=0): ?> 
                                        <span class="am-badge am-badge-secondary">国际运单号：<?= $item['inpack_id']; ?></span>
                                        <?php endif ;?>
                               
                                    </td>
                                    <td class="am-text-middle">
                                        用户ID:<?= $item['member_id']; ?> </br> 
                                        <?php if($setcode['is_show']!=0) :?> 
                                        <span>用户Code:<?= $item['user']['user_code']; ?></span></br>
                                        <?php endif;?>
                                        用户昵称:<?= $item['user']['nickName']; ?>
                                    </td>
                                    <td class="am-text-middle">
                                        拼团线路：<?= $item['line']['name']; ?></br> 
                                        仓库名称：<?= $item['storage']['shop_name']; ?></br></br> 
                                        共有 <?= $item['count'] ?> 个集运单 </br>
                                        <a href="<?= url('/store/apps.sharing.order/inpacklist', ['order_id' => $item['order_id']]) ?>">查看拼单明细</a>
                                    </td>
                                    <td class="am-text-middle">国家ID:<?= $item['country_id']; ?> </br> 国家名称:<?= $item['country']['title']; ?></br></td>
                                   <td class="am-text-middle">
                                        用户ID:<?= $item['member_id']; ?> </br>
                                        <?php if($setcode['is_show']!=0) :?> 
                                            用户Code:<?= $item['user']['user_code']; ?>
                                        <?php endif;?>
                                        收件人:<?= $item['address']['name'] ?>
                                        <span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['name'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        
                                        电话:<?= $item['address']['phone'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['phone'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php if ($set['is_identitycard']==1): ?> 
                                        身份证:<?= $item['address']['identitycard'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['identitycard'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        <?php if ($set['is_clearancecode']==1): ?> 
                                        通关代码:<?= $item['address']['clearancecode'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['clearancecode'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        寄往国家：<?= $item['address']['country'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['country'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        
                                        <?php if ($set['is_province']==1): ?> 
                                        省/州：<?= $item['address']['province'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['province'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
                                        <?php if ($set['is_city']==1): ?> 
                                        市：<?= $item['address']['city'] ?><span style="color:#ff6666;cursor:pointer" text="<?= $item['address']['city'];?>" onclick="copyUrl2(this)">[复制]</span></br>
                                        <?php endif ;?>
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
                                    <td class="am-text-middle"><?= $status[$item['status']['value']] ?></td>
                                    <td class="am-text-middle">
                                        开始时间：<?= date("Y-m-d",$item['start_time']); ?><br>
                                        结束时间：<?= date("Y-m-d",$item['end_time']); ?>
                                    </td>
                                    
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('apps.sharing.order/edit')): ?>
                                            <a href="<?= url('store/apps.sharing.order/edit', ['order_id' => $item['order_id']]) ?>">
                                                <i class="am-icon-pencil"></i> 编辑
                                            </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('apps.sharing.order/delivery')): ?>
                                            <?php if (in_array($item['status']['value'],[1,2,3,4,5])): ?>
                                             <a href="<?= url('apps.sharing.order/delivery', ['id' => $item['order_id']]) ?>">
                                                <i class="iconfont icon-baoguo_fahuo_o"></i> 发货
                                            </a>
                                            <?php endif ;?>
                                            <?php endif; ?>
                                             <?php if ($item['is_verify'] == 2): ?>
                                            <a class="shenhe" href="javascript:void(0);" data-id="<?= $item['order_id'] ?>">通过</a>
                                            <?php endif; ?>
                                            <?php if ($item['is_verify'] == 3): ?>
                                            <a class="shenhe" href="javascript:void(0);" data-id="<?= $item['order_id'] ?>">未通过</a>
                                            <?php endif; ?>
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


<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
 $(function () {
        // 删除元素
        var url = "<?= url('store/trOrder/orderdelete') ?>";
        $('.item-delete').delete('id', url);
         /**
         * 审核操作状态
         */
        $('.shenhe').on('click', function(){
            var $tabs, data = $(this).data();
            // console.log(data.id,88888);
            $.showModal({
                title: '订单审核'
                , area: '460px'
                , content: template('tpl-status', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('apps.sharing.order/verify') ?>',
                        data: {
                            id:data.id
                        }
                    });
                    return true;
                }
            });
        });
        

        
        
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
         * 批量手动更新物流信息
         */
        $('#j-wuliu').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
               layer.alert('请先选择拼团订单', {icon: 5});
                return;
            }
            if (selectIds.length>1){
               layer.alert('只能选择一个订单', {icon: 5});
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
                        url: '<?= url('apps.sharing.order/alllogistics') ?>',
                        data: {
                            selectIds:data.selectId
                        }
                    });
                    return true;
                }
            });
        });      
 });
    
</script>

