<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); 

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}
$host = '127.0.0.1';
$dbname = 'trusteducation';
$username = 'root';
$password = '';
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->query("SELECT * FROM demande_psy ORDER BY IDDP DESC");
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Demande de contact psychologue</title>
    <style>
body {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    min-height: 100vh;
}
.dashboard-container {
    max-width: 1100px;
    margin: 40px auto;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 6px 24px rgba(0,0,0,0.10);
    padding: 32px 40px;
}
h2 {
    text-align: center;
    color: #1a237e;
    margin-bottom: 32px;
    letter-spacing: 1px;
}
.demandes-table {
    width: 100%;
    border-collapse: collapse;
    background: #fafbfc;
    border-radius: 10px;
    overflow: hidden;
}
.demandes-table th, .demandes-table td {
    padding: 14px 12px;
    text-align: left;
}
.demandes-table th {
    background: #1a237e;
    color: #fff;
    font-weight: 600;
    letter-spacing: 0.5px;
}
.demandes-table tr:nth-child(even) {
    background: #f1f5fa;
}
.demandes-table tr:hover {
    background: #e3eafc;
    transition: background 0.2s;
}
.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.95em;
    color: #fff;
    background: #43a047;
    display: inline-block;
}
.status-badge.attente { background: #fbc02d; }
.status-badge.refuse { background: #e53935; }
.status-badge.valide { background: #43a047; }
.action-btn {
    background: #1a237e;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 7px 18px;
    cursor: pointer;
    font-size: 1em;
    transition: background 0.2s;
}
.action-btn:hover {
    background: #3949ab;
} 
.custom-navbar {
    width: 100%;
    background: #1a237e;
    padding: 10px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 35px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

 
.navbar-title {
    color: #fff;
    font-size: 1.2em;   
    font-weight: 600;
    letter-spacing: 0.5px;
}
 
.navbar-right {
    display: flex;
    align-items: center;
    gap: 16px;
}

 
.user-info {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #fff;
    font-size: 0.95em;
}

 
.user-icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #fff;
    padding: 2px;
}

 
.logout-btn {
    background: #43a047;
    color: #fff;
    padding: 6px 14px;
    font-size: 0.9em;
    font-weight: 500;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    margin-right: 70px;  
    transition: background 0.2s, transform 0.2s;
    box-shadow: 0 2px 8px rgba(67, 160, 71, 0.1);
}

.logout-btn:hover, .logout-btn:active {
    background: #2e7d32;
    color: #fff;
    transform: scale(1.03);
}

    </style>
</head>
<body>
<div class="custom-navbar">
    <div class="navbar-left">
        <span class="navbar-title">Trust Education</span>
    </div>
    <div class="navbar-right">
        <div class="user-info">
            <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" alt="User Icon" class="user-icon">
            <span class="user-name">Dachboard Assistant</span>
        </div>
        <a href="pageaccueil.html" class="logout-btn">Déconnexion</a>
    </div>
</div>


<div class="dashboard-container">
    <h2>Demandes de contact psychologue</h2
    >
    <table class="demandes-table">
       <thead>
    <tr>
        <th>Étudiant</th>
        <th>Questions</th>
        <th>Date de création</th>
        <th>Supprimer</th>
    </tr>
</thead>
      
</php>

<tbody>
<?php if (empty($demandes)): ?>
    <tr><td colspan="4" style="text-align:center;">Aucune demande trouvée.</td></tr>
<?php else: ?>
    <?php foreach ($demandes as $demande): ?>
    <tr>
        <td><?= htmlspecialchars($demande['email']) ?></td>
        <td><?= nl2br(htmlspecialchars($demande['questions'])) ?></td>
        <td><?= isset($demande['date_creation']) ? date('d/m/Y H:i', strtotime($demande['date_creation'])) : '' ?></td>
        <td>
            <form method="post" action="delete_psy.php" onsubmit="return confirm('Voulez-vous vraiment supprimer cette demande ?');" style="margin:0;">
                <input type="hidden" name="iddp" value="<?= htmlspecialchars($demande['IDDP']) ?>">
                <button type="submit" class="action-btn" style="background:#e53935;">Supprimer</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
<?php endif; ?>
</tbody>
    </table>
    
</body>
</html>