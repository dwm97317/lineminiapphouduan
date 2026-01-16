<div class="card">
    <div class="card-header border bottom">
        <h4 class="card-title">路线详情</h4>
    </div>
    <div class="detail-head">
         <div class="p-v-10 p-h-20 d-inline-block">	
            <img style="width:100px;height:100px;" class="thumb-img img-circle pull-left" alt="" src="<?= $data['image']?$data['image']['file_path']:'/assets/api/images/dzx_img179.png' ?>">
            <div class="info">
                <!--<span class="d-block font-size-16 text-dark">Erin Gonzales</span>-->
                <!--<span class="font-size-12">To: lukeskywalker@gmail.com</span>-->
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-5">
                
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">渠道名称:</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext"><?= $data['name'] ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">计费规则:</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext"><?= $data['free_mode'] ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">时效:</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext"><?= $data['limitationofdelivery'] ?>天</p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">最小重量:</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext"><?= $data['weight_min'] ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">最大重量:</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext"><?= $data['max_weight'] ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">关税说明:</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext"><?= $data['tariff'] ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">国家支持:</label>
                    <div class="col-sm-9">
                        <?php foreach($data['country'] as $v):?>
                          <span class="badge badge-default"><?= $v['title'] ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">类目支持:</label>
                    <div class="col-sm-9">
                         <?php foreach($data['category'] as $v):?>
                          <span class="badge badge-default"><?= $v['name'] ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">重量限制说明:</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext"><?= $data['weight_limit'] ?></p>
                    </div>
                </div>
				<div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">线路特点</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext"><?= $data['line_special'] ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">物品限制</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext"><?= $data['goods_limit'] ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">体积限制说明</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext"><?= $data['length_limit'] ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">更多规则:</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext"><?= htmlspecialchars_decode($data['line_content']) ?></p>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>
