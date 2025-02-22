<?php

require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';


class Config
{
    private static ?array $config = null; // Inicializa como null

    public static function load(): array
    {
        if (!self::$config) {
            self::$config = require __DIR__ . '/Config.php';
        }
        return self::$config;
    }
}

class Database
{
    private static ?PDO $pdo = null;

    public static function getInstance(): PDO
    {
        if (!self::$pdo) {
            $config = Config::load()['database'];
            self::$pdo = new PDO(
                "mysql:host={$config['host']};dbname={$config['dbname']}",
                $config['user'],
                $config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }
        return self::$pdo;
    }
}

class Auth
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Valida credenciais e retorna dados do usuário
    public function login(string $email, string $senha): ?array
    {
        $stmt = $this->pdo->prepare("SELECT id, nome, email, senha, nivel_acesso FROM usuarios WHERE email = ? AND senha = ? AND ativo = 1");
        $stmt->execute([$email, $senha]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            $this->atualizarUltimoAcesso($usuario['id']);
            return $usuario;
        }

        return null;
    }

    // Registra último acesso do usuário
    private function atualizarUltimoAcesso(int $userId): void
    {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET ultimo_acesso = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$userId]);
    }

    // Cria novo usuário com senha criptografada
    public function criarUsuario(array $dados): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES (:nome, :email, :senha, :nivel_acesso)");
        $stmt->execute([
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'senha' => password_hash($dados['senha'], PASSWORD_DEFAULT),
            'nivel_acesso' => $dados['nivel_acesso'] ?? 'usuario'
        ]);
        return $this->pdo->lastInsertId();
    }
}

// Gerencia operações com associados
class Associados
{
    private PDO $pdo;
    private Customers $customers;
    private Payments $payments;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
        $this->customers = new Customers();
        $this->payments = new Payments();
    }

    // Cria novo associado e opcionalmente no Asaas
    public function criar(array $dados, bool $criarNoAssas = true): int
    {
        
        $query = "INSERT INTO associados (
            nome, email, telefone, cpf_cnpj, tipo, 
            ponto, ponto_logradouro, ponto_municipio, ponto_uf,
            veiculo, veiculo_marca, veiculo_cor, veiculo_combustivel,
            contrato, contrato_ano, contrato_data_inicio, status,
            endereco, bairro, cidade, uf, cep
        ) VALUES (
            :nome, :email, :telefone, :cpf_cnpj, :tipo,
            :ponto, :ponto_logradouro, :ponto_municipio, :ponto_uf,
            :veiculo, :veiculo_marca, :veiculo_cor, :veiculo_combustivel,
            :contrato, :contrato_ano, :contrato_data_inicio, :status,
            :endereco, :bairro, :cidade, :uf, :cep
        )";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($dados);
        $associadoId = $this->pdo->lastInsertId();

        // Cria cliente no Asaas se solicitado
        if ($criarNoAssas) {
            $cliente = $this->customers->criar([
                'name' => $dados['nome'],
                'email' => $dados['email'],
                'cpfCnpj' => $dados['cpf_cnpj'],
                'notificationDisabled' => true
            ]);
            $this->atualizar($associadoId, ['asaas_customer_id' => $cliente['id']]);
        }

        return $associadoId;
    }

    public function listarTiposCobranca(): array
    {
        $stmt = $this->pdo->query("
            SELECT id, nome, descricao
            FROM tipos_cobranca
            WHERE ativo = TRUE
            ORDER BY nome
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Cria cobrança para associado
    public function criarPagamento(
        int $associadoId, 
        string|array $emails,
        string $nome,
        float $valor, 
        string $descricao, 
        string $vencimento, 
        int $tipoCobrancaId = null,
        bool $enviarEmail = false
    ): void {
        
        $associado = $this->getById($associadoId);

        if (!$associado) {
            throw new RuntimeException("Associado $associadoId não encontrado.");
        }

        // Cria cliente no Asaas se não existir
        if (empty($associado['asaas_customer_id'])) {
            $cliente = $this->customers->criar([
                'name' => $associado['nome'],
                'email' => is_array($emails) ? $emails[0] : $emails,
                'cpfCnpj' => $associado['cpf_cnpj'],
                'notificationDisabled' => true
            ]);
            
            $this->atualizar($associadoId, ['asaas_customer_id' => $cliente['id']]);
            $associado['asaas_customer_id'] = $cliente['id'];
        }

        // Configura dados da cobrança
        $cobrancaData = [
            'customer' => $associado['asaas_customer_id'],
            'value' => $valor,
            'dueDate' => $vencimento,
            'description' => $descricao,
            'billingType' => 'BOLETO',
            'fine' => ['value' => 0],
            'interest' => ['value' => 0]
        ];

        // Adiciona split se configurado
        $config = Config::load()['asaas'];
        
        if ($config['split']['enabled']) {
            $cobrancaData['split'] = [
                [
                    'walletId' => $config['split']['walletId'],
                    'percentualValue' => $config['split']['percentualValue']
                ]
            ];
        }

        // Cria cobrança no Asaas
        $cobranca = $this->payments->criar($cobrancaData);

        // Salvar a cobrança no banco de dados
        $query = "INSERT INTO cobrancas (associado_id, asaas_id, valor, vencimento, link_boleto, tipo_cobranca_id)
                  VALUES (:associado_id, :asaas_id, :valor, :vencimento, :link_boleto, :tipo_cobranca_id)";

        $stmt = $this->pdo->prepare($query);

        $stmt->execute([
            'associado_id' => $associadoId,
            'asaas_id' => $cobranca['id'],
            'valor' => $valor,
            'vencimento' => $vencimento,
            'link_boleto' => $cobranca['bankSlipUrl'],
            'tipo_cobranca_id' => $tipoCobrancaId
        ]);

        // Salvar o boleto como PDF na pasta local
        $this->salvarBoletoPDF($cobranca['id'], $cobranca['bankSlipUrl']);

        // Enviar email se solicitado
        if ($enviarEmail) {
            $emailEnviado = $this->enviarEmailCobranca(
                $emails, // Passa o array de emails ou email único
                $nome, 
                $valor, 
                $vencimento, 
                $descricao, 
                $this->getBoletoPdfPath($cobranca['id'])
            );

            if ($emailEnviado) {
                // Atualiza a data de envio do email na tabela cobrancas
                $stmt = $this->pdo->prepare("UPDATE cobrancas SET data_envio_email = NOW() WHERE id = :id");
                $stmt->execute(['id' => $cobranca['id']]);
            }
        }
    }

    // Envia email de cobrança
    public function enviarEmailCobranca(
        string|array $emails,
        string $nome,
        float $valor,
        string $vencimento,
        string $descricao,
        ?string $pdfPath = null
    ): bool {
        try {
            $config = Config::load();
            $templatePath = __DIR__ . '/../' . $config['cobranca']['template'];
            
            if (!file_exists($templatePath)) {
                throw new RuntimeException("Template de email não encontrado: $templatePath");
            }

            $template = file_get_contents($templatePath);

            $emailBody = str_replace(
                ['{{NOME}}', '{{VALOR}}', '{{VENCIMENTO}}', '{{DESCRICAO}}'],
                [
                    $nome,
                    number_format($valor, 2, ',', '.'),
                    date('d/m/Y', strtotime($vencimento)),
                    $descricao
                ],
                $template
            );

            $emailSender = new Email();
            $subject = $config['cobranca']['assunto_email'];
            
            return $emailSender->sendEmail(
                $emails,
                $nome,
                $subject,
                $emailBody,
                '',  // altBody vazio
                $pdfPath
            );
        } catch (Exception $e) {
            error_log("Erro ao enviar email de cobrança: " . $e->getMessage());
            return false;
        }
    }

    // Retorna caminho do PDF do boleto
    private function getBoletoPdfPath(string $paymentId): string
    {
        $config = Config::load();
        return __DIR__ . '/../' . $config['cobranca']['boletos'] . "/{$paymentId}.pdf";
    }

    // Atualiza dados do associado
    public function atualizar(int $id, array $dados): void
    {
        if (empty($dados)) {
            throw new InvalidArgumentException("O array de dados não pode estar vazio.");
        }
    
        // Inicializar o array de campos e placeholders
        $set = [];
        $parametros = [];
    
        // Construir a query com placeholders posicionais
        foreach ($dados as $campo => $valor) {
            $set[] = "`" . str_replace("`", "``", $campo) . "` = ?";
            $parametros[] = $valor;
        }
    
        // Adicionar o ID como último parâmetro
        $parametros[] = $id;
    
        // Query final
        $query = "UPDATE associados SET " . implode(", ", $set) . " WHERE id = ?";
    
        // Debug para verificar query e parâmetros
        echo "DEBUG QUERY: $query\n";
        echo "PARAMETERS: " . print_r($parametros, true) . "\n";
    
        // Tentar executar a query
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($parametros);
            echo "Query executada com sucesso.\n";
        } catch (PDOException $e) {
            // Captura de erros detalhados
            echo "Erro ao executar query: " . $e->getMessage() . "\n";
            echo "Query executada: $query\n";
            echo "Parâmetros enviados: " . print_r($parametros, true) . "\n";
            throw $e;
        }
    }
    

    // Lista associados com paginação
    public function listar(string $condicao, int $pagina = 1, int $porPagina = 50): array
    {
        $offset = ($pagina - 1) * $porPagina;
        
        // Conta total de registros
        $total = $this->pdo->query("SELECT COUNT(*) FROM associados WHERE $condicao")->fetchColumn();
        
        // Busca registros paginados
        $registros = $this->pdo->query(
            "SELECT * FROM associados 
             WHERE $condicao 
             LIMIT $porPagina OFFSET $offset"
        )->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'registros' => $registros,
            'total' => $total,
            'paginas' => ceil($total / $porPagina)
        ];
    }

    // Retorna associado por ID
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM associados WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Salva boleto como PDF
    private function salvarBoletoPDF(string $paymentId, string $urlBoleto): void
    {
        $config = Config::load();
        $path = __DIR__ . '/../' . $config['cobranca']['boletos'] . "/{$paymentId}.pdf";

        if (!is_dir(__DIR__ . '/../' . $config['cobranca']['boletos'])) {
            mkdir(__DIR__ . '/../' . $config['cobranca']['boletos'], 0755, true);
        }

        $boletoContent = file_get_contents($urlBoleto);

        if ($boletoContent === false) {
            throw new RuntimeException("Erro ao baixar o boleto PDF de {$urlBoleto}");
        }

        file_put_contents($path, $boletoContent);
    }

    // Lista cobranças do associado
    public function listarCobrancas(int $associadoId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM vw_cobrancas 
            WHERE associado_id = ?
            ORDER BY vencimento DESC
        ");
        $stmt->execute([$associadoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Retorna estatísticas de cobranças
    public function getEstatisticas(string $dataInicio, string $dataFim): array
    {
        // Total de associados ativos
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM associados WHERE status = 1");
        $totalAssociados = $stmt->fetchColumn();

        // Estatísticas de cobrança usando a view
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) AS total_cobrancas,
                SUM(valor) AS valor_total_cobrancas,
                COUNT(CASE WHEN status_cobranca = 'PENDING' THEN 1 END) AS total_pendentes,
                SUM(CASE WHEN status_cobranca = 'PENDING' THEN valor ELSE 0 END) AS valor_total_pendentes,        
                COUNT(CASE WHEN status_cobranca = 'PENDING' AND vencimento < CURRENT_DATE THEN 1 END) AS total_atrasadas,
                SUM(CASE WHEN status_cobranca = 'PENDING' AND vencimento < CURRENT_DATE THEN valor ELSE 0 END) AS valor_total_atrasadas,
                COUNT(CASE WHEN status_cobranca IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH') THEN 1 END) AS total_pagos,
                SUM(CASE WHEN status_cobranca IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH') THEN valor ELSE 0 END) AS valor_total_pagos
            FROM vw_cobrancas
            WHERE vencimento BETWEEN ? AND ?
        ");
        $stmt->execute([$dataInicio, $dataFim]);
        $cobrancasStats = $stmt->fetch(PDO::FETCH_ASSOC);

        return array_merge(['total_associados' => $totalAssociados], $cobrancasStats);
    }

    public function getPagamentosRecentes(string $dataInicio, string $dataFim): array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM vw_cobrancas
            WHERE status_cobranca IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
            AND vencimento BETWEEN ? AND ?
            ORDER BY data_pagamento DESC 
            LIMIT 5
        ");
        $stmt->execute([$dataInicio, $dataFim]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProximosVencimentos(string $dataInicio, string $dataFim): array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM vw_cobrancas
            WHERE status_cobranca = 'PENDING'
            AND vencimento >= CURRENT_DATE 
            AND vencimento BETWEEN ? AND ?
            ORDER BY vencimento ASC 
            LIMIT 5
        ");
        $stmt->execute([$dataInicio, $dataFim]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lista associados ativos
    public function listarAtivos(): array
    {
        $stmt = $this->pdo->prepare("SELECT id, nome FROM associados WHERE status = 'A' ORDER BY nome");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

class HttpClient
{
    private string $apiKey;

    public function __construct()
    {
        $config = Config::load()['asaas'];
        $environment = $config['environment'];

        $this->apiKey = $config['api_keys'][$environment];
    }

    // Envia requisição HTTP
    public function request(string $method, string $url, array $data = []): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "access_token: {$this->apiKey}",
            "User-Agent: SindTavi/1.0"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $responseData = json_decode($response, true);
        
        if ($httpCode !== 200) {
            throw new RuntimeException($responseData['message'] ?? $response);
        }

        return $responseData;
    }
}

// Gerencia clientes no Asaas
class Customers
{
    private HttpClient $httpClient;
    private string $endpoint;

    public function __construct()
    {
        $config = Config::load()['asaas'];
        $this->httpClient = new HttpClient();
        $environment = $config['environment'];
        $baseUrl = $config['base_urls'][$environment];
        $this->endpoint = $baseUrl . $config['endpoints']['customers'];
    }

    public function criar(array $dados): array
    {
        try {
            return $this->httpClient->request('POST', $this->endpoint, $dados);
        } catch (RuntimeException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}

// Gerencia pagamentos no Asaas
class Payments
{
    private HttpClient $httpClient;
    private string $endpoint;

    public function __construct()
    {
        $config = Config::load()['asaas'];
        $this->httpClient = new HttpClient();
        $environment = $config['environment'];
        $baseUrl = $config['base_urls'][$environment];
        $this->endpoint = $baseUrl . $config['endpoints']['payments'];
    }

    // Cria pagamento no Asaas
    public function criar(array $dados): array
    {
        try {
            // Adiciona "billingType" como "BOLETO" se não especificado
            
            $dados['billingType'] = $dados['billingType'] ?? 'BOLETO';
            
            return $this->httpClient->request('POST', $this->endpoint, $dados);
        } catch (RuntimeException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}


// Gerencia envio de emails
class Email
{
    private PHPMailer\PHPMailer\PHPMailer $mail;
    private $host;
    private $port;
    private $username;
    private $password;
    private $fromEmail;
    private $fromName;

    public function __construct() {
        $config = Config::load();
        
        $this->host = $config['smtp']['host'];
        $this->port = $config['smtp']['port'];
        $this->username = $config['smtp']['username'];
        $this->password = $config['smtp']['password'];
        $this->fromEmail = $config['smtp']['from_email'];
        $this->fromName = $config['smtp']['from_name'];
    }

    // Envia email
    public function sendEmail($toEmail, $toName, $subject, $body, $altBody = '', $attachment = null) {
        
        $this->mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Configurações do servidor SMTP
            $this->mail->isSMTP();
            $this->mail->Host = $this->host;
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $this->username;
            $this->mail->Password = $this->password;
            $this->mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = $this->port;
            
            // Configurações de codificação e remetente
            $this->mail->CharSet = 'UTF-8';   // Definir charset
            $this->mail->Encoding = 'base64';  // Codificação segura para caracteres especiais            

            // Configurações de remetente e destinatário
            $this->mail->setFrom($this->fromEmail, $this->fromName);

            // Se for array de emails, usa o primeiro como principal e os demais como CC
            if (is_array($toEmail)) {
                $this->mail->addAddress($toEmail[0], $toName);
                $ccList = array_slice($toEmail, 1);
                foreach ($ccList as $ccEmail) {
                    $this->mail->addCC($ccEmail);
                }
            } else {
                $this->mail->addAddress($toEmail, $toName);
            }
            $this->mail->addBCC('filiacao@sindtavi-es.com.br');

            // Conteúdo do e-mail
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->AltBody = $altBody;

            // Adiciona anexo se fornecido
            if ($attachment && file_exists($attachment)) {
                $this->mail->addAttachment($attachment, basename($attachment));
            }

            // Enviar o e-mail e retornar o resultado
            return $this->mail->send();

        } catch (PHPMailer\PHPMailer\Exception $e) {
            throw new Exception("Erro ao enviar e-mail: {$this->mail->ErrorInfo}");
        }
    }
}

// Gerencia anexos de documentos
class Anexos 
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Salva novo anexo
    public function criar(array $dados): int 
    {
        try {
            $query = "INSERT INTO anexos (
                associado_id, nome, arquivo, tipo, tamanho, data_upload
            ) VALUES (
                :associado_id, :nome, :arquivo, :tipo, :tamanho, NOW()
            )";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($dados);
            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            throw new RuntimeException("Erro ao salvar anexo: " . $e->getMessage());
        }
    }

    // Lista anexos do associado
    public function listar(int $associadoId): array 
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, nome, arquivo, tipo, tamanho, 
                       DATE_FORMAT(data_upload, '%d/%m/%Y %H:%i') as data_upload
                FROM anexos 
                WHERE associado_id = ? 
                ORDER BY data_upload DESC
            ");
            $stmt->execute([$associadoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new RuntimeException("Erro ao listar anexos: " . $e->getMessage());
        }
    }

    // Remove anexo
    public function excluir(int $id, int $associadoId): void 
    {
        try {
            // Busca info do arquivo antes de excluir
            $stmt = $this->pdo->prepare("SELECT arquivo FROM anexos WHERE id = ? AND associado_id = ?");
            $stmt->execute([$id, $associadoId]);
            $anexo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($anexo) {
                // Remove registro do banco
                $stmt = $this->pdo->prepare("DELETE FROM anexos WHERE id = ? AND associado_id = ?");
                $stmt->execute([$id, $associadoId]);

                // Remove arquivo físico
                $caminhoArquivo = __DIR__ . "/../uploads/anexos/" . $anexo['arquivo'];
                if (file_exists($caminhoArquivo)) {
                    unlink($caminhoArquivo);
                }
            }
        } catch (Exception $e) {
            throw new RuntimeException("Erro ao excluir anexo: " . $e->getMessage());
        }
    }

    // Retorna dados do anexo
    public function getById(int $id, int $associadoId): ?array 
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, nome, arquivo, tipo, tamanho,
                       DATE_FORMAT(data_upload, '%d/%m/%Y %H:%i') as data_upload
                FROM anexos 
                WHERE id = ? AND associado_id = ?
            ");
            $stmt->execute([$id, $associadoId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            throw new RuntimeException("Erro ao buscar anexo: " . $e->getMessage());
        }
    }
}
