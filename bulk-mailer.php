<?php
session_start();
require_once __DIR__ . '/src/Sindicato.php';
require_once __DIR__ . '/src/BulkMailer.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        $bulkMailer = new BulkMailer();
        
        if (isset($_POST['action']) && $_POST['action'] === 'enviar_cobranca') {
            // Processar envio de email de cobrança
            $params = [
                'dias_ate_vencimento' => (int)$_POST['dias_ate_vencimento'],
                'template_name' => 'enviar-email-cobranca.html' // Template fixo para este tipo de email
            ];
            
            $resultados = $bulkMailer->enviarEmailCobranca($params);
            
            if (!empty($resultados)) {
                foreach ($resultados as $resultado) {
                    if ($resultado['sucesso']) {
                        $message .= "Email enviado com sucesso para {$resultado['nome']} ({$resultado['email']})<br>";
                    } else {
                        $error .= "Falha ao enviar email para {$resultado['nome']} ({$resultado['email']}): {$resultado['mensagem']}<br>";
                    }
                }
            } else {
                $message = "Nenhuma cobrança encontrada para envio de emails no período especificado.";
            }
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Definir o título da página
$page_title = 'Disparador de Emails';

// Capturar o conteúdo da página
ob_start();
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Disparador de envio de emails</h2>
        <p class="page-subtitle">Enviar emails para associados</p>
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
        <div class="accordion" id="emailAccordion">
            <!-- Accordion Item 1: Email de Cobrança -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingCobranca">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCobranca" aria-expanded="true" aria-controls="collapseCobranca">
                        Email de Cobrança
                    </button>
                </h2>
                <div id="collapseCobranca" class="accordion-collapse collapse show" aria-labelledby="headingCobranca" data-bs-parent="#emailAccordion">
                    <div class="accordion-body">
                        <form method="POST" class="form-container" id="cobrancaForm">
                            <input type="hidden" name="action" value="enviar_cobranca">
                            
                            <div class="form-group mb-3">
                                <label for="dias_ate_vencimento">Dias até o vencimento</label>
                                <input type="number" class="form-control" id="dias_ate_vencimento" name="dias_ate_vencimento" value="5" min="1" max="30" required>
                                <small class="form-text text-muted">Enviar para cobranças que vencem em até X dias</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" id="submitCobranca">
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <span>Enviar Emails de Cobrança</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('cobrancaForm').addEventListener('submit', function(e) {
    const btn = this.querySelector('button[type="submit"]');
    const spinner = btn.querySelector('.spinner-border');
    const btnText = btn.querySelector('span:not(.spinner-border)');
    
    // Desabilita o botão
    btn.disabled = true;
    
    // Mostra o spinner
    spinner.classList.remove('d-none');
    
    // Muda o texto do botão
    btnText.textContent = 'Enviando...';
});
</script>

<?php
$page_content = ob_get_clean();

// Incluir o template
require_once 'includes/template.php';
?>
