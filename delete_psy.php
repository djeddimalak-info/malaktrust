<?php

session_start();

if (!isset($_SESSION['email'])) {
    header('Location: login.php'); // vers login
    exit;
}

if (isset($_POST['iddp']) && !empty($_POST['iddp'])) {
    $host = '127.0.0.1';
    $dbname = 'trusteducation';
    $username = 'root';
    $password = '';

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("DELETE FROM demande_psy WHERE IDDP = ?");  // suppd de ta table
        $stmt->execute([$_POST['iddp']]); // BY IDDP
    } catch(PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }
}

header('Location: dashboardpsy.php');
exit;
