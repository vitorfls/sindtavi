<?php

require 'src/Sindicato.php';

try {
    $config = Config::load();
    echo "<h1>Configuração Carregada com Sucesso</h1>";
    echo "<pre>";
    print_r($config);
    echo "</pre>";
} catch (Exception $e) {
    echo "<h1>Erro ao Carregar Configuração</h1>";
    echo "<p>{$e->getMessage()}</p>";
    error_log("Erro ao carregar configuração: {$e->getMessage()}");
}
