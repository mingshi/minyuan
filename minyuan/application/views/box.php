<link href="/public/css/theme.default.css" rel="stylesheet">
<script src="/public/js/jquery.tablesorter.min.js"></script>
<div class="crumb-wrap">
    <div class="crumb">
        BOX管理
    </div>
</div>
<div class="center-box">
<div class="center-wrapper">
<div class="search-block">
    <form method="get" action="?" class="clearfix">
        <?=form_dropdown(
            'status',
            array('' => '全部状态', Model_Box_Campaign::CAMPAIGN_ACTIVE => '有效', Model_Box_Campaign::CAMPAIGN_PAUSE => '暂停'),
            @$param['status'],
            'style="width:100px; float: left;"'
        )?>
        <input name="kwd" type="text" class="txt search-style" value="<?=@$param['kwd']?>" />
        <input type="submit" class="btn btn-primary search-style" value="查询" />
        <span class="search-style"><input id="accurate" name="accurate" type="checkbox" <?php if(@$param['accurate'] == 1):?>checked<?php endif;?> value="1" />精确查询</span>
    </form>
</div>

<div class="data-list">
    <table class="table tablesorter-default">
        <thead>
        <tr>
            <th>&nbsp;</th>
            <th>BOX名称</th>
            <th>状态</th>
            <th>推广计划</th>
            <th>BOX价格</th>
            <th>展现</th>
            <th>点击</th>
            <th>转化<span class="qustion-tips">？</span></th>
            <th>点击率</th>
            <th>平均点击价格</th>
            <th>转化率<span class="qustion-tips">？</span></th>
            <th>平均转化成本<span class="qustion-tips">？</span></th>
            <th>有效期</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($items as $k => $v):?>
        <tr>
            <td><input type="checkbox" value="<?=$k?>"/></td>
            <td><?=$v['bname']?></td>
            <td><?=$v['status']['des']?></td>
            <td><?=$v['cname']?></td>
            <td><?=$v['totalPrice']?></td>
            <td><?=$v['request']?></td>
            <td><?=$v['click']?></td>
            <td><?=$v['order_deal']?></td>
            <td><?=$v['rateClick']?></td>
            <td><?=$v['avgClickPrice']?></td>
            <td><?=$v['rateDeal']?></td>
            <td><?=$v['avgDealPrice']?></td>
            <td><?=$v['end_time']?></td>
        </tr>
        <?php endforeach;?>
        </tbody>
    </table>
</div>

</div>
</div>

<script>
    $(function(){
        $(".data-list table").tablesorter({
            usNumberFormat : false,
            sortReset      : true,
            sortRestart    : true
        });
    });
</script>
