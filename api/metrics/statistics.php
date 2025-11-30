<?php

require_once '../../config/security.php';
set_security_headers();

header('Content-Type: application/json');

require_manager();

require_once '../../config/Database.php';

$db = new Database();
$conn = $db->connect();

$manager_id = $_SESSION['user_id'];
$period = $_GET['period'] ?? 'year';

try {
    // Get monthly sales and revenue data for the past year
    $stmt = $conn->prepare("
        SELECT
            DATE_FORMAT(e.event_date, '%b') as month,
            MONTH(e.event_date) as month_num,
            COALESCE(SUM(t.sold * t.price), 0) as sales,
            COALESCE(SUM(CASE WHEN r.status = 'refunded' THEN t.price ELSE 0 END), 0) as returns
        FROM events e
        LEFT JOIN tickets t ON e.event_id = t.event_id
        LEFT JOIN rsvps r ON e.event_id = r.event_id
        WHERE e.manager_id = ?
        AND e.event_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY YEAR(e.event_date), MONTH(e.event_date)
        ORDER BY e.event_date ASC
    ");

    $stmt->bind_param("i", $manager_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize arrays with zeros for all months
    $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $sales = array_fill(0, 12, 0);
    $returns = array_fill(0, 12, 0);

    // Fill in actual data
    while ($row = $result->fetch_assoc()) {
        $monthIndex = (int)$row['month_num'] - 1;
        $sales[$monthIndex] = (float)$row['sales'];
        $returns[$monthIndex] = (float)$row['returns'];
    }

    $stmt->close();
    $db->close();

    // Return statistics data
    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'sales' => $sales,
        'returns' => $returns
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch statistics: ' . $e->getMessage()
    ]);
}

?>
