<?php

function handleLogin($twig) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'All fields are required';
        header('Location: /login');
        exit;
    }

    // Get registered users from JSON file
    $usersFile = __DIR__ . '/../data/users.json';
    $users = [];
    
    if (file_exists($usersFile)) {
        $users = json_decode(file_get_contents($usersFile), true) ?? [];
    }

    // Find matching user
    $foundUser = null;
    foreach ($users as $user) {
        if ($user['email'] === $email && $user['password'] === $password) {
            $foundUser = $user;
            break;
        }
    }

    if (!$foundUser) {
        $_SESSION['error'] = 'Invalid email or password';
        header('Location: /login');
        exit;
    }

    // Set session (simulating localStorage)
    $_SESSION['ticketapp_session'] = json_encode([
        'email' => $foundUser['email'],
        'name' => $foundUser['name'],
        'token' => 'fake_login_token_' . time()
    ]);

    $_SESSION['user'] = $foundUser;
    
    header('Location: /dashboard');
    exit;
}

function handleSignup($twig) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $name = $_POST['name'] ?? '';

    // Validation
    if (empty($email) || empty($password) || empty($confirmPassword) || empty($name)) {
        $_SESSION['error'] = 'All fields are required';
        header('Location: /signup');
        exit;
    }

    if ($password !== $confirmPassword) {
        $_SESSION['error'] = 'Passwords do not match';
        header('Location: /signup');
        exit;
    }

    // Get existing users
    $usersFile = __DIR__ . '/../data/users.json';
    $users = [];
    
    if (file_exists($usersFile)) {
        $users = json_decode(file_get_contents($usersFile), true) ?? [];
    }

    // Check for duplicate email
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            $_SESSION['error'] = 'Email is already registered';
            header('Location: /signup');
            exit;
        }
    }

    // Create new user
    $newUser = [
        'email' => $email,
        'password' => $password, // In production, hash this!
        'name' => $name,
        'token' => 'fake_signup_token_' . time()
    ];

    $users[] = $newUser;

    // Ensure data directory exists
    if (!is_dir(__DIR__ . '/../data')) {
        mkdir(__DIR__ . '/../data', 0755, true);
    }

    // Save users
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));

    // Set session
    $_SESSION['ticketapp_session'] = json_encode($newUser);
    $_SESSION['user'] = $newUser;

    header('Location: /dashboard');
    exit;
}

function handleLogout() {
    session_destroy();
    header('Location: /');
    exit;
}