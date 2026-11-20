<link href="<?= base_url(); ?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css" />

<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation =  \Config\Services::validation(); ?>

<style>
    .pagination {
        margin: 20px 0;
        float: left;
    }
    .pager {
        float: right;
    }

    /* Modern Toolbar Styling */
    .toolbar-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #eef1f5;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .toolbar-left {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .toolbar-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-title {
        margin: 0;
        font-size: 22px;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .page-title i {
        color: #F57F27;
        font-size: 24px;
    }

    .btn-green {
        background: #F57F27;
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-green:hover {
        background: #F57F27;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(26, 188, 156, 0.3);
        color: white;
    }

    .import-export-buttons {
        display: flex;
        gap: 10px;
        background: white;
        padding: 8px;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
    }

    .btn-upload, .btn-download {
        min-width: 45px;
        padding: 9px 15px;
        border-radius: 5px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .btn-upload {
        background: linear-gradient(135deg, #F57F27 0%, #FF9F43 100%);
        color: white;
    }

    .btn-upload:hover {
        background: linear-gradient(135deg, #F57F27 0%, #F57F27 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 127, 39, 0.3);
        color: white;
    }

    .btn-download {
        background: linear-gradient(135deg, #F57F27 0%, #F57F27 100%);
        color: white;
    }

    .btn-download:hover {
        background: linear-gradient(135deg, #2980b9 0%, #1c6ea4 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        color: white;
    }

    /* Pagination Controls Styling */
    .pagination-controls {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-left: 10px;
    }

    .pagination-select {
        width: 80px;
        padding: 6px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: white;
        font-size: 14px;
        color: #333;
    }

    .pagination-label {
        font-size: 14px;
        color: #666;
        white-space: nowrap;
    }

    /* Table Styling */
    .table-modern {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border: none;
    }

    .table-modern thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .table-modern thead th {
        border: none;
        padding: 15px 12px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 0.5px;
    }

    .table-modern tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }

    .table-modern tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    }

    .table-modern tbody td {
        padding: 15px 12px;
        vertical-align: middle;
        border: none;
        color: #2c3e50;
    }

    /* Action Buttons */
    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .btn-edit {
        background: #e8f6f3;
        color: #1abc9c;
        border: 1px solid #d0ece7;
    }

    .btn-edit:hover {
        background: #1abc9c;
        color: white;
        transform: scale(1.1);
    }

    .btn-delete {
        background: #fdedec;
        color: #e74c3c;
        border: 1px solid #fadbd8;
    }

    .btn-delete:hover {
        background: #e74c3c;
        color: white;
        transform: scale(1.1);
    }

    /* Pagination Styling */
    .pagination-wrapper {
        margin-top: 25px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .pagination > li > a {
        border: 1px solid #e0e0e0;
        margin: 0 3px;
        border-radius: 4px;
        color: #3498db;
        transition: all 0.3s ease;
    }

    .pagination > li > a:hover {
        background: #3498db;
        color: white;
        border-color: #3498db;
    }

    .pagination > .active > a {
        background: #3498db;
        border-color: #3498db;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #7f8c8d;
    }

    .empty-state i {
        font-size: 60px;
        color: #bdc3c7;
        margin-bottom: 20px;
    }

    .empty-state h4 {
        color: #7f8c8d;
        margin-bottom: 15px;
    }

    /* Modal Styling */
    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px 8px 0 0;
    }

    .modal-title {
        font-weight: 600;
    }

    .btn-default {
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        transition: all 0.3s ease;
    }

    .btn-default:hover {
        background: #e9ecef;
    }

    @media (max-width: 768px) {
        .toolbar-container {
            flex-direction: column;
            align-items: stretch;
            gap: 15px;
            padding: 15px;
        }
        
        .toolbar-right {
            flex-direction: column;
            width: 100%;
        }
        
        .import-export-buttons {
            width: 100%;
            justify-content: center;
        }
        
        .table-responsive {
            border: 1px solid #eef1f5;
            border-radius: 8px;
        }
        
        .pagination-wrapper {
            flex-direction: column;
            gap: 15px;
        }
        
        .pagination-controls {
            margin-left: 0;
            margin-top: 10px;
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="content">
    <div class="page-bar">
        <?= $breadcrumb; ?>
    </div>

    <div class="page-container">
        <div class="tab-pane" id="tab_1">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box grey-cascade">
                        <div class="portlet-body">
                            <!-- Modern Toolbar -->
                            <div class="toolbar-container">
                                <div class="toolbar-left">
                                    <h3 class="page-title">
                                        <i class="fa fa-utensils"></i>
                                        Food Management
                                    </h3>
                                    
                                    <!-- Pagination Controls -->
                                    <div class="pagination-controls">
                                        <span class="pagination-label">Show:</span>
                                        <select class="pagination-select" id="perPageSelect" onchange="changePerPage(this.value)">
                                            <option value="10" <?= (isset($perPage) && $perPage == 10) ? 'selected' : ''; ?>>10</option>
                                            <option value="25" <?= (isset($perPage) && $perPage == 25) ? 'selected' : ''; ?>>25</option>
                                            <option value="50" <?= (isset($perPage) && $perPage == 50) ? 'selected' : ''; ?>>50</option>
                                            <option value="100" <?= (isset($perPage) && $perPage == 100) ? 'selected' : ''; ?>>100</option>
                                            <option value="200" <?= (isset($perPage) && $perPage == 200) ? 'selected' : ''; ?>>200</option>
                                            <option value="500" <?= (isset($perPage) && $perPage == 500) ? 'selected' : ''; ?>>500</option>
                                        </select>
                                        <span class="pagination-label">entries</span>
                                    </div>
                                </div>
                                
                                <div class="toolbar-right">
                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'add')) { ?>
                                        <a href="<?= base_url(); ?>food/add" class="btn-green">
                                            <i class="fa fa-plus"></i>
                                            Add New Food
                                        </a>
                                    <?php } ?>
                                    
                                    <div class="import-export-buttons">
                                        <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'add')) { ?>
                                            <button class="btn-upload" onclick="addfile();" title="Upload Excel File">
                                                <i class="fa fa-upload"></i>
                                                <span class="btn-text">Upload</span>
                                            </button>
                                            
                                            <a href="<?= base_url('public/FoodData.xlsx'); ?>" 
                                               class="btn-download" 
                                               title="Download the full food sheet">
                                                <i class="fa fa-download"></i>
                                                <span class="btn-text">Download</span>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <!-- End Modern Toolbar -->

                            <?php if (count($food) > 0) { ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-modern" id="sample_1">
                                    <thead>
                                        <tr>
                                            <th width="8%">
                                                <a href="javascript:void(0)" onclick="sortTable('serial')">
                                                    Sr. No. 
                                                    <?php if (isset($sort) && $sort == 'serial'): ?>
                                                        <i class="fa fa-sort-<?= $order == 'asc' ? 'up' : 'down'; ?>"></i>
                                                    <?php else: ?>
                                                        <i class="fa fa-sort"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th width="30%">
                                                <a href="javascript:void(0)" onclick="sortTable('food_name')">
                                                    Food Name
                                                    <?php if (isset($sort) && $sort == 'food_name'): ?>
                                                        <i class="fa fa-sort-<?= $order == 'asc' ? 'up' : 'down'; ?>"></i>
                                                    <?php else: ?>
                                                        <i class="fa fa-sort"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th width="18%">
                                                <a href="javascript:void(0)" onclick="sortTable('calories')">
                                                    Calories
                                                    <?php if (isset($sort) && $sort == 'calories'): ?>
                                                        <i class="fa fa-sort-<?= $order == 'asc' ? 'up' : 'down'; ?>"></i>
                                                    <?php else: ?>
                                                        <i class="fa fa-sort"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th width="14%">Unit</th>
                                            <th width="14%">Food Type</th>
                                            <th width="12%" class="text-center">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php 
                                        // Calculate starting serial number safely
                                        $k = 1;
                                        if (isset($pager) && method_exists($pager, 'getCurrentPage')) {
                                            $currentPage = $pager->getCurrentPage();
                                            $perPage = isset($perPage) ? $perPage : 10;
                                            $k = ($currentPage - 1) * $perPage + 1;
                                        }
                                        
                                        foreach ($food as $row) {
                                            // Get food type names
                                            $food_type_names = '';
                                            if (!empty($row->food_type_id)) {
                                                $food_type_names = $row->food_type_id; // Temporary placeholder
                                            }

                                            // Same resolution the API uses, so this column shows
                                            // exactly what the app will show.
                                            $unit_code   = resolveFoodUnit($row->unit ?? null, $row->food_name);
                                            $unit_stored = foodUnitIsStored($row->unit ?? null);
                                        ?>
                                            <tr>
                                                <td><span class="badge badge-secondary"><?= $k; ?></span></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($row->food_name); ?></strong>
                                                    <div class="small text-muted mt-1">
                                                        ID: <?= $row->food_id; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-success" style="font-size: 14px; padding: 6px 12px;">
                                                        <?= number_format($row->calories, 0); ?> cal
                                                    </span>
                                                </td>
                                                <td>
                                                    <span title="<?= $unit_stored ? 'Set on the food item' : 'Worked out from the food name - not set on the item'; ?>">
                                                        <?= foodUnitEmoji($unit_code); ?>
                                                        <?= htmlspecialchars(foodUnitLabel($unit_code)); ?>
                                                    </span>
                                                    <?php if (!$unit_stored) { ?>
                                                        <div class="small text-muted mt-1">auto</div>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <?php if ($food_type_names) { ?>
                                                        <span class="badge badge-info" style="background: #e8f4fc; color: #3498db; border: 1px solid #d6eaf8;">
                                                            <?= htmlspecialchars($food_type_names); ?>
                                                        </span>
                                                    <?php } else { ?>
                                                        <span class="text-muted">Not specified</span>
                                                    <?php } ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                        <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'edit')) { ?>
                                                            <a href="<?= base_url(); ?>food/edit/<?= $row->food_id; ?>" 
                                                               class="btn-action btn-edit" 
                                                               title="Edit Food">
                                                                <i class="fa fa-edit"></i>
                                                            </a>
                                                        <?php } ?>

                                                        <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'close')) { ?>
                                                            <a href="javascript:void(0)" 
                                                               onclick="close_food(<?= $row->food_id; ?>)" 
                                                               class="btn-action btn-delete" 
                                                               title="Delete Food">
                                                                <i class="fa fa-trash"></i>
                                                            </a>
                                                        <?php } ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php 
                                            $k++;
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination Section -->
                            <?php if (isset($pager)) { ?>
                            <div class="pagination-wrapper">
                                <div>
                                    <span class="text-muted">
                                        <?php 
                                        // Show pagination info safely
                                        if (isset($total) && isset($perPage) && isset($currentPage)) {
                                            $start = (($currentPage - 1) * $perPage) + 1;
                                            $end = min($currentPage * $perPage, $total);
                                            echo "Showing $start to $end of $total entries";
                                        } elseif (isset($food) && is_array($food)) {
                                            echo "Showing " . count($food) . " entries";
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div>
                                    <?php if (isset($pager)): ?>
                                        <?= $pager->links(); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php } ?>
                            
                            <?php } else { ?>
                                <div class="empty-state">
                                    <i class="fa fa-utensils fa-3x"></i>
                                    <h4>No Food Items Found</h4>
                                    <p>Get started by adding your first food item or importing from Excel.</p>
                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'add')) { ?>
                                        <div class="mt-3">
                                            <a href="<?= base_url(); ?>food/add" class="btn-green">
                                                <i class="fa fa-plus"></i>
                                                Add First Food
                                            </a>
                                            <button class="btn-upload" onclick="addfile();" style="margin-left: 10px;">
                                                <i class="fa fa-upload"></i>
                                                Import from Excel
                                            </button>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="close_food" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fa fa-exclamation-triangle text-warning"></i>
                    Confirm Deletion
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <i class="fa fa-trash fa-3x text-danger mb-3"></i>
                <h5>Are you sure you want to delete this food item?</h5>
                <p class="text-muted">This action cannot be undone. The food item will be marked as inactive.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="yes">
                    <i class="fa fa-trash"></i> Yes, Delete It
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Excel Upload Modal -->
<div class="modal fade" id="postcomment" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">
                    <i class="fa fa-file-excel text-success"></i>
                    Upload Excel File
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    <strong>Instructions:</strong> Upload an Excel file (.xlsx) with food data. New items are added;
                    existing items are updated with the calories and unit from the sheet.
                    The download below is the full recipe list with its Unit column already filled in and
                    locked to a dropdown &ndash; review it, adjust any units, then upload it back.
                    A large sheet can take up to a minute to process.
                </div>
                
                <div class="form-group">
                    <label class="font-weight-bold">
                        Select Excel File 
                        <span class="text-danger">*</span>
                    </label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="excel_file" accept=".xlsx">
                        <label class="custom-file-label" for="excel_file">Choose file</label>
                    </div>
                    <small class="form-text text-muted">
                        Only .xlsx files are allowed.
                        <a href="<?= base_url('public/FoodData.xlsx'); ?>" class="text-primary">
                            <i class="fa fa-download"></i> Download food sheet
                        </a>
                    </small>
                </div>
                
                <div id="fileInfo" class="mt-3" style="display: none;">
                    <div class="alert alert-light border">
                        <i class="fa fa-file text-primary"></i>
                        <span id="fileName"></span>
                        <span id="fileSize" class="text-muted float-right"></span>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h6>Expected Format:</h6>
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Food Name</th>
                                <th>Calories</th>
                                <th>Unit (Optional)</th>
                                <th>Food Type (Optional)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Plain poha, 1 katori</td>
                                <td>137</td>
                                <td>Katori</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Chicken Breast</td>
                                <td>165</td>
                                <td>Serving</td>
                                <td>Meat</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="alert alert-light border small mb-0">
                        <strong>Unit</strong> is a dropdown in the sheet &ndash; pick one of:
                        <?= htmlspecialchars(implode(', ', foodUnitLabelList())); ?>.
                        Columns are matched by their header name, so the order does not matter.
                        Leave Unit blank and it is worked out from the food name
                        (&ldquo;Plain poha, 1 katori&rdquo; &rarr; Katori). Anything outside the list is
                        ignored and reported back to you.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-success" onclick="uploadExcelFile()" id="uploadBtn">
                    <i class="fa fa-upload"></i> Upload File
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Loading...</span>
                </div>
                <h5 class="mt-3">Processing...</h5>
                                <p class="text-muted">Please wait while we import your data.</p>
            </div>
        </div>
    </div>
</div>

<script>
// File input display
document.getElementById('excel_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const label = document.querySelector('.custom-file-label');
    
    if (file) {
        // Update label
        label.textContent = file.name;
        
        // Show file info
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        fileInfo.style.display = 'block';
    } else {
        label.textContent = 'Choose file';
        fileInfo.style.display = 'none';
    }
});

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Show upload modal
function addfile() {
    $('#postcomment').modal('show');
}

// Upload Excel File
function uploadExcelFile() {
    const excel_file = document.getElementById('excel_file');
    const uploadBtn = document.getElementById('uploadBtn');
    
    if (!excel_file.files.length) {
        showAlert('Please select an Excel file first.', 'warning');
        return;
    }

    var fileName = excel_file.files[0].name;
    var fileExtension = fileName.split('.').pop().toLowerCase();

    if (fileExtension !== 'xlsx') {
        showAlert('Invalid file type. Only .xlsx files are allowed.', 'error');
        return;
    }

    // Show loading modal
    $('#loadingModal').modal('show');
    $('#postcomment').modal('hide');
    
    // Disable upload button
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';

    const formData = new FormData();
    formData.append('excelFile', excel_file.files[0]);

    $.ajax({
        url: '<?= base_url("food/importfood"); ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $('#loadingModal').modal('hide');
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fa fa-upload"></i> Upload File';
            
            if (response.success) {
                showAlert(response.msg, 'success');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                showAlert(response.msg, 'error');
            }
        },
        error: function(xhr, status, error) {
            $('#loadingModal').modal('hide');
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fa fa-upload"></i> Upload File';
            
            showAlert('Error uploading file. Please try again.', 'error');
            console.error('Upload error:', error);
        }
    });
}

// Delete food function
function close_food(food_id) {
    $('#close_food').modal('show');
    
    $('#close_food #yes').off().click(function() {
        $('#close_food').modal('hide');
        $('#loadingModal').modal('show');
        
        $.post('<?= base_url("food/close"); ?>', {
            food_id: food_id,
        }, function(responseText) {
            $('#loadingModal').modal('hide');
            if (responseText == 1) {
                showAlert('Food item deleted successfully!', 'success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showAlert('Failed to delete food item. Please try again.', 'error');
            }
        });
    });
}

// Change items per page
function changePerPage(value) {
    // Get current URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    
    // Update or add perPage parameter
    urlParams.set('perPage', value);
    
    // Reset to page 1 when changing items per page
    urlParams.set('page', 1);
    
    // Redirect to new URL
    window.location.href = '<?= base_url("food"); ?>?' + urlParams.toString();
}

// Sort table function (optional enhancement)
function sortTable(column) {
    const urlParams = new URLSearchParams(window.location.search);
    let order = 'asc';
    
    // If already sorting by this column, toggle order
    if (urlParams.get('sort') === column) {
        order = urlParams.get('order') === 'asc' ? 'desc' : 'asc';
    }
    
    urlParams.set('sort', column);
    urlParams.set('order', order);
    
    // Redirect to new URL
    window.location.href = '<?= base_url("food"); ?>?' + urlParams.toString();
}

// Custom alert function
function showAlert(message, type = 'info') {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    };
    
    const icon = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };
    
    const alertHtml = `
        <div class="alert ${alertClass[type]} alert-dismissible fade show" role="alert" 
             style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="fa ${icon[type]}"></i>
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert-dismissible').remove();
    
    // Add new alert
    $('body').append(alertHtml);
    
    // Auto remove after 5 seconds
    setTimeout(function() {
        $('.alert-dismissible').alert('close');
    }, 5000);
}

// Initialize tooltips
$(document).ready(function() {
    $('[title]').tooltip({
        placement: 'top',
        trigger: 'hover'
    });
    
    // Clear file input when modal is closed
    $('#postcomment').on('hidden.bs.modal', function () {
        document.getElementById('excel_file').value = '';
        document.querySelector('.custom-file-label').textContent = 'Choose file';
        document.getElementById('fileInfo').style.display = 'none';
    });
    
    // Initialize per page selector from URL
    const urlParams = new URLSearchParams(window.location.search);
    const perPageFromUrl = urlParams.get('perPage');
    if (perPageFromUrl) {
        document.getElementById('perPageSelect').value = perPageFromUrl;
    }
});
</script>

<?= $this->endSection(); ?>