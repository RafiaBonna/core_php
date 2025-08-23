<?php 
$db = new mysqli('localhost','root','','jquery_evidence');

// ===== INSERT (SAVE) =====
if(isset($_POST['savefullname']) && isset($_POST['saveemail']) && isset($_POST['pass'])){
    $fullname = $_POST['savefullname'];
    $email = $_POST['saveemail'];
    $pass = $_POST['pass'];

    $query = "INSERT INTO user(fullname,email,pass) VALUES('$fullname','$email','$pass')";
    if($db->query($query)){
        echo "<span style='color:green'>Data Saved Successfully</span>";
    } else {
        echo "<span style='color:red'>Error: ".$db->error."</span>";
    }
}

// ===== UPDATE =====
if(isset($_POST['upid'])){
    $id = $_POST['upid'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $pass = $_POST['pass'];

    $query = "UPDATE user SET fullname='$fullname', email='$email', pass='$pass' WHERE id='$id'";
    if($db->query($query)){
        echo "<span style='color:green'>Data Updated Successfully</span>";
    } else {
        echo "<span style='color:red'>Error: ".$db->error."</span>";
    }
}

// ===== DELETE =====
if(isset($_POST['id'])){
    $id = $_POST['id'];
    $query = "DELETE FROM user WHERE id='$id'";
    if($db->query($query)){
        echo "<span style='color:green'>Data Deleted Successfully</span>";
    } else {
        echo "<span style='color:red'>Error: ".$db->error."</span>";
    }
}
?>
