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

// VM Configuration
$vmName = 'my-test-vm-' . time(); // Unique VM name
$location = 'eastus'; // Azure region
$adminUsername = 'azureuser';
$adminPassword = 'P@ssw0rd123!'; // Must meet Azure password requirements

// Network Configuration
// You need to provide an existing subnet ID
// Format: /subscriptions/{subscriptionId}/resourceGroups/{resourceGroup}/providers/Microsoft.Network/virtualNetworks/{vnetName}/subnets/{subnetName}
$subnetId = "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Network/virtualNetworks/vnet-eastus/subnets/snet-eastus-1";

// VM Specifications
$vmSize = 'Standard_DC16ads_cc_v5';
$diskSizeGB = 128;   // OS disk size in GB

// Optional: Create a public IP for RDP access
$dedicatedAdminRdp = false; // Set to true to create a public IP for remote access

// Operating System
// Using Gen2 image for DC-series VMs which require Hypervisor Generation 2
$os = [
    'publisher' => 'MicrosoftWindowsServer',
    'offer' => 'WindowsServer',
    'sku' => '2019-datacenter-gensecond',
    'version' => 'latest'
];

try {
    // Initialize Azure Client
    $azure = new AzureClient($tenant, $clientId, $clientSecret);
    $vmClient = new VmClient($azure);

    echo "Creating VM: {$vmName}\n";
    echo "Location: {$location}\n";
    echo "Specifications: {$vmSize}, {$diskSizeGB}GB disk\n";
    echo "Public IP: " . ($dedicatedAdminRdp ? 'Yes' : 'No') . "\n";
    echo "\nThis may take several minutes...\n\n";

    // Create the VM
    $result = $vmClient->createVM(
        subscriptionId: $subscriptionId,
        resourceGroup: $resourceGroup,
        location: $location,
        vmName: $vmName,
        subnetId: $subnetId,
        vmSize: $vmSize,
        diskSizeGB: $diskSizeGB,
        adminUsername: $adminUsername,
        adminPassword: $adminPassword,
        dedicatedAdminRdp: $dedicatedAdminRdp,
        imageReference: $os
    );

    echo "VM Creation Response:\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

    // Get VM details
    echo "Fetching VM details...\n";
    $vmDetails = $vmClient->getVm($subscriptionId, $resourceGroup, $vmName);
    
    echo "\nVM Details:\n";
    echo "Name: " . ($vmDetails['name'] ?? 'N/A') . "\n";
    echo "Location: " . ($vmDetails['location'] ?? 'N/A') . "\n";
    echo "VM Size: " . ($vmDetails['properties']['hardwareProfile']['vmSize'] ?? 'N/A') . "\n";
    echo "Provisioning State: " . ($vmDetails['properties']['provisioningState'] ?? 'N/A') . "\n";

    // Get instance view to check power state
    echo "\nFetching instance view...\n";
    $instanceView = $vmClient->getInstanceView($subscriptionId, $resourceGroup, $vmName);
    
    if (isset($instanceView['statuses'])) {
        echo "\nVM Status:\n";
        foreach ($instanceView['statuses'] as $status) {
            echo "- " . ($status['code'] ?? 'N/A') . ": " . ($status['displayStatus'] ?? 'N/A') . "\n";
        }
    }

    echo "\n✓ VM created successfully!\n";
    echo "\nConnection Details:\n";
    echo "Username: {$adminUsername}\n";
    echo "Password: {$adminPassword}\n";
    
    if ($dedicatedAdminRdp) {
        echo "\nNote: A public IP has been created. You can find the IP address in the Azure Portal.\n";
        echo "To get the public IP programmatically, you would need to query the Network Interface resource.\n";
    }

} catch (\Exception $e) {
    echo "Error creating VM: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
