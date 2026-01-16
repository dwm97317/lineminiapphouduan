<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">汇款凭证列表</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>打款用户</th>
                                <th>凭证图片</th>
                                <th>支付流水号</th>
                                <th>打款金额</th>
                                <th>打款银行</th>
                                <th>打款时间</th>
                                <th>打款货币</th>
                                <th>状态</th>
                                <th>提交时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $item['id'] ?></td>
                                        <td class="am-text-middle">
                                            <p>ID:<?= $item['user']['user_id'] ?></p>
                                            <p>昵称：<?= $item['user']['nickName'] ?></p>
                                        </td>
                                        <td> 
                                        <?php foreach ($item['image'] as $it): ?>
                                            <a href="<?= $it['file_path'] ?>" title="点击查看大图" target="_blank">
                                                <?php if($it['file_path']) :?>
                                                     <img src="<?= $it['file_path'] ?>" width="30" height="30" alt="">
                                                <?php endif;?>
                                              </a>
                                        <?php endforeach; ?>
                                            
                                        </td>
                                        <td class="am-text-middle"><?= $item['cert_order'] ?></td>
                                        <td class="am-text-middle"><?= $item['cert_price'] ?></td>
                                        <td class="am-text-middle"><?= $item['cert_bank'] ?></td>
                                        <td class="am-text-middle"><?= date("Y-m-d",strtotime($item['cert_date'])) ?></td>
                                        <?php $cert_type= [0=>'人民币',1=>'新币',2=>'泰铢',3=>'美元',4=>'欧元',5=>'日元',6=>'韩元',7=>'其他']; ?>
                                        <td class="am-text-middle"><?= $cert_type[$item['cert_type']] ?></td>
                                        <!--状态  1 待审核 2 确认打款 3 信息有误-->
                                        <?php $type = [1=>'待审核',2=>'审核通过',3=>'信息有误',''=>'未知']; ?>
                                        <td class="am-text-middle"><?= $type[$item['cert_status']] ?></td>
                                        <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation ">
                                                 <?php if ($item['cert_status']==1): ?>
                                                    <a class='j-upstatus' href="javascript:;" data-id="<?= $item['id'] ?>"><i class="am-icon-pencil"></i> 审核
                                                    </a>
                                                <?php endif; ?>
                                                    <a href="javascript:;"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $item['id'] ?>"> 删除
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
<script id="tpl-status" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择状态
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                          <select name="certificate[status]"
                                data-am-selected="{btnSize: 'sm', placeholder: '请选择线路'}">
                               <option value="2">审核通过</option>
                               <option value="3">信息有误</option>
                        </select>
                        <p class='am-form-static'>注意：更改状态后，请手动添加余额</p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>
<script>
    $(function () {

        // 删除元素
        var url = "<?= url('setting.certificate/delete') ?>";
        $('.item-delete').delete('id', url);

    });
    
      /**
         * 修改凭证状态
         */
        $('.j-upstatus').on('click', function () {
       
            var data = $(this).data();
            console.log(data);
            $.showModal({
                title: '凭证状态'
                , area: '460px'
                , content: template('tpl-status', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('setting.certificate/updateStatus') ?>',
                        data: {
                            id:data.id
                        }
                    });
                    return true;
                }
            });
        });
    
    
</script>

