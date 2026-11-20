<link href="<?= base_url(); ?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css" />
<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation = \Config\Services::validation(); ?>

<?php
// Define the function once at the top of the file
function getCorrectImageUrl($path) {
    if (empty($path)) return '';
    
    // Check if path already has base_url
    if (strpos($path, base_url()) === 0) {
        return $path;
    }
    
    // Try different possible locations
    $possiblePaths = [
        $path,
        'uploads/testimonials/' . basename($path),
        'public/uploads/testimonials/' . basename($path),
        'assets/uploads/testimonials/' . basename($path),
    ];
    
    foreach ($possiblePaths as $testPath) {
        $fullPath = FCPATH . ltrim($testPath, '/');
        if (file_exists($fullPath)) {
            return base_url($testPath);
        }
    }
    
    // Return original path as fallback
    return base_url($path);
}
?>

<style>
    .badge {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 12px;
    }
    
    .badge-pending {
        background-color: #f39c12;
        color: white;
    }
    
    .badge-approved {
        background-color: #00a65a;
        color: white;
    }
    
    .badge-rejected {
        background-color: #dd4b39;
        color: white;
    }
    
    .badge-text {
        background-color: #3498db;
        color: white;
    }
    
    .badge-image {
        background-color: #9b59b6;
        color: white;
    }
    
    .filter-section {
        background: #f5f5f5;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }
    
    .filter-section label {
        font-weight: bold;
        margin-bottom: 5px;
        display: block;
    }
    
    .image-thumbnail {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
        margin-right: 5px;
    }
    
    .view-btn {
        margin-right: 5px;
    }
    
    .testimonial-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border-bottom: 1px solid #eee;
    }
    
    .testimonial-header .caption {
        margin: 0;
        font-size: 26px;
        font-weight: 500;
        color: #000;
    }
    
    .btn-add-testimonial {
        background-color: #ff7a18;
        color: #fff;
        padding: 10px 18px;
        font-size: 16px;
        font-weight: 500;
        border-radius: 2px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-add-testimonial:hover {
        background-color: #e66a0f;
        color: #fff;
    }
    
    .btn-add-testimonial .plus {
        font-size: 20px;
        line-height: 1;
    }
    
    .btn-loading {
        position: relative;
        color: transparent !important;
    }
    
    .btn-loading:after {
        content: '';
        position: absolute;
        left: 50%;
        top: 50%;
        width: 16px;
        height: 16px;
        margin: -8px 0 0 -8px;
        border: 2px solid #fff;
        border-top-color: transparent;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    .alert-message {
        padding: 10px 15px;
        margin: 10px 0;
        border-radius: 4px;
        font-weight: 500;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .ajax-loader {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: none;
    }
    
    .spinner-border {
        width: 2rem;
        height: 2rem;
        border: 0.25em solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: spinner-border .75s linear infinite;
    }
    
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0,0,0,0);
        white-space: nowrap;
        border: 0;
    }
    
    .text-warning {
        color: #f39c12;
    }
    
    .text-muted {
        color: #95a5a6;
    }
    
    .img-thumbnail {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 5px;
        background-color: #fff;
    }
    
    .mt-3 {
        margin-top: 15px;
    }
    
    .modal-lg {
        max-width: 800px;
    }
     .blue.btn {
    color: #FFFFFF !important;
    background-color: #F57F27 !important;
}
</style>

<div class="content">
    <div class="page-bar"><?= $breadcrumb; ?></div>
    <div class="page-container">
        <div class="tab-pane" id="tab_1">
            <div class="row">
                <div class="col-md-12">
                    <!-- Filter Section -->
                    <div class="filter-section">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Testimonial Type</label>
                                <select class="form-control" id="typeFilter">
                                    <option value="">All Types</option>
                                    <option value="text" <?= isset($_GET['type']) && $_GET['type'] == 'text' ? 'selected' : '' ?>>Text Testimonials</option>
                                    <option value="image" <?= isset($_GET['type']) && $_GET['type'] == 'image' ? 'selected' : '' ?>>Image Testimonials</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Approval Status</label>
                                <select class="form-control" id="approvalFilter">
                                    <option value="">All Status</option>
                                    <option value="Pending" <?= isset($_GET['approval_status']) && $_GET['approval_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Approved" <?= isset($_GET['approval_status']) && $_GET['approval_status'] == 'Approved' ? 'selected' : '' ?>>Approved</option>
                                    <option value="Rejected" <?= isset($_GET['approval_status']) && $_GET['approval_status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button class="btn blue" onclick="applyFilters()">Apply Filters</button>
                                <button class="btn default" onclick="clearFilters()">Clear</button>
                            </div>
                            <div class="col-md-2">
                                <label>Per Page</label>
                                <select class="form-control" id="perPageFilter" onchange="applyFilters()">
                                    <option value="10" <?= isset($perPage) && $perPage == 10 ? 'selected' : '' ?>>10 Items</option>
                                    <option value="20" <?= isset($perPage) && $perPage == 20 ? 'selected' : '' ?>>20 Items</option>
                                    <option value="50" <?= isset($perPage) && $perPage == 50 ? 'selected' : '' ?>>50 Items</option>
                                    <option value="100" <?= isset($perPage) && $perPage == 100 ? 'selected' : '' ?>>100 Items</option>
                                </select>
                            </div>
                            <div class="col-md-2 text-right">
                                <div class="stat-card">
                                    <h4>Pending: <span class="badge badge-pending"><?= $pending_count ?></span></h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption"><?= $pagetitle ?></div>
                            <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'add')) { ?>
                                <div class="actions">
                                    <a href="<?= base_url(); ?>testimonial/add" class="btn green" style="padding: 8px 12px;">
                                        Add Testimonial <i class="fa fa-plus"></i>
                                    </a>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="portlet-body">
                            <!-- Messages Container -->
                            <div id="messageContainer" style="display: none;"></div>
                            
                            <table class="table table-striped table-modern" id="testimonialTable">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="15%">Client</th>
                                        <th width="10%">Type</th>
                                        <th width="20%">Description</th>
                                        <th width="10%">Images</th>
                                        <th width="10%">Status</th>
                                        <th width="10%">Approval</th>
                                        <th width="15%">Date</th>
                                        <th width="15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($testimonialList) > 0) {
                                        $k = 1;
                                        foreach ($testimonialList as $row) { ?>
                                            <tr id="testimonial-<?= $row->testimonial_id ?>">
                                                <td><?= $k; ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($row->client_name); ?></strong><br>
                                                    <small><?= htmlspecialchars($row->designation); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $row->testimonial_type === 'image' ? 'badge-image' : 'badge-text'; ?>">
                                                        <?= ucfirst($row->testimonial_type); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?= character_limiter(strip_tags($row->description), 80); ?>
                                                    <?php if ($row->rating > 0): ?>
                                                        <br>
                                                        <small>
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <i class="fa fa-star <?= $i <= $row->rating ? 'text-warning' : 'text-muted'; ?>"></i>
                                                            <?php endfor; ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($row->testimonial_type === 'image' && $row->before_image): ?>
                                                        <?php $beforeImageUrl = getCorrectImageUrl($row->before_image); ?>
                                                        <img src="<?= $beforeImageUrl; ?>" class="image-thumbnail" title="Before"
                                                             onerror="this.style.display='none';">
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($row->testimonial_type === 'image' && $row->after_image): ?>
                                                        <?php $afterImageUrl = getCorrectImageUrl($row->after_image); ?>
                                                        <img src="<?= $afterImageUrl; ?>" class="image-thumbnail" title="After"
                                                             onerror="this.style.display='none';">
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $row->status === 'Active' ? 'badge-approved' : 'badge-rejected'; ?>">
                                                        <?= $row->status; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?= strtolower($row->approval_status); ?>" id="approval-badge-<?= $row->testimonial_id ?>">
                                                        <?= $row->approval_status; ?>
                                                    </span>
                                                </td>
                                                <td><?= (($ts = strtotime($row->date ?? '')) && $ts > 0 ? date('M d, Y', $ts) : (($ts2 = strtotime($row->created_on ?? '')) && $ts2 > 0 ? date('M d, Y', $ts2) : 'N/A')); ?></td>
                                                <td>
                                                    <!-- View Button with data attributes -->
                                                    <button class="btn default btn-xs blue view-btn" 
                                                            data-client="<?= htmlspecialchars($row->client_name, ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-designation="<?= htmlspecialchars($row->designation, ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-type="<?= $row->testimonial_type; ?>"
                                                            data-rating="<?= $row->rating; ?>"
                                                            data-description="<?= htmlspecialchars($row->description, ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-before-image="<?= $row->before_image ? getCorrectImageUrl($row->before_image) : ''; ?>"
                                                            data-after-image="<?= $row->after_image ? getCorrectImageUrl($row->after_image) : ''; ?>"
                                                            data-status="<?= $row->status; ?>"
                                                            data-approval="<?= $row->approval_status; ?>"
                                                            data-date="<?= (($ts = strtotime($row->date ?? '')) && $ts > 0 ? date('M d, Y', $ts) : (($ts2 = strtotime($row->created_on ?? '')) && $ts2 > 0 ? date('M d, Y', $ts2) : 'N/A')); ?>"
                                                            title="View">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                    
                                                    <!-- Edit Button -->
                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'edit')) { ?>
                                                        <a href="<?= base_url(); ?>testimonial/edit/<?= $row->testimonial_id; ?>" 
                                                           class="btn default btn-xs purple" 
                                                           title="Edit">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                    <?php } ?>
                                                    
                                                    <!-- Approval Actions (for pending testimonials) -->
                                                    <?php if ($row->approval_status === 'Pending'): ?>
                                                        <button class="btn default btn-xs green approve-btn" 
                                                                data-id="<?= $row->testimonial_id; ?>"
                                                                title="Approve">
                                                            <i class="fa fa-check"></i>
                                                        </button>
                                                        <button class="btn default btn-xs red reject-btn" 
                                                                data-id="<?= $row->testimonial_id; ?>"
                                                                title="Reject">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Delete/Close Button -->
                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'close')) { ?>
                                                        <button class="btn default btn-xs red delete-btn" 
                                                                data-id="<?= $row->testimonial_id; ?>"
                                                                title="Delete">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php $k++;
                                        }
                                    } else { ?>
                                        <tr>
                                            <td colspan="9" class="text-center">No testimonials found.</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                            
                            <!-- CI4 Pagination Links -->
                            <div class="mt-3 text-center">
                                <?= $pager->links() ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Testimonial Modal -->
<div class="modal fade" id="viewTestimonialModal" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Testimonial Details</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body" id="testimonialDetails">
                <!-- Details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Load required libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

<script type="text/javascript">
    // CSRF Token for AJAX requests
    var csrf_token = '<?= csrf_hash() ?>';
    var base_url = '<?= base_url() ?>';

    $(document).ready(function() {
        console.log('Document ready, initializing testimonial management...');
        
        // Initialize DataTable
        if ($.fn.DataTable) {
            $('#testimonialTable').DataTable({
                "paging": false,
                "info": false,
                "order": [[7, 'desc']],
                "language": {
                    "emptyTable": "No testimonials found",
                    "info": "Showing _START_ to _END_ of _TOTAL_ testimonials",
                    "infoEmpty": "Showing 0 to 0 of 0 testimonials",
                    "infoFiltered": "(filtered from _MAX_ total testimonials)",
                    "search": "Search:",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                }
            });
            console.log('DataTable initialized');
        } else {
            console.warn('DataTables not available, using basic table');
            $('#testimonialTable').addClass('table-striped table-bordered');
        }
        
        // Handle View button click
        $(document).on('click', '.view-btn', function() {
            var $btn = $(this);
            
            // Extract data from data attributes
            var client_name = $btn.data('client') || 'N/A';
            var designation = $btn.data('designation') || '';
            var testimonial_type = $btn.data('type') || 'text';
            var rating = parseInt($btn.data('rating')) || 0;
            var description = $btn.data('description') || 'No description provided';
            var before_image = $btn.data('before-image') || '';
            var after_image = $btn.data('after-image') || '';
            var status = $btn.data('status') || 'Inactive';
            var approval_status = $btn.data('approval') || 'Pending';
            var date = $btn.data('date') || 'N/A';
            
            // Build the modal content
            var html = '<div class="row">';
            
            // Client Info
            html += '<div class="col-md-12">';
            html += '<h4>' + client_name + '</h4>';
            if (designation) {
                html += '<p><strong>Designation:</strong> ' + designation + '</p>';
            }
            html += '</div>';
            
            // Rating
            html += '<div class="col-md-12">';
            html += '<p><strong>Rating:</strong> ';
            for (var i = 1; i <= 5; i++) {
                html += '<i class="fa fa-star ' + (i <= rating ? 'text-warning' : 'text-muted') + '"></i>';
            }
            html += ' (' + rating + '/5)</p>';
            html += '</div>';
            
            // Description
            html += '<div class="col-md-12">';
            html += '<p><strong>Description:</strong></p>';
            html += '<div style="background: #f9f9f9; padding: 15px; border-radius: 5px; border-left: 4px solid #3498db; max-height: 300px; overflow-y: auto;">';
            html += description;
            html += '</div>';
            html += '</div>';
            
            // Images for image testimonials
            if (testimonial_type === 'image' && (before_image || after_image)) {
                html += '<div class="col-md-12 mt-3">';
                html += '<p><strong>Images:</strong></p>';
                html += '<div class="row">';
                
                if (before_image) {
                    html += '<div class="col-md-6">';
                    html += '<p><strong>Before:</strong></p>';
                    html += '<img src="' + before_image + '" class="img-thumbnail" style="max-height: 200px; width: 100%; object-fit: contain; background: #f5f5f5;">';
                    html += '</div>';
                }
                
                if (after_image) {
                    html += '<div class="col-md-6">';
                    html += '<p><strong>After:</strong></p>';
                    html += '<img src="' + after_image + '" class="img-thumbnail" style="max-height: 200px; width: 100%; object-fit: contain; background: #f5f5f5;">';
                    html += '</div>';
                }
                
                html += '</div>';
                html += '</div>';
            }
            
            // Status and Dates
            html += '<div class="col-md-12 mt-3">';
            html += '<div class="row">';
            html += '<div class="col-md-4">';
            html += '<p><strong>Type:</strong> <span class="badge ' + (testimonial_type === 'image' ? 'badge-image' : 'badge-text') + '">' + 
                    testimonial_type.charAt(0).toUpperCase() + testimonial_type.slice(1) + '</span></p>';
            html += '</div>';
            html += '<div class="col-md-4">';
            html += '<p><strong>Status:</strong> <span class="badge ' + (status === 'Active' ? 'badge-approved' : 'badge-rejected') + '">' + 
                    status + '</span></p>';
            html += '</div>';
            html += '<div class="col-md-4">';
            html += '<p><strong>Approval:</strong> <span class="badge badge-' + approval_status.toLowerCase() + '">' + 
                    approval_status + '</span></p>';
            html += '</div>';
            html += '</div>';
            
            html += '<p><strong>Date:</strong> ' + date + '</p>';
            html += '</div>';
            html += '</div>';
            
            // Set content and show modal
            $('#testimonialDetails').html(html);
            $('#viewTestimonialModal').modal('show');
        });
        
        // Handle Approve button click
        $(document).on('click', '.approve-btn', function(e) {
            e.preventDefault();
            var testimonial_id = $(this).data('id');
            updateApproval(testimonial_id, 'Approved', $(this));
        });
        
        // Handle Reject button click
        $(document).on('click', '.reject-btn', function(e) {
            e.preventDefault();
            var testimonial_id = $(this).data('id');
            updateApproval(testimonial_id, 'Rejected', $(this));
        });
        
        // Handle Delete button click
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            var testimonial_id = $(this).data('id');
            close_testimonial(testimonial_id);
        });
    });

    // Show message function
    function showMessage(type, message) {
        var $container = $('#messageContainer');
        var alertClass = type === 'success' ? 'alert-success' : 'alert-error';
        
        $container.html('<div class="alert-message ' + alertClass + '">' + message + '</div>');
        $container.slideDown();
        
        // Auto hide after 5 seconds
        setTimeout(function() {
            $container.slideUp();
        }, 5000);
    }

    // Update approval status function
    function updateApproval(testimonial_id, status, $button) {
        if (confirm('Are you sure you want to ' + status.toLowerCase() + ' this testimonial?')) {
            console.log('Updating testimonial ' + testimonial_id + ' to status: ' + status);
            
            // Show loading on the button
            var originalHtml = $button.html();
            $button.addClass('btn-loading').prop('disabled', true);
            
            $.ajax({
                url: base_url + 'testimonial/update_approval',
                type: 'POST',
                data: {
                    testimonial_id: testimonial_id,
                    approval_status: status,
                    csrf_test_name: csrf_token
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Response:', response);
                    
                    // Remove loading
                    $button.removeClass('btn-loading').prop('disabled', false).html(originalHtml);
                    
                    if (response.success) {
                        // Update the approval badge
                        var $badge = $('#approval-badge-' + testimonial_id);
                        $badge.removeClass('badge-pending badge-approved badge-rejected')
                               .addClass('badge-' + status.toLowerCase())
                               .text(status);
                        
                        // Remove the approve/reject buttons
                        $button.closest('td').find('.approve-btn, .reject-btn').remove();
                        
                        // Show success message
                        showMessage('success', 'Testimonial has been ' + status.toLowerCase() + ' successfully!');
                    } else {
                        showMessage('error', response.message || 'Failed to update approval status.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    // Remove loading
                    $button.removeClass('btn-loading').prop('disabled', false).html(originalHtml);
                    showMessage('error', 'An error occurred while updating approval status.');
                }
            });
        }
    }

    // Delete testimonial function
    function close_testimonial(testimonial_id) {
        if (confirm('Are you sure you want to delete this testimonial? This action cannot be undone.')) {
            console.log('Deleting testimonial: ' + testimonial_id);
            
            $.ajax({
                url: base_url + 'testimonial/close',
                type: 'POST',
                data: {
                    testimonial_id: testimonial_id,
                    csrf_test_name: csrf_token
                },
                success: function(response) {
                    console.log('Delete response:', response);
                    
                    if (response == '1' || response == 1 || (response && response.success)) {
                        // Remove the row from table
                        $('#testimonial-' + testimonial_id).fadeOut(300, function() {
                            $(this).remove();
                            
                            // Check if table is empty
                            if ($('#testimonialTable tbody tr').length === 0) {
                                $('#testimonialTable tbody').html(
                                    '<tr><td colspan="9" class="text-center">No testimonials found.</td></tr>'
                                );
                            }
                        });
                        
                        showMessage('success', 'Testimonial deleted successfully!');
                    } else {
                        showMessage('error', response.message || 'Failed to delete testimonial.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Delete error:', error);
                    showMessage('error', 'An error occurred while deleting the testimonial.');
                }
            });
        }
    }

    // Apply filters function
    function applyFilters() {
        var typeFilter = $('#typeFilter').val();
        var approvalFilter = $('#approvalFilter').val();
        var perPageFilter = $('#perPageFilter').val();
        
        var url = new URL(window.location.href);
        
        if (typeFilter) {
            url.searchParams.set('type', typeFilter);
        } else {
            url.searchParams.delete('type');
        }
        
        if (approvalFilter) {
            url.searchParams.set('approval_status', approvalFilter);
        } else {
            url.searchParams.delete('approval_status');
        }

        if (perPageFilter && perPageFilter != '10') {
            url.searchParams.set('per_page', perPageFilter);
        } else {
            url.searchParams.delete('per_page');
        }
        
        window.location.href = url.toString();
    }

    // Clear filters function
    function clearFilters() {
        var url = new URL(window.location.href);
        url.searchParams.delete('type');
        url.searchParams.delete('approval_status');
        url.searchParams.delete('per_page');
        window.location.href = url.toString();
    }

    // Function to update URL parameters without reloading (for AJAX filtering)
    function updateURLParams(params) {
        var url = new URL(window.location);
        Object.keys(params).forEach(key => {
            if (params[key]) {
                url.searchParams.set(key, params[key]);
            } else {
                url.searchParams.delete(key);
            }
        });
        window.history.pushState({}, '', url);
    }

    // Add image error handling
    $(document).on('error', 'img', function() {
        $(this).hide();
    });

    // Add keyboard shortcuts
    $(document).keydown(function(e) {
        // Escape key closes modal
        if (e.keyCode === 27 && $('#viewTestimonialModal').is(':visible')) {
            $('#viewTestimonialModal').modal('hide');
        }
        
        // Ctrl+F focuses on filter inputs
        if (e.ctrlKey && e.keyCode === 70) {
            e.preventDefault();
            $('#typeFilter').focus();
        }
    });

    // Refresh CSRF token periodically (every 30 minutes)
    setInterval(function() {
        $.get(base_url + 'get-csrf-token', function(data) {
            if (data.csrf_token) {
                csrf_token = data.csrf_token;
                console.log('CSRF token refreshed');
            }
        });
    }, 1800000); // 30 minutes

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        location.reload();
    });

    // Add loading indicator for AJAX requests
    $(document).ajaxStart(function() {
        if ($('.ajax-loader').length === 0) {
            $('body').append(
                '<div class="ajax-loader" style="position: fixed; top: 20px; right: 20px; z-index: 9999; display: none;">' +
                '<div class="spinner-border text-primary" role="status">' +
                '<span class="sr-only">Loading...</span>' +
                '</div>' +
                '</div>'
            );
        }
        $('.ajax-loader').show();
    });

    $(document).ajaxStop(function() {
        $('.ajax-loader').hide();
    });
</script>
<?= $this->endSection(); ?>