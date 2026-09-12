<?php

require_once __DIR__ . '/semaphore_config.php';

function sendSms(string $number, string $message): array
{
    // Prefer a server environment variable so secrets are not committed to source control.
    $apiKey = trim((string)SEMAPHORE_API_KEY);
    $senderName = trim((string)SEMAPHORE_SENDER_NAME);

    if ($apiKey === '' || $apiKey === 'PUT_YOUR_SEMAPHORE_API_KEY_HERE') {
        return [
            'success' => false,
            'message' => 'SMS is not configured yet. Put your Semaphore API key in api/semaphore_config.php.'
        ];
    }

    $number = preg_replace('/\D+/', '', $number);

    if (str_starts_with($number, '09')) {
        $number = '63' . substr($number, 1);
    } elseif (str_starts_with($number, '9') && strlen($number) === 10) {
        $number = '63' . $number;
    }

    if (strlen($number) !== 12 || !str_starts_with($number, '63')) {
        return [
            'success' => false,
            'message' => 'The client phone number is not a valid Philippine mobile number.'
        ];
    }

    $ch = curl_init('https://api.semaphore.co/api/v4/messages');

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'apikey' => $apiKey,
            'number' => $number,
            'message' => $message,
            'sendername' => $senderName
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded'
        ]
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlError) {
        return [
            'success' => false,
            'message' => 'SMS connection failed. Please check the server connection and cURL.'
        ];
    }

    $data = json_decode((string)$response, true);

    if ($httpCode >= 200 && $httpCode < 300 && is_array($data)) {
        $first = $data[0] ?? $data;
        $status = strtolower((string)($first['status'] ?? ''));

        if (!in_array($status, ['failed', 'refunded'], true)) {
            return [
                'success' => true,
                'message' => 'SMS queued successfully.',
                'response' => $data
            ];
        }
    }

    return [
        'success' => false,
        'message' => 'SMS provider rejected the message.'
    ];
}
