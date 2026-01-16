<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">会员地址列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <div class="am-btn-group am-btn-group-xs">
                                    <?php if (checkPrivilege('user.address/add')): ?>
                                    <a class="am-btn am-btn-default am-btn-success am-radius"
                                       href="<?= url('user.address/add') ?>">
                                        <span class="am-icon-plus"></span> 新增
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am fr">
                                <div class="am-form-group am-fl">
                                    <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                        <input type="text" class="am-form-field" name="search" placeholder="请输入昵称/用户ID"
                                               value="<?= $request->get('search') ?>">
                                        <div class="am-input-group-btn">
                                            <button class="am-btn am-btn-default am-icon-search" type="submit"></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>地址ID</th>
                                <th>所属用户</th>
                                <th>收件人姓名</th>
                                <th>手机号</th>
                                <th>国家</th>
                                <th>省/市/区</th>
                                <th>详细地址</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['address_id'] ?></td>
                                    <td class="am-text-middle">
                                        用户昵称：<?= $item['nickName'] ?><br>
                                        <?php if($set['is_show']!=0) :?>
                                             用户Code: <span><?= $item['user_code'] ?></span>
                                        <?php endif;?>
                                        <?php if($set['is_show']!=1) :?>
                                             用户ID: <span><?= $item['user_id'] ?></span>
                                        <?php endif;?>
                                     
                                    <td class="am-text-middle"><?= $item['name'] ?></td>
                                    <td class="am-text-middle"><?= $item['phone'] ?></td>
                                    <td class="am-text-middle"><?= $item['country'] ?></td>
                                    <td class="am-text-middle">
                                        <span><?= $item['province'] ?>/<?= $item['city'] ?>/<?= $item['region'] ?></span>
                                    </td>
                                    <td class="am-text-middle">
                                        <span><?= $item['detail'] ?></span>
                                    </td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('user.address/edit')): ?>
                                                <a class="tpl-table-black-operation-default"
                                                   href="<?= url('user.address/edit', ['address_id' => $item['address_id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('user.address/delete')): ?>
                                                <a class="j-delete tpl-table-black-operation-default"
                                                   href="javascript:void(0);" data-id="<?= $item['address_id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="8" class="am-text-center">暂无记录</td>
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
        var url = "<?= url('user.address/delete') ?>";
        $('.j-delete').delete('address_id', url);

    });
</script>

