<div class="card">
    <div class="card-header border bottom">
        <h4 class="card-title">我的包裹</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <ul class="nav nav-pills" role="tablist">
                    <li class="nav-item">
                        <a href="#pills-info-3" onclick="changese(1)" data-value="1" class="nav-link active" role="tab" data-toggle="tab">全部(<?= $count['count1'] ?>)</a>
                    </li>
                    <li class="nav-item">
                        <a href="#pills-info-3" onclick="changese(2)" data-value="2" class="nav-link" role="tab" data-toggle="tab">待入库(<?= $count['count2'] ?>)</a>
                    </li>
                    <li class="nav-item">
                        <a href="#pills-info-3" onclick="changese(3)" data-value="3" class="nav-link" role="tab" data-toggle="tab">已入库(<?= $count['count3'] ?>)</a>
                    </li>
                    <li class="nav-item">
                        <a href="#pills-info-3" onclick="changese(4)" data-value="4" class="nav-link" role="tab" data-toggle="tab">已打包(<?= $count['count4'] ?>)</a>
                    </li>
                    <li class="nav-item">
                        <a href="#pills-info-3" onclick="changese(5)" data-value="5" class="nav-link" role="tab" data-toggle="tab">已发货(<?= $count['count5'] ?>)</a>
                    </li>
                    <li class="nav-item">
                        <a href="#pills-info-3" onclick="changese(6)" data-value="6" class="nav-link" role="tab" data-toggle="tab">已签收(<?= $count['count6'] ?>)</a>
                    </li>
                    <li class="nav-item">
                        <a href="#pills-info-3" onclick="changese(7)" data-value="7" class="nav-link" role="tab" data-toggle="tab">已取消(<?= $count['count7'] ?>)</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!--搜索框-->
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <div class="input-group">
                        <input id="express_num" type="text" class="form-control" placeholder="请输入包裹单号" value="">
                        <input id="extype" type="hidden" class="form-control" placeholder="请输入包裹单号" value="1">
                        <span class="input-group-append">
                            <button class="btn btn-default btn-icon search" type="button">
                                <i class="mdi mdi-magnify"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group" id="dabao" style="display:none;">
                <div class="input-group">
                   <button  class="btn btn-gradient-primary dabao" id="submit">提交打包</button>
                </div>
            </div>
        </div>
        <div class="table-overflow">
            <table id="dt-opt" class="table table-hover table-xl">
                <thead>
                    <tr>
                        <th>
                            <div class="checkbox p-0">
                                <input id="selectable1" type="checkbox" class="checkAll" name="checkAll">
                                <label for="selectable1">ID</label>
                            </div>
                        </th>
                        <th>快递单号</th>
						<th>快递公司</th>
                        <th>状态</th> 
                        <th>仓库</th> 
                        <th>货物类型</th> 
                        <th>货物信息</th>
                        <th>总价</th>
                        <th>重量(KG</th>
                        <th>备注</th>
                        <th>预报时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="body"  class="gradfk">
                    <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                    <tr>
                        <td>
                            <div class="checkbox">
                                <input id="selectable<?= $item['id']; ?>" value="<?= $item['id'] ?>" type="checkbox">
                                <label for="selectable<?= $item['id']; ?>"><?= $item['id'] ?></label>
                            </div> 
                        </td>
                        <td>
                            <a target="_blank" class="j-search" data-id="<?= $item['express_num'] ?>" href="javascript:void(0)"><?= $item['express_num'] ?></a><br>
                            唛头：<?= $item['usermark'] ?>
                        </td>
                        <td>
                            <?= $item['express']['express_name'] ?>
                            
                        </td>
						<td><span class="badge badge-pill badge-gradient-success"><?= $item['status'] ?></span></td>
						<td><?= $item['storage']['shop_name'] ?></td>
						<td><?= $item['class_name'] ?></td>
						<td><?= $item['class_name'] ?></td>
						<td><?= $item['all_price'] ?></td>
						<td><?= $item['all_weight'] ?></td>
                        <td><?= $item['remark'] ?></td>
						<td><?= $item['created_time'] ?></td>
                        <td class="text-center font-size-18">
                            <a href="<?= urlCreate('/web/Package/edit', ['id' => $item['id']]) ?>" class="text-gray m-r-15"><i class="ti-pencil"></i></a>
                            <a href="javascript:void(0)" class="text-gray j-delete" data-id="<?= $item['id'] ?>"><i class="ti-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="11" class="am-text-center">暂无记录</td>
                        </tr>
                        
                    <?php endif; ?>
			   </tbody>
            </table>
            <div class="am-u-lg-12 am-cf">
                <div class="am-fr" id="render"><?= $list->render() ?> </div>
                <div class="am-fr pagination-total am-margin-right">
                    <div class="am-vertical-align-middle " id="total">总记录：<?= $list->total() ?></div>
                </div>
            </div>
        </div> 
    </div>
     <div class="modal fade" id="modal-sm" style="display: none;" aria-hidden="true"></div>        
   <div class="inpack-layer">
         <div class="card" >
            <div class="card-header border bottom modal-fs">
                <h4 class="card-title">提交打包</h4>
                <span style="float:right" onclick="hiddenDialog()" >
                   <a  style="top: 10px;"  class="modal-close" href="" data-dismiss="modal"><i class="ti-close"></i></a>
                </span>    
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-8">
                        <form role="form" id="ajaxForm">
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label control-label">选择线路 *</label>
                                <div class="col-sm-10">
                                        <select class="form-control" name="package[line_id]">
                                         <?php foreach ($line as $item): ?>
                                        <option value="<?= $item['id'] ?>"><?= $item['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
        				    <div class="form-group row">
                                <label class="col-sm-2 col-form-label control-label">选择地址</label>
                                <div class="col-sm-10">
                                        <select class="form-control" name="package[address_id]">
                                         <?php foreach ($address as $add): ?>
                                        <option value="<?= $add['address_id'] ?>"><?= $add['country'].'/'.$add['name'].'/'.$add['province'].'/'.$add['city'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label control-label">代收款</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="package[waitreceivedmoney]" placeholder="代收款">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label control-label">备注</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="package[remark]" placeholder="备注" required="">
                                </div>
                            </div>
        
                            <button type="button" class="btn btn-gradient-success package">提交预报</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
<script id="tpl-inpack" type="text/template">
    {{each data value}}
        <tr>
            <td>
                <div class="checkbox">
                    <input id="selectable{{value.id}}" value="{{value.id}}" type="checkbox">
                    <label for="selectable{{value.id}}">{{value.id}}</label>
                </div> 
            </td>
            <td><a class="j-searchf">{{value.express_num}}</a></td>
            <td>{{value.express_name}}</td>
    		<td><span class="badge badge-pill badge-gradient-success">{{value.status}}</span></td>
            <td>{{value.storage?value.storage.shop_name:''}}</td>
    		<td>{{value.class_name}}</td>
    		<td>{{value.class_name}}</td>
    		<td>{{value.all_price}}</td>
			<td>{{value.all_weight}}</td>
			<td>{{value.remark}}</td>
			<td>{{value.created_time}}</td>
            <td class="text-center font-size-18">
                <a href="<?= urlCreate('/web/Package/edit', ['id' => $item['id']]) ?>" class="text-gray m-r-15"><i class="ti-pencil"></i></a>
                <a href="javascript:void(0)" class="text-gray j-delete" data-id="<?= $item['id'] ?>"><i class="ti-trash"></i></a>
            </td>
        </tr>
    {{/each}}
</script>
<script>
function hiddenDialog(){
     $('.inpack-layer').fadeOut();
}
function changestatek(){
    var dataid =   $('#dataidss')[0].value;
    var remark =  $('#problem_result')[0].value;
    var type =  $('#problem_type')[0].value;
   
    var url = "index.php?s=/web/Package/doproblem"; 
      $.ajax({
        url:url,
        type:'POST',
        dataType:"json",
        data:{id:dataid,type:type,remark:remark},
        success:function(res){
            if (res['code']==1){
               location.reload();
            }
        }
     }) 
}

function changese(e){
    $('#extype').val(e);
    var type = e;
     if(type==3){
        $('#dabao')[0].style.display = 'block';
        console.log($('#dabao'),99999);
    }else{
        $('#dabao')[0].style.display = 'none';
    }
     $.ajax({
        url:'<?=urlCreate('/index.php/web/package/mypackage') ?>',
        type:'POST',
        dataType:"json",
        data:{type:e},
        success:function(res){
            if (res['code']==1){
                console.log(res,78);
                    var list = template('tpl-inpack',res.data.list)
                    $('.gradfk').html(list);
            }
        }
     })
}

function changestates(_this){
   var dataid =  _this.getAttribute("data-id");
   console.log(dataid);
   var datatype =  _this.getAttribute("data-type");
   var number =  _this.getAttribute("data-number");
   $('#dataidss').val(dataid);
   $('#datatypess').val(datatype);
   $('#numberss').val(number);
}

window.onload = function(){

 $(".package").click(res=>{
     var formData = $('#ajaxForm').serializeArray(); 
     var formJson = {};
     formData.forEach((val)=>{
        formJson[val['name']] = val['value']; 
     });
     var select = $('#body').find('input');
     
     var selectIds = [];
     for (var k in select){
        if (select[k].checked==true){
            selectIds.push(select[k].value);
        }
     }
     formJson['package[selectIds]'] = selectIds;
     console.log(formJson,'5555555');
     $.ajax({
        url:'<?= urlCreate('/web/Package/postPack') ?>', 
        type:'POST',
        dataType:"json",
        data:formJson,
        success:function(res){
            console.log(res,456789);
            if (res['code']==1){
                console.log(res.msg);
                 var url = "<?php echo(urlCreate('/web/package/mypackage')) ?>";
                window.location.href= url;
            }

        }
     })
    return false;
 })
 
$("#submit").click(function(){
    var select = $('#body').find('input');
    var selectIds = [];
    for (var k in select){
        if (select[k].checked==true){
            selectIds.push(select[k].value);
        }
    }
    if (selectIds.length==0){
         layer.alert('请选择要提交的包裹');
         return;
    }
       layer.confirm("确认提交吗",{
		btn: ['确认', '取消']
	}, function (index) {
	     $('.inpack-layer').fadeIn();
	     $('#modal-sm').addClass("back");
	     $('#modal-sm').fadeIn();
		// 按钮1的事件
		layer.close(index);
	}, function(){
		// 按钮2的事件
	});
 });  
 
//搜索功能
 $(".search").click(res=>{
     var formData =$("#express_num")[0].value;
     var type =  $('#extype')[0].value;
     console.log($('#total')[0]);
      
     $.ajax({
        url:'<?=urlCreate('/index.php/web/package/mypackage') ?>', 
        type:'POST',
        dataType:"json",
        data:{number:formData,type:type},
        success:function(res){
            if (res['code']==1){
               var list = template('tpl-inpack',res.data.list)
               $('.gradfk').html(list);
            }

        }
     })
    return false;
 })
}
</script>
<style>
.inpack-layer { 
        outline: 0;
    -webkit-box-shadow: 0 16px 24px rgb(43 47 49 / 25%);
    -moz-box-shadow: 0 16px 24px rgba(43, 47, 49, 0.25);
    box-shadow: 0 16px 24px rgb(43 47 49 / 25%);
    position: absolute;
    top: 0;
    display: none;
    width: 60%;
    margin-top: 75px;
    height: 30%;
    margin-left: -523px;
    z-index: 9999;
    left: 50%;
}
.back{
    z-index: 9999;
    background-color: rgb(0, 0, 0);
    opacity: 0.3;
}    
</style>