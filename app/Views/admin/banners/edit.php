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
                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-edit"></i> <?= $pagetitle ?>
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form action="<?= base_url() ?>banners/edit/<?= $bannerDetails['banner_id'] ?>" method="post" enctype="multipart/form-data" class="horizontal-form">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Title <span class="required">*</span></label>
                                                <input type="text" name="title" class="form-control" value="<?= set_value('title', $bannerDetails['title']) ?>" placeholder="Enter Title">
                                                <?php if($validation->getError('title')) {?>
                                                    <div class='alert alert-danger'><?= $validation->getError('title'); ?></div>
                                                <?php }?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Status <span class="required">*</span></label>
                                                <select class="form-control" name="status">
                                                    <option value="Active" <?= ($bannerDetails['status'] == 'Active') ? 'selected' : '' ?>>Active</option>
                                                    <option value="Inactive" <?= ($bannerDetails['status'] == 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Banner Image</label>
                                                <input type="file" name="banner_image" class="form-control">
                                                <br>
                                                <img src="<?= base_url() . 'public/uploads/banner/' . $bannerDetails['banner_image']; ?>" width="150" />
                                                <?php if($validation->getError('banner_image')) {?>
                                                    <div class='alert alert-danger'><?= $validation->getError('banner_image'); ?></div>
                                                <?php }?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions right">
                                    <a href="<?= base_url(); ?>banners" class="btn default">Cancel</a>
                                    <input type="submit" name="submit" value="Edit Banner" class="btn green">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
