<?php $status = [1=>'未入库',2=>'已入库',3=>'已拣货上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成','-1'=>'问题件']; ?>
<div class="card">
    <div class="card-body">
        <!--搜索框-->
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <div class="input-group">
                        <input id="express_num" type="text" class="form-control" placeholder="请输入包裹单号" value="">
                        <span class="input-group-append">
                            <button class="btn btn-default btn-icon search" type="button">
                                <i class="mdi mdi-magnify"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
		<button class="btn btn-gradient-primary" id="submit">提交打包</button>
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
                        <th>包裹单号</th>
						<th>快递公司</th>
                        <th>状态</th> 
                        <th>备注</th> 
                        <th>预报时间</th>
                        <th>包裹属性</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="body" class="gradfk">
                    <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                    <tr>
                        <td>
                            <div class="checkbox">
                                <input id="selectable<?= $item['id'] ?>" value="<?= $item['id'] ?>" type="checkbox">
                                <label for="selectable<?= $item['id'] ?>"><?= $item['id'] ?></label>
                            </div> 
                        </td>
                        <td><a target="_blank" class="j-search" data-id="<?= $item['express_num'] ?>" href="javascript:void(0)"><?= $item['express_num'] ?></a></td>
                        <td><?= $item['express']['express_name'] ?></td>
						<td><span class="badge badge-pill badge-gradient-success"><?= $item['status'] ?></span></td>
                        <td><?= $item['remark'] ?></td>
                        <td>
							<span class="badge badge-pill badge-primary">重量(kg)：<?= $item['weight'] ?></span>
							<span class="badge badge-pill badge-success">
							    长宽高：<?= $item['length'] ?>/<?=$item['width']?>/<?=$item['height'] ?></span>
						</td>
						<td><?= $item['created_time'] ?></td>
                        <td class="text-center font-size-18">
                            <a href="" class="text-gray m-r-15"><i class="ti-pencil"></i></a><!--编辑修改--->
                            <!--<a href="" class="text-gray j-delete" data-id="<?= $item['id'] ?>"><i class="ti-trash"></i></a> <!--取消预报-->
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
                <div class="am-fr"><?= $list->render() ?> </div>
                <div class="am-fr pagination-total am-margin-">
                    <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
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
<script id="tpl-inpack" type="text/template">
    {{each data value}}
        <tr>
            <td>
                <div class="checkbox">
                    <input id="selectable{{value.id}}" value="{{value.id}}" type="checkbox">
                    <label for="selectable{{value.id}}">{{value.id}}</label>
                </div> 
            </td>
            <td>{{value.express_num}}</td>
            <td>{{value.express_name}}</td>
    		<td><span class="badge badge-pill badge-gradient-success">{{value.status}}</span></td>
            <td>{{value.remark}}</td>
            <td>
    			<span class="badge badge-pill badge-primary">重量(kg)：{{value.weight}}</span>
    			<span class="badge badge-pill badge-success">
    			    长宽高：{{value.length}}/{{value.width}}/{{value.height}}</span>
    			<span class="badge badge-pill badge-primary">{{value.weight}}</span>
    		</td>
    		<td>{{value.created_time}}</td>
            <td class="text-center font-size-18">
                <a href="" class="text-gray m-r-15"><i class="ti-pencil"></i></a><!--编辑修改--->
                <a href="javascript:void(0)" class="text-gray j-delete" data-id="{{value.id}}"><i class="ti-trash"></i></a> <!--取消预报-->
            </td>
        </tr>
    {{/each}}
</script>
 <link href="/web/static/css/sweet-alert.css" rel="stylesheet">
    <script src="/web/static/js/sweet-alert.js"></script>
<script>
function hiddenDialog(){
     $('.inpack-layer').fadeOut();
}
window.onload = function(){

 $("#submit").click(function(){
    var select = $('#body').find('input');
    var selectIds = [];
    for (var k in select){
        if (select[k].checked==true){
            selectIds.push(select[k].value);
        }
    }
    if (selectIds.length==0){
         swal('请选择要提交的包裹');
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
                 var url = "<?php echo(urlCreate('/web/package/inpackage')) ?>";
                window.location.href= url;
            }

        }
     })
    return false;
 })
    
 $(".j-delete").click(
        function(){
             var $tabs, data = $(this).data();
             console.log(data,567);
             $.ajax({
                url:'<?= urlCreate('/web/Package/cancle') ?>', 
                type:'POST',
                dataType:"json",
                data:data,
                success:function(res){
                    console.log(res,456789);
                    if (res['code']==1){
                        var list = template('tpl-inpack',res)
                        $('.list').html(list);
                    }
                }
             })
            return false;
        } 
    )
    
    //搜索功能
 $(".search").click(res=>{
     var formData = $('#express_num')[0].value;
     $.ajax({
        url:'<?= urlCreate('/web/Package/search') ?>', 
        type:'POST',
        dataType:"json",
        data:{express_num:formData},
        success:function(res){
            if (res['code']==1){
                // var list = template('tpl-inpack',res['data']['data'])
                // var list = template('tpl-inpack',res[data][data])
                console.log(res.data.data,890)
                var list = template('tpl-inpack',res.data)
                console.log(list,890)
                $('.gradfk').html(list);
            }

        }
     })
    return false;
 }) 
var url = "<?php echo(urlCreate('/web/package/trajectory')) ?>";
$(".j-search").click(
    function(){
        var $tabs, data = $(this).data();
        location.replace(url+ '&express_num=' + data.id);
})     
}
</script>
