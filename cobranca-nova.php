<?php
session_start();
require_once __DIR__ . '/src/Sindicato.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = Database::getInstance();
$associados = new Associados();
$associado_id = $_GET['associado_id'] ?? null;

// Buscar tipos de cobrança
try {
    $tipos_cobranca = $associados->listarTiposCobranca();
} catch (Exception $e) {
    $error = 'Erro ao listar tipos de cobrança: ' . $e->getMessage();
    $tipos_cobranca = [];
}

if ($associado_id) {
    try {
        $associado = $associados->getById($associado_id);
        if (!$associado) {
            throw new RuntimeException("Associado não encontrado.");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: associados.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        
        // Converter valor para float
        $valor = str_replace(['R$', '.', ','], ['', '', '.'], $_POST['valor']);
        
        // Criar pagamento usando a classe Sindicato
        $associados->criarPagamento(
            (int) $_POST['associado_id'],
            $associado['email'],
            $associado['nome'],
            (float) $valor,
            $_POST['descricao'],
            $_POST['vencimento'],
            (int) $_POST['tipo_cobranca_id'],
            false
        );

        $_SESSION['flash'] = 'Cobrança criada com sucesso!';
        header('Location: associado-detalhes.php?id=' . $_POST['associado_id']);
        exit;

    } catch (RuntimeException $e) {

        $_SESSION['error'] = $e->getMessage();
        // Mantém o usuário na página para ver o erro
        $error = $e->getMessage();

    } catch (Exception $e) {
        
        $_SESSION['error'] = "Erro ao criar cobrança: " . $e->getMessage();
        // Mantém o usuário na página para ver o erro
        $error = "Erro ao criar cobrança: " . $e->getMessage();
    }
}

// Buscar todos associados para o select se não tiver associado_id
if (!$associado_id) {
    try {
        $associados_list = $associados->listarAtivos();
    } catch (Exception $e) {
        $error = 'Erro ao listar associados: ' . $e->getMessage();
        $associados_list = [];
    }
}

// Definir o título da página
$page_title = 'Nova Cobrança - Sistema de Gestão';

// Scripts específicos para esta página
$extra_scripts = <<<HTML
<script>
    // Validação do formulário
    function validateForm(form) {
        const valor = form.valor.value.replace(/[^\d,]/g, '').replace(',', '.');
        if (isNaN(valor) || valor <= 0) {
            alert('Por favor, insira um valor válido maior que zero.');
            form.valor.focus();
            return false;
        }

        const vencimento = new Date(form.vencimento.value);
        const hoje = new Date();
        hoje.setHours(0, 0, 0, 0);
        
        if (vencimento < hoje) {
            alert('A data de vencimento não pode ser anterior a hoje.');
            form.vencimento.focus();
            return false;
        }

        return true;
    }

    // Máscara para o campo de valor
    document.addEventListener('DOMContentLoaded', function() {
        const valorInput = document.getElementById('valor');
        valorInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = (parseInt(value) / 100).toFixed(2);
            e.target.value = value.replace('.', ',');
            e.target.value = 'R$ ' + e.target.value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        });
    });
</script>
HTML;

// Capturar o conteúdo da página
ob_start();
?>

<!-- Cabeçalho da Página -->
<div class="page-header">
    <div>
        <h2 class="page-title">Nova Cobrança</h2>
        <p class="page-subtitle">Criar uma nova cobrança para associado</p>
    </div>

</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class='bx bx-error-circle'></i>
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<!-- Formulário de Nova Cobrança -->
<div class="card">
    <div class="card-body">
        <form action="" method="POST" class="form-container" onsubmit="return validateForm(this)">
            <?php if ($associado_id): ?>
                <input type="hidden" name="associado_id" value="<?php echo $associado_id; ?>">
                <div class="mb-3">
                    <label class="form-label">Associado</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($associado['nome']); ?>" readonly>
                </div>
            <?php else: ?>
                <div class="mb-3">
                    <label for="associado_id" class="form-label">Associado</label>
                    <select name="associado_id" id="associado_id" class="form-select" required>
                        <option value="">Selecione um associado</option>
                        <?php foreach ($associados_list as $associado): ?>
                            <option value="<?php echo $associado['id']; ?>">
                                <?php echo htmlspecialchars($associado['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label for="tipo_cobranca_id" class="form-label">Tipo de Cobrança</label>
                <select name="tipo_cobranca_id" id="tipo_cobranca_id" class="form-select" required>
                    <?php foreach ($tipos_cobranca as $tipo): ?>
                        <option value="<?php echo $tipo['id']; ?>" 
                                title="<?php echo htmlspecialchars($tipo['descricao']); ?>">
                            <?php echo htmlspecialchars($tipo['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="valor" class="form-label">Valor</label>
                <div class="input-group">
                    <span class="input-group-text">R$</span>
                    <input type="text" class="form-control" id="valor" name="valor" required
                           placeholder="0,00" data-mask="money">
                </div>
            </div>

            <div class="mb-3">
                <label for="vencimento" class="form-label">Data de Vencimento</label>
                <input type="date" class="form-control" id="vencimento" name="vencimento" required
                       min="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class='bx bx-check'></i> Criar Cobrança
                </button>
                <a href="<?php echo $associado_id ? 'associado-detalhes.php?id=' . $associado_id : 'cobrancas.php'; ?>" 
                   class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php
$page_content = ob_get_clean();

// Incluir o template
require_once 'includes/template.php';
?>
