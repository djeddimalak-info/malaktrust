<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Méthode non autorisée');
}

$idd = $_POST['idd'] ?? null;
if (!$idd) {
    die('ID manquant');
}

$host = '127.0.0.1';
$dbname = 'trusteducation';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Suppression de la demande dans la table 'demande'
    $stmt = $conn->prepare("DELETE FROM demande WHERE IDD = ?");
    $stmt->execute([$idd]);
    
    header("Location: dashbord.php");
    exit;
    
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>