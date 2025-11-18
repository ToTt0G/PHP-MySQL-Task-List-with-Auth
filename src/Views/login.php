<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/login-register/styles.css?v=<?php echo time(); ?>">
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

    <script src="assets/login-register/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>