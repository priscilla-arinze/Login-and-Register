<?php
	// report & display any PHP or syntax errors on page, if any
	error_reporting(-1);
	ini_set('display_errors', 1);

	if (!isset($_SESSION))
	{
		session_start();
	}

	// initializing variables
	$username = "";
	$email = "";
	$password = "";

	$errors = array(); // will append the below errors to this array variable if applicable
	// CONNECT TO DATABASE; update w/ your db info, username & password
	$db = new PDO('mysql:host=localhost; dbname=registration', 'root', '');

	/**** MYSQLCONNECT VERSION:
	 * 		$conn = mysql_connect('localhost', 'root', '');
	 * 		$db = mysql_select_db(registration, $conn);
	 * ****/




	
	// ****************************REGISTER****************************
	//once registration form is submitted
	if (isset($_POST['register']))
	{

		// receive all input values from the registration form after submission
		$username = $_POST['username'];
		$email = $_POST['email'];
		$password_1 = $_POST['password_1'];
		$password_2 = $_POST['password_2'];


		// if any of the fields are empty: add errors to $errors array
		if (empty($username)) { array_push($errors, "Username is required"); }
		if (empty($email)) { array_push($errors, "Email is required"); }
		if (empty($password_1)) { array_push($errors, "Password is required"); }
		if ($password_1 != $password_2) { array_push($errors, "The two passwords do not match"); }


		// PREPARED SQL STATEMENTS TO AVOID SQL INJECTIONS; named parameters
		// check if user is already in the database
		$user_check_query = "SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1"; // declare the query as a string
		$result = $db->prepare($user_check_query); // prepares string query above for the following execute()
		$result->execute(['username' => $username, 'email' => $email]); // executes with set named parameters
		$user = $result->fetch(PDO::FETCH_ASSOC); // obtains query results as a PDO associative array

		/**** MYSQLCONNECT VERSION:
		 * $username_safe = mysql_real_escape_string($username);
		 * $email_safe = mysql_real_escape_string($email);
		 * $user_check_query = "SELECT * FROM users WHERE username = '$username_safe' OR email = '$email_safe' LIMIT 1";
		 * $result = mysql_query($user_check_query);
		 * $user = mysql_fetch_array($result, MYSQL_ASSOC);
		 * ****/


		// if user/row does exist in the database, then check if username or email already exist in database
		if ($user)
		{
			if ($user['username'] === $username) { array_push($errors, "Username already exists"); }
			if ($user['email'] === $email) { array_push($errors, "Email already exists"); }
		}


		// only none of the above errors:
		if (count($errors) == 0) // if both fields are filled
		
		{
			// encrypt/hash the password before saving in the database
			$password = password_hash($password_1, PASSWORD_DEFAULT);
			$insert_query = "INSERT INTO users (username, email, password) VALUES(:username, :email, :password)"; // declare the query as a string
			$stmt = $db->prepare($insert_query); // prepares string query above for the following execute()
			$stmt->execute(['username' => $username, 'email' => $email, 'password' => $password]); // executes with set named parameters
			/**** MYSQLCONNECT VERSION:
			 * $password = password_hash($password_1, PASSWORD_DEFAULT);
			 * $password_safe = mysql_real_escape_string($password);
			 * $insert_query = "INSERT INTO users (username, email, password) VALUES('$username_safe', '$email_safe', '$password_safe')";
			 * $stmt = mysql_query($user_check_query);
			 * ****/

			// logs in newly-registered user
			$_SESSION['username'] = $username;
			$_SESSION['success'] = "You are now logged in"; // FLASH MESSAGE
			header('Location: index.php'); // Raw HTTP header; redirects to index.php upon successful login
			
		}
	}





	// ****************************LOGIN****************************
	// once login form is submitted
	if (isset($_POST['login']))
	{

		// recieve input values from login form submission
		$username = $_POST['username'];
		$password = $_POST['password'];


		// if any of the fields are empty: add errors to $errors array
		if (empty($username)) { array_push($errors, "Username is required"); }
		if (empty($password)) { array_push($errors, "Password is required"); }


		// if both fields are filled in
		else if (count($errors) == 0)
		{

			// PREPARED SQL STATEMENTS TO AVOID SQL INJECTIONS; named parameters
			// check if user is in the database
			$query = "SELECT * FROM users WHERE username = :username LIMIT 0, 1"; // declare the query as a string
			$stmt = $db->prepare($query); // prepares string query above for the following execute()
			$stmt->execute(['username' => $username]); // executes with set named parameters
			$row_count = $stmt->rowCount();

			/**** MYSQLCONNECT VERSION:
			 * $query = "SELECT * FROM users WHERE username = '$username_safe' LIMIT 0, 1";
			 * $stmt = mysql_query($user_check_query);
			 * $row_count = mysql_num_rows($stmt);
			 * ****/


			// if username exists
			if ($row_count > 0)
			{
				$row = $stmt->fetch(PDO::FETCH_ASSOC); // obtains query results (the one row) as a PDO associative array
				// MYSQLCONNECT VERSION:  $row = mysql_fetch_array($stmt, MYSQL_ASSOC);
				$password_user_hash = $row['password'];
				$password_user_verify = password_verify($password, $password_user_hash); // returns a boolean if plain text password matches the hash stored in database for given username
				
				// if password hash matches, then successful login & redirect to index.php
				if ($password_user_verify)
				{
					$_SESSION['username'] = $username;
					$_SESSION['success'] = "You are now logged in";
					header('Location: index.php'); // Raw HTTP header; redirects to index.php upon successful login
					
				}


				// if password is incorrect
				else
				{
					array_push($errors, "Wrong username/password combination");
				}
			}


			// username doesn't exist
			else
			{
				array_push($errors, "Username is not registered, select Sign Up to register");
			}
		}
	}
?>
