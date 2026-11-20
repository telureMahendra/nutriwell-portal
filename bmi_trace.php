<?php
// Deeper div trace for bmi.php HTML portion
$html = file_get_contents('c:/xampp/htdocs/h4u-aws-backup/app/Views/admin/clients/partials/metrics/bmi.php');
$htmlPart = substr($html, 0, strpos($html, '<script'));
$lines = explode("\n", $htmlPart);
$stack = [];
foreach ($lines as $i => $line) {
    // Count divs, but skip PHP blocks
    $clean = preg_replace('/<\?php.*?\?>/s', '', $line);
    $clean = preg_replace('/<\?.*/s', '', $clean); // opening PHP tag without close
    preg_match_all('/<div\b[^>]*>/i', $clean, $m1);
    preg_match_all('/<\/div>/i', $clean, $m2);
    for ($j=0; $j<count($m1[0]); $j++) array_push($stack, $i+1);
    for ($j=0; $j<count($m2[0]); $j++) {
        if(!empty($stack)) array_pop($stack);
    }
}
echo 'Unclosed divs in HTML part: '; print_r($stack);
echo 'Total HTML lines: ' . count($lines) . PHP_EOL;
