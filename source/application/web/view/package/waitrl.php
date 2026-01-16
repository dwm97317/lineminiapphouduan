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
                        <th>入库时间</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="gradfk">
                    <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                    <tr>
                        <td>
                            <div class="checkbox">
                                <input id="selectable<?= $item['id']; ?>" value="<?= $item['id'] ?>" type="checkbox">
                                <label for="selectable<?= $item['id']; ?>"><?= $item['id'] ?></label>
                            </div> 
                        </td>
                        <td><a target="_blank" class="j-search" data-id="<?= $item['express_num'] ?>" href="javascript:void(0)"><?= $item['express_num'] ?></a></td>
                        <td><?= $item['express']['express_name'] ?></td>
						<td><span class="badge badge-pill badge-gradient-success"><?= $item['status'] ?></span></td>
                        <td><?= $item['remark'] ?></td>
						<td><?= $item['entering_warehouse_time'] ?></td>
                        <td class="text-center font-size-18">
                           <input id="chooseid" value="" type="hidden">
                           <button data-target="#basic-modal"  data-toggle="modal" data-id="<?= $item['id'] ?>" onclick="setid(this)" class="buzaidian m-t-20 btn-xs btn btn-primary btn-rounded btn-float">认领包裹</button>
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
                <div class="am-fr pagination-total am-margin-right">
                    <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
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
                    <input id="selectable2" type="checkbox">
                    <label for="selectable2">{{value.id}}</label>
                </div> 
            </td>
            <td><a class="j-searchf" data-id={{value.express_num}}>{{value.express_num}}</a></td>
            <td>{{value.express_name}}</td>
    		<td><span class="badge badge-pill badge-gradient-success">{{value.status}}</span></td>
            <td>{{value.remark}}</td>
    		<td>{{value.created_time}}}</td>
            <td class="text-center font-size-18">
                <input id="chooseid" value="" type="hidden">
                           <button data-target="#basic-modal"  data-toggle="modal" data-id="{{value.id}}" onclick="setid(this)" class="buzaidian m-t-20 btn-xs btn btn-primary btn-rounded btn-float">认领包裹</button>
            </td>
        </tr>
    {{/each}}
</script>
<div class="modal fade" id="basic-modal" type="text/template">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4>包裹认领</h4>
            </div>
            <div class="modal-body">
                <form role="form" id="ajaxForm">
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label control-label">包裹单号</label>
                        <div class="col-sm-9">
                            <input id="money" autocomplete="off" type="text" class="form-control" name="money" placeholder="请输入正确的包裹单号" value="">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer no-border">
                <div class="text-right">
                    <button class="btn btn-default"  data-dismiss="modal">取消</button>
                    <button class="btn btn-success" onclick="changemoney()" data-dismiss="modal">确认</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function setid(_this){
    var id =  _this.getAttribute('data-id');
    $("#chooseid").val(id);
}
function changemoney(){
    var express_num = $("#money")[0].value;
    var id = $("#chooseid")[0].value;
    console.log(id);
    layer.confirm('确定认领吗？', {title: '友情提示'},function(index){
                $.ajax({
                    url:'<?= urlCreate('/web/Package/renling') ?>', 
                    type:'POST',
                    dataType:"json",
                    data:{express_num,id},
                    success:function(res){
                        if (res['code']==1){
                            layer.alert(res.data);
                            setTimeout(function(){ location.reload();},2000);
                        }else{
                            layer.alert(res.msg);
                        }
                    }
                 })
                 layer.close(index);
             });
}


window.onload = function(){
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
                var list = template('tpl-inpack',res.data)
                $('.gradfk').html(list);
            }

        }
     })
    return false;
 })
 
var url = "<?php echo(urlCreate('/web/package/trajectory')) ?>";
$(".j-search").click(
    function(){
        console.log(6666,11111111)
        var $tabs, data = $(this).data();
        location.replace(url+ '&express_num=' + data.id);
})

$(".j-searchf").click(
    function(){
        var $tabs, data = $(this).data();
        location.replace(url+ '&express_num=' + data.id);
})  
 
}

</script>
