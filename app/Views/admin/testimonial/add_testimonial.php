<link href="<?= base_url(); ?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css" />
<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation = \Config\Services::validation(); ?>
<div class="content">
    <style type="text/css">
        span.help-block {
            color: #f30;
        }
        
        .image-preview-container {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        
        .image-preview {
            width: 150px;
            height: 150px;
            border: 2px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
        }
        
        .image-upload-label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .hidden {
            display: none;
        }
    </style>
    <div class="page-bar">
        <?= $breadcrumb; ?>
    </div>
    <div class="page-container">
        <div class="tab-pane" id="tab_1">
            <div class="portlet box grey-cascade">
                <div class="portlet-title">
                    <div class="caption">
                        <?= $pagetitle ?>
                    </div>
                </div>
                <div class="portlet-body form">
                    <?php
                    $attributes = ['class' => 'login-form', 'id' => 'testimonialForm', 'enctype' => 'multipart/form-data'];
                    echo form_open_multipart('testimonial/add', $attributes);

                    $client_name = set_value('client_name');
                    $designation = set_value('designation');
                    $description = set_value('description');
                    $rating = set_value('rating', 5);
                    $testimonial_type = set_value('testimonial_type', 'text');
                    $status = set_value('status', 'Active');
                    $approval_status = set_value('approval_status', 'Pending');
                    ?>

                    <div class="form-body">
                        <div class="row">
                            <!-- Client Name -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Client Name<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input([
                                        'name' => 'client_name',
                                        'id' => 'client_name',
                                        'value' => $client_name,
                                        'class' => 'form-control',
                                        'required' => 'required',
                                        'placeholder' => 'Client Name'
                                    ]); ?>
                                    <span class="help-block help-block-error"><?= $validation->showError('client_name') ?></span>
                                </div>
                            </div>

                            <!-- Designation -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Designation</label>
                                    <?= form_input([
                                        'name' => 'designation',
                                        'id' => 'designation',
                                        'value' => $designation,
                                        'class' => 'form-control',
                                        'placeholder' => 'Designation/Company'
                                    ]); ?>
                                </div>
                            </div>

                            <!-- Testimonial Type -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Testimonial Type<span class="required" aria-required="true"> *</span></label>
                                    <?= form_dropdown('testimonial_type', [
                                        'text' => 'Text Testimonial',
                                        'image' => 'Image Testimonial (Before & After)'
                                    ], $testimonial_type, 'class="form-control" id="testimonial_type" onchange="toggleImageFields()"'); ?>
                                    <span class="help-block help-block-error"><?= $validation->showError('testimonial_type') ?></span>
                                </div>
                            </div>

                            <!-- Rating -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Rating (1-5)</label>
                                    <?= form_dropdown('rating', [
                                        1 => '1 Star',
                                        2 => '2 Stars',
                                        3 => '3 Stars',
                                        4 => '4 Stars',
                                        5 => '5 Stars'
                                    ], $rating, 'class="form-control"'); ?>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Status<span class="required" aria-required="true"> *</span></label>
                                    <?= form_dropdown('status', [
                                        'Active' => 'Active',
                                        'Inactive' => 'Inactive'
                                    ], $status, 'class="form-control"'); ?>
                                    <span class="help-block help-block-error"><?= $validation->showError('status') ?></span>
                                </div>
                            </div>

                            <!-- Approval Status -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Approval Status<span class="required" aria-required="true"> *</span></label>
                                    <?= form_dropdown('approval_status', [
                                        'Pending' => 'Pending',
                                        'Approved' => 'Approved',
                                        'Rejected' => 'Rejected'
                                    ], $approval_status, 'class="form-control"'); ?>
                                    <span class="help-block help-block-error"><?= $validation->showError('approval_status') ?></span>
                                </div>
                            </div>

                            <!-- Date -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Date</label>
                                    <?= form_input([
                                        'name' => 'date',
                                        'id' => 'date',
                                        'value' => set_value('date', date('Y-m-d')),
                                        'class' => 'form-control datepicker',
                                        'type' => 'date'
                                    ]); ?>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">Description<span class="required" aria-required="true"> *</span></label>
                                    <?= form_textarea([
                                        'name' => 'description',
                                        'id' => 'description',
                                        'value' => $description,
                                        'class' => 'form-control',
                                        'required' => 'required',
                                        'rows' => 4,
                                        'placeholder' => 'Testimonial description'
                                    ]); ?>
                                    <span class="help-block help-block-error"><?= $validation->showError('description') ?></span>
                                </div>
                            </div>

                            <!-- Image Upload Fields (Hidden by default) -->
                            <div id="imageFields" class="col-md-12 hidden">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="image-upload-label">Before Image<span class="required"> *</span></label>
                                            <?= form_upload([
                                                'name' => 'before_image',
                                                'id' => 'before_image',
                                                'class' => 'form-control',
                                                'accept' => 'image/*',
                                                'onchange' => 'previewImage(this, "beforePreview")'
                                            ]); ?>
                                            <span class="help-block help-block-error"><?= $validation->showError('before_image') ?></span>
                                            <div class="image-preview-container">
                                                <div class="image-preview">
                                                    <img id="beforePreview" src="" alt="Before Image Preview" style="display: none;">
                                                    <span id="beforePlaceholder">No image selected</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="image-upload-label">After Image<span class="required"> *</span></label>
                                            <?= form_upload([
                                                'name' => 'after_image',
                                                'id' => 'after_image',
                                                'class' => 'form-control',
                                                'accept' => 'image/*',
                                                'onchange' => 'previewImage(this, "afterPreview")'
                                            ]); ?>
                                            <span class="help-block help-block-error"><?= $validation->showError('after_image') ?></span>
                                            <div class="image-preview-container">
                                                <div class="image-preview">
                                                    <img id="afterPreview" src="" alt="After Image Preview" style="display: none;">
                                                    <span id="afterPlaceholder">No image selected</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions right">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <?= form_submit([
                                    'name' => 'submit',
                                    'value' => 'Add Testimonial',
                                    'class' => 'btn green'
                                ]) ?>
                                <a href="<?= base_url('testimonial') ?>"><button type="button" class="btn default">Cancel</button></a>
                            </div>
                        </div>
                    </div>
                    <?= form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleImageFields() {
        const type = document.getElementById('testimonial_type').value;
        const imageFields = document.getElementById('imageFields');
        
        if (type === 'image') {
            imageFields.classList.remove('hidden');
            // Make image fields required
            document.getElementById('before_image').required = true;
            document.getElementById('after_image').required = true;
        } else {
            imageFields.classList.add('hidden');
            // Remove required from image fields
            document.getElementById('before_image').required = false;
            document.getElementById('after_image').required = false;
            // Clear file inputs
            document.getElementById('before_image').value = '';
            document.getElementById('after_image').value = '';
            // Reset previews
            resetImagePreviews();
        }
    }

    function resetImagePreviews() {
        const beforePreview = document.getElementById('beforePreview');
        const afterPreview = document.getElementById('afterPreview');
        const beforePlaceholder = document.getElementById('beforePlaceholder');
        const afterPlaceholder = document.getElementById('afterPlaceholder');
        
        if (beforePreview) {
            beforePreview.src = '';
            beforePreview.style.display = 'none';
        }
        if (afterPreview) {
            afterPreview.src = '';
            afterPreview.style.display = 'none';
        }
        if (beforePlaceholder) beforePlaceholder.style.display = 'block';
        if (afterPlaceholder) afterPlaceholder.style.display = 'block';
    }

    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        let placeholder;
        
        if (previewId === 'beforePreview') {
            placeholder = document.getElementById('beforePlaceholder');
        } else if (previewId === 'afterPreview') {
            placeholder = document.getElementById('afterPlaceholder');
        }
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                if (placeholder) placeholder.style.display = 'none';
            }
            
            reader.readAsDataURL(input.files[0]);
        } else {
            // If user clears the file input
            preview.src = '';
            preview.style.display = 'none';
            if (placeholder) placeholder.style.display = 'block';
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleImageFields();
        
        // Initialize datepicker
        if ($('.datepicker').length) {
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true
            });
        }
        
        // Set up file input change listeners
        const beforeImageInput = document.getElementById('before_image');
        const afterImageInput = document.getElementById('after_image');
        
        if (beforeImageInput) {
            beforeImageInput.addEventListener('change', function() {
                previewImage(this, 'beforePreview');
            });
        }
        
        if (afterImageInput) {
            afterImageInput.addEventListener('change', function() {
                previewImage(this, 'afterPreview');
            });
        }
    });
</script>
<?= $this->endSection(); ?>