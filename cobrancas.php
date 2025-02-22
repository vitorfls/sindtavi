<?php
session_start();
require_once __DIR__ . '/src/Sindicato.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = Database::getInstance();

// Filtros
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
$data_fim = $_GET['data_fim'] ?? date('Y-m-t');

// Configuração da paginação
$pagina = (int)($_GET['pagina'] ?? 1);
$porPagina = 10;
$offset = ($pagina - 1) * $porPagina;

// Primeiro, contar o total de registros
$queryCount = "SELECT COUNT(*) as total FROM vw_cobrancas WHERE 1=1";
$paramsCount = [];

if ($status) {
    $queryCount .= " AND status_cobranca = ?";
    $paramsCount[] = $status;
}

if ($search) {
    $queryCount .= " AND (associado_nome LIKE ? OR associado_municipio LIKE ?)";
    $paramsCount[] = '%' . $search . '%';
    $paramsCount[] = '%' . $search . '%';
}

$queryCount .= " AND vencimento BETWEEN ? AND ?";
$paramsCount[] = $data_inicio;
$paramsCount[] = $data_fim;

$stmtCount = $pdo->prepare($queryCount);
$stmtCount->execute($paramsCount);
$totalRegistros = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($totalRegistros / $porPagina);

// Construir a query principal com LIMIT e OFFSET
$query = "SELECT * FROM vw_cobrancas WHERE 1=1";
$params = [];

if ($status) {
    $query .= " AND status_cobranca = ?";
    $params[] = $status;
}

if ($search) {
    $query .= " AND (associado_nome LIKE ? OR associado_municipio LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$query .= " AND vencimento BETWEEN ? AND ?";
$params[] = $data_inicio;
$params[] = $data_fim;

$query .= " ORDER BY vencimento DESC LIMIT $porPagina OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$cobrancas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Definir classes e textos para os status
$status_classes = [
    'CONFIRMED' => 'bg-success',
    'OVERDUE' => 'bg-danger',
    'PENDING' => 'bg-warning',
    'RECEIVED' => 'bg-success',
    'RECEIVED_IN_CASH' => 'bg-success'
];

$status_text = [
    'CONFIRMED' => 'Confirmado',
    'OVERDUE' => 'Vencido',
    'PENDING' => 'Pendente',
    'RECEIVED' => 'Recebido',
    'RECEIVED_IN_CASH' => 'Recebido em Dinheiro'
];

// Definir o título da página
$page_title = 'Cobranças - Sistema de Gestão';

// Capturar o conteúdo da página
ob_start();
?>

<!-- Cabeçalho da Página -->
<div class="page-header">
    <div>
        <h2 class="page-title">Cobranças</h2>
        <p class="page-subtitle">Gerenciar cobranças do sindicato</p>
    </div>
    <div class="page-header-actions">
        <a href="cobranca-nova.php" class="btn btn-primary">
            <i class='bx bx-plus'></i> Nova Cobrança
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Todos os Status</option>
                    <option value="PENDING" <?php echo $status === 'PENDING' ? 'selected' : ''; ?>>Pendente</option>
                    <option value="RECEIVED" <?php echo $status === 'RECEIVED' ? 'selected' : ''; ?>>Recebido</option>
                    <option value="CONFIRMED" <?php echo $status === 'CONFIRMED' ? 'selected' : ''; ?>>Confirmado</option>
                    <option value="OVERDUE" <?php echo $status === 'OVERDUE' ? 'selected' : ''; ?>>Vencido</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class='bx bx-search'></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Nome do associado..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Data Inicial</label>
                <input type="date" class="form-control" name="data_inicio" value="<?php echo $data_inicio; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Data Final</label>
                <input type="date" class="form-control" name="data_fim" value="<?php echo $data_fim; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label d-none d-md-block">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Cobranças -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Associado</th>
                        <th>Tipo</th>
                        <th>Município</th>
                        <th>Valor</th>
                        <th>Vencimento</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cobrancas)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="text-muted">
                                <i class='bx bx-search-alt fs-4'></i>
                                <p class="mt-2">Nenhuma cobrança encontrada</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($cobrancas as $cobranca): ?>
                        <tr>
                            <td>
                                <a href="associado-detalhes.php?id=<?php echo $cobranca['associado_id']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($cobranca['associado_nome']); ?>
                                    <small class="d-block text-muted">
                                        <?php echo htmlspecialchars($cobranca['associado_tipo'] === 'D' ? 'Defensor' : 'Permissionário'); ?>
                                    </small>
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    <?php echo htmlspecialchars($cobranca['tipo_cobranca']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($cobranca['associado_municipio']); ?></td>
                            <td>R$ <?php echo number_format($cobranca['valor'], 2, ',', '.'); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($cobranca['vencimento'])); ?></td>
                            <td>
                                <span class="badge <?php echo $status_classes[$cobranca['status_cobranca']]; ?>">
                                    <?php echo $status_text[$cobranca['status_cobranca']]; ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <?php if ($cobranca['link_boleto']): ?>
                                    <a href="<?php echo htmlspecialchars($cobranca['link_boleto']); ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-primary"
                                       data-bs-toggle="tooltip"
                                       title="Baixar Boleto">
                                        <i class='bx bx-download'></i>
                                    </a>
                                <?php endif; ?>
                                <a href="cobranca-detalhes.php?id=<?php echo $cobranca['id']; ?>" 
                                   class="btn btn-sm btn-info"
                                   data-bs-toggle="tooltip"
                                   title="Ver Detalhes">
                                    <i class='bx bx-show'></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Paginação -->
<div class="d-flex justify-content-between align-items-center mt-4">
    <div>
        <p class="text-muted">Total de registros: <?php echo $totalRegistros; ?></p>
    </div>
    <?php if ($totalPaginas > 1): ?>
    <nav aria-label="Navegação de páginas">
        <ul class="pagination">
            <?php if ($pagina > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])); ?>">
                    Anterior
                </a>
            </li>
            <?php endif; ?>

            <?php
            $inicio = max(1, min($pagina - 2, $totalPaginas - 4));
            $fim = min($totalPaginas, max(5, $pagina + 2));
            
            if ($inicio > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => 1])); ?>">1</a>
                </li>
                <?php if ($inicio > 2): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $inicio; $i <= $fim; $i++): ?>
                <li class="page-item <?php echo $i === $pagina ? 'active' : ''; ?>">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>

            <?php if ($fim < $totalPaginas): ?>
                <?php if ($fim < $totalPaginas - 1): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $totalPaginas])); ?>">
                        <?php echo $totalPaginas; ?>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($pagina < $totalPaginas): ?>
            <li class="page-item">
                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])); ?>">
                    Próxima
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<?php
$page_content = ob_get_clean();

// Incluir o template
require_once 'includes/template.php';
?>
