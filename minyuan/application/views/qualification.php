<ul class="sub-menu nav">
    <li><a href="#">账户信息</a></li>
    <li class="active"><a href="#">资质管理</a></li>
    <li><a href="#">品牌产品管理</a></li>
    <li><a href="#">财务信息</a></li>
</ul>

<div class="data-list account">
    <form method="post" action="?">
    <dl>
        <dt>请选择所在行业：<dt>
        <dd><?=form_dropdown(
            'status',
            array('' => '保健品'),
            '',
            'style="width:100px"'
        )?> <span><?='资质正常生效'?></span></dd>
        <dd class="fr">修改资质信息请<a href="#">联系客服</a></dd>
    </dl>

    <div class="license-wrapper">
        <div>请您上传营业执照：</div>
        <div class="graytips">请您先选择您所在行业，并根据系统提示上传您的资质证明文件。支持的图片格式：gif/jpg/jpeg/png，单个图片最大1M。</div>
        <div class="license-info">
            <dl>
                <dt>营业执照</dt>
                <dd>(资质正常生效)</dd>
            </dl><dl></dl>
            <dl>
                <dt>资质文件编号：</dt>
                <dd>1101080011277784</dd>
            </dl>
            <dl>
                <dt>资质文件编号：</dt>
                <dd>1101080011277784</dd>
            </dl>
            <dl>
                <dt>资质主体名称：</dt>
                <dd>北京XXX有限公司</dd>
            </dl>
            <dl>
                <dt>资质主体名称：</dt>
                <dd>北京XXX有限公司</dd>
            </dl>
            <dl>
                <dt>资质有限期至：</dt>
                <dd>2028-08-19</dd>
            </dl>
            <dl>
                <dt>资质有限期至：</dt>
                <dd>2028-08-19</dd>
            </dl>
        </div>
        <div class="record-info">
            <dl>
                <dt>网站名称：</dt>
                <dd><input name="name" type="text" class="txt"/></dd>
            </dl>
            <dl>
                <dt>网站名称：</dt>
                <dd><input name="domain" type="text" class="txt"/></dd>
            </dl>
            <dl>
                <dt>网站描述：</dt>
                <dd>
                    <textarea name="domain" type="text"  class="txt"/></textarea>
                </dd>
            </dl>
            <dl>
                <dt>备案信息：</dt>
                <dd>
                    点击打开
                </dd>
            </dl>
        </div>
    </div>
    </form>
</div>