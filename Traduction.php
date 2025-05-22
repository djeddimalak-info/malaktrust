<?php
// Connexion à la base
$conn = new PDO("mysql:host=127.0.0.1;dbname=trusteducation;charset=utf8", "root", "");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer toutes les demandes de traduction
// $stmt = $conn->query("SELECT * FROM demandetraduction ORDER BY IDT DESC");
// $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = null; // Initialiser $message à null pour éviter tout affichage intempestif

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $langues = $_POST['langues'] ?? [];
    $files = $_FILES['fichiers'];

    // Vérifier que tous les champs obligatoires sont remplis
    if (empty($email) || empty($password) || empty($langues) || empty($files['name'][0])) {
        $message = "<div class='alert alert-danger text-center'>Veuillez remplir tous les champs et sélectionner au moins un fichier.</div>";
    } else {
        // Vérifier les identifiants de l'utilisateur
        $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $message = "<div class='alert alert-danger text-center'>Email introuvable dans utilisateur.</div>";
        } elseif ((strlen($row['password']) > 30 && strpos($row['password'], '$2y$') === 0) || strpos($row['password'], '$argon2') === 0 ? !password_verify($password, $row['password']) : $password !== $row['password']) {
            $message = "<div class='alert alert-danger text-center'>Mot de passe incorrect.</div>";
        } else {
            // Vérifier que l'email existe dans la table etudiant (clé étrangère)
            $stmtEtudiant = $conn->prepare("SELECT * FROM etudiant WHERE email = ?");
            $stmtEtudiant->execute([$email]);
            $rowEtudiant = $stmtEtudiant->fetch(PDO::FETCH_ASSOC);
            if (!$rowEtudiant) {
                $message = "<div class='alert alert-danger text-center'>Cet email n'est pas enregistré comme étudiant. Veuillez créer un compte étudiant avant de soumettre une demande de traduction.</div>";
            } else {
                // Insérer la demande (on stocke le hash du mot de passe)
                $stmt = $conn->prepare("INSERT INTO demandetraduction (email, password, date_creation) VALUES (:email, :password, NOW())");
                $stmt->execute(array(
                    ':email' => $email,
                    ':password' => $row['password']
                ));
                $IDT = $conn->lastInsertId();
                // Enregistrer chaque fichier
                foreach ($files['tmp_name'] as $i => $tmpName) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                        $nouveauNom = uniqid() . '.' . $extension;
                        if (!is_dir(__DIR__ . "/uploads")) {
                            mkdir(__DIR__ . "/uploads", 0777, true);
                        }
                        move_uploaded_file($tmpName, __DIR__ . "/uploads/" . $nouveauNom);
                        // DEBUG : log temporaire pour vérifier l'alignement fichiers/langues
                        file_put_contents(__DIR__ . '/debug_langues.txt', "i=$i, fichier=" . $files['name'][$i] . ", langue=" . ($langues[$i] ?? 'NULL') . "\n", FILE_APPEND);
                        $allowedLangues = ['fr','en','pl','al','es','ru'];
                        $langue = (isset($langues[$i]) && !empty($langues[$i]) && in_array($langues[$i], $allowedLangues)) ? $langues[$i] : null;
                        if ($langue === null) {
                            $message = "<div class='alert alert-danger text-center'>Veuillez sélectionner une langue pour chaque fichier.</div>";
                            // Suppression du fichier uploadé si la langue n'est pas valide
                            if (file_exists(__DIR__ . "/uploads/" . $nouveauNom)) {
                                unlink(__DIR__ . "/uploads/" . $nouveauNom);
                            }
                            break;
                        }
                        $typeFichier = $files['type'][$i];
                        // DEBUG : log insertion
                        file_put_contents(__DIR__ . '/debug_sql.txt', "Avant insertion: i=$i, fichier=$nouveauNom, langue=$langue\n", FILE_APPEND);
                        try {
                            $stmt2 = $conn->prepare("INSERT INTO fichiertraduction (IDT, nom_fichier, langue, type_fichier) VALUES (:IDT, :nom_fichier, :langue, :type_fichier)");
                            $stmt2->execute([
                                ':IDT' => $IDT,
                                ':nom_fichier' => $nouveauNom,
                                ':langue' => $langue,
                                ':type_fichier' => $typeFichier
                            ]);
                            file_put_contents(__DIR__ . '/debug_sql.txt', "Insertion OK: i=$i, fichier=$nouveauNom, langue=$langue\n", FILE_APPEND);
                        } catch (PDOException $e) {
                            file_put_contents(__DIR__ . '/debug_sql.txt', $e->getMessage() . "\n", FILE_APPEND);
                        }
                    }
                }
                // Afficher le message de succès uniquement après la création
                $message = "<div class='alert alert-success text-center'>Demande enregistrée avec succès !</div>";
                unset($lastDemande, $lastFichiers);
                // Utiliser le pattern PRG pour éviter la resoumission, mais passer le message via l'URL
                header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
                exit;
            }
        }
    }
}
// Affichage du message de succès si redirection
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $message = "<div class='alert alert-success text-center'>Demande enregistrée avec succès !</div>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploader des Documents</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background:white;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #222;
            min-height: 100vh;
            margin: 0;
            padding-top: 80px;
        }
        .navbar {
            background-color: #f8f9fa;
            width: 100%; /* Prend toute la largeur de la fenêtre */
            max-width: 1320px; /* Largeur max Bootstrap xl */
            margin: 0 auto;
            padding-left: 10px;
            padding-right: 10px;
        }
        .navbar .container {
            max-width: 1320px; /* Largeur max Bootstrap xl */
            width: 100%;
            padding-left: 0;
            padding-right: 0;
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
            background: none;
            color: #002244 !important;
        }
        .navbar-nav .dropdown-menu {
            border-radius: 0;
            border: none;
            box-shadow: none;
            color: #00008B;
            font-weight: normal;
            background: #fff;
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
        .container {
            flex-grow: 1;
            padding: 30px;
            margin-top: 20px;
            /* Suppression du fond blanc, de l'arrondi et de l'ombre */
        }
        h2 {
            color: #002244;
            font-weight: bold;
            font-size: 1.5em;
            margin-bottom: 24px;
        }
        .form-label {
            font-weight: 600;
            color: #0D3B66;
        }
        .form-control.animated-input {
            border: 1.5px solid #bbb;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 1.08em;
            background: #f7f7f7;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control.animated-input:focus {
            border-color: #0D3B66;
            box-shadow: 0 0 0 2px #0D3B6633;
            outline: none;
            background: #fff;
        }
        .file-input-custom {
            border: 2px solid #0D3B66;
            border-radius: 12px;
            background: #f7faff;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 16px;
            box-shadow: 0 2px 12px #0d3b6611;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .file-input-custom:focus-within {
            border-color: #093c70;
            box-shadow: 0 0 0 2px #0D3B6633;
        }
        .custom-file-label {
            flex: 1;
            display: flex;
            align-items: center;
            cursor: pointer;
            margin-bottom: 0;
            font-size: 1.08em;
            color: #0D3B66;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #fff;
            border-radius: 8px;
            border: 1.5px solid #bbb;
            padding: 12px 18px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .custom-file-label:hover, .custom-file-label:focus {
            border-color: #0D3B66;
            box-shadow: 0 0 0 2px #0D3B6633;
        }
        .custom-file-label input[type="file"] {
            display: none;
        }
        .file-label-text {
            color: #0D3B66;
            font-weight: 500;
            font-size: 1.08em;
            margin-right: 12px;
        }
        .fa-upload {
            color: #0D3B66;
            font-size: 1.25em;
            margin-left: 10px;
        }
        .form-select.w-auto {
            border: 2px solid #0D3B66;
            border-radius: 8px;
            background: #fff;
            padding: 0.6em 1.5em 0.6em 1em;
            min-width: 160px;
            font-size: 1em;
            color: #0D3B66;
            font-family: 'Segoe UI', Arial, sans-serif;
            box-shadow: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            margin-left: 8px;
            margin-right: 8px;
        }
        .form-select.w-auto:focus {
            border-color: #093c70;
            box-shadow: 0 0 0 2px #0D3B6633;
            outline: none;
            background: #fff;
        }
        .form-select.w-auto option {
            color: #0D3B66;
            background: #fff;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        /* Correction du style pour les champs dynamiques ajoutés après le bouton Ajouter */
        #fichiers-container .form-select.w-auto {
            border: 2px solid #0D3B66;
            border-radius: 8px;
            background: #fff;
            padding: 0.6em 1.5em 0.6em 1em;
            min-width: 160px;
            font-size: 1em;
            color: #0D3B66;
            font-family: 'Segoe UI', Arial, sans-serif;
            box-shadow: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            margin-left: 8px;
            margin-right: 8px;
        }
        #fichiers-container .form-select.w-auto:focus {
            border-color: #093c70;
            box-shadow: 0 0 0 2px #0D3B6633;
            outline: none;
            background: #fff;
        }
        #fichiers-container .form-select.w-auto option {
            color: #0D3B66;
            background: #fff;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        /* Corrige le style du premier select pour qu'il soit identique aux suivants */
        .file-input-custom select.form-select.w-auto {
            margin-left: 8px;
            margin-right: 8px;
        }
        .deleteFile {
            margin-left: 10px;
            font-size: 0.98em;
            padding: 7px 16px;
            border-radius: 6px;
            background: #fff;
            color: #c00;
            border: 1.5px solid #c00;
            transition: background 0.2s, color 0.2s;
        }
        .deleteFile:hover {
            background: #c00;
            color: #fff;
        }
        .file-input-custom .fa-arrow-right {
            color: #0D3B66;
            font-size: 1.1em;
        }
        .btn-primary.animated-btn {
            background: #0D3B66;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            padding: 10px 28px;
            font-size: 1.08em;
            transition: background 0.2s, transform 0.2s;
        }
        .btn-primary.animated-btn:hover {
            background: #093c70;
            transform: scale(1.05);
        }
        .btn-success.animated-btn {
            background: #198754;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            padding: 10px 28px;
            font-size: 1.08em;
            transition: background 0.2s, transform 0.2s;
        }
        .btn-success.animated-btn:hover {
            background: #146c43;
            transform: scale(1.05);
        }
        .footer-section {
            background-color: #003366;
            color: white;
            padding: 20px;
            margin-top: 30px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
            box-shadow: none;
            width: 100%;
            left: 0;
            right: 0;
            margin-left: 0;
            margin-right: 0;
            position: static;
        }
        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            padding: 20px 0;
        }
        .footer-info, .footer-links, .footer-contact {
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
        @media (max-width: 700px) {
            .container { max-width: 98vw; padding: 10px; }
        }
        @media (max-width: 600px) {
            .file-input-custom { flex-direction: column; align-items: stretch; gap: 10px; }
            .form-select.w-auto { min-width: 100%; }
            .container { padding: 6px; }
        }
    </style>
</head>
<body>
    <!-- BARRE DE NAVIGATION COPIÉE DE pageaccueil.html (structure, classes, HTML, sans adaptation) -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top" style="background-color: #f8f9fa; padding-left: 10px; padding-right: 10px;">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="your-logo.png" alt="Logo" />
            </a>
            <span style="color:rgb(5, 39, 50); font-size: 1.3em;" class="navbar-text">Trust Education</span>
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
    <br><br><br>

    <div class="container my-4">
        <h2 class="text-center mb-4"> Traduire vos documents</h2>
        <?php if (isset($message) && !empty($message)) echo $message; ?>
        <!-- Formulaire -->
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="email" class="form-label"><i class="fa-solid fa-envelope"></i> Adresse Email</label>
                <input type="email" id="email" name="email" class="form-control animated-input" placeholder="Entrez votre email" required style="width: 300px;">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label"><i class="fa-solid fa-lock"></i> Mot de passe</label>
                <input type="password" id="password" name="password" class="form-control animated-input" placeholder="Entrez votre mot de passe" required style="width: 300px;">
            </div>
            <label class="form-label"><i class="fa-solid fa-file"></i> Fichier à traduire</label>
            <div id="fichiers-container"></div>
            <button type="button" class="btn btn-primary animated-btn" id="addFileBtn">Ajouter</button>
            <br><br>
            <div class="form-check mt-3">
                <input type="checkbox" class="form-check-input" id="confirmCheckbox" required>
                <label for="confirmCheckbox" class="form-check-label">
                    Je certifie que les documents envoyés ne contiennent aucune donnée sensible.
                </label>
            </div>
            <br>
            <div class="mb-3 text-center">
                <p>Si vous n'avez pas de mot de passe, vous devez créer un compte <a href="creercompte.html">Créer un compte</a></p>
            </div>
            <br>
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-success animated-btn">Envoyer</button>
            </div>
        </form>
        <div id="lang-warning" class="alert alert-danger text-center" style="display:none;">Veuillez sélectionner une langue pour chaque fichier.</div>
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
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const fichiersContainer = document.getElementById('fichiers-container');
        const addBtn = document.getElementById('addFileBtn');
        const form = document.querySelector('form');
        const submitBtn = form.querySelector('button[type="submit"]');
        const checkBox = document.getElementById('confirmCheckbox');
        const langWarning = document.getElementById('lang-warning');

        function createFileInput() {
            const fileGroup = document.createElement('div');
            fileGroup.className = 'mb-3 file-input file-input-custom';
            fileGroup.style.display = 'flex';
            fileGroup.style.alignItems = 'center';
            fileGroup.style.gap = '12px';
            fileGroup.style.padding = '10px 14px';
            fileGroup.style.background = '#f7faff';
            fileGroup.style.borderRadius = '12px';
            fileGroup.style.border = '2px solid #0D3B66';
            fileGroup.style.boxShadow = '0 2px 12px #0d3b6611';

            // Bouton supprimer rond, icône ✖
            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'deleteFile';
            deleteBtn.innerHTML = '<i class="fa-solid fa-times"></i>';
            deleteBtn.style.width = '36px';
            deleteBtn.style.height = '36px';
            deleteBtn.style.borderRadius = '50%';
            deleteBtn.style.background = '#fff';
            deleteBtn.style.border = '2px solid #c00';
            deleteBtn.style.color = '#c00';
            deleteBtn.style.display = 'flex';
            deleteBtn.style.alignItems = 'center';
            deleteBtn.style.justifyContent = 'center';
            deleteBtn.style.fontSize = '1.1em';
            deleteBtn.style.transition = 'background 0.2s, color 0.2s';
            deleteBtn.style.marginRight = '6px';
            deleteBtn.style.marginLeft = '0';
            deleteBtn.style.flexShrink = '0';
            deleteBtn.onmouseover = function() {
                if (!deleteBtn.disabled) {
                    deleteBtn.style.background = '#c00';
                    deleteBtn.style.color = '#fff';
                }
            };
            deleteBtn.onmouseout = function() {
                deleteBtn.style.background = '#fff';
                deleteBtn.style.color = '#c00';
            };

            // Champ fichier stylé
            const fileInputWrapper = document.createElement('div');
            fileInputWrapper.style.position = 'relative';
            fileInputWrapper.style.flex = '1 1 0';
            fileInputWrapper.style.display = 'flex';
            fileInputWrapper.style.alignItems = 'center';

            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.name = 'fichiers[]';
            fileInput.required = true;
            fileInput.style.opacity = '0';
            fileInput.style.position = 'absolute';
            fileInput.style.left = '0';
            fileInput.style.top = '0';
            fileInput.style.width = '100%';
            fileInput.style.height = '100%';
            fileInput.style.cursor = 'pointer';
            fileInput.style.zIndex = '2';

            const fileDisplay = document.createElement('div');
            fileDisplay.style.display = 'flex';
            fileDisplay.style.alignItems = 'center';
            fileDisplay.style.width = '100%';
            fileDisplay.style.height = '38px';
            fileDisplay.style.background = '#fff';
            fileDisplay.style.border = '1.5px solid #bbb';
            fileDisplay.style.borderRadius = '8px';
            fileDisplay.style.padding = '0 14px 0 14px';
            fileDisplay.style.fontSize = '1em';
            fileDisplay.style.color = '#888';
            fileDisplay.style.fontWeight = '500';
            fileDisplay.style.position = 'relative';
            fileDisplay.style.transition = 'border-color 0.2s, box-shadow 0.2s';
            fileDisplay.style.flex = '1 1 0';
            fileDisplay.style.cursor = 'pointer';
            fileDisplay.tabIndex = 0;

            const fileText = document.createElement('span');
            fileText.textContent = 'Fichier…';
            fileText.style.flex = '1';
            fileText.style.overflow = 'hidden';
            fileText.style.textOverflow = 'ellipsis';
            fileText.style.whiteSpace = 'nowrap';

            const uploadIcon = document.createElement('i');
            uploadIcon.className = 'fa-solid fa-upload';
            uploadIcon.style.color = '#0D3B66';
            uploadIcon.style.fontSize = '1.15em';
            uploadIcon.style.marginLeft = '10px';

            fileDisplay.appendChild(fileText);
            fileDisplay.appendChild(uploadIcon);
            fileInputWrapper.appendChild(fileDisplay);
            fileInputWrapper.appendChild(fileInput);

            // Flèche
            const arrow = document.createElement('span');
            arrow.innerHTML = '<i class="fa-solid fa-arrow-right"></i>';
            arrow.className = 'fa-arrow-right';
            arrow.style.margin = '0 8px';
            arrow.style.color = '#0D3B66';
            arrow.style.fontSize = '1.1em';
            arrow.style.flexShrink = '0';

            // Select langue stylé
            const select = document.createElement('select');
            select.name = 'langues[]';
            select.className = 'form-select w-auto';
            select.required = true;
            select.style.height = '38px';
            select.style.minWidth = '120px';
            select.style.border = '2px solid #0D3B66';
            select.style.borderRadius = '8px';
            select.style.background = '#fff';
            select.style.padding = '0 1.5em 0 1em';
            select.style.fontSize = '1em';
            select.style.color = '#0D3B66';
            select.style.fontFamily = 'Segoe UI, Arial, sans-serif';
            select.style.boxShadow = 'none';
            select.style.transition = 'border-color 0.2s, box-shadow 0.2s';
            select.style.appearance = 'none';
            select.style.margin = '0 0 0 0';
            select.innerHTML = `
                <option value="" selected disabled>Langue</option>
                <option value="fr">Français</option>
                <option value="en">Anglais</option>
                <option value="pl">Polonais</option>
                <option value="al">Allemand</option>
                <option value="es">Espagnol</option>
                <option value="ru">Russe</option>
            `;

            // Responsive : colonne sur mobile
            fileGroup.style.flexWrap = 'nowrap';
            fileGroup.style.width = '100%';
            fileGroup.style.minWidth = '0';
            fileGroup.style.boxSizing = 'border-box';
            fileGroup.style.marginBottom = '12px';
            fileGroup.style.gap = '12px';
            fileGroup.style.alignItems = 'center';
            fileGroup.style.justifyContent = 'flex-start';

            // Ordre : Supprimer | input file | flèche | select
            fileGroup.appendChild(deleteBtn);
            fileGroup.appendChild(fileInputWrapper);
            fileGroup.appendChild(arrow);
            fileGroup.appendChild(select);

            // Gestion du bouton supprimer : grisé pour la première ligne, normal pour les suivantes
            if (fichiersContainer.childElementCount === 0) {
                deleteBtn.disabled = true;
                deleteBtn.style.opacity = 0.4;
                deleteBtn.style.pointerEvents = 'none';
            } else {
                deleteBtn.disabled = false;
                deleteBtn.style.opacity = 1;
                deleteBtn.style.pointerEvents = 'auto';
            }

            // Interaction fichier
            fileDisplay.onclick = function() { fileInput.click(); };
            fileDisplay.onkeydown = function(e) { if (e.key === 'Enter' || e.key === ' ') fileInput.click(); };
            fileInput.addEventListener('change', function() {
                fileText.textContent = fileInput.files.length > 0 ? fileInput.files[0].name : 'Fichier…';
                if (fichiersContainer.childElementCount === 0) {
                    deleteBtn.disabled = true;
                    deleteBtn.style.opacity = 0.4;
                    deleteBtn.style.pointerEvents = 'none';
                } else {
                    deleteBtn.disabled = fileInput.files.length === 0;
                    deleteBtn.style.opacity = fileInput.files.length === 0 ? 0.4 : 1;
                    deleteBtn.style.pointerEvents = fileInput.files.length === 0 ? 'none' : 'auto';
                }
                validateForm();
            });
            deleteBtn.addEventListener('click', () => {
                fileGroup.remove();
                validateForm();
            });
            select.addEventListener('change', validateForm);
            return fileGroup;
        }

        addBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const newInput = createFileInput();
            fichiersContainer.appendChild(newInput);
            validateForm();
        });

        // Efface le contenu initial du container pour garantir la cohérence
        fichiersContainer.innerHTML = '';
        // Ajoute la première ligne avec le bon rendu
        fichiersContainer.appendChild(createFileInput());

        function validateForm() {
            let valid = true;
            fichiersContainer.querySelectorAll('.file-input').forEach(function (fileGroup) {
                const fileInput = fileGroup.querySelector('input[type="file"]');
                const select = fileGroup.querySelector('select');
                if (!fileInput.value || !select.value || select.value === "") {
                    valid = false;
                }
            });
            if (!form.email.value.trim() || !form.password.value.trim() || !checkBox.checked) {
                valid = false;
            }
            submitBtn.disabled = !valid;
            langWarning.style.display = valid ? 'none' : 'block';
        }

        // Validation initiale au chargement
        validateForm();
    });
    </script>
</body>
</html>