<?php  
// Connexion BDD  
$host='127.0.0.1'; $dbname='trusteducation'; $user='root'; $pass='';  
try { $pdo=new PDO("mysql:host=$host;dbname=$dbname;charset=utf8",$user,$pass); }  
catch (PDOException $e) { die("Erreur : ". $e->getMessage()); }  

// Vérifier si les paramètres sont passés  
if (isset($_GET['idd']) && isset($_GET['email'])) {  
    $idd = trim($_GET['idd']);  
    $email = trim($_GET['email']);  
    // Afficher le nombre de lignes pour cet IDD et cet email
    /*
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM demande WHERE IDD=:idd AND email=:email");
    $stmtCount->execute([':idd'=>$idd, ':email'=>$email]);
    $count = $stmtCount->fetchColumn();
    echo '<div style="background:#eef;border:1px solid #00c;padding:10px;margin:10px 0;">Nombre de lignes trouvées : '.(int)$count.'</div>';
    */
    // Récupérer la demande  
    $stmt = $pdo->prepare("SELECT * FROM demande WHERE IDD=:idd AND email=:email");  
    $stmt->execute([':idd'=>$idd, ':email'=>$email]);  
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);  
    /*
    echo '<pre style="background:#eee;border:1px solid #ccc;padding:10px;">';
    var_dump($demande);
    echo '</pre>';
    if (!$demande) {
      echo '<div style="background:#fee;border:1px solid #c00;padding:10px;margin:10px 0;">Aucune ligne trouvée pour IDD = '.htmlspecialchars($idd).' et email = '.htmlspecialchars($email).'</div>';
    }
    // Debug : afficher toutes les demandes pour comparer
    $all = $pdo->query("SELECT IDD, email FROM demande")->fetchAll(PDO::FETCH_ASSOC);
    echo '<div style="background:#f8f8ff;border:1px solid #888;padding:10px;margin:10px 0;">';
    echo '<b>Liste des demandes (IDD, email) :</b><ul>';
    foreach($all as $row) {
      echo '<li>IDD = '.htmlspecialchars($row['IDD']).' | email = '.htmlspecialchars($row['email']).'</li>';
    }
    echo '</ul></div>';
    */
} else {  
    $demande = null;  
}  

// Traitement de la suppression
if (isset($_POST['action']) && $_POST['action'] === 'supprimer' && isset($_POST['idd']) && isset($_POST['email'])) {
    $idd = trim($_POST['idd']);
    $email = trim($_POST['email']);
    $stmt = $pdo->prepare("DELETE FROM demande WHERE IDD = :idd AND email = :email");
    $stmt->execute([':idd' => $idd, ':email' => $email]);
    $message = ($stmt->rowCount() > 0)
        ? '<div class="delete-success-bg"><div class="delete-success-card">
                <div class="delete-success-icon"><i class="fas fa-check-circle"></i></div>
                <h2 class="delete-success-title">Suppression réussie</h2>
                <p class="delete-success-text">Votre demande a été <span>supprimée avec succès</span>.</p>
                <p class="delete-success-subtext">Merci de votre confiance.<br>Vous pouvez effectuer une nouvelle demande à tout moment.</p>
                <a href="pageaccueil.html" class="delete-success-btn"><i class="fas fa-home"></i> Retour à l\'accueil</a>
            </div></div>'
        : '<div class="delete-success-bg"><div class="delete-success-card">
                <div class="delete-error-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <h2 class="delete-error-title">Erreur de suppression</h2>
                <p class="delete-success-text">La demande n\'a pas pu être supprimée.<br><span>Elle a peut-être déjà été supprimée ou n\'existe pas.</span></p>
                <a href="pageaccueil.html" class="delete-success-btn"><i class="fas fa-home"></i> Retour à l\'accueil</a>
            </div></div>';
    echo '<style>
    .delete-success-bg {
        min-height: 100vh;
        width: 100vw;
        background: linear-gradient(120deg, #f5f7fa 0%, #c3cfe2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        margin: 0;
    }
    .delete-success-card {
        background: #fff;
        border-radius: 24px;
        box-shadow: 0 8px 32px 0 rgba(31,38,135,0.13);
        padding: 48px 36px 36px 36px;
        max-width: 400px;
        width: 95vw;
        text-align: center;
        position: relative;
        animation: fadeInUp 0.7s cubic-bezier(.39,.575,.56,1.000);
    }
    .delete-success-icon {
        color: #27ae60;
        font-size: 3.5em;
        margin-bottom: 18px;
        animation: pop 0.7s;
    }
    .delete-error-icon {
        color: #e74c3c;
        font-size: 3.5em;
        margin-bottom: 18px;
        animation: pop 0.7s;
    }
    .delete-success-title {
        color: #222;
        font-size: 2em;
        font-weight: 800;
        margin-bottom: 10px;
        letter-spacing: 1px;
    }
    .delete-error-title {
        color: #e74c3c;
        font-size: 2em;
        font-weight: 800;
        margin-bottom: 10px;
        letter-spacing: 1px;
    }
    .delete-success-text {
        font-size: 1.15em;
        color: #222;
        margin-bottom: 10px;
    }
    .delete-success-text span {
        color: #27ae60;
        font-weight: 600;
    }
    .delete-success-subtext {
        color: #888;
        font-size: 1em;
        margin-bottom: 24px;
    }
    .delete-success-btn {
        display: inline-block;
        background: linear-gradient(90deg, #001f3f 60%, #0074d9 100%);
        color: #fff;
        font-weight: 700;
        border: none;
        border-radius: 8px;
        padding: 12px 36px;
        font-size: 1.1em;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        transition: background 0.2s, box-shadow 0.2s;
    }
    .delete-success-btn:hover {
        background: linear-gradient(90deg, #0074d9 60%, #001f3f 100%);
        color: #fff;
        box-shadow: 0 4px 16px rgba(0,0,0,0.13);
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(40px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes pop {
        0% { transform: scale(0.7); }
        60% { transform: scale(1.15); }
        100% { transform: scale(1); }
    }
    </style>';
    echo $message;
    exit;
}
?>  
<!DOCTYPE html>  
<html lang="fr">  
<head>  
<meta charset="UTF-8" />  
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Détails de la demande</title>  
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    body {
      background: linear-gradient(120deg, #e9ecef 0%, #f8fafc 100%);
      min-height: 100vh;
    }
    .container {
      max-width: 750px;
      margin: 50px auto;
      padding: 0;
    }
    .card {
      border-radius: 18px;
      box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.12);
      border: none;
      overflow: hidden;
    }
    .card-header {
      background: linear-gradient(90deg, #001f3f 60%, #0074d9 100%);
      color: #fff;
      font-size: 1.5em;
      letter-spacing: 1px;
      border-bottom: none;
      padding: 30px 0 20px 0;
    }
    .list-group-item {
      font-size: 1.13em;
      background: #f8fafc;
      border: none;
      border-bottom: 1px solid #e3e3e3;
      display: flex;
      align-items: center;
      padding: 18px 24px;
      transition: background 0.2s;
    }
    .list-group-item:last-child {
      border-bottom: none;
    }
    .label {
      font-weight: 600;
      color: #001f3f;
      min-width: 160px;
      text-transform: capitalize;
      letter-spacing: 0.5px;
    }
    .list-group-item i {
      font-size: 1.2em;
      margin-right: 10px;
      color: #0074d9;
    }
    .btn-darkblue {
      background: #001f3f;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 10px 24px;
      font-weight: 600;
      transition: background 0.2s;
    }
    .btn-darkblue:hover {
      background: #003366;
      color: #fff;
    }
    .btn-danger, .btn-warning, .btn-success {
      font-weight: 600;
      border-radius: 6px;
      padding: 10px 24px;
      margin-right: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .btn-warning {
      background: #ffb300;
      color: #fff;
      border: none;
    }
    .btn-warning:hover {
      background: #ff9800;
      color: #fff;
    }
    .btn-success {
      background: #28a745;
      color: #fff;
      border: none;
    }
    .btn-success:hover {
      background: #218838;
      color: #fff;
    }
    .btn-danger {
      background: #dc3545;
      color: #fff;
      border: none;
    }
    .btn-danger:hover {
      background: #b71c1c;
      color: #fff;
    }
    @media (max-width: 600px) {
      .container { max-width: 98vw; }
      .label { min-width: 90px; font-size: 0.98em; }
      .list-group-item { padding: 12px 8px; font-size: 1em; }
      .card-header { font-size: 1.1em; padding: 18px 0 10px 0; }
    }
  </style>
</head>  
<body>  
  <div class="container">
    <?php if ($demande): ?>
    <div class="card">
      <div class="card-header text-center">
        <i class="fas fa-file-alt"></i> Détail de votre demande d'études
      </div>
      <ul class="list-group list-group-flush">
        <?php foreach ($demande as $key => $value): ?>
          <li class="list-group-item d-flex align-items-center">
            <?php
              // Icônes selon le champ
              $icons = [
                'IDD' => 'fa-hashtag',
                'email' => 'fa-envelope',
                'Object' => 'fa-book',
                'objet' => 'fa-book',
                'Message' => 'fa-comment',
                'universite' => 'fa-university',
                'special_ite' => 'fa-graduation-cap',
                'niveau' => 'fa-layer-group',
                'date_creation' => 'fa-calendar-alt',
              ];
              $icon = isset($icons[$key]) ? $icons[$key] : 'fa-info-circle';
            ?>
            <span class="label mr-2"><i class="fas <?php echo $icon; ?>"></i></span>
            <span class="label mr-2"><?php echo htmlspecialchars($key); ?> :</span>
            <span><?php echo nl2br(htmlspecialchars($value)); ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <form method="post" class="text-center mt-4">
      <input type="hidden" name="idd" value="<?php echo htmlspecialchars($demande['IDD']); ?>">
      <input type="hidden" name="email" value="<?php echo htmlspecialchars($demande['email']); ?>">
       <button type="submit" name="action" value="supprimer" class="btn btn-danger mr-2 mb-2"><i class="fas fa-trash-alt"></i> SUPPRIMER</button>
      <button type="button" class="btn btn-success mb-2" onclick="window.location.href='créerdemande.php';"><i class="fas fa-check"></i> CONFIRMER</button>
      <a href="pageaccueil.html" class="btn btn-darkblue mb-2"><i class="fas fa-home"></i> Retour à l'accueil</a>
    </form>
    <?php else: ?>
      <div class="alert alert-danger text-center">Aucune demande trouvée ou paramètres manquants.</div>
      <div class="text-center mt-4">
        <a href="consulterdemande.php" class="btn btn-darkblue"><i class="fas fa-arrow-left"></i> Retour</a>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>