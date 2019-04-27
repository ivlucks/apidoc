<style>
    body {
        background-color: #eee;
        padding-bottom: 40px;
        padding-top: 40px;
    }
    .form-signin {
        margin: 0 auto;
        max-width: 330px;
        padding: 15px;
        margin-bottom: 10px;
    }
    .form-signin .form-control {
        box-sizing: border-box;
        font-size: 16px;
        height: auto;
        padding: 10px;
        position: relative;
    }
    .form-signin .form-control:focus {
        z-index: 2;
    }
    .form-signin input[type="password"] {
        border-top-left-radius: 0;
        border-top-right-radius: 0;
        margin-bottom: 10px;
    }
</style>
<form id="login-form" class="form-signin" role="form" method="post">
<h2 class="form-signin-heading ">请登录</h2>
<input type="hidden" value="<?php echo Yii::$app->request->csrfToken; ?>" name="_csrf" >
<div class="form-group field-manager-realname has-success">
    <input type="password" id="loginform-password" class="form-control" name="LoginForm[password]" aria-required="true">
</div>
<button type="submit" class="btn btn-lg btn-primary btn-block">登录</button>
</form>