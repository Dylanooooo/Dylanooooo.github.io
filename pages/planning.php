<?php
session_start();
include('../includes/config.php');

// Debug information
error_log("Appointment actions - User ID: " . ($_SESSION['user_id'] ?? 'Not set'));
error_log("Appointment actions - User role: " . ($_SESSION['rol'] ?? 'Not set'));

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Je moet ingelogd zijn']);
    exit();
}

// Check admin role with case-insensitive comparison
if (!isset($_SESSION['rol']) || strtolower($_SESSION['rol']) !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Geen toegang tot deze functionaliteit']);
    exit();
}

// Function to validate date and time format
function validateDateTime($dateTime) {
    $d = DateTime::createFromFormat('Y-m-d H:i:s', $dateTime);
    return $d && $d->format('Y-m-d H:i:s') === $dateTime;
}

// Handle add appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_appointment') {
    // Validate required fields
    if (!isset($_POST['start_time']) || !validateDateTime($_POST['start_time'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Geldige starttijd is verplicht']);
        exit();
    }

    if (!isset($_POST['end_time']) || !validateDateTime($_POST['end_time'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Geldige eindtijd is verplicht']);
        exit();
    }

    // Check that end time is after start time
    if (strtotime($_POST['end_time']) <= strtotime($_POST['start_time'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Eindtijd moet na starttijd liggen']);
        exit();
    }

    // Validate attendees
    if (!isset($_POST['attendees']) || empty($_POST['attendees'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Deelnemers zijn verplicht']);
        exit();
    }

    // Prepare and execute query
    try {
        $sql = "INSERT INTO afspraken (start_time, end_time, attendees, beschrijving) 
                VALUES (:start_time, :end_time, :attendees, :beschrijving)";
        $stmt = $pdo->prepare($sql);

        $result = $stmt->execute([
            'start_time' => $_POST['start_time'],
            'end_time' => $_POST['end_time'],
            'attendees' => $_POST['attendees'],
            'beschrijving' => $_POST['beschrijving'] ?? ''
        ]);

        if ($result) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Afspraak succesvol toegevoegd']);
            exit();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Kon afspraak niet toevoegen']);
            exit();
        }
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database fout: ' . $e->getMessage()]);
        exit();
    }
}

// If we reach here, unknown action
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Ongeldige actie']);
exit();
?>