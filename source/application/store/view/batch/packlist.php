<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">批次下的包裹列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                             <div class="am-u-sm-12 am-u-md-12">
                                <div class="am">
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
                                    
                                    <?php if ($type=='all'): ?>
                                    <div class="am-form-group am-fl">
                                        <?php $extractStatus = $request->get('status'); ?>
                                        <select name="status"
                                                data-am-selected="{btnSize: 'sm', placeholder: '包裹状态'}" onchange="document.getElementsByClassName('toolbar-form')[0].submit()">
                                            <option value=""></option>
                                            <option value=" "
                                                <?= $extractStatus === '' ? 'selected' : '' ?>>全部
                                            </option>
                                            <option value="1"
                                                <?= $extractStatus === '1' ? 'selected' : '' ?>>未入库
                                            </option>
                                            <option value="2"
                                                <?= $extractStatus === '2' ? 'selected' : '' ?>>已入库
                                            </option>
                                            <option value="9"
                                                <?= $extractStatus === '9' ? 'selected' : '' ?>>已发货
                                            </option>
                                            <option value="10"
                                                <?= $extractStatus === '10' ? 'selected' : '' ?>>已收货
                                            </option>
                                            <option value="11"
                                                <?= $extractStatus === '11' ? 'selected' : '' ?>>已完成
                                            </option>
                                            <option value="-1"
                                                <?= $extractStatus === '-1' ? 'selected' : '' ?>>问题件
                                            </option>
                                            <option value="12"
                                                <?= $extractStatus === '12' ? 'selected' : '' ?>>在库包裹
                                            </option>
                                            <option value="13"
                                                <?= $extractStatus === '13' ? 'selected' : '' ?>>已发货包裹
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
                                    <?php endif;?>
                                    <!--录入来源 [1小程序录入 2平台录入 3 代购同步 4 批量导入 5 PC端预报 6 拼团预报] 7预约取件 8仓管录入-->
                                    <div class="am-form-group am-fl">
                                        <select name="source"
                                                data-am-selected="{btnSize: 'sm', placeholder: '包裹来源'}">
                                            <option value="">包裹来源</option>
                                            <option value="1">小程序录入</option>
                                            <option value="2">平台录入</option>
                                            <option value="4">批量导入</option>
                                            <option value="5">H5端预报</option>
                                            <option value="6">拼团预报</option>
                                            <option value="7">预约取件</option>
                                            <option value="8">仓管录入</option>
                                        </select>
                                    </div>
                                    
                                    <div class="am-form-group am-fl">
                                        <?php $extractclassid = $request->get('class_id'); ?>
                                        <select name="class_id" id="Packcategory"
                                                data-am-selected="{btnSize: 'sm', placeholder: '包裹分类'}">
                                            <option value=""></option>
                                            <option value=""
                                                <?= $extractclassid === ' ' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php if (isset($category) && count($category)>0): foreach ($category as $item): ?>
                                            <option value="<?= $item['category_id'] ?>" <?= $item['category_id'] == $extractclassid ? 'selected' : '' ?>><?= $item['name'] ?></option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input style="padding:6px 5px;" autocomplete="off" type="text" name="start_time"
                                               class="am-form-field"
                                               value="<?= $request->get('start_time') ?>" placeholder="请选择起始日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input style="padding:6px 5px;" autocomplete="off" type="text" name="end_time"
                                               class="am-form-field"
                                               value="<?= $request->get('end_time') ?>" placeholder="请选择截止日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <textarea cols="200" rows="2"  class="am-form-field" name="express_num"
                                                   placeholder="可输入多个快递单号,按换车换行" value="<?= $request->get('express_num') ?>"></textarea>
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" style="width:200px;" class="am-form-field" name="likesearch"
                                                   placeholder="请输入包裹单号（模糊搜索）" value="<?= $request->get('likesearch') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="search"
                                                   placeholder="请输入用户昵称/ID/CODE" value="<?= $request->get('search') ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <?php if (isset($packlists) && $packlists!=''): ?>
                                            <span style="height:30px;line-height: 26px;font-size: 1.2rem;" class="am-badge am-badge-danger">未找到的包裹(<?= $i ?>)：<?= $packlists ?></span>
                                        <?php endif;?>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="page_toolbar am-margin-bottom-xs am-cf" style="margin-bottom:20px; margin-left:15px;">
                        <!--代用户打包-->
                        <?php if (checkPrivilege('package.index/inpack')): ?>
                        <?php if($type=='uninpack'): ?>
                        <button type="button" id="j-inpack" class="am-btn am-btn-secondary am-radius"><i class="iconfont icon-hebing "></i> 代用户打包</button>
                        <?php endif;?>
                        <?php endif;?>
                        <!--修改所属用户-->
                        <?php if (checkPrivilege('package.index/changeuser')): ?>
                        <button type="button" id="j-upuser" class="am-btn am-btn-success am-radius"><i class="iconfont icon-yonghu "></i> 修改所属用户</button>
                        <?php endif;?>
                        <!--修改包裹位置-->
                        <?php if (checkPrivilege('package.index/changeshelf')): ?>
                        <button type="button" id="j-change" class="am-btn am-btn-warning am-radius"><i class="iconfont icon-dingwei"></i> 修改包裹位置</button>
                        <?php endif;?>
                        <!--标记为问题件-->
                        <?php if (checkPrivilege('package.index/seterrors')): ?>
                        <?php if($type!='errors' && $type!='deletepack'): ?>
                        <button type="button" id="j-error" class="am-btn am-btn-danger am-radius"><i class="iconfont icon-htmal5icon27 "></i> 标记为问题件</button>
                        <?php endif;endif;?>
                        <!--批量删除-->
                        <?php if (checkPrivilege('package.index/deleteall')): ?>
                        <?php if($type!='deletepack'): ?>
                        <button type="button" id="j-deleteall" class="am-btn am-btn-danger am-radius"><i class="iconfont am-icon-trash"></i> 批量删除</button>
                        <?php endif;?>
                        <?php endif;?>
                        <?php if($type=='deletepack'): ?>
                        <button type="button" id="j-backall" class="am-btn am-btn-danger am-radius"><i class="iconfont am-icon-trash"></i> 批量还原</button>
                        <?php endif;?>
                        <?php if($type=='errors'): ?>
                        <button type="button" id="j-backnormal" class="am-btn am-btn-danger am-radius"><i class="iconfont am-icon-trash"></i> 还原为正常件</button>
                        <?php endif;?>
                        <!--导出-->
                        <?php if (checkPrivilege('package.index/loaddingoutexcel')): ?>
                        <button type="button" id="j-export" class="am-btn am-btn-default am-radius"><i class="iconfont icon-daochu am-margin-right-xs"></i>导出</button>
                        <?php endif;?>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th><input id="checkAll" type="checkbox" ></th>
                                <th>包裹ID</th>
                                <th width='300'>包裹预报单号/快递单号</th>
                                <th>用户昵称</th>
                                <th>仓库</th>
                                <th>运往国家</th>
                                <th>包裹信息</th>
                                <th>状态</th>
                                <th>时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody id="body">
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                             <?php $status = [ 0=>'问题件',1=>'未入库',2=>'已入库',3=>'已拣货上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已加入批次',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成','-1'=>'问题件',''=>'无状态']   ; ?>
                             <?php $taker_status = [1=>'待认领',2=>'已认领',3=>'已丢弃']; ?>
                         
                             <?php $source = [1=>'小程序预报',2=>'从平台录入','3'=>'代购单同步',4=>'批量导入','5'=>'网页端录入','6'=>'拼团','7'=>'预约取件','8'=>'仓管录入',9=>'API录入']; ?>
                                <tr>
                                    <td class="am-text-middle">
                                       <input name="checkIds" type="checkbox" value="<?= $item['id'] ?>"> 
                                    </td>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle">
                                        <?= $item['express_num'] ?> 
                                        <span style="color:#ff6666;cursor:pointer" text="<?= $item['express_num'];?>" onclick="copyUrl2(this)">[复制]</span> 
                                        <?= $item['express_name']?$item['express_name']:'' ?> </br> 
                                        <?php if($item['batch'] && $item['batch']['batch_id']) :?>
                                            所属批次：<a href="<?= url('store/batch/index', ['batch_id' => $item['batch']['batch_id']]) ?>">
                                            <?= $item['batch']['batch_name'] ?></a><br>
                                        <?php endif;?>
                                        <?php if($item['inpack_id'] && $item['inpack']['order_sn']) :?>
                                            所属订单：<a href="<?= url('store/trOrder/orderdetail', ['id' => $item['inpack']['id']]) ?>">
                                            <?= $item['inpack']['order_sn'] ?></a><br>
                                        <?php endif;?>
                                        
                                        <span class="am-badge am-badge-secondary"><?= $source[$item['source']]?></span></br>
                                        
                                        <?php if (!$item['category_attr']->isEmpty()): foreach ($item['category_attr'] as $attr): ?>
                                              <span class="am-badge am-badge-success"><?= $attr['class_name']?></span> </br>
                                        <?php endforeach;endif; ?>
                                        <?php if(isset($set['is_usermark']) && $set['is_usermark']==1 && $item['usermark'] ) :?>
                                             唛头：<?= $item['usermark']; ?></br>
                                        <?php endif;?>
                                    </td>
                                    <td class="am-text-middle">
                                        <?= $item['nickName'] ?></br>
                                     <?php if($set['usercode_mode']['is_show']!=1 ) :?>
                                    [ID:] <?= $item['member_id'] ?></br>
                                    <?php endif;?>
                                    <?php if($set['usercode_mode']['is_show']!=0 ) :?>
                                    <span>[Code:] <?= $item['user_code'] ?></span>
                                    <?php endif;?>
                                    </td>
                                    <td class="am-text-middle"><?= $item['shop_name'] ?></td>
                                    <td class="am-text-middle"><?= $item['title'] ?></td>
                                    <td class="am-text-middle">
                                        重量(<?= $set['weight_mode']['unit'] ?>):<?= $item['weight'] ?></br>
                                        体积(立方m³):<?= !empty($item['volume'])?$item['volume']:0 ?></br>
                                        
                                        价值(<?= $set['price_mode']['unit'] ?>):<?= $item['price'] ?></br>
                                    </td>
                                    <td class="am-text-middle">包裹状态:<?= $status[$item['a_status']];?></br>认领状态:<?= $taker_status[$item['is_take']];?></td>
                      
                                    <td class="am-text-middle">预报时间:<?= $item['created_time'] ?></br>更新时间:<?= $item['updated_time'] ?></br>入库时间:<?= $item['entering_warehouse_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <!--编辑-->
                                            <?php if (checkPrivilege('package.index/edit')): ?>
                                            <a href="<?= url('store/package.index/edit', ['id' => $item['id']]) ?>">
                                                <i class="am-icon-pencil"></i> 编辑
                                            </a>
                                            <?php endif;?>
                                            <!--详情-->
                                            <?php if (checkPrivilege('package.report/item')): ?>
                                            <a href="<?= url('store/package.report/item', ['id' => $item['id']]) ?>">
                                                <i class="iconfont icon-xiangqing"></i> 详情
                                            </a>
                                            <?php endif;?>
                                            <!--删除-->
                                            <?php if (checkPrivilege('package.index/delete')): ?>
                                             <a href="javascript:void(0);" class="item-delete tpl-table-black-operation-del"
                                               data-id="<?= $item['id'] ?>"> <i class="am-icon-trash"></i> 删除
                                             </a>
                                             <?php endif; ?>
                                        </div>
                                        <div class="tpl-table-black-operation" style="margin-top:10px">
                                            <?php if (checkPrivilege('package.index/delete')): ?>
                                            <a href="javascript:void(0);" 
                                                  class="item-yichu tpl-table-black-operation-del"
                                                data-id="<?= $item['id'] ?>">
                                                <i class="iconfont icon-daochu"></i> 移出批次
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
                            <div style="margin-left:10px;" class="am-vertical-align-middle">总重量(<?= $set['weight_mode']['unit'] ?>)：<?= $countweight ?></div>
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

<script id="tpl-errors" type="text/template">
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
                        标记原因
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                         <textarea rows="2" name="error[remark]" placeholder="请输入标记原因 如 包裹 长时间未入库"
                                          class="am-field-valid"></textarea>
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
               
                console.log(_data,78);
                if (sType=='shelf'){
                     var _data = res.data.shelf.data;
                    for (var i=0;i<_data.length;i++){
                        // _html += '<option value="">'+_data[i]['shelf_name']+'</option>';
                        $selected.append('<option value="' + _data[i]['id'] +'">' + _data[i]['shelf_name'] + '</option>');
                    }
                }else{
                     var _data = res.data.shelfunit.data;
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
       
        // 选择用户
        $('.j-selectUser').click(function () {
            alert(222);
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
            var options = [] ;
            $.ajax({
                type:'post',
                url:"<?= url('store/package.index/seachuserAddress') ?>",
                data:{
                    selectIds:data.selectId,
                },
                dataType:"json",
                success:function(res){
                      if (res.code==1){
                          for(var i=0;i<res.data.total;i++){
                              options[i] = "<option value ='"+ res.data.data[i].address_id+"'>"+"【用户地址】" +  res.data.data[i].name +'-'+ res.data.data[i].country +'-'+ res.data.data[i].city +'-'+ res.data.data[i].phone +"</option>"; 
                          }
			              $("#storeAddress").append(options);
                          console.log($('#storeAddress'));
                      }else{
                          layer.alert(res.msg, {icon: 5});
                      } 
                }
            })
            
           
            $.showModal({
                title: '用户打包'
                , area: '520px'
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
         * 修改会员
         */
        $('#j-error').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
                layer.alert('请先选择包裹', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            $.showModal({
                title: '标记为问题件'
                , area: '460px'
                , content: template('tpl-errors', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('/store/package.index/setErrors') ?>',
                        data: {selectIds:data.selectId},
                    });
                    return true;
                }
            });
        });
        
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
         * 批量删除
         */
        $('#j-deleteall').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            var hedanurl = "<?= url('store/package.index/deleteall') ?>";
            if (selectIds.length==0){
                layer.alert('请先选择包裹', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            console.log();
            layer.confirm('请确定是否批量删除选中包裹', {title: '批量删除包裹'}
                    , function (index) {
                        $.post(hedanurl,{selectId:data.selectId}, function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                        layer.close(index);
                    });
        });
        
        /**
         * 批量还原
         */
        $('#j-backall').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            var hedanurl = "<?= url('store/package.index/backall') ?>";
            if (selectIds.length==0){
                layer.alert('请先选择包裹', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            console.log();
            layer.confirm('请确定是否批量还原选中包裹状态', {title: '批量还原包裹'}
                    , function (index) {
                        $.post(hedanurl,{selectId:data.selectId}, function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                        layer.close(index);
                    });
        });
        
        $('#j-backnormal').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            var hedanurl = "<?= url('store/package.index/backtoNormalall') ?>";
            if (selectIds.length==0){
                layer.alert('请先选择包裹', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            console.log();
            layer.confirm('请确定是否批量还原选中包裹状态', {title: '还原成包裹'}
                    , function (index) {
                        $.post(hedanurl,{selectId:data.selectId}, function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                        layer.close(index);
                    });
        });
        
        /**
         * 移出订单
         */
        $('.item-yichu').on('click', function () {
            var $tabs, data = $(this).data();
            console.log(data.id);
            var hedanurl = "<?= url('store/batch/yichupack') ?>";
            // data.selectCount = selectIds.length;
            layer.confirm('请确定是否从改批次中移出该包裹', {title: '移出包裹'}
                    , function (index) {
                        $.post(hedanurl, data, function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                        layer.close(index);
                    });
        });

        /**
         * 导出包裹
         */
        $('#j-export').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            var serializeObj={};
            var fordata = $(".toolbar-form").serializeArray().forEach(function(item){
                if(item.name !='s'){
                   serializeObj[item.name]=item.value;
                }
                
            });
            if(isEmpty(serializeObj['search']) && selectIds.length==0){
                    layer.alert('请先选择包裹或者搜索后再点导出', {icon: 5});
                    return;
            }
            $.ajax({
                type:'post',
                url:"<?= url('store/package.index/loaddingOutExcel') ?>",
                data:{
                    selectId:selectIds,
                    seach:serializeObj
                },
                dataType:"json",
                success:function(res){
                      if (res.code==1){
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
         * 注册操作事件
         * @type {jQuery|HTMLElement}
         */
        var $dropdown = $('.j-opSelect');
        $dropdown.dropdown();
        
        // 删除元素
        var url = "<?= url('store/package.index/delete') ?>";
        $('.item-delete').delete('id', url);
    });
    
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
    
    function changecategory(_this){
        var categoryid = _this.value;
        console.log(_this.value);
        $.ajax({
            type:'post',
            url:"<?= url('store/package.index/changecategory') ?>",
            data:{
                categoryid:categoryid
            },
            dataType:"json",
            success:function(res){
                  if (res.code==1){
                    var $selected = $("#Packcategory");
                    $selected.html('');
                    var cate = res.data;
       
                    for (var i=0;i<cate.length;i++){
                        $selected.append('<option value="' + cate[i]['category_id'] +'" >' + cate[i]['name'] + '</option>');
                    }
                    // $("#Packcategory").html();
                  } 
            }
        })
    }
    
    function isEmpty(val){
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
                    isEmpty = val + "" === "null" || val + "" === "undefined" || val.length <= 0 ||  val.split("").length <= 0 ? true : false;
                  } catch (error) {
                    isEmpty = false;
                  };
                  break;
            case "[object Object]":
              try {
                let temp = JSON.stringify(val);
                isEmpty = temp + "" === "null" || temp + "" === "undefined" || temp === "{}" ? true : false;
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
        }

</script>

