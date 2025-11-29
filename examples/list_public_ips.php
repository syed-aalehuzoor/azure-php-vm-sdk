<?php

require_once __DIR__ . '/../vendor/autoload.php';

use AzureVmSdk\AzureClient;
use AzureVmSdk\NetworkInterfaceClient;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$tenantId = $_ENV['AZURE_TENANT_ID'];
$clientId = $_ENV['AZURE_CLIENT_ID'];
$clientSecret = $_ENV['AZURE_CLIENT_SECRET'];
$subscriptionId = $_ENV['AZURE_SUBSCRIPTION_ID'];
$resourceGroup = $_ENV['AZURE_RESOURCE_GROUP'];

try {
    $azureClient = new AzureClient($tenantId, $clientId, $clientSecret);
    $nicClient = new NetworkInterfaceClient($azureClient);

    echo "Listing Public IPs in resource group '{$resourceGroup}'...\n";
    $publicIps = $nicClient->listPublicIps($subscriptionId, $resourceGroup);

    if (isset($publicIps['value']) && is_array($publicIps['value'])) {
        foreach ($publicIps['value'] as $ip) {
            echo "Name: " . $ip['name'] . "\n";
            echo "ID: " . $ip['id'] . "\n";
            echo "Location: " . $ip['location'] . "\n";
            if (isset($ip['properties']['ipAddress'])) {
                echo "IP Address: " . $ip['properties']['ipAddress'] . "\n";
            }
            echo "-----------------------------------\n";
        }
    } else {
        echo "No Public IPs found or unexpected response format.\n";
        print_r($publicIps);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
