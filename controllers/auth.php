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

    // Set session - store user data
    $_SESSION['user'] = [
        'id' => $foundUser['id'],
        'email' => $foundUser['email'],
        'name' => $foundUser['name']
    ];
    
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

    // Create new user with unique ID
    $newUser = [
        'id' => uniqid('user_', true), // Unique user ID
        'email' => $email,
        'password' => $password, // In production, use password_hash()!
        'name' => $name,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $users[] = $newUser;

    // Ensure data directory exists
    $dataDir = __DIR__ . '/../data';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0777, true);
    }

    // Save users
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));

    // DO NOT log user in automatically - redirect to login instead
    $_SESSION['success'] = 'Account created successfully! Please log in.';
    
    header('Location: /login');
    exit;
}

function handleLogout() {
    session_destroy();
    header('Location: /');
    exit;
}