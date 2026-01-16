<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">折扣记录</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xl am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am fr">
                                <div class="am-form-group am-fl">
                                    <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                        <input type="text" class="am-form-field" name="search" placeholder="请输入用户昵称"
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
                                <th>ID</th>
                                <th>微信头像</th>
                                <th>微信昵称</th>
                                <th>路线信息</th>
                                <th>折扣</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle">
                                        <a href="<?= $item['avatarUrl'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $item['avatarUrl'] ?>" width="72" height="72" alt="">
                                        </a>
                                    </td>
                                    <td class="am-text-middle">
                                        <p class=""><?= $item['nickName'] ?></p>
                                        <p class="am-link-muted">(用户ID：<?= $item['user_id'] ?>)</p>
                                    </td>
                                    <td class="am-text-middle">
                                        路线id:<?= $item['line_id'] ?: '--' ?><br>
                                        路线名称：<?= $item['name'] ?: '--' ?>
                                       
                                    </td>
                                    <td class="am-text-middle"><?= $item['discount'] ?: '--' ?></td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <?php if (checkPrivilege('user/editdiscount')): ?>   
                                        <a class="j-zhekou tpl-table-black-operation-default" href="javascript:void(0);" style="margin-right:10px" title="修改会员折扣"   data-id="<?= $item['user_id'] ?>" data-lineid="<?= $item['line_id'] ?>" data-name="<?= $item['name'] ?>"  >
                                            <i class="am-icon-pencil"></i> 编辑
                                        </a>
                                        <?php endif; ?>
                                        <?php if (checkPrivilege('user/deletediscount')): ?>    
                                        <a class="j-delete tpl-table-black-operation-default"
                                            href="javascript:void(0);" data-id="<?= $item['id'] ?>" title="删除路线折扣">
                                            <i class="am-icon-trash"></i> 删除
                                        </a>
                                        <?php endif; ?>
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
<!-- 模板：修改会员折扣 -->
<script id="tpl-zhekou" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        集运路线
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <select
                                data-am-selected="{btnSize: 'sm', placeholder: '请选择路线'}">
                            <option value="0">{{name}}</option>
                        </select>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label"> 折扣 </label>
                    <div class="am-u-sm-8 am-u-end">
                                <textarea rows="2" name="discount" placeholder="请输入折扣，如0.95"
                                          class="am-field-valid"></textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>
<script>
    $(function () {
        // 删除元素
        var url = "<?= url('user/deletediscount') ?>";
        $('.j-delete').delete('id', url, '删除后不可恢复，确定要删除吗？');
        
        /**
         * 修改会员折扣
         */
        $('.j-zhekou').on('click', function () {
            var data = $(this).data();
            $.showModal({
                title: '修改会员折扣'
                , area: '460px'
                , content: template('tpl-zhekou', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('user/editdiscount') ?>',
                        data: {user_id: data.id,line_id:data.lineid}
                    });
                    return true;
                }
            });
        });
        
        
    });
</script>

