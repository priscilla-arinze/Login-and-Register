<?php 
	// display errors
	error_reporting(-1);
	ini_set('display_errors',1);
	
	
	if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 

	// initializing variables
	$username = "";
	$email    = "";
	$errors = array();
	$password = "";
	
	// CONNECT TO DATABASE; update w/ your db info, username & password
    $db = new PDO('mysql:host=localhost; dbname=registration', 'root', 'mysql');
	
	
	// ****************************REGISTER****************************
	if (isset($_POST['register'])) 
	{
	  // receive all input values from the form
	  $username = $_POST['username'];
	  $email = $_POST['email'];
	  $password_1 = $_POST['password_1'];
	  $password_2 = $_POST['password_2'];

	  // if any of the fields are empty:
	  // add errors to $errors array
	  if (empty($username)) 
	  { 
		array_push($errors, "Username is required"); 
	  }
	  if (empty($email)) 
	  {
		array_push($errors, "Email is required"); 
	  }
	  if (empty($password_1)) 
	  { 
		array_push($errors, "Password is required"); 
	  }
	  if ($password_1 != $password_2) 
	  {
		array_push($errors, "The two passwords do not match");
	  }

	  // check if user is already in the database
	  $user_check_query = "SELECT * FROM users WHERE username = :name OR email = :email LIMIT 1";
	  $result = $db->prepare($user_check_query);
	  $result->execute(['name' => $username, 'email' => $email]);
	  $user = $result->fetch(PDO::FETCH_ASSOC);
	  

	  if ($user)  // if user exists in database
	  {
		if ($user['username'] === $username) {
		  array_push($errors, "Username already exists");
		}

		if ($user['email'] === $email) {
		  array_push($errors, "Email already exists");
		}
	  }
	
	  if (count($errors) == 0) //if both fields are filled
	  {
		$password = password_hash($password_1, PASSWORD_DEFAULT);//encrypt the password before saving in the database
		$query = "INSERT INTO users (username, email, password) VALUES(:name, :email, :password)";
		$stmt = $db->prepare($query);
		$stmt->execute(['name' => $username, 'email' => $email, 'password' => $password]);
		
		//logs in newly-registered user
		$_SESSION['username'] = $username;
		$_SESSION['success'] = "You are now logged in";
		header('location: index.php');
	  }
	}
	
	
	
	// ****************************LOGIN****************************
	if (isset($_POST['login'])) 
	{
		//recieve input values from form
		$username = $_POST['username'];
		$password = $_POST['password'];
		
		// if any of the fields are empty:
		// add errors to $errors array
		if (empty($username)) 
		{
			array_push($errors, "Username is required");
		}
		
		if (empty($password)) 
		{
			array_push($errors, "Password is required");
		}
		
		else if(count($errors) == 0) //both fields are filled in
		{
			$query = "SELECT * FROM users WHERE username = :name LIMIT 0,1";
			$stmt = $db->prepare($query);
			$stmt->execute(['name' => $username]);
			$row_count = $stmt->rowCount();
				
			if ($row_count > 0) //if username exists
			{
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				$password_h = $row['password'];
				$pass_verif = password_verify($password, $password_h);
				
				if($pass_verif)
				{
					$_SESSION['username'] = $username;
					$_SESSION['success'] = "You are now logged in";
					header('location: index.php');
				}
				else //if password is incorrect
				{
					array_push($errors, "Wrong username/password combination");
				}	
			}
			else //username doesn't exist
			{
				array_push($errors, "Username unregistered");
			}	
		}		
	}
?>