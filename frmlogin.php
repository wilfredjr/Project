<?php
	require_once("support/config.php");

  if(isLoggedIn()){
    redirect("index.php");
    die();
  }

	makeHead("Login");
?>
<body background="dist/img/bg2.png" style="background-size:contain;">
    <div class="login-box" >
      <div class="login-box-body" style="border-radius: 10px;border: green 2px solid;margin-top: 190px">
        
        <div class="login-logo"><!-- 
        <img src="dist/img/s6logo.png" class='img-responsive center-block' > -->
        </div><!-- /.login-logo -->
        <?php
          Alert();
        ?>
        <h3><p class="login-box-msg text-green">SGTSI Project Monitoring System</p></h3> 
    <!--  <h4 class="form-signin-heading">Login to your Account</h4>-->
        <form action="logingin.php" method="post">
          <div class="form-group has-feedback">
            <i class="glyphicon glyphicon-user form-control-feedback"></i>
            <input type="text" class="form-control" placeholder="Username" name='username'>
            <!--<span class="glyphicon glyphicon-user form-control-feedback"></span>-->
          </div>
          <div class="form-group has-feedback">
            <input type="password" class="form-control" placeholder="Password" name='password'>
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
          </div>
          <div class="row">
            <div class="col-xs-12 col-xs-offset-0">
              <!--<button type="submit" class="btn btn-primary btn-block btn-flat">Sign In</button>-->
              <button type="submit" class="btn btn-lg btn-block bg-green">Login</button>
              <br/>
              <center><a class='text-yellow' href='forgot_password.php' >Forgot Password</a>
            </div><!-- /.col -->
          </div>
        </form>
      </div><!-- /.login-box-body -->
    </div><!-- /.login-box -->

<?php
  Modal();
	makeFoot();
?>