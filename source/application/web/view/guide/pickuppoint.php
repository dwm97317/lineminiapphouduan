 <div class="card">
    <div class="card-header border bottom">
        <h4 class="card-title">自提网点</h4>
    </div>
    <div class="card-body">
        <div class="table-overflow">
            <table class="table table-xl border">
                <thead class="thead-light">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">收件人姓名</th>
                        <th scope="col">收件人电话</th>
                        <th scope="col">国家</th>
                        <th scope="col">省/市/区</th>
                        <th scope="col">详细地址</th>
                        <th scope="col">创建时间</th>
                        <th class="text-center" scope="col">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                    <tr>
                        <td><?= $item['address_id'] ?></td>
                        <td><?= $item['name'] ?></td>
                        <td><?= $item['phone'] ?></td>
						<td><?= $item['country'] ?></td>
						<td><?= $item['province'] ?></td>
                        <td><?= $item['detail'] ?></td>
                        <td><?= $item['create_time'] ?></td>
                        <td class="text-center">
                            <a href="###" text="联系人：<?= $item['name'] ?> 电话：<?= $item['phone'] ?>  地址： <?= $item['province'] ?><?= $item['city'] ?><?= $item['region'] ?><?= $item['detail'] ?> 邮编：<?= $item['code'] ?>" onclick="copyUrl2(this)" class="text-gray font-size-14 m-r-10 text-success"><i class="mdi mdi-file-outline"></i>复制地址</a>
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