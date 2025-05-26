<?php
session_start();
include('../includes/config.php');
// Debug information
error_log("Admin actions - User ID: " . ($_SESSION['user_id'] ?? 'Not set'));
error_log("Admin actions - User role: " . ($_SESSION['rol'] ?? 'Not set'));
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
// Function to validate date format
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}
// Handle add project
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) &&
    $_POST['action'] === 'add_project') {
    // Validate required fields
    if (!isset($_POST['naam']) || empty($_POST['naam'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Projectnaam is verplicht']);
        exit();
    }

    if (!isset($_POST['start_datum']) || !validateDate($_POST['start_datum'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Geldige startdatum is verplicht']);
        exit();
    }

    if (!isset($_POST['eind_datum']) || !validateDate($_POST['eind_datum'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Geldige einddatum is verplicht']);
        exit();
    }

    // Check that end date is after start date
    if (strtotime($_POST['eind_datum']) < strtotime($_POST['start_datum'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Einddatum moet na startdatum
liggen']);
        exit();
    }

    // Validate status
    $allowedStatuses = ['aankomend', 'actief', 'afgerond'];
    if (!isset($_POST['status']) || !in_array($_POST['status'], $allowedStatuses)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ongeldige status']);
        exit();
    }

    // Validate voortgang (progress)
    $voortgang = isset($_POST['voortgang']) ? (int)$_POST['voortgang'] : 0;
    if ($voortgang < 0 || $voortgang > 100) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Voortgang moet tussen 0 en 100
liggen']);
        exit();
    }

    // Prepare and execute query
    try {
        $sql = "INSERT INTO projecten (naam, beschrijving, start_datum, eind_datum, status,
voortgang)
 VALUES (:naam, :beschrijving, :start_datum, :eind_datum, :status, :voortgang)";
        $stmt = $pdo->prepare($sql);

        $result = $stmt->execute([
            'naam' => $_POST['naam'],
            'beschrijving' => $_POST['beschrijving'] ?? '',
            'start_datum' => $_POST['start_datum'],
            'eind_datum' => $_POST['eind_datum'],
            'status' => $_POST['status'],
            'voortgang' => $voortgang
        ]);

        if ($result) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Project succesvol toegevoegd']);
            exit();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Kon project niet toevoegen']);
            exit();
        }
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database fout: ' . $e->getMessage()]);
        exit();
    }
}
// Handle add task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) &&
    $_POST['action'] === 'add_task') {
    // Validate required fields
    if (!isset($_POST['project_id']) || empty($_POST['project_id']) ||
        !is_numeric($_POST['project_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Geldig project is verplicht']);
        exit();
    }

    if (!isset($_POST['naam']) || empty($_POST['naam'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Taaknaam is verplicht']);
        exit();
    }

    // Validate deadline if provided
    if (isset($_POST['deadline']) && !empty($_POST['deadline']) &&
        !validateDate($_POST['deadline'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Geldige deadline is verplicht']);
        exit();
    }

    // Validate status
    $allowedStatuses = ['open', 'in_uitvoering', 'afgerond'];
    if (!isset($_POST['status']) || !in_array($_POST['status'], $allowedStatuses)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ongeldige status']);
        exit();
    }

    // Check if project exists
    $sql = "SELECT id FROM projecten WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $_POST['project_id']]);

    if ($stmt->rowCount() === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Project bestaat niet']);
        exit();
    }

    // Check if assigned user exists if provided
    if (isset($_POST['toegewezen_aan']) && !empty($_POST['toegewezen_aan'])) {
        $sql = "SELECT id FROM gebruikers WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $_POST['toegewezen_aan']]);

        if ($stmt->rowCount() === 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Toegewezen gebruiker bestaat
niet']);
            exit();
        }
    }

    // Prepare and execute query
    try {
        $sql = "INSERT INTO taken (project_id, naam, beschrijving, deadline, status,
toegewezen_aan)
 VALUES (:project_id, :naam, :beschrijving, :deadline, :status, :toegewezen_aan)";
        $stmt = $pdo->prepare($sql);

        $result = $stmt->execute([
            'project_id' => $_POST['project_id'],
            'naam' => $_POST['naam'],
            'beschrijving' => $_POST['beschrijving'] ?? '',
            'deadline' => !empty($_POST['deadline']) ? $_POST['deadline'] : null,
            'status' => $_POST['status'],
            'toegewezen_aan' => !empty($_POST['toegewezen_aan']) ? $_POST['toegewezen_aan']
                : null
        ]);

        if ($result) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Taak succesvol toegevoegd']);
            exit();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Kon taak niet toevoegen']);
            exit();
        }
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database fout: ' . $e->getMessage()]);
        exit();
    }
}
// Handle delete project
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) &&
    $_POST['action'] === 'delete_project') {
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ongeldig project ID']);
        exit();
    }

    try {
        // First delete all tasks associated with this project
        $sql = "DELETE FROM taken WHERE project_id = :project_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['project_id' => $_POST['id']]);

        // Then delete the project
        $sql = "DELETE FROM projecten WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute(['id' => $_POST['id']]);

        if ($result) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Project succesvol verwijderd']);
            exit();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Kon project niet verwijderen']);
            exit();
        }
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database fout: ' . $e->getMessage()]);
        exit();
    }
}
// Handle delete task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) &&
    $_POST['action'] === 'delete_task') {
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ongeldig taak ID']);
        exit();
    }

    try {
        $sql = "DELETE FROM taken WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute(['id' => $_POST['id']]);

        if ($result) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Taak succesvol verwijderd']);
            exit();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Kon taak niet verwijderen']);
            exit();
        }
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database fout: ' . $e->getMessage()]);
        exit();
    }
}
// Handle update project
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) &&
    $_POST['action'] === 'update_project') {
    // Validate required fields
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ongeldig project ID']);
        exit();
    }
    if (!isset($_POST['naam']) || empty($_POST['naam'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Projectnaam is verplicht']);
        exit();
    }

    if (!isset($_POST['start_datum']) || !validateDate($_POST['start_datum'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Geldige startdatum is verplicht']);
        exit();
    }

    if (!isset($_POST['eind_datum']) || !validateDate($_POST['eind_datum'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Geldige einddatum is verplicht']);
        exit();
    }

    // Check that end date is after start date
    if (strtotime($_POST['eind_datum']) < strtotime($_POST['start_datum'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Einddatum moet na startdatum
liggen']);
        exit();
    }

    // Validate status
    $allowedStatuses = ['aankomend', 'actief', 'afgerond'];
    if (!isset($_POST['status']) || !in_array($_POST['status'], $allowedStatuses)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ongeldige status']);
        exit();
    }

    // Validate voortgang (progress)
    $voortgang = isset($_POST['voortgang']) ? (int)$_POST['voortgang'] : 0;
    if ($voortgang < 0 || $voortgang > 100) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Voortgang moet tussen 0 en 100
liggen']);
        exit();
    }

    // Check if project exists
    $sql = "SELECT id FROM projecten WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $_POST['id']]);

    if ($stmt->rowCount() === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Project bestaat niet']);
        exit();
    }

    // Prepare and execute update query
    try {
        $sql = "UPDATE projecten
 SET naam = :naam,
 beschrijving = :beschrijving,
 start_datum = :start_datum,
 eind_datum = :eind_datum,
 status = :status,
 voortgang = :voortgang
 WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        $result = $stmt->execute([
            'id' => $_POST['id'],
            'naam' => $_POST['naam'],
            'beschrijving' => $_POST['beschrijving'] ?? '',
            'start_datum' => $_POST['start_datum'],
            'eind_datum' => $_POST['eind_datum'],
            'status' => $_POST['status'],
            'voortgang' => $voortgang
        ]);

        if ($result) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Project succesvol bijgewerkt']);
            exit();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Kon project niet bijwerken']);
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
