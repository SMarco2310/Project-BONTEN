<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'manager') {
    header("Location: ../../views/index.php");
    exit();
}

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
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

       
        if (!in_array($file_extension, $allowed_extensions)) {
            $error_message = 'Invalid image format. Only JPG, PNG, GIF, and WEBP are allowed.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $error_message = 'Image size must be less than 5MB.';
        } else {
            
            $new_filename = 'event_' . time() . '_' . uniqid() . '.' . $file_extension;
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
                    
                    if (isset($_POST['tickets']) && is_array($_POST['tickets'])) {
                        foreach ($_POST['tickets'] as $ticket) {
                            $ticket_name = $ticket['name'] ?? '';
                            $ticket_price = floatval($ticket['price'] ?? 0);
                            $ticket_quantity = (int)($ticket['quantity'] ?? 0);

                            if (!empty($ticket_name) && $ticket_quantity > 0) {
                                $stmt = $conn->prepare("
                                    INSERT INTO tickets (event_id, ticket_name, price, quantity, sold)
                                    VALUES (?, ?, ?, ?, 0)
                                ");
                                $stmt->bind_param("isdi", $event_id, $ticket_name, $ticket_price, $ticket_quantity);

                                if (!$stmt->execute()) {
                                    throw new Exception('Failed to create ticket: ' . $stmt->error);
                                }
                                $stmt->close();
                            }
                        }
                    }
                }

                
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


$_SESSION['error_message'] = $error_message;
header("Location: ../../views/create_event.php");
exit();
?>
