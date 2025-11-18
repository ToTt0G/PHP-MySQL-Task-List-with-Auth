<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Dashboard</title>
    <link rel="stylesheet" href="/assets/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/dashboard/styles.css?v=<?php echo time(); ?>">
    <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
</head>
<body>
    <div class="card">
        <div class="topbar">
            <div class="brand">Admin Dashboard</div>
        </div>

        <h1>Users</h1>

        <div id="user-cards-container" class="users-grid">
            <!-- Cards will be rendered here -->
        </div>
    </div>
    <script src="/assets/dashboard/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>
