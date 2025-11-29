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
        array $publicIpPayload
    ): array {      
        return $this->client->request(
            'PUT', "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Network/publicIPAddresses/{$publicIpName}",
            [], $publicIpPayload
        );
    }

    /**
     * Get a Public IP Address
     * 
     * @param string $subscriptionId Azure subscription ID
     * @param string $resourceGroup Resource group name
     * @param string $publicIpName Name for the public IP
     * @return array Public IP resource
     */
    public function getPublicIp(
        string $subscriptionId,
        string $resourceGroup,
        string $publicIpName
    ): array {
        return $this->client->request(
            'GET',
            "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Network/publicIPAddresses/{$publicIpName}"
        );
    }

    /**
     * Create a Network Interface
     * 
     * @param string $subscriptionId Azure subscription ID
     * @param string $resourceGroup Resource group name
     * @param string $nicName Name for the network interface
     * @param array $nicPayload Network Interface payload
     * @return array Created Network Interface resource
     */
    public function createNetworkInterface(
        string $subscriptionId,
        string $resourceGroup,
        string $nicName,
        array $nicPayload
    ): array {
        return $this->client->request(
            'PUT',
            "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Network/networkInterfaces/{$nicName}",
            [],
            $nicPayload
        );
    }

    /**
     * Get a Network Interface
     * 
     * @param string $subscriptionId Azure subscription ID
     * @param string $resourceGroup Resource group name
     * @param string $nicName Name for the network interface
     * @return array Network Interface resource
     */
    public function getNetworkInterface(
        string $subscriptionId,
        string $resourceGroup,
        string $nicName
    ): array {
        return $this->client->request(
            'GET',
            "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Network/networkInterfaces/{$nicName}"
        );
    }

    /**
     * List Public IP Addresses
     * 
     * @param string $subscriptionId Azure subscription ID
     * @param string $resourceGroup Resource group name
     * @return array List of Public IP resources
     */
    public function listPublicIps(
        string $subscriptionId,
        string $resourceGroup
    ): array {
        return $this->client->request(
            'GET',
            "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Network/publicIPAddresses"
        );
    }

    /**
     * List Network Interfaces
     * 
     * @param string $subscriptionId Azure subscription ID
     * @param string $resourceGroup Resource group name
     * @return array List of Network Interface resources
     */
    public function listNetworkInterfaces(
        string $subscriptionId,
        string $resourceGroup
    ): array {
        return $this->client->request(
            'GET',
            "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Network/networkInterfaces"
        );
    }
}
