<?php

class BulkHandler
{
    private PDO $pdo;
    private Associados $associados;
    private array $config;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
        $this->associados = new Associados();
        $this->config = Config::load()['cobranca'] ?? [];
    }

    public function handle(int $quantidade): array
    {
        $associadosSemCobranca = $this->getAssociadosSemCobranca($quantidade);
        
        $resultado = [
            'processados' => 0,
            'erros' => []
        ];

        foreach ($associadosSemCobranca as $associado) {

            try {
                $this->criarCobranca($associado);
                $resultado['processados']++;
            } catch (Exception $e) {
                $resultado['erros'][] = "Erro ao processar {$associado['nome']}: {$e->getMessage()}";
            }
            
        }

        return $resultado;
    }

    private function getAssociadosSemCobranca(int $quantidade): array
    {
        
        $sql = "SELECT a.* FROM associados a 
                LEFT JOIN (
                    SELECT associado_id, COUNT(*) as total_cobrancas 
                    FROM cobrancas 
                    GROUP BY associado_id
                ) c ON a.id = c.associado_id 
                LEFT JOIN blacklist b ON a.email = b.email
                WHERE (c.total_cobrancas IS NULL OR c.total_cobrancas = 0)
                AND a.cadastro_status = 1 
                AND b.email IS NULL
                LIMIT :quantidade";
              
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':quantidade', $quantidade, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function criarCobranca(array $associado): void
    {
        try {
            
            // Coletar todos os emails válidos
            $emails = [];
            
            foreach (['email', 'email2', 'email3'] as $emailField) {
                if (!empty($associado[$emailField]) && filter_var($associado[$emailField], FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $associado[$emailField];
                }
            }

            // Se não houver nenhum email válido, use o primeiro email mesmo que inválido
            if (empty($emails)) {
                $emails[] = $associado['email'];
            }

            // Criar uma única cobrança e enviar para todos os emails
            $response = $this->associados->criarPagamento(
                (int)$associado['id'],
                $emails,
                $associado['nome'],
                (float)($this->config['valor'] ?? 0),
                $this->config['descricao'] ?? 'Cobrança Sindical',
                date('Y-m-d', strtotime("+{$this->config['vencimento_dias']} days")),
                $this->config['id_tipo_cobranca'] ?? 1,
                true
            );

            // Atualiza a data de envio do email na última cobrança criada para este associado
            $stmt = $this->pdo->prepare("
                UPDATE cobrancas 
                SET data_envio_email = CURRENT_TIMESTAMP 
                WHERE associado_id = :associado_id 
                ORDER BY id DESC 
                LIMIT 1
            ");
            
            $stmt->execute([
                ':associado_id' => $associado['id']
            ]);

        } catch (Exception $e) {
            
            
            // Atualiza o status do associado em caso de exceção
            $stmt = $this->pdo->prepare("UPDATE associados SET cadastro_status = 0, cadastro_error = :error WHERE id = :id");
            
            $stmt->execute([
                ':error' => $e->getMessage(),
                ':id' => $associado['id']
            ]);

            echo "Ocorreu um erro ao processar o associado {$associado['nome']}: {$e->getMessage()}\n";
            

            // Apenas registra o erro e continua o processamento
            return;
        }
    }
}
