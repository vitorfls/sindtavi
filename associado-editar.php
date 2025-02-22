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

$pdo = Database::getInstance();

// Buscar dados do associado
$stmt = $pdo->prepare("SELECT * FROM associados WHERE id = ?");
$stmt->execute([$id]);
$associado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$associado) {
    header('Location: associados.php');
    exit;
}

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect only the fields that exist in the database
    $dados = [
        'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING),
        'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
        'telefone' => filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING),
        'cpf_cnpj' => filter_input(INPUT_POST, 'cpf_cnpj', FILTER_SANITIZE_STRING),
        'tipo' => filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_STRING),
        'ponto' => filter_input(INPUT_POST, 'ponto', FILTER_SANITIZE_STRING),
        'ponto_uf' => filter_input(INPUT_POST, 'ponto_uf', FILTER_SANITIZE_STRING),
        'ponto_logradouro' => filter_input(INPUT_POST, 'ponto_logradouro', FILTER_SANITIZE_STRING),
        'ponto_municipio' => filter_input(INPUT_POST, 'ponto_municipio', FILTER_SANITIZE_STRING),
        'veiculo' => filter_input(INPUT_POST, 'veiculo', FILTER_SANITIZE_STRING),
        'veiculo_marca' => filter_input(INPUT_POST, 'veiculo_marca', FILTER_SANITIZE_STRING),
        'veiculo_combustivel' => filter_input(INPUT_POST, 'veiculo_combustivel', FILTER_SANITIZE_STRING),
        'veiculo_cor' => filter_input(INPUT_POST, 'veiculo_cor', FILTER_SANITIZE_STRING),
        'contrato' => filter_input(INPUT_POST, 'contrato', FILTER_SANITIZE_STRING),
        'contrato_ano' => filter_input(INPUT_POST, 'contrato_ano', FILTER_VALIDATE_INT),
        'contrato_data_inicio' => filter_input(INPUT_POST, 'contrato_data_inicio', FILTER_SANITIZE_STRING),
        'status' => isset($_POST['status']),
        // Novos campos de endereço
        'endereco' => filter_input(INPUT_POST, 'endereco', FILTER_SANITIZE_STRING),
        'bairro' => filter_input(INPUT_POST, 'bairro', FILTER_SANITIZE_STRING),
        'cidade' => filter_input(INPUT_POST, 'cidade', FILTER_SANITIZE_STRING),
        'uf' => filter_input(INPUT_POST, 'uf', FILTER_SANITIZE_STRING),
        'cep' => filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_STRING)
    ];
    
    
    try {
        $associados = new Associados();
        $associados->atualizar($id, $dados);

        $_SESSION['success'] = "Associado atualizado com sucesso!";
        header("Location: associado-detalhes.php?id=" . $id);
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Erro ao atualizar associado: " . $e->getMessage();
    }
}

// Definir o título da página
$page_title = 'Editar Associado - Sistema de Gestão';

// Capturar o conteúdo da página
ob_start();
?>

<!-- Cabeçalho da Página -->
<div class="page-header">
    <div>
        <h2 class="page-title">Editar Associado</h2>
        <p class="page-subtitle">Atualize as informações do associado</p>
    </div>

</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="" method="POST" class="needs-validation" novalidate>
                    <!-- Status -->
                    <div class="form-check form-switch mb-4">
                        <input type="checkbox" class="form-check-input" id="status" name="status"
                               <?php echo $associado['status'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="status">Associado Ativo</label>
                    </div>

                    <!-- Informações Pessoais -->
                    <h5 class="card-title mb-4">Informações Pessoais</h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nome" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="nome" name="nome" 
                                   value="<?php echo htmlspecialchars($associado['nome']); ?>" required>
                            <div class="invalid-feedback">
                                Por favor, informe o nome completo.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="cpf_cnpj" class="form-label">CPF/CNPJ</label>
                            <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj" 
                                   value="<?php echo htmlspecialchars($associado['cpf_cnpj']); ?>" required>
                            <div class="invalid-feedback">
                                Por favor, informe o CPF ou CNPJ.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($associado['email']); ?>" required>
                            <div class="invalid-feedback">
                                Por favor, informe um e-mail válido.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="tel" class="form-control" id="telefone" name="telefone" 
                                   value="<?php echo htmlspecialchars($associado['telefone']); ?>" required>
                            <div class="invalid-feedback">
                                Por favor, informe um telefone válido.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="">Selecione...</option>
                                <option value="D" <?php echo $associado['tipo'] === 'D' ? 'selected' : ''; ?>>Defensor</option>
                                <option value="E" <?php echo $associado['tipo'] === 'P' ? 'selected' : ''; ?>>Permissionário</option>
                            </select>
                            <div class="invalid-feedback">
                                Por favor, selecione o tipo.
                            </div>
                        </div>
                    </div>

                    <!-- Ponto/Local -->
                    <h5 class="card-title mb-4 mt-5">Informações do Ponto</h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="ponto" class="form-label">Ponto</label>
                            <input type="text" class="form-control" id="ponto" name="ponto" 
                                   value="<?php echo htmlspecialchars($associado['ponto']); ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="ponto_uf" class="form-label">UF do Ponto</label>
                            <select class="form-select" id="ponto_uf" name="ponto_uf">
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
                                foreach ($estados as $uf => $nome) {
                                    $selected = $associado['ponto_uf'] === $uf ? 'selected' : '';
                                    echo "<option value=\"$uf\" $selected>$nome</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="ponto_logradouro" class="form-label">Logradouro do Ponto</label>
                            <input type="text" class="form-control" id="ponto_logradouro" name="ponto_logradouro" 
                                   value="<?php echo htmlspecialchars($associado['ponto_logradouro']); ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="ponto_municipio" class="form-label">Município do Ponto</label>
                            <input type="text" class="form-control" id="ponto_municipio" name="ponto_municipio" 
                                   value="<?php echo htmlspecialchars($associado['ponto_municipio']); ?>">
                        </div>
                    </div>

                    <!-- Veículo -->
                    <h5 class="card-title mb-4 mt-5">Informações do Veículo</h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="veiculo" class="form-label">Veículo</label>
                            <input type="text" class="form-control" id="veiculo" name="veiculo" 
                                   value="<?php echo htmlspecialchars($associado['veiculo']); ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="veiculo_marca" class="form-label">Marca do Veículo</label>
                            <input type="text" class="form-control" id="veiculo_marca" name="veiculo_marca" 
                                   value="<?php echo htmlspecialchars($associado['veiculo_marca']); ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="veiculo_combustivel" class="form-label">Combustível</label>
                            <select class="form-select" id="veiculo_combustivel" name="veiculo_combustivel">
                                <option value="">Selecione...</option>
                                <option value="Gasolina" <?php echo $associado['veiculo_combustivel'] === 'Gasolina' ? 'selected' : ''; ?>>Gasolina</option>
                                <option value="Etanol" <?php echo $associado['veiculo_combustivel'] === 'Etanol' ? 'selected' : ''; ?>>Etanol</option>
                                <option value="Diesel" <?php echo $associado['veiculo_combustivel'] === 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                                <option value="GNV" <?php echo $associado['veiculo_combustivel'] === 'GNV' ? 'selected' : ''; ?>>GNV</option>
                                <option value="Flex" <?php echo $associado['veiculo_combustivel'] === 'Flex' ? 'selected' : ''; ?>>Flex</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="veiculo_cor" class="form-label">Cor do Veículo</label>
                            <input type="text" class="form-control" id="veiculo_cor" name="veiculo_cor" 
                                   value="<?php echo htmlspecialchars($associado['veiculo_cor']); ?>">
                        </div>
                    </div>

                    <!-- Contrato -->
                    <h5 class="card-title mb-4 mt-5">Informações do Contrato</h5>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="contrato" class="form-label">Contrato</label>
                            <input type="text" class="form-control" id="contrato" name="contrato" 
                                   value="<?php echo htmlspecialchars($associado['contrato']); ?>">
                        </div>

                        <div class="col-md-4">
                            <label for="contrato_ano" class="form-label">Ano do Contrato</label>
                            <input type="number" class="form-control" id="contrato_ano" name="contrato_ano" 
                                   value="<?php echo htmlspecialchars($associado['contrato_ano']); ?>">
                        </div>

                        <div class="col-md-4">
                            <label for="contrato_data_inicio" class="form-label">Data de Início</label>
                            <input type="date" class="form-control" id="contrato_data_inicio" name="contrato_data_inicio" 
                                   value="<?php echo htmlspecialchars($associado['contrato_data_inicio']); ?>">
                        </div>
                    </div>

                    <!-- Endereço -->
                    <h5 class="card-title mb-4 mt-4">Endereço</h5>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="endereco" class="form-label">Endereço</label>
                            <input type="text" class="form-control" id="endereco" name="endereco" 
                                   value="<?php echo htmlspecialchars($associado['endereco'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="bairro" class="form-label">Bairro</label>
                            <input type="text" class="form-control" id="bairro" name="bairro" 
                                   value="<?php echo htmlspecialchars($associado['bairro'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="cidade" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="cidade" name="cidade" 
                                   value="<?php echo htmlspecialchars($associado['cidade'] ?? ''); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="uf" class="form-label">UF</label>
                            <input type="text" class="form-control" id="uf" name="uf" maxlength="2" 
                                   value="<?php echo htmlspecialchars($associado['uf'] ?? ''); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="cep" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="cep" name="cep" 
                                   value="<?php echo htmlspecialchars($associado['cep'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-5">
                        <a href="associado-detalhes.php?id=<?php echo $id; ?>" class="btn btn-outline-secondary me-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary" onclick="this.innerHTML='<span class=\'spinner-border spinner-border-sm me-2\'></span>Enviando...'">
                            <i class='bx bx-save'></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Informações Adicionais</h5>

                <p class="text-muted">Data de Cadastro</p>
                <p class="mb-4"><?php echo date('d/m/Y', strtotime($associado['created_at'])); ?></p>
                 
                <div class="alert alert-info" role="alert">
                    <h6 class="alert-heading">
                        <i class='bx bx-info-circle'></i> Dica
                    </h6>
                    <p class="mb-0">
                        Mantenha os dados do associado sempre atualizados para garantir uma comunicação eficiente.
                    </p>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Anexos</h5>
            </div>
            <div class="card-body">
                <?php
                $anexos = new Anexos();
                $listaAnexos = $anexos->listar($associado['id']);
                ?>

                <form action="associados-upload.php" method="post" enctype="multipart/form-data" class="mb-4">
                    <input type="hidden" name="associado_id" value="<?php echo $associado['id']; ?>">
                    
                    <?php if (empty($listaAnexos)): ?>
                    <!-- Card de Upload -->
                    <div id="upload-card" class="text-center py-5">
                        <div class="card border-0">
                            <div class="card-body text-center py-5">
                                <i class="bx bx-file text-muted" style="font-size: 48px;"></i>
                                <h5 class="mt-3">Nenhum anexo encontrado</h5>
                                <p class="text-muted">Clique no botão abaixo para adicionar um anexo</p>
                                <label for="arquivo" class="btn btn-primary mt-2">
                                    <i class="bx bx-upload me-2"></i>Adicionar Anexo
                                </label>
                                <input type="file" class="d-none" name="arquivo" id="arquivo">
                            </div>
                        </div>
                    </div>

                    <!-- Card de Preview -->
                    <div id="preview-card" class="d-none">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="card-title mb-0">Preview do Arquivo</h6>
                                    <button type="button" class="btn-close" id="cancel-preview"></button>
                                </div>
                                <div class="mb-2">
                                    <strong>Nome:</strong> <span id="file-name"></span>
                                </div>
                                <div class="mb-2">
                                    <strong>Tamanho:</strong> <span id="file-size"></span>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary" onclick="this.innerHTML='<span class=\'spinner-border spinner-border-sm me-2\'></span>Enviando...'">
                                        <i class="bx bx-upload me-2"></i>Enviar Arquivo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="d-flex justify-content-end mb-3">
                        <div class="input-group" style="max-width: 400px;">
                            <input type="file" class="form-control" name="arquivo" id="arquivo">
                            <div id="preview-container" class="d-none position-absolute bg-white shadow rounded p-3" 
                                 style="top: 100%; right: 0; width: 100%; z-index: 1000; margin-top: 5px;">
                                <h6 class="border-bottom pb-2">Detalhes do Arquivo</h6>
                                <div class="mb-2">
                                    <strong>Nome:</strong> <span id="file-name"></span>
                                </div>
                                <div class="mb-2">
                                    <strong>Tamanho:</strong> <span id="file-size"></span>
                                </div>
                                <div class="mb-2">
                                    <strong>Tipo:</strong> <span id="file-type"></span>
                                </div>
                                <div class="mt-3">
                                    <button type="button" id="btn-confirmar-upload" class="btn btn-primary">
                                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        <span class="btn-text">Confirmar Upload</span>
                                    </button>
                                    <button type="button" id="btn-cancelar-upload" class="btn btn-secondary">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </form>
                <?php if (!empty($listaAnexos)): ?>
                    <div class="list-group">
                        <?php foreach ($listaAnexos as $anexo): ?>
                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-truncate d-inline-block" style="max-width: 150px;" title="<?php echo htmlspecialchars($anexo['nome']); ?>">
                                        <?php echo htmlspecialchars($anexo['nome']); ?>
                                    </span>
                                </div>
                                <div class="btn-group">
                                    <a href="associados-download.php?id=<?php echo $anexo['id']; ?>&associado_id=<?php echo $associado['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="Download">
                                        <i class="bx bx-download"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            onclick="excluirAnexo(<?php echo $anexo['id']; ?>)"
                                            title="Excluir">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Manipulação do arquivo e upload
    document.getElementById('arquivo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const previewContainer = document.getElementById('preview-container');
        
        if (file) {
            // Atualiza detalhes do arquivo
            document.getElementById('file-name').textContent = file.name;
            document.getElementById('file-size').textContent = formatFileSize(file.size);
            document.getElementById('file-type').textContent = file.type || 'Não identificado';
            
            // Mostra preview
            previewContainer.classList.remove('d-none');
        } else {
            // Esconde preview se nenhum arquivo selecionado
            previewContainer.classList.add('d-none');
        }
    });

    // Manipulação do upload
    document.getElementById('btn-confirmar-upload').addEventListener('click', function() {
        const fileInput = document.getElementById('arquivo');
        const file = fileInput.files[0];
        
        if (!file) {
            alert('Por favor, selecione um arquivo primeiro.');
            return;
        }

        const formData = new FormData();
        formData.append('arquivo', file);
        formData.append('associado_id', '<?php echo $associado['id']; ?>');

        // Atualiza interface durante upload
        const btn = this;
        btn.disabled = true;
        btn.querySelector('.spinner-border').classList.remove('d-none');
        btn.querySelector('.btn-text').textContent = 'Enviando...';

        // Criar um form temporário e enviar
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'associados-upload.php';
        form.enctype = 'multipart/form-data';
        
        // Adicionar o arquivo
        const fileClone = fileInput.cloneNode(true);
        fileClone.name = 'arquivo';
        form.appendChild(fileClone);
        
        // Adicionar o ID do associado
        const associadoInput = document.createElement('input');
        associadoInput.type = 'hidden';
        associadoInput.name = 'associado_id';
        associadoInput.value = '<?php echo $associado['id']; ?>';
        form.appendChild(associadoInput);
        
        // Adicionar ao documento e enviar
        document.body.appendChild(form);
        form.submit();
    });

    // Cancelar upload
    document.getElementById('btn-cancelar-upload').addEventListener('click', function() {
        document.getElementById('arquivo').value = '';
        document.getElementById('preview-container').classList.add('d-none');
    });

    function resetUploadButton() {
        const btn = document.getElementById('btn-confirmar-upload');
        btn.disabled = false;
        btn.querySelector('.spinner-border').classList.add('d-none');
        btn.querySelector('.btn-text').textContent = 'Confirmar Upload';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Exclusão de anexo
    function excluirAnexo(id) {
        if (confirm('Tem certeza que deseja excluir este anexo?')) {
            window.location.href = `associados-download.php?id=${id}&associado_id=<?php echo $associado['id']; ?>&excluir=true`;
        }
    }
</script>

<script>
    document.getElementById('arquivo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Atualiza as informações do preview
            document.getElementById('file-name').textContent = file.name;
            document.getElementById('file-size').textContent = formatFileSize(file.size);
            
            // Mostra o card de preview e esconde o card de upload
            document.getElementById('upload-card').classList.add('d-none');
            document.getElementById('preview-card').classList.remove('d-none');
        }
    });

    // Cancelar preview
    document.getElementById('cancel-preview').addEventListener('click', function() {
        // Limpa o input file
        document.getElementById('arquivo').value = '';
        
        // Esconde o card de preview e mostra o card de upload
        document.getElementById('preview-card').classList.add('d-none');
        document.getElementById('upload-card').classList.remove('d-none');
    });

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
</script>

<?php
$page_content = ob_get_clean();
// Incluir o template
require_once 'includes/template.php';
?>
