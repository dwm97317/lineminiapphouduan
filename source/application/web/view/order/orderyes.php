
<div class="card">
    <div class="card-body">
        <!--搜索框-->
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <div class="input-group">
                        <input id="express_num" type="text" class="form-control" placeholder="请输入国际单号" value="">
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
                        <th>平台单号</th>
						<th>集运单号</th>
                        <th>状态</th> 
                        <th>创建时间</th>
                        <th>包裹属性</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="gradfk">
                    <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                    <tr>
                        <td>
                            <div class="checkbox">
                                <input id="selectable2" type="checkbox">
                                <label for="selectable2"><?= $item['id'] ?></label>
                            </div> 
                        </td>
                        <td><?= $item['order_sn'] ?></td>
                        <td ><a target="_blank" class="j-search" data-id="<?= $item['t_order_sn'] ?>" href="javascript:void(0)"><?= $item['t_order_sn'] ?></a></td>
						<td><span class="badge badge-pill badge-gradient-success"><?= $item['status'] ?></span></td>
                        <td><?= $item['created_time'] ?></td>
                        <td>
							<span class="badge badge-pill badge-primary">重量：<?= $item['weight'] ?></span>
							<span class="badge badge-pill badge-primary">零食<?= $item['logistics'] ?></span>
						</td>
                        <td class="text-center font-size-18">
                            <a href="" class="text-gray m-r-15"><i class="ti-pencil"></i></a><!--编辑修改--->
                            <a href="###" class="text-gray " data-id="<?= $item['id'] ?>"><i class="ti-trash"></i></a> <!--取消预报-->
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="11" class="am-text-center">暂无记录</td></tr>
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
            <td>{{value.order_sn}}</td>
            <td>{{value.t_order_sn}}</td>
    		<td><span class="badge badge-pill badge-gradient-success">{{value.status}}</span></td>
            <td>{{value.created_time}}</td>
            <td>
    			<span class="badge badge-pill badge-primary">重量(kg)：{{value.weight}}</span>
    			<span class="badge badge-pill badge-success">
    			    长宽高：{{value.length}}/{{value.width}}/{{value.height}}</span>
    			<span class="badge badge-pill badge-primary">{{value.weight}}</span>
    		</td>
            <td class="text-center font-size-18">
                <a href="" class="text-gray m-r-15"><i class="ti-pencil"></i></a><!--编辑修改--->
                <a href="javascript:void(0)" class="text-gray j-delete" data-id="{{value.id}}"><i class="ti-trash"></i></a> <!--取消预报-->
            </td>
        </tr>
    {{/each}}
</script>
<script>
window.onload = function(){

//搜索功能
 $(".search").click(res=>{
     var formData = $('#express_num')[0].value;
     $.ajax({
        url:'<?= urlCreate('/web/Package/searchOrder') ?>', 
        type:'POST',
        dataType:"json",
        data:{express_num:formData},
        success:function(res){
            if (res['code']==1){
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
