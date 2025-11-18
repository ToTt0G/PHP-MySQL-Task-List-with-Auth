<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo \App\Helpers\Csrf::generate(); ?>">
    <link rel="stylesheet" href="<?php echo \App\Helpers\Assets::versioned('assets/styles.css'); ?>">
    <link rel="stylesheet" href="<?php echo \App\Helpers\Assets::versioned('assets/login-register/styles.css'); ?>">
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

    <script src="<?php echo \App\Helpers\Assets::versioned('assets/login-register/script.js'); ?>"></script>
</body>
</html>