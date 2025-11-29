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

    echo "Listing Network Interfaces in resource group '{$resourceGroup}'...\n";
    $nics = $nicClient->listNetworkInterfaces($subscriptionId, $resourceGroup);

    if (isset($nics['value']) && is_array($nics['value'])) {
        foreach ($nics['value'] as $nic) {
            echo "Name: " . $nic['name'] . "\n";
            echo "ID: " . $nic['id'] . "\n";
            echo "Location: " . $nic['location'] . "\n";
            if (isset($nic['properties']['ipConfigurations'])) {
                foreach ($nic['properties']['ipConfigurations'] as $ipConfig) {
                    echo "  IP Config: " . $ipConfig['name'] . "\n";
                    if (isset($ipConfig['properties']['privateIPAddress'])) {
                        echo "  Private IP: " . $ipConfig['properties']['privateIPAddress'] . "\n";
                    }
                }
            }
            echo "-----------------------------------\n";
        }
    } else {
        echo "No Network Interfaces found or unexpected response format.\n";
        print_r($nics);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
