<div class="card">
    <div class="card-header border bottom">
        <h4 class="card-title">寄件人地址</h4>
    </div>
    <div class="card-body">
        <div class="" style="margin-bottom:10px;">
            <span style="padding: 13px 18px;" data-toggle="modal" data-target="#basic-modal" class="badge badge-primary" onclick="window.location.href='<?=urlCreate('/web/package/jaddAddress') ?>'">新增寄件人</span>
        </div>
        <div class="table-overflow">
            <table class="table table-xl border">
                <thead class="thead-light">
                    <tr>
                        <th scope="col">寄件人</th>
                        <th scope="col">联系电话</th>
                        <th scope="col">国家/地区</th>
						<th scope="col">城市</th>
						<th scope="col">门牌号</th>
						<th scope="col">身份证号</th>
                        <th scope="col">通关代码</th>
                        <th class="text-center" scope="col">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                    <tr>
                        <td>
                            <div class="list-media">
                                <div class="list-item">
                                    <div class="media-img">
                                        <img class="rounded" src="<?= $user['user']['avatarUrl'] ?>" alt="">
                                    </div>
                                    <div class="info">
                                        <span class="title"><?= $item['name'] ?></span>
                                        <span class="sub-title">用户ID：<?= $user['user']['user_id'] ?></span>
                                    </div>
                                </div>
                            </div>
                        </td>
						<td><?= $item['phone'] ?></td>
                        <td><?= $item['country'] ?></td>
                        <td><?= $item['city'] ?></td>
                        <td><?= $item['door'] ?></td>
                        <td><?= $item['identitycard'] ?></td>
						<td><?= $item['clearancecode'] ?></td>
                        <td class="text-center font-size-18">
                           <a href="<?= urlCreate('/web/address/edit&address_id='.$item['address_id']) ?>" class="text-gray  m-r-15"><i class="ti-pencil"></i></a>
                            <a href="javascript:void(0)" onclick="deleteaddress(this)"  data-id="<?= $item['address_id'] ?>" class="text-gray j-delete"><i class="ti-trash"></i></a>
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
    </div>
</div>
<script>
function deleteaddress(_this){
    var id= _this.getAttribute('data-id');
    layer.confirm("确定要删除吗",{btn: ['确认', '取消']}, function (index) {
	   $.ajax({
        url:'<?= urlCreate('/web/user/deleteaddress') ?>', 
        type:'POST',
        dataType:"json",
        data:{id:id},
        success:function(res){
            if (res['code']==1){
                location.reload();
            }

        }
     })
		// 按钮1的事件
		layer.close(index);
	}, function(){
		// 按钮2的事件
	});
}

window.onload = function(){
// 删除预报

}
</script>