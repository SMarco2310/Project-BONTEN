<?php

require_once '../../config/security.php';
set_security_headers();

header('Content-Type: application/json');

require_manager();

require_once '../../config/Database.php';

$db = new Database();
$conn = $db->connect();

$manager_id = $_SESSION['user_id'];

try {
    // Get average rating across all manager's events
    $rating_stmt = $conn->prepare("
        SELECT
            AVG(r.rating) as avg_rating,
            COUNT(r.review_id) as total_reviews
        FROM reviews r
        INNER JOIN events e ON r.event_id = e.event_id
        WHERE e.manager_id = ?
    ");

    $rating_stmt->bind_param("i", $manager_id);
    $rating_stmt->execute();
    $rating_result = $rating_stmt->get_result();
    $rating_data = $rating_result->fetch_assoc();
    $rating_stmt->close();

    // Get engagement rate (RSVPs / total ticket capacity)
    $engagement_stmt = $conn->prepare("
        SELECT
            COUNT(DISTINCT r.rsvp_id) as total_rsvps,
            SUM(t.quantity) as total_capacity
        FROM events e
        LEFT JOIN rsvps r ON e.event_id = r.event_id
        LEFT JOIN tickets t ON e.event_id = t.event_id
        WHERE e.manager_id = ?
        AND e.status = 'active'
    ");

    $engagement_stmt->bind_param("i", $manager_id);
    $engagement_stmt->execute();
    $engagement_result = $engagement_stmt->get_result();
    $engagement_data = $engagement_result->fetch_assoc();
    $engagement_stmt->close();

    // Calculate engagement rate
    $engagementRate = 0;
    if ($engagement_data['total_capacity'] > 0) {
        $engagementRate = ($engagement_data['total_rsvps'] / $engagement_data['total_capacity']) * 100;
    }

    // Get previous period average rating for trend (simplified)
    $prev_rating_stmt = $conn->prepare("
        SELECT AVG(r.rating) as prev_avg_rating
        FROM reviews r
        INNER JOIN events e ON r.event_id = e.event_id
        WHERE e.manager_id = ?
        AND e.status = 'completed'
        AND r.created_at < DATE_SUB(NOW(), INTERVAL 1 MONTH)
    ");

    $prev_rating_stmt->bind_param("i", $manager_id);
    $prev_rating_stmt->execute();
    $prev_rating_result = $prev_rating_stmt->get_result();
    $prev_rating_data = $prev_rating_result->fetch_assoc();
    $prev_rating_stmt->close();

    $db->close();

    $avgRating = $rating_data['avg_rating'] ? round((float)$rating_data['avg_rating'], 1) : 0;
    $prevAvgRating = $prev_rating_data['prev_avg_rating'] ? (float)$prev_rating_data['prev_avg_rating'] : $avgRating;

    // Determine trends
    $ratingTrend = $avgRating >= $prevAvgRating ? 'up' : 'down';
    $engagementTrend = $engagementRate >= 50 ? 'up' : 'down';

    // Return secondary metrics
    echo json_encode([
        'success' => true,
        'avgRating' => $avgRating,
        'maxRating' => 5,
        'engagementRate' => round($engagementRate, 1),
        'ratingTrend' => $ratingTrend,
        'engagementTrend' => $engagementTrend
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch secondary metrics: ' . $e->getMessage()
    ]);
}

?>
