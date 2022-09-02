<?php 
	/************** UPON SUCCESSFUL LOGIN **************/ 

	if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 

	// if the current session username is not set for whatever reason once session starts
	if (!isset($_SESSION['username'])) 
	{
		$_SESSION['msg'] = "You must log in first";
		header('Location: login.php'); // Raw HTTP header; redirects back to login page
	}

	// ends user session once user selects "logout"
	if (isset($_GET['logout'])) 
	{
		session_destroy();
		unset($_SESSION['username']); // resets session username variable back to original initialization on server.php
		header("Location: Login.php"); // Raw HTTP header; redirects back to login page
	}
?>


<!DOCTYPE html>
<html>
	<head>
		<title>Home</title>
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	
	<body>
		<div class="header">
			<h2>Home Page</h2>
		</div>
		<div class="content">

			<!-- notification message once successful -->
			<!-- if statement ternary operator for embedded php -->
			<?php if (isset($_SESSION['success'])) : ?>
			  <!-- for css -->
			  <div class="success">
				<h3>
				  <?php 
					echo $_SESSION['success']; 
					unset($_SESSION['success']);
				  ?>
				</h3>
			  </div>
			<?php endif; ?>

			<!-- logged in user information: "Welcome [insert username here] -->
			<?php  if (isset($_SESSION['username'])) : ?>
				<p>Welcome <strong><?php echo $_SESSION['username']; ?></strong></p>
				<p> <a href="index.php?logout='1'" style="color: red;">logout</a> </p>
			<?php endif; ?>
		</div>
		
</body>
</html>