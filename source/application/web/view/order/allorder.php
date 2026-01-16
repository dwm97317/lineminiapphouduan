<?php $status = [1=>'待查验',2=>'待支付',3=>'已支付','4'=>'已拣货','5'=>'已打包','6'=>'已发货','7'=>'已收货','8'=>'已完成','-1'=>'问题件']; ?>
<div class="card">
    <div class="card-body">

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
                        <th>状态</th> 
                        <th>创建时间</th>
                        <th>包裹属性</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                    <tr>
                        <td>
                            <div class="checkbox">
                                <input id="selectable2" type="checkbox">
                                <label for="selectable2"><?= $item['id'] ?></label>
                            </div> 
                        </td>
                        <td><?= $item['order_sn'] ?></td>
						<td><span class="badge badge-pill badge-gradient-success"><?= $item['status'] ?></span></td>
                        <td><?= $item['created_time'] ?></td>
                        <td>
							<span class="badge badge-pill badge-primary">重量：<?= $item['weight'] ?></span>
							<span class="badge badge-pill badge-primary">零食<?= $item['logistics'] ?></span>
						</td>
                        <td class="text-center font-size-18">
                            <a href="" class="text-gray m-r-15"><i class="ti-pencil"></i></a><!--编辑修改--->
                            <a href="" class="text-gray"><i class="ti-trash"></i></a> <!--取消预报-->
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
