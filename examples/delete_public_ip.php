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

// Specify the name of the public IP to delete
$publicIpName = 'test-ip-3'; // Change this to your public IP name

try {
    $azureClient = new AzureClient($tenantId, $clientId, $clientSecret);
    $nicClient = new NetworkInterfaceClient($azureClient);

    echo "Deleting Public IP '{$publicIpName}' from resource group '{$resourceGroup}'...\n";
    
    // First, check if the public IP exists
    try {
        $publicIp = $nicClient->getPublicIp($subscriptionId, $resourceGroup, $publicIpName);
        echo "Found Public IP:\n";
        echo "Name: " . $publicIp['name'] . "\n";
        echo "Location: " . $publicIp['location'] . "\n";
        if (isset($publicIp['properties']['ipAddress'])) {
            echo "IP Address: " . $publicIp['properties']['ipAddress'] . "\n";
        }
        echo "-----------------------------------\n";
    } catch (Exception $e) {
        echo "Public IP '{$publicIpName}' not found or error occurred: " . $e->getMessage() . "\n";
        exit(1);
    }

    // Delete the public IP
    $result = $nicClient->deletePublicIp($subscriptionId, $resourceGroup, $publicIpName);
    
    echo "Public IP '{$publicIpName}' deletion initiated successfully.\n";
    echo "Note: Deletion is asynchronous and may take a few moments to complete.\n";
    
    if ($result !== null && !empty($result)) {
        echo "\nDeletion Response:\n";
        print_r($result);
    } else {
        echo "\nDeletion request accepted (no response body returned).\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
