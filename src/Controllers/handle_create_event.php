<?php
require_once '../../config/security.php';
set_security_headers();

require_manager();

require_once '../../config/Database.php';

$db = new Database();

$conn = $db->connect();

$manager_id = $_SESSION['user_id'];


$success = false;
$error_message = '';
$event_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

   
    $event_name = trim($_POST['eventName'] ?? '');

    $category_id = (int)($_POST['eventCategory'] ?? 0);

    $description = trim($_POST['eventDescription'] ?? '');
    $event_date = $_POST['eventStartDate'] ?? '';

    $event_time = $_POST['eventStartTime'] ?? '';

    $event_type = $_POST['eventType'] ?? 'in-person';
    $location = trim($_POST['eventVenue'] ?? '');
    $city = trim($_POST['eventCity'] ?? '');

    $capacity = !empty($_POST['eventCapacity']) ? (int)$_POST['eventCapacity'] : null;
    $status = $_POST['eventStatus'] ?? 'active';

    $ticket_type = $_POST['ticketType'] ?? 'free';

    
    if (empty($event_name)) {
        $error_message = 'Event name is required.';
    } elseif ($category_id == 0) {
        $error_message = 'Please select a category.';
    } elseif (empty($description)) {
        $error_message = 'Event description is required.';
    } elseif (empty($event_date)) {
        $error_message = 'Event date is required.';
    } elseif (empty($event_time)) {
        $error_message = 'Event time is required.';
    } elseif (empty($location)) {
        $error_message = 'Event location is required.';
    } elseif (empty($city)) {
        $error_message = 'City is required.';
    } elseif (!isset($_FILES['eventImage']) || $_FILES['eventImage']['error'] !== UPLOAD_ERR_OK) {
        $error_message = 'Event image is required.';
    } else {

       
        $image_path = '';
        $upload_dir = '../../public/assets/events/';

       
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file = $_FILES['eventImage'];

        $validation_result = validate_image_upload($file);

        if ($validation_result !== true) {
            $error_message = implode(', ', $validation_result);
        } else {

            $new_filename = generate_secure_filename($file['name']);

            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $image_path = '../public/assets/events/' . $new_filename;
            } else {
                $error_message = 'Failed to upload image.';
            }
        }

        
        if (empty($error_message)) {

           
            $conn->begin_transaction();

            try {
                
                $stmt = $conn->prepare("
                    INSERT INTO events (
                        manager_id, category_id, name, description,
                        event_date, event_time, location, city,
                        event_type, capacity, status, image_path
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->bind_param(
                    "iissssssssss",
                    $manager_id,
                    $category_id,
                    $event_name,
                    $description,
                    $event_date,
                    $event_time,
                    $location,
                    $city,
                    $event_type,
                    $capacity,
                    $status,
                    $image_path
                );

                if (!$stmt->execute()) {
                    throw new Exception('Failed to create event: ' . $stmt->error);
                }

                $event_id = $conn->insert_id;
                $stmt->close();

                
                if ($ticket_type === 'free') {
                    
                    $free_quantity = (int)($_POST['freeTicketQuantity'] ?? 100);

                    $stmt = $conn->prepare("
                        INSERT INTO tickets (event_id, ticket_name, price, quantity, sold)
                        VALUES (?, 'Free', 0.00, ?, 0)
                    ");
                    $stmt->bind_param("ii", $event_id, $free_quantity);

                    if (!$stmt->execute()) {
                        throw new Exception('Failed to create free ticket: ' . $stmt->error);
                    }
                    $stmt->close();

                } else {
                    // Paid event - create tickets
                    if (isset($_POST['tickets']) && is_array($_POST['tickets'])) {
                        $tickets_created = 0;
                        foreach ($_POST['tickets'] as $ticket) {
                            $ticket_name = trim($ticket['name'] ?? '');
                            $ticket_price = floatval($ticket['price'] ?? 0);
                            $ticket_quantity = (int)($ticket['quantity'] ?? 0);

                            // Validate ticket data - name, price, and quantity are all required
                            if (!empty($ticket_name) && $ticket_price >= 0 && $ticket_quantity > 0) {
                                $stmt = $conn->prepare("
                                    INSERT INTO tickets (event_id, ticket_name, price, quantity, sold)
                                    VALUES (?, ?, ?, ?, 0)
                                ");
                                $stmt->bind_param("isdi", $event_id, $ticket_name, $ticket_price, $ticket_quantity);

                                if (!$stmt->execute()) {
                                    throw new Exception('Failed to create ticket: ' . $stmt->error);
                                }
                                $stmt->close();
                                $tickets_created++;
                            }
                        }
                        
                        // If paid event but no valid tickets were created, throw an error
                        if ($tickets_created === 0) {
                            throw new Exception('Paid events must have at least one valid ticket with name, price, and quantity.');
                        }
                    } else {
                        throw new Exception('Paid events require ticket information.');
                    }
                }

                // Add 2 random comments from actual users to the new event
                // Get random regular users (not managers) to create comments
                $users_stmt = $conn->prepare("
                    SELECT user_id FROM users 
                    WHERE user_type = 'user' 
                    ORDER BY RAND() 
                    LIMIT 2
                ");
                $users_stmt->execute();
                $users_result = $users_stmt->get_result();
                
                $sample_comments = [
                    "This looks amazing! Can't wait to attend! 🔥",
                    "The lineup sounds incredible! Already RSVP'd!",
                    "Been waiting for an event like this in the city!",
                    "The venue is perfect for this type of event!",
                    "Looking forward to this! Great initiative!",
                    "This is going to be epic! See you there!",
                    "Perfect timing! Count me in! 🎉",
                    "The description got me excited! Can't wait!"
                ];
                
                $comment_count = 0;
                while (($user_row = $users_result->fetch_assoc()) !== null && $comment_count < 2) {
                    $random_comment = $sample_comments[array_rand($sample_comments)];
                    
                    $comment_stmt = $conn->prepare("
                        INSERT INTO comments (event_id, user_id, comment_text, created_at)
                        VALUES (?, ?, ?, DATE_SUB(NOW(), INTERVAL ? MINUTE))
                    ");
                    // Random time between 1-30 minutes ago to simulate real-time comments
                    $minutes_ago = rand(1, 30);
                    $comment_stmt->bind_param("iisi", $event_id, $user_row['user_id'], $random_comment, $minutes_ago);
                    
                    if ($comment_stmt->execute()) {
                        $comment_count++;
                    }
                    $comment_stmt->close();
                    
                    // Break if we've added 2 comments
                    if ($comment_count >= 2) {
                        break;
                    }
                }
                
                // If we couldn't get 2 users, that's okay - just log it
                if ($comment_count < 2 && $users_result->num_rows > 0) {
                    error_log("Only added $comment_count comments for event $event_id (insufficient users in database)");
                }
                $users_stmt->close();

                
                $conn->commit();
                $success = true;

                
                $_SESSION['success_message'] = 'Event created successfully!';
                $_SESSION['created_event_id'] = $event_id;
                header("Location: ../../views/manager_dashboard.php?event_created=1&event_id=" . $event_id);
                exit();

            } catch (Exception $e) {
                
                $conn->rollback();
                $error_message = $e->getMessage();

                
                if (!empty($image_path) && file_exists($upload_path)) {
                    unlink($upload_path);
                }
            }
        }

    }
}

$db->close();

// Only redirect with error if there's actually an error and we haven't already redirected
if (!empty($error_message) && !$success) {
    $_SESSION['error_message'] = $error_message;
    header("Location: ../../views/create_event.php");
    exit();
}
?>
