<html>

<head>
  <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,400i,700,900&display=swap" rel="stylesheet">
  <link href="<?php echo base_url(); ?>public/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
  <script src="<?php echo base_url(); ?>public/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>

</head>
<style>
  body {
    text-align: center;
    /* padding: 40px 0;
        background: #EBF0F5; */
  }

  h1 {
    color: #88B04B;
    font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
    font-weight: 900;
    font-size: 40px;
    margin-bottom: 10px;
  }

  p {
    color: #404F5E;
    font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
    font-size: 20px;
    margin: 0;
  }

  i {
    color: #9ABC66;
    font-size: 100px;
    line-height: 200px;
    margin-left: -15px;
  }

  /* .card {
    background: white;
    padding: 60px;
    border-radius: 40px;
    box-shadow: 0 2px 3px #C8D0D8;
    display: inline-block;
    margin: 0 auto;

  } */

  .card {
    width: 100%;
  }
</style>

<body>
  <div class="content" style="padding: 20px 50px;">
    <div class="row">

      <?php
      //$statusArray  = array("Initiated", "Success");
      $statusArray  = array("Initiated", "Success", "Awaited", "Shipped");

      if (in_array($orderStatus, $statusArray)) { ?>
        <div class="col-md-12" style="text-align: center;">
          <div class="card">
            <div style="border-radius:200px; height:200px; width:200px; background: #F8FAF5; margin:0 auto;">
              <i class="checkmark">✓</i>
            </div>
            <h1>Success</h1>
            <p>We received your purchase request.</p>

            <p>
              <string>Order No:</string> <?php echo $orderNo; ?>
            </p>
            <p>
              <string>Order Amount:</string> <?php echo str_replace("amount=", "", $orderAmt); ?>
            </p>
          </div>

        </div>
      <?php } else { ?>
        <div class="col-md-12" style="text-align: center;">
          <div class="card">
            <div style="border-radius:200px; height:200px; width:200px; background: #F8FAF5; margin:0 auto;">
              <i class="checkmark" style="color: #e10808;">&#10007;</i>
            </div>
            <h1 style="color: #e10808;"><?php echo $orderStatus; ?></h1>
            <p>Purchase request not completed.</p>
          </div>

        </div>

      <?php } ?>

    </div>

  </div>
</body>

</html>