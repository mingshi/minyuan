<ul class="sub-menu nav">
    <li><a href="#">账户信息</a></li>
    <li><a href="#">资质管理</a></li>
    <li><a href="#">品牌产品管理</a></li>
    <li class="active"><a href="#">财务信息</a></li>
</ul>

<div class="data-list finance">
    <dl>
        <dt>企业名称：<dt>
        <dd><?='8888'?></dd>
    </dl>
    <div>如需要修改财务对象、企业名称，请邮件至chennaiyin@snda.com</div>
    <dl>
        <dt>汇款信息：<dt>
        <dd><a href="#">修改</a></dd>
    </dl>
    <dl>
        <dt>汇款银行：<dt>
        <dd><?='8888'?></dd>
    </dl>
    <dl>
        <dt>银行账号：<dt>
        <dd><?='8888'?></dd>
    </dl>
    <dl>
        <dt>开户地：<dt>
        <dd><?='8888'?></dd>
    </dl>
    <dl>
        <dt>联系人：<dt>
        <dd><?='8888'?></dd>
    </dl>
    <dl>
        <dt>支付宝账号：<dt>
        <dd><?='8888'?></dd>
    </dl>
</div>

<div class="edit-form">
    <h3>银行汇款信息</h3>
    <form method="post" action="?">
        <dl>
            <dt>收款银行：<dt>
            <dd><input name="bankname" type="text" class="txt" placeholder="请输入银行名称"/> - <input name="bankbranch" type="text" class="txt" placeholder="请输入详细的分行或者支行名称"/></dd>
        </dl>
        <dl>
            <dt><dt>
            <dd>请填写收款银行的完整名称，如：招商银行-上海分行东方支行</dd>
        </dl>
        <dl>
            <dt>开户地<dt>
            <dd><?=form_dropdown(
                    'status',
                    array('' => '省'),
                    '',
                    'style="width:100px"'
                )?>
                <?=form_dropdown(
                    'status',
                    array('' => '市'),
                    '',
                    'style="width:100px"'
                )?>
            </dd>
        </dl>
        <dl>
            <dt>银行账号：<dt>
            <dd><input name="bankname" type="text" class="txt"/></dd>
        </dl>
    </form>
</div>