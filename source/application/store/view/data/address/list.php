<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="renderer" content="webkit"/>
    <link rel="stylesheet" href="assets/common/css/amazeui.min.css"/>
    <link rel="stylesheet" href="assets/store/css/app.css?v=<?= $version ?>"/>
    <script src="assets/common/js/jquery.min.js"></script>
    <title>地址列表</title>
</head>
<body class="select-data">
<!-- 工具栏 -->
<div class="page_toolbar am-margin-bottom-xs am-cf">
    <form class="toolbar-form" action="">
        <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
        <div class="am-u-sm-12">
            <div class="am fr">
                <div class="am-form-group am-fl">
                    <div class="am-input-group am-input-group-sm tpl-form-border-form">
                        <input type="text" class="am-form-field" name="title"
                               placeholder="请输入国家名称或代码"
                               value="<?= $request->get('title') ?>">
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
    <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black am-text-nowrap">
        <thead>
        <tr>
            <th>
                <label class="am-checkbox">
                    <input data-am-ucheck data-check="all" type="checkbox">
                </label>
            </th>
            <th>地址ID</th>
            <th>收件人</th>
            <th>电话</th>
            <th>国家</th>
            <th>详细地址</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
            <tr>
                <td class="am-text-middle">
                    <label class="am-checkbox">
                        <input data-am-ucheck data-check="item" data-params='<?= json_encode([
                            'address_id' => (string)$item['address_id'],
                            'name' => (string)$item['name'],
                            'phone' => (string)$item['phone'],
                        ], JSON_UNESCAPED_SLASHES) ?>' type="checkbox">
                    </label>
                </td>
                <td class="am-text-middle"><?= $item['address_id'] ?></td>
                <td class="am-text-middle"><?= $item['name'] ?></td>
                <td class="am-text-middle"><?= $item['phone'] ?></td>
                <td class="am-text-middle"><?= $item['country'] ?></td>
                <td class="am-text-middle"><?= $item['detail'] ?></td>
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

<script src="assets/common/js/amazeui.min.js"></script>
<script>

    /**
     * 获取已选择的数据
     * @returns {Array}
     */
    function getSelectedData() {
        var data = [];
        $('input[data-check=item]:checked').each(function () {
            data.push($(this).data('params'));
        });
        return data;
    }

    $(function () {

        // 全选框元素
        var $checkAll = $('input[data-check=all]')
            , $checkItem = $('input[data-check=item]')
            , itemCount = $checkItem.length;

        // 复选框: 全选和反选
        $checkAll.change(function () {
            $checkItem.prop('checked', this.checked);
        });

        // 复选框: 子元素
        $checkItem.change(function () {
            if (!this.checked) {
                $checkAll.prop('checked', false);
            } else {
                var checkedItemNum = $checkItem.filter(':checked').length;
                checkedItemNum === itemCount && $checkAll.prop('checked', true);
            }
        });

    });
</script>
</body>
</html>
