<style>
.form-signin {
max-width: 300px;
padding: 19px 29px 29px;
margin: 60px auto 20px;
background-color: #fff;
border: 1px solid #e5e5e5;
-webkit-border-radius: 5px;
-moz-border-radius: 5px;
border-radius: 5px;
-webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
-moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
box-shadow: 0 1px 2px rgba(0,0,0,.05);
}
.form-signin .form-signin-heading,
.form-signin .checkbox {
margin-bottom: 10px;
}
.form-signin input[type="text"],
.form-signin input[type="password"] {
font-size: 16px;
height: auto;
margin-bottom: 15px;
padding: 7px 9px;
}
</style>
<form class="form-signin ajax" method="post" action="/login" ajax-msg="登录中...">
    <h2 class="form-signin-heading">登录系统</h2>
    <input type="text" placeholder="用户名" name="username" class="input-block-level">
    <input type="password" placeholder="密码" name="password" class="input-block-level">
    <label>
        <input type="text" placeholder="验证码" name="captcha" style="width:70px;margin-bottom:0" autocomplete="off"/>
        <img src="/login/captcha?r=<?=rand()?>" style="cursor:pointer" onclick="this.src=this.src.replace(/r=.+$/, 'r=' + Math.random())" title="看不清？点击换一张"/>
    </label>
    <?=form_hash('login')?>
    <label class="checkbox">
      <input type="checkbox" name="remember" value="1"> 记住我
    </label>
    <button type="submit" class="btn btn-large btn-primary">登录</button>
</form>
<script>$(function(){$('.form-signin :input:first')[0].focus()});</script>
