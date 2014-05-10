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

    <div class="header-wrap">
        <div class="header">
            <div class="top clearfix">
                <div class="fl">
                </div>
                
                <div class="top-info fr">
                    <div class="fl">
                    <?php if (@$me['id']) :?>
                        <ul class="top-nav clearfix">
                            <li><a href="#">欢迎您，<?=$me['login_name']?></a></li>
                            <!--
                            <li><a href="#">设置</a></li>
                            <li><a href="#" class="green">帮助</a></li>
                            -->
                            <li><a href="/logout" class="green">退出</a></li>
                        </ul>
                    <?php endif; ?>
                    </div>

                    <?php if (@$me['id']) :?>
                    <!--
                    <div class="fr">
                        <img src="images/avstar.png" alt="">
                    </div>
                    -->
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (@$me['id']) :?> 
            <ul class="nav clearfix">
                <li <?php if (@$nav == "index") { ?>class="selected" <?php } ?>><a href="/">首页</a></li>
                <li <?php if (@$nav == "manage") { ?>class="selected" <?php } ?>><a href="#">广告管理</a>
                    <ul class="second-nav">
                        <li><a href="/campaign">计划管理</a></li>
                        <li><a href="/box">Box管理</a></li>
                    </ul>
                </li>
                <li <?php if (@$nav == "report") { ?>class="selected" <?php } ?>><a href="#">运营报告</a>
                    <ul class="second-nav">
                        <li><a href="/allReport">整体报告</a></li>
                        <li><a href="/sizeReport">尺寸报告</a></li>
                    </ul>
                </li>
                <li <?php if (@$nav == "account") { ?>class="selected" <?php } ?>>
                    <a href="#">账户管理</a>
                    <ul class="second-nav">
                        <li><a href="/my">账户信息</a></li>
                        <!--
                        <li><a href="#">资质信息</a></li>
                        <li><a href="#">品牌产品管理</a></li>
                        <li><a href="#">财务管理</a></li>
                        -->
                    </ul>
                </li>
            </ul>
            <?php endif; ?>
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
                COPYRIGHT © 2013-2014 BOX.HZENG.NET Adlm Media Rights Reserved
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
