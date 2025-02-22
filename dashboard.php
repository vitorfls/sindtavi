<?php
session_start();
require_once __DIR__ . '/src/Sindicato.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$associados = new Associados();

// Datas do filtro
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
$data_fim = $_GET['data_fim'] ?? date('Y-m-t');

// Buscar estatísticas
$stats = $associados->getEstatisticas($data_inicio, $data_fim);

// Buscar cobranças recentes
$pagamentos_recentes = $associados->getPagamentosRecentes($data_inicio, $data_fim);

// Buscar próximos vencimentos
$proximos_vencimentos = $associados->getProximosVencimentos($data_inicio, $data_fim);

// Definir o título da página
$page_title = 'Dashboard - Sistema de Gestão';

// Capturar o conteúdo da página
ob_start();
?>

<!-- Cabeçalho da Página -->
<!-- Cabeçalho da Página -->
<div class="container mb-5">
    <div class="row align-items-center">
        <!-- Título -->
        <div class="col-12 col-lg-6 mb-3 mb-lg-0">
            <h2 class="page-title mb-1">📊 Dashboard</h2>
            <p class="page-subtitle mb-0">Visão geral do sistema</p>
        </div>
        <!-- Formulário -->
        <div class="col-12 col-lg-6">
            <form class="row g-2 justify-content-lg-end">
                <div class="col-12 col-sm-auto">
                    <input type="date" class="form-control" name="data_inicio" value="2024-12-01">
                </div>
                <div class="col-12 col-sm-auto">
                    <input type="date" class="form-control" name="data_fim" value="2024-12-31">
                </div>
                <div class="col-12 col-sm-auto">
                    <button type="submit" class="btn btn-primary w-100 w-sm-auto">Filtrar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Cards de Estatísticas -->
<div class="container">
    <!-- Card Principal: Overview -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 text-center">
                    <div class="row g-4">
                        <div class="col-12 col-md-4">
                            <div class="d-flex align-items-center justify-content-center flex-column">
                                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                                <h6 class="card-title text-muted">Total de associados</h6>
                                <h3 class="text-dark fw-bold"><?php echo number_format($stats['total_associados'], 0, ',', '.'); ?></h3>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="d-flex align-items-center justify-content-center flex-column">
                                <i class="fas fa-file-invoice-dollar fa-3x text-success mb-3"></i>
                                <h6 class="card-title text-muted">Cobranças emitidas</h6>
                                <h3 class="text-dark fw-bold"><?php echo number_format($stats['total_cobrancas'], 0, ',', '.'); ?></h3>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="d-flex align-items-center justify-content-center flex-column">
                                <i class="fas fa-money-bill-wave fa-3x text-info mb-3"></i>
                                <h6 class="card-title text-muted">Valor total emitido</h6>
                                <h3 class="text-dark fw-bold">R$ <?php echo number_format($stats['valor_total_cobrancas'], 2, ',', '.'); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Segmento: Pagamentos -->
    <div class="row mb-5">
        <div class="col-12">
            <h4 class="text-muted ">💳 <strong>Pagamentos realizados</strong></h4>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3 text-center">
                    <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                    <h6 class="card-title text-muted">Pagos</h6>
                    <h3 class="text-dark fw-bold"><?php echo number_format($stats['total_pagos'], 0, ',', '.'); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3 text-center">
                    <i class="fas fa-wallet fa-2x text-primary mb-3"></i>
                    <h6 class="card-title text-muted">Total pago</h6>
                    <h3 class="text-dark fw-bold">R$ <?php echo number_format($stats['valor_total_pagos'], 2, ',', '.'); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3 text-center">
                    <i class="fas fa-chart-line fa-2x text-success mb-3"></i>
                    <h6 class="card-title text-muted">Percentual pago</h6>
                    <h3 class="text-dark fw-bold">
                    <?php 
                        if ($stats['total_cobrancas'] > 0) {
                            $percentual_pago = ($stats['total_pagos'] / $stats['total_cobrancas']) * 100;
                        } else {
                            $percentual_pago = 0; // Evita divisão por zero
                        }
                        echo number_format($percentual_pago, 2, ',', '.') . '%';
                    ?>

                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Segmento: Pendências -->
    <div class="row mb-5">
        <div class="col-12">
            <h4 class="text-muted ">⏳ <strong>Aguardando pagamento</strong></h4>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3 text-center">
                    <i class="fas fa-clock fa-2x text-warning mb-3"></i>
                    <h6 class="card-title text-muted">Pendentes</h6>
                    <h3 class="text-dark fw-bold"><?php echo number_format($stats['total_pendentes'], 0, ',', '.'); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3 text-center">
                    <i class="fas fa-money-bill-wave-alt fa-2x text-danger mb-3"></i>
                    <h6 class="card-title text-muted">Total pendente</h6>
                    <h3 class="text-dark fw-bold">R$ <?php echo number_format($stats['valor_total_pendentes'], 2, ',', '.'); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3 text-center">
                    <i class="fas fa-chart-pie fa-2x text-warning mb-3"></i>
                    <h6 class="card-title text-muted">Percentual pendente</h6>
                    <h3 class="text-dark fw-bold">
                    <?php 
                        if ($stats['total_cobrancas'] > 0) {
                            $percentual_pendente = ($stats['total_pendentes'] / $stats['total_cobrancas']) * 100;
                        } else {
                            $percentual_pendente = 0; // Evita divisão por zero
                        }
                        echo number_format($percentual_pendente, 2, ',', '.') . '%';
                    ?>

                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Segmento: Atrasados -->
    <div class="row mb-5">
        <div class="col-12">
            <h4 class="text-muted ">📅 <strong>Atrasos</strong></h4>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3 text-center">
                    <i class="fas fa-calendar-times fa-2x text-danger mb-3"></i>
                    <h6 class="card-title text-muted">Atrasados</h6>
                    <h3 class="text-dark fw-bold"><?php echo number_format($stats['total_atrasadas'], 0, ',', '.'); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3 text-center">
                    <i class="fas fa-file-invoice fa-2x text-danger mb-3"></i>
                    <h6 class="card-title text-muted">Total atrasados</h6>
                    <h3 class="text-dark fw-bold">R$ <?php echo number_format($stats['valor_total_atrasadas'], 2, ',', '.'); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3 text-center">
                    <i class="fas fa-chart-bar fa-2x text-danger mb-3"></i>
                    <h6 class="card-title text-muted">Percentual atrasado</h6>
                    <h3 class="text-dark fw-bold">
                    <?php 
                        if ($stats['total_cobrancas'] > 0) {
                            $percentual_vencido = ($stats['total_atrasadas'] / $stats['total_cobrancas']) * 100;
                        } else {
                            $percentual_vencido = 0; // Evita divisão por zero
                        }
                        echo number_format($percentual_vencido, 2, ',', '.') . '%';
                    ?>

                    </h3>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Seção de Listas -->
<div class="row g-3">
    <!-- Cobranças Recentes -->
    <div class="col-12 col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Pagamentos recentes</h5>
            </div>
            <div class="card-body">
                <?php if (empty($pagamentos_recentes)): ?>
                    <div class="text-center py-4">
                        <div class="text-muted">
                            <i class='bx bx-receipt fs-1'></i>
                            <p class="mt-2">Nenhuma cobrança encontrada</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Associado</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagamentos_recentes as $cobranca): ?>
                                <tr>
                                    <td>
                                        <a href="associado-detalhes.php?id=<?php echo $cobranca['associado_id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($cobranca['associado_nome']); ?>
                                        </a>
                                    </td>
                                    <td>R$ <?php echo number_format($cobranca['valor'], 2, ',', '.'); ?></td>
                                    <td>
                                        <span class="text-truncate badge <?php echo $cobranca['status_cobranca'] === 'RECEIVED' || $cobranca['status_cobranca'] === 'CONFIRMED' || $cobranca['status_cobranca'] === 'RECEIVED_IN_CASH' ? 'bg-success' : 'bg-warning'; ?>">
                                            <?php echo $cobranca['status_cobranca']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($cobranca['vencimento'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Próximos Vencimentos -->
    <div class="col-12 col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Próximos Vencimentos</h5>
            </div>
            <div class="card-body">
                <?php if (empty($proximos_vencimentos)): ?>
                    <div class="text-center py-4">
                        <div class="text-muted">
                            <i class='bx bx-calendar fs-1'></i>
                            <p class="mt-2">Nenhum vencimento próximo</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Associado</th>
                                    <th>Valor</th>
                                    <th>Vencimento</th>
                                    <th>Dias</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proximos_vencimentos as $vencimento): ?>
                                <tr>
                                    <td>
                                        <a href="associado-detalhes.php?id=<?php echo $vencimento['associado_id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($vencimento['associado_nome']); ?>
                                        </a>
                                    </td>
                                    <td>R$ <?php echo number_format($vencimento['valor'], 2, ',', '.'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($vencimento['vencimento'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo $vencimento['dias_vencimento'] <= 3 ? 'bg-danger' : 'bg-warning'; ?>">
                                            <?php echo $vencimento['dias_vencimento']; ?> dias
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$page_content = ob_get_clean();

// Incluir o template
require_once 'includes/template.php';
?>
