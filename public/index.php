<?php
// public/index.php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/config/database.php';

// Connexion à la base de données
$pdo = getPDO();

// Créer l'application Slim
$app = AppFactory::create();

// Configurer Twig
$twig = Twig::create(__DIR__ . '/../app/templates', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

// Route page de connexion
$app->get('/', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'login.twig');
});

// Traitement de la connexion
$app->post('/login', function (Request $request, Response $response) use ($twig, $pdo) {
    $data = (array)$request->getParsedBody();
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        return $response->withHeader('Location', '/home')->withStatus(302);
    }

    return $twig->render($response, 'login.twig', [
        'error' => 'Email ou mot de passe incorrect'
    ]);
});

// Déconnexion
$app->get('/logout', function ($request, $response) {
    session_destroy();
    return $response->withHeader('Location', '/')->withStatus(302);
});

// Page d'accueil selon rôle
$app->get('/home', function (Request $request, Response $response) use ($twig) {
    if (!isset($_SESSION['user_email'])) {
        return $response->withHeader('Location', '/')->withStatus(302);
    }

    $role = $_SESSION['role'] ?? null;

    switch ($role) {
        case 'admin':
            return $twig->render($response, 'admin_home.twig');
        case 'tech':
            return $twig->render($response, 'tech_home.twig');
        default:
            return $response->withHeader('Location', '/')->withStatus(302);
    }
});

// Ajouter un document (admin uniquement)
$app->get('/ajouter', function (Request $request, Response $response) use ($twig) {
    $role = $_SESSION['role'] ?? null;
    if ($role !== 'admin') {
        return $response->withHeader('Location', '/home')->withStatus(302);
    }
    return $twig->render($response, 'ajouter.twig');
});

// Modifier un document (admin uniquement)
$app->get('/modifier', function (Request $request, Response $response) use ($twig) {
    $role = $_SESSION['role'] ?? null;
    if ($role !== 'admin') {
        return $response->withHeader('Location', '/home')->withStatus(302);
    }
    return $twig->render($response, 'modifier.twig');
});

// Supprimer un document (admin uniquement)
$app->get('/supprimer', function (Request $request, Response $response) use ($twig) {
    $role = $_SESSION['role'] ?? null;
    if ($role !== 'admin') {
        return $response->withHeader('Location', '/home')->withStatus(302);
    }
    return $twig->render($response, 'supprimer.twig');
});

// Page de gestion utilisateurs (admin seulement)
$app->get('/utilisateurs', function (Request $request, Response $response) use ($twig, $pdo) {
    if ($_SESSION['role'] !== 'admin') {
        return $response->withHeader('Location', '/home')->withStatus(302);
    }

    $stmt = $pdo->query("SELECT email, role FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $twig->render($response, 'gestion_users.twig', [
        'utilisateurs' => $users
    ]);
});

// Ajouter un utilisateur
$app->post('/utilisateurs/ajouter', function (Request $request, Response $response) use ($pdo) {
    $data = (array)$request->getParsedBody();
    $email = $data['email'];
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $role = $data['role'];

    $stmt = $pdo->prepare("INSERT INTO users (email, mot_de_passe, role) VALUES (?, ?, ?)");
    $stmt->execute([$email, $password, $role]);

    return $response->withHeader('Location', '/utilisateurs')->withStatus(302);
});

// Modifier le rôle d’un utilisateur
$app->post('/utilisateurs/modifier-role', function (Request $request, Response $response) use ($pdo) {
    $data = (array)$request->getParsedBody();
    $email = $data['email'];
    $role = $data['role'];

    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE email = ?");
    $stmt->execute([$role, $email]);

    return $response->withHeader('Location', '/utilisateurs')->withStatus(302);
});

// Supprimer un utilisateur
$app->post('/utilisateurs/supprimer', function (Request $request, Response $response) use ($pdo) {
    $data = (array)$request->getParsedBody();
    $email = $data['email'];

    $stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
    $stmt->execute([$email]);

    return $response->withHeader('Location', '/utilisateurs')->withStatus(302);
});

// Page de recherche de documents (accessible à tous connectés)
$app->get('/recherche', function (Request $request, Response $response) use ($twig) {
    if (!isset($_SESSION['user_email'])) {
        return $response->withHeader('Location', '/')->withStatus(302);
    }

    return $twig->render($response, 'recherche.twig');
});

$app->run();
