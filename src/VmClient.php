<?php
namespace AzureVmSdk;

class VmClient {
    private AzureClient $client;
    private NetworkInterfaceClient $networkInterfaceClient;

    public function __construct(AzureClient $client) {
        $this->client = $client;
        $this->networkInterfaceClient = new NetworkInterfaceClient($client);
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

    /**
     * Get available VM image publishers for a specific location
     * 
     * @param string $subscriptionId Azure subscription ID
     * @param string $location Azure region (e.g., 'eastus', 'westus')
     * @return array List of available publishers
     */
    public function getAvailablePublishers(string $subscriptionId, string $location): array {
        $path = "/subscriptions/{$subscriptionId}/providers/Microsoft.Compute/locations/{$location}/publishers";
        $response = $this->client->request('GET', $path, []);
        return $this->extractList($response);
    }

    /**
     * Get available VM image offers for a specific publisher and location
     * 
     * @param string $subscriptionId Azure subscription ID
     * @param string $location Azure region (e.g., 'eastus', 'westus')
     * @param string $publisher Publisher name (e.g., 'MicrosoftWindowsServer', 'Canonical')
     * @return array List of available offers
     */
    public function getAvailableOffers(string $subscriptionId, string $location, string $publisher): array {
        $path = "/subscriptions/{$subscriptionId}/providers/Microsoft.Compute/locations/{$location}/publishers/{$publisher}/artifacttypes/vmimage/offers";
        $response = $this->client->request('GET', $path, []);
        return $this->extractList($response);
    }

    /**
     * Get available VM image SKUs for a specific publisher, offer, and location
     * 
     * @param string $subscriptionId Azure subscription ID
     * @param string $location Azure region (e.g., 'eastus', 'westus')
     * @param string $publisher Publisher name (e.g., 'MicrosoftWindowsServer', 'Canonical')
     * @param string $offer Offer name (e.g., 'WindowsServer', 'UbuntuServer')
     * @return array List of available SKUs
     */
    public function getAvailableSKUs(string $subscriptionId, string $location, string $publisher, string $offer): array {
        $path = "/subscriptions/{$subscriptionId}/providers/Microsoft.Compute/locations/{$location}/publishers/{$publisher}/artifacttypes/vmimage/offers/{$offer}/skus";
        $response = $this->client->request('GET', $path, []);
        return $this->extractList($response);
    }

    /**
     * Get available OS types (VM images) for a specific location
     * 
     * @param string $subscriptionId Azure subscription ID
     * @param string $location Azure region (e.g., 'eastus')
     * @param string|null $publisher Filter by publisher (e.g., 'MicrosoftWindowsServer', 'Canonical')
     * @param string|null $offer Filter by offer (e.g., 'WindowsServer', 'UbuntuServer')
     * @return array List of available VM images
     */
    public function getAvailableOSTypes(string $subscriptionId, string $location, ?string $publisher = null, ?string $offer = null): array {
        // If publisher and offer are provided, get SKUs
        if ($publisher && $offer) {
            $path = "/subscriptions/{$subscriptionId}/providers/Microsoft.Compute/locations/{$location}/publishers/{$publisher}/artifacttypes/vmimage/offers/{$offer}/skus";
            $response = $this->client->request('GET', $path, []);
            return $this->extractList($response);
        }
        
        // If only publisher is provided, get offers
        if ($publisher) {
            $path = "/subscriptions/{$subscriptionId}/providers/Microsoft.Compute/locations/{$location}/publishers/{$publisher}/artifacttypes/vmimage/offers";
            $response = $this->client->request('GET', $path, []);
            return $this->extractList($response);
        }
        
        // Otherwise, get all publishers
        $path = "/subscriptions/{$subscriptionId}/providers/Microsoft.Compute/locations/{$location}/publishers";
        $response = $this->client->request('GET', $path, []);
        return $this->extractList($response);
    }

    private function extractList(array $response): array {
        if (isset($response['value']) && is_array($response['value'])) {
            return $response['value'];
        }
        // If response is a numeric array (list), return it directly
        if (array_is_list($response) && !empty($response)) {
            return $response;
        }
        return [];
    }



    public function createVM(
        string $subscriptionId,
        string $resourceGroup,
        string $location,
        string $vmName,
        string $subnetId,
        string $vmSize,
        int $diskSizeGB,
        string $adminUsername,
        string $adminPassword,
        array $imageReference = [
            'publisher' => 'MicrosoftWindowsServer',
            'offer' => 'WindowsServer',
            'sku' => '2019-Datacenter',
            'version' => 'latest'
        ],
        bool $dedicatedAdminRdp = false,
    ): array {
        // Prepare Network Interface name and ID
        $nicName = "{$vmName}-nic";
        $nicId = "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Network/networkInterfaces/{$nicName}";
        
        $publicIpId = null;
        
        // Create Public IP if dedicated admin RDP is requested
        if ($dedicatedAdminRdp) {
            $publicIpName = "{$vmName}-pip";
            $domainNameLabel = strtolower($vmName . '-' . substr(md5(uniqid()), 0, 6)); // Ensure uniqueness
            
            $this->networkInterfaceClient->createPublicIp(
                $subscriptionId,
                $resourceGroup,
                $location,
                $publicIpName,
                $domainNameLabel
            );
            
            $publicIpId = "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Network/publicIPAddresses/{$publicIpName}";
        }

        // Create Network Interface
        $this->networkInterfaceClient->createNetworkInterface(
            $subscriptionId,
            $resourceGroup,
            $location,
            $nicName,
            $subnetId,
            $publicIpId
        );

        // Prepare VM payload
        $vmPayload = [
            'location' => $location,
            'properties' => [
                'hardwareProfile' => [
                    'vmSize' => $vmSize
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

        // Create VM
        return $this->createOrUpdateVm($subscriptionId, $resourceGroup, $vmName, $vmPayload);
    }

    public function getQuotaByResource(string $subscriptionId, string $location, string $resourceName): array {
        $path = "/subscriptions/{$subscriptionId}/providers/Microsoft.Compute/locations/{$location}/providers/Microsoft.Quota/quotas/{$resourceName}";
        return $this->client->request('GET', $path, []);
    }

    public function getComputeUsages(string $subscriptionId, string $location): array {
        $path = "/subscriptions/{$subscriptionId}/providers/Microsoft.Compute/locations/{$location}/usages";
        $response = $this->client->request('GET', $path, []);
        return $response['value'] ?? [];
    }

    public function getAllQuotas(string $subscriptionId, string $location): array {
        $path = "/subscriptions/{$subscriptionId}/providers/Microsoft.Compute/locations/{$location}/providers/Microsoft.Quota/quotas";
        $response = $this->client->request('GET', $path, []);
        return $response['value'] ?? [];
    }
}