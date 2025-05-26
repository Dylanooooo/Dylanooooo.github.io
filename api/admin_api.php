<?php
session_start();
include('../includes/config.php');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['rol']) !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Ongeautoriseerde toegang']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_project':
            try {
                $naam = trim($_POST['naam']);
                $beschrijving = trim($_POST['beschrijving'] ?? '');
                $start_datum = $_POST['start_datum'];
                $eind_datum = $_POST['eind_datum'];
                $status = $_POST['status'];
                $voortgang = intval($_POST['voortgang']);
                
                // Validatie
                if (empty($naam) || empty($start_datum) || empty($eind_datum)) {
                    throw new Exception('Verplichte velden zijn niet ingevuld');
                }
                
                if (strtotime($start_datum) > strtotime($eind_datum)) {
                    throw new Exception('Startdatum kan niet na einddatum liggen');
                }
                
                if ($voortgang < 0 || $voortgang > 100) {
                    throw new Exception('Voortgang moet tussen 0 en 100% liggen');
                }
                
                // Check if aangemaakt_door column exists
                $stmt = $pdo->query("SHOW COLUMNS FROM projecten LIKE 'aangemaakt_door'");
                $column_exists = $stmt->rowCount() > 0;
                
                if ($column_exists) {
                    // Project toevoegen aan database met aangemaakt_door
                    $sql = "INSERT INTO projecten (naam, beschrijving, start_datum, eind_datum, status, voortgang, aangemaakt_door) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $naam,
                        $beschrijving,
                        $start_datum,
                        $eind_datum,
                        $status,
                        $voortgang,
                        $_SESSION['user_id']
                    ]);
                } else {
                    // Project toevoegen aan database zonder aangemaakt_door
                    $sql = "INSERT INTO projecten (naam, beschrijving, start_datum, eind_datum, status, voortgang) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $naam,
                        $beschrijving,
                        $start_datum,
                        $eind_datum,
                        $status,
                        $voortgang
                    ]);
                }
                
                $project_id = $pdo->lastInsertId();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Project succesvol aangemaakt',
                    'project_id' => $project_id
                ]);
                
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Fout bij aanmaken project: ' . $e->getMessage()
                ]);
            }
            break;
            
        case 'create_task':
            try {
                $project_id = intval($_POST['project_id']);
                $naam = trim($_POST['naam']);
                $beschrijving = trim($_POST['beschrijving'] ?? '');
                $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
                $status = $_POST['status'];
                $toegewezen_aan = !empty($_POST['toegewezen_aan']) ? intval($_POST['toegewezen_aan']) : null;
                
                // Validatie
                if (empty($naam) || empty($project_id)) {
                    throw new Exception('Verplichte velden zijn niet ingevuld');
                }
                
                // Controleer of project bestaat
                $stmt = $pdo->prepare("SELECT id FROM projecten WHERE id = ?");
                $stmt->execute([$project_id]);
                if (!$stmt->fetch()) {
                    throw new Exception('Geselecteerd project bestaat niet');
                }
                
                // Check if aangemaakt_door column exists in taken table
                $stmt = $pdo->query("SHOW COLUMNS FROM taken LIKE 'aangemaakt_door'");
                $column_exists = $stmt->rowCount() > 0;
                
                if ($column_exists) {
                    // Taak toevoegen aan database met aangemaakt_door
                    $sql = "INSERT INTO taken (project_id, naam, beschrijving, deadline, status, toegewezen_aan, aangemaakt_door) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $project_id,
                        $naam,
                        $beschrijving,
                        $deadline,
                        $status,
                        $toegewezen_aan,
                        $_SESSION['user_id']
                    ]);
                } else {
                    // Taak toevoegen aan database zonder aangemaakt_door
                    $sql = "INSERT INTO taken (project_id, naam, beschrijving, deadline, status, toegewezen_aan) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $project_id,
                        $naam,
                        $beschrijving,
                        $deadline,
                        $status,
                        $toegewezen_aan
                    ]);
                }
                
                $task_id = $pdo->lastInsertId();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Taak succesvol aangemaakt',
                    'task_id' => $task_id
                ]);
                
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Fout bij aanmaken taak: ' . $e->getMessage()
                ]);
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Onbekende actie'
            ]);
            break;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Alleen POST requests toegestaan'
    ]);
}
?>
