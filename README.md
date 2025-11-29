# Azure PHP VM SDK

A minimal PHP SDK for managing Azure Virtual Machines using the Azure ARM REST API. This library provides a fluent, builder-based interface for creating and managing VMs, Network Interfaces, and Public IPs.

## Features

- **Authentication**: Easy integration with Azure Active Directory via Client Credentials flow.
- **VM Management**: Create, start, stop, delete, and list Virtual Machines.
- **Network Management**: Create and manage Network Interfaces and Public IPs.
- **Builder Pattern**: Fluent builders for constructing complex resource configurations (VMs, NICs, Public IPs).
- **Helper Utilities**: Fetch available OS images, VM sizes, and publishers.

## Requirements

- PHP ^8.1
- Guzzle HTTP Client ^7.0

## Installation

Install the package via Composer:

```bash
composer require digiscalers/azure-vm-sdk
```

## Configuration

You need an Azure Service Principal with appropriate permissions (e.g., Contributor) on your Subscription or Resource Group.

Create a `.env` file in your project root (or configure your environment variables accordingly):

```env
AZURE_TENANT_ID=your-tenant-id
AZURE_CLIENT_ID=your-client-id
AZURE_CLIENT_SECRET=your-client-secret
AZURE_SUBSCRIPTION_ID=your-subscription-id
AZURE_RESOURCE_GROUP=your-resource-group
AZURE_REGION=eastus
```

## Usage

### 1. Initialize the Client

```php
use AzureVmSdk\AzureClient;
use AzureVmSdk\VmClient;
use AzureVmSdk\NetworkInterfaceClient;

$azure = new AzureClient(
    getenv('AZURE_TENANT_ID'),
    getenv('AZURE_CLIENT_ID'),
    getenv('AZURE_CLIENT_SECRET')
);

$vmClient = new VmClient($azure);
$nicClient = new NetworkInterfaceClient($azure);
```

### 2. Create a Public IP

```php
use AzureVmSdk\PublicIpBuilder;

$publicIpPayload = (new PublicIpBuilder())
    ->setLocation('eastus')
    ->setSku('Standard')
    ->setAllocationMethod('Static')
    ->setDomainNameLabel('my-unique-dns-label')
    ->build();

$nicClient->createPublicIp(
    'subscription-id',
    'resource-group',
    'eastus',
    'my-public-ip',
    $publicIpPayload
);
```

### 3. Create a Network Interface

```php
use AzureVmSdk\NetworkInterfaceBuilder;

$nicPayload = (new NetworkInterfaceBuilder())
    ->setLocation('eastus')
    ->setSubnet('/subscriptions/.../subnets/mySubnet')
    ->setPublicIp('/subscriptions/.../publicIPAddresses/my-public-ip')
    ->setNetworkSecurityGroup('subscription-id', 'resource-group', 'my-nsg') // Optional
    ->build();

$nicClient->createNetworkInterface(
    'subscription-id',
    'resource-group',
    'my-nic',
    $nicPayload
);
```

### 4. Create a Virtual Machine

```php
use AzureVmSdk\VmBuilder;

$vmPayload = (new VmBuilder())
    ->setLocation('eastus')
    ->setVmSize('Standard_B2s')
    ->setImageReference([
        'publisher' => 'Canonical',
        'offer' => 'UbuntuServer',
        'sku' => '18.04-LTS',
        'version' => 'latest'
    ])
    ->setOsDisk('my-vm-os-disk', 128, 'Standard_LRS')
    ->setOsProfile('my-vm', 'azureuser', 'P@ssw0rd123!')
    ->addNetworkInterface('/subscriptions/.../networkInterfaces/my-nic', true)
    ->build();

$result = $vmClient->createOrUpdateVm(
    'subscription-id',
    'resource-group',
    'my-vm',
    $vmPayload
);
```

### 5. List Resources

**List VMs:**
```php
$vms = $vmClient->listVms('subscription-id', 'resource-group');
foreach ($vms as $vm) {
    echo $vm['name'] . "\n";
}
```

**List Network Interfaces:**
```php
$nics = $nicClient->listNetworkInterfaces('subscription-id', 'resource-group');
```

**List Public IPs:**
```php
$ips = $nicClient->listPublicIps('subscription-id', 'resource-group');
```

### 6. Helper Functions

**Get Available OS Types:**
```php
$osTypes = $vmClient->getAvailableOSTypes('subscription-id', 'eastus');
```

**Get Available VM Sizes:**
```php
$sizes = $vmClient->getAvailableVmSizes('subscription-id', 'eastus');
```

## License

MIT
