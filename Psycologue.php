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
                // Vérifier si une demande psy existe déjà
                $stmt3 = $conn->prepare("SELECT * FROM demande_psy WHERE email = :email");
                $stmt3->execute([':email' => $email]);
                if ($stmt3->fetch()) {
                    $msg = "Une demande existe déjà avec cet email.";
                } else {
                    // Insérer la demande (questions = message)
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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">  
    <style>  
        body {  
            background-color: #ffffff; /* Fond clair */  
            color: #003366; /* Texte bleu foncé */  
            font-family: 'Georgia', serif;
            padding-top: 80px;
        }  
        .navbar {
            background-color: #f8f9fa;
            width: 100%;
            padding-left: 10px;
            padding-right: 10px;
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
        }
        .container { flex-grow: 1; padding: 20px; margin-top: 20px; }
        .flag { width: 20px; height: auto; margin-left: 8px; vertical-align: middle; }
        .footer-section { width: 100vw; position: relative; left: 50%; right: 50%; margin-left: -50vw; margin-right: -50vw; background-color: #003366; color: white; padding: 20px 0; flex-shrink: 0; }
        .footer-content { display: flex; justify-content: space-between; flex-wrap: wrap; padding: 20px 0; }
        .footer-info, .footer-links, .footer-contact { flex: 1; min-width: 200px; margin: 10px; }
        .footer-links ul { list-style-type: none; padding: 0; }
        .footer-links a { color: white; text-decoration: none; }
        .footer-links a:hover { text-decoration: underline; }
        .footer-bottom { text-align: center; margin-top: 20px; }
        hr { border: 1px solid white; margin: 10px 0; }

        h3 {  
            color: #003366;  
            margin-bottom: 20px; /* Réduit l'espace en bas du titre */  
        }  

        .form-section {  
            background-color: #eaf4fa; /* Fond blanc pour la section de formulaire */  
            border-radius: 10px;   
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);  
            padding: 30px; /* Réduction du padding */  
            width: 550px;  
            margin: auto; /* Centrer le formulaire */  
            animation: fadeIn 0.5s ease-in-out;  
        }  

        .content-wrapper {  
            display: flex; /* Ajoute un conteneur flexible */  
            justify-content: space-between;  
            align-items: center;  
        }  

        .animation-section {  
            flex-shrink: 0; /* Assure que l'animation ne se réduise pas */  
            margin-left: 20px;  
        }  

        @keyframes fadeIn {  
            from {  
                opacity: 0;  
            }  

            to {  
                opacity: 1;  
            }  
        }  

        .form-control {  
            border-radius: 25px; /* Coins arrondis pour les champs de formulaire */  
        }  

        .btn-primary {  
            background-color: #004080; /* Bleu foncé */  
            border: none;  
            border-radius: 25px; /* Coins arrondis pour le bouton */  
        }  

        .btn-primary:hover {  
            background-color: #003366;  
        }  

        .input-group-text {  
            border: none; /* Suppression de la bordure */  
            background-color: #f8f9fa; /* Couleur de fond */  
            border-radius: 25px 0 0 25px; /* Coins arrondis */  
        }  
    </style>  
</head>  
<body>  
<nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="your-logo.png" alt="Logo" />
        </a>
        <span style="color:rgb(5, 39, 50); font-size: 1.3em;" class="navbar-text">TrustEducation</span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="pageaccueil.html">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="propos.html">À propos</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUniversities" role="button"
                       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Universités
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownUniversities">
                        <a class="dropdown-item" href="pologne.html">Université polonaise</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="russie.html">Université russe</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownLanguages" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Langues
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownLanguages">
                        <a class="dropdown-item" href="applicationslangues.html">Applications de langues</a>
                        <a class="dropdown-item" href="ecoleslangues.html">Écoles de langues</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownLanguages" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Payes
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownLanguages">
                        <a class="dropdown-item" href="Payes.html">Russie</a>
                        <a class="dropdown-item" href="payes1.html">Pologne</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Connexion
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="creercompteAA.html">Assistant</a>
                        <a class="dropdown-item" href="creercompte.html">Étudiant</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Dashoard
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="login.php?redirect=dashbord.php"> Dashoard</a>
                        <a class="dropdown-item" href="login.php?redirect=DashboardTraduction.php"> Dashoard Traduction</a>
                        <a class="dropdown-item" href="login.php?redirect=dashboardpsy.php"> Dashoard Psychologue</a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>
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
            <!-- Animation Section -->  
            <div class="animation-section">  
                <img src="OIP4.jpg" alt="Psychologie Animée" class="img-fluid" style="width: 300px; height: auto;">  
            </div>  
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

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>  
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>  
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>  
    <script>   
        document.getElementById('current-year').textContent = new Date().getFullYear();  
    </script>  
</body>  

</html>