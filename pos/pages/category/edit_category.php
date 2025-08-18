<?php
include __DIR__ . '/../../config.php';

$category_name = "";
$parent_id = "";
$id = "";
$message = "";

$categories = [];
$cat_sql = "SELECT id, category_name FROM categories WHERE parent_id IS NULL";
$cat_result = $conn->query($cat_sql);
if ($cat_result && $cat_result->num_rows > 0) {
    while ($row = $cat_result->fetch_assoc()) $categories[] = $row;
}

if (isset($_POST["btnUpdate"])) {
    $id = $_POST["id"];
    $category_name = trim($_POST["category_name"]);
    $parent_id = trim($_POST["parent_id"]);

    if ($category_name) {
        $stmt = $conn->prepare("UPDATE categories SET category_name=?, parent_id=? WHERE id=?");
        
        if(empty($parent_id)) {
            $null_parent_id = NULL; // Variable created for bind_param
            $stmt->bind_param("sii", $category_name, $null_parent_id, $id);
        } else {
            $stmt->bind_param("sii", $category_name, $parent_id, $id);
        }

        if ($stmt->execute()) {
            $message = "Category updated successfully!";
        } else {
            $message = "Error updating record: " . $conn->error;
        }
        $stmt->close();
    } else {
        $message = "Category name cannot be empty.";
    }
} else if (isset($_GET["id"])) {
    $id = intval($_GET["id"]);
    $stmt = $conn->prepare("SELECT category_name, parent_id FROM categories WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $category_name = $row['category_name'];
        $parent_id = $row['parent_id'];
    } else {
        $message = "No category found with that ID.";
    }
    $stmt->close();
}
?>

<div class="container my-5">
    <h3>Update Category</h3>
    <div class="ftitle text-center"> 
        <h4><?php echo $message ? $message : "Category Update Form" ?></h4>
    </div>
    <form action="?page=6&id=<?php echo $id; ?>" method="post">
        <div class="form-group">
            <input type="hidden" name="id" value="<?php echo $id ?>">
        </div>
        <div class="form-group">
            <label for="category_name">Category Name</label>
            <input type="text" class="form-control" id="category_name" name="category_name" value="<?php echo htmlspecialchars($category_name); ?>">
        </div>
        <div class="form-group">
            <label for="parent_id">Parent Category</label>
            <select name="parent_id" class="form-select mb-2">
                <option value="">Select Parent Category (Optional)</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php if($parent_id==$cat['id']) echo "selected"; ?>>
                    <?php echo htmlspecialchars($cat['category_name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" name="btnUpdate">Update Category</button>
    </form>
</div>