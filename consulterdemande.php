<?php
// Connexion à la BDD
$host='127.0.0.1'; $dbname='trusteducation'; $user='root'; $pass='';
try { $pdo=new PDO("mysql:host=$host;dbname=$dbname;charset=utf8",$user,$pass); }
catch(PDOException $e) { die("Erreur : " . $e->getMessage()); }

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $idd = trim($_POST['identifiant']);
  $email = trim($_POST['email']);
  $password = trim($_POST['motdepasse']);

  // 1. Vérifier que l'email existe dans utilisateur et que le mot de passe est correct
  $stmtUser = $pdo->prepare("SELECT * FROM utilisateur WHERE email = :email");
  $stmtUser->execute([':email' => $email]);
  $utilisateur = $stmtUser->fetch(PDO::FETCH_ASSOC);
  $isValid = false;
  if ($utilisateur) {
    if ((strlen($utilisateur['password']) > 30 && strpos($utilisateur['password'], '$2y$') === 0) || strpos($utilisateur['password'], '$argon2') === 0) {
      $isValid = password_verify($password, $utilisateur['password']);
    } else {
      $isValid = $password === $utilisateur['password'];
    }
  }
  if (!$utilisateur || !$isValid) {
    $message = "Email ou mot de passe incorrect.";
  } else {
    // 2. Vérifier que l'email existe dans etudiant
    $stmtEtudiant = $pdo->prepare("SELECT * FROM etudiant WHERE email = :email");
    $stmtEtudiant->execute([':email' => $email]);
    $etudiant = $stmtEtudiant->fetch(PDO::FETCH_ASSOC);
    if (!$etudiant) {
      $message = "Aucun étudiant trouvé avec cet email.";
    } else {
      // 3. Vérifier qu'il existe une demande avec cet IDD et cet email
      $stmtDemande = $pdo->prepare("SELECT * FROM demande WHERE IDD = :idd AND email = :email");
      $stmtDemande->execute([':idd' => $idd, ':email' => $email]);
      $demande = $stmtDemande->fetch(PDO::FETCH_ASSOC);
      if (!$demande) {
        $message = "Aucune demande trouvée avec cet identifiant et cet email.";
      } else {
        // 4. Rediriger vers demande.php avec les infos de la demande
        // Correction : forcer les clés en minuscules pour l'URL
        $params = http_build_query([
          'idd' => $demande['IDD'],
          'email' => $demande['email']
        ]);
        header("Location: demande.php?" . $params);
        exit;
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Consultation de demande d'études</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <style>
    html, body {
      height: 100%;
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #e9ecef;
    }
    .container {
      max-width: 700px;
      margin: auto;
      background-color: #fff;
      padding: 40px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      border-radius: 10px;
    }
    .wrapper {
      display: flex;
      flex-direction: column;
      justify-content: center;
      flex: 1;
      padding: 20px;
    }
    .intro {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
      font-size: 1.3em;
    }
    label {
      font-weight: bold;
      color: #001f3f;
    }
    .form-control {
      border-radius: 0.25rem;
    }
    .btn-darkblue {
      background-color: #001f3f;
      color: #fff;
      border: none;
    }
    .btn-darkblue:hover {
      background-color: #001530;
    }
    .input-group-text {
      background-color: #001f3f;
      color: #fff;
    }
    .footer-section {
        background-color: #003366;
        color: white;
        padding: 20px;
        margin-top: 30px;
    }
    .footer-content {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        padding: 20px 0;
    }
    .footer-info,
    .footer-links,
    .footer-contact {
        flex: 1;
        min-width: 200px;
        margin: 10px;
    }
    .footer-links ul {
        list-style-type: none;
        padding: 0;
    }
    .footer-links a {
        color: white;
        text-decoration: none;
    }
    .footer-links a:hover {
        text-decoration: underline;
    }
    .footer-bottom {
        text-align: center;
        margin-top: 20px;
    }
    hr {
        border: 1px solid white;
        margin: 10px 0;
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="container">
      <p class="intro"><strong><i class="fas fa-search"></i> Consultez votre demande d'études</strong></p>
      <form action="" method="POST">
        <div class="form-group">
          <label for="identifiant"><i class="fas fa-id-badge"></i> Identifiant de Demande</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-key"></i></span>
            </div>
            <input type="text" class="form-control" id="identifiant" name="identifiant" placeholder="Entrez l'identifiant de la demande" required>
          </div>
        </div>
        <div class="form-group">
          <label for="email"><i class="fas fa-envelope"></i> Adresse Email</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            </div>
            <input type="email" class="form-control" id="email" name="email" placeholder="Entrez votre email" required>
          </div>
        </div>
        <div class="form-group">
          <label for="motdepasse"><i class="fas fa-lock"></i> Mot de Passe</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-lock"></i></span>
            </div>
            <input type="password" class="form-control" id="motdepasse" name="motdepasse" placeholder="Entrez votre mot de passe" required>
          </div>
        </div>
        <button type="submit" class="btn btn-darkblue btn-block mt-3">
          <i class="fas fa-search"></i> Consulter
        </button>
      </form>
      <?php if ($message): ?>
        <div class="alert alert-info mt-3"><?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>
    </div>
  </div>
  <div class="footer-section">
    <div class="footer-content">
        <div class="footer-info">
            <h5><i class="fas fa-info-circle"></i> À Propos de Nous</h5>
            <hr>
            <p>Nous sommes spécialisés dans l'accompagnement des étudiants pour leurs démarches d'études en Pologne et en Russie.</p>
        </div>
        <div class="footer-links">
            <h5><i class="fas fa-link"></i> Liens Utiles</h5>
            <hr>
            <ul>
                <li><a href="https://www.instagram.com/votreprofil" target="_blank">  Instagram <i class="fab fa-instagram"></i></a></li>
                <li><a href="https://wa.me/123456789" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a></li>
                <li><a href="propos.html">À Propos</a></li>
            </ul>
        </div>
        <div class="footer-contact">
            <h5><i class="fas fa-envelope"></i> Contact</h5>
            <hr>
            <p>Email: <a href="mailto:contact@votreorganisation.com">contact@votreorganisation.com</a></p>
            <p>Téléphone: <a href="tel:+123456789">+33 1 23 45 67 89</a></p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <span id="current-year">2025</span> Trust Education. Tous droits réservés.</p>
    </div>
  </div>
  <script>
    document.getElementById('current-year').textContent = new Date().getFullYear();
  </script>
</body>
</html>