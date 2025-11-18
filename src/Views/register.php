<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/login-register/styles.css?v=<?php echo time(); ?>">
    <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
    <title>Register - To-Do List</title>
</head>
<body>
    <div class="card">
        <div class="topbar">
            <div class="brand">Tasks</div>
            <div class="nav">
                <a href="/login">Sign In Instead?</a>
            </div>
        </div>

        <h1>Sign Up</h1>
        <p class="lead">Hello, please register to continue.</p>
        <form id="register-form">
            <input type="text" id="email-input" name="email" class="ghost" placeholder="Email">
            <input type="text" id="name-input" name="name" class="ghost" placeholder="Name">
            <input type="password" id="password-input" name="password" class="ghost" placeholder="Password">
            <button type="submit" class="button ghost">Sign Up</button>
        </form>
    </div>

    <script src="assets/login-register/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>