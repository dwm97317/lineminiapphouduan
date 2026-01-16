<style>
  .sendOrderLayer { width:100%; height:100%; position:fixed; top:0; left:0; background:rgba(0,0,0,.8)}
  .sendOrderView { width:60%; height: 500px; position:relative; background:#fff; border:5px; margin:300px auto;}
  .sendOrderViewTitle { height:40px; line-height: 40px; text-indent: 1em; color:#333;}
  .sendOrderViewContent { width:90%; margin:0 auto;}
  .sendOrderViewSelected { width:90%; min-height:100px;}
  .sendOrderViewSelected p , .sendOrderViewSelectedDetails p { font-size:14px; color:#666;}
  .sendOrderViewSelectedCon { width:100%; padding:5px; }
  .sendOrderViewSelectedCon span { background:#000; display:inline-block; cursor:pointer; padding:5px 10px; color:#fff; margin-right:15px; margin-bottom:10px;}
  .sendOrderViewSelectedDetailsItem { padding-top:10px;}
  .sendOrderViewSelectedDetailsItemLable { width:100%; height:40px; }
  .lable-item { width:15%; display:inline-block;}
  .sendOrderViewSelectedDetails-item { margin-bottom:10px; color:#666;}
  .sendOrderViewBtn { width:100%; height:40px; position:absolute; bottom:10px;}
    .sendOrderViewBtn a { width:100px; height:30px; display:inline-block; float:right;} 
</style>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">预发货列表</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                          onclick="sender.showSend()">
                                            <span class="am-icon-plus"></span> 一键发货
                                        </a>
                                    </div>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th><input id="checkAll" type="checkbox" ></th>
                                <th>ID</th>
                                <th>预发货单号</th>
                                <th>包裹数量</th>
                                <th>打包封箱时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody id="send">
                            <?php if (!$list->isEmpty()): ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td>
                                            <input name="checkIds" type="checkbox" > 
                                        </td>
                                        <td class="am-text-middle id"><?= $item['send_id'] ?></td>
                                        <td class="am-text-middle order_sn"><?= $item['order_sn'] ?></td>
                                        <td class="am-text-middle"><?= $item['num'] ?></td>
                                        <td class="am-text-middle"><?= $item['created_time'] ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('sendOrder/details')): ?>
                                                    <a href="<?= url('sendOrder/details',
                                                        ['send_id' => $item['send_id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 包裹详情
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('setting.line/delete')): ?>
                                                    <a href="javascript:;"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $item['send_id'] ?>" onclick="tools.init(this)" data-mode='request' data-confirm=true data-confirm_text='请确认是否删除！' data-refresh=true data-url='<?= url('store/package.line/delete') ?>'>
                                                        <i class="am-icon-trash"></i> 删除
                                                    </a>
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
<div class="sendOrderLayer" style="display:none">
    <div class="sendOrderView">
         <div class='sendOrderViewTitle'>
              一键发货 <a href="javascript:;" style="float:right; margin-right:20px" onclick="sender.showSend('hide')">X</a>    
         </div>
         <div class='sendOrderViewContent'>
              <div class="sendOrderViewSelected">
                   <p>已选预发货单</p>
                   <div class="sendOrderViewSelectedCon">
                       
                   </div>
              </div>
              <div class="sendOrderViewSelectedDetails">
                   <p>预发货单详情</p>
                   <div class="sendOrderViewSelectedDetailsItem">
                        <div class="sendOrderViewSelectedDetailsItemLable">
                             <span class="lable-item" style="width:250px">包裹单号</span><span class="lable-item">快递单号</span><span class="lable-item">状态</span><span class="lable-item">实付金额</span><span class="lable-item">价值</span>
                        </div>
                        <div class="sendOrderViewSelectedDetails-item-con">
                           
                        </div>
                        
                   </div>
              </div>
         </div> 
         <div class="sendOrderViewBtn">
              <a href="javascript:;" onclick="sender.doConfirm()">确定发货</a>
         </div>
    </div>
</div>
<script>
    $(function () {
       checker = {
          num:0, 
          check:[],
          init:function(){
              this.check = document.getElementById('send').getElementsByTagName('input');
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
          }
       }
       
       checker.init();
    });
      
       var sender = {
           send_data:[],
           showSend:function(mode){
               var data = this.getSelectData();
               console.log(data);
               if (!data.length){
                   alert('请选择预发货单');
                   return;
               }
               if (mode=='hide'){ 
                  $('.sendOrderLayer').hide();
                  return ; 
               }
               $('.sendOrderLayer').show();
               this.renderOrderSn();
               this.getItem(this.send_data[0]['id']);
           },
           renderOrderSn:function(){
               var orderSnItem =  document.getElementsByClassName('sendOrderViewSelectedCon')[0];
               var _orderSn = '';
               for (var i=0; i<this.send_data.length;i++){
                    _orderSn +='<span data-id='+this.send_data[i]['id']+'>'+this.send_data[i]['sn']+'</span>';
               }
               orderSnItem.innerHTML = _orderSn;
               this.bindClick();
           },
           bindClick:function(){
               var aSpan = document.getElementsByClassName('sendOrderViewSelectedCon')[0].getElementsByTagName('span');
               for (var i=0; i<aSpan.length; i++){
                    aSpan[i].onclick = function(){
                        var id = this.getAttribute('data-id');
                        sender.getItem(id);
                    }
               }
           },
           getItem:function(id){
              $.ajax({
                  type:"get",
                  url:"/index.php?s=/store/send_order/packlist&send_id="+id,
                  dataType:"json",
                  success:function(res){
                     if (res.code==1){
                         var data = res.data;
                         sender.renderPack(data['item']);
                     }
                  }
              })  
           },
           renderPack:function(data){
                var packItem =  document.getElementsByClassName('sendOrderViewSelectedDetails-item-con')[0];
                var _packhtml = '';
                var _statusMap = {
                    1:'待入库',
                    2:'已入库',
                    3:'已分拣上架',
                    4:'待打包',
                    5:'待支付',
                    6:'已支付',
                    7:'已分拣下架',
                    8:'已打包',
                    9:'已发货',
                    10:'已收货',
                    11:'已完成'
                };
                for (var i=0; i<data.length;i++){
                _packhtml += '<div class="sendOrderViewSelectedDetails-item"><span class="lable-item"  style="width:250px">'+data[i]['order_sn']+'</span><span class="lable-item">'+data[i]['express_num']+'</span><span class="lable-item">'+_statusMap[data[i]['status']]+'</span><span class="lable-item">'+data[i]['real_payment']+'</span><span class="lable-item">'+data[i]['price']+'</span></div>';
               }
               packItem.innerHTML = _packhtml;
           },
           doConfirm:function(){
                var ids = [];
                for (var i=0; i<this.send_data.length;i++){
                     ids.push(this.send_data[i]['id']);
                }
                $.ajax({
                  type:"get",
                  url:"/index.php?s=/store/send_order/createdSendOrder&ids="+ids.join(','),
                  dataType:"json",
                  success:function(res){
                     if (res.code==1){
                         alert('发货单创建成功');
                     }
                  }
                })  
           },
           getSelectData:function(){
                var check = document.getElementById('send').getElementsByTagName('input');
                var order_sn = document.getElementsByClassName('order_sn');
                var id = document.getElementsByClassName('id');
                this.send_data = [];
                for (var i=0;i<check.length;i++){
                    if (check[i].checked){
                       var _data = {
                           'id':id[i].innerHTML,
                           'sn':order_sn[i].innerHTML,
                       }
                       this.send_data.push(_data);
                    }
                }
                return this.send_data;
           },
       }
</script>

