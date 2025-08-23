<?php session_start();

$db = new mysqli('localhost','root','','jquery_evidence');

if(!isset($_SESSION['s_email'])){
	header("location:login.php");
}


if(isset($_GET['logout'])){
	unset($_SESSION['s_email']);
	session_destroy($_SESSION['s_email']);
	header("location:login.php");
}
?>

<!doctype html>
<html lang="en">
<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="css/bootstrap.min.css">

	<title>Hello, world!</title>
</head>
<body>
  
  
  <h2 class="display-2">Login successfull</h2>
  
  
  
  <a href="?logout=1" class="">Logout</a>
  
    
	
	
	
	
	
	
	
	
	
	
	
	

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>