<?php
session_start();
require_once __DIR__ . '/src/Sindicato.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: associados.php');
    exit;
}

try {
    $config = Config::load()['upload'];
    $anexos = new Anexos();

    // Validações básicas
    if (!isset($_FILES['arquivo']) || !isset($_POST['associado_id'])) {
        throw new RuntimeException('Parâmetros inválidos');
    }

    $arquivo = $_FILES['arquivo'];
    $associadoId = (int)$_POST['associado_id'];

    // Validações do arquivo
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Erro no upload do arquivo');
    }

    if ($arquivo['size'] > $config['tamanho_maximo']) {
        throw new RuntimeException('Arquivo muito grande. Máximo permitido: 5MB');
    }

    // Validar tipo do arquivo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $tipo = finfo_file($finfo, $arquivo['tmp_name']);
    finfo_close($finfo);

    if (!in_array($tipo, $config['tipos_permitidos'])) {
        throw new RuntimeException('Tipo de arquivo não permitido');
    }

    // Criar diretório se não existir
    $diretorioUpload = __DIR__ . '/' . $config['diretorio'];
    if (!is_dir($diretorioUpload)) {
        mkdir($diretorioUpload, 0777, true);
    }

    // Gerar nome único para o arquivo
    $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    $nomeArquivo = uniqid() . '.' . $extensao;
    $caminhoCompleto = $diretorioUpload . $nomeArquivo;

    // Mover arquivo
    if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
        throw new RuntimeException('Falha ao mover arquivo');
    }

    // Salvar no banco
    $dados = [
        'associado_id' => $associadoId,
        'nome' => $arquivo['name'],
        'arquivo' => $nomeArquivo,
        'tipo' => $tipo,
        'tamanho' => $arquivo['size']
    ];

    $anexos->criar($dados);

    $_SESSION['mensagem'] = 'Arquivo anexado com sucesso!';
    $_SESSION['tipo_mensagem'] = 'success';

} catch (Exception $e) {
    $_SESSION['mensagem'] = 'Erro: ' . $e->getMessage();
    $_SESSION['tipo_mensagem'] = 'danger';
}

// Redirecionar de volta
header('Location: associado-editar.php?id=' . $associadoId);
exit;
