<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $pagetitle ?? 'FAQ Management' ?></title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    
    <!-- DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .page-bar {
            background: #ffffff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .portlet {
            background: #ffffff;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .portlet-title {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .portlet-title .caption {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        .portlet-body {
            padding: 20px;
        }
        .btn-green {
            background: #32c5d2;
            border-color: #32c5d2;
            color: #fff;
        }
        .btn-green:hover {
            background: #2bb8c4;
            border-color: #2bb8c4;
        }
        .table th {
            background: #f8f9fa;
        }
        .modal-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        .alert-error {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
    </style>
</head>
<body>
    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>
    
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-error">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>
    
    <div class="page-bar">
        <?= $breadcrumb; ?>
    </div>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption">
                            <?= $pagetitle ?>
                        </div>
                        <a href="<?= base_url('faq/add') ?>" class="btn btn-green">
                            <i class="fa fa-plus"></i> Add FAQ
                        </a>
                    </div>
                    
                    <div class="portlet-body">
                        <?php if (empty($faqList)): ?>
                            <div class="alert alert-info">
                                No FAQs found. <a href="<?= base_url('faq/add') ?>">Add your first FAQ</a>
                            </div>
                        <?php else: ?>
                            <table class="table table-striped table-bordered" id="sample_1">
                                <thead>
                                    <tr>
                                        <th width="5%">Sr. No.</th>
                                        <th width="20%">Question</th>
                                        <th width="65%">Answer</th>
                                        <th width="10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $counter = 1; ?>
                                    <?php foreach ($faqList as $row): ?>
                                        <tr>
                                            <td><?= $counter++; ?></td>
                                            <td><?= htmlspecialchars($row->question) ?></td>
                                            <td><?= nl2br(htmlspecialchars(substr($row->answer, 0, 150))) . (strlen($row->answer) > 150 ? '...' : '') ?></td>
                                            <td>
                                                <a href="<?= base_url('faq/edit/' . $row->faq_id) ?>" 
                                                   class="btn btn-default btn-xs" 
                                                   title="Edit" style="background: #8e44ad; color: white;">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <button onclick="deleteFaq(<?= $row->faq_id ?>)" 
                                                        class="btn btn-default btn-xs" 
                                                        title="Delete" style="background: #e74c3c; color: white;">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Delete FAQ</h4>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this FAQ? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    $(document).ready(function() {
        $('#sample_1').DataTable({
            "pageLength": 10,
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            }
        });
    });
    
    let currentFaqId = null;
    
    function deleteFaq(faqId) {
        currentFaqId = faqId;
        $('#deleteModal').modal('show');
    }
    
    $('#confirmDelete').click(function() {
        if (currentFaqId) {
            $.ajax({
                url: '<?= base_url("faq/close") ?>',
                type: 'POST',
                data: { faq_id: currentFaqId },
                success: function(response) {
                    if (response == 1) {
                        location.reload();
                    } else {
                        alert('Failed to delete FAQ. Please try again.');
                    }
                },
                error: function() {
                    alert('Network error. Please try again.');
                }
            });
        }
        $('#deleteModal').modal('hide');
    });
    </script>
</body>
</html>