<?php
$serveur = "localhost";
$utilisateur = "root"; 
$motDePasse = "";
$baseDeDonnees = "trusteducation";

$conn = new mysqli($serveur, $utilisateur, $motDePasse, $baseDeDonnees);

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

$signInError = "";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signIn'])) {
    $email = trim($_POST['email']);
    $code = trim($_POST['password']);  // Le code est envoyé dans le champ password
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : (isset($_POST['redirect']) ? $_POST['redirect'] : '');

    $sql = "SELECT * FROM assistantagence WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $dbCode = $row['code'];

        if ($code === $dbCode) {
            $_SESSION['email'] = $row['email'];
            $destination = (!empty($redirect) && preg_match('/^[a-zA-Z0-9_.-]+\\.php$/', $redirect)) ? $redirect : 'dashbord.php';
            header("Location: $destination");
            exit();
        } else {
            $signInError = "Email ou code incorrect.";
        }
    } else {
        $signInError = "Email ou code incorrect.";
    }
}
?>

<!DOCTYPE html>  
<html lang="fr">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Page de Connexion</title>  
     <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
 
    <style>  
        * {  
            margin: 0;  
            padding: 0;  
            box-sizing: border-box;  
            font-family: Arial, serif;  
        }  

        body {  
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);  
            min-height: 100vh;  
            display: flex;  
            flex-direction: column;  
        
            padding-top: 10px;
        }  

        .container {  
            display: flex;  
            justify-content: center;  
            align-items: center;  
            flex: 1;  
            padding: 20px;  
            /* Ajout d'un margin-top pour décaler le bloc de connexion plus bas */
            margin-top: 30px;
        }  

        .login-section {  
            background: rgba(255, 255, 255, 0.95);  
            border-radius: 20px;  
            padding: 40px;  
            width: 100%;  
            max-width: 400px;  
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);  
            transition: transform 0.3s ease;  
        }  

        .login-section:hover {  
            transform: translateY(-5px);  
        }  

        .login-section h3 {  
            color:rgb(20, 72, 124);  
            font-size: 1.4em;  
            margin-bottom: 30px;  
            text-align: center;  
        }  

        .input-group {  
            position: relative;  
            margin-bottom: 25px;  
        }  

        .input-group input {  
            width: 100%;  
            padding: 15px 15px 15px 42px; /* Ajout d'un padding-left plus grand pour laisser la place à l'icône */  
            border: none;  
            background: #f0f0f0;  
            border-radius: 10px;  
            font-size: 16px;  
            transition: all 0.3s ease;  
        }  

        .input-group input:focus {  
            outline: none;  
            background: #fff;  
            box-shadow: 0 0 0 2px #2c3e50;  
        }  

        .input-group span {  
            position: absolute;  
            left: 15px;  
            top: 67%;  
            transform: translateY(-60%); /* Légèrement plus haut qu'avant */  
            color: #666;  
            font-size: 1.1em; /* Légèrement plus petit pour ne pas gêner le texte */  
            pointer-events: none; /* Laisse le champ input cliquable partout */  
        }  

        button {  
            width: 100%;  
            padding: 15px;  
            background: linear-gradient(45deg, #2c3e50, #3498db);  
            border: none;  
            border-radius: 10px;  
            color: white;  
            font-size: 16px;  
            font-weight: 600;  
            cursor: pointer;  
            transition: transform 0.3s ease;  
        }  

        button:hover {  
            background: linear-gradient(45deg, #3498db, #2c3e50);  
            transform: scale(1.02);  
        }  

        .options {  
            margin-top: 20px;  
            text-align: center;  
        }  

        .options a {  
            color: #3498db;  
            text-decoration: none;  
            font-weight: 500;  
            transition: color 0.3s ease;  
        }  

        .options a:hover {  
            color: #2c3e50;  
            text-decoration: underline;  
        }  
       .footer-section {
    background-color: #003366;
    color: white;
    padding: 20px;
    width: 100%;
    box-sizing: border-box;
    position: relative;
    left: 0;
    right: 0;
    flex-shrink: 0;
    margin-top: 0 !important;
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
        
        @keyframes fadeIn {  
            from {  
                opacity: 0;  
                transform: translateY(-20px);  
            }  
            to {  
                opacity: 1;  
                transform: translateY(0);  
            }  
        }  

    
    </style>  
</head>  
<body>
    
    <div class="container">  
        <div class="login-section">  
            <h3> Accès sécurisé</h3>  
            <form action="" method="POST">
<?php if (isset($_GET['redirect']) && !empty($_GET['redirect'])): ?>
    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect']); ?>">
<?php endif; ?>
                <div class="input-group">
                    <label for="email">E-mail</label>
                    <span class="fa fa-envelope"></span>
                    <input type="email" id="email" name="email" placeholder="Votre e-mail" required>
                </div>
                <div class="input-group password-group">
                    <label for="password">Mot de passe</label>
                    <span class="fa fa-key"></span>
                    <input type="password" id="password" name="password" placeholder="Votre code" required>
                </div>
                <button type="submit" name="signIn">CONNEXION</button>
            </form>
           <?php if (!empty($signInError)): ?>
    <div style="color:red;text-align:center;margin-bottom:10px;">
        <?php echo $signInError; ?>
    </div>
<?php endif; ?>
            <div class="options">  
                <p>Pas de compte ? <a href="creercompteAA.php">Créer un compte</a></p>  
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
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>  
</html>