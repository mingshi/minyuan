<!DOCTYPE html>
<html> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<title><?=@$__title ? "$title - " : ""?><?=SITE_NAME?></title> 
<link type="text/css" rel="stylesheet" href="/public/css/smoothness/jquery-ui-1.9.1.custom.min.css" />

<link type="text/css" rel="stylesheet" href="/public/bootstrap/css/bootstrap.min.css" />
<link type="text/css" rel="stylesheet" href="/public/bootstrap/css/bootstrap-responsive.min.css" />
<script type="text/javascript" src="/public/js/jquery-1.8.2.min.js"></script>
<script type="text/javascript" src="/public/js/jquery-ui-1.9.1.custom.min.js"></script>

<link type="text/css" rel="stylesheet" href="/css/main.css?v=1113" />
<link rel="stylesheet" href="http://ep.fasteng.net/common/base.min.css">
<link type="text/css" rel="stylesheet" href="/css/box.css?v=1118" />

<link type="text/css" rel="stylesheet" href="/datetimepicker/jquery.datetimepicker.css" />
<script type="text/javascript" src="/datetimepicker/jquery.datetimepicker.js"></script>

</head> 

<body>
   <div class="navbar navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <a class="brand" href="/">敏远后台管理系统</a>
                    <div class="nav-collapse">
                        <ul class="nav">
                            <?php if (@$me['id']) :?>
                                <li class="active"><a href="/create">添加订单状态</a></li>
                            <?php endif;?>

                            <?php if (@$me['id'] && @$me['is_admin']) :?>
                                <li><a href="/export">导出Excel</a></li>
                            <?php endif;?>
                        </ul>
                    </div>

                    <div class="brand" style="float:right;">
                        Hello!
                        <?php if (@$me['id']) :?>
                            <?=$me['username']?>
                            <a href="/logout" style="font-size:12px">退出</a>
                        <?php else :?>
                            浪子
                        <?php endif;?>
                    </div>
                </div>
            </div>
        </div> 
    
    <div class="container" style="width: 100%">
        <?=$__content?>
    </div>
    

    <div id="popup-msg" class="alert" style="display:none"></div>
    <?php if (@$__msg) :?>
    <script>
    $(function(){
        popup_msg(<?=escape_js_quotes($__msg['msg'], TRUE)?>, <?=escape_js_quotes($__msg['type'], TRUE)?>);
    });
    </script>
    <?php endif;?>
 
    <div class="footer-wrap">
        <div class="footer">
            <p id="other-link">
                <!--
                <a href="#">关于我们</a>|
                <a href="#">联系客服</a>|
                <a href="#">合作伙伴</a>
                -->
            </p>
            <p>
                COPYRIGHT © 2013-2014 MingShi Rights Reserved
            </p> 
        </div>
    </div>
    <script src="/js/common.js?v=0902"></script>

    <script src="/public/bootstrap/js/bootstrap.min.js"></script>
    <script>
        $(".nav li").mouseenter(function(){
            $(this).find(".second-nav").show();
        }).mouseleave(function(){
            $(this).find(".second-nav").hide();
        })
    </script>
    </body>
</html>
