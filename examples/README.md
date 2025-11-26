# Azure VM SDK - Examples Guide

This directory contains comprehensive examples for using the Azure PHP VM SDK.

## Quick Reference

### Prerequisites
1. Copy `.env.example` to `.env` and fill in your Azure credentials
2. Ensure you have an existing VNet and subnet
3. Run `composer install` to install dependencies

## Available Examples

### 1. **list_vms.php** - List All VMs
Lists all virtual machines in your resource group.

```bash
php examples/list_vms.php
```

**What it does:**
- Connects to Azure
- Retrieves all VMs in the specified resource group
- Displays VM information in JSON format

---

### 2. **get_vm.php** - Get VM Details
Retrieves detailed information about a specific VM.

```bash
php examples/get_vm.php
```

**What it does:**
- Gets comprehensive VM details
- Shows hardware profile, OS profile, network configuration
- Displays provisioning state

---

### 3. **create_vm.php** - Create VM (Simple)
Creates a new VM with automatic configuration.

```bash
php examples/create_vm.php
```

**What it does:**
- Automatically selects appropriate VM size based on CPU/RAM requirements
- Creates network interface automatically
- Optionally creates public IP for RDP access
- Deploys Windows Server 2019 Datacenter
- Displays connection details

**Configuration:**
- Edit the file to set VM name, location, and specifications
- Update `$subnetId` with your actual subnet resource ID
- Set `$dedicatedAdminRdp = true` if you need RDP access

**Important:** Make sure to update the subnet ID before running!

---

### 4. **create_vm_advanced.php** - Create VM (Advanced)
Creates a VM with full control over configuration.

```bash
php examples/create_vm_advanced.php
```

**What it does:**
- Uses specific VM size (Standard_B2s)
- Deploys Windows Server 2022 Datacenter
- Attaches additional data disk (512GB)
- Enables boot diagnostics
- Adds resource tags
- Uses Premium SSD storage

**Use this when:**
- You need specific VM sizes
- You want to attach data disks
- You need custom OS configurations
- You want to add tags for organization

**Important:** Requires pre-created network interface!

---

### 5. **manage_vm.php** - VM Lifecycle Management
Demonstrates all VM power operations.

```bash
php examples/manage_vm.php
```

**What it does:**
- Start VM
- Restart VM
- Power off VM (still charges for compute)
- Deallocate VM (stops compute charges)
- Delete VM (commented out by default)

**Operations explained:**
- **Start**: Starts a stopped/deallocated VM
- **Restart**: Reboots a running VM
- **Power Off**: Stops the VM but keeps resources allocated (you still pay)
- **Deallocate**: Stops the VM and releases compute resources (no compute charges)
- **Delete**: Permanently removes the VM

---

### 6. **vm_lifecycle.php** - Complete Lifecycle Demo
Interactive demo showing the complete VM lifecycle.

```bash
php examples/vm_lifecycle.php
```

**What it does:**
1. Creates a new VM
2. Retrieves and displays VM details
3. Checks power state
4. Lists all VMs in resource group
5. Performs power operations (restart, stop, start, deallocate)
6. Optionally deletes the VM (with confirmation)

**Perfect for:**
- Learning the SDK
- Testing your Azure setup
- Understanding VM states and operations

---

## Common Configuration

All examples use environment variables from `.env`:

```env
AZURE_TENANT_ID=your-tenant-id
AZURE_CLIENT_ID=your-client-id
AZURE_CLIENT_SECRET=your-client-secret
AZURE_SUBSCRIPTION_ID=your-subscription-id
AZURE_RESOURCE_GROUP=your-resource-group
```

## Getting Subnet ID

To get your subnet ID, use Azure CLI:

```bash
az network vnet subnet show \
  --resource-group YOUR_RESOURCE_GROUP \
  --vnet-name YOUR_VNET_NAME \
  --name YOUR_SUBNET_NAME \
  --query id -o tsv
```

Or from Azure Portal:
1. Navigate to Virtual Networks
2. Select your VNet
3. Click on Subnets
4. Click on your subnet
5. Copy the "Resource ID"

## VM Size Selection

The SDK automatically selects VM sizes based on your requirements:

| Your Request | Possible VM Size | vCPUs | RAM |
|--------------|------------------|-------|-----|
| 1 core, 1GB  | Standard_B1s     | 1     | 1GB |
| 2 cores, 4GB | Standard_B2s     | 2     | 4GB |
| 2 cores, 8GB | Standard_D2s_v3  | 2     | 8GB |
| 4 cores, 16GB| Standard_D4s_v3  | 4     | 16GB|

To see all available sizes for a region:

```bash
az vm list-sizes --location eastus --output table
```

## Password Requirements

Azure requires strong passwords:
- **Length**: 12-123 characters
- **Complexity**: Must contain 3 of the following:
  - Lowercase letters
  - Uppercase letters
  - Numbers
  - Special characters

Examples of valid passwords:
- `P@ssw0rd123!`
- `SecureP@ss2024`
- `MyVm!Pass123`

## Cost Management Tips

1. **Use Deallocate, not Power Off**
   ```php
   $vmClient->deallocateVm($subscriptionId, $resourceGroup, $vmName);
   ```

2. **Choose appropriate VM sizes**
   - B-series: Burstable, cost-effective for dev/test
   - D-series: General purpose
   - E-series: Memory-optimized (more expensive)

3. **Delete unused resources**
   - VMs
   - Network interfaces
   - Public IPs
   - Disks

4. **Use tags to track resources**
   ```php
   'tags' => [
       'Environment' => 'Development',
       'CostCenter' => 'Engineering'
   ]
   ```

## Troubleshooting

### "No suitable VM size found"
- The requested CPU/RAM combination isn't available in the region
- Try a different region or adjust requirements

### "Subnet not found"
- Verify the subnet ID is correct
- Ensure the subnet exists in the specified resource group

### "Authentication failed"
- Check your service principal credentials
- Verify the service principal has Contributor role

### "Quota exceeded"
- You've hit your subscription limits
- Request a quota increase in Azure Portal

## Next Steps

1. Start with `list_vms.php` to verify your setup
2. Try `create_vm.php` to create your first VM
3. Use `vm_lifecycle.php` for a complete walkthrough
4. Explore `create_vm_advanced.php` for custom configurations

## Support

For issues or questions:
- Check the main [README.md](../README.md)
- Review Azure VM documentation
- Open an issue on GitHub
