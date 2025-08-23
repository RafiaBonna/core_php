<?php 
$db = new mysqli('localhost','root','','jquery_evidence');
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/style.css">
	
	<title>CRUD with jQuery</title>
</head>
<body>
  
<span class="result"></span>
<form>
	<div> ID: <br />
	<input type="text" name="id" id="id" value="" placeholder="id">
	<div> Full Name: <br />
		<input type="text" name="fullname" id="fullname" placeholder="Enter full name">
	</div>
	<div> Email: <br />
		<input type="text" name="email" id="email" placeholder="Enter email">
	</div>
	<div> Password: <br />
		<input type="text" name="pass" id="pass" placeholder="Enter password">
	</div>
	<br />
	<div>
		<input type="button" id="save" value="Save">
		<input type="button" id="update" value="Update">
		<input type="button" id="delete" value="Delete">
		<input type="button" id="reset" value="Reset">
	</div>
</form>

<table style="border-collapse:collapse" border="1" cellspacing="5"> 
	<thead> 
		<tr> 
			<th>Id</th>
			<th>Full Name</th>
			<th>Email</th>
			<th>Password</th>
		</tr>
	</thead>
	<tbody id="data"> 
		<?php 
		$data = $db->query("SELECT * FROM user");
		while($row = $data->fetch_assoc()){
			echo "<tr>
					<td>{$row['id']}</td>
					<td>{$row['fullname']}</td>
					<td>{$row['email']}</td>
					<td>{$row['pass']}</td>
				</tr>";	
		}
		?>
	</tbody>
</table>

<script src="js/jquery-3.3.1.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
	
<script type="text/javascript">
$(function(){

	// Save data
	$("#save").click(function(){
		var fullname = $("#fullname").val();
		var email = $("#email").val();
		var pass = $("#pass").val();
		$.ajax({
			url: "ajaxdata/crud_data.php",
			type: "post",
			data: {savefullname: fullname, saveemail: email, pass: pass},
			success: function(data){
				$(".result").html(data);
			}					
		});	
	});

	// Update data
	$("#update").click(function(){
		var upid = $("#id").val();
		var fullname = $("#fullname").val();
		var email = $("#email").val();
		var pass = $("#pass").val();
		$.ajax({
			url: "ajaxdata/crud_data.php",
			type: "post",
			data: {upid: upid, fullname: fullname, email: email, pass: pass},
			success: function(data){
				$(".result").html(data);
			}					
		});	
	});
	
	// Delete data
	$("#delete").click(function(){
		var id = $("#id").val();
		$.ajax({
			url: "ajaxdata/crud_data.php",
			type: "post",
			data: {id: id},
			success: function(data){
				$(".result").html(data);
			}					
		});	
	});
	
	// Select row data (click row)
	$("#data tr").on("click",function(){	
		var id = $(this).find("td:eq(0)").text();
		var fullname = $(this).find("td:eq(1)").text();
		var email = $(this).find("td:eq(2)").text();
		var pass = $(this).find("td:eq(3)").text();
		$("#id").val(id);
		$("#fullname").val(fullname);
		$("#email").val(email);
		$("#pass").val(pass);
	});
		
	// Reset form
	$("#reset").click(function(){
		$("#id").val("");
		$("#fullname").val("");
		$("#email").val("");
		$("#pass").val("");
		$(".result").html("<span style='color:red'>Form reset</span>");
	});	
});
</script>
</body>
</html>
