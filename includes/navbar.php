<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class='bx bx-building-house'></i>
            <span>Sistema Condomínio</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <i class='bx bx-menu'></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" href="index.php">
                        <i class='bx bx-grid-alt'></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'associados.php' ? 'active' : ''; ?>" href="associados.php">
                        <i class='bx bx-group'></i>
                        <span>Associados</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'cobrancas.php' ? 'active' : ''; ?>" href="cobrancas.php">
                        <i class='bx bx-dollar'></i>
                        <span>Cobranças</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'relatorios.php' ? 'active' : ''; ?>" href="relatorios.php">
                        <i class='bx bx-line-chart'></i>
                        <span>Relatórios</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'configuracoes.php' ? 'active' : ''; ?>" href="configuracoes.php">
                        <i class='bx bx-cog'></i>
                        <span>Configurações</span>
                    </a>
                </li>
            </ul>

            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                    <i class='bx bx-user-circle fs-5'></i>
                    <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuário'); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="perfil.php">
                            <i class='bx bx-user'></i>
                            <span>Perfil</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="configuracoes.php">
                            <i class='bx bx-cog'></i>
                            <span>Configurações</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="logout.php">
                            <i class='bx bx-log-out'></i>
                            <span>Sair</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
