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
$tipoCobranca = $_POST['tipo_cobranca'] ?? null;

if ($dataInicio && $dataFim) {
    $sql = "SELECT associado_nome as associado, valor, data_pagamento as data, tipo_cobranca
            FROM vw_cobrancas
            WHERE data_pagamento BETWEEN :data_inicio AND :data_fim
            AND status_cobranca_id IN (2, 3, 5)"; // RECEIVED, CONFIRMED, RECEIVED_IN_CASH
            
    if ($tipoCobranca) {
        $sql .= " AND tipo_cobranca_id = :tipo_cobranca";
    }
    
    $sql .= " ORDER BY data_pagamento DESC";
            
    $stmt = $pdo->prepare($sql);
    $params = [
        ':data_inicio' => $dataInicio,
        ':data_fim' => $dataFim
    ];
    
    if ($tipoCobranca) {
        $params[':tipo_cobranca'] = $tipoCobranca;
    }
    
    $stmt->execute($params);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Relatório de Arrecadação</title>
    <meta charset="utf-8">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1>Relatório de Arrecadação</h1>
        <?php if ($dataInicio && $dataFim): ?>
            <p>Período: <?php echo date('d/m/Y', strtotime($dataInicio)); ?> até <?php echo date('d/m/Y', strtotime($dataFim)); ?></p>
            
            <?php if (!empty($dados)): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Associado</th>
                            <th>Valor</th>
                            <th>Tipo Cobrança</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalArrecadado = 0;
                        foreach ($dados as $linha): 
                            $totalArrecadado += $linha['valor'];
                        ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($linha['data'])); ?></td>
                                <td><?php echo htmlspecialchars($linha['associado']); ?></td>
                                <td>R$ <?php echo number_format($linha['valor'], 2, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($linha['tipo_cobranca'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-info">
                            <td colspan="2"><strong>Total Arrecadado</strong></td>
                            <td colspan="2"><strong>R$ <?php echo number_format($totalArrecadado, 2, ',', '.'); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhum registro encontrado para o período selecionado.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
