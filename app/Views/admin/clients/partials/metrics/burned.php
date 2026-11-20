<!-- Calories Burned Modal -->
<?php
// Fetch calories data if not provided
if (!isset($calories_data) || empty($calories_data)) {
    $clientId = $clientDetails[0]->client_id ?? 0;
    
    if ($clientId > 0) {
        try {
            // Get database connection
            $db = \Config\Database::connect();
            
            // Get today's calories burned
            $builder = $db->table('calories_burned');
            $builder->select('SUM(calories_burned) as today')
                   ->where('client_id', $clientId)
                   ->where('date', date('Y-m-d'));
            $todayResult = $builder->get()->getRow();
            $today = $todayResult ? intval($todayResult->today) : 0;
            
            // Get weekly average (last 7 days)
            $startDate = date('Y-m-d', strtotime('-6 days'));
            $builder = $db->table('calories_burned');
            $builder->select('date, SUM(calories_burned) as daily_total')
                   ->where('client_id', $clientId)
                   ->where('date >=', $startDate)
                   ->where('date <=', date('Y-m-d'))
                   ->groupBy('date');
            $weeklyData = $builder->get()->getResult();
            
            $weeklyTotals = [];
            foreach ($weeklyData as $row) {
                $weeklyTotals[] = intval($row->daily_total);
            }
            
            $average = !empty($weeklyTotals) ? round(array_sum($weeklyTotals) / count($weeklyTotals)) : 0;
            
            // Get target from calories_target table
            $target = 500; // Default
            $builder = $db->table('calories_target');
            $builder->select('target_calories')
                   ->where('client_id', $clientId)
                   ->where('target_type', 'burned')
                   ->where('target_date <=', date('Y-m-d'))
                   ->orderBy('target_date', 'DESC')
                   ->limit(1);
            $targetResult = $builder->get()->getRow();
            if ($targetResult) {
                $target = intval($targetResult->target_calories);
            }
            
            // Calculate completion
            $completion = $target > 0 ? round(($today / $target) * 100, 1) : 0;
            
            // Get last 7 days data for chart
            $series = [];
            $categories = [];
            $detailed_data = [];
            
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $dayName = date('D', strtotime($date));
                
                // Get calories for this day
                $builder = $db->table('calories_burned');
                $builder->select('SUM(calories_burned) as total')
                       ->where('client_id', $clientId)
                       ->where('date', $date);
                $dayResult = $builder->get()->getRow();
                $dayTotal = $dayResult ? intval($dayResult->total) : 0;
                
                // Get activities for this day
                $builder = $db->table('calories_burned');
                $builder->select('*')
                       ->where('client_id', $clientId)
                       ->where('date', $date)
                       ->orderBy('created_at', 'ASC');
                $activities = $builder->get()->getResultArray();
                
                $activityType = 'General Exercise';
                $duration = 30;
                if (!empty($activities)) {
                    $activityType = $activities[0]['activity_type'] ?? 'General Exercise';
                    $duration = $activities[0]['duration_minutes'] ?? 30;
                }
                
                $series[] = $dayTotal;
                $categories[] = $dayName;
                $detailed_data[] = [
                    'date' => $date,
                    'formatted_date' => date('M d, Y', strtotime($date)),
                    'calories_burned' => $dayTotal,
                    'activity_type' => $activityType,
                    'duration_minutes' => $duration
                ];
            }
            
            // Get recent history (last 10 entries)
            $builder = $db->table('calories_burned');
            $builder->select('*')
                   ->where('client_id', $clientId)
                   ->orderBy('date', 'DESC')
                   ->orderBy('created_at', 'DESC')
                   ->limit(10);
            $history = $builder->get()->getResultArray();
            
            // If detailed_data is empty but we have history, use it
            if (empty($detailed_data) && !empty($history)) {
                foreach ($history as $row) {
                    $detailed_data[] = [
                        'date' => $row['date'],
                        'formatted_date' => date('M d, Y', strtotime($row['date'])),
                        'calories_burned' => intval($row['calories_burned']),
                        'activity_type' => $row['activity_type'],
                        'duration_minutes' => $row['duration_minutes']
                    ];
                }
            }
            
            $calories_data = [
                'today' => $today,
                'average' => $average,
                'target' => $target,
                'completion' => $completion,
                'series' => [$series], // Must be array of arrays for ApexCharts
                'categories' => $categories,
                'detailed_data' => $detailed_data
            ];
            
        } catch (\Exception $e) {
            // Fallback data if there's an error
            $calories_data = [
                'today' => 0,
                'average' => 0,
                'target' => 500,
                'completion' => 0,
                'series' => [[0, 0, 0, 0, 0, 0, 0]],
                'categories' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'detailed_data' => []
            ];
        }
    } else {
        // Default data if no client ID
        $calories_data = [
            'today' => 0,
            'average' => 0,
            'target' => 500,
            'completion' => 0,
            'series' => [[0, 0, 0, 0, 0, 0, 0]],
            'categories' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'detailed_data' => []
        ];
    }
}
?>


<div class="modal fade metric-modal burned-modal" id="burnedGraphModal" tabindex="-1" role="dialog" aria-labelledby="caloriesModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #ff6b6b, #ee5253); color: white; border-bottom: none; padding: 20px; border-radius: 6px 6px 0 0;">
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1; text-shadow: none; margin-top: -5px;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" style="font-weight: 700; margin: 0; font-size: 18px;">
                    <i class="fa fa-fire" style="margin-right: 8px;"></i> Calories Burned Tracking <span style="opacity: 0.8; font-weight: 400; font-size: 16px;">| <?= htmlspecialchars($clientDetails[0]->client_name ?? 'Client'); ?></span>
                </h4>
            </div>
            <div class="modal-body">



                <!-- Stats Cards -->
                <div class="row mb-4" style="margin-top: 10px;">
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-danger">
                            <i class="fa fa-fire bsc-icon"></i>
                            <h2 id="todayCalories"><?= number_format($calories_data['today']); ?></h2>
                            <p>Today's Burned</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-warning">
                            <i class="fa fa-calendar bsc-icon"></i>
                            <h2 id="avgCalories"><?= number_format($calories_data['average']); ?></h2>
                            <p>Daily Average</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-info">
                            <i class="fa fa-bullseye bsc-icon"></i>
                            <h2 id="targetCalories"><?= number_format($calories_data['target']); ?></h2>
                            <p>Daily Target</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-success">
                            <i class="fa fa-check-circle bsc-icon"></i>
                            <h2 id="caloriesCompletion"><?= $calories_data['completion']; ?>%</h2>
                            <p>Completion Rate</p>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mb-4" style="background: #f8f9fa; padding: 20px; border-radius: 12px; box-shadow: inset 0 2px 5px rgba(0,0,0,0.03); margin-bottom: 25px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-weight: 700; color: #2d3436;">
                        <span><i class="fa fa-bolt" style="color: #ee5253; margin-right: 6px;"></i> Progress towards daily target</span>
                        <span id="progressPercentage" style="color: #ee5253;"><?= $calories_data['completion']; ?>%</span>
                    </div>
                    <div class="progress" style="height: 14px; border-radius: 10px; background-color: #dfe6e9; box-shadow: none; margin-bottom: 0;">
                        <div class="progress-bar progress-bar-striped active" 
                             role="progressbar" 
                             style="width: <?= min($calories_data['completion'], 100); ?>%; background: linear-gradient(90deg, #ff6b6b, #ee5253); border-radius: 10px;"
                             aria-valuenow="<?= $calories_data['completion']; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                </div>

                <!-- Graph Section -->
                <div class="card mb-4" style="border: none; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border-radius: 14px; overflow: hidden; margin-bottom: 25px;">
                    <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #f1f2f6; padding: 18px 25px; display: flex; justify-content: space-between; align-items: center;">
                        <h5 class="mb-0" style="color: #ee5253; font-weight: 700; margin: 0;">
                            <i class="fa fa-chart-area" style="margin-right: 8px;"></i> Calories Burned Trend
                        </h5>
                        <div class="btn-group" style="background: #f8f9fa; border-radius: 4px; padding: 4px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);">
                            <button type="button" class="btn btn-sm burned-time-btn active" data-days="7" onclick="changeBurnedTimeRange(7)" style="border: none; background: #ee5253; color: white; font-weight: 500; padding: 5px 12px; border-radius: 3px; box-shadow: 0 2px 4px rgba(238,82,83,0.3);">7 Days</button>
                            <button type="button" class="btn btn-sm burned-time-btn" data-days="30" onclick="changeBurnedTimeRange(30)" style="border: none; background: transparent; color: #555; font-weight: 500; padding: 5px 12px; border-radius: 3px;">30 Days</button>
                            <button type="button" class="btn btn-sm burned-time-btn" data-days="90" onclick="changeBurnedTimeRange(90)" style="border: none; background: transparent; color: #555; font-weight: 500; padding: 5px 12px; border-radius: 3px;">90 Days</button>
                            <button type="button" class="btn btn-sm burned-time-btn" data-days="365" onclick="changeBurnedTimeRange(365)" style="border: none; background: transparent; color: #555; font-weight: 500; padding: 5px 12px; border-radius: 3px;">1 Year</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="metric-graph-container" style="position: relative;">
                            <div id="caloriesChart" style="height: 350px; min-height: 350px;"></div>
                            <div id="caloriesTooltip" class="tooltip-custom" style="display: none;">
                                <strong id="caloriesTooltipDate"></strong><br>
                                <span id="caloriesTooltipCalories"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calories Burned History Table -->
                <div class="card mb-4" style="border: none; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border-radius: 14px; overflow: hidden;">
                    <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #f1f2f6; padding: 18px 25px; display: flex; justify-content: space-between; align-items: center;">
                        <h5 class="mb-0" style="color: #2d3436; font-weight: 700; margin: 0;">
                            <i class="fa fa-history" style="color: #ee5253; margin-right: 8px;"></i> Recent Calories Burned History
                        </h5>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary" onclick="exportCaloriesData()">
                                <i class="fa fa-download"></i> Export
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="loadCaloriesHistory()">
                                <i class="fa fa-refresh"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Date Range Filter -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>From Date</label>
                                    <input type="date" class="form-control" id="caloriesStartDate"
                                           value="<?= date('Y-m-d', strtotime('-7 days')); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>To Date</label>
                                    <input type="date" class="form-control" id="caloriesEndDate"
                                           value="<?= date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label><br>
                                    <button type="button" class="btn btn-primary" onclick="filterCalories()">
                                        <i class="fa fa-filter"></i> Filter
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="resetCaloriesFilter()">
                                        <i class="fa fa-refresh"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="caloriesHistoryTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Activity</th>
                                        <th>Duration</th>
                                        <th>Calories Burned</th>
                                        <th>Target</th>
                                        <th>% Complete</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if(!empty($calories_data['detailed_data'])):
                                        $target = $calories_data['target'];
                                        foreach(array_reverse($calories_data['detailed_data']) as $entry):
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($entry['formatted_date'] ?? $entry['date']); ?></td>
                                            <td><?= htmlspecialchars($entry['activity_type'] ?? 'General Exercise'); ?></td>
                                            <td><?= ($entry['duration_minutes'] ?? 30) . ' min'; ?></td>
                                            <td class="font-weight-bold text-danger"><?= number_format($entry['calories_burned']); ?></td>
                                            <td><?= number_format($target); ?></td>
                                            <td>
                                                <?php
                                                    $completion = $target > 0 ? ($entry['calories_burned'] / $target) * 100 : 0;
                                                    $badgeClass = $completion >= 100 ? 'badge-success' : ($completion >= 70 ? 'badge-warning' : 'badge-danger');
                                                    echo '<span class="badge ' . $badgeClass . '">' . round($completion, 1) . '%</span>';
                                                ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fa fa-chart-line fa-3x mb-3"></i>
                                                    <p>No calories burned data available. Start adding activities to see your progress!</p>
                                                    <button class="btn btn-danger" onclick="document.getElementById('caloriesDate').focus()">
                                                        <i class="fa fa-plus"></i> Add First Activity
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-success" onclick="exportCaloriesData()">
                    <i class="fa fa-download"></i> Export Data
                </button>

                <button type="button" class="btn btn-danger" onclick="initializeCaloriesChart()">
                    <i class="fa fa-refresh"></i> Refresh Chart
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.burned-stat-card {
    border-radius: 14px;
    padding: 24px 20px;
    color: #fff;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
    margin-bottom: 20px;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.burned-stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgba(0,0,0,0.15);
}
.bsc-danger { background: linear-gradient(135deg, #ff6b6b, #ee5253); }
.bsc-warning { background: linear-gradient(135deg, #feca57, #ff9f43); }
.bsc-info { background: linear-gradient(135deg, #48dbfb, #0abde3); }
.bsc-success { background: linear-gradient(135deg, #1dd1a1, #10ac84); }

.burned-stat-card .bsc-icon {
    position: absolute;
    right: -15px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 90px;
    opacity: 0.15;
    pointer-events: none;
}
.burned-stat-card h2 {
    font-size: 38px;
    font-weight: 800;
    margin: 0 0 5px 0;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.burned-stat-card p {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin: 0;
    opacity: 0.95;
}

.tooltip-custom {
    position: absolute;
    background: white;
    z-index: 1000;
    border: 2px solid #e74c3c;
    padding: 12px;
    border-radius: 6px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    min-width: 200px;
}

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 8px;
}

.card-header {
    border-radius: 8px 8px 0 0 !important;
}

.btn-outline-danger:hover {
    background-color: #e74c3c;
    color: white;
}

#caloriesChart {
    width: 100%;
    min-width: 100%;
}
</style>

<script>
// Global variables
let caloriesChart = null;
let currentCaloriesClientId = <?= $clientDetails[0]->client_id; ?>;
let caloriesTooltipElement = null;
let currentBurnedDays = 7;

// Data passed from PHP to JavaScript
const caloriesData = <?= json_encode($calories_data); ?>;

// Initialize when DOM is ready
$(document).ready(function() {
    console.log('Calories burned module initialized');
    console.log('Calories Data:', caloriesData);
    
    // Check if ApexCharts is loaded
    if (typeof ApexCharts === 'undefined') {
        console.error('ApexCharts not loaded!');
        $('#caloriesChart').html(`
            <div class="text-center p-5 text-danger">
                <i class="fa fa-exclamation-triangle fa-3x mb-3"></i>
                <p>Chart library not loaded. Please refresh the page.</p>
            </div>
        `);
    }
    
    // Initialize modal events
    $('#burnedGraphModal').on('shown.bs.modal', function(e) {
        if (e.target !== this) return;
        currentBurnedDays = 7;
        // Reset active button
        $('.burned-time-btn').removeClass('active').css({ 'background': 'transparent', 'color': '#555', 'box-shadow': 'none' });
        $('.burned-time-btn[data-days="7"]').addClass('active').css({ 'background': '#ee5253', 'color': 'white', 'box-shadow': '0 2px 4px rgba(238,82,83,0.3)' });
        setTimeout(() => loadBurnedChartData(7), 300);
    });
    
    $('#burnedGraphModal').on('hidden.bs.modal', function() {
        if (caloriesChart) {
            try {
                caloriesChart.destroy();
                caloriesChart = null;
            } catch(e) {
                console.log('Error destroying chart:', e);
            }
        }
    });
    
    // Form submission
    $('#caloriesForm').on('submit', function(e) {
        e.preventDefault();
        saveCalories();
    });
    
    // Initialize tooltip
    caloriesTooltipElement = document.getElementById('caloriesTooltip');
});

// Function to initialize the chart
function initializeCaloriesChart() {
    const chartContainer = document.getElementById('caloriesChart');
    
    if (!chartContainer) {
        console.error('Chart container not found');
        return;
    }
    
    // Clear any existing content
    chartContainer.innerHTML = '';
    
    // Check if we have data
    if (!caloriesData || !caloriesData.series || !caloriesData.series[0] || caloriesData.series[0].length === 0) {
        chartContainer.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: center; height: 100%; width: 100%;">
                <div style="text-align: center; color: #999;">
                    <i class="fa fa-chart-line" style="font-size: 48px; margin-bottom: 10px;"></i><br>
                    No calories burned data available. Add some activities to see the graph!
                </div>
            </div>
        `;
        return;
    }
    
    // Destroy existing chart
    if (caloriesChart) {
        try {
            caloriesChart.destroy();
            caloriesChart = null;
        } catch(e) {
            console.log('Error destroying chart:', e);
        }
    }
    
    // Create chart options
    const options = {
        series: [{
            name: 'Calories Burned',
            data: caloriesData.series[0]
        }],
        chart: {
            type: 'area',
            height: 350,
            toolbar: {
                show: true,
                tools: {
                    zoom: true,
                    zoomin: true,
                    zoomout: true,
                    pan: true,
                    reset: true
                }
            },
            animations: {
                enabled: true,
                speed: 800
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 3,
            colors: ['#e74c3c']
        },
        colors: ['#e74c3c'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.2,
                stops: [0, 90, 100]
            }
        },
        markers: {
            size: 6,
            colors: ['#fff'],
            strokeColors: '#e74c3c',
            strokeWidth: 2,
            hover: {
                size: 8
            }
        },
        xaxis: {
            categories: caloriesData.categories,
            labels: {
                style: {
                    colors: '#666',
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            min: 0,
            title: {
                text: 'Calories Burned',
                style: {
                    color: '#666',
                    fontSize: '14px'
                }
            },
            labels: {
                formatter: function(val) {
                    return Math.round(val);
                },
                style: {
                    colors: '#666',
                    fontSize: '12px'
                }
            }
        },
        grid: {
            borderColor: '#f1f1f1',
            strokeDashArray: 4
        },
        tooltip: {
            enabled: true,
            y: {
                formatter: function(val) {
                    return val + ' calories';
                },
                title: {
                    formatter: function() {
                        return 'Calories Burned:';
                    }
                }
            },
            marker: {
                show: true
            },
            theme: 'light'
        }
    };
    
    // Create and render chart
    try {
        caloriesChart = new ApexCharts(chartContainer, options);
        caloriesChart.render();
        console.log('Calories chart rendered successfully');
    } catch(error) {
        console.error('Calories chart error:', error);
        chartContainer.innerHTML = `
            <div style="text-align: center; color: #e74c3c; padding: 20px;">
                <i class="fa fa-exclamation-triangle"></i> Error loading chart: ${error.message}
            </div>
        `;
    }
}

// Global functions
// Save calories burned
window.saveCalories = function() {
    const form = document.getElementById('caloriesForm');
    if (!form) {
        alert('Form not found');
        return;
    }
    
    const formData = new FormData(form);
    const date = formData.get('date');
    const calories = formData.get('calories_burned');
    
    // Basic validation
    if (!date) {
        alert('Please select a date');
        return;
    }
    
    if (!calories || isNaN(calories) || calories < 0) {
        alert('Please enter a valid number of calories burned');
        return;
    }
    
    // Show loading
    const messageDiv = document.getElementById('caloriesMessage');
    if (messageDiv) {
        messageDiv.innerHTML = '<div class="alert alert-info">Saving calories burned...</div>';
    }
    
    fetch('<?= base_url("calories/saveCalories"); ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Calories save response:', data);
        if (messageDiv) {
            if (data.success) {
                messageDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                // Clear form
                document.getElementById('caloriesBurned').value = '';
                document.getElementById('durationMinutes').value = '30';
                document.getElementById('caloriesNotes').value = '';
                // Keep the date for next entry
                // Reload modal after 1.5 seconds
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                messageDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (messageDiv) {
            messageDiv.innerHTML = '<div class="alert alert-danger">Error saving calories burned</div>';
        }
    });
};

// Edit calories
window.editCalories = function(date, calories) {
    console.log('Editing calories:', date, calories);
    
    const dateInput = document.getElementById('caloriesDate');
    const caloriesInput = document.getElementById('caloriesBurned');
    
    if (dateInput && caloriesInput) {
        dateInput.value = date;
        caloriesInput.value = calories;
        caloriesInput.focus();
        caloriesInput.select();
        
        // Show message
        const messageDiv = document.getElementById('caloriesMessage');
        if (messageDiv) {
            const formattedDate = new Date(date).toLocaleDateString();
            messageDiv.innerHTML = `<div class="alert alert-info">Editing ${formattedDate} - ${calories} calories burned</div>`;
        }
        
        // Scroll to form
        const form = document.querySelector('.calories-edit-form');
        if (form) {
            form.scrollIntoView({ behavior: 'smooth' });
        }
    }
};

window.editCaloriesFromChart = function(data) {
    console.log('Chart clicked:', data);
    editCalories(data.date, data.calories_burned);
};

// Filter calories history
window.filterCalories = function() {
    const startDate = document.getElementById('caloriesStartDate');
    const endDate = document.getElementById('caloriesEndDate');
    
    if (!startDate || !endDate) {
        alert('Date inputs not found');
        return;
    }
    
    const start = startDate.value;
    const end = endDate.value;
    
    if (!start || !end) {
        alert('Please select both start and end dates');
        return;
    }
    
    if (start > end) {
        alert('Start date cannot be after end date');
        return;
    }
    
    // Show loading
    const tableBody = document.querySelector('#caloriesHistoryTable tbody');
    if (tableBody) {
        tableBody.innerHTML = '<tr><td colspan="7" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>';
    }
    
    fetch(`<?= base_url("calories/getCaloriesHistory"); ?>/${currentCaloriesClientId}?start_date=${start}&end_date=${end}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network error');
        }
        return response.json();
    })
    .then(data => {
        console.log('Calories filter response:', data);
        if (data.success && data.data) {
            updateCaloriesHistoryTable(data.data);
        } else {
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="7" class="text-center">No data found</td></tr>';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (tableBody) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading data</td></tr>';
        }
    });
};

// Reset filter
window.resetCaloriesFilter = function() {
    const startDate = document.getElementById('caloriesStartDate');
    const endDate = document.getElementById('caloriesEndDate');
    
    if (startDate && endDate) {
        // Set to last 7 days
        const today = new Date();
        const lastWeek = new Date();
        lastWeek.setDate(today.getDate() - 7);
        
        startDate.value = lastWeek.toISOString().split('T')[0];
        endDate.value = today.toISOString().split('T')[0];
        
        filterCalories();
    }
};

// Export data
window.exportCaloriesData = function() {
    const rows = document.querySelectorAll('#caloriesHistoryTable tbody tr');
    if (rows.length === 0) {
        alert('No data to export');
        return;
    }
    
    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += "Date,Activity,Duration (min),Calories Burned,Target,% Complete\n";
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const rowData = [];
        
        // Skip the last cell (actions)
        for (let i = 0; i < cells.length - 1; i++) {
            let cellText = cells[i].textContent.trim();
            // Clean up the text
            cellText = cellText.replace(/,/g, '').replace(/%/g, '');
            if (cellText.includes(',') || cellText.includes('"')) {
                cellText = `"${cellText}"`;
            }
            rowData.push(cellText);
        }
        
        csvContent += rowData.join(",") + "\n";
    });
    
    // Create download link
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `calories_burned_${currentCaloriesClientId}_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

// Load history
window.loadCaloriesHistory = function() {
    filterCalories();
};

// Quick add calories
window.quickAddCalories = function(calories, activity) {
    const date = document.getElementById('caloriesDate').value || new Date().toISOString().split('T')[0];
    
    const formData = new FormData();
    formData.append('client_id', currentCaloriesClientId);
    formData.append('date', date);
    formData.append('calories_burned', calories);
    formData.append('activity_type', activity);
    formData.append('duration_minutes', 30);
    formData.append('notes', 'Quick entry');
    
    // Show loading
    const messageDiv = document.getElementById('caloriesMessage');
    if (messageDiv) {
        messageDiv.innerHTML = '<div class="alert alert-info">Adding activity...</div>';
    }
    
    fetch('<?= base_url("calories/saveCalories"); ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Quick add response:', data);
        if (messageDiv) {
            if (data.success) {
                messageDiv.innerHTML = `<div class="alert alert-success">${activity} added successfully!</div>`;
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                messageDiv.innerHTML = `<div class="alert alert-danger">Failed to add activity</div>`;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (messageDiv) {
            messageDiv.innerHTML = '<div class="alert alert-danger">Error adding activity</div>';
        }
    });
};

// Load activity types
window.loadActivityTypes = function() {
    fetch('<?= base_url("calories/getActivityTypes"); ?>')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('activityType');
            if (select) {
                // Clear existing options except first
                while (select.options.length > 1) {
                    select.remove(1);
                }
                
                // Add new options
                data.data.forEach(activity => {
                    const option = document.createElement('option');
                    option.value = activity;
                    option.textContent = activity;
                    select.appendChild(option);
                });
                
                // Show success message
                const messageDiv = document.getElementById('caloriesMessage');
                if (messageDiv) {
                    messageDiv.innerHTML = `<div class="alert alert-success">Loaded ${data.data.length} activity types</div>`;
                }
            }
        }
    });
};

// Show calories comparison
window.showCaloriesComparison = function() {
    const date = document.getElementById('caloriesDate').value || new Date().toISOString().split('T')[0];
    
    fetch(`<?= base_url("calories/getCaloriesComparison"); ?>/${currentCaloriesClientId}?date=${date}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const comparisonContent = document.getElementById('comparisonContent');
            const comparisonData = data.data;
            
            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><i class="fa fa-utensils text-success"></i> Calories In</h4>
                            </div>
                            <div class="panel-body">
                                <h3>${comparisonData.intake.toLocaleString()}</h3>
                                <p>Target: ${comparisonData.intake_target.toLocaleString()}</p>
                                <div class="progress">
                                    <div class="progress-bar progress-bar-success" style="width: ${Math.min(comparisonData.intake_completion, 100)}%">
                                        ${comparisonData.intake_completion}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><i class="fa fa-fire text-danger"></i> Calories Out</h4>
                            </div>
                            <div class="panel-body">
                                <h3>${comparisonData.burned.toLocaleString()}</h3>
                                <p>Target: ${comparisonData.burned_target.toLocaleString()}</p>
                                <div class="progress">
                                    <div class="progress-bar progress-bar-danger" style="width: ${Math.min(comparisonData.burned_completion, 100)}%">
                                        ${comparisonData.burned_completion}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><i class="fa fa-balance-scale"></i> Net Calories</h4>
                            </div>
                            <div class="panel-body">
                                <h3 class="${comparisonData.net < 0 ? 'text-success' : 'text-danger'}">
                                    ${comparisonData.net.toLocaleString()} calories
                                </h3>
                                <p>
                                    <strong>${comparisonData.net < 0 ? 'Calorie Deficit' : 'Calorie Surplus'}</strong><br>
                                    ${Math.abs(comparisonData.net).toLocaleString()} calories ${comparisonData.net < 0 ? 'burned more than consumed' : 'consumed more than burned'}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            comparisonContent.innerHTML = html;
            $('#caloriesComparisonModal').modal('show');
        }
    });
};

// Delete calories entry
window.deleteCaloriesEntry = function(date) {
    if (!confirm('Are you sure you want to delete calories burned for ' + date + '?')) {
        return;
    }
    
    fetch(`<?= base_url("calories/deleteCaloriesByDate"); ?>`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            client_id: currentCaloriesClientId,
            date: date
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            filterCalories(); // Reload the table
            // Also reload chart
            initializeCaloriesChart();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting entry');
    });
};

// Helper function to update history table
function updateCaloriesHistoryTable(data) {
    const tableBody = document.querySelector('#caloriesHistoryTable tbody');
    if (!tableBody) return;
    
    const target = <?= $calories_data['target']; ?>;
    
    if (!data || data.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="7" class="text-center">No data found</td></tr>';
        return;
    }
    
    let html = '';
    data.forEach(entry => {
        const completion = target > 0 ? (entry.calories_burned / target) * 100 : 0;
        const date = new Date(entry.date);
        const formattedDate = date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
        const badgeClass = completion >= 100 ? 'badge-success' : (completion >= 70 ? 'badge-warning' : 'badge-danger');
        
        html += `
            <tr>
                <td>${formattedDate}</td>
                <td>${entry.activity_type || 'General Exercise'}</td>
                <td>${entry.duration_minutes || 30} min</td>
                <td class="font-weight-bold text-danger">${parseInt(entry.calories_burned).toLocaleString()}</td>
                <td>${target.toLocaleString()}</td>
                <td><span class="badge ${badgeClass}">${completion.toFixed(1)}%</span></td>
                <td>
                    <!-- View only mode, actions removed -->
                </td>
            </tr>
        `;
    });
    
    tableBody.innerHTML = html;
}

// Tooltip functions
function showCaloriesTooltip(event, data) {
    if (!caloriesTooltipElement) {
        caloriesTooltipElement = document.getElementById('caloriesTooltip');
        if (!caloriesTooltipElement) return;
    }
    
    document.getElementById('caloriesTooltipDate').textContent = data.formatted_date;
    document.getElementById('caloriesTooltipCalories').textContent = data.calories_burned.toLocaleString() + ' calories';
    
    caloriesTooltipElement.style.display = 'block';
    
    // Position tooltip near cursor
    const x = (event.clientX || event.pageX) + window.scrollX;
    const y = (event.clientY || event.pageY) + window.scrollY;
    const offset = 20;
    
    caloriesTooltipElement.style.left = (x + offset) + 'px';
    caloriesTooltipElement.style.top = (y - offset) + 'px';
}

function hideCaloriesTooltip() {
    if (caloriesTooltipElement) {
        caloriesTooltipElement.style.display = 'none';
    }
}

// Make sure functions are available globally
if (typeof window !== 'undefined') {
    console.log('Calories burned functions loaded globally');
}
window.saveCaloriesTarget = function() {
    const targetInput = document.getElementById('caloriesTarget');
    if (!targetInput) {
        alert('Target input not found');
        return;
    }
    
    const target = targetInput.value;
    const targetDate = document.getElementById('caloriesDate').value || new Date().toISOString().split('T')[0];
    
    if (!target || isNaN(target) || target < 0) {
        alert('Please enter a valid target (minimum 0)');
        targetInput.focus();
        return;
    }
    
    const formData = new FormData();
    formData.append('client_id', currentCaloriesClientId);
    formData.append('target_calories', target);
    formData.append('target_type', 'burned');
    formData.append('target_date', targetDate);
    
    const messageDiv = document.getElementById('caloriesMessage');
    if (messageDiv) {
        messageDiv.innerHTML = '<div class="alert alert-info">Saving target...</div>';
    }
    
    fetch('<?= base_url("calories/saveTarget"); ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (messageDiv) {
            if (data.success) {
                messageDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                // Update displayed target
                targetInput.value = target;
                document.getElementById('targetCalories').textContent = parseInt(target).toLocaleString();
                // Update progress calculations
                const today = <?= $calories_data['today']; ?>;
                const newCompletion = target > 0 ? round((today / target) * 100, 1) : 0;
                document.getElementById('caloriesCompletion').textContent = newCompletion + '%';
                document.getElementById('progressPercentage').textContent = newCompletion + '%';
                document.querySelector('.progress-bar').style.width = Math.min(newCompletion, 100) + '%';
                document.querySelector('.progress-bar').textContent = newCompletion + '%';
                document.querySelector('.progress-bar').setAttribute('aria-valuenow', newCompletion);
            } else {
                messageDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (messageDiv) {
            messageDiv.innerHTML = '<div class="alert alert-danger">Error saving target</div>';
        }
    });
};

// Add other functions (saveCalories, editCalories, filterCalories, etc.) as before
// Make sure they're properly defined

// Helper function
function round(value, decimals) {
    return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals);
}

// Initialize chart on page load if modal is already shown
if ($('#burnedGraphModal').hasClass('in') || $('#burnedGraphModal').hasClass('show')) {
    setTimeout(initializeCaloriesChart, 500);
}

// Time range support for burned calories chart
function changeBurnedTimeRange(days) {
    currentBurnedDays = days;
    $('.burned-time-btn').removeClass('active').css({ 'background': 'transparent', 'color': '#555', 'box-shadow': 'none' });
    $(`.burned-time-btn[data-days="${days}"]`).addClass('active').css({ 'background': '#ee5253', 'color': 'white', 'box-shadow': '0 2px 4px rgba(238,82,83,0.3)' });
    loadBurnedChartData(days);
}

function loadBurnedChartData(days) {
    const chartContainer = document.getElementById('caloriesChart');
    if (!chartContainer) return;

    chartContainer.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;"><div class="text-center" style="color:#999;"><i class="fa fa-spinner fa-spin" style="font-size:24px;margin-bottom:10px;"></i><br>Loading data...</div></div>';

    const endDate = new Date().toISOString().split('T')[0];
    const startDateObj = new Date();
    startDateObj.setDate(startDateObj.getDate() - (days - 1));
    const startDate = startDateObj.toISOString().split('T')[0];

    fetch(`<?= base_url("calories/getCaloriesHistory"); ?>/${currentCaloriesClientId}?start_date=${startDate}&end_date=${endDate}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(result => {
        if (!result.success || !result.data) {
            chartContainer.innerHTML = '<div class="text-center text-muted" style="padding:80px 0;">No data for selected period</div>';
            return;
        }

        // Aggregate by date
        const byDate = {};
        result.data.forEach(row => {
            const d = row.date.split(' ')[0];
            byDate[d] = (byDate[d] || 0) + parseInt(row.calories_burned || 0);
        });

        const series = [], categories = [];
        let cur = new Date(startDate);
        const end = new Date(endDate);
        while (cur <= end) {
            const d = cur.toISOString().split('T')[0];
            series.push(byDate[d] || 0);
            categories.push(days <= 90 ? cur.toLocaleDateString('en-US', {month:'short', day:'numeric'}) : cur.toLocaleDateString('en-US', {month:'short', year:'2-digit'}));
            cur.setDate(cur.getDate() + 1);
        }

        if (caloriesChart) { try { caloriesChart.destroy(); } catch(e) {} caloriesChart = null; }
        chartContainer.innerHTML = '';

        const options = {
            series: [{ name: 'Calories Burned', data: series }],
            chart: { type: 'area', height: 350, toolbar: { show: true }, animations: { enabled: true, speed: 600 } },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3, colors: ['#e74c3c'] },
            colors: ['#e74c3c'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.2, stops: [0, 90, 100] } },
            markers: { size: days <= 30 ? 4 : 0, colors: ['#fff'], strokeColors: '#e74c3c', strokeWidth: 2 },
            xaxis: { categories: categories, labels: { rotate: days > 30 ? -45 : 0, style: { fontSize: '11px' } } },
            yaxis: { min: 0, title: { text: 'Calories Burned' }, labels: { formatter: val => Math.round(val) } },
            grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
            tooltip: { y: { formatter: val => val + ' calories' } }
        };

        try {
            caloriesChart = new ApexCharts(chartContainer, options);
            caloriesChart.render();
        } catch(e) {
            chartContainer.innerHTML = '<div class="text-center text-danger" style="padding:50px;">Error rendering chart</div>';
        }
    })
    .catch(err => {
        console.error('Error:', err);
        chartContainer.innerHTML = '<div class="text-center text-danger" style="padding:50px;">Error loading data</div>';
    });
}
</script>