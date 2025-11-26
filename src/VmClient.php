<?php
namespace AzureVmSdk;

class VmClient {
    private AzureClient $client;
    public function __construct(AzureClient $client) {
        $this->client = $client;
    }

    public function listVms(string $subscriptionId, string $resourceGroup): array {
        $path = "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/virtualMachines";
        $response = $this->client->request('GET', $path, []);
        return $response['value'] ?? [];
    }

    public function getVm(string $subscriptionId, string $resourceGroup, string $vmName): array {
        $path = "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/virtualMachines/{$vmName}";
        return $this->client->request('GET', $path, []);
    }

    public function getInstanceView(string $subscriptionId, string $resourceGroup, string $vmName): array {
        $path = "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/virtualMachines/{$vmName}/instanceView";
        return $this->client->request('GET', $path, []);
    }

    public function createOrUpdateVm(string $subscriptionId, string $resourceGroup, string $vmName, array $vmPayload): array {
        $path = "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/virtualMachines/{$vmName}";
        return $this->client->request('PUT', $path, [], $vmPayload);
    }

    public function deleteVm(string $subscriptionId, string $resourceGroup, string $vmName): array {
        $path = "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/virtualMachines/{$vmName}";
        return $this->client->request('DELETE', $path, []);
    }

    public function startVm(string $subscriptionId, string $resourceGroup, string $vmName): array {
        $path = "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/virtualMachines/{$vmName}/start";
        return $this->client->request('POST', $path, []);
    }

    public function powerOffVm(string $subscriptionId, string $resourceGroup, string $vmName): array {
        $path = "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/virtualMachines/{$vmName}/powerOff";
        return $this->client->request('POST', $path, []);
    }

    public function restartVm(string $subscriptionId, string $resourceGroup, string $vmName): array {
        $path = "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/virtualMachines/{$vmName}/restart";
        return $this->client->request('POST', $path, []);
    }

    public function deallocateVm(string $subscriptionId, string $resourceGroup, string $vmName): array {
        $path = "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/virtualMachines/{$vmName}/deallocate";
        return $this->client->request('POST', $path, []);
    }

    public function getAvailableVMSizes(string $subscriptionId, string $location): array {
        $path = "/subscriptions/{$subscriptionId}/providers/Microsoft.Compute/locations/{$location}/vmSizes";
        $response = $this->client->request('GET', $path, []);
        return $response['value'] ?? [];
    }

    public function createVM(
        string $subscriptionId,
        string $resourceGroup,
        string $location,
        string $vmName,
        string $subnetId,
        int $ramGB,
        int $cpuCores,
        int $diskSizeGB,
        string $adminUsername,
        string $adminPassword,
        bool $dedicatedAdminRdp = false,
        string $os = 'Windows'
    ): array {
        // 1. Select VM Size
        $vmSizes = $this->getAvailableVMSizes($subscriptionId, $location);
        $selectedSize = null;
        
        // Simple selection logic: find first size that meets requirements
        // Note: Azure VM sizes are strings like 'Standard_D2s_v3'. 
        // We need to parse or rely on a mapping if we want to be precise.
        // However, the API response for vmSizes includes numberOfCores and memoryInMB.
        
        foreach ($vmSizes as $size) {
            $memoryGB = ($size['memoryInMB'] ?? 0) / 1024;
            $cores = $size['numberOfCores'] ?? 0;
            
            if ($cores >= $cpuCores && $memoryGB >= $ramGB) {
                $selectedSize = $size['name'];
                break;
            }
        }

        if (!$selectedSize) {
            throw new \RuntimeException("No suitable VM size found for {$cpuCores} cores and {$ramGB}GB RAM in {$location}");
        }

        // 2. Prepare Network Interface
        // We need to create a NIC. For simplicity, we'll name it {$vmName}-nic
        $nicName = "{$vmName}-nic";
        $nicId = "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Network/networkInterfaces/{$nicName}";
        
        // We need to create the NIC first.
        $nicPayload = [
            'location' => $location,
            'properties' => [
                'ipConfigurations' => [
                    [
                        'name' => 'ipconfig1',
                        'properties' => [
                            'subnet' => [
                                'id' => $subnetId
                            ],
                            'privateIPAllocationMethod' => 'Dynamic'
                        ]
                    ]
                ]
            ]
        ];

        if ($dedicatedAdminRdp) {
            // Create Public IP
            $publicIpName = "{$vmName}-pip";
            $publicIpId = "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Network/publicIPAddresses/{$publicIpName}";
            
            $publicIpPayload = [
                'location' => $location,
                'properties' => [
                    'publicIPAllocationMethod' => 'Dynamic',
                    'dnsSettings' => [
                        'domainNameLabel' => strtolower($vmName . '-' . substr(md5(uniqid()), 0, 6)) // Ensure uniqueness
                    ]
                ]
            ];
            
            $this->client->request('PUT', $publicIpId, [], $publicIpPayload);

            // Attach Public IP to NIC
            $nicPayload['properties']['ipConfigurations'][0]['properties']['publicIPAddress'] = [
                'id' => $publicIpId
            ];
        }

        $this->client->request('PUT', $nicId, [], $nicPayload);

        // 3. Prepare VM Payload
        $imageReference = [
            'publisher' => 'MicrosoftWindowsServer',
            'offer' => 'WindowsServer',
            'sku' => '2019-Datacenter',
            'version' => 'latest'
        ];

        if (strtolower($os) !== 'windows') {
             // Fallback or error. For now, defaulting to Windows as requested.
             // If user passes something else, we might want to handle it, but per requirements "just windos for now"
        }

        $vmPayload = [
            'location' => $location,
            'properties' => [
                'hardwareProfile' => [
                    'vmSize' => $selectedSize
                ],
                'storageProfile' => [
                    'imageReference' => $imageReference,
                    'osDisk' => [
                        'name' => "{$vmName}_OsDisk",
                        'caching' => 'ReadWrite',
                        'createOption' => 'FromImage',
                        'managedDisk' => [
                            'storageAccountType' => 'Standard_LRS'
                        ],
                        'diskSizeGB' => $diskSizeGB
                    ]
                ],
                'osProfile' => [
                    'computerName' => substr($vmName, 0, 15), // Windows computer name limit
                    'adminUsername' => $adminUsername,
                    'adminPassword' => $adminPassword
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
                ]
            ]
        ];

        // 4. Create VM
        return $this->createOrUpdateVm($subscriptionId, $resourceGroup, $vmName, $vmPayload);
    }
}