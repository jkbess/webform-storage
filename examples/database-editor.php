<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Web Form Database Editor</title>
<meta name="robots" content="nofollow,noindex" />

<?php include ( $_SERVER['DOCUMENT_ROOT'] . '/head-tags.php'); ?>
</head>

<body>   

<?php include ( $_SERVER['DOCUMENT_ROOT'] . '/header.php'); ?>

<main>
<div class="jumbotron jumbotron-fluid px-1 py-4 mb-5 bg-primary text-white">
    <div class="container">
        <h1>Web Form Database Editor</h1>
    </div>
</div>

<?php if(isset($_SESSION['user_name'])): ?>
<div class="container mb-4 p-2 text-right bg-light">
    <span class="mr-2">You are signed in as <em><?php echo $_SESSION['user_name']; ?></em>.</span>
    <a class="btn btn-primary" href="/user/logout">Sign out</a>
</div>
<?php endif; ?>

<div class="container">
    <p>Use this form to add fields to the database.</p>
    <p>You can't delete columns from the database, but you can simply not include them in your form. If you really need to delete a field you may want to just create a new database.</p>

    <h2>Current Fields</h2>
    <div id="info">Loading...</div>

    <h2>Add New Field</h2>
    <div>
        <span class="mr-4">
            <label for="new_column_name">Column Name</label>
            <input type="text" name="new_column_name" id="new_column_name" />
        </span>
        <span class="mr-4">
            <label for="new_is_numeric">Numeric?</label>
            <input type="checkbox" name="new_is_numeric" id="new_is_numeric" />
        </span>
        <button id="add-field" class="btn btn-primary">Add</button>
      </div>
</div>
</main>

<?php include ( $_SERVER['DOCUMENT_ROOT'] . '/footer.php'); ?>
</body>

<script src="database-editor.js"></script>
</html>