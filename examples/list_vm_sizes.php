<?php

require __DIR__ . '/../vendor/autoload.php';

use AzureVmSdk\AzureClient;
use AzureVmSdk\VmClient;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Azure credentials and configuration
$tenant = $_ENV['AZURE_TENANT_ID'];
$clientId  = $_ENV['AZURE_CLIENT_ID'];
$clientSecret = $_ENV['AZURE_CLIENT_SECRET'];
$subscriptionId = $_ENV['AZURE_SUBSCRIPTION_ID'];
$resourceGroup = $_ENV['AZURE_RESOURCE_GROUP'];

$azure = new AzureClient($tenant, $clientId, $clientSecret);
$vmClient = new VmClient($azure);

$vmSizes = $vmClient->getAvailableVMSizes($subscriptionId, 'eastus');

foreach ($vmSizes as $vmSize) {
    echo "Name: " . $vmSize['name'] . "\n";
    echo "Cores: " . $vmSize['numberOfCores'] . "\n";
    echo "Memory: " . $vmSize['memoryInMB'] / 1024 . "GB\n";
    echo "\n";
}