<!-- edit and delete form. delete option only when user role=admin -->
 
<form action="edit.php" method="post">
    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
    <input type="text" name="title" value="<?php echo $row['title']; ?>">
    <input type="text" name="content" value="<?php echo $row['content']; ?>">
    <input type="submit" name="update" value="Update">
    <?php if ($_SESSION['role'] == 'admin'): ?>
        <input type="submit" name="delete" value="Delete">
    <?php endif; ?>
</form>