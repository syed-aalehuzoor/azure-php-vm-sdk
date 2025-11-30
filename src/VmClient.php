<?php
namespace AzureVmSdk;

class VmClient {
    private AzureClient $client;


    public function __construct(AzureClient $client) {
        $this->client = $client;

    }

    public function listVms(string $subscriptionId, string $resourceGroup): array {
        return $this->client->request('GET', "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/virtualMachines", []);
    }

    public function getVm(string $subscriptionId, string $resourceGroup, string $vmName): array {
        return $this->client->request('GET', "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/virtualMachines/{$vmName}", []);
    }

    public function getInstanceView(string $subscriptionId, string $resourceGroup, string $vmName): array {
        return $this->client->request('GET', "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/virtualMachines/{$vmName}/instanceView", []);
    }

    public function createOrUpdateVm(string $subscriptionId, string $resourceGroup, string $vmName, array $vmPayload): array {
        return $this->client->request('PUT', "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/virtualMachines/{$vmName}", [], $vmPayload);
    }

    public function startVm(string $subscriptionId, string $resourceGroup, string $vmName): array {
        return $this->client->request('POST', "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/virtualMachines/{$vmName}/start", []);
    }

    public function restartVm(string $subscriptionId, string $resourceGroup, string $vmName): array {
        return $this->client->request('POST', "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/virtualMachines/{$vmName}/restart", []);
    }

    public function powerOffVm(string $subscriptionId, string $resourceGroup, string $vmName): array {
        return $this->client->request('POST', "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/virtualMachines/{$vmName}/powerOff", []);
    }

    public function deleteVm(string $subscriptionId, string $resourceGroup, string $vmName): array {
        return $this->client->request('DELETE', "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/virtualMachines/{$vmName}", []);
    }

    public function deallocateVm(string $subscriptionId, string $resourceGroup, string $vmName): array {
        return $this->client->request('POST', "/subscriptions/{$subscriptionId}/resourceGroups/{$resourceGroup}/providers/Microsoft.Compute/virtualMachines/{$vmName}/deallocate", []);
    }

    public function getAvailableVMSizes(string $subscriptionId, string $location): array {
        return $this->client->request('GET', "/subscriptions/{$subscriptionId}/providers/Microsoft.Compute/locations/{$location}/vmSizes", []);
    }

    public function getQuotaByResource(string $subscriptionId, string $location, string $resourceName): array {
        return $this->client->request('GET', "/subscriptions/{$subscriptionId}/providers/Microsoft.Compute/locations/{$location}/providers/Microsoft.Quota/quotas/{$resourceName}", []);
    }

    public function getComputeUsages(string $subscriptionId, string $location): array {
        return $this->client->request('GET', "/subscriptions/{$subscriptionId}/providers/Microsoft.Compute/locations/{$location}/usages", []);
    }

    public function getAllQuotas(string $subscriptionId, string $location): array {
        return $this->client->request('GET', "/subscriptions/{$subscriptionId}/providers/Microsoft.Compute/locations/{$location}/providers/Microsoft.Quota/quotas", []);
    }
}