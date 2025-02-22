<?php

require 'Sindicato.php';

class WebhookHandler
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /**
     * Processa a requisição recebida do Assas.
     */
    public function handle(): void
    {
        header('Content-Type: application/json');

        try {

            $data = $this->getPayload();
            $this->processEvent($data);
            
            http_response_code(200);
            echo json_encode(['success' => true]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Obtém e valida o payload da requisição.
     */
    private function getPayload(): array
    {
        $payload = file_get_contents('php://input');
        $data = json_decode($payload, true);

        if (!$data) {
            throw new RuntimeException('Payload inválido');
        }

        return $data;
    }

    /**
     * Processa o evento recebido do Assas.
     */
    private function processEvent(array $data): void
    {
        $event = $data['event'] ?? null;
        $payment = $data['payment'] ?? null;

        if (!$event || !$payment) {
            throw new RuntimeException('Dados do evento inválidos');
        }

        switch ($event) {

            case 'PAYMENT_CONFIRMED':               
                $this->updatePaymentStatus($payment['id'], 3);
                break;

            case 'PAYMENT_RECEIVED':
                $this->updatePaymentStatus($payment['id'], 2);
                break;

            case 'PAYMENT_OVERDUE':
                $this->updatePaymentStatus($payment['id'], 4);
                break;                

            default:
                break;
        }
    }

    /**
     * Atualiza o status do pagamento no banco de dados.
     */
    private function updatePaymentStatus(string $paymentId, int $statusId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE cobrancas 
             SET status_cobranca_id = :status_id 
             WHERE asaas_id = :payment_id"
        );

        $stmt->execute([
            'status_id' => $statusId,
            'payment_id' => $paymentId
        ]);
    }
}
