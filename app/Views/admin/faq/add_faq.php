<link href="<?= base_url();?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>
<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation = \Config\Services::validation(); ?>
<div class="content">
    <style type="text/css">
        span.help-block{
            color: #f30;
        }
        .char-count {
            font-size: 12px;
            color: #666;
        }
        .char-count.warning {
            color: #ff9800;
        }
        .char-count.error {
            color: #f44336;
            font-weight: bold;
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
                        $attributes = array('class' => 'login-form', 'id' => 'faqform');
                        echo form_open_multipart('faq/add', $attributes);

                        $question = set_value('question');                  
                        $data_question = array(
                            'name'          => 'question',
                            'id'            => 'question',
                            'value'         => $question,
                            'class'         => 'form-control',
                            'required'      => 'required',
                            'rows'          => 3,
                            'maxlength'     => '250',
                            'placeholder'   => 'Question (max 250 characters)'
                        );

                        $answer = set_value('answer');                  
                        $data_answer = array(
                            'name'          => 'answer',
                            'id'            => 'answer',
                            'value'         => $answer,
                            'class'         => 'form-control',
                            'required'      => 'required',
                            'rows'          => 5,
                            'maxlength'     => '1000',
                            'placeholder'   => 'Answer (max 1000 characters)'
                        );

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
                                    <label class="control-label" for="question">Question<span class="required" aria-required="true"> *</span></label>
                                    <?= form_textarea($data_question); ?>
                                    <small class="char-count" id="question-count" style="float: right;">
                                        <span id="question-counter">0</span>/250 characters
                                    </small>
                                    <span class="help-block help-block-error" for="question"><?= $validation->showError('question') ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="answer">Answer<span class="required" aria-required="true"> *</span></label>
                                    <?= form_textarea($data_answer); ?>
                                    <small class="char-count" id="answer-count" style="float: right;">
                                        <span id="answer-counter">0</span>/1000 characters
                                    </small>
                                    <span class="help-block help-block-error" for="answer"><?= $validation->showError('answer') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions right">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <?php echo form_submit($arr_submit)?>
                                <a href="<?php echo base_url();?>faq"><button type="button" class="btn default">Cancel</button></a>
                            </div>
                            <div class="col-md-6"> </div>
                        </div>
                    </div>
                    <?= form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>  

<?= $this->endSection();?>

<?= $this->section("scripts"); ?>
<script type="text/javascript">
$(document).ready(function() {
    // Character counter for question
    $('#question').on('input', function() {
        var maxLength = 250;
        var currentLength = $(this).val().length;
        var remaining = maxLength - currentLength;
        
        // Update counter display
        $('#question-counter').text(currentLength);
        
        // Update counter styling
        var $counter = $('#question-count');
        $counter.removeClass('warning error');
        
        if (remaining <= 20) {
            $counter.addClass('warning');
        }
        if (remaining <= 10) {
            $counter.addClass('error');
        }
    });
    
    // Character counter for answer
    $('#answer').on('input', function() {
        var maxLength = 1000;
        var currentLength = $(this).val().length;
        var remaining = maxLength - currentLength;
        
        // Update counter display
        $('#answer-counter').text(currentLength);
        
        // Update counter styling
        var $counter = $('#answer-count');
        $counter.removeClass('warning error');
        
        if (remaining <= 100) {
            $counter.addClass('warning');
        }
        if (remaining <= 50) {
            $counter.addClass('error');
        }
    });
    
    // Initialize counters on page load
    $('#question').trigger('input');
    $('#answer').trigger('input');
    
    // Form validation
    $('#faqform').submit(function(e) {
        var question = $('#question').val().trim();
        var answer = $('#answer').val().trim();
        
        if (question.length === 0) {
            alert('Please enter a question.');
            $('#question').focus();
            return false;
        }
        
        if (answer.length === 0) {
            alert('Please enter an answer.');
            $('#answer').focus();
            return false;
        }
        
        if (question.length > 250) {
            alert('Question cannot exceed 250 characters.');
            $('#question').focus();
            return false;
        }
        
        if (answer.length > 1000) {
            alert('Answer cannot exceed 1000 characters.');
            $('#answer').focus();
            return false;
        }
        
        return true;
    });
});
</script>
<?= $this->endSection();?>