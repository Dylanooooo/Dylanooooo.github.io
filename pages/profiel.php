<?php
session_start();
include('../includes/config.php');

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Maak de uploads directory aan als deze nog niet bestaat
$uploadsDir = '../uploads/profiles/';
if (!file_exists($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

// Verwerk profielupdates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Verwerk wachtwoord wijziging
    if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Haal huidige wachtwoord op uit database
        $stmt = $pdo->prepare("SELECT wachtwoord FROM gebruikers WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        $user_data = $stmt->fetch();
        
        // Controleer of het huidige wachtwoord correct is
        if (!password_verify($current_password, $user_data['wachtwoord'])) {
            $error_message = "Huidig wachtwoord is onjuist.";
        } 
        // Controleer of de nieuwe wachtwoorden overeenkomen
        elseif ($new_password !== $confirm_password) {
            $error_message = "Nieuwe wachtwoorden komen niet overeen.";
        }
        // Controleer of het nieuwe wachtwoord sterk genoeg is
        elseif (strlen($new_password) < 6) {
            $error_message = "Wachtwoord moet minimaal 6 karakters bevatten.";
        }
        else {
            // Hash het nieuwe wachtwoord
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update het wachtwoord in de database
            $stmt = $pdo->prepare("UPDATE gebruikers SET wachtwoord = :wachtwoord WHERE id = :id");
            $stmt->bindParam(':wachtwoord', $hashed_password);
            $stmt->bindParam(':id', $user_id);
            
            if ($stmt->execute()) {
                $success_message = "Wachtwoord succesvol gewijzigd.";
            } else {
                $error_message = "Er is een fout opgetreden bij het wijzigen van je wachtwoord.";
            }
        }
    }
    
    // Profielfoto upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $fileInfo = pathinfo($_FILES['profile_image']['name']);
        $extension = strtolower($fileInfo['extension']);
        
        // Controleer bestandstype
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($extension, $allowed_extensions)) {
            $newFileName = 'profile_' . $user_id . '_' . time() . '.' . $extension;
            $uploadFile = $uploadsDir . $newFileName;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile)) {
                // Update database met nieuwe profielfoto
                $stmt = $pdo->prepare("UPDATE gebruikers SET profile_image = :profile_image WHERE id = :id");
                $stmt->bindParam(':profile_image', $newFileName);
                $stmt->bindParam(':id', $user_id);
                $stmt->execute();
                
                // Update sessie
                $_SESSION['profile_image'] = $newFileName;
                $success_message = "Profielfoto succesvol bijgewerkt.";
            } else {
                $error_message = "Er is een fout opgetreden bij het uploaden van de profielfoto.";
            }
        } else {
            $error_message = "Alleen JPG, PNG en GIF bestanden zijn toegestaan.";
        }
    }
    
    // Andere profielgegevens updaten
    if (isset($_POST['naam']) && !empty($_POST['naam'])) {
        $naam = trim($_POST['naam']);
        $email = trim($_POST['email']);
        
        $stmt = $pdo->prepare("UPDATE gebruikers SET naam = :naam, email = :email WHERE id = :id");
        $stmt->bindParam(':naam', $naam);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['naam'] = $naam;
            $success_message = "Profielgegevens succesvol bijgewerkt.";
        } else {
            $error_message = "Er is een fout opgetreden bij het bijwerken van je profiel.";
        }
    }
}

// Haal gebruikersgegevens op
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM gebruikers WHERE id = :id");
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch();

// Relatief pad voor navigatie
$root_path = "../";
$pageTitle = "Mijn Profiel | Flitz-Events";
$useIcons = true;
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Inclusie van de consistente navigatie component -->
    <?php include('../includes/navigation.php'); ?>

    <section id="profiel">
        <div class="container">
            <h2>Mijn Profiel</h2>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-container">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php if (!empty($user['profile_image']) && file_exists($uploadsDir . $user['profile_image'])): ?>
                            <img src="<?php echo '../uploads/profiles/' . $user['profile_image']; ?>" alt="Profielfoto">
                        <?php else: ?>
                            <div class="profile-initials">
                                <?php
                                $name_parts = explode(' ', $user['naam']);
                                echo strtoupper(substr($name_parts[0], 0, 1));
                                if (count($name_parts) > 1) {
                                    echo strtoupper(substr(end($name_parts), 0, 1));
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                        <div class="avatar-overlay">
                            <i class="fas fa-camera"></i>
                        </div>
                    </div>
                    <div class="profile-info">
                        <h3><?php echo htmlspecialchars($user['naam']); ?></h3>
                        <p class="profile-role">
                            <i class="fas fa-user-tag"></i> <?php echo ucfirst(htmlspecialchars($user['rol'])); ?>
                        </p>
                        <p class="profile-email">
                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                        </p>
                        <?php if (isset($user['datum_aangemaakt'])): ?>
                        <p class="profile-join-date">
                            <i class="fas fa-calendar-plus"></i> Lid sinds <?php echo date('M Y', strtotime($user['datum_aangemaakt'])); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="profile-content">
                    <div class="dashboard-widget">
                        <h3><i class="fas fa-user-edit"></i> Persoonlijke Informatie</h3>
                        <form id="profile-form" class="profile-form" method="post" enctype="multipart/form-data">
                            <div class="form-row">
                                <label for="profile-naam"><i class="fas fa-user"></i> Naam</label>
                                <input type="text" id="profile-naam" name="naam" value="<?php echo htmlspecialchars($user['naam']); ?>" required>
                            </div>
                            <div class="form-row">
                                <label for="profile-email"><i class="fas fa-envelope"></i> E-mail</label>
                                <input type="email" id="profile-email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="form-row">
                                <label for="profile-image"><i class="fas fa-camera"></i> Profielfoto</label>
                                <input type="file" id="profile-image" name="profile_image" accept="image/*">
                                <p class="form-help">Toegestane formaten: JPG, PNG, GIF (max 5MB)</p>
                            </div>
                            <div class="form-row">
                                <button type="submit" class="button-small">
                                    <i class="fas fa-save"></i> Opslaan
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="dashboard-widget">
                        <h3><i class="fas fa-lock"></i> Wachtwoord Wijzigen</h3>
                        <form id="password-form" class="profile-form" method="post" action="">
                            <input type="hidden" name="action" value="change_password">
                            <div class="form-row">
                                <label for="current-password"><i class="fas fa-key"></i> Huidig Wachtwoord</label>
                                <input type="password" id="current-password" name="current_password" required>
                            </div>
                            <div class="form-row">
                                <label for="new-password"><i class="fas fa-lock"></i> Nieuw Wachtwoord</label>
                                <input type="password" id="new-password" name="new_password" required minlength="6">
                                <p class="form-help">Minimaal 6 karakters</p>
                            </div>
                            <div class="form-row">
                                <label for="confirm-password"><i class="fas fa-lock"></i> Bevestig Wachtwoord</label>
                                <input type="password" id="confirm-password" name="confirm_password" required>
                            </div>
                            <div class="form-row">
                                <button type="submit" class="button-small">
                                    <i class="fas fa-shield-alt"></i> Wachtwoord Wijzigen
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Additional profile sections -->
                    <div class="dashboard-widget">
                        <h3><i class="fas fa-chart-line"></i> Activiteit Overzicht</h3>
                        <div class="activity-stats">
                            <?php
                            // Get user statistics
                            $stmt = $pdo->prepare("SELECT COUNT(*) as task_count FROM taken WHERE toegewezen_aan = :user_id");
                            $stmt->execute(['user_id' => $user_id]);
                            $task_count = $stmt->fetch()['task_count'];
                            
                            $stmt = $pdo->prepare("SELECT COUNT(*) as completed_tasks FROM taken WHERE toegewezen_aan = :user_id AND status = 'afgerond'");
                            $stmt->execute(['user_id' => $user_id]);
                            $completed_tasks = $stmt->fetch()['completed_tasks'];
                            ?>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $task_count; ?></div>
                                <div class="stat-label">Toegewezen taken</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $completed_tasks; ?></div>
                                <div class="stat-label">Voltooide taken</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $task_count > 0 ? round(($completed_tasks / $task_count) * 100) : 0; ?>%</div>
                                <div class="stat-label">Voltooiingspercentage</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include('../includes/footer.php'); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password confirmation validation
            const newPassword = document.getElementById('new-password');
            const confirmPassword = document.getElementById('confirm-password');
            
            function validatePassword() {
                if (newPassword.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Wachtwoorden komen niet overeen');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
            
            newPassword.addEventListener('change', validatePassword);
            confirmPassword.addEventListener('keyup', validatePassword);
            
            // File upload preview
            const profileImage = document.getElementById('profile-image');
            const avatar = document.querySelector('.profile-avatar');
            
            profileImage.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = avatar.querySelector('img');
                        if (img) {
                            img.src = e.target.result;
                        } else {
                            const newImg = document.createElement('img');
                            newImg.src = e.target.result;
                            newImg.alt = 'Profielfoto';
                            avatar.querySelector('.profile-initials').style.display = 'none';
                            avatar.insertBefore(newImg, avatar.firstChild);
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
</body>
</html>
