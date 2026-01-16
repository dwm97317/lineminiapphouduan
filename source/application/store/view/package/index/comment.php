
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">订单评价</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>订单单号</th>
                                <th>包裹单号</th>
                                <th>评论用户</th>
                                <th>评论内容</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $item['comment_id'] ?></td>
                                        <td class="am-text-middle"><?= $item['package']['order_sn'] ?></td>
                                        <td class="am-text-middle"><?= $item['package']['express_num'] ?></td>
                                        <td class="am-text-middle"><?= $item['user_id'].'-'.$item['user']['nickName']; ?></td>
                                        <td class="am-text-middle"><?= $item['content'] ?></td>
                                        <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <a href="javascript:void(0);"
                                               class="item-delete tpl-table-black-operation-del"
                                               data-id="<?= $item['comment_id'] ?>" onclick="tools.init(this)" data-mode='request' data-confirm=true data-confirm_text='请确认是否删除！' data-refresh=true data-url='<?= url('store/package.index/commentdelete') ?>'>
                                                <i class="am-icon-trash"></i> 删除
                                            </a>
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
                      
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {

    });
</script>

