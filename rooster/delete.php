<?php
include __DIR__ . '/config.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM rooster WHERE id = ?");
    $stmt->execute([$id]);
}
header("Location: ../pages/rooster.html");
exit;