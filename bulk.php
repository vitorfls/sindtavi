<?php
session_start();
require_once __DIR__ . '/src/Sindicato.php';
require_once __DIR__ . '/src/BulkHandler.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        
        $bulkHandler = new BulkHandler();
        $resultado = $bulkHandler->handle((int)$_POST['quantidade'] ?? 10);
        
        if ($resultado['processados'] > 0) {
            $message = "Processados com sucesso: {$resultado['processados']} associados";
        }
        
        if (!empty($resultado['erros'])) {
            $error = implode("<br>", $resultado['erros']);
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Definir o título da página
$page_title = 'Gerar Cobranças em Lote';

// Capturar o conteúdo da página
ob_start();
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Gerar Cobranças em Lote</h2>
        <p class="page-subtitle">Gerar cobranças para associados sem cobrança</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" class="form-container" id="bulkForm">
            <div class="form-group">
                <label for="quantidade">Quantidade de Associados</label>
                <input type="number" class="form-control" id="quantidade" name="quantidade" value="10" min="1" max="100">
                <small class="form-text text-muted">Número máximo de associados para processar por vez</small>
            </div>
            
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="loadingSpinner"></span>
                <span id="btnText">Gerar Cobranças</span>
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('bulkForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('submitBtn');
    const spinner = document.getElementById('loadingSpinner');
    const btnText = document.getElementById('btnText');
    
    // Desabilita o botão
    btn.disabled = true;
    
    // Mostra o spinner
    spinner.classList.remove('d-none');
    
    // Muda o texto do botão
    btnText.textContent = 'Processando...';
});
</script>

<?php
$page_content = ob_get_clean();

// Incluir o template
require_once 'includes/template.php';
?>
