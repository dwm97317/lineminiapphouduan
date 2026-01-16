 <div class="card">
    <div class="card-header border bottom">
        <h4 class="card-title">会员等级</h4>
    </div>
    <div class="card-body">
        <div class="row m-v-30">
            <div class="col-sm-3">
                <img class="img-fluid rounded-circle d-block mx-auto m-b-30" src="<?= $detail['avatarUrl'] ?>" alt="">
            </div>
            <div class="col-sm-4 text-center text-sm-left">
                <h2 class="m-b-5"><?= $detail['nickName'] ?></h2>
                <p class="text-opacity m-b-20 font-size-13">会员等级：<span class="badge badge-pill badge-info"><?= $detail['grade']['name'] ?></span></p>
                <div class="d-flex flex-row justify-content-center justify-content-sm-start">
                    <div class="p-v-20 p-r-15 text-center">
                        <span class="font-size-18 text-info text-semibold"><?= $detail['expend_money'] ?></span>
                        <small class="d-block">消费金额</small>
                    </div>
                    <div class="p-v-20 p-h-15 text-center">
                        <span class="font-size-18 text-info text-semibold"><?= $detail['income'] ?></span>
                        <small class="d-block">我的收益</small>
                    </div>
                    <div class="p-v-20 p-h-15 text-center">
                        <span class="font-size-18 text-info text-semibold"><?= $detail['points'] ?></span>
                        <small class="d-block">我的积分</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
                      
    <div class="card-body">
        <div class="table-overflow">
            <table class="table table-xl border">
                <thead class="thead-light">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">等级名称</th>
                        <th scope="col">升级条件</th>
                        <th scope="col">等级权益</th>
                        <th scope="col">创建时间</th>
                    </tr>
                </thead>
 
                <tbody>
                    <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                    <tr>
                        <td><?= $item['grade_id'] ?></td>
                        <td><?= $item['name'] ?></td>
						<td>消费满<?= $item['upgrade']['expend_money'] ?>元</td>
						<td><?= $item['equity']['discount'] ?>折</td>
                        <td><?= $item['create_time'] ?></td>
                        <td class="text-center">
                            
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
function copyUrl2(_this)
    {
        console.log(444);
        var Url2= _this.getAttribute('text');
        var oInput = document.createElement('input');
        oInput.value = Url2;
        document.body.appendChild(oInput);
        oInput.select(); // 选择对象
        document.execCommand("Copy"); // 执行浏览器复制命令
        oInput.className = 'oInput';
        oInput.style.display='none';
        layer.msg('复制成功');
    }

window.onload = function(){
function copyUrl2(_this)
    {
        console.log(444);
        var Url2= _this.getAttribute('text');
        var oInput = document.createElement('input');
        oInput.value = Url2;
        document.body.appendChild(oInput);
        oInput.select(); // 选择对象
        document.execCommand("Copy"); // 执行浏览器复制命令
        oInput.className = 'oInput';
        oInput.style.display='none';
        layer.msg('复制成功');
    }
}
</script>                    