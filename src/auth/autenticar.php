<?php
session_start();
require_once __DIR__ . '/../Sindicato.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    if (empty($email) || empty($senha)) {
        header('Location: ../../login.php?erro=campos_vazios');
        exit;
    }

    try {
        $auth = new Auth();
        $usuario = $auth->login($email, $senha);

        if ($usuario) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['nivel_acesso'] = $usuario['nivel_acesso'];

            header('Location: ../../dashboard.php');
            exit;
        } else {
            header('Location: ../../login.php?erro=credenciais_invalidas');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Erro de autenticação: " . $e->getMessage());
        header('Location: ../../login.php?erro=erro_sistema');
        exit;
    }
} else {
    header('Location: ../../login.php');
    exit;
}
