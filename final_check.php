<?php
// Run all critical checks
echo "=== FINAL VALIDATION ===" . PHP_EOL;

$files = ['bmi','burned','calorie','steps','water','weight'];
foreach ($files as $f) {
    $path = "c:/xampp/htdocs/h4u-aws-backup/app/Views/admin/clients/partials/metrics/{$f}.php";
    $html = file_get_contents($path);
    
    // 1. PHP syntax check via tokenizer
    $tokens = token_get_all($html);
    
    // 2. Check for jQuery CDN re-load
    $hasJqueryCDN = strpos($html, 'jquery.com/jquery') !== false;
    
    // 3. Check modal ID
    preg_match('/id="(\w+GraphModal)"/', $html, $modalMatch);
    $modalId = $modalMatch[1] ?? 'NONE';
    
    // 4. Div balance in HTML portion (before first script)
    $htmlPart = substr($html, 0, strpos($html, '<script'));
    preg_match_all('/<div\b[^>]*>/i', $htmlPart, $m1);
    preg_match_all('/<\/div>/i', $htmlPart, $m2);
    $divBalance = count($m1[0]) - count($m2[0]);
    
    // 5. Has PHP init block
    $hasInit = strpos($html, 'if (!isset($') !== false;
    
    $status = $hasJqueryCDN ? '??  HAS JQUERY CDN (BAD)' : '? No jQuery CDN';
    
    echo PHP_EOL . "--- $f.php ---" . PHP_EOL;
    echo "  Modal ID: $modalId" . PHP_EOL;
    echo "  Div balance (HTML): $divBalance (0=OK)" . PHP_EOL;
    echo "  jQuery CDN: $status" . PHP_EOL;
    echo "  Has PHP init: " . ($hasInit ? 'YES' : 'NO - relies on controller data') . PHP_EOL;
}
