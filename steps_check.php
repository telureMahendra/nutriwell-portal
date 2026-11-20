<?php
$html = file_get_contents('c:/xampp/htdocs/h4u-aws-backup/app/Views/admin/clients/partials/metrics/steps.php');
echo 'First 200 chars: ' . substr($html, 0, 200) . PHP_EOL;
echo PHP_EOL . 'Has steps_tracker table ref: ' . (strpos($html, 'steps_tracker') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'Has php block before first div: ' . (strpos($html, '<?php') < strpos($html, '<div') ? 'YES' : 'NO') . PHP_EOL;
