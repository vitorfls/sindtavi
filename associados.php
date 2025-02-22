<?php
session_start();
require_once __DIR__ . '/src/Sindicato.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$associados = new Associados();

// Filtro
$nome = $_GET['nome'] ?? '';
$pagina = (int)($_GET['pagina'] ?? 1);
$porPagina = 10;

$resultado = $associados->listar(
    "nome LIKE '%" . $nome . "%' ORDER BY nome",
    $pagina,
    $porPagina
);

$lista = $resultado['registros'];
$totalPaginas = $resultado['paginas'];
$total = $resultado['total'];

// Definir o título da página
$page_title = 'Associados - Sistema de Gestão';

// Capturar o conteúdo da página
ob_start();
?>

<!-- Cabeçalho da Página -->
<div class="page-header">
    <div>
        <h2 class="page-title">Associados</h2>
        <p class="page-subtitle">Gerenciar associados do sindicato</p>
    </div>
    <div class="page-header-actions">
        <a href="associado-novo.php" class="btn btn-primary">
            <i class='bx bx-plus'></i> 
        </a>
    </div>
</div>

<!-- Filtro de Busca -->
<div class="card mb-4">
    <div class="card-body">
        <form class="row g-3">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text"><i class='bx bx-search'></i></span>
                    <input type="text" class="form-control" name="nome" placeholder="Buscar por nome" value="<?php echo htmlspecialchars($nome); ?>">
                </div>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">
                    Buscar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Associados -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <p>Total de registros: <?php echo $total; ?></p>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Tipo</th>
                        <th>Município</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lista)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">
                                <i class='bx bx-search-alt fs-4'></i>
                                <p class="mt-2">Nenhum associado encontrado</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($lista as $associado): ?>
                        <tr>
                            <td>
                                <a href="associado-detalhes.php?id=<?php echo $associado['id']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($associado['nome']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($associado['cpf_cnpj']); ?></td>
                            <td>
                                <span class="badge bg-primary">
                                    <?php echo htmlspecialchars($associado['tipo']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($associado['ponto_municipio']); ?></td>
                            <td>
                                <span class="badge <?php echo $associado['status'] ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $associado['status'] ? 'Ativo' : 'Inativo'; ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="associado-detalhes.php?id=<?php echo $associado['id']; ?>" 
                                   class="btn btn-sm btn-primary" 
                                   data-bs-toggle="tooltip" 
                                   title="Ver detalhes">
                                    <i class='bx bx-show'></i>
                                </a>
                                <a href="associado-editar.php?id=<?php echo $associado['id']; ?>" 
                                   class="btn btn-sm btn-warning" 
                                   data-bs-toggle="tooltip" 
                                   title="Editar">
                                    <i class='bx bx-edit'></i>
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
<?php if ($totalPaginas > 1): ?>
<nav aria-label="Navegação de página">
    <ul class="pagination">
        <?php if ($pagina > 1): ?>
        <li class="page-item">
            <a class="page-link" href="?pagina=<?php echo ($pagina - 1); ?>&nome=<?php echo urlencode($nome); ?>">Anterior</a>
        </li>
        <?php endif; ?>

        <?php for ($i = max(1, $pagina - 2); $i <= min($totalPaginas, $pagina + 2); $i++): ?>
        <li class="page-item <?php echo $i === $pagina ? 'active' : ''; ?>">
            <a class="page-link" href="?pagina=<?php echo $i; ?>&nome=<?php echo urlencode($nome); ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>

        <?php if ($pagina < $totalPaginas): ?>
        <li class="page-item">
            <a class="page-link" href="?pagina=<?php echo ($pagina + 1); ?>&nome=<?php echo urlencode($nome); ?>">Próxima</a>
        </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>

<?php
$page_content = ob_get_clean();

// Incluir o template
require_once 'includes/template.php';
?>
