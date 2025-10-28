<?php

function renderDashboard($twig) {
    // Load tickets to calculate stats
    $ticketsFile = __DIR__ . '/../data/tickets.json';
    $tickets = [];
    
    if (file_exists($ticketsFile)) {
        $tickets = json_decode(file_get_contents($ticketsFile), true) ?? [];
    }

    // Calculate statistics
    $stats = [
        'total' => count($tickets),
        'open' => 0,
        'inProgress' => 0,
        'resolved' => 0
    ];

    foreach ($tickets as $ticket) {
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