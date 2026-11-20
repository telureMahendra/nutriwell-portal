<link href="<?= base_url(); ?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css" />


<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation = \Config\Services::validation(); ?>

<div class="content">
    <div class="page-bar">
        <?= $breadcrumb; ?>
    </div>

    <div class="page-container">
        <div class="tab-pane" id="tab_1">
            <div class="row">
                <div class="col-md-12">

                    <div class="portlet box grey-cascade">

                        <!-- 🔥 UPDATED HEADER WITH TITLE + ADD BUTTON IN 1 ROW -->
                        <div class="portlet-title">
                            <div class="caption" style="display:flex;justify-content:space-between;align-items:center;width:100%;">

                                <!-- Left: Page Title -->
                                <span><?= $pagetitle ?></span>

                                <!-- Right: Add Plan Button -->
                                <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'add')) { ?>
                                    <a href="<?= base_url('plan/add'); ?>" class="btn green">
                                        Add Plan <i class="fa fa-plus"></i>
                                    </a>
                                <?php } ?>

                            </div>
                        </div>

                        <div class="portlet-body">

                            <table class="table table-modern" id="sample_1">
    <thead>
        <tr>
            <th width="5%">Sr. No.</th>
            <th width="10%">Plan</th>
            <th width="13%">Caption</th>
            <th width="15%">Description</th>
            <th width="5%">Price</th>
            <th width="9%">Validity</th>
            <th width="9%">Category</th>
            <th width="12%">Background Image</th>
            <th width="12%">Header Image</th>
            <th width="7%">Action</th>
        </tr>
    </thead>

    <tbody>
        <?php if (count($planList) > 0) {
            $k = 1;
            foreach ($planList as $row) { ?>
                <tr>
                    <td><?= $k; ?></td>
                    <td><?= esc($row->name); ?></td>
                    <td><?= esc($row->caption); ?></td>
                    <td><?= esc($row->description); ?></td>
                    <td><?= esc($row->price); ?></td>
                    <td><?= esc($row->validaty); ?></td>
                    <td><?= esc($row->category); ?></td>

                    <td>
                        <img src="<?= base_url('public/uploads/plan/background/' . $row->background_img); ?>"
                             height="60" width="90"
                             style="border-radius:6px !important;cursor:pointer"
                             onclick="showLargeImage('<?= base_url('public/uploads/plan/background/' . $row->background_img); ?>')">
                    </td>

                    <td>
                        <img src="<?= base_url('public/uploads/plan/header/' .$row->header_img); ?>"
                             height="60" width="90"
                             style="border-radius:6px;cursor:pointer"
                             onclick="showLargeImage('<?= base_url('public/uploads/plan/header/' . $row->header_img); ?>')">
                    </td>

                    <td>
                        <a href="<?= base_url('plan/view/' . $row->plan_id); ?>" class="btn  btn-default">
                            <i class="fa fa-eye"></i>
                        </a>
                        <a href="<?= base_url('plan/edit/' . $row->plan_id); ?>" class="btn  btn-default">
                            <i class="fa fa-edit"></i>
                        </a>
                        <a href="javascript:void(0)" onclick="close_plan(<?= $row->plan_id; ?>)"
                           class="btn  btn-danger">
                            <i class="fa fa-trash"></i>
                        </a>
                    </td>

                </tr>
        <?php $k++; } } ?>
    </tbody>
</table>


                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


<!-- DELETE CONFIRM MODAL -->
<div class="modal fade" id="close_plan" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Close Plan</h4>
            </div>

            <div class="modal-body">
                Are you sure you want to close this plan?
            </div>

            <div class="modal-footer">
                <button type="button" class="btn default" id="yes">Yes</button>
                <button type="button" class="btn default" data-dismiss="modal">No</button>
            </div>

        </div>
    </div>
</div>


<!-- LARGE IMAGE PREVIEW MODAL -->
<div class="modal fade" id="largeImageModal" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog" style="max-width:60vw;">
        <div class="modal-content">
            <div class="modal-body">
                <img id="largeImage" src="" alt="Large Plan Image" style="width:100%;height:80vh;object-fit:contain;">
            </div>
        </div>
    </div>
</div>


<!-- JAVASCRIPT -->
<script type="text/javascript">

    // Confirm Delete Plan
    function close_plan(plan_id) {
        $('#close_plan').modal();

        $('#close_plan #yes').off().on('click', function () {

            $.post('<?= base_url("plan/close"); ?>', { plan_id: plan_id }, function (response) {

                if (response == 1) {
                    location.href = '<?= base_url("plan"); ?>';
                } else {
                    alert('Something went wrong with your request.');
                }

            });

        });
    }

    // Show Large Image
    function showLargeImage(imageUrl) {
        $('#largeImage').attr('src', imageUrl);
        $('#largeImageModal').modal('show');
    }

</script>

<?= $this->endSection(); ?>
