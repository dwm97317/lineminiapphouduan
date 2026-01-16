<div class="card">
    <div class="card-body m-v-40">
        <div class="row">
            <div class="col-sm-10 offset-sm-1">
                <div class="row">
                    <div class="col-sm-3">
                        <h5><b>常见问题</b></h5>
                        <ul class="list-unstyled m-t-30 nav d-block" role="tablist">
                            <li class="m-b-20 d-block">
                                <a href="#faq-tab-1" onclick="changestatus(1)" class="text-semibold font-size-16 text-gray active" role="tab" data-toggle="tab">新手问题</a>
                            </li>
                            <li class="m-b-20 d-block">
                                <a href="#faq-tab-2" onclick="changestatus(2)" class="text-semibold font-size-16 text-gray" role="tab" data-toggle="tab">禁用物品</a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-sm-9">
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane fade in active" id="faq-tab-1">
                                <div class="accordion borderless" id="general" role="tablist">
                                    <?php if (!$newhand->isEmpty()): foreach ($newhand as $item): ?>
                                        <div class="card">
                                            <div class="card-header" role="tab">
                                                <h5 class="card-title">
                                                    <a data-toggle="collapse" href="#collapseOneBorderless" aria-expanded="true">
                                                        <span><?= $item['article_title'] ?></span>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapseOneBorderless" class="collapse show" data-parent="#general">
                                                <div class="card-body">
                                                    <p><?= $item['article_content'] ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; else: ?>
                                        <div class="card">
                                            <div class="card-header" role="tab">
                                                <h5 class="card-title">
                                                    <a data-toggle="collapse" href="#collapseOneBorderless" aria-expanded="true">
                                                        <span>啥都没有</span>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapseOneBorderless" class="collapse show" data-parent="#general">
                                                <div class="card-body">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane  fade in " id="faq-tab-2">
                                <div class="accordion borderless" id="services" role="tablist">
                                    <?php if (!$bidding->isEmpty()): foreach ($bidding as $item): ?>
                                    <div class="card">
                                        <div class="card-header" role="tab">
                                            <h5 class="card-title">
                                                <a data-toggle="collapse" href="#collapseSixBorderless" aria-expanded="true">
                                                    <span><?= $item['article_title'] ?></span>
                                                </a>
                                            </h5>
                                        </div>
                                        <div id="collapseSixBorderless" class="collapse show" data-parent="#services">
                                            <div class="card-body">
                                                <p><?= $item['article_content'] ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; else: ?>
                                        <div class="card">
                                            <div class="card-header" role="tab">
                                                <h5 class="card-title">
                                                    <a data-toggle="collapse" href="#collapseOneBorderless" aria-expanded="true">
                                                        <span>啥都没有</span>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapseOneBorderless" class="collapse show" data-parent="#services">
                                                <div class="card-body">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                      
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
 function changestatus(e){
    if(e==1){
        $("#faq-tab-2").removeClass('active');
        $("#faq-tab-1").addClass('active');
    }
    if(e==2){
        $("#faq-tab-1").removeClass('active');
        $("#faq-tab-2").addClass('active');
    }
 }
</script>					