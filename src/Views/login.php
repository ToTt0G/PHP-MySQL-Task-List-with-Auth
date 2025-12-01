<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo \App\Helpers\Csrf::generate(); ?>">
    <link rel="stylesheet" href="<?php echo \App\Helpers\Assets::versioned('assets/styles.css'); ?>">
    <link rel="stylesheet" href="<?php echo \App\Helpers\Assets::versioned('assets/login-register/styles.css'); ?>">
    <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
    <title>Login - To-Do List</title>
</head>
<body>
    <div class="card">
        <div class="topbar">
            <div class="brand">Tasks</div>
            <div class="nav">
                <a href="/register">Sign Up Instead?</a>
            </div>
        </div>

        <h1>Login</h1>
        <p class="lead">Hello, please login to continue.</p>
        <form id="login-form">
            <input type="text" id="email-input" name="email" class="ghost" placeholder="Email">
            <input type="password" id="password-input" name="password" class="ghost" placeholder="Password">
            <!-- Add this checkbox -->
            <label style="display:flex;gap:6px;align-items:center;margin:8px 0;">
                <input type="checkbox" id="remember-me-input" name="remember_me">
                Remember me
            </label>
            <button type="submit" class="button ghost">Login</button>
        </form>
    </div>

    <script src="<?php echo \App\Helpers\Assets::versioned('assets/login-register/script.js'); ?>"></script>
</body>
</html>