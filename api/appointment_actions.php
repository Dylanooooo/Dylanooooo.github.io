<?php
session_start();
include('../includes/config.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Je moet ingelogd zijn']);
    exit();
}

// Function to validate date format
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

// Function to validate time format
function validateTime($time) {
    $t = DateTime::createFromFormat('H:i', $time);
    return $t && $t->format('H:i') === $time;
}

// Handle create appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'plan_afspraak') {
    // Debug log
    error_log("Appointment creation started - User ID: {$_SESSION['user_id']}");
    error_log("Appointment data: " . json_encode($_POST));

    // Check CSRF token
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['appointment_form_token']) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ongeldige token']);
        exit();
    }

    // Validate required fields
    if (!isset($_POST['titel']) || empty($_POST['titel'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Titel is verplicht']);
        exit();
    }

    if (!isset($_POST['datum']) || empty($_POST['datum']) || !validateDate($_POST['datum'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Geldige datum is verplicht']);
        exit();
    }

    if (!isset($_POST['start_tijd']) || empty($_POST['start_tijd']) || !validateTime($_POST['start_tijd'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Geldige starttijd is verplicht']);
        exit();
    }

    if (!isset($_POST['eind_tijd']) || empty($_POST['eind_tijd']) || !validateTime($_POST['eind_tijd'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Geldige eindtijd is verplicht']);
        exit();
    }

    if (!isset($_POST['deelnemers']) || !is_array($_POST['deelnemers']) || empty($_POST['deelnemers'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Tenminste één deelnemer is verplicht']);
        exit();
    }

    // Validate time range
    if ($_POST['start_tijd'] >= $_POST['eind_tijd']) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'De eindtijd moet later zijn dan de starttijd']);
        exit();
    }

    // Format times for database
    $startTijd = $_POST['start_tijd'] . ':00';
    $eindTijd = $_POST['eind_tijd'] . ':00';
    $datum = $_POST['datum'];
    $deelnemers = array_map('intval', $_POST['deelnemers']);

    // Validate all deelnemers exist in the database
    $placeholders = implode(',', array_fill(0, count($deelnemers), '?'));
    $sql = "SELECT id FROM gebruikers WHERE id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    
    foreach ($deelnemers as $index => $id) {
        $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    $foundUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($foundUsers) !== count($deelnemers)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Eén of meer deelnemers bestaan niet']);
        exit();
    }

    // Add the current user to deelnemers for conflict check
    $currentUserId = $_SESSION['user_id'];
    $deelnemers[] = $currentUserId;
    $deelnemers = array_unique($deelnemers);

    try {
        // Check for scheduling conflicts
        $placeholders = implode(',', array_fill(0, count($deelnemers), '?'));
        $sql = "SELECT DISTINCT g.naam 
                FROM afspraken a
                JOIN afspraak_deelnemers ad ON a.id = ad.afspraak_id
                JOIN gebruikers g ON ad.gebruiker_id = g.id
                WHERE a.datum = ?
                AND ((a.start_tijd < ? AND a.eind_tijd > ?) OR (a.start_tijd < ? AND a.eind_tijd > ?))
                AND ad.gebruiker_id IN ($placeholders)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $datum, PDO::PARAM_STR);
        $stmt->bindValue(2, $eindTijd, PDO::PARAM_STR);
        $stmt->bindValue(3, $startTijd, PDO::PARAM_STR);
        $stmt->bindValue(4, $eindTijd, PDO::PARAM_STR);
        $stmt->bindValue(5, $startTijd, PDO::PARAM_STR);
        
        foreach ($deelnemers as $index => $id) {
            $stmt->bindValue($index + 6, $id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $conflicts = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($conflicts)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => 'Conflicten gevonden met: ' . implode(', ', $conflicts)
            ]);
            exit();
        }

        // Check if users are available on the selected date/time
        $placeholders = implode(',', array_fill(0, count($deelnemers), '?'));
        $sql = "SELECT DISTINCT g.naam 
                FROM aanwezigheid a
                JOIN gebruikers g ON a.gebruiker_id = g.id
                WHERE a.datum = ?
                AND a.status != 'aanwezig'
                AND a.gebruiker_id IN ($placeholders)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $datum, PDO::PARAM_STR);
        
        foreach ($deelnemers as $index => $id) {
            $stmt->bindValue($index + 2, $id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $unavailableUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($unavailableUsers)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => 'Afwezige gebruikers: ' . implode(', ', $unavailableUsers)
            ]);
            exit();
        }

        // Begin transaction
        $pdo->beginTransaction();

        // Insert appointment
        $sql = "INSERT INTO afspraken (titel, beschrijving, locatie, datum, start_tijd, eind_tijd, organisator_id, project_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $_POST['titel'], PDO::PARAM_STR);
        $stmt->bindValue(2, $_POST['beschrijving'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(3, $_POST['locatie'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(4, $datum, PDO::PARAM_STR);
        $stmt->bindValue(5, $startTijd, PDO::PARAM_STR);
        $stmt->bindValue(6, $eindTijd, PDO::PARAM_STR);
        $stmt->bindValue(7, $currentUserId, PDO::PARAM_INT);
        $stmt->bindValue(8, !empty($_POST['project_id']) ? $_POST['project_id'] : null, PDO::PARAM_INT);
        
        $stmt->execute();
        $appointmentId = $pdo->lastInsertId();

        // Add participants
        foreach ($deelnemers as $deelnemer) {
            if ($deelnemer != $currentUserId) { // Skip organizer for now
                $sql = "INSERT INTO afspraak_deelnemers (afspraak_id, gebruiker_id, status) VALUES (?, ?, 'uitgenodigd')";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(1, $appointmentId, PDO::PARAM_INT);
                $stmt->bindValue(2, $deelnemer, PDO::PARAM_INT);
                $stmt->execute();
            }
        }

        // Add organizer as accepted
        $sql = "INSERT INTO afspraak_deelnemers (afspraak_id, gebruiker_id, status) VALUES (?, ?, 'geaccepteerd')";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $appointmentId, PDO::PARAM_INT);
        $stmt->bindValue(2, $currentUserId, PDO::PARAM_INT);
        $stmt->execute();

        // Commit transaction
        $pdo->commit();

        // Create new token to prevent duplicate submissions
        $_SESSION['appointment_form_token'] = md5(uniqid(mt_rand(), true));

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => 'Afspraak succesvol aangemaakt',
            'new_token' => $_SESSION['appointment_form_token']
        ]);
        exit();

    } catch (PDOException $e) {
        // Rollback on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log("Appointment creation error: " . $e->getMessage());
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Fout bij aanmaken afspraak: ' . $e->getMessage()
        ]);
        exit();
    }
}

// If we reach here, unknown action
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Ongeldige actie']);
exit();
?>
