<ul class="sub-menu nav">
    <li><a href="#">账户信息</a></li>
    <li><a href="#">资质管理</a></li>
    <li class="active"><a href="#">品牌产品管理</a></li>
    <li><a href="#">财务信息</a></li>
</ul>

<div class="data-list brand">
    <table class="table">
        <tr>
            <th><input name="selectAll" type="checkbox" /></th>
            <th>主账户</th>
            <th>品牌产品列表</th>
            <th>产品URL</th>
            <th>品牌效果数据</th>
        </tr>
        <?php foreach(array(1) as $v):?>
        <tr>
            <td><input name="selectAll" type="checkbox" value="<?=$v?>"/></td>
            <td><?=$v?></td>
            <td><?=$v?></td>
            <td><?=$v?></td>
            <td><?=$v?></td>
        </tr>
        <?php endforeach;?>
    </table>
</div>