<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">仓库列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <?php if (checkPrivilege('shop/add')): ?>
                                        <div class="am-btn-group am-btn-group-xs">
                                            <a class="am-btn am-btn-default am-btn-success"
                                               href="<?= url('shop/add') ?>">
                                                <span class="am-icon-plus"></span> 新增
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="am-u-sm-12 am-u-md-9">
                                <div class="am fr">
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="search"
                                                   placeholder="请输入仓库名称/联系人/电话"
                                                   value="<?= $request->get('search') ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
                                            </div>
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
                                <th>仓库ID</th>
                                <th>仓库名称</th>
                                <th>仓库门头</th>
                                <th>营业时间</th>
                                <th>联系人</th>
                                <th>联系电话</th>
                                <th>邮编</th>
                                <th>仓库地址</th>
                                <th>仓库状态</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['shop_id'] ?></td>
                                    <td class="am-text-middle"><?= $item['shop_name'] ?></td>
                                    <td class="am-text-middle">
                                        <a href="<?= $item['logo']['file_path'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $item['logo']['file_path'] ?>" width="72" height="72" alt="">
                                        </a>
                                    </td>
                                    <td class="am-text-middle"><?= $item['shop_hours'] ?></td>
                                    <td class="am-text-middle"><?= $item['linkman'] ?></td>
                                    <td class="am-text-middle"><?= $item['phone'] ?></td>
                                    <td class="am-text-middle"><?= $item['post'] ?></td>
                                    <td class="am-text-middle">
                                        <?= $item['region']['province'] ?>  <?= $item['region']['city'] ?>  <?= $item['region']['region'] ?>
                                        <?= $item['address'] ?>
                                    </td>
                                    <td class="am-text-middle">
                                            <span class="am-badge am-badge-<?= $item['status'] ? 'success' : 'warning' ?>">
                                               <?= $item['status'] ? '启用' : '禁用' ?>
                                           </span>
                                    </td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('shop/edit')): ?>
                                                <a href="<?= url('shop/edit', ['shop_id' => $item['shop_id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('shop/delete')): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['shop_id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('shop/discount')): ?>
                                            <a class="j-zhekou tpl-table-black-operation-default"
                                                   href="javascript:void(0);"
                                                   data-id="<?= $item['shop_id'] ?>"
                                                   title="修改会员折扣">
                                                    <i class="iconfont icon-zhekou"></i>
                                                    设置路线分成
                                            </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('shop/servicediscount')): ?>
                                            <a class="j-service tpl-table-black-operation-default"
                                                   href="javascript:void(0);"
                                                   data-id="<?= $item['shop_id'] ?>"
                                                   title="修改会员折扣">
                                                    <i class="iconfont icon-zhekou"></i>
                                                    设置服务分成
                                            </a>
                                            <?php endif; ?>
                                        </div>
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
                        <select name="bonus[line_id]"
                                data-am-selected="{btnSize: 'sm', placeholder: '请选择路线'}">
                            <option value="0">请选择路线</option>
                            <?php foreach ($line as $item): ?>
                                <option value="<?= $item['id'] ?>"><?= $item['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        分成类型
                    </label>
                    <div class="am-u-sm-9">
                        <label class="am-radio-inline">
                            <input type="radio" name="bonus[bonus_type]" value="0"
                                   data-am-ucheck required checked>
                            固定金额
                        </label>
                        <label class="am-radio-inline">
                            <input type="radio" name="bonus[bonus_type]" value="1"
                                   data-am-ucheck>
                            按运费比例
                        </label>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        寄取类型
                    </label>
                    <div class="am-u-sm-9">
                        <label class="am-radio-inline">
                            <input type="radio" name="bonus[sr_type]" value="0"
                                   data-am-ucheck required checked>
                            寄件
                        </label>
                        <label class="am-radio-inline">
                            <input type="radio" name="bonus[sr_type]" value="1"
                                   data-am-ucheck>
                            取件
                        </label>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label"> 折扣 </label>
                    <div class="am-u-sm-8 am-u-end">
                                <textarea rows="2" name="bonus[proportion]" placeholder="请输入折扣，如12"
                                          class="am-field-valid"></textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>
<!-- 模板：修改服务项目分成 -->
<script id="tpl-service" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        服务项目
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <select name="bonus[line_id]"
                                data-am-selected="{btnSize: 'sm', placeholder: '请选择路线'}">
                            <option value="0">请选择路线</option>
                            <?php foreach ($service as $item): ?>
                                <option value="<?= $item['id'] ?>"><?= $item['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        分成类型
                    </label>
                    <div class="am-u-sm-9">
                        <label class="am-radio-inline">
                            <input type="radio" name="bonus[bonus_type]" value="0"
                                   data-am-ucheck required checked>
                            固定金额
                        </label>
                        <label class="am-radio-inline">
                            <input type="radio" name="bonus[bonus_type]" value="1"
                                   data-am-ucheck>
                            按运费比例
                        </label>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        寄取类型
                    </label>
                    <div class="am-u-sm-9">
                        <label class="am-radio-inline">
                            <input type="radio" name="bonus[sr_type]" value="0"
                                   data-am-ucheck required checked>
                            寄件
                        </label>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label"> 折扣 </label>
                    <div class="am-u-sm-8 am-u-end">
                                <textarea rows="2" name="bonus[proportion]" placeholder="请输入折扣，如12"
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
        var url = "<?= url('shop/delete') ?>";
        $('.item-delete').delete('shop_id', url, '删除后不可恢复，确定要删除吗？');
        
        
        /**
         * 设置仓库分成规则折扣
         */
        $('.j-zhekou').on('click', function () {
            var data = $(this).data();
            $.showModal({
                title: '设置仓库分成规则'
                , area: '460px'
                , content: template('tpl-zhekou', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('shop/discount') ?>',
                        data: {shop_id: data.id}
                    });
                    return true;
                }
            });
        });
        
        /**
         * 设置仓库分成规则折扣
         */
        $('.j-service').on('click', function () {
            var data = $(this).data();
            $.showModal({
                title: '设置服务项目分成规则'
                , area: '460px'
                , content: template('tpl-service', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('shop/servicediscount') ?>',
                        data: {shop_id: data.id}
                    });
                    return true;
                }
            });
        });
    });
</script>

