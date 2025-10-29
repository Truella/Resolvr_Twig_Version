<?php

function handleLogin($twig) {
    error_log('=== LOGIN ATTEMPT START ===');
    error_log('Session ID: ' . session_id());
    error_log('Session data before: ' . print_r($_SESSION, true));
    error_log('POST data: ' . print_r($_POST, true));

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    error_log('Extracted - Email: [' . $email . '], Password length: ' . strlen($password));

    if (empty($email) || empty($password)) {
        error_log('FAILED: Empty email or password');
        $_SESSION['error'] = 'All fields are required';
        header('Location: /login');
        exit;
    }

    // Get registered users from JSON file
    $usersFile = __DIR__ . '/../data/users.json';
    error_log('Users file path: ' . $usersFile);
    error_log('Users file exists: ' . (file_exists($usersFile) ? 'YES' : 'NO'));
    
    $users = [];
    
    if (file_exists($usersFile)) {
        $content = file_get_contents($usersFile);
        error_log('Users file raw content: ' . $content);
        $users = json_decode($content, true) ?? [];
        error_log('Total users loaded: ' . count($users));
    } else {
        error_log('ERROR: Users file does NOT exist at path: ' . $usersFile);
    }

    // Find matching user
    $foundUser = null;
    foreach ($users as $index => $user) {
        error_log('Checking user #' . $index . ' - Email: [' . $user['email'] . ']');
        error_log('Email match: ' . ($user['email'] === $email ? 'YES' : 'NO'));
        error_log('Password match: ' . ($user['password'] === $password ? 'YES' : 'NO'));
        
        if ($user['email'] === $email && $user['password'] === $password) {
            $foundUser = $user;
            error_log('✓ USER MATCH FOUND!');
            break;
        }
    }

    if (!$foundUser) {
        error_log('FAILED: No matching user found');
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

    error_log('✓ Session data after setting user: ' . print_r($_SESSION, true));
    error_log('✓ Redirecting to /dashboard');

    // Force session write before redirect
    session_write_close();

    header('Location: /dashboard');
    exit;
}

function handleSignup($twig) {
    error_log('=== SIGNUP ATTEMPT START ===');
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $name = $_POST['name'] ?? '';

    // Validation
    if (empty($email) || empty($password) || empty($confirmPassword) || empty($name)) {
        error_log('SIGNUP FAILED: Empty fields');
        $_SESSION['error'] = 'All fields are required';
        header('Location: /signup');
        exit;
    }

    if ($password !== $confirmPassword) {
        error_log('SIGNUP FAILED: Passwords do not match');
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
            error_log('SIGNUP FAILED: Email already exists - ' . $email);
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
        error_log('Created data directory: ' . $dataDir);
    }

    // Save users
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
    error_log('✓ User created successfully: ' . $email);
    error_log('✓ Saved to: ' . $usersFile);

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