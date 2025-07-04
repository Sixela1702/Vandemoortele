<?php
// create_admin.php

$host = '127.0.0.1';
$port = '3308';
$db   = 'conseil_maintenance';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    $email = 'test@vandemoortele.com';
    $plainPassword = 'admin123';
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
    $role = 'admin';

    $stmt = $pdo->prepare("INSERT INTO users (email, mot_de_passe, role) VALUES (?, ?, ?)");
    $stmt->execute([$email, $hashedPassword, $role]);

    echo "✅ Compte admin créé avec succès !<br>";
    echo "Email : $email<br>";
    echo "Mot de passe : $plainPassword";

} catch (\PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
