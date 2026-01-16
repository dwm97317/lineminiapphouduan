<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">审核列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-12">
                                <div class="am fr">
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
                                        <select name="state"
                                                data-am-selected="{btnSize: 'sm', placeholder: '处理状态'}">
                                            <option value=""></option>
                                            <option value="0" <?= $request->get('state') === '0' ? 'selected' : '' ?>>
                                                进行中
                                            </option>
                                            <option value="10" <?= $request->get('state') === '10' ? 'selected' : '' ?>>
                                                已拒绝
                                            </option>
                                            <option value="20" <?= $request->get('state') === '20' ? 'selected' : '' ?>>
                                                已完成
                                            </option>
                                        </select>
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
                    <div class="order-list am-scrollable-horizontal am-u-sm-12 am-margin-top-xs">
                        <table width="100%" class="am-table am-table-centered
                        am-text-nowrap am-margin-bottom-xs">
                            <thead>
                            <tr>
                                 <th><input id="checkAll" type="checkbox"></th>
                                <th width="24%" class="goods-detail">用户信息</th>
                                <th width="10%">手机号</th>
                                <th width="15%">真实姓名</th>
                                <th>身份证</th>
                                <th>处理状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody id="body">
                            <?php $map = [1=>'已通过',2=>'待审核',4=>'未通过']; ?>    
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr class="order-empty">
                                    <td colspan="7"></td>
                                </tr>
                                <tr>
                                 
                                </tr>
                                <tr>
                                     <td class="am-text-middle">
                                       <input name="checkIds" type="checkbox" value="<?= $item['id'] ?>"> 
                                    </td>
                                    <td class="goods-detail am-text-middle">
                                        <div class="goods-image">
                                            <img src="<?= $item['user']['avatarUrl'] ?>"
                                                 alt="">
                                        </div>
                                        <div class="goods-info">
                                            <p class="goods-title"><?= $item['user']['user_id'] ?></p>
                                            <p class="goods-spec am-link-muted"><?= $item['user']['nickName'] ?></p>
                                        </div>
                                    </td>
                                    <td class="am-text-middle">
                                        <?= $item['mobile'] ?>
                                    </td>
                                    <td class="am-text-middle">
                                       <?= $item['truename'] ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <?= $item['idcard'] ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <span class="am-badge am-badge-secondary"><?= $map[$item['status']]; ?></span>
                                    </td>
                                    <td class="am-text-middle">
                                         <?php if (checkPrivilege('apps.sharing.verify/users')): ?>
                                                    <a class="tpl-table-black-operation-default"
                                                       href="javascript:void()" id="j-verify" data-id="<?= $item['id']; ?>">
                                                        <i class="iconfont icon-shenhe"></i> 审核操作
                                                    </a>
                                                <?php endif; ?>
                                    </td>
                                   
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="7" class="am-text-center">暂无记录</td>
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
                        选择审核数量
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                       <p class='am-form-static'> 共选中 {{ selectCount }} 用户</p>
                    </div>
                </div>
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
                                    <input type="radio" name="verify[status]" value="3" data-am-ucheck>
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
         * 审核操作状态
         */
        $('#j-verify').on('click', function(){
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            selectIds.push(data.id);
            if (selectIds.length==0){
                layer.alert('请先选择审核单', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            $.showModal({
                title: '修改审核状态'
                , area: '460px'
                , content: template('tpl-status', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('apps.sharing.verify/update') ?>',
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

