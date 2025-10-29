<?php

function renderDashboard($twig) {
    // Load tickets for current user only
    $ticketsFile = __DIR__ . '/../data/tickets.json';
    $userId = $_SESSION['user']['id'];
    $userTickets = [];
    
    if (file_exists($ticketsFile)) {
        $allTickets = json_decode(file_get_contents($ticketsFile), true) ?? [];
        
        // Filter tickets for current user only
        $userTickets = array_filter($allTickets, function($ticket) use ($userId) {
            return isset($ticket['user_token']) && $ticket['user_token'] == $userId;
        });
    }

    // Calculate statistics for current user's tickets only
    $stats = [
        'total' => count($userTickets),
        'open' => 0,
        'inProgress' => 0,
        'resolved' => 0
    ];

    foreach ($userTickets as $ticket) {
        switch ($ticket['status']) {
            case 'open':
                $stats['open']++;
                break;
            case 'in_progress':
                $stats['inProgress']++;
                break;
            case 'closed':
                $stats['resolved']++;
                break;
        }
    }

    echo $twig->render('dashboard/home.twig', [
        'user' => $_SESSION['user'],
        'currentPath' => $_SERVER['REQUEST_URI'],
        'stats' => $stats
    ]);
}