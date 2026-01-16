<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">国家支持</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <?php if (checkPrivilege('setting.country/add')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('setting.country/add') ?>">
                                            <span class="am-icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <?php if (checkPrivilege('setting.country/copy')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <button type="button" id="j-copycontry" class="am-btn am-btn-warning am-radius">复用测试后台国家清单</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>国家名称</th>
                                <th>代码</th>
                                <th>是否热门</th>
                                <th>是否默认</th>
                                <th>排序</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $item['id'] ?></td>
                                        <td class="am-text-middle"><?= $item['title'] ?></td>
                                        <td class="am-text-middle"><?= $item['code'] ?></td>
                                        <td class="am-text-middle"><?= $item['is_hot']==1?"热门":'' ?></td>
                                        <td class="am-text-middle"><?= $item['is_top']==1?"默认":'' ?></td>
                                        <td class="am-text-middle"><?= $item['sort'] ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('setting.country/edit')): ?>
                                                    <a href="<?= url('setting.country/edit',
                                                        ['id' => $item['id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 编辑
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('setting.country/delete')): ?>
                                                    <a href="javascript:;"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $item['id'] ?>">
                                                        <i class="am-icon-trash"></i> 删除
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $list->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
// 删除元素
        var url = "<?= url('store/setting.country/delete') ?>";
        $('.item-delete').delete('id', url);
        
        /**
         * 批量手动更新物流信息
         */
        $('#j-copycontry').on('click', function () {
            $.ajax({
                type:"POST",
                url:'<?= url('store/setting.country/copy') ?>',
                data:{},
                dataType:"JSON",
                success:function(result){
                    layer.alert(result.msg)
                    location.reload();
                }
            })
            
        });
        
    });
</script>

