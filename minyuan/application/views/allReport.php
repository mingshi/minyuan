<style>
#chartdiv {
    width   : 100%;
    height  : 400px;
}
</style>
<link rel="stylesheet" type="text/css" href="/public/css/jquery.multiselect.css" />
<script src="/public/js/jquery.multiselect.min.js"></script>
<script type="text/javascript" src="/public/amcharts/amcharts.js"></script>
<script type="text/javascript" src="/public/amcharts/serial.js"></script>
<link href="/public/css/theme.default.css" rel="stylesheet">
<script type="text/javascript" src="/public/js/jquery.tablesorter.min.js"></script>
<div class="crumb-wrap">
    <div class="crumb">
        整体报告
    </div>
</div>

<div class="center-box">
    <div class="center-wrapper">
        <div class="search-block">
            <form id="searchForm" action="" method="get">
                推广计划:
                <select multiple="multiple" style="width:150px; overflow: hidden;" name="cid[]" id="cid">
                    <optgroup label="投放计划">
                    <?php if(!empty($campaigns)) { foreach ($campaigns as $_cid => $value) { ?>
                            <option value="<?=$_cid?>" <?php if (in_array($_cid, $cids)) { ?>   selected    <?php } ?>><?=$value['campaign']['name']?> </option> 
                    <?php }} ?>
                    </optgroup>
                </select>

                <span style="margin-left:10px;">box名称:</span>
                <select multiple="multiple" name="bid[]" id="bid" style="width:150px; overflow: hidden;">
                    <optgroup label="Box">
                    <?php if (!empty($boxes)) { foreach ($boxes as $bid => $bname) { ?>
                        <option value="<?=$bid?>" <?php if (in_array($bid, $bids)) { ?> selected  <?php } ?>><?=$bname?></option>
                    <?php }} ?>
                    </optgroup>
                </select>

                <span style="margin-left:10px;">From:</span><input style="width:100px;margin-bottom: 0px;" id="datetimepicker1" type="text" name="fdate" value="<?=@$start_time?>">

                <span style="margin-left:10px;">To:</span><input style="width:100px;margin-bottom: 0px;" id="datetimepicker2" type="text" name="edate" value="<?=@$end_time?>"> 

                <input type="submit" class="btn btn-primary" style="margin-left:10px;" value="搜索" />
        </div>

        <div class="tit clearfix" style="margin-top:30px;">
        </div>

        <div style="margin-top: 20px;">
            <select style="margin-left:20px;" class="fr" id="search2" name="search2">
                <option value="request" <?php if ($search2 == "request") { ?>selected <?php } ?>>pv</option>
                <option value="click" <?php if ($search2 == "click") { ?>selected <?php } ?>>点击</option>
                <option value="rateClick" <?php if ($search2 == "rateClick") { ?>selected <?php } ?>>ctr</option>
                <option value="ecpm" <?php if ($search2 == "ecpm") { ?>selected <?php } ?>>ecpm</option>
                <option value="ecpc" <?php if ($search2 == "ecpc") { ?>selected <?php } ?>>ecpc</option>
            </select>

            <select class="fr" id="search1" name="search1">
                <option value="request" <?php if ($search1 == "request") { ?>selected <?php } ?>>pv</option>
                <option value="click" <?php if ($search1 == "click") { ?>selected <?php } ?>>点击</option>
                <option value="rateClick" <?php if ($search1 == "rateClick") { ?>selected <?php } ?>>ctr</option>
                <option value="ecpm" <?php if ($search1 == "ecpm") { ?>selected <?php } ?>>ecpm</option>
                <option value="ecpc" <?php if ($search1 == "ecpc") { ?>selected <?php } ?>>ecpc</option>
            </select>
        </div>
        </form>

        <div id="chartdiv" style="margin-top: 30px;"></div>
        <table class="table table-hover table-striped table-bordered tablesorter-default" style="margin-top: 30px;" id="reportTable">
            <script>
            $(document).ready(function() {
             $("#reportTable").tablesorter();
            });
            </script>
            
            <thead>
                <tr style="cursor:pointer;">
                    <th>日期</th>
                    <th>消费</th>
                    <th>曝光</th>
                    <th>点击</th>
                    <th>点击率(%)</th>
                    <th>eCPM(&yen;)</th>
                    <th>eCPC(&yen;)</th>
                    <th>流水订单数量</th>
                    <th>成交订单数量</th>
                    <th>单订单点击</th>
                    <th>单订单成本</th>
                    <th>订单成交总额</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reportInfo as $_date => $value) { ?>
                <tr>
                    <td><?=$_date?></td>
                    <td><?=$value['income']?></td>
                    <td><?=$value['request']?></td>
                    <td><?=$value['click']?></td>
                    <td><?=$value['rateClick']?></td>
                    <td><?=$value['ecpm']?></td>
                    <td><?=$value['ecpc']?></td>
                    <td><?=$value['order_total']?></td>
                    <td><?=$value['order_deal']?></td>
                    <td><?=$value['eock']?></td>
                    <td><?=$value['eoco']?></td>
                    <td><?=$value['order_money']?></td>
                </tr>
                <?php } ?>
            </tbody>
            <tbody>
                <tr class="info">
                    <td>总计</td>
                    <td><?=$total['income']?></td>
                    <td><?=$total['request']?></td>
                    <td><?=$total['click']?></td>
                    <td><?=$total['rateClick']?></td>
                    <td><?=$total['ecpm']?></td>
                    <td><?=$total['ecpc']?></td>
                    <td><?=$total['order_total']?></td>
                    <td><?=$total['order_deal']?></td>
                    <td><?=$total['eock']?></td>
                    <td><?=$total['eoco']?></td>
                    <td><?=$total['order_money']?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
$(function() {
    $("#datetimepicker1, #datetimepicker2").datetimepicker({
        timepicker:false,
        format: 'Y-m-d',
        lang: 'ch',
    });

    $('#cid').multiselect({
        checkAllText : '全选',
        uncheckAllText : '取消全选',
        noneSelectedText : '选择相应选项',
        selectedText : function(selected_count, total_count, selected_elems) {
            var cids = $.map(selected_elems, function(elem) { return $(elem).val() });
            return cids.length + "个选中";
        },
        height : 400,
        close : function() {}
    });
    $('#bid').multiselect({
        checkAllText : '全选',
        uncheckAllText : '取消全选',
        noneSelectedText : '选择相应选项',
        selectedText : function(selected_count, total_count, selected_elems) {
            var bids = $.map(selected_elems, function(elem) { return $(elem).val() });
            return bids.length + "个选中";
        },
        height : 400,
        close : function() {}
    });

    var chartData = <?=$chartData?>;
    var chart = AmCharts.makeChart("chartdiv", {
        "type": "serial",
        "theme": "none",
        "pathToImages": "/public/amcharts/images/",
        "legend": {
            "useGraphSettings": true
        },
        "dataProvider": chartData,
        "valueAxes": [{
            "id":"v1",
            "axisColor": "#3AAAD8",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "left",
            "minimum": 0,
        }, {
            "id":"v2",
            "axisColor": "#B5CA52",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "right",
            "minimum": 0,
        }],
        "graphs": [{
            "valueAxis": "v1",
            "lineColor": "#3AAAD8",
            "bullet": "round",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": $("#search1").val(),
            "valueField": "<?=$search1?>",
            <?php if ($search1 == "rateClick") { ?>
            "balloonText": "[[value]]%",
            <?php } ?>
            "fillAlphas": 0
        }, {
            "valueAxis": "v2",
            "lineColor": "#B5CA52",
            "bullet": "square",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": $("#search2").val(),
            "valueField": "<?=$search2?>",
            <?php if ($search2 == "rateClick") { ?>
            "balloonText": "[[value]]%",
            <?php } ?>
            "fillAlphas": 0
        }],
        "chartScrollbar": {},
        "chartCursor": {
            "cursorPosition": "mouse"
        },
        "categoryField": "date",
        "categoryAxis": {
            "parseDates": true,
            "axisColor": "#DADADA",
            "minorGridEnabled": true
        }
    });

    chart.addListener("dataUpdated", zoomChart);
    zoomChart();


    // generate some random data, quite different range
    function zoomChart(){
        chart.zoomToIndexes(chart.dataProvider.length - 20, chart.dataProvider.length - 1);
    }

    $("#search1, #search2").change(function() {
        $("#searchForm").submit();
    });
});

</script>
