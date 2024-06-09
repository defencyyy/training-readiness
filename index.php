<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Training Readiness Management System</title>
  <link rel="icon" type="image/png" href="assets\pictures\logo.png">
  <link rel="stylesheet" href="assets\css\style.css">
  <link rel="stylesheet" href="assets\css\mediaqueries.css">
</head>

<style>
  body {
    background-image: url(assets/pictures/picture1.png);
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
      <label class="logo"><img src="assets\pictures\logo.png" id="logoimg" /></label>
      <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="login.php">Login</a></li>
      </ul>
    </nav>

    <nav id="hamburger-nav">
      <label class="logo"><img src="assets\pictures\logo.png" id="logoimg" /></label>
      <div class="hamburger-menu">
        <div class="hamburger-icon" onclick="toggleMenu()">
          <span></span>
          <span></span>
          <span></span>
        </div>
        <div class="hamburger-links">
          <li><a href="index.php" onclick="toggleMenu()">Home</a></li>
          <li><a href="login.php" onclick="toggleMenu()">Login</a></li>
        </div>
      </div>
    </nav>
  </section>

  <!-- ===== Home ===== -->
  <section id="home">
    <div class="content-text">
      Unleash your Potential, <br>
      One Rep at a time!
    </div>
  </section>


</body>

</html>

<script>
  function toggleMenu() {
    const menu = document.querySelector(".hamburger-links");
    const icon = document.querySelector(".hamburger-icon");
    menu.classList.toggle("open");
    icon.classList.toggle("open");
  }
</script>