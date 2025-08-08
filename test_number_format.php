<?php

$amount = '5000.00';
echo "Original amount: " . $amount . "\n";
echo "Formatted: " . '$' . number_format((float)$amount, 2, '.', ',') . "\n";

// Test different values
$amounts = ['5000', '5000.00', 5000, 5000.00];
foreach ($amounts as $test_amount) {
    echo "Testing {$test_amount}: " . '$' . number_format((float)$test_amount, 2, '.', ',') . "\n";
}
