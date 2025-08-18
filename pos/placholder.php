<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['page'])) {
    $page = $_GET['page'];

    switch ($page) {
        case "1": include __DIR__ . '/pages/user/add_user.php'; break;
        case "2": include __DIR__ . '/pages/user/manage_user.php'; break;
        case "3": include __DIR__ . '/pages/user/edit_user.php'; break;

        case "4": include __DIR__ . '/pages/category/add_category.php'; break;
        case "5": include __DIR__ . '/pages/category/manage_category.php'; break;
        case "6": include __DIR__ . '/pages/category/edit_category.php'; break;

        case "7": include __DIR__ . '/pages/product/add_product.php'; break;
        case "8": include __DIR__ . '/pages/product/manage_product.php'; break;
        case "9": include __DIR__ . '/pages/product/edit_product.php'; break;

        default: echo "<h4 class='text-center mt-5'>Page not found!</h4>"; break;
    }

} else {
    echo "<h4 class='text-center mt-5'>Welcome to my New Project</h4>";
}
?>