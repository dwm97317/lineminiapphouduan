<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">货位列表</div>
                </div>
                <div class="widget-body am-fr">
                     <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                             <div class="am-u-sm-12 am-u-md-12">
                                <div class="am">
                                    <div class="am-form-group am-fl">
                                        <?php $extractShopId = $request->get('shelf_id'); ?>
                                        <select name="shelf_id"
                                                data-am-selected="{btnSize: 'sm', placeholder: '所属货架'}">
                                            <option value=""></option>
                                            <option value=" "
                                                <?= $extractShopId === ' ' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php if (isset($list)): foreach ($list as $items): ?>
                                                <option value="<?= $items['id'] ?>"
                                                    <?= $items['id'] == $extractShopId ? 'selected' : '' ?>><?= $items['shelf_name'] ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                     <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="express_num"
                                                   placeholder="请输入快递单号" value="<?= $request->get('express_num') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="search"
                                                   placeholder="请输入货位ID" value="<?= $request->get('search') ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <button type="button" class="am-btn am-btn-default" style="height:33px;line-height:15px;" data-id="" onclick="tools.init(this)" data-mode='request' data-confirm=true data-confirm_text='请确认是否全部下架！' data-refresh=true data-url='<?= url('store/shelf_manager.index/deleteAllShelfUnit') ?>'>
                                              <i class="am-icon-download"></i>
                                              一键下架
                                        </button>
                                       
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <?php if (checkPrivilege('shop.shelf/reset')): ?>
                                <button type="button" id="j-created" class="am-btn am-btn-secondary am-radius">重置货位码</button>
                                <?php endif; ?>
                                <?php if (checkPrivilege('shop.shelf/download')): ?>
                                <button type="button" id="j-download" class="am-btn am-btn-secondary am-radius">批量下载</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th><input id="checkAll" type="checkbox"></th>
                                <th width='100px'>货位ID</th>
                                <th width='100px'>货位编号</th>
                                <th width='150px'>所属货架</th>
                                <th width='200px'>货位码</th>
                                <th>货位数据</th>
                                <th width='150px'>操作</th>
                            </tr>
                            </thead>
                            <tbody id="body">
                            <?php if (!$data->isEmpty()): ?>
                                <?php foreach ($data as $item): ?>
                                    <tr>
                                        <td class="am-text-middle">
                                            <input name="checkIds" type="checkbox" value="<?= $item['shelf_unit_id'] ?>"> 
                                        </td>
                                        <td class="am-text-middle"><?= $item['shelf_unit_id'] ?></td>
                                        <td class="am-text-middle"><?= $item['shelf_unit_no'] ?></td>
                                        <td class="am-text-middle"><?= $item['shelf']['shelf_name'] ?></td>
                                        <td class="am-text-middle"><a target="_blank" href="/<?= $item['shelf_unit_qrcode'];?>"><img width="30" height="30" src="/<?= $item['shelf_unit_qrcode'];?>" ></a></td>
                                        <td class="am-text-middle"><?php if (isset($item['shelfunititem']) && !$item['shelfunititem']->isEmpty() ): ?><?php foreach($item['shelfunititem'] as $_item):?>
                                            包裹单号:<?= $_item['express_num']; ?> 
                                            [UID]:<?= $_item['user_id'] ;?>  上架时间：<?= $_item['created_time'] ;?></br>
                                            <?php endforeach ;?>
                                            <?php else: ?> 货位空空如也 <?php endif; ?>
                                        </td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('shelf_manager.index/deleteshelfunititem')): ?>
                                                <?php if(isset($item['shelfunititem']) && !$item['shelfunititem']->isEmpty() ) :?>
                                                    <a href="javascript:;"
                                                       class="item-delete tpl-table-black-operation-del j-xiajia"
                                                       data-id="<?= $item['shelf_unit_id'] ?>"
                                                        <i class="am-icon-trash"></i> 全部下架
                                                    </a>
                                                <?php endif ;?>    
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $data->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $data->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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
        
        $("#j-download").on('click',function(){
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
                layer.alert('请先选择要导出的项目', {icon: 5});
                return;
            }
            $.ajax({
				type: 'post',
				url: "<?= url('/store/shop.shelf/download') ?>",
				data: {
					selectId: selectIds
				},
				dataType: "json",
				success: function(res) {
					if (res.code == 1) {
						console.log(res.url.file_name);
						var a = document.createElement('a');
						document.body.appendChild(a);
						a.href = res.msg;
						a.click();
					}
				}
		   })
        })
     
      $("#j-created").on('click',function(){
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
                layer.alert('请先选择要生成二维码的项目', {icon: 5});
                return;
            }
            $.ajax({
				type: 'post',
				url: "<?= url('/store/shop.shelf/reset') ?>",
				data: {
					selectId: selectIds
				},
				dataType: "json",
				success: function(res) {
					if (res.code == 1) {
					    $.show_success('生成成功');
					    setTimeout(()=>{
					        window.location.reload();
					    },1000);
					}
				}
		   })
        })
        
        $(".j-xiajia").on('click',function(){
            var id = $(this).data();
            console.log(id,8899)
            $.ajax({
				type: 'post',
				url: "<?= url('store/shelf_manager.index/deleteShelfUnitItem') ?>",
				data: {
					id
				},
				dataType: "json",
				success: function(res) {
					if (res.code == 1) {
                        window.location.reload();
					}
				}
		   })
        })
     })
     
</script>

