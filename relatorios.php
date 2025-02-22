<?php
session_start();
require_once __DIR__ . '/src/Sindicato.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = Database::getInstance();

// Buscar tipos de cobrança para o filtro
$sql = "SELECT id, nome FROM tipos_cobranca ORDER BY nome";
$stmt = $pdo->query($sql);
$tiposCobranca = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Relatórios</h2>
        </div>
    </div>

    <div class="row">
        <!-- Relatório de Arrecadação -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Relatório de Arrecadação</h5>
                    <p class="card-text">Visualize os pagamentos recebidos em um período específico.</p>
                    <form action="relatorio-arrecadacao.php" method="POST" target="_blank">
                        <div class="mb-3">
                            <label for="data_inicio_arrecadacao" class="form-label">Data Início</label>
                            <input type="date" class="form-control" id="data_inicio_arrecadacao" name="data_inicio" required>
                        </div>
                        <div class="mb-3">
                            <label for="data_fim_arrecadacao" class="form-label">Data Fim</label>
                            <input type="date" class="form-control" id="data_fim_arrecadacao" name="data_fim" required>
                        </div>
                        <div class="mb-3">
                            <label for="tipo_cobranca_arrecadacao" class="form-label">Tipo de Cobrança</label>
                            <select class="form-select" id="tipo_cobranca_arrecadacao" name="tipo_cobranca">
                                <option value="">Todos</option>
                                <?php foreach ($tiposCobranca as $tipo): ?>
                                    <option value="<?php echo $tipo['id']; ?>"><?php echo htmlspecialchars($tipo['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Gerar Relatório</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Relatório de Inadimplência -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Relatório de Inadimplência</h5>
                    <p class="card-text">Visualize as cobranças vencidas em um período específico.</p>
                    <form action="relatorio-inadimplencia.php" method="POST" target="_blank">
                        <div class="mb-3">
                            <label for="data_inicio_inadimplencia" class="form-label">Data Início</label>
                            <input type="date" class="form-control" id="data_inicio_inadimplencia" name="data_inicio" required>
                        </div>
                        <div class="mb-3">
                            <label for="data_fim_inadimplencia" class="form-label">Data Fim</label>
                            <input type="date" class="form-control" id="data_fim_inadimplencia" name="data_fim" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Gerar Relatório</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Relatório de Status de Pagamentos -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Status de Pagamentos</h5>
                    <p class="card-text">Visualize o status de todas as cobranças em um período.</p>
                    <form action="relatorio-status-pagamentos.php" method="POST" target="_blank">
                        <div class="mb-3">
                            <label for="data_inicio_status" class="form-label">Data Início</label>
                            <input type="date" class="form-control" id="data_inicio_status" name="data_inicio" required>
                        </div>
                        <div class="mb-3">
                            <label for="data_fim_status" class="form-label">Data Fim</label>
                            <input type="date" class="form-control" id="data_fim_status" name="data_fim" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Gerar Relatório</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Define data mínima como primeiro dia do mês atual
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        
        const formatDate = date => date.toISOString().split('T')[0];
        
        // Configurar datas padrão para todos os formulários
        const startInputs = document.querySelectorAll('input[name="data_inicio"]');
        const endInputs = document.querySelectorAll('input[name="data_fim"]');
        
        startInputs.forEach(input => {
            input.value = formatDate(firstDay);
        });
        
        endInputs.forEach(input => {
            input.value = formatDate(lastDay);
        });
    });
</script>

<?php
$page_content = ob_get_clean();
require_once 'includes/template.php';
?>
