<?php

require_once '../../config/security.php';
set_security_headers();

header('Content-Type: application/json');

require_manager();

require_once '../../config/Database.php';

$db = new Database();
$conn = $db->connect();

$manager_id = $_SESSION['user_id'];
$period = $_GET['period'] ?? 'june';

try {
    // Get current period revenue, tickets sold, and active events
    $stmt = $conn->prepare("
        SELECT
            COUNT(DISTINCT e.event_id) as total_events,
            COALESCE(SUM(t.sold), 0) as tickets_sold,
            COALESCE(SUM(t.sold * t.price), 0) as total_revenue
        FROM events e
        LEFT JOIN tickets t ON e.event_id = t.event_id
        WHERE e.manager_id = ?
        AND e.status = 'active'
    ");

    $stmt->bind_param("i", $manager_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current = $result->fetch_assoc();
    $stmt->close();

    // Get previous period data for comparison (simplified - using completed events)
    $prev_stmt = $conn->prepare("
        SELECT
            COALESCE(SUM(t.sold), 0) as tickets_sold,
            COALESCE(SUM(t.sold * t.price), 0) as total_revenue
        FROM events e
        LEFT JOIN tickets t ON e.event_id = t.event_id
        WHERE e.manager_id = ?
        AND e.status = 'completed'
        LIMIT 10
    ");

    $prev_stmt->bind_param("i", $manager_id);
    $prev_stmt->execute();
    $prev_result = $prev_stmt->get_result();
    $previous = $prev_result->fetch_assoc();
    $prev_stmt->close();

    $db->close();

    // Calculate percentage changes
    $revenueChange = 0;
    $ticketsChange = 0;

    if ($previous['total_revenue'] > 0) {
        $revenueChange = (($current['total_revenue'] - $previous['total_revenue']) / $previous['total_revenue']) * 100;
    } elseif ($current['total_revenue'] > 0) {
        $revenueChange = 100;
    }

    if ($previous['tickets_sold'] > 0) {
        $ticketsChange = (($current['tickets_sold'] - $previous['tickets_sold']) / $previous['tickets_sold']) * 100;
    } elseif ($current['tickets_sold'] > 0) {
        $ticketsChange = 100;
    }

    // Return summary metrics
    echo json_encode([
        'success' => true,
        'totalRevenue' => (float)$current['total_revenue'],
        'ticketsSold' => (int)$current['tickets_sold'],
        'activeEvents' => (int)$current['total_events'],
        'revenueChange' => round($revenueChange, 1),
        'ticketsChange' => round($ticketsChange, 1),
        'currency' => 'GHC'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch summary metrics: ' . $e->getMessage()
    ]);
}

?>
