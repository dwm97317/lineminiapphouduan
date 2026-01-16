<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">待认领</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                                 <div class="am-u-sm-12 am-u-md-12">
                                <div class="am">
                                   
                                    <div class="am-form-group am-fl">
                                        <?php $extractShopId = $request->get('extract_shop_id'); ?>
                                        <select name="extract_shop_id"
                                                data-am-selected="{btnSize: 'sm', placeholder: '仓库名称'}">
                                            <option value=""></option>
                                            <option value=" "
                                                <?= $extractShopId === ' ' ? 'selected' : '' ?>>全部
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
                                           <input type="text" class="am-form-field" name="express_num"
                                                   placeholder="请输入快递单号" value="<?= $request->get('express_num') ?>">
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
                        <!--修改所属用户-->
                        <?php if (checkPrivilege('package.index/changeUser')): ?>
                        <button type="button" id="j-upuser" class="am-btn am-btn-success am-radius"><i class="iconfont icon-yonghu "></i> 修改所属用户</button>
                        <?php endif;?>
                        <!--修改包裹位置-->
                        <?php if (checkPrivilege('package.index/changeShelf')): ?>
                        <button type="button" id="j-change" class="am-btn am-btn-warning am-radius"><i class="iconfont icon-dingwei "></i> 修改包裹位置</button>
                        <?php endif;?>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                
                                <th><input id="checkAll" type="checkbox" ></th>
                                <th>包裹ID</th>
                                <th>包裹预报单号/快递单号</th>
                                <th>包裹预览图</th>
                                <th>仓库</th>
                                <th>运往国家</th>
                                <th>包裹信息</th>
                                <th>备注</th>
                                <th>状态</th>
                                <th>时间</th>
                            </tr>
                            </thead>
                            <tbody id="body">
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                             <?php $status = [-1=>'问题件',1=>'未入库',2=>'已入库',3=>'已拣货上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成']; ?>
                             <?php $taker_status = [1=>'待认领',2=>'已认领',3=>'已丢弃']; ?>
                             <?php $source = [1=>'小程序预报',2=>'从平台录入','3'=>'代购单同步',4=>'批量导入','5'=>'PC','6'=>'拼团','7'=>'预约取件','8'=>'仓管录入',9=>'API录入']; ?>
                                <tr>
                                    <td class="am-text-middle">
                                       <input name="checkIds" type="checkbox" value="<?= $item['id'] ?>"  > 
                                    </td>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle"><?= $item['order_sn'] ?><br>
                                    <?= $item['express_num'] ?> <span style="color:#ff6666;cursor:pointer" text="<?= $item['express_num'];?>" onclick="copyUrl2(this)">[复制]</span> <?= $item['express_name']?$item['express_name']:'' ?> </br> <span class="am-badge am-badge-secondary">
                                        <?= $source[$item['source']]?></span>
                                        <?php if (!$item['category_attr']->isEmpty()): foreach ($item['category_attr'] as $attr): ?>
                                              <span class="am-badge am-badge-success"><?= $attr['class_name']?></span> 
                                        <?php endforeach;endif; ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <figure style="display:inline-flex;" data-am-widget="figure" class="am am-figure am-figure-default "   data-am-figure="{  pureview: 'true' }">
                                            <?php if (!$item['packageimage']->isEmpty()): foreach ($item['packageimage'] as $itemd): ?>
                                            <a href="<?= $itemd['file_path'] ?>" title="点击查看大图" target="_blank">
                                                <img src="<?= $itemd['file_path'] ?>" width="50" height="50" alt="评论图片">
                                            </a>
                                            <?php endforeach;endif; ?>
                                        </figure>
                                    </td>
                                    <td class="am-text-middle"><?= $item['shop_name'] ?></td>
                                    <td class="am-text-middle"><?= $item['title'] ?></td>
                                    <td class="am-text-middle">长:<?= $item['length']?></br>宽:<?= $item['width']?></br>高:<?= $item['height']?></br>称重:<?= $item['weight']?></br>物品属性:<?= implode(',',json_decode($item['goods_attr'],true)??[])?></td>
                                    <td class="am-text-middle"><?= $item['remark'] ?></td>
                                    <td class="am-text-middle">包裹状态:<?= $status[$item['a_status']];?></br>认领状态:<?= $taker_status[$item['is_take']];?></td>
                                    <td class="am-text-middle"></td>
                                    <td class="am-text-middle">预报时间:<?= $item['created_time'] ?></br>更新时间:<?= $item['updated_time'] ?></br>入库时间:<?= $item['entering_warehouse_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <!--详情-->
                                            <?php if (checkPrivilege('package.report/item')): ?>
                                            <a href="<?= url('store/package.report/item', ['id' => $item['id']]) ?>">
                                                <i class="iconfont icon-xiangqing"></i> 详情
                                            </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('package.index/delete')): ?>
                                            <a href="javascript:void(0);"
                                               class="item-delete tpl-table-black-operation-del"
                                               data-id="<?= $item['id'] ?>">
                                                <i class="am-icon-trash"></i> 删除
                                            </a>
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

<script id="tpl-user-item" type="text/template">
    {{ each $data }}
    <div class="file-item">
        <a href="{{ $value.avatarUrl }}" title="{{ $value.nickName }} (ID:{{ $value.user_id }})" target="_blank">
            <img src="{{ $value.avatarUrl }}">
        </a>
        <input type="hidden" name="package[user_id]" value="{{ $value.user_id }}">
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
                                                class="j-selectUser upload-file am-btn am-btn-secondary am-radius"  onclick="doSelectUser()">
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

<script id="tpl-shelf" type="text/template">
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
                        选择仓库
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                          <select name="shelf[shop_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" onchange="getSelectData(this)" data-select_type='shelf'>
                                        <option value="">请选择</option>
                                        <?php if (isset($shopList) && !$shopList->isEmpty()):
                                            foreach ($shopList as $item): ?>
                                                <option value="<?= $item['shop_id'] ?>"><?= $item['shop_name'] ?></option>
                                            <?php endforeach; endif; ?>
                                    </select>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择货架
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                         <select id="select-shelf" data-select_type = 'shelf_unit'
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'选择货架', maxHeight: 400}" onchange="getSelectData(this)" data-select_type='shelf'>
                                        <option value=""></option>
                                    </select> 
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择货位
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                         <select id="select_shelf_unit" name="shelf[shelf_unit]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择货位', maxHeight: 400}">
                                        <option value=""></option>
                                    </select>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
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
                    console.log($selected,'$selected');
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
        var url = "<?= url('store/package.index/delete') ?>";
        $('.item-delete').delete('id', url);


     /**
         * 修改包裹位置
         */
        $('#j-change').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
                layer.alert('请先选择包裹', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            $.showModal({
                title: '修改包裹位置'
                , area: '460px'
                , content: template('tpl-shelf', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('/store/package.index/changeShelf') ?>',
                        data: {selectIds:data.selectId},
                    });
                    return true;
                }
            });
        }); 
        
      
    
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
                        url: '<?= url('/store/package.index/changeUser') ?>',
                        data: {selectIds:data.selectId}
                    });
                    return true;
                }
            });
        });
        
        
      /**
         * 代用户打包
         */
        $('#j-inpack').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
                layer.alert('请先选择包裹', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            $.showModal({
                title: '用户打包'
                , area: '460px'
                , content: template('tpl-inpack', data)
                , uCheck: true
                , success: function ($content) {
                    $tabs = $content.find('.j-tabs');
                    $tabs.tabs({noSwipe: 1});
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('/store/package.index/inpack') ?>',
                        data: {
                            selectIds:data.selectId,
                        }
                    });
                    return true;
                }
            });
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

