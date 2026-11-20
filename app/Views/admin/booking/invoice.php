<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body {
            font-family: Verdana, Geneva, sans-serif;
            font-size: 13px;
        }

        p {
            margin: 0 0 1px;
        }

        table {
            font-family: Verdana, Geneva, sans-serif;
            font-size: 14px;
        }

        .table-border {
            border-collapse: collapse;
            width: 100%;
        }

        .table-border th,
        .table-border td {
            border: 1px solid #ddd;
            /*padding: 2px;*/
        }

        .table-noborder {
            border-collapse: collapse;
            width: 100%;
        }

        .table-noborder th,
        .table-noborder td {
            border: 0px solid #ddd;
        }
    </style>
</head>

<body>
    <table class="table-border">
        <tr>
            <th>
                <h1>Invoice</h1>
            </th>
        </tr>
        <tr>
            <td>
                <p><img src="<?php echo base_url("public/assets/admin/pages/img/logo-black.png"); ?>" width="30%"></p>
                <h2>Kaizen Global Services</h2>
                <p style="margin:2px;font-size:14px;">58th West, 1st Floor, Above DCB Bank, Opposite Andheri Subway, Andheri West - 400058</p>
                <p style="margin:2px;font-size:14px;"><strong>GSTIN:</strong> 27AARPI0054K1ZJ</p>
                <p style="margin:2px;font-size:14px;"><strong>Contact No:</strong> 91 7400174991</p>
                <p style="margin:2px;font-size:14px;"><strong>Email ID:</strong> info@healthie4u.com</p>
                <p style="margin:2px;font-size:14px;"><strong>Website:</strong> www.healthie4u.com</p>
            </td>
        </tr>
        <tr>
            <td>
                <table width="100%" class="table-noborder">
                    <tr>
                        <td><strong> Bill To:</strong> <?php echo $booking[0]->client_name . ' ('.$booking[0]->client_mobile_number.')'; ?></td>
                        <td align="right">
                            <strong>Date Of Invoice:</strong> <?php echo date('d/m/Y', strtotime($booking[0]->date)); ?><br>
                            <strong>Invoice Number:</strong> <?php echo sprintf("%08d", $booking[0]->booking_id); ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="height: 600px;" valign="middle">
                <table width="90%" align="center">
                    <tr>
                        <th>Description</th>
                        <th width="10%">Qty.</th>
                        <th width="15%">Actual Price <br>(Rs)</th>
                        <th width="12%">Discount</th>
                        <th width="15%">Payable Amount<br>(Rs)</th>
                    </tr>
                    <tr valign="top">
                        <td style="height: 400px;"><?php echo $booking[0]->name; ?> <br> <small>(<?php echo $booking[0]->plan_duration; ?>)</small></td>
                        <td align="center">1</td>
                        <td align="center"><?php echo number_format($booking[0]->subtotal, 2); ?></td>
                        <td align="center"><?php echo number_format($booking[0]->discount, 2); ?></td>
                        <td align="center"><?php echo number_format($booking[0]->total_amount, 2); ?></td>
                    </tr>
                    <tr>
                        <th align="right">Total (Rs) :</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                        <th align="center"><?php echo number_format($booking[0]->total_amount, 2); ?></th>
                    </tr>
                    <tr>
                        <th align="right">Paid Amount(Rs):</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                        <th align="center"><?php echo number_format($booking[0]->total_amount, 2); ?></th>
                    </tr>
                    <tr>
                        <th align="right">Balance Amount (Rs):</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                        <th align="center">0.00</th>
                    </tr>
                    <tr>
                        <td align="left" colspan="5"><i>*Inc of all taxes</i></td>
                    </tr>
                    
                </table>
            </td>
        </tr>
        <tr>
            <td style="text-align:center;height: 40px;">Thank you for purchasing Healthie4U Subscription.</td>
        </tr>
        <tr>
            <td style="text-align:center;height: 40px;">Anyquestions? Contact us at support@healthie4.com (mailto: support@healthie4u.com)</td>
        </tr>
    </table>
    <?php if (isset($print_mode) && $print_mode): ?>
    <script type="text/javascript">
        window.onload = function() {
            window.print();
        };
    </script>
    <?php endif; ?>
</body>

</html>