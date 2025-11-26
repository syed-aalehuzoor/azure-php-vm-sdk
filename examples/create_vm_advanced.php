<?php
require __DIR__ . '/../vendor/autoload.php';
use AzureVmSdk\AzureClient;
use AzureVmSdk\VmClient;
use Dotenv\Dotenv;

/**
 * Advanced VM Creation Example
 * 
 * This example demonstrates creating a VM using the lower-level createOrUpdateVm method
 * for users who need more control over the VM configuration.
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

// VM Configuration
$vmName = 'advanced-vm-' . time();
$location = 'eastus';

// Network Interface ID (must be created beforehand)
$nicId = "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Network/networkInterfaces/{$vmName}-nic";

try {
    // Initialize clients
    $azure = new AzureClient($tenant, $clientId, $clientSecret);
    $vmClient = new VmClient($azure);

    // Define VM payload with custom configuration
    $vmPayload = [
        'location' => $location,
        'properties' => [
            'hardwareProfile' => [
                'vmSize' => 'Standard_B2s' // 2 vCPUs, 4GB RAM
            ],
            'storageProfile' => [
                'imageReference' => [
                    'publisher' => 'MicrosoftWindowsServer',
                    'offer' => 'WindowsServer',
                    'sku' => '2022-Datacenter',
                    'version' => 'latest'
                ],
                'osDisk' => [
                    'name' => "{$vmName}_OsDisk",
                    'caching' => 'ReadWrite',
                    'createOption' => 'FromImage',
                    'managedDisk' => [
                        'storageAccountType' => 'Premium_LRS' // Premium SSD
                    ],
                    'diskSizeGB' => 256
                ],
                // Optional: Add data disks
                'dataDisks' => [
                    [
                        'lun' => 0,
                        'name' => "{$vmName}_DataDisk_0",
                        'createOption' => 'Empty',
                        'diskSizeGB' => 512,
                        'managedDisk' => [
                            'storageAccountType' => 'Premium_LRS'
                        ],
                        'caching' => 'ReadOnly'
                    ]
                ]
            ],
            'osProfile' => [
                'computerName' => substr($vmName, 0, 15),
                'adminUsername' => 'azureadmin',
                'adminPassword' => 'SecureP@ssw0rd123!',
                'windowsConfiguration' => [
                    'provisionVMAgent' => true,
                    'enableAutomaticUpdates' => true,
                    'timeZone' => 'UTC'
                ]
            ],
            'networkProfile' => [
                'networkInterfaces' => [
                    [
                        'id' => $nicId,
                        'properties' => [
                            'primary' => true
                        ]
                    ]
                ]
            ],
            // Optional: Boot diagnostics
            'diagnosticsProfile' => [
                'bootDiagnostics' => [
                    'enabled' => true
                ]
            ]
        ],
        // Optional: Tags
        'tags' => [
            'Environment' => 'Development',
            'Project' => 'MyProject',
            'CreatedBy' => 'PHP-SDK',
            'CreatedAt' => date('Y-m-d H:i:s')
        ]
    ];

    echo "Creating VM with advanced configuration: {$vmName}\n";
    echo "VM Size: Standard_B2s\n";
    echo "OS: Windows Server 2022 Datacenter\n";
    echo "OS Disk: 256GB Premium SSD\n";
    echo "Data Disk: 512GB Premium SSD\n\n";

    // Create the VM
    $result = $vmClient->createOrUpdateVm(
        $subscriptionId,
        $resourceGroup,
        $vmName,
        $vmPayload
    );

    echo "VM Creation Response:\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";

    echo "\n✓ VM created successfully with advanced configuration!\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
