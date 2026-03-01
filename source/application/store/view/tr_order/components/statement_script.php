<!-- 账单功能JavaScript组件 -->
<script>
$(function() {
    /**
     * 生成账单
     */
    $('#j-create-statement').on('click', function () {
        var selectIds = checker.getCheckSelect();
        
        if (selectIds.length === 0) {
            layer.msg('请先选择订单');
            return;
        }
        
        // 验证是否同一客户
        var memberIds = [];
        $('input[name="checkIds"]:checked').each(function() {
            var memberId = $(this).data('member-id');
            if (memberId && memberIds.indexOf(memberId) === -1) {
                memberIds.push(memberId);
            }
        });
        
        if (memberIds.length === 0) {
            layer.msg('无法获取客户信息');
            return;
        }
        
        if (memberIds.length > 1) {
            layer.msg('只能选择同一客户的订单生成账单');
            return;
        }
        
        // 获取所有Inpack的pack_ids（包裹ID）
        var packageIds = [];
        $('input[name="checkIds"]:checked').each(function() {
            var packIds = $(this).data('pack-ids');
            if (packIds) {
                var ids = packIds.toString().split(',');
                packageIds = packageIds.concat(ids);
            }
        });
        
        if (packageIds.length === 0) {
            layer.msg('选中的订单没有包裹');
            return;
        }
        
        // 确认生成
        layer.confirm('确定为选中的 ' + selectIds.length + ' 个集运单（共 ' + packageIds.length + ' 个包裹）生成账单吗？', {
            title: '生成账单',
            btn: ['确定', '取消']
        }, function(index) {
            $.ajax({
                type: 'POST',
                url: "<?= url('package.statement/create') ?>",
                data: {
                    package_ids: packageIds,
                    member_id: memberIds[0]
                },
                dataType: "json",
                success: function(res) {
                    if (res.code == 1) {
                        layer.msg('账单生成成功', {icon: 1});
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        layer.msg(res.msg, {icon: 2});
                    }
                },
                error: function() {
                    layer.msg('网络错误，请稍后重试', {icon: 2});
                }
            });
            layer.close(index);
        });
    });
});
</script>
