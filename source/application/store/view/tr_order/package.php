<style>
      .tip-layer { width: 250px; border-radius:10px; height:100px; background:#fff; box-shadow:1px 1px 1px 1px #ccc; position:absolute;top:20px; right:-250px; transition: .4s;
    -webkit-transition: .4s; /* Safari */}
    .tip-layer-content { font-size:13px; padding:10px; padding-top:5px; color:#666;}
    .tip-layer-title { width:96%; padding-left:4%; font-size:14px; height:40px; line-height:40px;}
    .layer-tips-area { position:absolute; top:10%; width:300px; right:0;}
    .err { color:red;}
    .success { color:green;}
</style>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">包裹列表</div>
                </div>
                <div class="widget-body am-fr">
                   
                    <div class="page_toolbar am-margin-bottom-xs am-cf am-fl" >
                        <?php if (checkPrivilege('tr_order/add')): ?>
                                        <div class="am-btn-group am-btn-group-xs">
                                            <a class="am-btn am-btn-default am-btn-success"
                                               href="<?= url('tr_order/add') ?>&id=<?= $id;?>">
                                                <span class="am-icon-plus"></span> 新增
                                            </a>
                                        </div>
                                    <?php endif; ?>
                        <button type="button" id="j-yichu" class="am-btn am-btn-secondary am-radius">批量移出</button>
                        <button type="button" id="j-caihe" class="am-btn am-btn-danger  am-radius">拆包合包</button>
                        
                    </div>
                    <div class="page_toolbar am-margin-bottom-xs am-cf am-fr" >
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                             <div class="am-u-sm-12 am-u-md-9">
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                          <input style="width:300px" autocomplete='off' type="text" class="am-form-field" name="search"
                                                   placeholder="出库单号输入" id="keys" value="<?= $request->get('search') ?>">
                                            <div class="am-input-group-btn">
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th><input id="checkAll" type="checkbox"></th>
                                <th>包裹ID</th>
                                <th>快递单号</th>
                                <th>包裹图片</th>
                                <th>包裹信息</th>
                                <th>备注信息</th>
                                <th>包裹位置</th>
                                <th>状态</th>
                                <th>扫描状态</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody id="body">
                                <?php $status = [1=>'未入库',2=>'已入库',3=>'已拣货上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成']; $is_scan=[1=>'未扫描',2=>'已扫描'] ;?>
                            <?php if (!$list->isEmpty()): ?>
                                <?php foreach ($list as $item): ?>
                                    <tr id="tr_<?= $item['express_num'] ?>">
                                        <td class="am-text-middle">
                                             <input name="checkIds" type="checkbox" value="<?= $item['id'];?>" id="<?= $id;?>"> 
                                        </td>
                                        <td class="am-text-middle"><?= $item['id'] ?></td>
                                        <td class="am-text-middle"><?= $item['express_num'] ?></td>
                                        <td class="am-text-middle">
                                            <figure style="display:inline-flex;" data-am-widget="figure" class="am am-figure am-figure-default "   data-am-figure="{  pureview: 'true' }">
                                            <?php if (!$item['packageimage']->isEmpty()): foreach ($item['packageimage'] as $itemd): ?>
                                            <a href="<?= $itemd['file_path'] ?>" title="点击查看大图" target="_blank">
                                                <img src="<?= $itemd['file_path'] ?>" width="50" height="50" alt="评论图片">
                                            </a>
                                            <?php endforeach;endif; ?>
                                            </figure>
                                        </td>
                                        <td class="am-text-middle">
                                            <?php foreach ($item['pakitem'] as $items): ?>
                                            类别：<?= $items['class_name'] ?><br>
                                            <?php endforeach; ?>
                                        </td>
                                         <td class="am-text-middle"><?= $item['remark'];?></td>
                                        <td class="am-text-middle"><?php if($item['shelf']):?> <?= $item['shelf']; ?><?php else :?>包裹不在货架上<?php endif;?></td>
                                        <td class="am-text-middle"><?= $status[$item['status']];?></td>
                                        <td  <?php if ($item['is_scan']==2):?>style="color:red;"<?php endif; ?>class="am-text-middle scan">
                                        <?= $is_scan[$item['is_scan']];?></td>
                                        <td class="am-text-middle"><?= $item['created_time']; ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation"  >
                                              <a href="javascript:void(0);" 
                                               class="j-yichue item-delete tpl-table-black-operation-del"
                                               data-id="<?= $item['id'];?>" data-value="<?= $id;?>" > <i class="am-icon-delete"></i> 移出
                                            </a>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="layer-tips-area"></div>
<script>
    var pack_id = "<?= $id ;?>"
    // 修改数据
    function changeData(express_num){
     var tr = document.getElementById('tr_'+express_num);
     var scan =tr.getElementsByClassName("scan")[0];
     scan.innerHTML = '已扫描';
     scan.style.color= 'red';
     console.log(scan,55555555);
    }
    
    

   
    // 渲染 提示窗口
    function renderTips(content){
        var layerTipsArea = document.getElementsByClassName('layer-tips-area')[0];
        var tipLayers = document.getElementsByClassName('tip-layer');
        var top = tipLayers.length * 100 +30;
        var tipLayer = document.createElement('div');
        tipLayer.className = 'tip-layer';
        tipLayer.style.top = top+'px';
        var opTitle = $("input[name='op']:checked").val()==1?'入库提示':'出库提示';
        tipLayer.innerHTML = '<div class="tip-layer-title">'+opTitle+'</div><div class="tip-layer-content"><p>'+content+'</p></div>';
        layerTipsArea.appendChild(tipLayer);
        setTimeout(function(){
             tipLayer.style.right = 10+'px';
        },10);
        setTimeout(function(){
            tipLayer.style.opacity = 0;
            tipLayer.style.top = tipLayer.offsetTop - 50+'px'; 
            setTimeout(function(){
                layerTipsArea.removeChild(tipLayer);
            },400);
        },3000);
    }
    var code = "";
    var lastTime, nextTime;
    var lastCode, nextCode;
    document.onkeypress = function (e) {
        if (window.event) { // IE
            nextCode = e.keyCode;
        } else if (e.which) { // Netscape/Firefox/Opera
            nextCode = e.which;
        }
        if (nextCode === 13) {
            if (code.length < 3) return; // 手动输入的时间不会让code的长度大于2，所以这里只会对扫码枪有效；
           
            // 给搜索框赋值并搜索
            $("#keys").attr("value", code);
            console.log(code); // 获取到扫码枪输入的内容，做别的操作
            console.log({'barcode':code,'op':2,'pack_id':pack_id,'form':'package'});
            // 得到扫码枪的值,请求数据库,返回结果
            $.ajax({
                type: "POST",
                url: "<?= url('store/package.index/scanResult')?>",
                data: {'barcode':code,'op':2,'pack_id':pack_id,'form':'package'},
                dataType: "json",
                success: function (res) {
                    renderTips(res.msg);
                    if (res.code == 1){
                        console.log(res,666666);
                        //  setTimeout(function(){location.reload();},3000);
                        if(res.data.data.express_num){
                            changeData(res.data.data.express_num);
                        }
                        
                    }else {
                        console.log('111')
                    }
                },error: function (error) {
                    console.log(error,11)
                }
            });
            code = '';
            lastCode = '';
            lastTime = '';
            return;
        }
        nextTime = new Date().getTime();
        if (!lastTime && !lastCode) {
            code += e.key;
        }

        if (lastCode && lastTime && nextTime - lastTime > 30) { // 当扫码前有keypress事件时,防止首字缺失
            code = e.key;
        } else if (lastCode && lastTime) {
            code += e.key;
        }
        lastCode = nextCode;
        lastTime = nextTime;
    }
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
          },
          
          getCheckSelectId:function(){
              var selectItem = [];
              for (var i=0;i<this.check.length;i++){
                    if (this.check[i].checked){
                       selectItem.push(this.check[i].id);
                    }
              }
              return selectItem;
          }
       }
       
       checker.init();

         /**
         * 批量包裹
         */
        $('#j-yichu').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            var selectItems = checker.getCheckSelectId();
            var hedanurl = "<?= url('store/trOrder/delete_package') ?>";
            if (selectIds.length==0){
                layer.alert('请至少选择一个包裹', {icon: 5});
                return;
            }
            data.selectId = selectIds;
            data.selectItem = selectItems[0];
            // data.selectCount = selectIds.length;
           layer.confirm('移出的包裹将回归到可提交打包状态，可从新申请打包哦', {title: '批量移除'}
                    , function (index) {
                        $.post(hedanurl,data, function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                        layer.close(index);
                    });
        });
        
         /**
         * 移出包裹
         */
        $('.j-yichue').on('click', function () {
            var $tabs, data = $(this).data();
            console.log(data,999);
            var hedanurl = "<?= url('store/trOrder/delete_package') ?>";
            // data.selectId = data.id;
            // data.selectItem = data.value;
            layer.confirm('移出的包裹将回归到可提交打包状态，可从新申请打包哦', {title: '批量移除'}
                    , function (index) {
                        $.post(hedanurl,data, function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                        layer.close(index);
                    });
        });
        
        /**
         * 将选中的包裹合并成新的集运单
         */
        $('#j-caihe').on('click', function () {
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            var selectItems = checker.getCheckSelectId();
            var hedanurl = "<?= url('store/trOrder/packageInOut') ?>";
                        console.log(data,999);
            if (selectIds.length==0){
                layer.alert('请至少选择一个包裹', {icon: 5});
                return;
            }
            data.selectId = selectIds;
            data.selectItem = selectItems[0];
            // data.selectCount = selectIds.length;
           layer.confirm('选中的包裹将被移出并打包成一个状态处于待打包状态的集运单', {title: '拆合包包'}
                    , function (index) {
                        $.post(hedanurl,data, function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                        layer.close(index);
                    });
        });
        

    });
    
</script>

