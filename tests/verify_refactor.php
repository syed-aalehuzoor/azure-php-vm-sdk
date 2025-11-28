<?php

require_once __DIR__ . '/../vendor/autoload.php';

use AzureVmSdk\AzureClient;
use AzureVmSdk\VmClient;
use AzureVmSdk\NetworkInterfaceClient;

echo "Verifying Refactoring...\n";

// 1. Verify NetworkInterfaceClient class exists
if (class_exists(NetworkInterfaceClient::class)) {
    echo "[PASS] NetworkInterfaceClient class exists.\n";
} else {
    echo "[FAIL] NetworkInterfaceClient class does not exist.\n";
    exit(1);
}

// 2. Verify methods in NetworkInterfaceClient
$methods = get_class_methods(NetworkInterfaceClient::class);
if (in_array('createPublicIp', $methods) && in_array('createNetworkInterface', $methods)) {
    echo "[PASS] NetworkInterfaceClient has required methods.\n";
} else {
    echo "[FAIL] NetworkInterfaceClient is missing methods.\n";
    print_r($methods);
    exit(1);
}

// 3. Verify VmClient instantiation and dependency injection
try {
    // Mock AzureClient since we don't want to make real requests
    $azureClient = new class('tenant', 'client', 'secret') extends AzureClient {
        public function __construct($t, $c, $s) {}
        public function request(string $method, string $path, array $query = [], $body = null) {
            return [];
        }
    };

    $vmClient = new VmClient($azureClient);
    echo "[PASS] VmClient instantiated successfully.\n";

    // Use reflection to check if networkInterfaceClient property is initialized
    $reflection = new ReflectionClass($vmClient);
    $property = $reflection->getProperty('networkInterfaceClient');
    $property->setAccessible(true);
    $nicClient = $property->getValue($vmClient);

    if ($nicClient instanceof NetworkInterfaceClient) {
        echo "[PASS] VmClient has NetworkInterfaceClient initialized.\n";
    } else {
        echo "[FAIL] VmClient did not initialize NetworkInterfaceClient.\n";
        exit(1);
    }

} catch (Throwable $e) {
    echo "[FAIL] Error verifying VmClient: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Refactoring Verified Successfully!\n";
