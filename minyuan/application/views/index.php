<style>
#chartdiv {
    width   : 100%;
    height  : 400px;
}
</style>
<script type="text/javascript" src="/public/amcharts/amcharts.js"></script>
<script type="text/javascript" src="/public/amcharts/serial.js"></script>
<div class="crumb-wrap">
    <div class="crumb">
        首页
    </div>
</div>

<div class="content">
    <div class="clearfix">
        <div class="sidebar fl">
            <div class="account-info">
                <h2>账户概况</h2>
                <ul>
                    <!--
                    <li>账户余额：0.00元<a class="recharge" href="#">充值</a></li>
                    -->
                    <li>本月消费：<?=$monthCost?>元</li>
                    <li>有效BOX：<?=$activeBox?>个</li>
                    <!--
                    <li>有效单品：2个</li>
                    -->
                </ul>
            </div>
            <div class="message-center">
                <h2>消息中心</h2>
                <ul>
                    <li><span class="warning">【系统公告】</span>互众自助平台beta0.1测试版查询系统正式发布 2014-03-06 11:00:00</li>
                    <li><span class="warning">【系统公告】</span>互众自助平台beta0.2自助物料更换系统即将上线 2014-03-07 09:00:00</li>
                </ul>
            </div>
            <div class="hotline">
                <img src="images/hotline.png" alt="">
                <div class="service">
                    客服人员: 吴晓菁
                    电话: 021-68810966
                </div>
                <div class="mailto">
                    <a href="mailto:wuxiaojing@adeaz.com"></a>
                </div>
            </div>
        </div>
        <div class="main fr">
            <form action="" method="get" id="searchForm">
                <div class="tit clearfix">
                    <h2>投放概况</h2>
                        <div class="fr">
                            <span>To:</span><input style="width:100px;margin-bottom: 0px;" id="datetimepicker2" type="text" name="edate" value="<?=@$end_time?>">
                            <input type="submit" class="btn btn-primary" value="确认" />
                        </div>
                        <div class="fr">
                            <span>From:</span><input style="width:100px;margin-bottom: 0px;" id="datetimepicker1" type="text" name="fdate" value="<?=@$start_time?>">
                        </div>
                </div>
                <ul class="overview clearfix">
                    <li style="background:#84e268;"><?=@$baseInfo['request']?><br/>展现次数</li>
                    <li style="background:#3aaad8;"><?=@$baseInfo['click']?><br/>点击次数</li>
                    <li style="background:#b5ca52;"><?=@$baseInfo['rateClick']?><br/>点击率</li>
                    <li style="background:#f27741;"><?=@$baseInfo['avgClickPrice']?><br/>平均点击价格</li>
                    <li style="background:#e76b8c;"><?=@$baseInfo['thousandResPrice']?><br/>千次展示价格</li>
                    <li style="background:#47b3e2;"><?=@$baseInfo['income']?><br/>消费</li>
                </ul>
                <div class="tit clearfix">
                    <h2>趋势</h2>
                    <select name="search2" id="search2" class="fr" style="margin-left:20px;">
                        <option value="income" <?php if ($search2 == "income") { ?>selected <?php } ?>>消费</option>
                        <option value="request" <?php if ($search2 == "request") { ?>selected <?php } ?>>展示次数</option>
                        <option value="click" <?php if ($search2 == "click") { ?>selected <?php } ?>>点击次数</option>
                        <option value="rateClick" <?php if ($search2 == "rateClick") { ?>selected <?php } ?>>点击率</option>
                        <option value="clickPrice" <?php if ($search2 == "clickPrice") { ?>selected <?php } ?>>平均点击价格</option>
                        <option value="requestPrice" <?php if ($search2 == "requestPrice") { ?>selected <?php } ?>>千次展示价格</option>
                    </select>
                    <select name="search1" id="search1" class="fr">
                        <option value="income" <?php if ($search1 == "income") { ?>selected <?php } ?>>消费</option>
                        <option value="request" <?php if ($search1 == "request") { ?>selected <?php } ?>>展示次数</option>
                        <option value="click" <?php if ($search1 == "click") { ?>selected <?php } ?>>点击次数</option>
                        <option value="rateClick" <?php if ($search1 == "rateClick") { ?>selected <?php } ?>>点击率</option>
                        <option value="clickPrice" <?php if ($search1 == "clickPrice") { ?>selected <?php } ?>>平均点击价格</option>
                        <option value="requestPrice" <?php if ($search1 == "requestPrice") { ?>selected <?php } ?>>千次展示价格</option>
                    </select>
                </div>
                <!-- highchart -->
                <div id="chartdiv"></div>
            </div>
            </form>
        </div>
        <!--
        <div class="tit clearfix">
            <h2>单品展示效果</h2>
        </div>

        <table class="effect-table">
            <thead>
                <th></th>
                <th>推广计划名称</th>
                <th>展现</th>
                <th>点击</th>
                <th>点击率</th>
                <th>消费</th>
                <th>流水订单数量</th>
                <th>成交订单数量</th>
                <th>单订单点击</th>
                <th>单订单成本</th>
                <th>订单成交总额</th>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>魔茶</td>
                    <td>14219</td>
                    <td>254</td>
                    <td>1.79%</td>
                    <td>700.59</td>
                    <td>700.59</td>
                    <td>10</td>
                    <td>1000</td>
                    <td>2000</td>
                    <td>100,000  </td>
                </tr>
                <tr class="odd">
                    <td>2</td>
                    <td>魔茶</td>
                    <td>14219</td>
                    <td>254</td>
                    <td>1.79%</td>
                    <td>700.59</td>
                    <td>700.59</td>
                    <td>10</td>
                    <td>1000</td>
                    <td>2000</td>
                    <td>100,000  </td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>魔茶</td>
                    <td>14219</td>
                    <td>254</td>
                    <td>1.79%</td>
                    <td>700.59</td>
                    <td>700.59</td>
                    <td>10</td>
                    <td>1000</td>
                    <td>2000</td>
                    <td>100,000  </td>
                </tr>
                <tr class="odd">
                    <td>4</td>
                    <td>魔茶</td>
                    <td>14219</td>
                    <td>254</td>
                    <td>1.79%</td>
                    <td>700.59</td>
                    <td>700.59</td>
                    <td>10</td>
                    <td>1000</td>
                    <td>2000</td>
                    <td>100,000  </td>
                </tr>
                <tr>
                    <td>5</td>
                    <td>魔茶</td>
                    <td>14219</td>
                    <td>254</td>
                    <td>1.79%</td>
                    <td>700.59</td>
                    <td>700.59</td>
                    <td>10</td>
                    <td>1000</td>
                    <td>2000</td>
                    <td>100,000  </td>
                </tr>
            </tbody>
        </table> 
    -->
</div>
<script type="text/javascript">
$(function(){
    $("#datetimepicker1, #datetimepicker2").datetimepicker({
        timepicker:false,
        format: 'Y-m-d',
        lang: 'ch',
    });


    //var chartData = generateChartData();
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
            "title": $("#search1").val() == "income" ? "cost" : $("#search1").val(),
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
            "title": $("#search2").val() == "income" ? "cost" : $("#search2").val(),
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

