<?php
session_start();
require_once __DIR__ . '/src/Sindicato.php';
require_once __DIR__ . '/src/Querys.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Validar parâmetros
$tipo = filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_STRING);
$dataInicio = filter_input(INPUT_POST, 'data_inicio', FILTER_SANITIZE_STRING);
$dataFim = filter_input(INPUT_POST, 'data_fim', FILTER_SANITIZE_STRING);

if (!$tipo || !$dataInicio || !$dataFim) {
    $_SESSION['error'] = "Parâmetros inválidos para geração do relatório.";
    header('Location: relatorios.php');
    exit;
}

$relatorios = new RelatoriosQuery();

try {
    $dados = [];
    switch ($tipo) {
        case 'inadimplencia':
            $dados = $relatorios->inadimplencia($dataInicio, $dataFim);
            $colunas = ['Associado', 'Valor', 'Vencimento', 'Status'];
            $titulo = 'Relatório de Inadimplência';
            break;

        case 'arrecadacao':
            $tipoCobranca = filter_input(INPUT_POST, 'tipo_cobranca', FILTER_VALIDATE_INT);
            $dados = $relatorios->arrecadacao($dataInicio, $dataFim, $tipoCobranca);
            $colunas = ['Data', 'Associado', 'Valor', 'Tipo Cobrança'];
            $titulo = 'Relatório de Arrecadação';
            break;

        case 'status_pagamentos':
            $dados = $relatorios->statusPagamentos($dataInicio, $dataFim);
            $colunas = ['Associado', 'Data', 'Valor', 'Status'];
            $titulo = 'Relatório de Status de Pagamentos';
            break;

        default:
            throw new Exception("Tipo de relatório inválido");
    }

    // Exibe o relatório em HTML
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title><?php echo $titulo; ?></title>
        <meta charset="utf-8">
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            h1 { color: #333; }
            .periodo { margin-bottom: 20px; color: #666; }
        </style>
    </head>
    <body>
        <h1><?php echo $titulo; ?></h1>
        <div class="periodo">
            Período: <?php echo date('d/m/Y', strtotime($dataInicio)); ?> até <?php echo date('d/m/Y', strtotime($dataFim)); ?>
        </div>

        <table>
            <thead>
                <tr>
                    <?php foreach ($colunas as $coluna): ?>
                        <th><?php echo $coluna; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dados as $linha): ?>
                    <tr>
                        <?php foreach ($linha as $valor): ?>
                            <td><?php 
                                if (strtotime($valor)) {
                                    echo date('d/m/Y', strtotime($valor));
                                } elseif (is_numeric($valor)) {
                                    echo 'R$ ' . number_format($valor, 2, ',', '.');
                                } else {
                                    echo htmlspecialchars($valor);
                                }
                            ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php

} catch (Exception $e) {
    $_SESSION['error'] = "Erro ao gerar relatório: " . $e->getMessage();
    header('Location: relatorios.php');
    exit;
}
