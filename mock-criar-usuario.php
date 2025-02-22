<?php

require 'src/Sindicato.php';

try {
    // Instancia a classe de associados
    $associados = new Associados();

    // Dados de teste para criar um associado
    $dadosTeste = [
        'nome' => 'João da Silva',
        'email' => 'joao.silva@email.com',
        'telefone' => '11999999999',
        'cpf_cnpj' => '12345678909'
    ];

    // Cria o associado no banco de dados
    $associadoId = $associados->criar($dadosTeste, false); // false para não criar no Assas

    echo "<h1>Conexão e Inserção Testadas com Sucesso</h1>";
    echo "<p>Associado criado com ID: <strong>{$associadoId}</strong></p>";

} catch (Exception $e) {
    echo "<h1>Erro ao Testar a Conexão</h1>";
    echo "<p>{$e->getMessage()}</p>";
    error_log("Erro ao testar conexão: {$e->getMessage()}");
}
