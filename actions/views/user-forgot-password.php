<? if (!empty($message)): ?>
  <div class="alert alert-info" role="alert">
    <?=$message?>
  </div>
  <? return; ?>
<? endif; ?>


<style type="text/css">
.form-forgot-password {
  width: 100%;
  max-width: 330px;
  padding: 15px;
  margin: 0 auto;
}
.form-forgot-password .checkbox {
  font-weight: 400;
}
.form-forgot-password .form-control {
  position: relative;
  box-sizing: border-box;
  height: auto;
  padding: 10px;
  font-size: 16px;
}
.form-forgot-password .form-control:focus {
  z-index: 2;
}
.form-forgot-password input[type="email"] {
  margin-bottom: 10px;
  border-bottom-right-radius: 0;
  border-bottom-left-radius: 0;
}
.form-forgot-password input[type="password"] {
  margin-bottom: 20px;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}
</style>

<form class="form-forgot-password" method="POST">
  <h1 class="h3 mb-3 font-weight-normal">Password recovery</h1>
  
  <label for="inputEmail" class="sr-only">Email address</label>
  <input type="email" id="inputEmail" class="form-control" placeholder="Email address" name="email" value="<?=!empty($_REQUEST['email']) ? $_REQUEST['email'] : ''?>" required autofocus>

  <button class="btn btn-lg btn-primary btn-block" type="submit">Send a recovery link</button>
  <br/><br/>
  
  <div style="text-align: left">
    <a href="index.php?page=login&email=<?=!empty($_REQUEST['email']) ? urlencode($_REQUEST['email']) : ''?>"><small>Back to login page</a></a>
  </div>
</form>