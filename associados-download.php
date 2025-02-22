<?php
session_start();
require_once __DIR__ . '/src/Sindicato.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

function excluirAnexo(int $id, int $associadoId): void {
    $anexos = new Anexos();
    $anexos->excluir($id, $associadoId);
    $_SESSION['mensagem'] = 'Anexo excluído com sucesso!';
    $_SESSION['tipo_mensagem'] = 'success';
    header('Location: associado-editar.php?id=' . $associadoId);
    exit;
}

function downloadAnexo(int $id, int $associadoId): void {
    $config = Config::load()['upload'];
    $anexos = new Anexos();
    $anexo = $anexos->getById($id, $associadoId);

    if (!$anexo) {
        throw new RuntimeException('Anexo não encontrado');
    }

    $caminhoArquivo = __DIR__ . '/' . $config['diretorio'] . $anexo['arquivo'];
    if (!file_exists($caminhoArquivo)) {
        throw new RuntimeException('Arquivo não encontrado');
    }

    header('Content-Type: ' . $anexo['tipo']);
    header('Content-Disposition: inline; filename="' . $anexo['nome'] . '"');
    header('Content-Length: ' . filesize($caminhoArquivo));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    readfile($caminhoArquivo);
    exit;
}

try {

    if (!isset($_GET['id']) || !is_numeric($_GET['id']) || 
        !isset($_GET['associado_id']) || !is_numeric($_GET['associado_id'])) {
        throw new RuntimeException('Parâmetros inválidos');
    }

    $id = (int)$_GET['id'];
    $associadoId = (int)$_GET['associado_id'];
    $excluir = isset($_GET['excluir']) && $_GET['excluir'] === 'true';

    if ($excluir) {
        excluirAnexo($id, $associadoId);
    } else {
        downloadAnexo($id, $associadoId);
    }
    
} catch (Exception $e) {
    $_SESSION['mensagem'] = 'Erro: ' . $e->getMessage();
    $_SESSION['tipo_mensagem'] = 'danger';
    header('Location: associado-editar.php?id=' . ($associadoId ?? 0));
    exit;
}
