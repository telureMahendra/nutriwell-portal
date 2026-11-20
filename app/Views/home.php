<link href="<?= base_url(); ?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css" />

<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<div class="content">
    <div class="page-bar">
        <?= $breadcrumb; ?>
    </div>
    <div class="page-container">
        <div class="row" style="text-align: center;">
            <div class="col-md-12">
                <h1 style="color: red;"><i class="fa fa-ban" style="font-size: 35px;"></i> Access Denied</h1>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>