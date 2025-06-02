<?php  
$host='127.0.0.1'; 
$dbname='trusteducation'; 
$user='root'; 
$pass='';  

try { 
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass); 
} catch (PDOException $e) { 
    die("Erreur : " . $e->getMessage()); 
}  

if (isset($_GET['idd']) && isset($_GET['email'])) {  
    $idd = trim($_GET['idd']);  
    $email = trim($_GET['email']);  
    
    $stmt = $pdo->prepare("SELECT * FROM demande WHERE IDD = :idd AND email = :email");  
    $stmt->execute([':idd' => $idd, ':email' => $email]);  
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);  
} else {  
    $demande = null;  
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
        <?php
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
            'password' => 'fa-key',
          ];
        ?>
        <?php foreach ($demande as $key => $value): ?>
          <?php if ($key !== 'password'): ?>
          <li class="list-group-item d-flex align-items-center">
            <?php
              $icon = isset($icons[$key]) ? $icons[$key] : 'fa-info-circle';
            ?>
            <span class="label mr-2"><i class="fas <?php echo $icon; ?>"></i></span>
            <span class="label mr-2"><?php echo htmlspecialchars($key); ?> :</span>
            <span><?php echo nl2br(htmlspecialchars($value)); ?></span>
          </li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </div>
    <form method="post" class="text-center mt-4">
      <input type="hidden" name="idd" value="<?php echo htmlspecialchars($demande['IDD']); ?>">
      <input type="hidden" name="email" value="<?php echo htmlspecialchars($demande['email']); ?>">
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
