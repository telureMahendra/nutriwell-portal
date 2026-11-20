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
                        // Debug: Check available variables (remove in production)
                        // echo '<!-- Available variables: ' . implode(', ', array_keys(get_defined_vars())) . ' -->';
                        // echo '<!-- FAQ exists: ' . (isset($faq) ? 'YES' : 'NO') . ' -->';
                        // if (isset($faq) && is_object($faq)) {
                        //     echo '<!-- FAQ object: ' . print_r($faq, true) . ' -->';
                        // }
                        
                        // Safely get FAQ data with fallback
                        $questionValue = '';
                        $answerValue = '';
                        $faqId = '';
                        
                        if (isset($faq) && is_object($faq)) {
                            $questionValue = set_value('question', $faq->question ?? '');
                            $answerValue = set_value('answer', $faq->answer ?? '');
                            $faqId = $faq->faq_id ?? '';
                        } elseif (isset($faq) && is_array($faq)) {
                            $questionValue = set_value('question', $faq['question'] ?? '');
                            $answerValue = set_value('answer', $faq['answer'] ?? '');
                            $faqId = $faq['faq_id'] ?? '';
                        } else {
                            $questionValue = set_value('question', '');
                            $answerValue = set_value('answer', '');
                            $faqId = '';
                        }
                        
                        // Form URL - handle both edit and create scenarios
                        $formUrl = $faqId ? base_url('faq/edit/' . $faqId) : base_url('faq/create');
                        
                        $attributes = array('class' => 'login-form', 'id' => 'faqform');
                        echo form_open_multipart($formUrl, $attributes);
                    ?>
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="question">Question<span class="required" aria-required="true"> *</span></label>
                                    <?php
                                        $data_question = array(
                                            'name'          => 'question',
                                            'id'            => 'question',
                                            'value'         => $questionValue,
                                            'class'         => 'form-control',
                                            'required'      => 'required',
                                            'rows'          => 3,
                                            'maxlength'     => '250',
                                            'placeholder'   => 'Question (max 250 characters)'
                                        );
                                        echo form_textarea($data_question);
                                    ?>
                                    <small class="char-count" id="question-count" style="float: right;">
                                        <span id="question-counter"><?= isset($faq) && ((is_object($faq) && !empty($faq->question)) || (is_array($faq) && !empty($faq['question']))) ? strlen(is_object($faq) ? $faq->question : $faq['question']) : 0 ?></span>/250 characters
                                    </small>
                                    <span class="help-block help-block-error" for="question"><?= $validation->showError('question') ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="answer">Answer<span class="required" aria-required="true"> *</span></label>
                                    <?php
                                        $data_answer = array(
                                            'name'          => 'answer',
                                            'id'            => 'answer',
                                            'value'         => $answerValue,
                                            'class'         => 'form-control',
                                            'required'      => 'required',
                                            'rows'          => 5,
                                            'maxlength'     => '1000',
                                            'placeholder'   => 'Answer (max 1000 characters)'
                                        );
                                        echo form_textarea($data_answer);
                                    ?>
                                    <small class="char-count" id="answer-count" style="float: right;">
                                        <span id="answer-counter"><?= isset($faq) && ((is_object($faq) && !empty($faq->answer)) || (is_array($faq) && !empty($faq['answer']))) ? strlen(is_object($faq) ? $faq->answer : $faq['answer']) : 0 ?></span>/1000 characters
                                    </small>
                                    <span class="help-block help-block-error" for="answer"><?= $validation->showError('answer') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions right">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <?php 
                                    $arr_submit = array(
                                        'name'          => 'submit',
                                        'value'         => $faqId ? 'Update FAQ' : 'Create FAQ',
                                        'class'         => 'btn green'
                                    );
                                    echo form_submit($arr_submit);
                                ?>
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
    
    // Trigger initial count for question
    $('#question').trigger('input');
    
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
    
    // Trigger initial count for answer
    $('#answer').trigger('input');
    
    // Form validation
    $('#faqform').submit(function(e) {
        var question = $('#question').val().trim();
        var answer = $('#answer').val().trim();
        
        // Clear previous errors
        $('.help-block-error').text('');
        $('.form-control').removeClass('error');
        
        var hasError = false;
        
        if (question.length === 0) {
            $('[for="question"]').next('.help-block-error').text('Please enter a question.');
            $('#question').addClass('error');
            hasError = true;
        } else if (question.length > 250) {
            $('[for="question"]').next('.help-block-error').text('Question cannot exceed 250 characters.');
            $('#question').addClass('error');
            hasError = true;
        }
        
        if (answer.length === 0) {
            $('[for="answer"]').next('.help-block-error').text('Please enter an answer.');
            $('#answer').addClass('error');
            hasError = true;
        } else if (answer.length > 1000) {
            $('[for="answer"]').next('.help-block-error').text('Answer cannot exceed 1000 characters.');
            $('#answer').addClass('error');
            hasError = true;
        }
        
        if (hasError) {
            e.preventDefault();
            // Scroll to first error
            $('html, body').animate({
                scrollTop: $('.help-block-error:not(:empty)').first().offset().top - 100
            }, 500);
            return false;
        }
        
        // Show loading indicator
        var submitBtn = $(this).find('input[type="submit"]');
        var originalText = submitBtn.val();
        submitBtn.val('Processing...').prop('disabled', true);
        
        // Allow form submission
        return true;
    });
});
</script>
<?= $this->endSection();?>