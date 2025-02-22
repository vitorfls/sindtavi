<?php
require_once __DIR__ . '/src/Sindicato.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';

function sendTestEmail(string $to, string $subject, string $content): array
{
    try {
        $config = Config::load()['smtp'];
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        // Configurações do servidor SMTP
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['port'];
        
        // Configurações de codificação
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        // Configurações de remetente e destinatário
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($to);

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $content;
        
        // Enviar o e-mail
        $mail->send();
        
        return [
            'success' => true,
            'message' => 'Email enviado com sucesso via SMTP'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $mail->ErrorInfo
        ];
    }
}

function sendTestEmailAPI(string $to, string $subject, string $content): array
{
    try {
        $config = Config::load()['smtp'];
        $apiKey = $config['password'];

        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ];

        $data = [
            'personalizations' => [
                [
                    'to' => [['email' => $to]]
                ]
            ],
            'from' => [
                'email' => $config['from_email'],
                'name' => $config['from_name']
            ],
            'subject' => $subject,
            'content' => [
                [
                    'type' => 'text/html',
                    'value' => $content
                ]
            ]
        ];

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception($error);
        }

        if ($httpCode !== 202) {
            throw new Exception('Erro ao enviar email: ' . $response);
        }

        return [
            'success' => true,
            'message' => 'Email enviado com sucesso via API',
            'status' => $httpCode
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = $_POST['to'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $content = $_POST['content'] ?? '';
    $method = $_POST['method'] ?? 'smtp';
    
    if ($to && $subject && $content) {
        $startTime = microtime(true);
        
        $result = $method === 'smtp' 
            ? sendTestEmail($to, $subject, $content)
            : sendTestEmailAPI($to, $subject, $content);
        
        $endTime = microtime(true);
        $executionTime = number_format($endTime - $startTime, 2);
        
        if ($result['success']) {
            $message = $result['message'] . " (Tempo: {$executionTime}s)";
        } else {
            $error = $result['error'];
        }
    } else {
        $error = "Todos os campos são obrigatórios";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teste Email - Comparativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h2>Teste de Email - Comparativo SMTP vs API</h2>
    <p>Compare o tempo de envio entre SMTP e API do SendGrid</p>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="method" class="form-label">Método de Envio:</label>
                    <select class="form-control" id="method" name="method">
                        <option value="smtp">SMTP (PHPMailer)</option>
                        <option value="api">API SendGrid</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="to" class="form-label">Para:</label>
                    <input type="email" class="form-control" id="to" name="to" required>
                </div>
                
                <div class="mb-3">
                    <label for="subject" class="form-label">Assunto:</label>
                    <input type="text" class="form-control" id="subject" name="subject" required>
                </div>
                
                <div class="mb-3">
                    <label for="content" class="form-label">Conteúdo (HTML):</label>
                    <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Enviar Email</button>
            </form>
        </div>
    </div>
</body>
</html>
