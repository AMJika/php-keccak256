# php-keccak

High-performance Keccak-256 (SHA-3) hashing extension for PHP.

![Performance](https://img.shields.io/badge/Performance-~15×_faster-brightgreen)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
![PHP Version](https://img.shields.io/badge/PHP-8.1-blue)

## Why This Exists

PHP lacks native support for Keccak-256 hashing, which is critical for Ethereum and blockchain applications. Pure PHP implementations are prohibitively slow for production use—taking **~0.3ms per hash** compared to this extension's **~0.02ms**.

This C extension provides production-grade performance for developers building blockchain integrations, dApp backends, or any application requiring high-throughput cryptographic hashing.

## Performance Benchmarks

Tested against `kornrunner/keccak` pure PHP implementation:

| Hardware | Native C | Pure PHP | Speedup |
|----------|----------|----------|---------|
| Intel Core i3-2130 (2011) | 0.032 ms/hash | 0.443 ms/hash | **14.0×** |
| AMD Ryzen 7 3700X (2019) | 0.018 ms/hash | 0.280 ms/hash | **16.0×** |

**Environment compatibility:**
- ✅ Bare metal Linux servers
- ✅ Docker containers
- ✅ Works across Intel and AMD architectures
- ✅ Consistent speedup regardless of CPU generation

**Real-world impact:**
- **1 million hashes:** 18-32 seconds (C) vs 4-7 minutes (PHP)
- **10 million hashes:** 3-5 minutes (C) vs 45-75 minutes (PHP)

## Installation

### Requirements

- PHP 8.0 or higher
- GCC or compatible C compiler
- `make` and `phpize` tools

### Build from Source

```bash
# Clone the repository
git clone https://github.com/BuildCoreWorks/php-keccak.git
cd php-keccak

# Build the extension
phpize
./configure
make

# Install (requires sudo)
sudo make install
```

### Enable the Extension

# Determine your PHP version
```bash
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")

# Create module configuration
echo -e "; configuration for php keccak module\n; priority=20\nextension=keccak.so" | sudo tee /etc/php/${PHP_VERSION}/mods-available/keccak.ini

# Enable the module
sudo phpenmod -v ${PHP_VERSION} keccak

# Restart your web server
sudo systemctl restart apache2   # For Apache
# OR
sudo systemctl restart php${PHP_VERSION}-fpm  # For PHP-FPM
```

## Manual Configuration (All Systems):
Alternatively, add directly to your php.ini:
```bash
# Find your php.ini location
php --ini

# Add the extension
echo "extension=keccak.so" | sudo tee -a /etc/php/8.1/cli/php.ini
echo "extension=keccak.so" | sudo tee -a /etc/php/8.1/apache2/php.ini  # If using Apache
echo "extension=keccak.so" | sudo tee -a /etc/php/8.1/fpm/php.ini      # If using PHP-FPM
```

## Usage

### Basic Usage

```php
<?php
$data = "Hello, Ethereum!";

// Hexadecimal output (default) - returns 64-character string
$hash = keccak_hash($data);
echo $hash; // e.g., "8c3064b5e3a1c5e86e1c9be8e8a9c3a4..."

// Raw binary output - returns 32 bytes
$raw = keccak_hash($data, true);
```

### Ethereum Address Derivation

```php
<?php
// Derive Ethereum address from public key
function deriveEthereumAddress($publicKey) {
    // Remove '04' prefix if present (uncompressed public key)
    if (substr($publicKey, 0, 2) === '04') {
        $publicKey = substr($publicKey, 2);
    }
    
    // Hash the public key
    $hash = keccak_hash(hex2bin($publicKey));
    
    // Take last 20 bytes (40 hex characters)
    return '0x' . substr($hash, -40);
}

$pubKey = '04a1b2c3d4...'; // Your public key
$address = deriveEthereumAddress($pubKey);
echo "Ethereum Address: {$address}\n";
```

### Transaction Hashing

```php
<?php
// Hash Ethereum transaction data
function hashTransaction($rlpEncodedTx) {
    return keccak_hash($rlpEncodedTx, false);
}

$txHash = hashTransaction($encodedTransaction);
```

### Smart Contract Event Signature

```php
<?php
// Generate event signature hash for Solidity events
function getEventSignature($signature) {
    // e.g., "Transfer(address,address,uint256)"
    return keccak_hash($signature);
}

$transferSig = getEventSignature("Transfer(address,address,uint256)");
echo "Event signature: 0x{$transferSig}\n";
```

## API Reference

### `keccak_hash(string $data, bool $raw_output = false): string`

Computes the Keccak-256 hash of the input data.

**Parameters:**
- `$data` (string, required): The data to hash
- `$raw_output` (bool, optional): 
  - `false` (default): Returns 64-character hexadecimal string
  - `true`: Returns 32 bytes of raw binary data

**Returns:**
- String containing the hash (format depends on `$raw_output`)

**Example:**
```php
$hex = keccak_hash("test");        // "9c22ff5f21f0b81b113e63f7db6da94fedef11b2119b4088b89664fb9a3cb658"
$raw = keccak_hash("test", true);  // 32 bytes of binary data
```

## Use Cases

- **Ethereum Development**: Address derivation, transaction signing, event logs
- **Smart Contract Verification**: Generate function selectors and event signatures
- **Blockchain APIs**: High-throughput transaction processing
- **Merkle Trees**: Fast leaf hashing for blockchain data structures
- **dApp Backends**: Efficient cryptographic operations in PHP services

## Benchmarking

Run the included benchmark script:

```bash
php benchmark.php
```

Sample output:
```
Benchmarking keccak_hash() over 1000 iterations...
Total time: 3.119 seconds
Average time per hash: 3.12 ms

Estimated time for 1,000,000 hashes: 52 minutes
```

## Technical Details

### Implementation

This extension implements the official Keccak-f[1600] permutation as specified in the Keccak reference. It uses:
- **Rate (r)**: 1088 bits (136 bytes)
- **Capacity (c)**: 512 bits (64 bytes)
- **Output**: 256 bits (32 bytes)
- **Suffix**: 0x01 (Keccak, not SHA-3)

**Note:** This implements Keccak-256 as used by Ethereum, which differs from the final SHA-3 standard (suffix 0x06).

### Why C Extension?

PHP is an interpreted language, which makes complex bitwise operations and state manipulation slow. By implementing the core algorithm in C:
- Direct memory manipulation eliminates PHP overhead
- Tight loops execute at CPU speed
- 64-bit operations are native instead of emulated
- No array allocations or garbage collection during hashing

### Memory Safety

The extension:
- Uses fixed-size buffers (no dynamic allocation)
- Validates input parameters via Zend API
- Properly handles binary data (null bytes, non-UTF8)
- Returns PHP strings managed by Zend memory manager

## Troubleshooting

### "PHP Fatal error: Call to undefined function keccak_hash()"

The extension isn't loaded. Verify:
```bash
php -m | grep keccak
php --ini  # Check which ini files are loaded
```

### "Cannot find autoconf" during build

Install build tools:
```bash
# Debian/Ubuntu
sudo apt-get install php-dev autoconf build-essential

# CentOS/RHEL
sudo yum install php-devel autoconf gcc
```

### Performance not as expected

- Ensure you're using `php -d opcache.enable_cli=1` for CLI scripts
- Compile PHP with optimization flags: `--enable-inline-optimization`
- Use PHP 7.4+ for best performance

## Contributing

Contributions are welcome! Areas for improvement:

- [ ] Add `keccak_hash_file()` function for file hashing
- [ ] Support for Keccak-384 and Keccak-512
- [ ] Windows build support (MSVC)
- [ ] Additional benchmark suites
- [ ] PHP 8.x attribute support

**To contribute:**
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/improvement`)
3. Commit your changes with clear messages
4. Add tests if applicable
5. Submit a pull request

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Author

Built by [BuildCoreWorks](https://buildcoreworks.com) - Performance-critical engineering for blockchain systems.

## Support

- 📧 Email: contact@buildcoreworks.com
- 🐛 Issues: [GitHub Issues](https://github.com/BuildCoreWorks/php-keccak/issues)
- 💬 Discussions: [GitHub Discussions](https://github.com/BuildCoreWorks/php-keccak/discussions)

## Related Projects

- [php-secp256k1](https://github.com/BuildCoreWorks/php-secp256k1) - Elliptic curve cryptography (3000× faster) **[COMING SOON]**
- [php-rlp](https://github.com/BuildCoreWorks/php-rlp) - RLP encoding/decoding (13-26× faster) **[COMING SOON]**

---
