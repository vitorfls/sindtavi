<?php
session_start();
require_once __DIR__ . '/src/Sindicato.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $associados = new Associados();
        $criarAsaas = isset($_POST['criar_asaas']) && $_POST['criar_asaas'] === '1';

        $dados = [
            'nome' => $_POST['nome'],
            'email' => $_POST['email'],
            'telefone' => $_POST['telefone'],
            'cpf_cnpj' => preg_replace('/[^0-9]/', '', $_POST['cpf_cnpj']),
            'tipo' => $_POST['tipo'],
            'ponto' => $_POST['ponto'],
            'ponto_logradouro' => $_POST['ponto_logradouro'],
            'ponto_municipio' => $_POST['ponto_municipio'],
            'ponto_uf' => $_POST['ponto_uf'],
            'veiculo' => $_POST['veiculo'],
            'veiculo_marca' => $_POST['veiculo_marca'],
            'veiculo_cor' => $_POST['veiculo_cor'],
            'veiculo_combustivel' => $_POST['veiculo_combustivel'],
            'contrato' => $_POST['contrato'],
            'contrato_ano' => $_POST['contrato_ano'],
            'contrato_data_inicio' => $_POST['contrato_data_inicio'],
            'status' => $_POST['status'],
            // Campos de endereço
            'endereco' => $_POST['endereco'],
            'bairro' => $_POST['bairro'],
            'cidade' => $_POST['cidade'],
            'uf' => $_POST['uf'],
            'cep' => $_POST['cep']
        ];
        
        $associadoId = $associados->criar($dados, $criarAsaas);
        
        if ($associadoId) {
            header('Location: associado-detalhes.php?id=' . $associadoId);
            exit;
        }
    } catch (Exception $e) {
        $mensagem = 'Erro ao criar associado: ' . $e->getMessage();
    }
}

ob_start();
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2>Novo Associado</h2>
            <a href="associados.php" class="btn btn-secondary">
                <i class="bx bx-arrow-back"></i> Voltar
            </a>
        </div>
    </div>

    <?php if ($mensagem): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($mensagem); ?></div>
    <?php endif; ?>

    <form method="POST" class="needs-validation" novalidate>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Dados Pessoais</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nome" class="form-label">Nome Completo *</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="col-md-6">
                        <label for="cpf_cnpj" class="form-label">CPF/CNPJ *</label>
                        <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">E-mail *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="col-md-6">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="tel" class="form-control" id="telefone" name="telefone">
                    </div>
                    <div class="col-md-6">
                        <label for="tipo" class="form-label">Tipo de Associado *</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="">Selecione...</option>
                            <option value="D">Defensor</option>
                            <option value="P" selected=true>Permissionário</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title mb-4">Informações do Ponto</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="ponto" class="form-label">Nome do Ponto</label>
                        <input type="text" class="form-control" id="ponto" name="ponto">
                    </div>
                    <div class="col-md-6">
                        <label for="ponto_logradouro" class="form-label">Logradouro</label>
                        <input type="text" class="form-control" id="ponto_logradouro" name="ponto_logradouro">
                    </div>
                    <div class="col-md-6">
                        <label for="ponto_municipio" class="form-label">Município</label>
                        <input type="text" class="form-control" id="ponto_municipio" name="ponto_municipio">
                    </div>
                    <div class="col-md-6">
                        <label for="ponto_uf" class="form-label">UF</label>
                        <select class="form-select" id="ponto_uf" name="ponto_uf">
                            <option value="ES" selected>Espírito Santo</option>
                            <option value="MG">Minas Gerais</option>
                            <option value="RJ">Rio de Janeiro</option>
                            <option value="SP">São Paulo</option>
                            <option value="BA">Bahia</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title mb-4">Informações do Veículo</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="veiculo" class="form-label">Veículo</label>
                        <input type="text" class="form-control" id="veiculo" name="veiculo">
                    </div>
                    <div class="col-md-6">
                        <label for="veiculo_marca" class="form-label">Marca</label>
                        <input type="text" class="form-control" id="veiculo_marca" name="veiculo_marca">
                    </div>
                    <div class="col-md-6">
                        <label for="veiculo_cor" class="form-label">Cor</label>
                        <input type="text" class="form-control" id="veiculo_cor" name="veiculo_cor">
                    </div>
                    <div class="col-md-6">
                        <label for="veiculo_combustivel" class="form-label">Combustível</label>
                        <select class="form-select" id="veiculo_combustivel" name="veiculo_combustivel">
                            <option value="">Selecione...</option>
                            <option value="Gasolina">Gasolina</option>
                            <option value="Etanol">Etanol</option>
                            <option value="Diesel">Diesel</option>
                            <option value="GNV">GNV</option>
                            <option value="Flex">Flex</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title mb-4">Informações do Contrato</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="contrato" class="form-label">Número do Contrato</label>
                        <input type="text" class="form-control" id="contrato" name="contrato">
                    </div>
                    <div class="col-md-4">
                        <label for="contrato_ano" class="form-label">Ano do Contrato</label>
                        <input type="number" class="form-control" id="contrato_ano" name="contrato_ano" min="2000" max="2100">
                    </div>
                    <div class="col-md-4">
                        <label for="contrato_data_inicio" class="form-label">Data de Início</label>
                        <input type="date" class="form-control" id="contrato_data_inicio" name="contrato_data_inicio">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title mb-4">Endereço</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="endereco" class="form-label">Endereço</label>
                        <input type="text" class="form-control" id="endereco" name="endereco">
                    </div>
                    <div class="col-md-6">
                        <label for="bairro" class="form-label">Bairro</label>
                        <input type="text" class="form-control" id="bairro" name="bairro">
                    </div>
                    <div class="col-md-6">
                        <label for="cidade" class="form-label">Cidade</label>
                        <input type="text" class="form-control" id="cidade" name="cidade">
                    </div>
                    <div class="col-md-3">
                        <label for="uf" class="form-label">UF</label>
                        <select class="form-select" id="uf" name="uf">
                            <option value="">Selecione...</option>
                            <?php
                            $estados = [
                                'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 
                                'AM' => 'Amazonas', 'BA' => 'Bahia', 'CE' => 'Ceará',
                                'DF' => 'Distrito Federal', 'ES' => 'Espírito Santo',
                                'GO' => 'Goiás', 'MA' => 'Maranhão', 'MT' => 'Mato Grosso',
                                'MS' => 'Mato Grosso do Sul', 'MG' => 'Minas Gerais',
                                'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná',
                                'PE' => 'Pernambuco', 'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro',
                                'RN' => 'Rio Grande do Norte', 'RS' => 'Rio Grande do Sul',
                                'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina',
                                'SP' => 'São Paulo', 'SE' => 'Sergipe', 'TO' => 'Tocantins'
                            ];
                            foreach ($estados as $sigla => $nome) {
                                echo "<option value=\"$sigla\">$nome</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="cep" class="form-label">CEP</label>
                        <input type="text" class="form-control" id="cep" name="cep">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="criar_asaas" name="criar_asaas" value="1" checked>
                    <label class="form-check-label" for="criar_asaas">
                        Criar cliente no Asaas
                    </label>
                </div>
            </div>
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
            <button type="submit" class="btn btn-primary">
                <i class='bx bx-save'></i> Salvar Associado
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    
<script>
    $(document).ready(function() {
        $('#cpf_cnpj').mask('000.000.000-00');
        $('#telefone').mask('(00) 00000-0000');
        
        // Validação do formulário
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    });
</script>

<?php
$page_content = ob_get_clean();

// Incluir o template
require_once 'includes/template.php';
?>
