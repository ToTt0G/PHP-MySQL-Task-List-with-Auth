<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/tasks/index.css?v=<?php echo time(); ?>">
    <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
    <title>To-Do List - Js, PHP, MySQL</title>
</head>
<body>
    <div class="card">
        <div class="topbar">
            <div class="brand">Welcome, <?php echo $_SESSION['name']; ?>!</div>
            <button class="button ghost" id="logout-button">Logout</button>
        </div>

        <h1>To-Do List</h1>
        <p class="lead">Hello <?php echo $_SESSION['name']; ?>! Welcome to your fast and private PHP based task list.</p>
        <form id="task-submit-form">
            <input type="text" id="task-input" name="task" class="ghost" placeholder="Add a new task">
            <button type="submit" class="button ghost">Add Task</button>
        </form>
        <ul id="task-list">
            <ul><li class="skeleton"></li></ul>
        </ul>
        <button id="clear-tasks-button" class="button ghost">Clear All Tasks</button>
    </div>
    
    

    <script src="assets/tasks/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>