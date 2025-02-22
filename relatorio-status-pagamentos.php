<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'src/Sindicato.php';

$pdo = Database::getInstance();

$dataInicio = $_POST['data_inicio'] ?? '';
$dataFim = $_POST['data_fim'] ?? '';

if ($dataInicio && $dataFim) {
    $sql = "SELECT associado_nome as associado, data_pagamento as data, valor, status_cobranca as status
            FROM vw_cobrancas
            WHERE vencimento BETWEEN :data_inicio AND :data_fim
            ORDER BY vencimento DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':data_inicio' => $dataInicio,
        ':data_fim' => $dataFim
    ]);
    
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Relatório de Status de Pagamentos</title>
    <meta charset="utf-8">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h1 { color: #333; }
        .RECEIVED { color: green; }
        .PENDING { color: orange; }
        .OVERDUE { color: red; }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1>Relatório de Status de Pagamentos</h1>
        <?php if ($dataInicio && $dataFim): ?>
            <p>Período: <?php echo date('d/m/Y', strtotime($dataInicio)); ?> até <?php echo date('d/m/Y', strtotime($dataFim)); ?></p>
            
            <?php if (!empty($dados)): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Associado</th>
                            <th>Data</th>
                            <th>Valor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dados as $linha): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($linha['associado']); ?></td>
                                <td><?php echo $linha['data'] ? date('d/m/Y', strtotime($linha['data'])) : '-'; ?></td>
                                <td>R$ <?php echo number_format($linha['valor'], 2, ',', '.'); ?></td>
                                <td class="<?php echo $linha['status']; ?>"><?php 
                                    $status = [
                                        'RECEIVED' => 'Recebido',
                                        'PENDING' => 'Pendente',
                                        'OVERDUE' => 'Vencido'
                                    ];
                                    echo $status[$linha['status']] ?? $linha['status']; 
                                ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhum registro encontrado para o período selecionado.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
