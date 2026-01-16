  <div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
            <div class="widget-body">
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">API接口- 本账号的【APPID：<?= $store['wxapp']['wxapp_id'] ?>】【TOKEN：<?= $store['wxapp']['token'] ?>】</div>
                    </div>
                      <section data-am-widget="accordion" class="am-accordion am-accordion-gapped" data-am-accordion='{  }'>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>  
                              <dl class="am-accordion-item am-active">
                                <dt class="am-accordion-title">
                                  <?= $item['api_title'] ?> - <?= $item['create_time'] ?>
                                </dt>
                                <dd class="am-accordion-bd am-collapse am-in">
                                  <!-- 规避 Collapase 处理有 padding 的折叠内容计算计算有误问题， 加一个容器 -->
                                  <div class="am-accordion-content">
                                        <?= $item['api_content'] ?>
                                  </div>
                                </dd>
                              </dl>
                              <?php endforeach; else: ?>
                              
                              <dl class="am-accordion-item">
                                <dt class="am-accordion-title">
                                    暂无更新
                                </dt>
                                <dd class="am-accordion-bd am-collapse ">
                                </dd>
                              </dl>
                            <?php endif; ?>
                    </section>

                     
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>