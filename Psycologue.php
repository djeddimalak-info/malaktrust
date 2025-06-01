<?php
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = '127.0.0.1';
    $dbname = 'trusteducation';
    $username = 'root';
    $password_db = '';

    $email = $_POST['email'] ?? '';
    $mot_de_passe = $_POST['password'] ?? '';
    $message_psy = $_POST['message'] ?? '';

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password_db);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Vérifier que l'étudiant existe
        $stmt = $conn->prepare("SELECT * FROM etudiant WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if (!$stmt->fetch()) {
            $msg = "Aucun compte étudiant n'existe avec cet email.";
        } else {
            // Vérifier le mot de passe dans la table utilisateur
            $stmt2 = $conn->prepare("SELECT password FROM utilisateur WHERE email = :email");
            $stmt2->execute([':email' => $email]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);

            // Vérification mot de passe clair OU hashé
            if (!$row || ($mot_de_passe !== $row['password'] && !password_verify($mot_de_passe, $row['password']))) {
                $msg = "Mot de passe incorrect.";
            } else {
                // ?existe déjà
                $stmt3 = $conn->prepare("SELECT * FROM demande_psy WHERE email = :email");
                $stmt3->execute([':email' => $email]);
                if ($stmt3->fetch()) {
                    $msg = "Une demande existe déjà avec cet email.";
                } else {
                    
                    $stmt4 = $conn->prepare("INSERT INTO demande_psy (email, questions) VALUES (:email, :questions)");
                    $stmt4->execute([
                        ':email' => $email,
                        ':questions' => $message_psy
                    ]);
                    $msg = "Votre demande a bien été envoyée.";
                }
            }
        }
    } catch(PDOException $e) {
        $msg = "Erreur : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>  
<html lang="fr">  

<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Contactez un Psychologue</title>  
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

       <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>  
   body {  
    background-color: #ffffff;   
    color: #003366;   
    font-family: 'Georgia', serif;
    padding-top: 30px;
}  

.container {
    flex-grow: 1;
    padding: 10px;
    margin-top: 0;
}

h3 {
    color: #003366;
    margin-bottom: 30px;
    font-size: 28px;
    font-weight: bold;
    text-transform: uppercase;
    text-align: center;
}

.content-wrapper {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    flex-wrap: wrap;
}

.form-section {
    background-color: #f4faff;
    border: 1px solid #007BFF;
    border-radius: 10px;
    padding: 30px;
    max-width: 450px;
    width: 100%;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-control {
    border-radius: 20px;
    padding: 10px 16px;
    font-size: 15px;
    border: 1px solid #ccc;
    box-shadow: none;
    width: 100%;
}

.input-group {
    max-width: 100%;
}

.input-group-text {
    border: none;
    background-color: #e6ecf0;
    border-radius: 20px 0 0 20px;
    padding: 10px 14px;
}

.btn-primary {
    background-color: #004080;
    border: none;
    border-radius: 20px;
    padding: 12px;
    font-size: 16px;
    font-weight: bold;
}

.btn-primary:hover {
    background-color: #003366;
}

.animation-section {
    flex-shrink: 0;
    margin-left: 20px;
}

.animation-section img {
    width: 100px;
    height: auto;
    animation: fadeIn 1.5s ease-in;
}


@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* FOOTER — NE PAS MODIFIER */

.footer-section {
    background-color: #003366;   
    color: white;    
    padding: 20px;  
    width: 100%; 
    font-family: Arial, sans-serif;
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
 
 <?php if (!empty($msg)): ?>
        <div class="alert alert-info text-center"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <div class="container mt-5">  
        <h3 class="text-center">Demande de Contact</h3>  
        <br>  
        <div class="content-wrapper">  
            <div class="form-section">  
                <form method="post" action="">
    <div class="form-group">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            </div>
            <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
        </div>
    </div>

    <div class="form-group">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
            </div>
            <input type="password" class="form-control" id="password" name="password" placeholder="Mot de passe" required>
        </div>
    </div>

    <div class="form-group">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-comment-dots"></i></span>
            </div>
            <textarea class="form-control" id="message" name="message" rows="3" placeholder="Message" required></textarea>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Envoyer <i class="fas fa-paper-plane"></i></button>
</form>
            </div>  
             
            <div class="animation-section">  
                <img src="psyy.jpg" alt="Psychologie Animée" class="img-fluid" style="width: 300px; height: auto;">  
            </div>  
        </div>  
    </div>  
 
    <div class="footer-section">  
        <div class="footer-content">  
            <div class="footer-info">  
                <h5><span class="fa fa-info-circle"></span> À Propos de Nous</h5>  
                <hr>  
                <p>Nous sommes spécialisés dans l'accompagnement des étudiants pour leurs démarches d'études en Pologne et en Russie.</p>  
            </div>  
            <div class="footer-links">  
                <h5><span class="fa fa-link"></span> Liens Utiles</h5>  
                <hr>  
                <ul>  
                    <li><a href="https://www.instagram.com/votreprofil" target="_blank">Instagram <span class="fa fa-instagram"></span></a></li>  
                    <li><a href="https://wa.me/123456789" target="_blank">WhatsApp <span class="fa fa-whatsapp"></span></a></li>  
                    <li><a href="propos.html">À Propos</a></li>  
                </ul>  
            </div>  
            <div class="footer-contact">  
                <h5><span class="fa fa-envelope"></span> Contact</h5>  
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
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>  
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>  
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>  
       
</body>  

</html>