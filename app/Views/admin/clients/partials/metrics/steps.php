<!-- Steps Modal -->
<?php
// Fetch steps data if not provided by the controller
if (!isset($steps_data) || empty($steps_data)) {
    $__clientId = $clientDetails[0]->client_id ?? 0;
    if ($__clientId > 0) {
        try {
            $__db = \Config\Database::connect();
            // Today's steps
            $__r = $__db->table('steps_tracker')->select('SUM(steps) as today')
                ->where('client_id', $__clientId)->where('date', date('Y-m-d'))->get()->getRow();
            $__todaySteps = $__r ? intval($__r->today) : 0;
            // 7-day average
            $__weekly = $__db->table('steps_tracker')->select('date, SUM(steps) as daily_total')
                ->where('client_id', $__clientId)
                ->where('date >=', date('Y-m-d', strtotime('-6 days')))
                ->where('date <=', date('Y-m-d'))
                ->groupBy('date')->get()->getResult();
            $__tots = array_map(fn($r) => intval($r->daily_total), $__weekly);
            $__avgSteps = count($__tots) > 0 ? round(array_sum($__tots) / count($__tots)) : 0;
            // Target
            $__targetSteps = 10000;
            try {
                $__tr = $__db->table('steps_target')->select('target_steps')
                    ->where('client_id', $__clientId)
                    ->where('target_date <=', date('Y-m-d'))
                    ->orderBy('target_date', 'DESC')->limit(1)->get()->getRow();
                if ($__tr) $__targetSteps = intval($__tr->target_steps);
            } catch (\Exception $e) {}
            $__completion = $__targetSteps > 0 ? round(($__todaySteps / $__targetSteps) * 100, 1) : 0;
            // Last 7 days chart data
            $__series = $__cats = $__detail = [];
            for ($__i = 6; $__i >= 0; $__i--) {
                $__d = date('Y-m-d', strtotime("-$__i days"));
                $__day = $__db->table('steps_tracker')->select('SUM(steps) as total')
                    ->where('client_id', $__clientId)->where('date', $__d)->get()->getRow();
                $__daySteps = $__day ? intval($__day->total) : 0;
                $__series[] = $__daySteps;
                $__cats[]   = date('D', strtotime($__d));
                $__detail[] = ['date' => $__d, 'formatted_date' => date('M d, Y', strtotime($__d)),
                               'steps' => $__daySteps, 'calories' => round($__daySteps * 0.04), 'target' => $__targetSteps];
            }
            $steps_data = ['today' => $__todaySteps, 'average' => $__avgSteps, 'target' => $__targetSteps,
                           'completion' => $__completion, 'series' => [$__series], 'categories' => $__cats,
                           'detailed_data' => $__detail];
        } catch (\Exception $e) {
            $steps_data = ['today' => 0, 'average' => 0, 'target' => 10000, 'completion' => 0,
                           'series' => [[0,0,0,0,0,0,0]], 'categories' => ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
                           'detailed_data' => []];
        }
    } else {
        $steps_data = ['today' => 0, 'average' => 0, 'target' => 10000, 'completion' => 0,
                       'series' => [[0,0,0,0,0,0,0]], 'categories' => ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
                       'detailed_data' => []];
    }
}
?>

<div class="modal fade metric-modal steps-modal" id="stepsGraphModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #f39c12, #e67e22); color: white; border-bottom: none; padding: 20px; border-radius: 6px 6px 0 0;">
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1; text-shadow: none; margin-top: -5px;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" style="font-weight: 700; margin: 0; font-size: 18px;">
                    <i class="fa fa-walking" style="margin-right: 8px;"></i> Steps Tracking <span style="opacity: 0.8; font-weight: 400; font-size: 16px;">| <?= htmlspecialchars($clientDetails[0]->client_name ?? 'Client'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <!-- Stats Section -->
                <div class="row mb-4 mt-3">
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-info">
                            <i class="fa fa-shoe-prints bsc-icon"></i>
                            <h2 id="todaySteps"><?= number_format($steps_data['today'] ?? 0); ?></h2>
                            <p>Today's Steps</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-warning">
                            <i class="fa fa-chart-bar bsc-icon"></i>
                            <h2 id="avgSteps"><?= number_format($steps_data['average'] ?? 0); ?></h2>
                            <p>Daily Average</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-success">
                            <i class="fa fa-bullseye bsc-icon"></i>
                            <h2 id="targetSteps"><?= number_format($steps_data['target'] ?? 10000); ?></h2>
                            <p>Daily Target</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-danger">
                            <i class="fa fa-check-circle bsc-icon"></i>
                            <h2 id="stepsCompletion"><?= isset($steps_data['completion']) ? $steps_data['completion'] . '%' : '0%'; ?></h2>
                            <p>Completion Rate</p>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mb-4" style="background: #f8f9fa; padding: 20px; border-radius: 12px; box-shadow: inset 0 2px 5px rgba(0,0,0,0.03); margin-bottom: 25px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-weight: 700; color: #2d3436;">
                        <span><i class="fa fa-walking" style="color: #f39c12; margin-right: 6px;"></i> Progress to Target</span>
                        <span id="stepsProgressText" style="color: #f39c12;"><?= isset($steps_data['completion']) ? $steps_data['completion'] . '%' : '0%'; ?></span>
                    </div>
                    <div class="progress" style="height: 14px; border-radius: 10px; background-color: #dfe6e9; box-shadow: none; margin-bottom: 10px;">
                        <div class="progress-bar progress-bar-striped active" role="progressbar" id="stepsProgressBar"
                             style="width: <?= isset($steps_data['completion']) ? min($steps_data['completion'], 100) : 0; ?>%; background: linear-gradient(90deg, #f39c12, #e67e22); border-radius: 10px;" 
                             aria-valuenow="<?= $steps_data['completion'] ?? 0; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; color: #636e72;">
                        <span>Start: 0</span>
                        <span id="stepsProgressCurrentText">Current: <?= number_format($steps_data['today'] ?? 0); ?></span>
                        <span id="stepsProgressTargetText">Target: <?= number_format($steps_data['target'] ?? 10000); ?></span>
                    </div>
                </div>

                <!-- Time Range Selector -->
                <div class="time-range-selector text-center" style="margin-bottom: 25px;">
                    <div class="btn-group" style="background: #f8f9fa; border-radius: 4px; padding: 4px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.05); display: inline-block;">
                        <button type="button" class="btn btn-sm time-range-btn steps-time-btn active" data-days="7" onclick="changeStepsTimeRange(7)" style="border: none; background: #f39c12; color: white; font-weight: 500; padding: 6px 15px; border-radius: 3px; box-shadow: 0 2px 4px rgba(243, 156, 18, 0.3);">7 Days</button>
                        <button type="button" class="btn btn-sm time-range-btn steps-time-btn" data-days="30" onclick="changeStepsTimeRange(30)" style="border: none; background: transparent; color: #555; font-weight: 500; padding: 6px 15px; border-radius: 3px;">30 Days</button>
                        <button type="button" class="btn btn-sm time-range-btn steps-time-btn" data-days="90" onclick="changeStepsTimeRange(90)" style="border: none; background: transparent; color: #555; font-weight: 500; padding: 6px 15px; border-radius: 3px;">90 Days</button>
                        <button type="button" class="btn btn-sm time-range-btn steps-time-btn" data-days="365" onclick="changeStepsTimeRange(365)" style="border: none; background: transparent; color: #555; font-weight: 500; padding: 6px 15px; border-radius: 3px;">1 Year</button>
                    </div>
                </div>

                <!-- Graph Section -->
                <div class="metric-graph-container" style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: relative;">
                    <div id="stepsChart" style="height: 300px; min-height: 300px;"></div>
                    <div id="stepsTooltip" style="display: none; position: absolute; background: white; z-index: 1000; border: 1px solid #ddd; padding: 10px; border-radius: 4px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <strong id="tooltipDate"></strong><br>
                        Steps: <span id="tooltipSteps"></span><br>
                        Calories: <span id="tooltipCalories"></span>
                    </div>
                </div>
                
                <!-- View Only Steps History -->
                <div class="steps-history" style="margin-top: 20px;">
                    <h5 style="color: #f39c12; margin-bottom: 15px; font-weight: bold;">
                        <i class="fa fa-history"></i> Recent Steps History
                    </h5>
                    <div class="table-responsive" style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <table class="table table-hover" id="stepsHistoryTable" style="margin-bottom: 0;">
                            <thead style="background: #f8f9fa;">
                                <tr>
                                    <th style="border-bottom: none;">Date</th>
                                    <th style="border-bottom: none;">Steps</th>
                                    <th style="border-bottom: none;">Calories Burned</th>
                                    <th style="border-bottom: none;">Target</th>
                                    <th style="border-bottom: none;">% Complete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if(isset($steps_data['detailed_data']) && !empty($steps_data['detailed_data'])): 
                                    $target = $steps_data['target'] ?? 10000;
                                    foreach(array_reverse($steps_data['detailed_data']) as $entry): 
                                ?>
                                    <tr>
                                        <td><?= $entry['formatted_date']; ?></td>
                                        <td style="font-weight: bold; color: #f39c12;"><?= number_format($entry['steps']); ?></td>
                                        <td><?= number_format($entry['calories']); ?> kcal</td>
                                        <td><?= number_format($target); ?></td>
                                        <td>
                                            <?php 
                                                $completion = $target > 0 ? ($entry['steps'] / $target) * 100 : 0;
                                                $badgeClass = $completion >= 100 ? 'badge-success' : ($completion >= 70 ? 'badge-warning' : 'badge-danger');
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= round($completion, 1) ?>%</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted" style="padding: 20px;">No steps data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="exportStepsData()">
                    <i class="fa fa-download"></i> Export Data
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let stepsChart = null;
let currentStepsDays = 7;
currentClientId = <?= $clientDetails[0]->client_id; ?>;

// Initialize when DOM is ready
$(document).ready(function() {
    console.log('Steps module initialized for client:', currentClientId);
    
    // Initialize modal
    $('#stepsGraphModal').on('shown.bs.modal', function(e) {
        if (e.target !== this) return;
        console.log('Modal shown, initializing chart...');
        setTimeout(initializeStepsChart, 300);
    });
    
    // Clean up when modal closes
    $('#stepsGraphModal').on('hidden.bs.modal', function() {
        console.log('Modal closed, cleaning up...');
        if (stepsChart) {
            try {
                stepsChart.destroy();
                stepsChart = null;
            } catch(e) {
                console.log('Error destroying chart:', e);
            }
        }
    });
    
    // Form submission
    $('#stepsForm').on('submit', function(e) {
        e.preventDefault();
        saveSteps();
    });
});

function changeStepsTimeRange(days) {
    // Update active button UI
    $('.steps-time-btn').removeClass('active').css({
        'background': 'transparent',
        'color': '#555',
        'box-shadow': 'none'
    });
    $(`.steps-time-btn[data-days="${days}"]`).addClass('active').css({
        'background': '#f39c12',
        'color': 'white',
        'box-shadow': '0 2px 4px rgba(243, 156, 18, 0.3)'
    });
    
    currentStepsDays = days;
    loadChartData(days);
}

// Function to initialize the chart
function initializeStepsChart() {
    console.log('Initializing chart for client:', currentClientId);
    
    // First, load fresh data from server
    loadChartData();
}

// Load chart data from server
function loadChartData(days) {
    days = days || currentStepsDays || 7;
    console.log('Loading chart data for client:', currentClientId, 'days:', days);
    
    const chartContainer = document.querySelector("#stepsChart");
    if (!chartContainer) {
        console.error('Chart container not found');
        return;
    }
    
    // Show loading
    chartContainer.innerHTML = 
        '<div style="display: flex; align-items: center; justify-content: center; height: 100%; width: 100%;">' +
        '<div style="text-align: center; color: #999;">' +
        '<i class="fa fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i><br>' +
        'Loading steps data...' +
        '</div></div>';
    
    const endDate = new Date().toISOString().split('T')[0];
    const startDateObj = new Date();
    startDateObj.setDate(startDateObj.getDate() - (days - 1));
    const startDate = startDateObj.toISOString().split('T')[0];

    fetch(`<?= base_url("steps/getStepsChartData"); ?>/${currentClientId}?start_date=${startDate}&end_date=${endDate}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(result => {
        console.log('Chart data response:', result);
        
        // Validate response structure
        if (!result.success) {
            throw new Error(result.message || 'Failed to load chart data');
        }
        
        if (!result.data) {
            throw new Error('No data returned from server');
        }
        
        // Ensure data has required structure
        const data = result.data;
        if (!Array.isArray(data.detailed_data)) {
            data.detailed_data = [];
        }
        if (!Array.isArray(data.series)) {
            data.series = [[]];
        }
        if (!Array.isArray(data.categories)) {
            data.categories = [];
        }
        chartContainer.innerHTML='';
        renderChart(data);
        updateStats(data);
        updateHistoryTable(data.detailed_data);
    })
    .catch(error => {
        console.error('Error loading chart data:', error);
        chartContainer.innerHTML = 
            '<div style="text-align: center; color: #e74c3c; padding: 20px;">' +
            '<i class="fa fa-exclamation-triangle"></i> Error loading chart data<br>' +
            '<small>Error: ' + error.message + '</small><br>' +
            '</div>';
    });
}

// Render the chart
function renderChart(chartData) {
    const chartContainer = document.querySelector("#stepsChart");
    if (!chartContainer) return;
    
    // Check if we have valid data
    if (!chartData || !Array.isArray(chartData.series) || !chartData.series[0] || 
        !Array.isArray(chartData.detailed_data)) {
        console.error('Invalid chart data structure:', chartData);
        chartContainer.innerHTML = 
            '<div style="text-align: center; color: #e74c3c; padding: 20px;">' +
            '<i class="fa fa-exclamation-triangle"></i> Invalid chart data format<br>' +
            '</div>';
        return;
    }
    
    // Destroy existing chart
    if (stepsChart) {
        try {
            stepsChart.destroy();
        } catch(e) {
            console.log('Error destroying chart:', e);
        }
    }
    
    // Ensure series data is valid
    const seriesData = chartData.series[0].map(value => {
        return typeof value === 'number' ? value : 0;
    });
    
    const options = {
        series: [{
            name: 'Steps',
            data: seriesData
        }],
        chart: {
            type: 'line',
            height: 300,
            toolbar: {
                show: true
            },
            events: {
                dataPointMouseEnter: function(event, chartContext, config) {
                    if (chartData.detailed_data && chartData.detailed_data[config.dataPointIndex]) {
                        showTooltip(event, chartData.detailed_data[config.dataPointIndex]);
                    }
                },
                dataPointMouseLeave: hideTooltip,
                click: function(event, chartContext, config) {
                    // Action removed
                }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function(val) {
                return val > 0 ? val.toLocaleString() : '';
            }
        },
        stroke: {
            curve: 'smooth',
            width: 3,
            colors: ['#f39c12']
        },
        colors: ['#f39c12'],
        markers: {
            size: 6,
            colors: ['#f39c12'],
            strokeColors: '#fff',
            strokeWidth: 2,
            hover: {
                size: 8
            }
        },
        xaxis: {
            categories: chartData.categories || []
        },
        yaxis: {
            min: 0,
            labels: {
                formatter: function(val) {
                    return typeof val === 'number' ? val.toLocaleString() : '0';
                }
            }
        },
        tooltip: {
            enabled: true,
            custom: function({ series, seriesIndex, dataPointIndex, w }) {
                if (chartData.detailed_data && chartData.detailed_data[dataPointIndex]) {
                    const data = chartData.detailed_data[dataPointIndex];
                    // Use safe access
                    const steps = data.steps || 0;
                    const calories = data.calories || 0;
                    const date = data.formatted_date || data.date || 'Unknown date';
                    
                    return `
                        <div class="custom-tooltip" style="padding: 5px; background: white; border: 1px solid #ddd; border-radius: 3px;">
                            <strong>${date}</strong><br>
                            Steps: <strong>${steps.toLocaleString()}</strong><br>
                            Calories: <strong>${calories.toLocaleString()} kcal</strong>
                        </div>
                    `;
                }
                return '';
            }
        },
        grid: {
            borderColor: '#f1f1f1',
        }
    };
    
    try {
        stepsChart = new ApexCharts(chartContainer, options);
        stepsChart.render();
        console.log('Chart rendered successfully');
    } catch(error) {
        console.error('Chart rendering error:', error);
        chartContainer.innerHTML = 
            '<div style="text-align: center; color: #e74c3c; padding: 20px;">' +
            '<i class="fa fa-exclamation-triangle"></i> Error rendering chart<br>' +
            '<small>' + error.message + '</small>' +
            '</div>';
    }
}

// Update stats display
function updateStats(chartData) {
    if (!chartData || !chartData.detailed_data) return;
    
    const today = new Date().toISOString().split('T')[0];
    const todayData = chartData.detailed_data.find(data => data.date === today);
    const todaySteps = todayData ? (todayData.steps || 0) : 0;
    
    const last7Days = chartData.detailed_data.slice(-7);
    const sum = last7Days.reduce((total, day) => total + (day.steps || 0), 0);
    const average = last7Days.length > 0 ? Math.round(sum / last7Days.length) : 0;
    
    const target = (last7Days.length > 0 && last7Days[0].target) ? last7Days[0].target : 10000;
    const completion = target > 0 ? Math.round((todaySteps / target) * 100) : 0;
    
    const todayStepsElement = document.getElementById('todaySteps');
    const avgStepsElement = document.getElementById('avgSteps');
    const targetStepsElement = document.getElementById('targetSteps');
    const stepsCompletionElement = document.getElementById('stepsCompletion');
    
    if (todayStepsElement) todayStepsElement.textContent = todaySteps.toLocaleString();
    if (avgStepsElement) avgStepsElement.textContent = average.toLocaleString();
    if (targetStepsElement) targetStepsElement.textContent = target.toLocaleString();
    if (stepsCompletionElement) stepsCompletionElement.textContent = completion + '%';
    
    // Update progress bar
    const progressText = document.getElementById('stepsProgressText');
    const progressBar = document.getElementById('stepsProgressBar');
    const currentText = document.getElementById('stepsProgressCurrentText');
    const targetText = document.getElementById('stepsProgressTargetText');
    
    if (progressText) progressText.textContent = completion + '%';
    if (progressBar) progressBar.style.width = Math.min(completion, 100) + '%';
    if (currentText) currentText.textContent = 'Current: ' + todaySteps.toLocaleString();
    if (targetText) targetText.textContent = 'Target: ' + target.toLocaleString();
}

// Save steps function
window.saveSteps = function() {
    console.log('Saving steps...');
    
    const form = document.getElementById('stepsForm');
    if (!form) {
        alert('Form not found');
        return;
    }
    
    const formData = new FormData(form);
    const date = formData.get('date');
    const steps = formData.get('steps');
    
    // Basic validation
    if (!date) {
        alert('Please select a date');
        return;
    }
    
    if (steps === '' || isNaN(steps) || steps < 0) {
        alert('Please enter a valid number of steps (minimum 0)');
        return;
    }
    
    // Show loading
    const messageDiv = document.getElementById('stepsMessage');
    if (messageDiv) {
        messageDiv.innerHTML = '<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> Saving steps...</div>';
    }
    
    // Add source parameter
    formData.append('source', 'form');
    
    fetch('<?= base_url("steps/saveSteps"); ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Save response:', data);
        if (messageDiv) {
            if (data.success) {
                messageDiv.innerHTML = `<div class="alert alert-success"><i class="fa fa-check"></i> ${data.message}</div>`;
                
                // Clear only steps input
                document.getElementById('stepsCount').value = '';
                
                // Reload chart data
                setTimeout(() => {
                    loadChartData();
                }, 1000);
                
                // Clear success message after 3 seconds
                setTimeout(() => {
                    messageDiv.innerHTML = '';
                }, 3000);
            } else {
                let errorMsg = data.message || 'Failed to save steps';
                if (data.errors) {
                    errorMsg += '<br>' + Object.values(data.errors).join('<br>');
                }
                messageDiv.innerHTML = `<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> ${errorMsg}</div>`;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (messageDiv) {
            messageDiv.innerHTML = '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Error saving steps: ' + error.message + '</div>';
        }
    });
}

// Save target function
window.saveTarget = function() {
    console.log('Saving target...');
    
    const targetInput = document.getElementById('stepsTarget');
    const dateInput = document.getElementById('stepsDate');
    
    if (!targetInput) {
        alert('Target input not found');
        return;
    }
    
    const target = targetInput.value;
    const targetDate = dateInput ? dateInput.value : new Date().toISOString().split('T')[0];
    
    if (!target || isNaN(target) || target < 0) {
        alert('Please enter a valid target (minimum 0)');
        targetInput.focus();
        return;
    }
    
    if (!confirm(`Set daily target to ${target} steps for ${targetDate}?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('client_id', currentClientId);
    formData.append('target_steps', target);
    formData.append('target_date', targetDate);
    
    // Show loading
    const messageDiv = document.getElementById('stepsMessage');
    if (messageDiv) {
        messageDiv.innerHTML = '<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> Saving target...</div>';
    }
    
    fetch('<?= base_url("steps/saveTarget"); ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Target response:', data);
        if (messageDiv) {
            if (data.success) {
                messageDiv.innerHTML = `<div class="alert alert-success"><i class="fa fa-check"></i> ${data.message}</div>`;
                
                // Reload chart data to update targets
                setTimeout(() => {
                    loadChartData();
                }, 1000);
                
                // Clear success message after 3 seconds
                setTimeout(() => {
                    messageDiv.innerHTML = '';
                }, 3000);
            } else {
                let errorMsg = data.message || 'Failed to save target';
                if (data.errors) {
                    errorMsg += '<br>' + Object.values(data.errors).join('<br>');
                }
                messageDiv.innerHTML = `<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> ${errorMsg}</div>`;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (messageDiv) {
            messageDiv.innerHTML = '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Error saving target: ' + error.message + '</div>';
        }
    });
}

// Edit steps from history
window.editSteps = function(date, steps) {
    console.log('Editing:', date, steps);
    
    const dateInput = document.getElementById('stepsDate');
    const stepsInput = document.getElementById('stepsCount');
    
    if (dateInput && stepsInput) {
        dateInput.value = date;
        stepsInput.value = steps;
        stepsInput.focus();
        stepsInput.select();
        
        // Show message
        const messageDiv = document.getElementById('stepsMessage');
        if (messageDiv) {
            const formattedDate = new Date(date).toLocaleDateString();
            messageDiv.innerHTML = `<div class="alert alert-info"><i class="fa fa-edit"></i> Editing ${formattedDate} - ${steps} steps</div>`;
        }
        
        // Scroll to form
        const form = document.querySelector('.steps-edit-form');
        if (form) {
            form.scrollIntoView({ behavior: 'smooth' });
        }
    }
}

// Edit from chart click
window.editStepsFromChart = function(data) {
    console.log('Chart clicked:', data);
    editSteps(data.date, data.steps);
}

// Filter steps by date range
window.filterSteps = function() {
    console.log('Filtering steps...');
    
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    
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
    const tableBody = document.querySelector('#stepsHistoryTable tbody');
    if (tableBody) {
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading history...</td></tr>';
    }
    
    fetch(`<?= base_url("steps/getStepsHistory"); ?>/${currentClientId}?start_date=${start}&end_date=${end}`)
    .then(response => {
        if (!response.ok) {
            throw new Error('Network error');
        }
        return response.json();
    })
    .then(data => {
        console.log('Filter response:', data);
        if (data.success && data.data) {
            updateHistoryTable(data.data);
        } else {
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center">No data found</td></tr>';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (tableBody) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading data: ' + error.message + '</td></tr>';
        }
    });
}

// Reset filter
window.resetFilter = function() {
    console.log('Resetting filter...');
    
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    
    if (startDate && endDate) {
        // Set to last 7 days
        const today = new Date();
        const lastWeek = new Date();
        lastWeek.setDate(today.getDate() - 6); // 7 days including today
        
        startDate.value = lastWeek.toISOString().split('T')[0];
        endDate.value = today.toISOString().split('T')[0];
        
        // Reload chart with default range
        loadChartData();
    }
}

// Update history table
function updateHistoryTable(data) {
    const tableBody = document.querySelector('#stepsHistoryTable tbody');
    if (!tableBody) return;
    
    if (!data || !Array.isArray(data) || data.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center">No steps data found for selected period</td></tr>';
        return;
    }
    
    let html = '';
    data.forEach(entry => {
        // Use safe access with defaults
        const entryDate = entry.date || '';
        const entrySteps = entry.steps || 0;
        const entryTarget = entry.target || 10000;
        
        let formattedDate = 'Invalid date';
        try {
            const date = new Date(entryDate);
            if (!isNaN(date.getTime())) {
                formattedDate = date.toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
            }
        } catch (e) {
            console.error('Error formatting date:', e);
        }
        
        // Calculate safely
        const calories = Math.round(entrySteps * 0.04);
        const completion = entryTarget > 0 ? (entrySteps / entryTarget) * 100 : 0;
        
        html += `
            <tr>
                <td>${formattedDate}</td>
                <td style="font-weight: bold; color: #f39c12;">${entrySteps.toLocaleString()}</td>
                <td>${calories.toLocaleString()} kcal</td>
                <td>${entryTarget.toLocaleString()}</td>
                <td>
                    <span class="badge ${completion >= 100 ? 'badge-success' : (completion >= 70 ? 'badge-warning' : 'badge-danger')}">${completion.toFixed(1)}%</span>
                </td>
            </tr>
        `;
    });
    
    tableBody.innerHTML = html;
}

// Tooltip functions
function showTooltip(event, data) {
    const tooltip = document.getElementById('stepsTooltip');
    if (!tooltip) return;
    
    document.getElementById('tooltipDate').textContent = data.formatted_date;
    document.getElementById('tooltipSteps').textContent = data.steps.toLocaleString() + ' steps';
    document.getElementById('tooltipCalories').textContent = data.calories.toLocaleString() + ' kcal';
    
    tooltip.style.display = 'block';
    
    // Position tooltip near mouse
    const x = event.clientX || event.pageX;
    const y = event.clientY || event.pageY;
    const offset = 20;
    
    tooltip.style.left = (x + offset) + 'px';
    tooltip.style.top = (y - offset) + 'px';
}

function hideTooltip() {
    const tooltip = document.getElementById('stepsTooltip');
    if (tooltip) {
        tooltip.style.display = 'none';
    }
}

// Load history on button click
window.loadHistory = function() {
    console.log('Loading full history...');
    resetFilter(); // This will load the default 7-day range
}
</script>