# VM Quota Checking Feature - Summary

## Overview
Added comprehensive quota checking functionality to the Azure PHP VM SDK, allowing users to check VM quotas for CPUs, VMs, disks, and other compute resources before creating VMs.

## New Methods Added to VmClient

### 1. `getComputeUsages()`
- **Purpose**: Get all compute resource usage and quota information for a specific location
- **Returns**: Array of usage information including vCPUs, VMs, disks, public IPs, and network interfaces
- **Use Case**: Get raw quota data for detailed analysis

### 2. `checkAvailableQuota()`
- **Purpose**: Check if there is available quota for creating VMs with specific core requirements
- **Parameters**: 
  - `$subscriptionId` - Azure subscription ID
  - `$location` - Azure region
  - `$requiredCores` - Number of CPU cores needed (default: 1)
- **Returns**: 
  - `hasAvailableQuota` - Boolean indicating if quota is available
  - `quotas` - Detailed quota information for all resources
  - `warnings` - Array of warning messages
- **Use Case**: Validate quota before VM creation in automated scripts

### 3. `getQuotaSummary()`
- **Purpose**: Get a formatted, human-readable summary of quota usage
- **Returns**: Formatted summary with usage statistics, available quota, and percentage used
- **Use Case**: Display quota information to users or in dashboards

### 4. `getQuotaByResource()`
- **Purpose**: Get quota information for a specific resource using the Azure Quota API
- **Parameters**: 
  - `$resourceName` - Resource name (e.g., 'standardNDSFamily', 'virtualMachines')
- **Use Case**: Check quota for specific VM families or resource types

### 5. `getAllQuotas()`
- **Purpose**: Get all quotas for compute resources in a specific location
- **Returns**: List of all quota information from the Azure Quota API
- **Use Case**: Comprehensive quota analysis

## Example Scripts Created

### 1. `examples/check_quota.php`
Demonstrates basic quota checking functionality:
- Displays quota summary for all compute resources
- Shows current usage vs. limits
- Checks availability for specific core requirements
- Warns when quota usage is high (>80%)

**Usage:**
```bash
php examples/check_quota.php
```

### 2. `examples/create_vm_with_quota_check.php`
Advanced example showing pre-creation quota validation:
- Validates quota before VM creation
- Shows visual quota usage bars
- Prevents creation if insufficient quota
- Displays before/after quota status
- Integrates with VM creation workflow

**Usage:**
```bash
php examples/create_vm_with_quota_check.php
```

## API Endpoints Used

The implementation uses two Azure REST API endpoints:

1. **Microsoft.Compute Usages API**
   - Endpoint: `/subscriptions/{subscriptionId}/providers/Microsoft.Compute/locations/{location}/usages`
   - Returns current usage and limits for all compute resources
   - No special prerequisites required

2. **Microsoft.Quota API** (optional, for advanced scenarios)
   - Endpoint: `/subscriptions/{subscriptionId}/providers/Microsoft.Compute/locations/{location}/providers/Microsoft.Quota/quotas`
   - Provides more detailed quota information
   - May require `Microsoft.Quota` resource provider registration

## Key Features

✅ **Comprehensive Coverage**: Checks all compute quotas including:
   - Total regional vCPUs
   - VM family-specific vCPUs (e.g., D-series, E-series, DC-series)
   - Virtual machines count
   - Disks (standard and premium)
   - Public IP addresses
   - Network interfaces

✅ **Smart Validation**: 
   - Automatically checks if required cores are available
   - Warns when quota usage exceeds 80%
   - Provides detailed error messages

✅ **User-Friendly Output**:
   - Formatted summaries with usage percentages
   - Visual progress bars in examples
   - Clear warning and error messages

✅ **Integration Ready**:
   - Easy to integrate into existing VM creation workflows
   - Can be used in automated deployment scripts
   - Prevents failed deployments due to quota limits

## Documentation Updates

Updated `README.md` with:
- Added quota checking to features list
- Added two new example scripts documentation
- Added API reference for all 5 quota methods
- Added quota management best practices section

## Usage Example

```php
<?php
use AzureVmSdk\AzureClient;
use AzureVmSdk\VmClient;

$azure = new AzureClient($tenantId, $clientId, $clientSecret);
$vmClient = new VmClient($azure);

// Check if we have quota for 16 cores
$quotaCheck = $vmClient->checkAvailableQuota(
    $subscriptionId, 
    'eastus', 
    16
);

if ($quotaCheck['hasAvailableQuota']) {
    echo "✓ Sufficient quota available!\n";
    // Proceed with VM creation
} else {
    echo "✗ Insufficient quota!\n";
    foreach ($quotaCheck['warnings'] as $warning) {
        echo "  • {$warning}\n";
    }
}
```

## Best Practices

1. **Always check quota before creating VMs** - Prevents failed deployments
2. **Monitor quota usage regularly** - Avoid hitting limits unexpectedly
3. **Request quota increases proactively** - For planned large deployments
4. **Use in automated scripts** - Integrate `checkAvailableQuota()` in CI/CD pipelines
5. **Set up alerts** - When quota usage exceeds 80%

## Files Modified

1. `/home/aalehuzoor/Projects/azure-php-vm-sdk/src/VmClient.php` - Added 5 quota methods
2. `/home/aalehuzoor/Projects/azure-php-vm-sdk/README.md` - Updated documentation
3. `/home/aalehuzoor/Projects/azure-php-vm-sdk/examples/check_quota.php` - New example
4. `/home/aalehuzoor/Projects/azure-php-vm-sdk/examples/create_vm_with_quota_check.php` - New example
5. `/home/aalehuzoor/Projects/azure-php-vm-sdk/examples/create_vm.php` - Fixed Gen2 image issue

## Testing

To test the quota checking functionality:

```bash
# Check current quota status
php examples/check_quota.php

# Create VM with quota validation
php examples/create_vm_with_quota_check.php
```
