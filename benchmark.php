<?php
/**
 * Keccak Benchmark Script
 *
 * Compares PHP's native keccak_hash() with the kornrunner\Keccak implementation.
 *
 * Usage:
 *   - Ensure Composer autoload is included.
 *   - Install kornrunner/keccak: composer require kornrunner/keccak
 *   - Access in browser: benchmark.php?iterations=1000
 *
 * @author  https://buildcoreworks.com/
 * @license MIT
 */

require __DIR__ . '/vendor/autoload.php';
use kornrunner\Keccak;

// --- Dynamic iteration count, change value to test iterations ---
$iterations = 1234;

$data = "Hello, Ethereum!";

// --- Start output ---
echo "<pre>Keccak Benchmark ({$iterations} iterations)\n\n";

// --- Native keccak_hash() benchmark ---
$startNative = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $hashNative = keccak_hash($data);
}
$timeNative = microtime(true) - $startNative;

// --- Kornrunner Keccak benchmark ---
$startKorn = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $hashKorn = Keccak::hash($data, 256);
}
$timeKorn = microtime(true) - $startKorn;

// --- Calculate averages and speed difference ---
$avgNative = $timeNative / $iterations;
$avgKorn   = $timeKorn / $iterations;
$speedDiff = (($timeKorn - $timeNative) / $timeKorn) * 100;

// --- Display results ---
echo "Results:\n";
echo "  Native keccak_hash(): " . number_format($timeNative, 6) . "s total (" . number_format($avgNative * 1000, 6) . " ms/hash)\n";
echo "  Kornrunner Keccak:   " . number_format($timeKorn, 6) . "s total (" . number_format($avgKorn * 1000, 6) . " ms/hash)\n";
echo "  Speed difference:    " . number_format(abs($speedDiff), 2) . "% " . ($speedDiff > 0 ? "faster (native)" : "slower (native)") . "\n";

echo "\nExample Hash:\n";
echo "  keccak_hash(): $hashNative\n";
echo "  Keccak::hash(): $hashKorn\n";
echo "</pre>";
