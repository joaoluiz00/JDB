<?php
class MercadoPagoService
{
    private static $instance;
    private $accessToken;
    private $publicKey;
    private $integratorId;
    private $config;

    private function __construct()
    {
        $configPath = __DIR__ . '/../config/mercado_pago.php';
        if (!file_exists($configPath)) {
            throw new RuntimeException('Arquivo de configuração do Mercado Pago não encontrado.');
        }

        $this->config = require $configPath;
        $this->accessToken = $this->config['access_token'] ?? '';
        $this->publicKey = $this->config['public_key'] ?? '';
        $this->integratorId = $this->config['integrator_id'] ?? '';

        if (empty($this->accessToken) || strpos($this->accessToken, 'TEST-ACCESS-TOKEN') !== false) {
            throw new RuntimeException('Configure seu ACCESS TOKEN do Mercado Pago em config/mercado_pago.php ou via variável de ambiente.');
        }
    }

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPublicKey(): string
    {
        if (empty($this->publicKey) || strpos($this->publicKey, 'TEST-PUBLIC-KEY') !== false) {
            throw new RuntimeException('Configure sua PUBLIC KEY do Mercado Pago para pagamentos com cartão.');
        }
        return $this->publicKey;
    }

    public function createPixPayment(float $amount, string $description, array $payer, array $metadata = []): array
    {
        $payload = [
            'transaction_amount' => round($amount, 2),
            'description' => $description,
            'payment_method_id' => 'pix',
            'payer' => $this->buildPayerPayload($payer),
            'external_reference' => $metadata['external_reference'] ?? null,
            'metadata' => $metadata,
            'notification_url' => $this->config['notification_url'] ?? null,
        ];
        return $this->request('POST', '/v1/payments', $payload);
    }

    public function createCardPayment(array $data): array
    {
        $payload = [
            'transaction_amount' => round(floatval($data['amount'] ?? 0), 2),
            'token' => $data['token'] ?? '',
            'installments' => intval($data['installments'] ?? 1),
            'payment_method_id' => $data['payment_method_id'] ?? '',
            'issuer_id' => $data['issuer_id'] ?? null,
            'description' => $data['description'] ?? 'Compra Loja JDB',
            'payer' => [
                'email' => $data['payer']['email'] ?? '',
                'first_name' => $data['payer']['first_name'] ?? '',
                'last_name' => $data['payer']['last_name'] ?? '',
                'identification' => [
                    'type' => $data['payer']['identification']['type'] ?? 'CPF',
                    'number' => preg_replace('/\D/', '', $data['payer']['identification']['number'] ?? ''),
                ],
            ],
            'metadata' => $data['metadata'] ?? [],
            'notification_url' => $this->config['notification_url'] ?? null,
        ];

        return $this->request('POST', '/v1/payments', $payload);
    }

    public function getPayment(int|string $paymentId): array
    {
        return $this->request('GET', '/v1/payments/' . $paymentId);
    }

    private function buildPayerPayload(array $payer): array
    {
        $identification = $payer['identification'] ?? [];
        $idNumber = preg_replace('/\D/', '', $identification['number'] ?? '');
        if (empty($idNumber)) {
            $idNumber = '12345678909';
        }

        return [
            'email' => $payer['email'] ?? 'comprador@example.com',
            'first_name' => $payer['first_name'] ?? ($payer['nome'] ?? 'Cliente'),
            'last_name' => $payer['last_name'] ?? '',
            'identification' => [
                'type' => strtoupper($identification['type'] ?? 'CPF'),
                'number' => $idNumber,
            ],
            'address' => $payer['address'] ?? null,
        ];
    }

    private function request(string $method, string $uri, array $payload = null): array
    {
        $ch = curl_init('https://api.mercadopago.com' . $uri);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->buildHeaders());

        if ($payload !== null) {
            $body = json_encode(array_filter($payload, function ($value) {
                return !($value === null || $value === '');
            }));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Erro ao se comunicar com o Mercado Pago: ' . $error);
        }

        $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);
        if ($statusCode >= 400) {
            $message = $decoded['message'] ?? $decoded['error'] ?? 'Erro desconhecido';
            throw new RuntimeException('Mercado Pago retornou erro (' . $statusCode . '): ' . $message);
        }

        return $decoded ?: [];
    }

    private function buildHeaders(): array
    {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->accessToken,
        ];

        if (!empty($this->integratorId)) {
            $headers[] = 'X-Integrator-Id: ' . $this->integratorId;
        }

        return $headers;
    }
}
