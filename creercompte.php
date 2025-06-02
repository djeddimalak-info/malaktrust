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
    $email = $_POST['email'] ?? '';
    $motdepasse = $_POST['password'] ?? '';
    $numero_de_telephone = $_POST['phone1'] ?? '';
    $prenom = $_POST['firstname'] ?? '';
    $nom = $_POST['lastname'] ?? '';
    $date_naissance = $_POST['birthdate'] ?? '';

    if (empty($email) || empty($motdepasse) || empty($numero_de_telephone) || empty($prenom) || empty($nom) || empty($date_naissance)) {
        $message = "Veuillez remplir tous les champs obligatoires.";
    } elseif (!preg_match('/^0[675][0-9]{8}$/', $numero_de_telephone)) {
        $message = "Le numéro de téléphone doit commencer par 05, 06 ou 07 et contenir 9 chiffres au total.";
    } elseif (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d).+$/', $motdepasse)) {
        $message = "Le mot de passe doit contenir au moins une lettre et un chiffre.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $message = "Un compte avec cet email existe déjà.";
        } else {
            $hash = password_hash($motdepasse, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO utilisateur (email, numero_de_telephone, password, date_naissance, nom, prenom) VALUES (:email, :numero_de_telephone, :password, :date_naissance, :nom, :prenom)");
            $stmt->execute([
                ':email' => $email,
                ':numero_de_telephone' => $numero_de_telephone,
                ':password' => $hash,
                ':date_naissance' => $date_naissance,
                ':nom' => $nom,
                ':prenom' => $prenom
            ]);

            $stmt2 = $conn->prepare("INSERT INTO etudiant (email) VALUES (:email)");
            $stmt2->execute([':email' => $email]);

            $message = "Compte étudiant créé avec succès !";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Créer un Compte</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
<style>
    body {
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100vh;
        margin: 0;
        background: white;
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
    .input-icon {
        position: relative;
        margin-bottom: 20px;
    }
    .input-icon i {
        position: absolute;
        top: 50%;
        left: 10px;
        transform: translateY(-50%);
        color: #1e88e5;
        pointer-events: none;
        font-size: 18px;
    }
    .input-icon input {
        width: 100%;
        padding: 10px 10px 10px 35px; /* padding-left = place pour icône */
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
        transition: box-shadow 0.3s;
    }
    .input-icon input:focus {
        box-shadow: 0 0 8px #093c70;
        outline: none;
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
</style>
</head>
<body>

<div class="container">
    <h2 class="welcome-message">Créer votre Compte</h2>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($message)): ?>
        <div style="color:<?= strpos($message, 'succès') !== false ? 'green' : 'red' ?>;text-align:center;margin-bottom:10px;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form action="" method="post">
        <div class="input-icon">
            <i class="fas fa-paper-plane"></i>
            <input type="email" name="email" placeholder="E-mail" required>
        </div>
        <div class="input-icon">
            <i class="fas fa-user-circle"></i>
            <input type="text" name="firstname" placeholder="Prénom Assistant" required>
        </div>
        <div class="input-icon">
            <i class="fas fa-user-tie"></i>
            <input type="text" name="lastname" placeholder="Nom Assistant" required>
        </div>
        <div class="input-icon">
            <i class="fas fa-calendar-alt"></i>
            <input type="date" name="birthdate" required>
        </div>
        <div class="input-icon">
            <i class="fas fa-unlock-alt"></i>
            <input type="password" name="password" placeholder="Mot de passe" required>
        </div>
        <div class="input-icon">
            <i class="fas fa-phone"></i>
            <input type="tel" name="phone1" placeholder="Numéro de téléphone" required>
        </div>
        <button type="submit">Créer le compte</button>
    </form>
</div>

<br>

<div class="footer-section">
    <div class="footer-content">
        <div class="footer-info">
            <h5><i class="fas fa-info-circle"></i> À Propos de Nous</h5>
            <hr />
            <p>Nous sommes spécialisés dans l'accompagnement des étudiants pour leurs démarches d'études en Pologne et en Russie.</p>
        </div>
        <div class="footer-links">
            <h5><i class="fas fa-link"></i> Liens Utiles</h5>
            <hr />
            <ul>
                <li><a href="https://www.instagram.com/votreprofil" target="_blank">Instagram <i class="fab fa-instagram"></i></a></li>
                <li><a href="https://wa.me/123456789" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a></li>
                <li><a href="propos.html">À Propos</a></li>
            </ul>
        </div>
        <div class="footer-contact">
            <h5><i class="fas fa-envelope"></i> Contact</h5>
            <hr />
            <p>Email: <a href="mailto:contact@votreorganisation.com">contact@votreorganisation.com</a></p>
            <p>Téléphone: <a href="tel:+123456789">+33 1 23 45 67 89</a></p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <span id="current-year">2025</span> Trust Education. Tous droits réservés.</p>
    </div>
</div>

<script>
    // Correction du span id
    document.getElementById('current-year').textContent = new Date().getFullYear();
</script>

</body>
</html>
