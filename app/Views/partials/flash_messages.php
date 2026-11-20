<?php
$flashTypes = [
    'success' => [
        'class' => 'alert-success',
        'icon' => 'fa-check-circle',
        'title' => 'Success!'
    ],
    'error' => [
        'class' => 'alert-danger',
        'icon' => 'fa-exclamation-circle',
        'title' => 'Error!'
    ],
    'warning' => [
        'class' => 'alert-warning',
        'icon' => 'fa-exclamation-triangle',
        'title' => 'Warning!'
    ],
    'info' => [
        'class' => 'alert-info',
        'icon' => 'fa-info-circle',
        'title' => 'Info!'
    ]
];
?>

<?php foreach ($flashTypes as $type => $config): ?>
    <?php if ($message = session()->getFlashdata($type)): ?>
        <div class="alert <?= $config['class'] ?> alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa <?= $config['icon'] ?> me-2"></i>
                <div>
                    <strong><?= $config['title'] ?></strong>
                    <div class="mt-1"><?= $message ?></div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
<?php endforeach; ?>