
                  <div class="card">
                            <div class="card-header border bottom">
                                <h4 class="card-title">充值记录</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-overflow">
                                    <table class="table table-xl border">
                                        <thead class="thead-light">
                                            <tr>
                                                <th scope="col">ID</th>
                                                <th scope="col">订单号</th>
                                                <th scope="col">余额变动场景</th>
                                                <th scope="col">套餐名称</th>
                                                <th scope="col">支付金额</th>
                                                <th scope="col">赠送金额</th>
                                                <th scope="col">支付状态</th>
                                                <th scope="col">创建时间</th>
                                            </tr>
                                        </thead>
                         
                                        <tbody>
                                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                            <tr>
                                                <td><?= $item['order_id'] ?></td>
                                                <td><?= $item['order_no'] ?></td>
                                                <td><?= $item['recharge_type']['text'] ?></td>
												<td><?= $item['order_plan']['plan_name'] ?: '--' ?></td>
												<td><?= $item['pay_price'] ?></td>
                                                <td><?= $item['gift_money'] ?></td>
                                                <td><?= $item['pay_status']['text'] ?></td>
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