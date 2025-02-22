<?php

require_once __DIR__ . '/Sindicato.php';

class BulkMailer {
    private $associados;
    private $pdo;
    private $config;
    private $templatePath;
    private $pdfFolder;
    
    public function __construct() {
        $this->associados = new Associados();
        $this->pdo = Database::getInstance();
        $this->config = Config::load();
        
        // Validar e configurar caminhos
        $this->templatePath = __DIR__ . '/../Templates';
        $this->pdfFolder = __DIR__ . '/../' . ($this->config['cobranca']['boletos'] ?? 'uploads/boletos');
        
        // Validar diretório de templates
        if (!is_dir($this->templatePath)) {
            throw new Exception("Diretório de templates não encontrado: {$this->templatePath}");
        }
        
        // Validar diretório de boletos
        if (!is_dir($this->pdfFolder)) {
            throw new Exception("Diretório de pdfs não encontrado: {$this->pdfFolder}");
        }
    }
    
    private function buscarCobrancasProximasVencimento(int $diasAteVencimento = 1): array {
        $sql = "
            SELECT 
                a.id as associado_id,
                a.nome,
                a.email,
                v.id as cobranca_id,
                v.asaas_id,
                v.valor,
                v.vencimento,
                v.tipo_cobranca,
                v.tipo_cobranca_id
            FROM vw_cobrancas v 
            INNER JOIN associados a ON v.associado_id = a.id 
            WHERE 
                a.cadastro_status = 1 
                AND a.status = 1 
                AND v.status_cobranca = 'PENDING'
                AND v.data_envio_email IS NULL 
                AND v.vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias DAY)
            ORDER BY v.vencimento ASC
            LIMIT 1
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':dias', $diasAteVencimento, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function validarTemplate(string $templateName): string {
        $templateFile = $this->templatePath . '/' . $templateName;
        
        if (!file_exists($templateFile)) {
            throw new Exception("Template não encontrado: {$templateName}. Verifique se o arquivo existe na pasta Templates.");
        }
        
        return $templateFile;
    }
    
    private function atualizarStatusEnvioEmail(int $cobrancaId): void {
        $sql = "UPDATE cobrancas SET data_envio_email = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cobrancaId]);
    }
    
    public function enviarEmailCobranca(array $params = []): array {
        $resultados = [];
        $diasAteVencimento = $params['dias_ate_vencimento'] ?? 1; 
        $templateName = $params['template_name'] ?? null;
        
        if (!$templateName) {
            throw new Exception("Nome do template HTML é obrigatório");
        }
        
        // Validar template
        $templateFile = $this->validarTemplate($templateName);
        
        // Busca cobranças próximas ao vencimento
        $cobrancas = $this->buscarCobrancasProximasVencimento($diasAteVencimento);
        
        foreach ($cobrancas as $cobranca) {

            try {

                // Verificar se existe o PDF do boleto
                $pdfPath = $this->pdfFolder . "/{$cobranca['asaas_id']}.pdf";
                $pdfAttachment = null;
                
                if (file_exists($pdfPath) && is_readable($pdfPath)) {
                    $pdfAttachment = $pdfPath;
                }

                // Envia o email usando o método existente da classe Associados
                $enviado = $this->associados->enviarEmailCobranca(
                    $cobranca['email'],
                    $cobranca['nome'],
                    (float)$cobranca['valor'],
                    $cobranca['vencimento'],
                    $cobranca['tipo_cobranca'],
                    $pdfAttachment,
                    $templateFile
                );
                
                //if ($enviado) {
                //    $this->atualizarStatusEnvioEmail($cobranca['cobranca_id']);
                //}
                
                $resultados[] = [
                    'associado_id' => $cobranca['associado_id'],
                    'nome' => $cobranca['nome'],
                    'email' => $cobranca['email'],
                    'sucesso' => $enviado,
                    'mensagem' => $enviado ? 'Email enviado com sucesso' : 'Falha ao enviar email'
                ];
                
            } catch (Exception $e) {
                $resultados[] = [
                    'associado_id' => $cobranca['associado_id'],
                    'nome' => $cobranca['nome'],
                    'email' => $cobranca['email'],
                    'sucesso' => false,
                    'mensagem' => 'Erro: ' . $e->getMessage()
                ];
            }

            $this->atualizarStatusEnvioEmail($cobranca['cobranca_id']);
        }
        
        return $resultados;
    }
}
