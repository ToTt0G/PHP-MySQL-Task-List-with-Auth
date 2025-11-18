<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo \App\Helpers\Csrf::generate(); ?>">
    <title>Admin - Dashboard</title>
    <link rel="stylesheet" href="<?php echo \App\Helpers\Assets::versioned('assets/styles.css'); ?>">
    <link rel="stylesheet" href="<?php echo \App\Helpers\Assets::versioned('assets/dashboard/styles.css'); ?>">
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
    <script src="<?php echo \App\Helpers\Assets::versioned('assets/dashboard/script.js'); ?>"></script>
</body>
</html>
