<?php
if (!isset($page_title)) {
    $page_title = 'Sistema de Gestão';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if (isset($extra_css)): ?>
        <?php echo $extra_css; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="d-flex flex-column h-100">
            <div class="p-3 text-center">
                <h4 class="mb-0">Sistema de Gestão</h4>
            </div>
            <div class="p-3 border-bottom">
                <div class="text-center mb-3">
                    <i class='bx bxs-user-circle' style="font-size: 3rem;"></i>
                    <h6 class="mt-2 mb-0"><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário'); ?></h6>
                </div>
            </div>
            <nav class="nav flex-column mt-3">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" 
                   href="dashboard.php">
                    <i class='bx bxs-dashboard'></i>Dashboard
                </a>
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'associados.php' ? 'active' : ''; ?>" 
                   href="associados.php">
                    <i class='bx bxs-user'></i>Associados
                </a>
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'cobrancas.php' ? 'active' : ''; ?>" 
                   href="cobrancas.php">
                    <i class='bx bxs-file'></i>Cobranças
                </a>
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'relatorios.php' ? 'active' : ''; ?>" 
                   href="relatorios.php">
                    <i class='bx bxs-report'></i>Relatórios
                </a>
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'bulk.php' ? 'active' : ''; ?>" 
                   href="bulk.php">
                    <i class='bx bxs-send'></i>Disparador
                </a>
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'bulk-mailer.php' ? 'active' : ''; ?>" 
                   href="bulk-mailer.php">
                    <i class='bx bxs-send'></i>Mailer
                </a> 

            </nav>
            <div class="mt-auto p-3">
                <a href="src/auth/logout.php" class="btn btn-danger w-100">
                    <i class='bx bx-log-out'></i> Sair
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-max">
            <?php if (isset($page_content)): ?>
                <?php echo $page_content; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.createElement('button');
            toggleBtn.className = 'btn btn-primary d-md-none position-fixed';
            toggleBtn.style.cssText = 'top: 1rem; right: 1rem; z-index: 1050;';
            toggleBtn.innerHTML = '<i class="bx bx-menu"></i>';
            document.body.appendChild(toggleBtn);

            toggleBtn.addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('active');
            });
        });
    </script>
    <?php if (isset($extra_js)): ?>
        <?php echo $extra_js; ?>
    <?php endif; ?>
</body>
</html>
