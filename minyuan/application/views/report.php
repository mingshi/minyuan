<ul class="sub-menu nav">
    <li class="active"><a href="#">整体报告</a></li>
    <li><a href="#">尺寸报告</a></li>
    <li><a href="#">素材报告</a></li>
</ul>

<div class="search-block span10">
    <form method="get" action="?">
    <dl>
        <dt>推广名称：<dt>
        <dd><?=form_dropdown(
            'status',
            array('' => '选择后自动查找推广名称'),
            '',
            'style="width:200px"'
           )?>
            <span>(可多选)</span>
        <dd>
    </dl>
    <dl>
        <dt>BOX名称：<dt>
        <dd><?=form_dropdown(
            'status',
            array('' => '选择后自动查找BOX名称'),
            '',
            'style="width:200px"'
           )?>
            <span>(可多选)</span>
        <dd>
    </dl>
    <dl>
        <dt>时间段：<dt>
        <dd><?=form_dropdown(
            'status',
            array('' => '最近7天'),
            '',
            'style="width:100px"'
           )?>
            <span>(可多选)</span>
        <dd>
    </dl>
    <dl>
        <dd class="fr"><input type="submit" class="btn" value="生成报告" />
        </dd>
    </dl>
    </form>
</div>

<div class="chart-wrapper span12">
    <span id="data1-name" class="fl">pv</span>
    <span>
        <img src=""/>
        <?=form_dropdown(
            'status',
            array('pv' => 1, '点击' => 2, 'ctr' => 3, 'ecpm' => 4, 'ecpc' => 5),
            '',
            'style="width:100px"'
           )?>
        <img src=""/>
        <?=form_dropdown(
            'status',
            array('pv' => 1, '点击' => 2, 'ctr' => 3, 'ecpm' => 4, 'ecpc' => 5),
            '',
            'style="width:100px"'
           )?>
    </span>
    <span id="data2-name" class="fr">点击</span>
    <div class="chart" style="height: 250px;">
        
    </div>
</div>

<div class="data-list">
    <table class="table">
        <tr>
            <th>时间</th>
            <th>消耗金额(￥)</th>
            <th>曝光数</th>
            <th>点击数</th>
            <th>点击率(%)</th>
            <th>eCPM(￥)</th>
            <th>eCPC(￥)</th>
            <th>流水订单数量</th>
            <th>成交订单数量</th>
            <th>单订单点击</th>
            <th>单订单成本</th>
            <th>订单成交总额</th>
        </tr>
        <?php foreach(array(1) as $v):?>
        <tr>
            <td><?=$v?></td>
            <td><?=$v?></td>
            <td><?=$v?></td>
            <td><?=$v?></td>
            <td><?=$v?></td>
            <td><?=$v?></td>
            <td><?=$v?></td>
            <td><?=$v?></td>
            <td><?=$v?></td>
            <td><?=$v?></td>
            <td><?=$v?></td>
            <td><?=$v?></td>
        </tr>
        <?php endforeach;?>
    </table>
</div>