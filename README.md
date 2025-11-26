# Azure PHP VM SDK

A PHP SDK for managing Azure Virtual Machines with a simple and intuitive API.

## Features

- ✅ Create, read, update, and delete Azure VMs
- ✅ Start, stop, restart, and deallocate VMs
- ✅ Get VM instance view and power state
- ✅ List available VM sizes by region
- ✅ Automatic network interface and public IP creation
- ✅ Support for Windows Server VMs
- ✅ OAuth2 authentication with token caching

## Installation

```bash
composer require your-vendor/azure-php-vm-sdk
```

## Prerequisites

Before using this SDK, you need:

1. **Azure Subscription** - An active Azure subscription
2. **Service Principal** - Create a service principal with appropriate permissions:
   ```bash
   az ad sp create-for-rbac --name "my-app" --role Contributor --scopes /subscriptions/{subscription-id}
   ```
3. **Resource Group** - An existing resource group
4. **Virtual Network & Subnet** - An existing VNet and subnet for VM networking

## Configuration

Create a `.env` file in your project root:

```env
AZURE_TENANT_ID=your-tenant-id
AZURE_CLIENT_ID=your-client-id
AZURE_CLIENT_SECRET=your-client-secret
AZURE_SUBSCRIPTION_ID=your-subscription-id
AZURE_RESOURCE_GROUP=your-resource-group
```

## Quick Start

### Basic VM Creation

```php
<?php
require 'vendor/autoload.php';
use AzureVmSdk\AzureClient;
use AzureVmSdk\VmClient;

// Initialize client
$azure = new AzureClient($tenantId, $clientId, $clientSecret);
$vmClient = new VmClient($azure);

// Create a VM
$result = $vmClient->createVM(
    subscriptionId: $subscriptionId,
    resourceGroup: $resourceGroup,
    location: 'eastus',
    vmName: 'my-vm',
    subnetId: '/subscriptions/.../subnets/default',
    ramGB: 4,
    cpuCores: 2,
    diskSizeGB: 128,
    adminUsername: 'azureuser',
    adminPassword: 'P@ssw0rd123!',
    dedicatedAdminRdp: true
);
```

## Examples

The `examples/` directory contains working examples:

### 1. List VMs
```bash
php examples/list_vms.php
```
Lists all VMs in the specified resource group.

### 2. Get VM Details
```bash
php examples/get_vm.php
```
Retrieves detailed information about a specific VM.

### 3. Create VM (Simple)
```bash
php examples/create_vm.php
```
Creates a new VM with automatic VM size selection based on CPU and RAM requirements.

**Features:**
- Automatic VM size selection
- Automatic NIC creation
- Optional public IP for RDP access
- Windows Server 2019 Datacenter

### 4. Create VM (Advanced)
```bash
php examples/create_vm_advanced.php
```
Creates a VM with advanced configuration options.

**Features:**
- Custom VM size specification
- Data disk attachment
- Boot diagnostics
- Resource tagging
- Windows Server 2022 Datacenter

### 5. Manage VM
```bash
php examples/manage_vm.php
```
Demonstrates VM lifecycle operations: start, restart, power off, deallocate, and delete.

## API Reference

### VmClient Methods

#### `createVM()`
High-level method to create a VM with automatic configuration.

```php
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
): array
```

**Parameters:**
- `$subscriptionId` - Azure subscription ID
- `$resourceGroup` - Resource group name
- `$location` - Azure region (e.g., 'eastus', 'westus2')
- `$vmName` - Name for the VM
- `$subnetId` - Full resource ID of the subnet
- `$ramGB` - Minimum RAM in GB
- `$cpuCores` - Minimum CPU cores
- `$diskSizeGB` - OS disk size in GB
- `$adminUsername` - Administrator username
- `$adminPassword` - Administrator password
- `$dedicatedAdminRdp` - Create public IP for RDP access (default: false)
- `$os` - Operating system (default: 'Windows')

#### `createOrUpdateVm()`
Low-level method for creating or updating a VM with full control.

```php
public function createOrUpdateVm(
    string $subscriptionId,
    string $resourceGroup,
    string $vmName,
    array $vmPayload
): array
```

#### `listVms()`
List all VMs in a resource group.

```php
public function listVms(
    string $subscriptionId,
    string $resourceGroup
): array
```

#### `getVm()`
Get details of a specific VM.

```php
public function getVm(
    string $subscriptionId,
    string $resourceGroup,
    string $vmName
): array
```

#### `getInstanceView()`
Get the instance view of a VM (includes power state).

```php
public function getInstanceView(
    string $subscriptionId,
    string $resourceGroup,
    string $vmName
): array
```

#### `startVm()`
Start a stopped VM.

```php
public function startVm(
    string $subscriptionId,
    string $resourceGroup,
    string $vmName
): array
```

#### `powerOffVm()`
Power off a running VM (still incurs compute charges).

```php
public function powerOffVm(
    string $subscriptionId,
    string $resourceGroup,
    string $vmName
): array
```

#### `restartVm()`
Restart a VM.

```php
public function restartVm(
    string $subscriptionId,
    string $resourceGroup,
    string $vmName
): array
```

#### `deallocateVm()`
Deallocate a VM (stops compute charges).

```php
public function deallocateVm(
    string $subscriptionId,
    string $resourceGroup,
    string $vmName
): array
```

#### `deleteVm()`
Delete a VM.

```php
public function deleteVm(
    string $subscriptionId,
    string $resourceGroup,
    string $vmName
): array
```

#### `getAvailableVMSizes()`
Get available VM sizes for a location.

```php
public function getAvailableVMSizes(
    string $subscriptionId,
    string $location
): array
```

## VM Sizes

The SDK automatically selects an appropriate VM size based on your CPU and RAM requirements. Common sizes include:

| Size | vCPUs | RAM (GB) | Use Case |
|------|-------|----------|----------|
| Standard_B1s | 1 | 1 | Development/Testing |
| Standard_B2s | 2 | 4 | Small workloads |
| Standard_D2s_v3 | 2 | 8 | General purpose |
| Standard_D4s_v3 | 4 | 16 | Medium workloads |
| Standard_E4s_v3 | 4 | 32 | Memory-intensive |

## Error Handling

The SDK throws `AzureVmSdk\Exceptions\ApiException` for API errors:

```php
try {
    $result = $vmClient->createVM(...);
} catch (\AzureVmSdk\Exceptions\ApiException $e) {
    echo "API Error: " . $e->getMessage();
    echo "Status Code: " . $e->getCode();
}
```

## Best Practices

1. **Password Requirements**: Azure requires strong passwords:
   - 12-123 characters
   - Mix of uppercase, lowercase, numbers, and special characters

2. **VM Naming**: VM names must:
   - Be 1-15 characters for Windows
   - Contain only alphanumeric characters and hyphens
   - Start with a letter

3. **Cost Management**:
   - Use `deallocateVm()` instead of `powerOffVm()` to stop compute charges
   - Choose appropriate VM sizes for your workload
   - Use Standard_LRS for development, Premium_LRS for production

4. **Security**:
   - Avoid creating public IPs unless necessary
   - Use Network Security Groups to restrict access
   - Rotate credentials regularly

## License

MIT

## Contributing

Contributions are welcome! Please submit pull requests or open issues.

## Support

For issues and questions, please open an issue on GitHub.