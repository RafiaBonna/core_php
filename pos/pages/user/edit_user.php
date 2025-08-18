<?php

  // যেহেতু placholder.php ফাইলটি edit_user.php ফাইলকে কল করছে,
  // এবং placholder.php ও config.php একই ফোল্ডারে আছে, তাই এই Pathটি সঠিক।
  require_once 'config.php';

  // ভেরিয়েবলগুলোকে ইনিশিয়ালাইজ করা হয়েছে যাতে 'Undefined variable' ত্রুটি না আসে।
  $fname = "";
  $lname = "";
  $email = "";
  $password = "";
  $id = "";
  $r = ""; // Variable to hold the success or error message

  // এই ব্লকটি ফর্ম সাবমিট করার পর রান করবে
  if(isset($_POST["btnUpdate"])){
	  
	  $id=$_POST["id"];	  
	  $fname=$_POST["fname"]; 
	  $lname=$_POST["uname"];
	  $email=$_POST["mail"];
	  $password=$_POST["role"];
	  
	  // SQL Injection প্রতিরোধের জন্য Prepared Statement ব্যবহার করা হয়েছে
      // ডাটাবেজের সঠিক কলামের নাম ব্যবহার করা হয়েছে: full_name, username, email, এবং role_id
	  $stmt = $conn->prepare("UPDATE users SET full_name=?, username=?, email=?, role_id=? WHERE id=?");
      $stmt->bind_param("sssii", $fname, $lname, $email, $password, $id);

	  if ($stmt->execute()) {
          $r = "Success Updated";
      } else {
          $r = "Error updating record: " . $conn->error;
      }
      $stmt->close();
  }
  
  // এই ব্লকটি যখন কোনো ব্যবহারকারীর তথ্য এডিট করতে চাওয়া হয় তখন রান করবে
  // এবং URL এ ?id=123 (GET request) থাকলে এই কোড রান করবে।
  else if(isset($_GET["id"])){
	  $id=$_GET["id"];
	  
	  // এখানেও Prepared Statement ব্যবহার করা হয়েছে এবং ডাটাবেজের সঠিক কলাম সিলেক্ট করা হয়েছে
	  $stmt = $conn->prepare("SELECT full_name, username, email, role_id FROM users WHERE id=?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $use_tbl = $stmt->get_result();
      
      if($use_tbl->num_rows > 0) {
        list($fname, $lname, $email, $password) = $use_tbl->fetch_row();
      } else {
        $r = "No user found with that ID.";
      }

      $stmt->close();
  }
?>
  
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Update User</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Blank Page</li>
            </ol>
          </div>
        </div>
      </div></section>

    <section class="content">

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Title</h3>

          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
        <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Quick Example</h3>
              </div>
              </div>
  <div class="ftitle text-center"> 
			<h4><?php echo isset($r) ? $r : "Users update Form" ?></h4>
		</div>
              <form action="?page=3&id=<?php echo $id; ?>" method="post">
                  <div class="card-body">

              <div class="form-group">
                <input type="hidden" class="form-control"  name="id" value="<?php echo $id ?>">
              </div>
              <div class="form-group">
                <label for="y">Full Name</label>
                <input type="text" class="form-control" id="y" name="fname" value="<?php echo $fname ?>">
              </div>
              <div class="form-group">
                <label for="y">User Name</label>
                <input type="text" class="form-control" id="y" name="uname" value="<?php echo $lname ?>">
              </div>
               <div class="form-group">
                <label for="p">Email</label>
                <input type="text" class="form-control" id="p" name="mail" value="<?php echo $email ?>">
              </div>
               <div class="form-group">
                <label for="r">Role Name</label>
                <input type="text" class="form-control" id="r" name="role" value="<?php echo $password ?>">
              </div>
              
            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-primary" name="btnUpdate">Submit</button>
            </div>
          </form>
        </div>
      </div>

      </div>
            </div>
        </div>
      
        </div>
      </section>
    </div>