<?php 

$db = new mysqli('localhost','root','','jquery_evidence');

if(isset($_POST['disid'])){	
	$disid = trim($_POST['disid']);
	
	$address = $db->query("select id,name from districts where id='$disid'");

	while(list($_id,$_name)=$address->fetch_row()){
		echo "<option value='$_id'>$_name</option>";
	}
}



?>