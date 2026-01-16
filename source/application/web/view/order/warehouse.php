 <div class="card">
                            <div class="card-header border bottom">
                                <h4 class="card-title">仓库列表</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-overflow">
                                    <table class="table table-xl border">
                                        <thead class="thead-light">
                                            <tr>
                                                <th scope="col">ID</th>
                                                <th scope="col">仓库照</th>
                                                <th scope="col">仓库名称</th>
                                                <th scope="col">营业时间</th>
                                                <th scope="col">联系人</th>
                                                <th scope="col">联系电话</th>
												<th scope="col">邮编</th>
												<th scope="col">仓库地址</th>
                                                <th class="text-center" scope="col">操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                            <tr>
                                                <td><?= $item['shop_id'] ?></td>
                                                <td>
                                                    <div class="list-media">
                                                        <div class="list-item">
                                                            <div class="media-img">
                                                                <img class="rounded" src="<?= $item['logo']['file_path'] ?>" alt="">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?= $item['shop_name'] ?></td>
												<td><?= $item['shop_hours'] ?></td>
												<td><?= $item['linkman'] ?></td>
                                                <td><?= $item['phone'] ?></td>
                                                <td><?= $item['post'] ?></td>
                                                <td>
                                                     <?= $item['region']['province'] ?>  <?= $item['region']['city'] ?>  <?= $item['region']['region'] ?><?= $item['address'] ?>
                                                </td>
                                                <td class="text-center">
                                                    <a href="###" text="仓库名：<?= $item['shop_name'];?> 联系人：<?= $item['linkman'] ?> 电话：<?= $item['phone'] ?>  地址： <?= $item['region']['province'] ?><?= $item['region']['city'] ?><?= $item['region']['region'] ?><?= $item['address'] ?> 邮编：<?= $item['post'] ?>" onclick="copyUrl2(this)" class="text-gray font-size-14 m-r-10 text-success"><i class="mdi mdi-file-outline"></i>复制地址</a>
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