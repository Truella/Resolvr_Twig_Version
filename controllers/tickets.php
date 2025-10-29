<?php
function getTickets() {
    $ticketsFile = __DIR__ . '/../data/tickets.json';
    $userId = $_SESSION['user']['id'] ?? null; // Get current user ID safely

    if (!$userId) {
        return []; // No user logged in
    }

    if (file_exists($ticketsFile)) {
        $tickets = json_decode(file_get_contents($ticketsFile), true) ?? [];

        // Filter tickets by user ID
        return array_filter($tickets, function($ticket) use ($userId) {
            return isset($ticket['user_token']) && $ticket['user_token'] == $userId;
        });
    }

    return [];
}

function getAllTickets() {
    $ticketsFile = __DIR__ . '/../data/tickets.json';
    
    if (file_exists($ticketsFile)) {
        return json_decode(file_get_contents($ticketsFile), true) ?? [];
    }
    
    return [];
}

function saveTickets($tickets) {
    $ticketsFile = __DIR__ . '/../data/tickets.json';
    
    // Ensure data directory exists
    if (!is_dir(__DIR__ . '/../data')) {
        mkdir(__DIR__ . '/../data', 0755, true);
    }
    
    file_put_contents($ticketsFile, json_encode(array_values($tickets), JSON_PRETTY_PRINT));
}

function renderTickets($twig) {
    $tickets = getTickets();
    $toast = $_SESSION['toast'] ?? null;
    unset($_SESSION['toast']);
    
    echo $twig->render('dashboard/tickets.twig', [
        'user' => $_SESSION['user'],
        'tickets' => array_values($tickets), // Re-index array
        'toast' => $toast
    ]);
}

function handleTicketAction($twig) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            createTicket();
            break;
        case 'update':
            updateTicket();
            break;
        case 'delete':
            deleteTicket();
            break;
        default:
            $_SESSION['toast'] = ['message' => 'Invalid action', 'type' => 'error'];
    }
    
    header('Location: /dashboard/tickets');
    exit;
}

function createTicket() {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'open';
    $priority = $_POST['priority'] ?? 'medium';
    
    // Validation
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'Title is required';
    } elseif (strlen($title) > 100) {
        $errors[] = 'Title must be less than 100 characters';
    }
    
    if (!in_array($status, ['open', 'in_progress', 'closed'])) {
        $errors[] = 'Invalid status';
    }
    
    if (!empty($description) && strlen($description) > 500) {
        $errors[] = 'Description must be less than 500 characters';
    }
    
    if (!empty($errors)) {
        $_SESSION['toast'] = ['message' => implode(', ', $errors), 'type' => 'error'];
        return;
    }
    
    // Get ALL tickets from file
    $allTickets = getAllTickets();
    
    $newTicket = [
        'id' => uniqid('ticket_', true), // Better unique ID
        'title' => $title,
        'description' => $description,
        'status' => $status,
        'priority' => $priority,
        'createdAt' => date('c'),
        'user_token' => $_SESSION['user']['id']
    ];
    
    // Add new ticket to all tickets
    $allTickets[] = $newTicket;
    
    // Save ALL tickets (not just current user's)
    saveTickets($allTickets);
    
    error_log('✓ Ticket created for user: ' . $_SESSION['user']['id']);
    
    $_SESSION['toast'] = ['message' => 'Ticket created successfully!', 'type' => 'success'];
}

function updateTicket() {
    $id = $_POST['id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'open';
    $priority = $_POST['priority'] ?? 'medium';
    $userId = $_SESSION['user']['id'];
    
    // Validation
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'Title is required';
    } elseif (strlen($title) > 100) {
        $errors[] = 'Title must be less than 100 characters';
    }
    
    if (!in_array($status, ['open', 'in_progress', 'closed'])) {
        $errors[] = 'Invalid status';
    }
    
    if (!empty($description) && strlen($description) > 500) {
        $errors[] = 'Description must be less than 500 characters';
    }
    
    if (!empty($errors)) {
        $_SESSION['toast'] = ['message' => implode(', ', $errors), 'type' => 'error'];
        return;
    }
    
    // Get ALL tickets from file
    $allTickets = getAllTickets();
    $updated = false;
    
    foreach ($allTickets as &$ticket) {
        // Only update if it's the right ticket AND belongs to current user
        if ($ticket['id'] === $id && $ticket['user_token'] === $userId) {
            $ticket['title'] = $title;
            $ticket['description'] = $description;
            $ticket['status'] = $status;
            $ticket['priority'] = $priority;
            $ticket['updatedAt'] = date('c');
            $updated = true;
            break;
        }
    }
    
    if ($updated) {
        // Save ALL tickets
        saveTickets($allTickets);
        $_SESSION['toast'] = ['message' => 'Ticket updated successfully!', 'type' => 'success'];
    } else {
        $_SESSION['toast'] = ['message' => 'Ticket not found or unauthorized', 'type' => 'error'];
    }
}

function deleteTicket() {
    $id = $_POST['id'] ?? '';
    $userId = $_SESSION['user']['id'];
    
    // Get ALL tickets from file
    $allTickets = getAllTickets();
    
    // Remove only if ticket belongs to current user
    $remainingTickets = array_filter($allTickets, function($ticket) use ($id, $userId) {
        return !($ticket['id'] === $id && $ticket['user_token'] === $userId);
    });
    
    // Save ALL remaining tickets
    saveTickets($remainingTickets);
    
    $_SESSION['toast'] = ['message' => 'Ticket deleted successfully!', 'type' => 'success'];
}