<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<button class="sidebar-toggle">
    <i class="bx bx-menu"></i>
</button>

<div class="sidebar">
    <div class="sidebar-header">
        <h4>Sistema de Gestão</h4>
    </div>
    
    <div class="user-info">
        <i class="bx bxs-user-circle"></i>
        <h6><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário'); ?></h6>
    </div>
    
    <nav class="nav-menu">
        <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" 
           href="dashboard.php">
            <i class="bx bxs-dashboard"></i>
            Dashboard
        </a>
        
        <a class="nav-link <?php echo in_array($current_page, ['associados.php', 'associado-detalhes.php']) ? 'active' : ''; ?>" 
           href="associados.php">
            <i class="bx bxs-user"></i>
            Associados
        </a>
        
        <a class="nav-link <?php echo in_array($current_page, ['cobrancas.php', 'cobranca-nova.php']) ? 'active' : ''; ?>" 
           href="cobrancas.php">
            <i class="bx bxs-file"></i>
            Cobranças
        </a>
        
        <a class="nav-link <?php echo $current_page === 'relatorios.php' ? 'active' : ''; ?>" 
           href="relatorios.php">
            <i class="bx bxs-report"></i>
            Relatórios
        </a>
        
        <a class="nav-link <?php echo $current_page === 'bulk.php' ? 'active' : ''; ?>" 
           href="bulk.php">
            <i class="bx bxs-cog"></i>
            Disparador de cobrança
        </a>

        <a class="nav-link <?php echo $current_page === 'bulk-mailer.php' ? 'active' : ''; ?>" 
           href="bulk-mailer.php">
            <i class="bx bxs-cog"></i>
            Mailer
        </a>        
    </nav>
    
    <div class="sidebar-footer">
        <a href="src/auth/logout.php" class="btn btn-danger w-100 d-flex align-items-center justify-content-center">
            <i class="bx bx-log-out me-2"></i>
            Sair
        </a>
    </div>
</div>
