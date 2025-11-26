<?php
namespace AzureVmSdk;

use GuzzleHttp\Client;
use AzureVmSdk\Exceptions\ApiException;

class AzureClient {
    private Client $http;
    private string $tenantId;
    private string $clientId;
    private string $clientSecret;
    private ?array $tokenCache = null;
    private string $baseUrl = 'https://management.azure.com';
    private string $apiVersion = '2023-09-01'; // Stable API version supported across Azure resources

    public function __construct(string $tenantId, string $clientId, string $clientSecret, array $guzzleOpts = []) {
        if (trim($tenantId) === '') {
            throw new \InvalidArgumentException('Azure tenant ID is required and cannot be empty');
        }
        if (trim($clientId) === '') {
            throw new \InvalidArgumentException('Azure client ID is required and cannot be empty');
        }
        if (trim($clientSecret) === '') {
            throw new \InvalidArgumentException('Azure client secret is required and cannot be empty');
        }

        $this->tenantId = $tenantId;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->http = new Client($guzzleOpts);
    }

    private function getToken(): string {
        if ($this->tokenCache && ($this->tokenCache['expires_at'] > time() + 60)) {
            return $this->tokenCache['access_token'];
        }

        $url = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token";
        $resp = $this->http->post($url, [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => 'https://management.azure.com/.default',
            ],
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        $data = json_decode((string)$resp->getBody(), true);
        if (!isset($data['access_token'])) {
            throw new ApiException('Failed to obtain access token', $resp->getStatusCode());
        }

        $this->tokenCache = [
            'access_token' => $data['access_token'],
            'expires_at' => time() + ($data['expires_in'] ?? 3600)
        ];

        return $data['access_token'];
    }

    public function request(string $method, string $path, array $query = [], $body = null) {
        $token = $this->getToken();
        $query['api-version'] = $this->apiVersion;

        $options = [
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ],
            'query' => $query,
        ];
        if ($body !== null) {
            $options['body'] = json_encode($body);
        }

        try {
            $resp = $this->http->request($method, $this->baseUrl . $path, $options);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $resp = $e->getResponse();
            $body = (string)$resp->getBody();
            throw new ApiException("API error: {$body}", $resp->getStatusCode());
        }

        return json_decode((string)$resp->getBody(), true);
    }
}
