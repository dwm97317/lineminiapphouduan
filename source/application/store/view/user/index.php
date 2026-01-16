<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">用户列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-9 am-u-sm-push-3">
                                <div class="am fr">
                                    <div class="am-form-group am-fl">
                                        <?php $grade = $request->get('grade'); ?>
                                        <select name="grade"
                                                data-am-selected="{btnSize: 'sm', placeholder: '请选择会员等级'}">
                                            <option value=""></option>
                                            <?php foreach ($gradeList as $item): ?>
                                                <option value="<?= $item['grade_id'] ?>"
                                                    <?= $grade == $item['grade_id'] ? 'selected' : '' ?>><?= $item['name'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <?php $gender = $request->get('gender'); ?>
                                        <select name="gender"
                                                data-am-selected="{btnSize: 'sm', placeholder: '请选择性别'}">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $gender === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <option value="1"
                                                <?= $gender === '1' ? 'selected' : '' ?>>男
                                            </option>
                                            <option value="2"
                                                <?= $gender === '2' ? 'selected' : '' ?>>女
                                            </option>
                                            <option value="0"
                                                <?= $gender === '0' ? 'selected' : '' ?>>未知
                                            </option>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="user_id"
                                                   placeholder="请输入用户ID" value="<?= $request->get('user_id') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="user_code"
                                                   placeholder="请输入用户编号" value="<?= $request->get('user_code') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="nickName"
                                                   placeholder="请输入微信昵称"
                                                   value="<?= $request->get('nickName') ?>">
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
                    <div class="page_toolbar am-margin-bottom-xs am-cf" style="margin-bottom:20px; margin-left:15px;">
                        
                        
                        <div class="am-btn-group am-btn-group-xs">
                            <a class="am-btn am-btn-default am-btn-success"
                               href="<?= url('user/addUSer') ?>">
                                <span class="am-icon-plus"></span> 新增
                            </a>
                        </div>
                        
                        <!--发放优惠券-->
                        <?php if (checkPrivilege('user/receive')): ?>
                        <button type="button" id="j-youhuiquan" class="am-btn am-btn-success  am-radius">
                            <i class="iconfont icon-youhuiquan"></i> 发放优惠券
                        </button>
                        <?php endif;?>
                        
                        <?php if (checkPrivilege('user/setpaytype')): ?>
                        <button type="button" id="j-paytype" class="am-btn am-btn-success  am-radius">
                            <i class="iconfont icon-youhuiquan"></i> 设置用户默认支付方式
                        </button>
                        <?php endif;?>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th><input id="checkAll" type="checkbox" ></th>
                                <th>用户ID</th>
                                <th>微信头像</th>
                                <th>会员资料</th>
                                <th>OPEN_ID</th>
                                <th>专属客服</th>
                                <th>默认支付方式</th>
                                <th>交易数据</th>
                                <th>会员唛头</th>
                                <th>时间</th>
                                <th width="300px">操作</th>
                            </tr>
                            </thead>
                            <tbody id="body">
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <?php $typeMap = [0=>'普通用户',1=>'发货仓入库员',2=>'分拣员',3=>'打包员',4=>'签收员',5=>'仓管员','6'=>'到达仓入库员',7=>'专属客服'] ?>
                                <tr>
                                    <td class="am-text-middle">
                                       <input name="checkIds" type="checkbox" value="<?= $item['user_id'] ?>"> 
                                    </td>
                                    <td class="am-text-middle"><?= $item['user_id'] ?></td>
                                    <td class="am-text-middle">
                                        <a href="<?= $item['avatarUrl'] ?>" title="点击查看大图" target="_blank">
                                            <?php if($item['avatarUrl']) :?>
                                                 <img src="<?= $item['avatarUrl'] ?>" width="50" height="50" alt="">
                                            <?php else:?>
                                                 <img src="assets/admin/img/head.jpg" width="50" height="50" alt="">
                                            <?php endif;?>
                                            
                                        </a>
                                    </td>
                                    <td class="am-text-middle">
                                        <?= $item['nickName'] ?> <br> 
                                        <?php if($set['is_show']!=0) :?>
                                             CODE: <span><?= $item['user_code'] ?></span><br>
                                        <?php endif;?>
                                        性别：<?= $item['gender']['text'] ?><br>
                                        <?php if($item['user_type']!=4) :?>
                                             <?= $typeMap[$item['user_type']];?><br>
                                          <?php else:?>
                                              <?php if($item['grade']['name']) :?>
                                                    <?= $item['grade']['name'];?><br>
                                              <?php else:?>
                                               普通会员<br>
                                              <?php endif;?>
                                          <?php endif;?>
                                          <?php if($item['mobile'] !=0 ) :?>
                                            手机号：<?= $item['mobile']; ?>
                                          <?php endif;?>
                                    </td>
                                    <td class="am-text-middle">
                                        开放平台ID:<?= $item['union_id'] ?> <br> 
                                        小程序ID:<?= $item['open_id'] ?> <br>
                                        公众号ID:<?= $item['gzh_openid'] ?> <br>
                                        LINE ID:<?= $item['line_openid'] ?> <br>
                                    </td>
                                     <td class="am-text-middle"><?= $item['service']['real_name'] ?></td>
                                    <!--<?php $usource = [1=>'小程序',2=>'公众号',3=>'PC端',4=>'App'] ?>-->
                                    <!--<td class="am-text-middle"><?= $usource[$item['u_source']] ?></td>-->
                                    <?php $paytype = [0=>'付款发货',1=>'货到付款',2=>'月结'] ?>
                                    <td class="am-text-middle"><?= $paytype[$item['paytype']] ?></td>
                                    <td class="am-text-middle">
                                        用户余额：<?= $item['balance'] ?><br> 
                                        可用积分：<?= $item['points'] ?><br>
                                        实际消费金额：<?= $item['pay_money'] ?>
                                    </td>
                                    <td class="am-text-middle">
                                         <?php if (isset($item['usermark']) && !$item['usermark']->isEmpty()):
                                            foreach ($item['usermark'] as $kry=>$items): ?>
                                            <?php if ($kry <=3): ?>
                                            <?= $items['mark']; ?> - <?= $items['markdes']; ?> <br>
                                        <?php endif;endforeach; endif; ?>
                                        <a href="<?= url('store/user/marklist', ['search' => $item['user_id']]) ?>">
                                            查看更多唛头</a><br>
                                    </td>
                                    <td class="am-text-middle">
                                        注册时间：<?= $item['create_time'] ?><br/>
                                        最近登录：<?= $item['last_login_time'] ?><br/>
                                        最近订单：<?= $item['update_time']=='2000-01-01 00:00:00'?'暂未下单':$item['update_time'] ?><br/>
                                    </td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('user/recharge')): ?>
                                                <a class="j-recharge tpl-table-black-operation-default"
                                                   href="javascript:void(0);"
                                                   title="用户充值"
                                                   data-id="<?= $item['user_id'] ?>"
                                                   data-balance="<?= $item['balance'] ?>"
                                                   data-points="<?= $item['points'] ?>"
                                                >
                                                    <i class="iconfont icon-qiandai"></i>
                                                    充值
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('user/grade')): ?>
                                                <a class="j-zhekou tpl-table-black-operation-default"
                                                   href="javascript:void(0);"
                                                   data-id="<?= $item['user_id'] ?>"
                                                   title="修改会员折扣">
                                                    <i class="iconfont icon-zhekou"></i>
                                                    会员折扣
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('user/grade')): ?>
                                                <a class="j-grade tpl-table-black-operation-default"
                                                   href="javascript:void(0);"
                                                   data-id="<?= $item['user_id'] ?>"
                                                   title="修改会员等级">
                                                    <i class="iconfont icon-grade-o"></i>
                                                    会员等级
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('user/usermark')): ?>
                                                <a class="j-usermark tpl-table-black-operation-default"
                                                   href="javascript:void(0);"
                                                   data-id="<?= $item['user_id'] ?>"
                                                   title="添加会员唛头">
                                                    <i class="iconfont icon-grade-o"></i>
                                                    新增唛头
                                                </a><br />
                                                <span style="padding-bottom:10px;display: block;"></span>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('user/delete')): ?>
                                                <a class="j-delete tpl-table-black-operation-default"
                                                   href="javascript:void(0);"
                                                   data-id="<?= $item['user_id'] ?>" title="删除用户">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('user/edit')): ?>
                                                <a class="tpl-table-black-operation-default"
                                                   href="<?= url('user/edit', ['user_id' => $item['user_id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('user/reset')): ?>
                                                <a class="j-reset tpl-table-black-operation-default"
                                                   href="javascript:void(0);"
                                                   data-id="<?= $item['user_id'] ?>" title="删除用户">
                                                    <i class="am-icon-trash"></i> 重置密码
                                                </a>
                                            <?php endif; ?>
                                            <div class="j-opSelect operation-select am-dropdown">
                                                <button type="button"
                                                        class="am-dropdown-toggle am-btn am-btn-sm am-btn-secondary">
                                                    <span>更多</span>
                                                    <span class="am-icon-caret-down"></span>
                                                </button>
                                                <ul class="am-dropdown-content" data-id="<?= $item['user_id'] ?>">
                                                    <?php if (checkPrivilege('tr_order/all_list')): ?>
                                                        <li>
                                                            <a class="am-dropdown-item" target="_blank"
                                                               href="<?= url('tr_order/all_list', ['user_id' => $item['user_id']]) ?>">用户订单</a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if (checkPrivilege('user.recharge/order')): ?>
                                                        <li>
                                                            <a class="am-dropdown-item" target="_blank"
                                                               href="<?= url('user.recharge/order', ['user_id' => $item['user_id']]) ?>">充值记录</a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if (checkPrivilege('user.balance/log')): ?>
                                                        <li>
                                                            <a class="am-dropdown-item" target="_blank"
                                                               href="<?= url('user.balance/log', ['user_id' => $item['user_id']]) ?>">余额明细</a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if (checkPrivilege('user.balance/log')): ?>
                                                        <li>
                                                            <a class="am-dropdown-item" target="_blank"
                                                               href="<?= url('user/discountlist', ['user_id' => $item['user_id']]) ?>">折扣路线</a>
                                                        </li>
                                                    <?php endif; ?>
                                                        <li>
                                                            <a class="am-dropdown-item j-UpdateCode"
                                                               href="javascript:void(0);"
                                                             data-id="<?= $item['user_id'] ?>">修改用户Code</a>
                                                        </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="12" class="am-text-center">暂无记录</td>
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
                        <select name="line[id]"
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
                    <label class="am-u-sm-3 am-form-label"> 折扣 </label>
                    <div class="am-u-sm-8 am-u-end">
                                <textarea rows="2" name="line[discount]" placeholder="请输入折扣，如0.95"
                                          class="am-field-valid"></textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>
<!--修改用户code--->
<script id="tpl-usercode" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        输入用户code
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text" name="user_code" value="">
                    </div>
                </div>
    
            </div>
        </form>
    </div>
</script>
<!-- 模板：修改会员等级 -->
<script id="tpl-grade" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        会员等级
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <select name="grade[grade_id]"
                                data-am-selected="{btnSize: 'sm', placeholder: '请选择会员等级'}">
                            <option value="0">无等级</option>
                            <?php foreach ($gradeList as $item): ?>
                                <option value="<?= $item['grade_id'] ?>"
                                    <?= $grade == $item['grade_id'] ? 'selected' : '' ?>><?= $item['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label"> 管理员备注 </label>
                    <div class="am-u-sm-8 am-u-end">
                                <textarea rows="2" name="grade[remark]" placeholder="请输入管理员备注"
                                          class="am-field-valid"></textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>
<script id="tpl-paytype" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
               <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择用户数量
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                       <p class='am-form-static'> 共选中 {{ selectCount }} 用户</p>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择付款方式
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                          <select name="paytype"
                                data-am-selected="{btnSize: 'sm', placeholder: '请选择默认付款方式'}">
                                <option value="0">付款发货</option>
                                <option value="1">货到付款</option>
                                <option value="2">月结</option>
                        </select>
                    </div>
                </div>
                
            </div>
        </form>
    </div>
</script>
<!-- 模板：修改会员唛头 -->
<script id="tpl-usermark" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label"> 唛头编号 </label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text" name="mark[mark]" value="" placeholder="请输入唛头编号">
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label"> 使用场景描述 </label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="text" name="mark[markdes]" value="" placeholder="请输入唛头用途">
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>
<!-- 模板：用户充值 -->
<script id="tpl-recharge" type="text/template">
    <div class="am-padding-xs am-padding-top-sm">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="j-tabs am-tabs">

                <ul class="am-tabs-nav am-nav am-nav-tabs">
                    <li class="am-active"><a href="#tab1">充值余额</a></li>
                    <li><a href="#tab2">充值积分</a></li>
                </ul>

                <div class="am-tabs-bd am-padding-xs">

                    <div class="am-tab-panel am-padding-0 am-active" id="tab1">
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                当前余额
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <div class="am-form--static">{{ balance }}</div>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                充值方式
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <label class="am-radio-inline">
                                    <input type="radio" name="recharge[balance][mode]"
                                           value="inc" data-am-ucheck checked>
                                    增加
                                </label>
                                <label class="am-radio-inline">
                                    <input type="radio" name="recharge[balance][mode]" value="dec" data-am-ucheck>
                                    减少
                                </label>
                                <label class="am-radio-inline">
                                    <input type="radio" name="recharge[balance][mode]" value="final" data-am-ucheck>
                                    最终金额
                                </label>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                变更金额
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <input type="number" min="0" class="tpl-form-input"
                                       placeholder="请输入要变更的金额" name="recharge[balance][money]" value="" required>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                管理员备注
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <textarea rows="2" name="recharge[balance][remark]" placeholder="请输入管理员备注"
                                          class="am-field-valid"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="am-tab-panel am-padding-0" id="tab2">
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                当前积分
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <div class="am-form--static">{{ points }}</div>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                充值方式
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <label class="am-radio-inline">
                                    <input type="radio" name="recharge[points][mode]"
                                           value="inc" data-am-ucheck checked>
                                    增加
                                </label>
                                <label class="am-radio-inline">
                                    <input type="radio" name="recharge[points][mode]" value="dec" data-am-ucheck>
                                    减少
                                </label>
                                <label class="am-radio-inline">
                                    <input type="radio" name="recharge[points][mode]" value="final" data-am-ucheck>
                                    最终积分
                                </label>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                变更数量
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <input type="number" min="0" class="tpl-form-input"
                                       placeholder="请输入要变更的数量" name="recharge[points][value]" value="" required>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                管理员备注
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <textarea rows="2" name="recharge[points][remark]" placeholder="请输入管理员备注"
                                          class="am-field-valid"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</script>
<script id="tpl-youhuiquan" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
               <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择包裹数量
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                       <p class='am-form-static'> 共选中 {{ selectCount }} 用户</p>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择优惠券
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                          <select name="coupon_id"
                                data-am-selected="{btnSize: 'sm', placeholder: '请选择优惠券'}">
                                <?php if (isset($coupon) && !$coupon->isEmpty()):
                                    foreach ($coupon as $item): ?>
                                        <option value="<?= $item['coupon_id'] ?>"><?= $item['name'] ?></option>
                                <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>
                
            </div>
        </form>
    </div>
</script>
<script>
    $(function () {
               checker = {
          num:0, 
          check:[],
          init:function(){
              this.check = document.getElementById('body').getElementsByTagName('input');
              this.num = this.check.length;
              this.bindEvent();
          },
          bindEvent:function(){
              var that = this;
              for(var i=0; i< this.check.length; i++){
                  this.check[i].onclick = function(){
                       var _check = that.isFullCheck();
                       if (_check){
                           document.getElementById('checkAll').checked = 'checked';
                       }else{
                           document.getElementById('checkAll').checked = '';
                       }
                  }
              }
              
              var  allCheck = document.getElementById('checkAll');
              allCheck.onclick = function(){
                  if (this.checked){
                      that.setFullCheck();
                  }else{
                      that.setFullCheck('');
                  }
              }
              
          },
          setFullCheck:function(checked='checked'){
             for (var ik =0; ik<this.num; ik++){
                  this.check[ik].checked = checked; 
              } 
          },
          isFullCheck:function(){
              var hasCheck = 0;
              for (var k =0; k<this.num; k++){
                   if (this.check[k].checked){
                       hasCheck++;
                   }
              }
              return hasCheck==this.num?true:false;
          },
          getCheckSelect:function(){
              var selectIds = [];
              for (var i=0;i<this.check.length;i++){
                    if (this.check[i].checked){
                       selectIds.push(this.check[i].value);
                    }
              }
              return selectIds;
          }
       }
       
       checker.init();
       
       /**
         * 批量设置用户默认支付方式
         */
        $('#j-paytype').on('click', function(){
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            if (selectIds.length==0){
                layer.alert('请先选择用户', {icon: 5});
                return;
            }
            $.showModal({
                title: '批量设置用户默认支付方式'
                , area: '460px'
                , content: template('tpl-paytype', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('store/user/setpaytype') ?>',
                        data: {
                            user_id:data.selectId
                        }
                    });
                    return true;
                }
            });
        });
        /**
         * 批量发优惠券
         */
        $('#j-youhuiquan').on('click', function(){
            var $tabs, data = $(this).data();
            var selectIds = checker.getCheckSelect();
            if (selectIds.length==0){
                layer.alert('请先选择用户', {icon: 5});
                return;
            }
            if (selectIds.length>1){
                layer.alert('只能选择1个用户', {icon: 5});
                return;
            }
            data.selectId = selectIds.join(',');
            data.selectCount = selectIds.length;
            $.showModal({
                title: '批量发送优惠券'
                , area: '460px'
                , content: template('tpl-youhuiquan', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('store/user/receive') ?>',
                        data: {
                            user_id:data.selectId
                        }
                    });
                    return true;
                }
            });
        });
       
       
       /**
         * 合并订单
         */
        $('.j-reset').on('click', function () {
            var $tabs, data = $(this).data();
            console.log(data);
            var hedanurl = "<?= url('store/user/reset') ?>";
            layer.confirm('网页端登录密码将被重置为123456', {title: '密码重置'}
                    , function (index) {
                        $.post(hedanurl, data, function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                        layer.close(index);
                    });
        });
        
        /**
         * 账户充值
         */
        $('.j-recharge').on('click', function () {
            var $tabs, data = $(this).data();
            $.showModal({
                title: '用户充值'
                , area: '460px'
                , content: template('tpl-recharge', data)
                , uCheck: true
                , success: function ($content) {
                    $tabs = $content.find('.j-tabs');
                    $tabs.tabs({noSwipe: 1});
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('user/recharge') ?>',
                        data: {
                            user_id: data.id,
                            source: $tabs.data('amui.tabs').activeIndex
                        }
                    });
                    return true;
                }
            });
        });
        
        //修改用户Code
        $('.j-UpdateCode').on('click', function(){
            var data = $(this).data();
            $.showModal({
                title: '修改用户Code'
                , area: '460px'
                , content: template('tpl-usercode', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('store/user/edituserCode') ?>',
                        data: {
                            user_id: data.id,
                        }
                    });
                    return true;
                }
            });
        });
        
        /**
         * 修改会员等级
         */
        $('.j-grade').on('click', function () {
            var data = $(this).data();
            $.showModal({
                title: '修改会员等级'
                , area: '460px'
                , content: template('tpl-grade', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('user/grade') ?>',
                        data: {user_id: data.id}
                    });
                    return true;
                }
            });
        });
        
        /**
         * 修改会员等级
         */
        $('.j-usermark').on('click', function () {
            var data = $(this).data();
            $.showModal({
                title: '新增会员等级'
                , area: '460px'
                , content: template('tpl-usermark', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('user/usermark') ?>',
                        data: {user_id: data.id}
                    });
                    return true;
                }
            });
        });
         
        
        /**
         * 修改会员折扣
         */
        $('.j-zhekou').on('click', function () {
            var data = $(this).data();
            $.showModal({
                title: '修改会员折扣'
                , area: '460px'
                , content: template('tpl-zhekou', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('user/discount') ?>',
                        data: {user_id: data.id}
                    });
                    return true;
                }
            });
        });

        /**
         * 注册操作事件
         * @type {jQuery|HTMLElement}
         */
        var $dropdown = $('.j-opSelect');
        $dropdown.dropdown();
        $dropdown.on('click', 'li a', function () {
            var $this = $(this);
            var id = $this.parent().parent().data('id');
            var type = $this.data('type');
            if (type === 'delete') {
                layer.confirm('删除后不可恢复，确定要删除吗？', function (index) {
                    $.post("index.php?s=/store/apps.dealer.user/delete", {dealer_id: id}, function (result) {
                        result.code === 1 ? $.show_success(result.msg, result.url)
                            : $.show_error(result.msg);
                    });
                    layer.close(index);
                });
            }
            $dropdown.dropdown('close');
        });

        // 删除元素
        var url = "<?= url('user/delete') ?>";
        $('.j-delete').delete('user_id', url, '删除后不可恢复，确定要删除吗？');

    });
</script>

