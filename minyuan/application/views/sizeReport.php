<link href="/public/css/jquery.multiselect.css" rel="stylesheet">
<link href="/public/css/theme.default.css" rel="stylesheet">
<script src="/public/js/jquery.multiselect.min.js"></script>
<script src="/public/js/jquery.tablesorter.min.js"></script>
<div class="crumb-wrap">
    <div class="crumb">
        尺寸报告
    </div>
</div>

<div class="center-box">
<div class="center-wrapper">
<div class="search-block span10">
    <form method="get" action="?">
    <dl>
        <dt>推广名称：<dt>
        <dd><select multiple="multiple" style="width:150px; overflow: hidden;" name="cid[]" id="campaign">
                    <optgroup label="投放计划">
                    <?php if(!empty($campaign)) { foreach ($campaign as $_cid => $value) { ?>
                            <option value="<?=$_cid?>" <?php if (in_array($_cid, $cids)) { ?>   selected    <?php } ?>><?=$value?> </option> 
                    <?php }} ?>
                    </optgroup>
                </select>
            <span>(可多选)</span>
        <dd>
    </dl>
    <dl>
        <dt>BOX名称：<dt>
        <dd><select multiple="multiple" name="bid[]" style="width:150px; overflow: hidden;" id="box">
                <optgroup label="Box">
                <?php if (!empty($box)) { foreach ($box as $bid => $bname) { ?>
                    <option value="<?=$bid?>" <?php if (in_array($bid, $bids)) { ?> selected  <?php } ?>><?=$bname?></option>
                <?php }} ?>
                </optgroup>
            </select>
            <span>(可多选)</span>
        <dd>
    </dl>
    <dl>
        <dt>广告尺寸：<dt>
        <dd><?=form_dropdown(
            'size',
            array('' => '选择后自动查找尺寸') + @$sizes,
            @$param['size'],
            'style="width:200px"'
           )?>

        <dd>
    </dl>
    <dl>
        <dt>时间段：<dt>
        <dd><span style="width:50px;display:inline-block;">From:</span><input style="width:100px;margin-bottom: 0px;" id="datetimepicker1" type="text" name="fdate" value="<?=@$param['fdate']?>"><br>
            <span style="width:50px;display:inline-block;margin-left:58px;">To:</span><input style="width:100px;margin-bottom: 0px;" id="datetimepicker2" type="text" name="edate" value="<?=@$param['edate']?>"> 
        <dd>
    </dl>
    <dl style="clear:both;">
        <dd><input type="submit" class="btn" value="生成报告" />
        </dd>
    </dl>
    </form>
</div>

<div class="data-list clearfix">
    <table class="table tablesorter-default">
        <thead>
        <tr>
            <th>广告尺寸</th>
            <th>消耗金额(￥)</th>
            <th>曝光数</th>
            <th>点击数</th>
            <th>点击率(%)</th>
            <th>eCPM(￥)</th>
            <th>eCPC(￥)</th>
        </tr>
        </thead>
        <tbody>
        <?php if(count($items) > 0):?>
        <?php $sum = array('income' => 0, 'request' => 0, 'click' => 0);?>
        <?php foreach($items as $k => $v):?>
        <?php
            $sum['income'] += $v['income'];
            $sum['request'] += $v['request'];
            $sum['click'] += $v['click'];
        ?>
        <tr>
            <td><?=$k?></td>
            <td><?=$v['income']?></td>
            <td><?=$v['request']?></td>
            <td><?=$v['click']?></td>
            <td><?=$v['rateClick']?></td>
            <td><?=$v['ecpm']?></td>
            <td><?=$v['ecpc']?></td>
        </tr>
        <?php endforeach;?>
        </tbody>
        <?php
            $sum['rateClick'] = $sum['request'] != 0 ? round($sum['click'] / $sum['request'] * 100, 2) . '%' : 0;
            $sum['ecpm'] = $sum['request'] != 0 ? round($sum['income'] / $sum['request'] * 1000, 2) : 0;
            $sum['ecpc'] = $sum['click'] != 0 ? round($sum['income'] / $sum['click'], 2) : 0;
        ?>
        <tr class="info">
            <td><strong>汇总：</strong></td>
            <td><?=$sum['income']?></td>
            <td><?=$sum['request']?></td>
            <td><?=$sum['click']?></td>
            <td><?=$sum['rateClick']?></td>
            <td><?=$sum['ecpm']?></td>
            <td><?=$sum['ecpc']?></td>
        </tr>
        <?php else:?>
        </tbody>
        <?php endif;?>
    </table>
</div>

</div>
</div>
<script>
    $(function(){
       $("#datetimepicker1,#datetimepicker2").datepicker({
            timepicker:false,
            format: 'Y-m-d',
            lang: 'ch',
        });

       $(".data-list table").tablesorter({
           usNumberFormat : false,
           sortReset      : true,
           sortRestart    : true
       });

       $('#campaign,#box').multiselect({
            checkAllText : '全选',
            uncheckAllText : '取消全选',
            noneSelectedText : '选择相应选项',
            selectedText : function(selected_count, total_count, selected_elems) {
                var items = $.map(selected_elems, function(elem) { return $(elem).val() });
                return items.length + "个选中";
            },
            height : 400,
            close : function() {}
        });

    });
</script>
