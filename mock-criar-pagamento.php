<?php

require 'src/Sindicato.php';

try {
    // Instancia a classe de associados
    $associados = new Associados();

    // Dados de teste para criar um associado
    $dadosAssociado = [
        'nome' => 'Teste Mock',
        'email' => 'teste.mock@email.com',
        'telefone' => '11999999999',
        'cpf_cnpj' => '09946469766'
    ];

    // Configuração carregada
    $config = Config::load();

    // 1. Criar o associado
    echo "<h1>Gerando Associado</h1>";
    $associadoId = $associados->criar($dadosAssociado);
    echo "<p>Associado criado com sucesso. ID: <strong>{$associadoId}</strong></p>";

    // 2. Criar a cobrança para o associado
    echo "<h1>Criando Cobrança</h1>";
    $vencimento = date('Y-m-d', strtotime('+' . $config['cobranca']['vencimento_dias'] . ' days'));
    $associados->criarPagamento(
        $associadoId,
        $config['cobranca']['valor'],
        $config['cobranca']['descricao'],
        $vencimento
    );
    echo "<p>Cobrança criada com sucesso para o associado.</p>";


} catch (Exception $e) {
    echo "<h1>Erro no Mock de Teste</h1>";
    echo "<p>{$e->getMessage()}</p>";
    error_log("Erro no mock de teste: {$e->getMessage()}");
}
