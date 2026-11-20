<?php
echo "=== FINAL STATUS CHECK ===" . PHP_EOL . PHP_EOL;
$files = ['bmi','burned','calorie','steps','water','weight'];
foreach ($files as $f) {
    $path = "c:/xampp/htdocs/h4u-aws-backup/app/Views/admin/clients/partials/metrics/{$f}.php";
    $html = file_get_contents($path);
    
    // jQuery CDN check
    $hasJqueryCDN = strpos($html, 'jquery.com/jquery') !== false;
    
    // Modal ID
    preg_match('/id="(\w+GraphModal)"/', $html, $mm); $mid = $mm[1] ?? 'NONE';
    
    // PHP init block before first <div
    $firstPHP = strpos($html, '<?php');
    $firstDiv = strpos($html, '<div');
    $hasInit = $firstPHP !== false && $firstPHP < $firstDiv;
    
    // Correct table reference for steps
    $hasCorrectTable = ($f !== 'steps') || strpos($html, 'steps_tracker') !== false;
    
    $ok1 = !$hasJqueryCDN ? '[OK] No jQuery CDN' : '[!!] HAS JQUERY CDN - BREAKS BOOTSTRAP';
    $ok2 = $hasInit ? '[OK] PHP init block present' : '[--] No init (uses controller data)';
    $ok3 = $hasCorrectTable ? '[OK] Correct table name' : '[!!] Wrong table name';
    
    echo "{$f}.php (modal: #$mid)" . PHP_EOL;
    echo "     $ok1" . PHP_EOL;
    echo "     $ok2" . PHP_EOL;
    echo "     $ok3" . PHP_EOL . PHP_EOL;
}
echo "=== ALL CHECKS COMPLETE ===" . PHP_EOL;
