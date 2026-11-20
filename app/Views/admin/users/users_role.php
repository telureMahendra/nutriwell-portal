<?php
$db = db_connect();

$Sql   = "SELECT * FROM adminmenu WHERE parentID=0 AND isDeleted=0 ORDER BY menuID ASC";
$Query = $db->query($Sql);
$menuResult = $Query->getResult();


//$userID = 1;

?>
<link href="<?= base_url(); ?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css" />
<!-- BEGIN DASHBOARD STATS -->

<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation =  \Config\Services::validation(); ?>




<div class="content">




    <div class="page-bar">
        <?= $breadcrumb; ?>
    </div>
    <div class="page-container">
        <div class="tab-pane" id="tab_1">
            <div class="row">
                <div class="col-md-12">
                    <!-- BEGIN EXAMPLE TABLE PORTLET-->
                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-arrow-right"></i><?= $pagetitle ?>
                            </div>
                        </div>
                        <div class="portlet-body">
                        <?php 
                    $attributes = array('class' => 'login-form', 'id' => 'loginform');
                    echo form_open_multipart('users/role/'.$userID,$attributes);
                    
                    $arr_submit = array(
                        'name'          => 'submit',
                        'value'         => "Submit",
                        'class'         => 'btn green'
                    );
                    
                    ?>                            
                            <table id="tableData" class="table table-striped table-bordered" style="width:100%;padding-top:10px;">
                                <thead>
                                    <tr>
                                        <th style="width: 25px;">Sr.No</th>
                                        <th style="width: 200px;">Menu Title</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                    <?php
                                    if ($menuResult) {
                                        $pNo = 0;
                                        foreach ($menuResult as $menu) {
                                            $pNo++;

                                            /*if($userRow["isRoot"]){
						$subSql   = "
						SELECT 
							adminmenu.*,
							ur.rID
						FROM 
							adminmenu 
						LEFT JOIN 
							(SELECT rID,userID,menuID from userrole WHERE userID='".$user->userID."') ur ON adminmenu.menuID=ur.menuID 
						WHERE 
							adminmenu.parentID='".$menu["menuID"]."' AND
							adminmenu.isDeleted=0 
						ORDER BY 
							menuID ASC";	
					}else{*/
                                            $subSql   = "
						SELECT 
							adminmenu.*,
							ur.rID
						FROM 
							adminmenu 
						LEFT JOIN 
							(SELECT rID,userID,menuID from userrole WHERE userID='" . $userID . "') ur ON adminmenu.menuID=ur.menuID 
						WHERE 
							adminmenu.parentID='" . $menu->menuID . "' AND 
							adminmenu.isActive=1 AND 
							adminmenu.isDeleted=0 
						ORDER BY 
							menuID ASC";
                                            //}			
                                            $subQuery = $db->query($subSql);
                                            $subResult = $subQuery->getResult();
                                            if ($subResult) {
                                    ?>
                                                <tr>
                                                    <th style="text-align: center;"><?php echo $pNo; ?>.</th>
                                                    <th><?php echo $menu->menuTitle; ?></th>
                                                    <td style="text-wrap: wrap;"><?php foreach ($subResult as $sub) {
                                                                                        $checked = '';

                                                                                        if ($sub->rID > 0) {
                                                                                            $checked = 'checked';
                                                                                        }
                                                                                    ?>
                                                            <div style="display: inline;">
                                                                <label>
                                                                    <input type="checkbox" name="menuID[]" class="roleChk" value="<?php echo $sub->menuID; ?>" <?php echo $checked; ?>><?php echo $sub->menuTitle; ?>
                                                                </label>
                                                            </div>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                    <?php
                                            }
                                        }
                                    } ?>
                                </thead>
                                <tr>
                                    <th colspan="3"><?php echo form_submit($arr_submit)?></th>
                                </tr>
                            </table>
                            <?= form_close(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="close_user" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Close User</h4>
            </div>
            <div class="modal-body">
                Are you sure to close this User?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default" id="yes">Yes</button>
                <button type="button" class="btn default" id="no" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function close_user(uid) {
        $('#close_user').modal();
        $('#close_user #yes').click(function() {
            var url = '<?= base_url('users/close') ?>'; // Generate the URL
            // alert(url);
            $.post(url, {
                uid: uid,
            }, function(responseText) {
                if (responseText == 1) {
                    location.href = '<?= base_url('users') ?>'; // Redirect to the users page
                } else {
                    alert('Something went wrong with your request');
                }
            });
        });
    }
</script>
<?= $this->endSection(); ?>