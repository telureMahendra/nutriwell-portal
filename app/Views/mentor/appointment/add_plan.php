<link href="<?= base_url();?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>
<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation =  \Config\Services::validation(); ?>
<div class="content">
    <style type="text/css">
    span.help-block{
        color: #f30;
    }
    .drop-zone {
        max-width: 100%;
        height: 100px;
        padding: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-weight: 500;
        font-size: 16px;
        cursor: pointer;
        color: #777;
        border: 2px dashed #009578;
        border-radius: 5px;
        background: #f9f9f9;
        transition: background 0.3s;
    }
    .drop-zone--over {
        border-style: solid;
        background: #e9e9e9;
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
                        <?= $pagetitle?>
                    </div>
                </div>
                <div class="portlet-body form">
                <?php 
                    $attributes = array('class' => 'login-form', 'id' => 'loginform');
                    echo form_open_multipart('mentor/appointment/addplan/'.$appointment_id, $attributes);

                
                    $arr_submit = array(
                                'name'          => 'submit',
                                'value'         => $pagetitle,
                                'class'         => 'btn green'
                            );
                ?>
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label"><span class="required" aria-required="true"> *</span>Diet Plan ()</label>
                                    &nbsp;&nbsp;&nbsp; <?php if (!empty($appointmentList[0]->dietplan)) {
                                    ?>
                                    <a href="<?php echo base_url();?>mentor/appointment/download_diet/<?= $appointmentList[0]->dietplan; ?>" class="btn default btn-xs green" title="Download Diet Plan" alt="Download Diet Plan"><i class="fa fa-download"></i></a>
                                    <?php } ?> 

                                    <div class="drop-zone" onclick="document.getElementById('dietplan').click()">
                                        <span class="drop-zone__prompt" id="prompt-dietplan">Drag & Drop PDF file here or click to upload</span>
                                        <input type="file" name="dietplan" id="dietplan" style="display:none;" accept="application/pdf" onchange="updateFileName(this, 'prompt-dietplan')">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label"><span class="required" aria-required="true"> *</span>Workout Plan</label>
                                    &nbsp;&nbsp;&nbsp; <?php if (!empty($appointmentList[0]->workoutplan)) {
                                    ?>
                                    <a href="<?php echo base_url();?>mentor/appointment/download_workout/<?= $appointmentList[0]->workoutplan; ?>" class="btn default btn-xs green" title="Download Workout Plan" alt="Download Workout Plan"><i class="fa fa-download"></i></a>
                                    <?php } ?> 
                                    <div class="drop-zone" onclick="document.getElementById('workoutplan').click()">
                                        <span class="drop-zone__prompt" id="prompt-workoutplan">Drag & Drop PDF file here or click to upload</span>
                                        <input type="file" name="workoutplan" id="workoutplan" style="display:none;" accept="application/pdf" onchange="updateFileName(this, 'prompt-workoutplan')">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label"><span class="required" aria-required="true"> *</span>Prescription</label>
                                    &nbsp;&nbsp;&nbsp; <?php if (!empty($appointmentList[0]->prescription)) {
                                    ?>
                                    <a href="<?php echo base_url();?>mentor/appointment/download_prescription/<?= $appointmentList[0]->prescription; ?>" class="btn default btn-xs green" title="Download Prescription" alt="Download Prescription"><i class="fa fa-download"></i></a>
                                    <?php } ?> 
                                    <div class="drop-zone" onclick="document.getElementById('prescription').click()">
                                        <span class="drop-zone__prompt" id="prompt-prescription">Drag & Drop PDF file here or click to upload</span>
                                        <input type="file" name="prescription" id="prescription" style="display:none;" accept="application/pdf" onchange="updateFileName(this, 'prompt-prescription')">
                                    </div>
                                </div>
                            </div>
                            
                            

                        </div>
                    </div>
                    <div class="form-actions right">
                        <div class="row">
                              <div class="col-md-offset-3 col-md-9">
                                <?php echo form_submit($arr_submit)?>
                                <a href="<?php echo base_url();?>mentor/appointment"><button type="button" class="btn default">Cancel</button></a>
                              </div>
                          <div class="col-md-6"></div>
                        </div>
                      </div>
                    <?= form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function updateFileName(input, promptId) {
    if (input.files && input.files.length > 0) {
        var file = input.files[0];
        if (file.type !== "application/pdf") {
            alert("Please upload a PDF file only.");
            input.value = ""; // Clear
            document.getElementById(promptId).innerHTML = "Drag & Drop PDF file here or click to upload";
            return;
        }
        document.getElementById(promptId).innerHTML = "Selected file: " + file.name;
    } else {
        document.getElementById(promptId).innerHTML = "Drag & Drop PDF file here or click to upload";
    }
}

document.querySelectorAll('.drop-zone').forEach(dropZoneElement => {
    dropZoneElement.addEventListener("dragover", e => {
        e.preventDefault();
        dropZoneElement.classList.add("drop-zone--over");
    });

    ["dragleave", "dragend"].forEach(type => {
        dropZoneElement.addEventListener(type, e => {
            dropZoneElement.classList.remove("drop-zone--over");
        });
    });

    dropZoneElement.addEventListener("drop", e => {
        e.preventDefault();
        dropZoneElement.classList.remove("drop-zone--over");

        if (e.dataTransfer.files.length) {
            let input = dropZoneElement.querySelector("input[type='file']");
            input.files = e.dataTransfer.files;
            const event = new Event('change');
            input.dispatchEvent(event);
        }
    });
});
</script>
<?= $this->endSection();?>