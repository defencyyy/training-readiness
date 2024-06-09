<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Training Readiness Management System</title>
  <link rel="icon" type="image/png" href="assets\pictures\logo.png">
  <link rel="stylesheet" href="assets\css\style.css">
  <link rel="stylesheet" href="assets\css\mediaqueries.css">
   <?php
        require 'authentication.php'; // admin authentication check 

        // auth check
        if(isset($_SESSION['admin_id'])){
            $user_id = $_SESSION['admin_id'];
            $user_name = $_SESSION['admin_name'];
            $security_key = $_SESSION['security_key'];
            if ($user_id != NULL && $security_key != NULL) {
                header('Location: task-info.php');
            }
        }

        if(isset($_POST['login_btn'])){
            $info = $obj_admin->admin_login_check($_POST);
            if ($info) {
                $_SESSION['login_error'] = $info; // Store the error message in session
            }
        }
   ?>

</head>

<style>
  body {
    background-image: url(assets/pictures/bgpic.jpg);
    background-size: cover;
    background-position: center;
    background-size: auto;
    background-repeat: no-repeat;
  }
</style>

<body>
  <!-- ===== Navigation ===== -->
  <section id="header">
    <nav class="desktop-nav">
      <ul class="nav-links-admin">
        <li <?php if (basename($_SERVER['PHP_SELF']) == 'index.php') echo 'class="active"'; ?>><a href="index.php">Home</a></li>
        <li <?php if (basename($_SERVER['PHP_SELF']) == 'login.php') echo 'class="active"'; ?>><a href="login.php">Login</a></li>
      </ul>
      <label class="logo-nav"><img src="assets\pictures\logo.png" id="logoimg" /></label>
    </nav>
  </section>

  <!-- ===== Main ===== -->
  <section id="login-body">
    <div class="login-content">
      <div class="content-left">
        <img src="assets\pictures\loginpic.png" id="loginpic" />
      </div>
      <div class="content-right">
        <img src="assets\pictures\formbg.png" id="formpic" />
        <div class="content-form">
          <p class="title-login">Log in </p>
          <form class="login-form" action="" method="POST">
            <label class="form-title">Username</label>
            <div class="form-group">
			    <input type="text" class="input-form" placeholder="Username" name="username" required/>
			</div>
            <label class="form-title">Password</label>
            <div class="form-group" ng-class="{'has-error': loginForm.password.$invalid && loginForm.password.$dirty, 'has-success': loginForm.password.$valid}">
			    <input type="password" class="input-form" placeholder="Password" name="admin_password" required/>
			</div>
            <button type="submit" name="login_btn" class="loginbtn">Login</button>
          </form>
        </div>
      </div>
    </div>
  </section>
  <script>
    // Check if there's an error message stored in the session
    <?php if (isset($_SESSION['login_error'])) { ?>
      alert("<?php echo $_SESSION['login_error']; ?>");
      <?php unset($_SESSION['login_error']); // Clear the error message ?>
    <?php } ?>
  </script>
</body>

</html>