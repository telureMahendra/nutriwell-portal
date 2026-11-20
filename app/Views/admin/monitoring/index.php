<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>

<div class="content">
    <div class="page-bar">
        <?= $breadcrumb; ?>
    </div>

    <div class="page-container">
        <div class="tab-pane" id="tab_1">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box grey-cascade">
                        <div class="portlet-body">
                            <!-- ====== TITLE ====== -->
                            <div class="table-toolbar">
                                <div style="display:flex; align-items:center; justify-content:space-between;">
                                    <h3 style="margin:0; padding:10px 0;"><?= $pagetitle ?></h3>
                                </div>
                            </div>
                            <!-- ====== END TOOLBAR ROW ====== -->

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-modern " id="sample_1">
                                            <thead>
                                                <tr>
                                                    <th>Sr. No.</th>
                                                    <th>Source of Information</th>
                                                    <th>Number of Users</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (count($stats) > 0) {
                                                    $k = 1;
                                                    foreach ($stats as $row) { ?>
                                                        <tr class="odd gradeX">
                                                            <td><?= $k; ?></td>
                                                            <td><span class="label label-sm label-success"><?= ucfirst(htmlspecialchars($row->source_of_info)); ?></span></td>
                                                            <td><strong><?= $row->count; ?></strong></td>
                                                        </tr>
                                                <?php $k++; } } else { ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center">No monitoring data available.</td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="portlet light bordered">
                                        <div class="portlet-title">
                                            <div class="caption">
                                                <i class="icon-pie-chart font-green-haze"></i>
                                                <span class="caption-subject bold uppercase font-green-haze"> Marketing Spread</span>
                                            </div>
                                        </div>
                                        <div class="portlet-body">
                                            <!-- Simple placeholder or dynamic chart -->
                                            <div id="chart_div" style="height:300px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
                            <script type="text/javascript">
                                google.charts.load('current', {'packages':['corechart']});
                                google.charts.setOnLoadCallback(drawChart);

                                function drawChart() {
                                    var data = new google.visualization.DataTable();
                                    data.addColumn('string', 'Source');
                                    data.addColumn('number', 'Users');
                                    
                                    data.addRows([
                                        <?php foreach($stats as $row): ?>
                                            ['<?= addslashes(ucfirst($row->source_of_info)) ?>', <?= $row->count ?>],
                                        <?php endforeach; ?>
                                    ]);

                                    var options = {
                                        title: 'User Acquisition Sources',
                                        is3D: true,
                                        backgroundColor: 'transparent'
                                    };

                                    var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
                                    chart.draw(data, options);
                                }
                            </script>

                        </div> 
                    </div> <!-- END PORTLET -->
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
