<?php
namespace AzureVmSdk;

class NetworkInterfaceClient {
    private AzureClient $client;

    public function __construct(AzureClient $client) {
        $this->client = $client;
    }

    /**
     * Create a Public IP Address
     * 
     * @param string $subscriptionId Azure subscription ID
     * @param string $resourceGroup Resource group name
     * @param string $location Azure region (e.g., 'eastus', 'westus')
     * @param string $publicIpName Name for the public IP
     * @param string $domainNameLabel DNS domain name label for the public IP
     * @return array Created Public IP resource
     */
    public function createPublicIp(
        string $subscriptionId,
        string $resourceGroup,
        string $location,
        string $publicIpName,
        string $domainNameLabel
    ): array {
        $publicIpId = "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Network/publicIPAddresses/{$publicIpName}";
        
        $publicIpPayload = [
            'location' => $location,
            'properties' => [
                'publicIPAllocationMethod' => 'Dynamic',
                'dnsSettings' => [
                    'domainNameLabel' => $domainNameLabel
                ]
            ]
        ];
        
        return $this->client->request('PUT', $publicIpId, [], $publicIpPayload);
    }

    /**
     * Create a Network Interface
     * 
     * @param string $subscriptionId Azure subscription ID
     * @param string $resourceGroup Resource group name
     * @param string $location Azure region (e.g., 'eastus', 'westus')
     * @param string $nicName Name for the network interface
     * @param string $subnetId Full resource ID of the subnet
     * @param string|null $publicIpId Optional full resource ID of the public IP to attach
     * @return array Created Network Interface resource
     */
    public function createNetworkInterface(
        string $subscriptionId,
        string $resourceGroup,
        string $location,
        string $nicName,
        string $subnetId,
        ?string $publicIpId = null
    ): array {
        $nicId = "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Network/networkInterfaces/{$nicName}";
        
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

        // Attach Public IP if provided
        if ($publicIpId !== null) {
            $nicPayload['properties']['ipConfigurations'][0]['properties']['publicIPAddress'] = [
                'id' => $publicIpId
            ];
        }

        return $this->client->request('PUT', $nicId, [], $nicPayload);
    }
}
