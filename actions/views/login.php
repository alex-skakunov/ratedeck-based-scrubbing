<style type="text/css">
.form-signin {
  width: 100%;
  max-width: 330px;
  padding: 15px;
  margin: 0 auto;
}
.form-signin .checkbox {
  font-weight: 400;
}
.form-signin .form-control {
  position: relative;
  box-sizing: border-box;
  height: auto;
  padding: 10px;
  font-size: 16px;
}
.form-signin .form-control:focus {
  z-index: 2;
}
.form-signin input[type="text"] {
  margin-bottom: -1px;
  border-bottom-right-radius: 0;
  border-bottom-left-radius: 0;
}
.form-signin input[type="password"] {
  margin-bottom: 10px;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}
</style>
<? if (!empty($errorMessage)): ?>
  <div class="alert alert-warning" role="alert">
    <?=$errorMessage?>
  </div>
<? endif; ?>

<form class="form-signin" action="index.php?page=login" method="POST">
  <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
  
  <label for="inputEmail" class="sr-only">Email address</label>
  <input type="email" id="inputEmail" class="form-control" placeholder="Email address" name="email" value="<?=!empty($_POST['email']) ? $_POST['email'] : ''?>" required autofocus>

  <label for="inputPassword" class="sr-only">Password</label>
  <input type="password" id="inputPassword" class="form-control" placeholder="Password" name="password" value="<?=!empty($_POST['password']) ? $_POST['password'] : ''?>" required>

  <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
</form>