<?php
require_once '../config/security.php';
set_security_headers();
header('Content-Type: application/json');

require_manager();

require_once '../config/Database.php';

$db = new Database();

$conn = $db->connect();

$manager_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'list';

// Get all events for the manager
if ($action === 'list') {
    $status_filter = $_GET['status'] ?? 'all';
    $sort_by = $_GET['sort'] ?? 'date-desc';
    $search = $_GET['search'] ?? '';

    // Build query based on filters
    $query = "SELECT e.*, c.name as category_name,
                     (SELECT SUM(t.sold) FROM tickets t WHERE t.event_id = e.event_id) as tickets_sold,
                     (SELECT SUM(t.sold * t.price) FROM tickets t WHERE t.event_id = e.event_id) as revenue,

                     (SELECT SUM(t.quantity) FROM tickets t WHERE t.event_id = e.event_id) as total_tickets,
                     (SELECT AVG(r.rating) FROM reviews r WHERE r.event_id = e.event_id) as avg_rating,
                     (SELECT COUNT(*) FROM rsvps r WHERE r.event_id = e.event_id AND r.attended = 1) as checkins
              FROM events e
              LEFT JOIN categories c ON e.category_id = c.category_id
              WHERE e.manager_id = ?";


    $params = [$manager_id];
    $types = 'i';

    // Apply status filter
    if ($status_filter !== 'all') {
        $query .= " AND e.status = ?";
        $params[] = $status_filter;
        $types .= 's';

    }

    // Apply search filter
    if (!empty($search)) {
        $query .= " AND (e.name LIKE ? OR c.name LIKE ? OR e.location LIKE ?)";
        $search_param = '%' . $search . '%';
        $params[] = $search_param;

        $params[] = $search_param;
        $params[] = $search_param;
        $types .= 'sss';
    }

    // Apply sorting
    switch ($sort_by) {
        case 'date-asc':
            $query .= " ORDER BY e.event_date ASC";
            break;
        case 'revenue-desc':
            $query .= " ORDER BY revenue DESC";
            break;
        case 'revenue-asc':
            $query .= " ORDER BY revenue ASC";

            break;
        case 'tickets-desc':
            $query .= " ORDER BY tickets_sold DESC";
            break;
        default: // date-desc
            $query .= " ORDER BY e.event_date DESC";
            break;
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $events = [
        'active' => [],

        'past' => [],
        'other' => [],
        'total' => 0
    ];

    while ($row = $result->fetch_assoc()) {
        $event_data = [
            'id' => 'event-' . $row['event_id'],
            'event_id' => $row['event_id'],
            'name' => htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'),
            'category' => htmlspecialchars($row['category_name'] ?? 'Event', ENT_QUOTES, 'UTF-8'),
            'image' => htmlspecialchars($row['image_path'] ?? '../public/assets/hero.png', ENT_QUOTES, 'UTF-8'),
            'date' => date('F j, Y', strtotime($row['event_date'])),
            'dateObj' => $row['event_date'],
            'location' => htmlspecialchars($row['location'] ?? 'TBD', ENT_QUOTES, 'UTF-8'),
            'ticketsSold' => (int)($row['tickets_sold'] ?? 0),
            'totalTickets' => (int)($row['total_tickets'] ?? 0),
            'revenue' => (float)($row['revenue'] ?? 0),
            'status' => htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'),
            'rating' => $row['avg_rating'] ? round($row['avg_rating'], 1) : null,
            'checkins' => (int)($row['checkins'] ?? 0),
            'createdAt' => date('F j, Y', strtotime($row['created_at']))
        ];

        // Categorize events
        if ($row['status'] === 'active') {
            $events['active'][] = $event_data;
        } elseif ($row['status'] === 'completed') {
            $events['past'][] = $event_data;
        } else {
            $events['other'][] = $event_data;
        }
        $events['total']++;
    }

    $stmt->close();
    $db->close();

    echo json_encode($events);
}


// Get event details
elseif ($action === 'details') {
    $event_id = $_GET['id'] ?? 0;

    $stmt = $conn->prepare("
        SELECT e.*, c.name as category_name,
               (SELECT SUM(t.sold) FROM tickets t WHERE t.event_id = e.event_id) as tickets_sold,
               (SELECT SUM(t.sold * t.price) FROM tickets t WHERE t.event_id = e.event_id) as revenue,
               (SELECT SUM(t.quantity) FROM tickets t WHERE t.event_id = e.event_id) as total_tickets,
               (SELECT AVG(r.rating) FROM reviews r WHERE r.event_id = e.event_id) as avg_rating,
               (SELECT COUNT(*) FROM rsvps r WHERE r.event_id = e.event_id AND r.attended = 1) as checkins
        FROM events e
        LEFT JOIN categories c ON e.category_id = c.category_id
        WHERE e.event_id = ? AND e.manager_id = ?
    ");
    $stmt->bind_param("ii", $event_id, $manager_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();

    $stmt->close();

    if (!$event) {
        http_response_code(404);
        echo json_encode(['error' => 'Event not found']);

        $db->close();
        exit();
    }

    // Get ticket types
    $stmt = $conn->prepare("
        SELECT ticket_name as name, sold, quantity as total, price
        FROM tickets
        WHERE event_id = ?
        ORDER BY price ASC
    ");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ticket_types = [];
    while ($row = $result->fetch_assoc()) {
        $ticket_types[] = $row;
    }
    $stmt->close();

    // Get recent reviews
    $stmt = $conn->prepare("
        SELECT r.rating, r.review_text as text, u.full_name as name, r.created_at
        FROM reviews r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.event_id = ?
        ORDER BY r.created_at DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        $reviews[] = [
            'name' => $row['name'],

            'rating' => (int)$row['rating'],
            'text' => $row['text']
        ];
    }
    $stmt->close();

    $event_details = [
        'id' => 'event-' . $event['event_id'],
        'event_id' => $event['event_id'],
        'name' => $event['name'],
        'category' => $event['category_name'] ?? 'Event',
        'image' => $event['image_path'] ?? '../public/assets/hero.png',
        'date' => date('F j, Y', strtotime($event['event_date'])),
        'location' => $event['location'] ?? 'TBD',
        'ticketsSold' => (int)($event['tickets_sold'] ?? 0),
        'totalTickets' => (int)($event['total_tickets'] ?? 0),
        'revenue' => (float)($event['revenue'] ?? 0),
        'status' => $event['status'],
        'rating' => $event['avg_rating'] ? round($event['avg_rating'], 1) : null,

        'checkins' => (int)($event['checkins'] ?? 0),
        'ticketTypes' => $ticket_types,
        'reviews' => $reviews
    ];

    $db->close();
    echo json_encode($event_details);
}


// Cancel event
elseif ($action === 'cancel') {

    $data = json_decode(file_get_contents('php://input'), true);
    $event_id = $data['event_id'] ?? 0;
    $reason = $data['reason'] ?? 'Cancelled by organizer';

    $stmt = $conn->prepare("UPDATE events SET status = 'cancelled' WHERE event_id = ? AND manager_id = ?");
    $stmt->bind_param("ii", $event_id, $manager_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Event cancelled successfully']);

    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to cancel event']);
    }


    $stmt->close();
    $db->close();
}

// Delete draft event
elseif ($action === 'delete') {
    $data = json_decode(file_get_contents('php://input'), true);
    $event_id = $data['event_id'] ?? 0;

    $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ? AND manager_id = ? AND status = 'draft'");
    $stmt->bind_param("ii", $event_id, $manager_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Draft deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete draft']);
    }

    $stmt->close();
    $db->close();
}


else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);

    $db->close();
}
?>
