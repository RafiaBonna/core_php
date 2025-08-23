<?php 

$db = new mysqli('localhost','root','','jquery_evidence');

if(isset($_POST['email'])){	
	$email = trim($_POST['email']);
	
	$email_address = $db->query("select email from user where email='$email'");

	list($_email)=$email_address->fetch_row();
	
	if($email == $_email ){
		echo "<span style='color:green'>Email available</span>";
	}else{
		echo "<span style='color:red'>Email not available !</span>";
	}
}



?>