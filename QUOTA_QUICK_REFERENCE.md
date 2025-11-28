# Quick Reference: Quota Checking Methods

## Method Overview

| Method | Purpose | Returns |
|--------|---------|---------|
| `getComputeUsages()` | Get all quota data | Raw usage array |
| `checkAvailableQuota()` | Validate quota availability | Status + warnings |
| `getQuotaSummary()` | Get formatted summary | Human-readable summary |
| `getQuotaByResource()` | Get specific resource quota | Single resource data |
| `getAllQuotas()` | Get all quotas (Quota API) | Detailed quota list |

## Quick Examples

### 1. Simple Quota Check
```php
$summary = $vmClient->getQuotaSummary($subscriptionId, 'eastus');
print_r($summary);
```

### 2. Check Before Creating VM
```php
$requiredCores = 16;
$check = $vmClient->checkAvailableQuota($subscriptionId, 'eastus', $requiredCores);

if (!$check['hasAvailableQuota']) {
    die("Insufficient quota!\n");
}
```

### 3. Get Specific Resource Quota
```php
$quota = $vmClient->getQuotaByResource(
    $subscriptionId, 
    'eastus', 
    'standardDSv3Family'
);
```

### 4. Monitor All Resources
```php
$usages = $vmClient->getComputeUsages($subscriptionId, 'eastus');
foreach ($usages as $usage) {
    $name = $usage['name']['localizedValue'];
    $current = $usage['currentValue'];
    $limit = $usage['limit'];
    echo "{$name}: {$current}/{$limit}\n";
}
```

## Common Quota Resource Names

### VM Families
- `cores` - Total regional vCPUs
- `standardDSv3Family` - DSv3-series vCPUs
- `standardDSv2Family` - DSv2-series vCPUs
- `standardESv3Family` - ESv3-series vCPUs
- `standardDDSv4Family` - DDSv4-series vCPUs
- `standardDCSv2Family` - DCSv2-series (confidential computing)
- `standardNDSFamily` - NDS-series (GPU)

### Other Resources
- `virtualMachines` - Total VM count
- `standardSSDDiskCount` - Standard SSD disks
- `premiumDiskCount` - Premium SSD disks
- `publicIPAddresses` - Public IPs
- `networkInterfaces` - NICs

## Response Structure

### checkAvailableQuota() Response
```php
[
    'hasAvailableQuota' => true,
    'quotas' => [
        'cores' => [
            'name' => 'cores',
            'localizedName' => 'Total Regional vCPUs',
            'currentValue' => 10,
            'limit' => 100,
            'available' => 90,
            'unit' => 'Count',
            'percentageUsed' => 10.0
        ],
        // ... more quotas
    ],
    'warnings' => [
        'Total Regional vCPUs usage is high: 85% (85/100)'
    ]
]
```

### getQuotaSummary() Response
```php
[
    'location' => 'eastus',
    'totalResources' => 25,
    'resources' => [
        [
            'name' => 'Total Regional vCPUs',
            'usage' => '10 / 100 Count',
            'available' => '90 Count',
            'percentageUsed' => 10.0
        ],
        // ... more resources
    ]
]
```

## Error Handling

```php
try {
    $check = $vmClient->checkAvailableQuota($subscriptionId, 'eastus', 16);
    
    if (!$check['hasAvailableQuota']) {
        // Handle insufficient quota
        foreach ($check['warnings'] as $warning) {
            error_log($warning);
        }
        throw new Exception("Insufficient quota");
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## Integration with VM Creation

```php
// 1. Get VM size details
$vmSizes = $vmClient->getAvailableVMSizes($subscriptionId, 'eastus');
$vmSize = array_filter($vmSizes, fn($s) => $s['name'] === 'Standard_D4s_v3')[0];
$requiredCores = $vmSize['numberOfCores'];

// 2. Check quota
$quotaCheck = $vmClient->checkAvailableQuota($subscriptionId, 'eastus', $requiredCores);

if (!$quotaCheck['hasAvailableQuota']) {
    die("Cannot create VM: insufficient quota\n");
}

// 3. Create VM
$result = $vmClient->createVM(/* ... */);
```

## Monitoring Script Example

```php
#!/usr/bin/env php
<?php
// monitor_quota.php - Run this daily via cron

require 'vendor/autoload.php';

$vmClient = new VmClient(new AzureClient($tenant, $clientId, $clientSecret));
$check = $vmClient->checkAvailableQuota($subscriptionId, 'eastus', 1);

foreach ($check['quotas'] as $quota) {
    if ($quota['percentageUsed'] > 80) {
        // Send alert (email, Slack, etc.)
        mail(
            'admin@example.com',
            'Azure Quota Alert',
            "{$quota['localizedName']} is at {$quota['percentageUsed']}%"
        );
    }
}
```

## Tips

1. **Cache quota data** - Quota limits rarely change, cache for 1 hour
2. **Check before batch operations** - Always validate before creating multiple VMs
3. **Monitor trends** - Track quota usage over time
4. **Regional differences** - Quotas are per-region, check each location
5. **Family-specific limits** - Some VM families have separate quota limits
