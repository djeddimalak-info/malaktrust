<?php
 
$host = '127.0.0.1';
$dbname = 'trusteducation';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $idd = $_POST['idd'] ?? '';
    $email = $_POST['email'] ?? '';
    $user_password = $_POST['password'] ?? '';
    $objet = $_POST['objet'] ?? '';
    $msg = $_POST['message'] ?? '';
    $universites = [];
    $specialites = [];
    $niveaux = [];
    $i = 1; // pls lignes
    while (isset($_POST["universite-$i"])) {
        $universites[] = $_POST["universite-$i"];
        $specialites[] = $_POST["specialite-$i"];
        $niveaux[] = $_POST["niveau-$i"];
        $i++;
    }

     
    if (empty($idd) || empty($email) || empty($user_password) || empty($objet) || empty($msg) || empty($universites)) {
        $message = "Veuillez remplir tous les champs obligatoires."; /// test avec empty
    } else {
    
        $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE email = :email"); // chercher dans uti
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            
            $dbPassword = $user['password'];
            if ((strlen($dbPassword) > 30 && strpos($dbPassword, '$2y$') === 0) || strpos($dbPassword, '$argon2') === 0) {
                $isValid = password_verify($user_password, $dbPassword);
            } else {
                $isValid = ($user_password === $dbPassword);
            }

            if ($isValid) {
                //  si l'IDD  dans  demande
                $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM demande WHERE IDD = :idd"); // si est trouvé
                $stmtCheck->execute([':idd' => $idd]);
                $iddExists = $stmtCheck->fetchColumn() > 0;
                if ($iddExists) {
                    $message = "Cet identifiant de demande existe déjà. Veuillez en choisir un autre.";
                } else {
                      // sinon on stocke
                    $stmt2 = $conn->prepare("INSERT INTO demande (IDD, email, Objet, Message, universite, specialite, niveau, date_creation) VALUES (:idd, :email, :objet, :message, :universite, :specialite, :niveau, NOW())");
                    foreach ($universites as $index => $universite) {
                        $stmt2->execute([
                            ':idd' => $idd,
                            ':email' => $email,
                            ':objet' => $objet,
                            ':message' => $msg,
                            ':universite' => $universite,
                            ':specialite' => $specialites[$index] ?? '',
                            ':niveau' => $niveaux[$index] ?? ''
                        ]);
                    }
                    $message = "Votre demande a été enregistrée avec succès.";
                }
            } else {
                $message = "Email ou mot de passe incorrect.";
            }
        } else {
            $message = "Email ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>  
<html lang="fr">  
<head>  
  <meta charset="UTF-8">  
  <meta name="viewport" content="width=device-width, initial-scale=1.0">  
  <title>Formulaire de Demande</title>  
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">  
  <style>  
   body {  
  margin: 0;  
  padding: 0;  
  background-color: #e9ecef;  
  font-family: Arial, sans-serif;  
  display: flex;  
  flex-direction: column;  
  min-height: 100vh;  
}  

.container {
  max-width: 100vw;
  margin: 0;
  background: none;
  padding: 0;
  box-shadow: none;
  border-radius: 0;
  display: flex;
  justify-content: center;
  flex-grow: 1;
  position: relative;
}
    .form-content {  
      width: 100%;  
      background: #fff;
      padding: 30px;  
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);  
      border-radius: 10px;  
      margin: 50px auto;  
      max-width: 1000px;  
    }  
    .image-container {  
      display: none;  
    }  
    .intro {  
      text-align: center;  
      margin-bottom: 20px;  
      color: #333;  
      font-size: 1.2em;  
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
    .btn-circle {  
      width: 40px;  
      height: 40px;  
      border-radius: 50%;  
      color: white;  
      border: none;  
      display: flex;  
      justify-content: center;  
      align-items: center;  
      font-size: 1.5rem;  
      transition: background-color 0.3s, transform 0.2s;  
    }  
    .btn-circle-green {  
      background-color: #28a745;  
    }  
    .btn-circle-green:hover {  
      background-color: #218838;  
      transform: scale(1.1);  
    }  
    .btn-circle-red {  
      background-color: #dc3545;  
    }  
    .btn-circle-red:hover {  
      background-color: #c82333;  
      transform: scale(1.1);  
    }  
    .fields-row {  
      display: flex;  
      align-items: center;  
      gap: 10px;  
    }  
     
    footer:hover {  
      background-color: #002244; /* Couleur plus foncée au survol */  
    }  
    footer h5 {  
      margin-top: 0;  
    }  
    
      
    .social-icons i {  
      margin: 0 10px;  
      transition:0.3s;  
    }  
    .social-icons i:hover {  
      transform: translateY(-5px); /* Animation au survol des icônes */  
    }  
    .social-icons {  
      margin-top: 10px;  
    }  
    .footer-section {  
        background-color: #003366;  
        color: white;  
        padding: 20px;  
        margin-top: 30px;
        font-family: Arial, Helvetica, sans-serif;  
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
        .navbar {
            background-color: #f8f9fa;
            width: 100vw;
            min-width: 100vw;
            max-width: 100vw;
            height: 70px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            padding: 0 !important;
            border-bottom: 1px solid #e5e5e5;
            box-sizing: border-box;
            margin: 0 !important;
            overflow-x: hidden;
        }
        .navbar .container {
            max-width: 100vw;
            width: 100vw;
            padding: 0 !important;
            margin: 0 !important;
        }
        .navbar-brand img {
            width: 40px;
            height: auto;
            margin-right: 10px;
        }
        .navbar-nav .nav-link {
            color: #00008B;
            margin-right: 5px;
            font-weight: bold;
            font-size: 0.9em;
            padding: 8px 12px;
            border-radius: 0;
            transition: none;
        }
        .navbar-nav .nav-link:hover, .navbar-nav .nav-link.active {
            background: none !important;
            color: #002244 !important;
        }
        .navbar-nav .dropdown-menu {
            border-radius: 0;
            border: none;
            box-shadow: none;
            color: #00008B;
            font-weight: normal;
        }
        .navbar-nav .dropdown-item {
            color: #00008B;
            font-weight: normal;
            transition: background 0.2s, color 0.2s;
        }
        .navbar-nav .dropdown-item:hover {
            background: #eaf3fa;
            color: #002244;
        }
        .navbar-toggler {
            border: none;
        }
        .navbar-toggler:focus {
            outline: none;
            box-shadow: 0 0 0 2px #0D3B6633;
        }
        .navbar-text {
            color: rgb(5, 39, 50);
            font-size: 1.3em;
            font-weight: normal;
        }
        body {
            padding-top: 70px !important;
            margin: 0 !important;
            box-sizing: border-box;
            overflow-x: hidden;
        }
  </style>  
</head>  
<body>
 
 
<div style="padding-top:10px;"></div>
<div class="text-center my-4">
  <button class="btn btn-darkblue btn-lg" style="font-size:1.0em;padding:14px 36px;box-shadow:0 2px 8px #001f3f33;letter-spacing:0.5px;border-radius:10px;" onclick="window.location.href='consulterdemande.php'">
    <i class="fas fa-search"></i> Consulter une demande
  </button>
</div>
<div class="container">  
    <div class="form-content">  
      <p class="intro"><strong>Remplissez ce formulaire pour faciliter le traitement de votre demande.</strong></p>  
     
      <form method="post" action="créerdemande.php">
    <div class="form-group">
        <label for="IDD">Identifiant de Demande</label>
        <input type="text" class="form-control" id="IDD" name="idd" placeholder="Identifiant de Demande" required>
    </div>
    <div class="form-group">
        <label for="email">Adresse Email</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Votre email" required>
    </div>
    <div class="form-group">
        <label for="password">Mot de Passe</label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Votre mot de passe" required>
    </div>
    <div class="form-group">
        <label for="objet">Objet de la demande</label>
        <select class="form-control" id="objet" name="objet" required>
            <option value="">Sélectionnez un objet</option>
            <option value="inscription">Inscription</option>
            <option value="information">Demande d'information</option>
        </select>
    </div>
    <div class="form-group">
        <label for="message">Message</label>
        <textarea class="form-control" id="message" name="message" rows="4" placeholder="Votre demande..." required></textarea>
    </div>
    <div id="dynamic-fields">
        <div class="fields-row" id="row-1">
            <div class="form-group flex-grow-1">
                <label for="universite-1">Université</label>
                <input type="text" class="form-control" id="universite-1" name="universite-1" placeholder="Nom de l'université" required>
            </div>
            <div class="form-group flex-grow-1">
                <label for="specialite-1">Spécialité</label>
                <input type="text" class="form-control" id="specialite-1" name="specialite-1" placeholder="Spécialité" required>
            </div>
            <div class="form-group flex-grow-1">
                <label for="niveau-1">Niveau</label>
                <input type="text" class="form-control" id="niveau-1" name="niveau-1" placeholder="Votre niveau" required>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-darkblue btn-block mt-3">Envoyer</button>
</form>
<?php if (!empty($message)): ?>
    <div style="color:<?= strpos($message, 'succès') !== false ? 'green' : 'red' ?>;text-align:center;margin-bottom:10px;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>
    </div>  

    <div class="image-container">  
      <img src="localisation1.png" alt="Illustration localisation">  
      <img src="localisation2.png" alt="Illustration localisation">  
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

  let count = 2;

  
  document.addEventListener('submit', (event) => {
      const allRows = document.querySelectorAll('.fields-row');
      allRows.forEach((row) => {
          const inputs = row.querySelectorAll('input');
          let filledCount = 0;

          inputs.forEach((input) => {
              if (input.value.trim()) filledCount++;
          });

          if (filledCount > 0 && filledCount < inputs.length) {
              event.preventDefault();
              alert("Si vous remplissez un champ, vous devez compléter les autres champs de la même ligne.");
              inputs.forEach((input) => {
                  if (!input.value.trim()) input.focus();
              });
          }
      });
  });
</script>
</body>  
</html>