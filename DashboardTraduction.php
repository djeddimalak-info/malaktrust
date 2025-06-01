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

    // Réc fichiers et la date
    $stmt = $conn->query("SELECT d.IDT, d.email, d.date_creation, f.IDF, f.nom_fichier, f.langue, f.type_fichier FROM demandetraduction d LEFT JOIN fichiertraduction f ON d.IDT = f.IDT ORDER BY d.IDT DESC");
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $grouped = [];
    foreach ($demandes as $row) {
        $idt = $row['IDT'];
        if (!isset($grouped[$idt])) {
            $grouped[$idt] = [
                'email' => $row['email'],
                'date_creation' => $row['date_creation'],
                'fichiers' => []
            ];
        }
        if ($row['nom_fichier']) {
            $grouped[$idt]['fichiers'][] = [
                'IDF' => isset($row['IDF']) ? $row['IDF'] : null,
                'nom_fichier' => $row['nom_fichier'],
                'langue' => $row['langue'],
                'type_fichier' => $row['type_fichier']
            ];
        }
    }
     
    $langueLabels = [
        'fr' => 'Français',
        'en' => 'Anglais',
        'pl' => 'Polonais',
        'al' => 'Allemand',
        'es' => 'Espagnol',
        'ru' => 'Russe'
    ];

    // Supp auto des demandes sans fichie 
    foreach ($grouped as $idt => $demande) {
        if (empty($demande['fichiers'])) {
            // Supp  demande  
            $stmtDel = $conn->prepare("DELETE FROM demandetraduction WHERE IDT = ?");
            $stmtDel->execute([$idt]);
            unset($grouped[$idt]);
        }
    }
} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// Supp fichier trad
if (isset($_POST['delete_file'], $_POST['IDT'], $_POST['IDF'])) {
    $idt = $_POST['IDT'];
    $idf = $_POST['IDF'];
    // Supp fichier 
    $stmtDel = $conn->prepare("DELETE FROM fichiertraduction WHERE IDT = ? AND IDF = ?");
    $stmtDel->execute([$idt, $idf]);
    // Opt supprimer le fichier physique du dossier uploads si besoin
    
    header("Location: DashboardTraduction.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard des demandes de traduction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
h3 {
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

/* Icône utilisateur */
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
            <span class="user-name">Dashboard Traduction</span>
        </div>
        <a href="pageaccueil.html" class="logout-btn">Déconnexion</a>
    </div>
</div>

<div class="dashboard-container">
    <h3>Demandes de Traduction</h3>
    <table class="demandes-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Date de creation</th>
                <th>Nom du fichier</th>
                <th>Langue</th>
                <th>Type</th>
                <th>Téléchargement</th>
            </tr>
        </thead>
        <tbody>
<?php if (empty($grouped)): ?>
    <tr><td colspan="7" style="text-align:center;">Aucune demande trouvée.</td></tr>
<?php else: ?>
    <?php foreach ($grouped as $idt => $demande): ?>
        <?php if (!empty($demande['fichiers'])): ?>
            <?php foreach ($demande['fichiers'] as $fichier): ?>
                <tr>
                    <td><?= htmlspecialchars($idt) ?></td>
                    <td><?= htmlspecialchars($demande['email']) ?></td>
                    <td><?= isset($demande['date_creation']) ? date('d/m/Y H:i', strtotime($demande['date_creation'])) : '' ?></td>
                    <td><?= htmlspecialchars($fichier['nom_fichier']) ?></td>
                    <td>
                        <?php 
                            $code = trim($fichier['langue']);
                            $label = isset($langueLabels[$code]) ? $langueLabels[$code] : ($code ? $code : null);
                        ?>
                        <?php if ($label): ?>
                            <span style="color:#0D3B66;font-weight:bold;">
                                <?= htmlspecialchars($label) ?>
                            </span>
                        <?php else: ?>
                            <span style="color:red;">Langue non renseignée</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($fichier['type_fichier']) ?></td>
                    <td>
                        <a href="uploads/<?= urlencode($fichier['nom_fichier']) ?>" target="_blank">Télécharger</a>
                        
                        <form method="post" style="display:inline;" onsubmit="return confirm('Voulez-vous vraiment supprimer ce fichier ?');">
                            <input type="hidden" name="delete_file" value="1">
                            <input type="hidden" name="IDT" value="<?= htmlspecialchars($idt) ?>">
                            <input type="hidden" name="IDF" value="<?= htmlspecialchars(isset($fichier['IDF']) ? $fichier['IDF'] : '') ?>">
                            <button type="submit" class="action-btn" style="background:#e53935; padding:4px 10px; font-size:0.85em;">
                                <i class="fas fa-trash-alt"></i> Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td><?= htmlspecialchars($idt) ?></td>
                <td><?= htmlspecialchars($demande['email']) ?></td>
                <td><?= isset($demande['date_creation']) ? date('d/m/Y H:i', strtotime($demande['date_creation'])) : '' ?></td>
                <td colspan="4">Aucun fichier</td>
            </tr>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
</tbody>
    </table>
</div>
</body>
</html>