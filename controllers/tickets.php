<?php

function getTickets() {
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
    
    file_put_contents($ticketsFile, json_encode($tickets, JSON_PRETTY_PRINT));
}

function renderTickets($twig) {
    $tickets = getTickets();
    $toast = $_SESSION['toast'] ?? null;
    unset($_SESSION['toast']);
    
    echo $twig->render('dashboard/tickets.twig', [
        'user' => $_SESSION['user'],
        'tickets' => $tickets,
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
    
    $tickets = getTickets();
    
    $newTicket = [
        'id' => (string)time(),
        'title' => $title,
        'description' => $description,
        'status' => $status,
        'priority' => $priority,
        'createdAt' => date('c')
    ];
    
    $tickets[] = $newTicket;
    saveTickets($tickets);
    
    $_SESSION['toast'] = ['message' => 'Ticket created successfully!', 'type' => 'success'];
}

function updateTicket() {
    $id = $_POST['id'] ?? '';
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
    
    $tickets = getTickets();
    
    foreach ($tickets as &$ticket) {
        if ($ticket['id'] === $id) {
            $ticket['title'] = $title;
            $ticket['description'] = $description;
            $ticket['status'] = $status;
            $ticket['priority'] = $priority;
            $ticket['updatedAt'] = date('c');
            break;
        }
    }
    
    saveTickets($tickets);
    
    $_SESSION['toast'] = ['message' => 'Ticket updated successfully!', 'type' => 'success'];
}

function deleteTicket() {
    $id = $_POST['id'] ?? '';
    
    $tickets = getTickets();
    $tickets = array_filter($tickets, function($ticket) use ($id) {
        return $ticket['id'] !== $id;
    });
    
    saveTickets(array_values($tickets));
    
    $_SESSION['toast'] = ['message' => 'Ticket deleted successfully!', 'type' => 'success'];
}