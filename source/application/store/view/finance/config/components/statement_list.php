<!-- 账单列表组件 -->
<div class="am-tab-panel am-fade" id="tab-statement-list">
    <!-- 筛选工具栏 -->
    <div class="page_toolbar am-margin-bottom-xs am-cf">
        <form class="toolbar-form" id="statementSearchForm">
            <div class="am-u-sm-12 am-u-md-12">
                <div class="am">
                    <!-- 客户选择 -->
                    <div class="am-form-group am-fl">
                        <select name="member_id" id="statement_member_id" data-am-selected="{btnSize: 'sm', placeholder: '选择客户', searchBox: 1}">
                            <option value="">全部客户</option>
                        </select>
                    </div>
                    
                    <!-- 支付状态 -->
                    <div class="am-form-group am-fl">
                        <select name="pay_status" data-am-selected="{btnSize: 'sm', placeholder: '支付状态'}">
                            <option value="">全部</option>
                            <option value="1">未支付</option>
                            <option value="2">已支付</option>
                        </select>
                    </div>
                    
                    <!-- 账单状态 -->
                    <div class="am-form-group am-fl">
                        <select name="status" data-am-selected="{btnSize: 'sm', placeholder: '账单状态'}">
                            <option value="">全部</option>
                            <option value="1">正常</option>
                            <option value="2">已作废</option>
                        </select>
                    </div>
                    
                    <!-- 日期范围 -->
                    <div class="am-form-group am-fl">
                        <input type="text" name="start_date" class="am-form-field statement-date" placeholder="开始日期" readonly>
                    </div>
                    <div class="am-form-group am-fl">
                        <input type="text" name="end_date" class="am-form-field statement-date" placeholder="结束日期" readonly>
                    </div>
                    
                    <!-- 关键词搜索 -->
                    <div class="am-form-group am-fl">
                        <input type="text" name="keyword" class="am-form-field" placeholder="账单编号/客户姓名">
                    </div>
                    
                    <!-- 搜索按钮 -->
                    <div class="am-form-group am-fl">
                        <button type="submit" class="am-btn am-btn-primary am-btn-sm">
                            <i class="am-icon-search"></i> 搜索
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- 统计信息 -->
    <div class="am-margin-bottom-sm" id="statement_statistics">
        <span class="am-badge am-badge-primary">总账单：<span id="stat_total">0</span></span>
        <span class="am-badge am-badge-success">已支付：<span id="stat_paid">0</span></span>
        <span class="am-badge am-badge-warning">未支付：<span id="stat_unpaid">0</span></span>
        <span class="am-badge am-badge-secondary">已作废：<span id="stat_void">0</span></span>
        <span class="am-badge am-badge-primary">总金额：¥<span id="stat_amount">0.00</span></span>
    </div>

    <!-- 账单列表 -->
    <div class="am-scrollable-horizontal am-u-sm-12">
        <table class="am-table am-table-striped am-table-hover am-table-bordered">
            <thead>
            <tr>
                <th>账单编号</th>
                <th>客户</th>
                <th>订单数</th>
                <th>总重量(KG)</th>
                <th>总金额(元)</th>
                <th>支付状态</th>
                <th>账单状态</th>
                <th>创建时间</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody id="statementListBody">
            <!-- 动态填充 -->
            </tbody>
        </table>
    </div>

    <!-- 分页 -->
    <div class="am-u-lg-12 am-cf">
        <div class="am-fr" id="statement_pagination"></div>
    </div>
</div>
