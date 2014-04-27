<div class="crumb-wrap">
    <div class="crumb">
        账户信息
    </div>
</div>

<div class="center-box">
    <div class="center-wrapper" style="margin-top:30px;">
        <table class="table" style="width:50%;">
            <tr>
                <td>用户名:</td>
                <td><?=$info['login_name']?></td>
            </tr>
            <tr>
                <td>类型:</td>
                <td><?=Model_Advertiser::$TYPES[$info['type']]?></td>
            </tr>
            <tr>
                <td>用户ID:</td>
                <td><?=$info['id']?></td>
            </tr>
            <tr>
                <td>电子邮箱:</td>
                <td><?=$info['email']?></td>
            </tr>
            <tr>
                <td>公司名称:</td>
                <td><?=$info['real_name']?></td>
            </tr>
            <tr>
                <td>网站域名:</td>
                <td><?=$info['web_url']?></td>
            </tr>
            <tr>
                <td>联系人:</td>
                <td><?=$info['name']?></td>
            </tr>
            <tr>
                <td>电话:</td>
                <td><?=$info['phone']?></td>
            </tr>
            <tr>
                <td>通讯地址:</td>
                <td><?=$info['address']?></td>
            </tr>
            <tr>
                <td>QQ:</td>
                <td><?=$info['qq']?></td>
            </tr>
        </table>
    </div>
</div>

