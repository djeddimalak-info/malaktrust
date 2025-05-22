<?php
$serveur = "localhost";
$utilisateur = "root"; 
$motDePasse = "";
$baseDeDonnees = "trusteducation";

$conn = new mysqli($serveur, $utilisateur, $motDePasse, $baseDeDonnees);

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}
?>