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
    $sql = "SELECT associado_nome as associado, valor, vencimento, tipo_cobranca, dias_vencimento
            FROM vw_cobrancas
            WHERE vencimento BETWEEN :data_inicio AND :data_fim
            AND status_cobranca_id = 4 -- OVERDUE
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
    <title>Relatório de Inadimplência</title>
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
        <h1>Relatório de Inadimplência</h1>
        <?php if ($dataInicio && $dataFim): ?>
            <p>Período: <?php echo date('d/m/Y', strtotime($dataInicio)); ?> até <?php echo date('d/m/Y', strtotime($dataFim)); ?></p>
            
            <?php if (!empty($dados)): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Associado</th>
                            <th>Vencimento</th>
                            <th>Dias Vencidos</th>
                            <th>Valor</th>
                            <th>Tipo Cobrança</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalInadimplencia = 0;
                        foreach ($dados as $linha): 
                            $totalInadimplencia += $linha['valor'];
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($linha['associado']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($linha['vencimento'])); ?></td>
                                <td><?php echo abs($linha['dias_vencimento']); ?> dias</td>
                                <td>R$ <?php echo number_format($linha['valor'], 2, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($linha['tipo_cobranca'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-danger">
                            <td colspan="3"><strong>Total Inadimplência</strong></td>
                            <td colspan="2"><strong>R$ <?php echo number_format($totalInadimplencia, 2, ',', '.'); ?></strong></td>
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
