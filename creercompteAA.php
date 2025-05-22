<?php
// Connexion à la base de données
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
    // Récupérer les données du formulaire
    $email = $_POST['email'] ?? '';
    $motdepasse = $_POST['password'] ?? '';
    $numero_de_telephone = $_POST['phone1'] ?? '';
    $prenom = $_POST['firstname'] ?? '';
    $nom = $_POST['lastname'] ?? '';
    $date_naissance = $_POST['birthdate'] ?? '';
    $nationalite = $_POST['nationalite'] ?? '';
 

    // Vérifier que tous les champs obligatoires sont remplis
    if (empty($email) || empty($motdepasse) || empty($numero_de_telephone) || empty($prenom) || empty($nom) || empty($date_naissance)) {
        $message = "Veuillez remplir tous les champs obligatoires.";
    } else {
        // Vérifier si l'utilisateur existe déjà
        $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $message = "Un compte avec cet email existe déjà.";
        } else {
            // Hacher le mot de passe
            $hash = password_hash($motdepasse, PASSWORD_DEFAULT);

            // Insérer dans la table utilisateur
            $stmt = $conn->prepare("INSERT INTO utilisateur (email, numero_de_telephone, password, nationalite, date_naissance, sexe, nom, prenom) VALUES (:email, :numero_de_telephone, :password, :nationalite, :date_naissance, :sexe, :nom, :prenom)");
            $stmt->execute([
                ':email' => $email,
                ':numero_de_telephone' => $numero_de_telephone,
                ':password' => $hash,
                ':nationalite' => $nationalite,
                ':date_naissance' => $date_naissance,
                ':sexe' => $sexe,
                ':nom' => $nom,
                ':prenom' => $prenom
            ]);

            // Insérer dans la table assistantagence en liant par email
            $stmt2 = $conn->prepare("INSERT INTO assistantagence (email) VALUES (:email)");
            $stmt2->execute([':email' => $email]);

            $message = "Compte assistant créé avec succès!.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Accès sécurisé</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
            margin: 0;
            background:  white;
        }
        .container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            margin-top: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            animation: fadeIn 1.5s ease-in;
            flex-grow: 1;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .welcome-message {
            margin-bottom: 20px;
            text-align: center;
            color: #093c70;
            font-size: 1.5rem;
        }
        .container input, .container select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: box-shadow 0.3s;
        }
        .container input:focus, .container select:focus {
            box-shadow: 0 0 8px #093c70;
        }
        .input-icon {
            position: relative;
        }
        .input-icon i {
            position: absolute;
            left:10px;
            top: 50%;
            transform: translateY(-50%);
            color: #1e88e5;
            transition: transform 0.3s, color 0.3s;
        }
        .container button {
            width: 100%;
            padding: 10px;
            background-color: #093c70;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }
        .container button:hover {
            background-color: #002244;
            transform: scale(1.05);
        }
        .footer-section {  
        background-color: #003366; /* Bleu foncé */  
        color: white; /* Texte blanc */  
        padding: 20px;  
        margin-top: 30px;
        font-family: Arial, Helvetica, sans-serif; /* Espace entre le contenu et le footer */  
    }  

    .footer-content {  
        display: flex; /* Utilisation d'un flexbox pour le contenu */  
        justify-content: space-between;  
        flex-wrap: wrap; /* Permet de passer à la ligne si l'espace est insuffisant */  
        padding: 20px 0;  
    }  

    .footer-info,  
    .footer-links,  
    .footer-contact {  
        flex: 1; /* Équitablement répartir l'espace */  
        min-width: 200px; /* Largeur minimale */  
        margin: 10px; /* Espacement autour de chaque section */  
    }  

    .footer-links ul {  
        list-style-type: none; /* Suppression des puces */  
        padding: 0; /* Suppression du padding */  
    }  

    .footer-links a {  
        color: white; /* Couleur des liens */  
        text-decoration: none; /* Pas de soulignement */  
    }  

    .footer-links a:hover {  
        text-decoration: underline; /* Soulignement lors du survol */  
    }  

    .footer-bottom {  
        text-align: center; /* Centre le texte */  
        margin-top: 20px; /* Espace au-dessus */  
    }  

    hr {  
        border: 1px solid white; /* Couleur et épaisseur de la ligne */  
        margin: 10px 0; /* Espacement autour de la ligne */  
    } 
    </style>
</head>
<body>
 
<div style="padding-top:120px;"></div>
<div class="container">
    <h2 class="welcome-message"> Accès sécurisé</h2>
     <?php if (!empty($message)): ?>
    <div style="color:<?= strpos($message, 'succès') !== false ? 'green' : 'red' ?>;text-align:center;margin-bottom:10px;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>
    <form action="" method="post">
    <div class="input-icon">
        <i class="fas fa-paper-plane"></i>
        <input type="email" name="email" placeholder="      E-mail" required>
    </div>
    <div class="input-icon">
        <i class="fas fa-user-circle"></i>
        <input type="text" name="firstname" placeholder="      Prénom Assistant" required>
    </div>
    <div class="input-icon">
        <i class="fas fa-user-tie"></i>
        <input type="text" name="lastname" placeholder="      Nom Assistant" required>
    </div>
    <div class="input-icon">
        <input type="date" name="birthdate" placeholder="     Date de naissance Assistant" required>
    </div>
    <div class="input-icon">
        <i class="fas fa-unlock-alt"></i>
        <input type="password" name="password" placeholder="     Mot de passe" required>
    </div>
    <div class="input-icon">
        <i class="fas fa-phone"></i>
        <input type="tel" name="phone1" placeholder="       Numéro de téléphone" required>
    </div>
    <!-- Ajoute nationalite et sexe si tu veux -->
    <button type="submit">Créer le compte</button>
</form>
</div>
<br>
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
    document.getElementById('currentYear').textContent = new Date().getFullYear();
</script>
</body>
</body>
</html>