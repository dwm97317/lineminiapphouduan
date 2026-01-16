<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">国家地区短信前缀</div>
                </div>
                <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <?php if (checkPrivilege('setting.printer/add')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('setting.printer/add') ?>">
                                            <span class="am-icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th>中文名</th>
                                <th>英文名</th>
                                <th>手机号前缀</th>
                                <th>费率</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (count($list)>0): ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $item['cname'] ?></td>
                                        <td class="am-text-middle"><?= $item['ename'] ?></td>
                                        <td class="am-text-middle"><?= $item['areanum'] ?></td>
                                        <td class="am-text-middle"><?= $item['fee'] ?></td>
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

