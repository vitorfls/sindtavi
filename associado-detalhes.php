<?php
session_start();
require_once __DIR__ . '/src/Sindicato.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: associados.php');
    exit;
}

$associados = new Associados();
$associado = $associados->getById($id);

if (!$associado) {
    header('Location: associados.php');
    exit;
}

// Buscar cobranças do associado
$cobrancas = $associados->listarCobrancas($id);

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

// Calcular totais
$totais = [
    'total' => 0,
    'pagas' => 0,
    'pendentes' => 0,
    'atrasadas' => 0
];

foreach ($cobrancas as $cobranca) {
    $totais['total'] += 1; //$cobranca['valor'];
    
    if ($cobranca['status_cobranca'] === 'RECEIVED' || $cobranca['status_cobranca'] === 'RECEIVED_IN_CASH') {
        $totais['pagas'] += 1; //$cobranca['valor'];
    } elseif ($cobranca['status_cobranca'] === 'PENDING') {
        if ($cobranca['dias_vencimento'] < 0) {
            $totais['atrasadas'] += 1; //+= $cobranca['valor'];
        } else {
            $totais['pendentes'] += 1; //$cobranca['valor'];
        }
    }
}

// Definir o título da página
$page_title = 'Detalhes do Associado - Sistema de Gestão';

// Capturar o conteúdo da página
ob_start();
?>

<div class="container-fluid">

    <!-- Header destacado -->
<div class="row mb-4">
    <div class="col-12">
        
            <!-- Conteúdo Principal -->
            <div>
                <h2 class="mb-2"><?php echo htmlspecialchars($associado['nome']); ?></h2>
            </div>
        
    </div>
</div>


    <!-- Primeira linha de cards -->
    <div class="row mb-4">
        <!-- Card Informações do Associado -->
        <div class="col-md-6">
            <div class="card h-100">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Informações do Associado</h5>
                    <a href="associado-editar.php?id=<?php echo $id; ?>" class="btn btn-primary btn-sm">
                        <span class="fas fa-edit">Editar</span>
                    </a>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Tipo:</strong> <?php echo $associado['tipo'] == 'D' ? 'Defensor' : 'Permissionário'; ?>
                    </div>                      
                    <div class="mb-3">
                        <strong>Status:</strong> <?php echo $associado['status'] == '1' ? 'Ativo' : 'Inativo'; ?>
                    </div>                    
                    <div class="mb-3">
                        <strong>CPF/CNPJ:</strong> <?php echo htmlspecialchars($associado['cpf_cnpj']); ?>
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong> <?php echo htmlspecialchars($associado['email']); ?>
                    </div>
                    <div class="mb-3">
                        <strong>Telefone:</strong> <?php echo htmlspecialchars($associado['celular']); ?>
                    </div>
                    <div class="mb-3">
                        <strong>Data de Cadastro:</strong> <?php echo date('d/m/Y H:i', strtotime($associado['created_at'])); ?>
                    </div>
                                     
                </div>
            </div>
        </div>

        <!-- Card Detalhes Adicionais -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Detalhes Adicionais</h5>
                </div>
                <div class="card-body">
                    <!-- Accordion para todos os detalhes -->
                    <div class="accordion" id="accordionDetalhes">
                        <!-- Endereço -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEndereco">
                                    <i class="bx bx-map-pin me-2"></i>
                                    Endereço
                                </button>
                            </h2>
                            <div id="collapseEndereco" class="accordion-collapse collapse show" data-bs-parent="#accordionDetalhes">
                                <div class="accordion-body">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold">Endereço</label>
                                            <p class="mb-0"><?php echo htmlspecialchars($associado['endereco'] ?? '-'); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Bairro</label>
                                            <p class="mb-0"><?php echo htmlspecialchars($associado['bairro'] ?? '-'); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Cidade</label>
                                            <p class="mb-0"><?php echo htmlspecialchars($associado['cidade'] ?? '-'); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">UF</label>
                                            <p class="mb-0"><?php echo htmlspecialchars($associado['uf'] ?? '-'); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">CEP</label>
                                            <p class="mb-0"><?php echo htmlspecialchars($associado['cep'] ?? '-'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informações do Ponto -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePonto">
                                    <i class="bx bx-store me-2"></i>
                                    Informações do Ponto
                                </button>
                            </h2>
                            <div id="collapsePonto" class="accordion-collapse collapse" data-bs-parent="#accordionDetalhes">
                                <div class="accordion-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Ponto</label>
                                            <p class="mb-0"><?php echo htmlspecialchars($associado['ponto']) ?: '-'; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">UF</label>
                                            <p class="mb-0"><?php echo htmlspecialchars($associado['ponto_uf']) ?: '-'; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Logradouro</label>
                                            <p class="mb-0"><?php echo htmlspecialchars($associado['ponto_logradouro']) ?: '-'; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Município</label>
                                            <p class="mb-0"><?php echo htmlspecialchars($associado['ponto_municipio']) ?: '-'; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informações do Veículo -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVeiculo">
                                    <i class="bx bx-car me-2"></i>
                                    Veículo
                                </button>
                            </h2>
                            <div id="collapseVeiculo" class="accordion-collapse collapse" data-bs-parent="#accordionDetalhes">
                                <div class="accordion-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Veículo</label>
                                            <p class="mb-0"><?php echo htmlspecialchars($associado['veiculo']) ?: '-'; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Marca</label>
                                            <p class="mb-0"><?php echo htmlspecialchars($associado['veiculo_marca']) ?: '-'; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Combustível</label>
                                            <p class="mb-0"><?php echo htmlspecialchars($associado['veiculo_combustivel']) ?: '-'; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Cor</label>
                                            <p class="mb-0"><?php echo htmlspecialchars($associado['veiculo_cor']) ?: '-'; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informações do Contrato -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContrato">
                                    <i class="bx bx-file me-2"></i>
                                    Contrato
                                </button>
                            </h2>
                            <div id="collapseContrato" class="accordion-collapse collapse" data-bs-parent="#accordionDetalhes">
                                <div class="accordion-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Contrato</label>
                                            <p class="mb-0"><?php echo htmlspecialchars($associado['contrato']) ?: '-'; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Ano</label>
                                            <p class="mb-0"><?php echo htmlspecialchars($associado['contrato_ano']) ?: '-'; ?></p>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold">Data Início</label>
                                            <p class="mb-0">
                                                <?php echo $associado['contrato_data_inicio'] && $associado['contrato_data_inicio'] != '0000-00-00' 
                                                    ? date('d/m/Y', strtotime($associado['contrato_data_inicio'])) 
                                                    : '-'; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Segunda linha de cards -->
    <div class="row mb-4">
        <!-- Resumo Financeiro -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Resumo Financeiro</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Total de Cobranças</h6>
                                    <h2 class="mb-0"><?php echo $totais['total']; ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-success">
                                <div class="card-body">
                                    <h6 class="card-title">Cobranças Pagas</h6>
                                    <h2 class="mb-0"><?php echo $totais['pagas']; ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-warning">
                                <div class="card-body">
                                    <h6 class="card-title">Cobranças Pendentes</h6>
                                    <h2 class="mb-0"><?php echo $totais['pendentes']; ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-danger">
                                <div class="card-body">
                                    <h6 class="card-title">Cobranças Atrasadas</h6>
                                    <h2 class="mb-0"><?php echo $totais['atrasadas']; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Histórico de Cobranças -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Histórico de Cobranças</h5>
                    <a href="cobranca-nova.php?associado_id=<?php echo $id; ?>" class="btn btn-primary btn-sm">
                        Nova Cobrança
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($cobrancas)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Valor</th>
                                    <th>Vencimento</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cobrancas as $cobranca): ?>
                                <tr>
                                    <td>R$ <?php echo number_format($cobranca['valor'], 2, ',', '.'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($cobranca['vencimento'])); ?></td>
                                    <td><?php echo $cobranca['tipo_cobranca']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $status_classes[$cobranca['status_cobranca']] ?? 'bg-secondary'; ?>">
                                            <?php echo $status_text[$cobranca['status_cobranca']] ?? $cobranca['status_cobranca']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($cobranca['link_boleto'])): ?>
                                            <a href="<?php echo $cobranca['link_boleto']; ?>" target="_blank" class="btn btn-sm btn-secondary">
                                                <i class="bx bx-file"></i> Boleto
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="bx bx-receipt bx-lg mb-3 d-block"></i>
                        <p class="mb-0">Nenhuma cobrança encontrada</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Anexos -->
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title mb-4">Anexos</h5>
            <?php 
            $anexos = new Anexos();
            $listaAnexos = $anexos->listar($associado['id']);
            if (!empty($listaAnexos)): 
            ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listaAnexos as $anexo): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($anexo['nome']); ?></td>
                            <td><?php echo $anexo['data_upload']; ?></td>
                            <td>
                                <a href="associados-download.php?id=<?php echo $anexo['id']; ?>&associado_id=<?php echo $associado['id']; ?>" 
                                   class="btn btn-sm btn-info" 
                                   target="_blank">
                                    <i class="bx bx-show"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center text-muted py-4">
                <i class="bx bx-folder-open bx-lg mb-3 d-block"></i>
                <p class="mb-0">Nenhum anexo encontrado</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$page_content = ob_get_clean();
require_once 'includes/template.php';
?>
