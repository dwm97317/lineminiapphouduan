<style>
    .am-selected-list{
        height: 300px;
        overflow: scroll;}
</style>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">运费查询<small style="padding-left:10px;color:#1686ef">(提示：查询到运费后可以导出运费清单以供客户选择)</small></div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                             <div class="am-u-sm-12 am-u-md-12">
                                <div class="am">
                         
                                    <div class="am-form-group am-fl">
                                        <select   name="country"
                                                data-am-selected="{btnSize: 'sm', placeholder: '请选择目标国家'}">
                                             <?php if (isset($country)): foreach ($country as $item): ?>
                                                 <option value="<?= $item['id'] ?>"  <?= $request->get('id') == $item['id'] ? 'selected' : '' ?>><?= $item['title'] ?></option>
                                             <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                     <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="weight"
                                                   placeholder="重量(kg)" value="<?= $request->get('weight') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field x-w-80" name="weigthV"
                                                   placeholder="体积" value="<?= $request->get('weigthV') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field x-w-80" name="length"
                                                   placeholder="长度" value="<?= $request->get('length') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field x-w-80" name="width"
                                                   placeholder="宽度" value="<?= $request->get('width') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field x-w-80" name="height"
                                                   placeholder="高度" value="<?= $request->get('height') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        
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
                                <th>路线ID</th>
                                <th>路线名称</th>
                                <th>路线规则</th>
                                <th>计费重量(kg)</th>
                                <th>时效(天)</th>
                                <th>路线价格(元)</th>
                            </tr>
                            </thead>
                            <tbody id="body">
                                <?php if (count($list)>0): foreach ($list as $item): ?>
                                 <tr>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle"><?= $item['name'] ?></td>
                                    <td class="am-text-middle">
                                        最小：<?= $item['weight_min'] ?>kg<br>
                                        最大：<?= $item['max_weight'] ?>kg
                                    </td>
                                    <td class="am-text-middle"><?= $item['predict']['weight'] ?></td>
                                    <td class="am-text-middle"><?= $item['limitationofdelivery'] ?></td>
                                    <td class="am-text-middle"><?= $item['predict']['price'] ?></td>
                                 <tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                      <td colspan="11" class="am-text-center">暂无记录</td>
                                    </tr>
                                 <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"></div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



