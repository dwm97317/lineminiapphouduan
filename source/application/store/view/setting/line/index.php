<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">集运线路</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <?php if (checkPrivilege('setting.line/add')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('setting.line/add') ?>">
                                            <span class="am-icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                        </div>
                    </div>

                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12">
                                <div class="am fl">
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="name"
                                                   placeholder="请输入渠道名称"
                                                   value="<?= $request->get('name') ?>">
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
                    
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th>线路ID</th>
                                <th>路线名称</th>
                                <th>限重</th>
                                <th>时效</th>
                                <th>关税</th>
                                <th width='700px'>限制条件</th>      
                                <th>计费方式</th>
                                <th width='40px'>排序</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $item['id'] ?></td>
                                        <td class="am-text-middle"><?= $item['name'] ?></td>
                                        <td class="am-text-middle"><?= $item['weight_min'] ?></td>
                                        <td class="am-text-middle"><?= $item['limitationofdelivery'] ?></td>
                                        <td class="am-text-middle"><?= $item['tariff'] ?></td>
                                        <td  class="am-text-middle">物品限制:<?= $item['goods_limit'] ?></br>长度限制:<?= $item['length_limit'] ?></br>重量限制:<?= $item['weight_limit'] ?></br></td>
                                        <td class="am-text-middle"><?= $item['free_mode'] ?></td>
                                        <td class="am-text-middle"><?= $item['sort'] ?></td>
                                        <td class="am-text-middle"><?= date('Y-m-d',$item['created_time']) ?></td>
                                        <td class="am-text-middle" width="180px">
                                            <div class="tpl-table-black-operation">
                                                 <?php if (checkPrivilege('setting.line/copyline')): ?>
                                                    <a href="javascript:;" class="tpl-table-black-operation-green fuyong" data-id="<?= $item['id'] ?>"> 
                                                        <i class="am-icon-pencil fuyong"></i> 复用
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('setting.line/edit')): ?>
                                                    <a href="<?= url('setting.line/edit',
                                                        ['id' => $item['id']]) ?>"
                                                         class="tpl-table-black-operation-green`">
                                                        <i class="am-icon-pencil"></i> 编辑
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('setting.line/delete')): ?>
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
            var url = "<?= url('store/setting.line/delete') ?>";
            $('.item-delete').delete('id', url);
            
            
            /**
             * 代用户打包
             */
            $('.fuyong').on('click', function () {
            var $tabs, data = $(this).data();
            var hedanurl = "<?= url('store/setting.line/copyline') ?>";
            layer.confirm('确认复用则会生成一条规则一样的集运路线哦！，为了方便用户选择，请修改路线名称或规则', {title: '复用路线'}
                    , function (index) {
                        $.post(hedanurl,data, function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                        layer.close(index);
                    });
            }); 

        });
</script>

