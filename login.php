<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestão de Cobrança</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
        }
        .login-container {
            max-width: 400px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .login-header {
            text-align: center;
            padding: 20px 0;
        }
        .login-header img {
            max-width: 150px;
            margin-bottom: 15px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
        }
        .btn-login {
            border-radius: 10px;
            padding: 12px;
            background: #667eea;
            border: none;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="login-container p-5 w-100">
            <div class="login-header">
                <h2 class="mb-4">Gestão de Cobrança</h2>
            </div>
            <?php
            if (isset($_GET['erro'])) {
                $mensagem = '';
                switch ($_GET['erro']) {
                    case 'campos_vazios':
                        $mensagem = 'Por favor, preencha todos os campos.';
                        break;
                    case 'credenciais_invalidas':
                        $mensagem = 'E-mail ou senha incorretos.';
                        break;
                    case 'erro_sistema':
                        $mensagem = 'Erro no sistema. Tente novamente mais tarde.';
                        break;
                }
                if ($mensagem) {
                    echo "<div class='alert alert-danger mb-4' role='alert'>$mensagem</div>";
                }
            }
            ?>
            <form action="src/auth/autenticar.php" method="POST">
                <div class="mb-4">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-4">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="senha" name="senha" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-login btn-primary btn-lg">Entrar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
