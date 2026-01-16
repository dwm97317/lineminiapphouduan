<?php
use app\common\enum\BatchType as BatchTypeEnum;
?>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">批次列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <?php if ($type==0) : ?>
                                    <?php if (checkPrivilege('batch/addbatch')): ?>
                                        <div class="am-btn-group am-btn-group-xs">
                                            <a class="am-btn am-btn-default am-btn-success"
                                               href="<?= url('batch/addbatch') ?>">
                                                <span class="am-icon-plus"></span> 新增
                                            </a>
                                        </div>
                                    <?php endif; endif; ?>
                                </div>
                            </div>

                        </form>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>批次ID</th>
                                <th>批次名称</th>
                                <th>批次类型</th>
                                <th>装箱代码</th>
                                <th>目标仓库</th>
                                <th>物流模板</th>
                                <th>批次信息(长/宽/高/重/体积)</th>
                                <th>状态</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['batch_id'] ?></td>
                                    <td class="am-text-middle"><?= $item['batch_name'] ?></td>
                                    <td class="am-text-middle"><?= BatchTypeEnum::data()[$item['batch_type']]['name'] ?></td>
                                    <td class="am-text-middle"><?= $item['batch_no'] ?></td>
                                    <td class="am-text-middle"><?= $item['shop']['shop_name'] ?></td>
                                    <td class="am-text-middle"><?= $item['template']['template_name']?$item['template']['template_name']:'未选择' ?></td>
                                    <td class="am-text-middle">
                                        <span class="am-badge am-badge-success">长</span>
                                            <?= $item['length'] ?>/<?= $item['width'] ?>/<?= $item['height'] ?><br>
                                            重：<?= $item['weigth'] ?><br>
                                            体积：<?= $item['wegihtvol'] ?>
                                       </td>
                                    <?php $status=[0=>'待发货',1=>'运送中',2=>'已到达'] ?>
                                    <td class="am-text-middle">
                                            <span class="am-badge am-badge-warning">
                                               <?= $status[$item['status']] ?>
                                           </span>
                                    </td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('batch/editbatch')): ?>
                                                <a href="<?= url('batch/editbatch', ['batch_id' => $item['batch_id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('batch/deletebatch')): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['batch_id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (checkPrivilege('batch/logistics')): ?>
                                            <a href="javascript:void(0);" class="j-wuliu tpl-table-black-operation-green " data-id="<?= $item['batch_id'] ?>"> 
                                                <i class="iconfont icon-755danzi "></i> 物流更新
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                        <div style="margin-top:10px;" class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('batch/batchvsinpack')): ?>
                                            <a href="<?= url('batch/batchvsinpack', ['id' => $item['batch_id']]) ?>">
                                            查看批次订单
                                            </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('batch/batchvspack')): ?>
                                            <a  class="tpl-table-black-operation-del" href="<?= url('batch/batchvspack', ['id' => $item['batch_id']]) ?>">
                                            查看批次包裹
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
<script id="tpl-wuliu" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        输入物流状态
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text" name="logistics_describe" value="">
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择物流时间
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text"  name="created_time" placeholder="请选择起始日期" value="<?php echo date("Y-m-d H:i:s",time()) ?>" id="datetimepicker" class="am-form-field">
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>
<script>
    $(function () {
        // 删除元素
        var url = "<?= url('batch/deletebatch') ?>";
        $('.item-delete').delete('batch_id', url, '删除后不可恢复，确定要删除吗？');
        
    });
    
    /**
     * 批量手动更新物流信息
     */
    $('.j-wuliu').on('click', function () {
        var $tabs, data = $(this).data();
        console.log(data);
        $.showModal({
            title: '更新批次订单物流轨迹'
            , area: '460px'
            , content: template('tpl-wuliu', data)
            , uCheck: true
            , success: function ($content) {
            }
            , yes: function ($content) {
                $content.find('form').myAjaxSubmit({
                    url: '<?= url('store/batch/logistics') ?>',
                    data: {
                        batch_id:data.id,
                    }
                });
                return true;
            }
    });
    
   
    $('#datetimepicker').datetimepicker({
      format: 'yyyy-mm-dd hh:ii'
    });
    
    
    $('#datetimepicker').datetimepicker().on('changeDate', function(ev){
        $('#datetimepicker').datetimepicker('hide');
      });
        
    });
</script>

