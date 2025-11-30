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

// Specify the name of the network interface to delete
$nicName = 'my-network-interface'; // Change this to your network interface name

try {
    $azureClient = new AzureClient($tenantId, $clientId, $clientSecret);
    $nicClient = new NetworkInterfaceClient($azureClient);

    echo "Deleting Network Interface '{$nicName}' from resource group '{$resourceGroup}'...\n";
    
    // First, check if the network interface exists
    try {
        $nic = $nicClient->getNetworkInterface($subscriptionId, $resourceGroup, $nicName);
        echo "Found Network Interface:\n";
        echo "Name: " . $nic['name'] . "\n";
        echo "Location: " . $nic['location'] . "\n";
        echo "ID: " . $nic['id'] . "\n";
        
        // Display IP configuration if available
        if (isset($nic['properties']['ipConfigurations']) && is_array($nic['properties']['ipConfigurations'])) {
            echo "IP Configurations:\n";
            foreach ($nic['properties']['ipConfigurations'] as $ipConfig) {
                echo "  - " . $ipConfig['name'] . "\n";
                if (isset($ipConfig['properties']['privateIPAddress'])) {
                    echo "    Private IP: " . $ipConfig['properties']['privateIPAddress'] . "\n";
                }
            }
        }
        echo "-----------------------------------\n";
    } catch (Exception $e) {
        echo "Network Interface '{$nicName}' not found or error occurred: " . $e->getMessage() . "\n";
        exit(1);
    }

    // Delete the network interface
    $result = $nicClient->deleteNetworkInterface($subscriptionId, $resourceGroup, $nicName);
    
    echo "Network Interface '{$nicName}' deletion initiated successfully.\n";
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
