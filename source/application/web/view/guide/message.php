              <div class="page-header">
                            <h2 class="header-title">站内消息</h2>
                            <div class="header-sub-title">
                                <nav class="breadcrumb breadcrumb-dash">
                                    <a href="#" class="breadcrumb-item"><i class="ti-home p-r-5"></i>主页</a>
                                    <span class="breadcrumb-item active">站内消息</span>
                                </nav>
                            </div>
                        </div>  
                  <div class="card">
                            <div class="card-body">
                                <div class="table-overflow">
                                    <table class="table table-xl border">
                                        <thead class="thead-light">
                                            <tr>
                                                <th scope="col">ID</th>
                                                <th scope="col">消息</th>
                                                <th scope="col">是否已读</th>
                                                <th scope="col">时间</th>
                                                <th class="text-center" scope="col">操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                            <tr>
                                                <td><?= $item['id'] ?></td>
                                                <td><?= $item['content'] ?></td>
                                                <td><?= $item['is_read']==0?"未读":"已读" ?></td>
                                                <td><?= $item['created_time'] ?></td>
                                                <td class="text-center">
                                                    <button  class="m-t-20 btn-xs btn btn-primary btn-rounded btn-float"  id="resetbag">标为已读</button>
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

<script type="javascript">
 $("#resetbag").click(res=>{
    //  var formData = $('#express_num')[0].value;
    console.log(res)
     $.ajax({
        url:'/web/Package/seachpack', 
        type:'POST',
        dataType:"json",
        data:{ bag_name:formData },
        success:function(res){
            if (res['code']==1){
                var list = template('tpl-inpack',res.data)
                $('.gradfk').html(list);
            }

        }
     })
    return false;
 })

window.onload = function(){
      
    
}

</script>                    