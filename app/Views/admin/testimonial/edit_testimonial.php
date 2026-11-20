<link href="<?= base_url();?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>
<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation =  \Config\Services::validation(); ?>

<?php
// Check if testimonial data exists
$testimonial = isset($testimonial) ? $testimonial : (isset($testimonialDetails) ? $testimonialDetails : null);

// Function to check if file exists and get proper URL
function getImageUrl($path) {
    if (empty($path)) {
        return false;
    }
    
    // Remove leading slash if present
    $path = ltrim($path, '/');
    
    // Check if file exists
    if (file_exists(FCPATH . $path)) {
        return base_url($path);
    }
    
    // Try alternative paths
    $alternativePaths = [
        $path,
        'uploads/' . $path,
        'uploads/testimonials/' . basename($path),
        'public/uploads/testimonials/' . basename($path)
    ];
    
    foreach ($alternativePaths as $altPath) {
        if (file_exists(FCPATH . $altPath)) {
            return base_url($altPath);
        }
    }
    
    return false;
}

// Get image URLs
$beforeImageUrl = getImageUrl($testimonial->before_image ?? '');
$afterImageUrl = getImageUrl($testimonial->after_image ?? '');

// Check if files exist
$beforeImageExists = $beforeImageUrl !== false;
$afterImageExists = $afterImageUrl !== false;
?>

<div class="content">
    <style type="text/css">
        span.help-block{
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
            position: relative;
        }
        
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .image-upload-label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .hidden {
            display: none;
        }
        
        .current-images {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }
        
        .current-image {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .current-image img {
            width: 150px;
            height: 150px;
            object-fit: contain;
            border: 2px solid #ddd;
            border-radius: 4px;
            background: #f5f5f5;
        }
        
        .image-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            display: block;
            font-weight: bold;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .error-message a {
            color: #721c24;
            font-weight: bold;
            text-decoration: underline;
        }
        
        .help-block-error {
            color: #f30;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        
        .image-placeholder {
            width: 150px;
            height: 150px;
            background: #f5f5f5;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 2px dashed #ccc;
            color: #999;
            font-size: 12px;
            border-radius: 4px;
        }
        
        .image-placeholder i {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .image-info {
            font-size: 10px;
            color: #888;
            margin-top: 5px;
            max-width: 150px;
            word-break: break-all;
        }
    </style>
    <div class="page-bar">
        <?= $breadcrumb; ?>
    </div>
    <div class="page-container">
        <div class="tab-pane" id="tab_1">
            <div class="portlet box grey-cascade">
                <div class="portlet-title">
                    <div class="caption"><?= $pagetitle?></div>
                </div>
                <div class="portlet-body form">
                    <?php if (!$testimonial): ?>
                        <div class="error-message">
                            <strong>Error:</strong> Testimonial data not found. 
                            <a href="<?= base_url('testimonial') ?>">Return to testimonials list</a>
                        </div>
                    <?php else: ?>
                    
                    <?php 
                    $attributes = array('class' => 'login-form', 'id' => 'testimonialForm', 'enctype' => 'multipart/form-data');
                    echo form_open_multipart('testimonial/edit/'.($testimonial ? $testimonial->testimonial_id : ''), $attributes);
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
                                        'value' => set_value('client_name', $testimonial ? $testimonial->client_name : ''),
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
                                        'value' => set_value('designation', $testimonial ? $testimonial->designation : ''),
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
                                    ], set_value('testimonial_type', $testimonial ? $testimonial->testimonial_type : 'text'), 'class="form-control" id="testimonial_type" onchange="toggleImageFields()"'); ?>
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
                                    ], set_value('rating', $testimonial ? $testimonial->rating : 5), 'class="form-control"'); ?>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Status<span class="required" aria-required="true"> *</span></label>
                                    <?= form_dropdown('status', [
                                        'Active' => 'Active',
                                        'Inactive' => 'Inactive'
                                    ], set_value('status', $testimonial ? $testimonial->status : 'Active'), 'class="form-control"'); ?>
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
                                    ], set_value('approval_status', $testimonial ? $testimonial->approval_status : 'Pending'), 'class="form-control"'); ?>
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
                                        'value' => set_value('date', $testimonial ? $testimonial->date : date('Y-m-d')),
                                        'class' => 'form-control',
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
                                        'value' => set_value('description', $testimonial ? $testimonial->description : ''),
                                        'class' => 'form-control',
                                        'required' => 'required',
                                        'rows' => 4,
                                        'placeholder' => 'Testimonial description'
                                    ]); ?>
                                    <span class="help-block help-block-error"><?= $validation->showError('description') ?></span>
                                </div>
                            </div>

                            <!-- Current Images Section -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">Current Images</label>
                                    <div class="current-images">
                                        <!-- Before Image -->
                                        <div class="current-image">
                                            <?php if ($beforeImageExists): ?>
                                                <img src="<?= $beforeImageUrl; ?>" 
                                                     alt="Before Image"
                                                     onload="console.log('Before image loaded:', this.src)"
                                                     onerror="console.error('Before image failed to load:', this.src); this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="image-placeholder" style="display: none;">
                                                    <i>📷</i>
                                                    Image failed to load
                                                </div>
                                            <?php else: ?>
                                                <div class="image-placeholder">
                                                    <i>📷</i>
                                                    No Before Image
                                                </div>
                                            <?php endif; ?>
                                            <span class="image-label">Before Image</span>
                                            <?php if (!empty($testimonial->before_image)): ?>
                                                <div class="image-info">
                                                    Path: <?= $testimonial->before_image ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- After Image -->
                                        <div class="current-image">
                                            <?php if ($afterImageExists): ?>
                                                <img src="<?= $afterImageUrl; ?>" 
                                                     alt="After Image"
                                                     onload="console.log('After image loaded:', this.src)"
                                                     onerror="console.error('After image failed to load:', this.src); this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="image-placeholder" style="display: none;">
                                                    <i>📷</i>
                                                    Image failed to load
                                                </div>
                                            <?php else: ?>
                                                <div class="image-placeholder">
                                                    <i>📷</i>
                                                    No After Image
                                                </div>
                                            <?php endif; ?>
                                            <span class="image-label">After Image</span>
                                            <?php if (!empty($testimonial->after_image)): ?>
                                                <div class="image-info">
                                                    Path: <?= $testimonial->after_image ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <p class="help-block">Leave blank to keep current images.</p>
                                </div>
                            </div>

                            <!-- Image Upload Fields -->
                            <div id="imageFields" class="col-md-12 <?= ($testimonial && $testimonial->testimonial_type == 'image') ? '' : 'hidden'; ?>">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="image-upload-label">Before Image</label>
                                            <?= form_upload([
                                                'name' => 'before_image',
                                                'id' => 'before_image',
                                                'class' => 'form-control',
                                                'accept' => 'image/*',
                                                'onchange' => 'previewImage(this, "beforePreview", "beforePlaceholder")'
                                            ]); ?>
                                            <span class="help-block help-block-error"><?= $validation->showError('before_image') ?></span>
                                            <div class="image-preview-container">
                                                <div class="image-preview">
                                                    <?php if ($beforeImageExists): ?>
                                                        <img id="beforePreview" src="<?= $beforeImageUrl; ?>" alt="Before Image Preview">
                                                        <span id="beforePlaceholder" style="display: none;">No image selected</span>
                                                    <?php else: ?>
                                                        <img id="beforePreview" src="" alt="Before Image Preview" style="display: none;">
                                                        <span id="beforePlaceholder">No image selected</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <small class="text-muted">Upload new image to replace current one</small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="image-upload-label">After Image</label>
                                            <?= form_upload([
                                                'name' => 'after_image',
                                                'id' => 'after_image',
                                                'class' => 'form-control',
                                                'accept' => 'image/*',
                                                'onchange' => 'previewImage(this, "afterPreview", "afterPlaceholder")'
                                            ]); ?>
                                            <span class="help-block help-block-error"><?= $validation->showError('after_image') ?></span>
                                            <div class="image-preview-container">
                                                <div class="image-preview">
                                                    <?php if ($afterImageExists): ?>
                                                        <img id="afterPreview" src="<?= $afterImageUrl; ?>" alt="After Image Preview">
                                                        <span id="afterPlaceholder" style="display: none;">No image selected</span>
                                                    <?php else: ?>
                                                        <img id="afterPreview" src="" alt="After Image Preview" style="display: none;">
                                                        <span id="afterPlaceholder">No image selected</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <small class="text-muted">Upload new image to replace current one</small>
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
                                    'value' => 'Edit Testimonial',
                                    'class' => 'btn green'
                                ]); ?>
                                <a href="<?= base_url(); ?>testimonial"><button type="button" class="btn default">Cancel</button></a>
                            </div>
                            <div class="col-md-6"> </div>
                        </div>
                    </div>
                    <?= form_close(); ?>
                    <?php endif; ?>
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
        } else {
            imageFields.classList.add('hidden');
            // Clear file inputs when hiding
            document.getElementById('before_image').value = '';
            document.getElementById('after_image').value = '';
        }
    }

    function previewImage(input, previewId, placeholderId) {
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById(placeholderId);
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                if (placeholder) placeholder.style.display = 'none';
            }
            
            reader.readAsDataURL(input.files[0]);
        } else {
            // If clearing the file input, check if we have existing database image
            const hasExistingImage = preview.src && preview.src.includes('<?= base_url() ?>');
            if (!hasExistingImage) {
                preview.style.display = 'none';
                if (placeholder) placeholder.style.display = 'inline';
            }
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleImageFields();
        
        // Debug: Log all image information
        console.log('=== IMAGE DEBUG INFORMATION ===');
        console.log('Before Image Database Path:', '<?= $testimonial->before_image ?? "null" ?>');
        console.log('After Image Database Path:', '<?= $testimonial->after_image ?? "null" ?>');
        console.log('Before Image Exists:', <?= $beforeImageExists ? 'true' : 'false' ?>);
        console.log('After Image Exists:', <?= $afterImageExists ? 'true' : 'false' ?>);
        console.log('Before Image URL:', '<?= $beforeImageUrl ?: "null" ?>');
        console.log('After Image URL:', '<?= $afterImageUrl ?: "null" ?>');
        console.log('Base URL:', '<?= base_url() ?>');
        
        // Test image loading
        if ('<?= $beforeImageUrl ?>') {
            console.log('Testing before image URL...');
            fetch('<?= $beforeImageUrl ?>')
                .then(response => {
                    console.log('Before image fetch status:', response.status, response.statusText);
                    return response.ok;
                })
                .catch(error => {
                    console.error('Before image fetch error:', error);
                });
        }
        
        if ('<?= $afterImageUrl ?>') {
            console.log('Testing after image URL...');
            fetch('<?= $afterImageUrl ?>')
                .then(response => {
                    console.log('After image fetch status:', response.status, response.statusText);
                    return response.ok;
                })
                .catch(error => {
                    console.error('After image fetch error:', error);
                });
        }
        
        // Set up event listeners for file inputs
        const beforeImageInput = document.getElementById('before_image');
        const afterImageInput = document.getElementById('after_image');
        
        if (beforeImageInput) {
            beforeImageInput.addEventListener('change', function() {
                previewImage(this, 'beforePreview', 'beforePlaceholder');
            });
        }
        
        if (afterImageInput) {
            afterImageInput.addEventListener('change', function() {
                previewImage(this, 'afterPreview', 'afterPlaceholder');
            });
        }
    });
</script>
<?= $this->endSection(); ?>