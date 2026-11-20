<?php $session = session(); 

//echo $session->get("suc_msg");
//print_r($session->get());


//die();

?>

<?= $this->include('layouts/header'); ?>
<?php ?>
<div class="page-container">
   <!-- BEGIN SIDEBAR -->
   <?php

   //if (session()->get('userrole') == 'Admin') {
      echo $this->include('layouts/sidebar');
   //} elseif (session()->get('userrole') == 'Doctor' || session()->get('userrole') == 'Dietitian' || session()->get('userrole') == 'Psychologist' || session()->get('userrole') == 'Fitness Trainer') {
     // echo view('layouts/doctor_sidebar');
   //} ?>
   <!-- END SIDEBAR MENU -->
   <!-- BEGIN CONTENT -->
   <div class="page-content-wrapper">
      <div class="page-content">
<?= $this->include('partials/flash_messages') ?>
         <?Php if ($session->get("msg-type") == 'success') { ?>
            <div id="message">
               <div style="padding: 5px;">
                  <div class="alert alert-success" id="success-alert">
                     <button type="button" class="close" data-dismiss="alert"></button>
                     <strong>Success!</strong>
                     <?php echo $session->get("suc_msg");?>
                  </div>
               </div>
            </div>
         <?php $session->remove('msg-type');} ?>

         <?Php if ($session->get("msg-type") == 'danger') { ?>
            <div id="message">
               <div style="padding: 5px;">
                  <div class="alert alert-danger" id="danger-alert">
                     <button type="button" class="close" data-dismiss="alert"></button>
                     <strong>Danger!</strong>
                     <?php echo $session->get("suc_msg");?>
                  </div>
               </div>
            </div>
         <?php $session->remove('msg-type');} ?>


         <?= $this->renderSection("content"); ?>
      </div>
   </div>
</div>
<?= $this->include('layouts/footer'); ?>
<?= $this->include('layouts/chat_widget'); ?>