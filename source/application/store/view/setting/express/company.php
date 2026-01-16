<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-body">
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">物流公司编码表</div>
                    </div>
                    <div class="tips am-margin-bottom-sm">
                        <div class="pre">
                            <p>友情提示：可使用Ctrl+F键 快速检索</p>
                        </div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table class="regional-table am-table am-table-bordered
                                         am-table-centered am-margin-bottom-xs">
                            <tbody>
                            <tr>
                                <th width="150px">公司编码</th>
                                <th>国际名称</th>
                                <th>中国名称</th>
                                <th>香港名称</th>
                                <th>URL</th>
                            </tr>
                            <?php if (isset($track)): foreach ($track as $item): ?>
                            <tr>
                                <td><?= $item['key'] ?></td>
                                <td><?= $item['_name'] ?></td>
                                <td><?= $item['_name_zh-cn'] ?></td>
                                <td><?= $item['_name_zh-hk'] ?></td>
                                <td><?= $item['_url'] ?></td>
                            </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>