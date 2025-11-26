<?php
require __DIR__ . '/../vendor/autoload.php';
use AzureVmSdk\AzureClient;
use AzureVmSdk\VmClient;
use Dotenv\Dotenv;

/**
 * Complete VM Lifecycle Example
 * 
 * This example demonstrates the complete lifecycle of a VM:
 * 1. Create a VM
 * 2. Check its status
 * 3. Perform operations (start, stop, restart)
 * 4. Clean up (delete)
 */

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Azure credentials
$tenant = $_ENV['AZURE_TENANT_ID'];
$clientId  = $_ENV['AZURE_CLIENT_ID'];
$clientSecret = $_ENV['AZURE_CLIENT_SECRET'];
$subscriptionId = $_ENV['AZURE_SUBSCRIPTION_ID'];
$resourceGroup = $_ENV['AZURE_RESOURCE_GROUP'];

// Configuration
$vmName = 'lifecycle-demo-' . time();
$location = 'eastus';
$subnetId = "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Network/virtualNetworks/my-vnet/subnets/default";

try {
    // Initialize client
    $azure = new AzureClient($tenant, $clientId, $clientSecret);
    $vmClient = new VmClient($azure);

    echo "=== Azure VM Lifecycle Demo ===\n\n";

    // Step 1: Create VM
    echo "Step 1: Creating VM '{$vmName}'...\n";
    $createResult = $vmClient->createVM(
        subscriptionId: $subscriptionId,
        resourceGroup: $resourceGroup,
        location: $location,
        vmName: $vmName,
        subnetId: $subnetId,
        ramGB: 2,
        cpuCores: 1,
        diskSizeGB: 64,
        adminUsername: 'demouser',
        adminPassword: 'DemoP@ssw0rd123!',
        dedicatedAdminRdp: false
    );
    echo "✓ VM created successfully!\n\n";
    sleep(2); // Wait for Azure to process

    // Step 2: Get VM details
    echo "Step 2: Retrieving VM details...\n";
    $vmDetails = $vmClient->getVm($subscriptionId, $resourceGroup, $vmName);
    echo "VM Name: " . $vmDetails['name'] . "\n";
    echo "Location: " . $vmDetails['location'] . "\n";
    echo "VM Size: " . $vmDetails['properties']['hardwareProfile']['vmSize'] . "\n";
    echo "Provisioning State: " . $vmDetails['properties']['provisioningState'] . "\n\n";

    // Step 3: Get instance view
    echo "Step 3: Checking VM power state...\n";
    $instanceView = $vmClient->getInstanceView($subscriptionId, $resourceGroup, $vmName);
    if (isset($instanceView['statuses'])) {
        foreach ($instanceView['statuses'] as $status) {
            if (strpos($status['code'], 'PowerState/') !== false) {
                echo "Power State: " . $status['displayStatus'] . "\n";
            }
        }
    }
    echo "\n";

    // Step 4: List all VMs
    echo "Step 4: Listing all VMs in resource group...\n";
    $allVms = $vmClient->listVms($subscriptionId, $resourceGroup);
    echo "Total VMs: " . count($allVms) . "\n";
    foreach ($allVms as $vm) {
        echo "- " . $vm['name'] . "\n";
    }
    echo "\n";

    // Step 5: Power operations
    echo "Step 5: Testing power operations...\n";
    
    echo "  → Restarting VM...\n";
    $vmClient->restartVm($subscriptionId, $resourceGroup, $vmName);
    echo "  ✓ Restart initiated\n";
    sleep(5);

    echo "  → Stopping VM (power off)...\n";
    $vmClient->powerOffVm($subscriptionId, $resourceGroup, $vmName);
    echo "  ✓ Power off initiated\n";
    sleep(5);

    echo "  → Starting VM...\n";
    $vmClient->startVm($subscriptionId, $resourceGroup, $vmName);
    echo "  ✓ Start initiated\n";
    sleep(5);

    echo "  → Deallocating VM (to stop billing)...\n";
    $vmClient->deallocateVm($subscriptionId, $resourceGroup, $vmName);
    echo "  ✓ Deallocate initiated\n\n";
    sleep(5);

    // Step 6: Cleanup (optional - uncomment to delete)
    echo "Step 6: Cleanup\n";
    $cleanup = readline("Do you want to delete the VM? (yes/no): ");
    
    if (strtolower(trim($cleanup)) === 'yes') {
        echo "  → Deleting VM...\n";
        $vmClient->deleteVm($subscriptionId, $resourceGroup, $vmName);
        echo "  ✓ VM deletion initiated\n";
        echo "\nNote: VM deletion is asynchronous and may take several minutes.\n";
    } else {
        echo "  ℹ VM '{$vmName}' was not deleted. You can manage it manually.\n";
    }

    echo "\n=== Demo Complete ===\n";
    echo "\nSummary:\n";
    echo "- Created VM: {$vmName}\n";
    echo "- Location: {$location}\n";
    echo "- Operations performed: restart, power off, start, deallocate\n";
    
    if (strtolower(trim($cleanup ?? 'no')) !== 'yes') {
        echo "\n⚠ Remember to delete the VM manually to avoid charges!\n";
        echo "Command: php examples/manage_vm.php (uncomment delete section)\n";
    }

} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
