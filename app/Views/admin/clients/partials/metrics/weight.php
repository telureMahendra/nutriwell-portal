<?php
// Get client_id from available sources
if (!isset($client_id)) {
    if (isset($clientDetails[0]->client_id)) {
        $client_id = $clientDetails[0]->client_id;
    } elseif (isset($_GET['client_id'])) {
        $client_id = $_GET['client_id'];
    } else {
        $client_id = 0; // Default value
    }
}

// Get weight data
$weightModel = new \App\Models\Weight_model();
$weight_data = $weightModel->getWeightStats($client_id);
$weekly_change = $weightModel->getWeeklyChange($client_id);
$monthly_change = $weightModel->getMonthlyChange($client_id);
$recent_logs = $weightModel->getRecentLogs($client_id, 5);
?>

<div class="modal fade metric-modal weight-modal" id="weightGraphModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #27ae60, #2ecc71); color: white; border-bottom: none; padding: 20px; border-radius: 6px 6px 0 0;">
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1; text-shadow: none; margin-top: -5px;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" style="font-weight: 700; margin: 0; font-size: 18px;">
                    <i class="fa fa-weight" style="margin-right: 8px;"></i> Weight Tracking <span style="opacity: 0.8; font-weight: 400; font-size: 16px;">| <?= htmlspecialchars($clientDetails[0]->client_name ?? 'Client'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row mb-4 mt-3">
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-success">
                            <i class="fa fa-weight bsc-icon"></i>
                            <h2 id="currentWeight"><?= number_format($weight_data['current'], 1); ?> <small style="font-size: 18px; color: rgba(255,255,255,0.8);">kg</small></h2>
                            <p>Current Weight</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-info">
                            <i class="fa fa-hourglass-start bsc-icon"></i>
                            <h2 id="startWeight"><?= number_format($weight_data['start'], 1); ?> <small style="font-size: 18px; color: rgba(255,255,255,0.8);">kg</small></h2>
                            <p>Start Weight</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-warning">
                            <i class="fa fa-bullseye bsc-icon"></i>
                            <h2 id="targetWeight"><?= number_format($weight_data['target'], 1); ?> <small style="font-size: 18px; color: rgba(255,255,255,0.8);">kg</small></h2>
                            <p>Target Weight</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="burned-stat-card <?= $weight_data['change'] > 0 ? 'bsc-danger' : ($weight_data['change'] < 0 ? 'bsc-success' : 'bsc-info'); ?>">
                            <i class="fa fa-exchange-alt bsc-icon"></i>
                            <h2 id="weightChange"><?= $weight_data['change'] > 0 ? '+' : '' ?><?= number_format($weight_data['change'], 1); ?> <small style="font-size: 18px; color: rgba(255,255,255,0.8);">kg</small></h2>
                            <p>Total Change</p>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <?php if ($weight_data['target'] > 0 && $weight_data['start'] > 0): ?>
                <div class="mb-4" style="background: #f8f9fa; padding: 20px; border-radius: 12px; box-shadow: inset 0 2px 5px rgba(0,0,0,0.03); margin-bottom: 25px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-weight: 700; color: #2d3436;">
                        <span><i class="fa fa-tasks" style="color: #27ae60; margin-right: 6px;"></i> Progress to Target</span>
                        <span style="color: #27ae60;"><?= number_format($weight_data['progress'], 1); ?>%</span>
                    </div>
                    <div class="progress" style="height: 14px; border-radius: 10px; background-color: #dfe6e9; box-shadow: none; margin-bottom: 10px;">
                        <div class="progress-bar progress-bar-striped active" role="progressbar" 
                             style="width: <?= min(100, $weight_data['progress']); ?>%; background: linear-gradient(90deg, #2ecc71, #27ae60); border-radius: 10px;" 
                             aria-valuenow="<?= $weight_data['progress']; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; color: #636e72;">
                        <span>Start: <?= number_format($weight_data['start'], 1); ?>kg</span>
                        <span>Current: <?= number_format($weight_data['current'], 1); ?>kg</span>
                        <span>Target: <?= number_format($weight_data['target'], 1); ?>kg</span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Time Range Selector -->
                <div class="time-range-selector text-center" style="margin-bottom: 25px;">
                    <div class="btn-group" style="background: #f8f9fa; border-radius: 4px; padding: 4px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.05); display: inline-block;">
                        <button type="button" class="btn btn-sm weight-time-btn" data-days="7" onclick="changeWeightTimeRange(7)">7 Days</button>
                        <button type="button" class="btn btn-sm weight-time-btn wt-active" data-days="30" onclick="changeWeightTimeRange(30)">30 Days</button>
                        <button type="button" class="btn btn-sm weight-time-btn" data-days="90" onclick="changeWeightTimeRange(90)">90 Days</button>
                        <button type="button" class="btn btn-sm weight-time-btn" data-days="365" onclick="changeWeightTimeRange(365)">1 Year</button>
                    </div>
                </div>
                
                <div class="metric-graph-container">
                    <div id="weightChart" style="height: 300px;"></div>
                </div>

                <div class="weight-trend" style="margin-top: 20px;">
                    <h5 style="color: #27ae60; margin-bottom: 15px;">
                        <i class="fa fa-chart-line"></i> Weight Trends
                    </h5>
                    <div class="trend-stats">
                        <div class="trend-item" style="display: flex; justify-content: space-between; padding: 10px; background: #f8f9fa; border-radius: 5px; margin-bottom: 10px;">
                            <span><i class="fa fa-calendar-week"></i> Weekly Change</span>
                            <span id="weeklyChange" style="color: <?= $weekly_change['value'] > 0 ? '#e74c3c' : ($weekly_change['value'] < 0 ? '#27ae60' : '#f39c12'); ?>; font-weight: bold;">
                                <i class="fa <?= $weekly_change['icon']; ?>"></i>
                                <?= $weekly_change['value'] > 0 ? '+' : '' ?><?= number_format($weekly_change['value'], 1); ?> kg
                            </span>
                        </div>
                        <div class="trend-item" style="display: flex; justify-content: space-between; padding: 10px; background: #f8f9fa; border-radius: 5px; margin-bottom: 10px;">
                            <span><i class="fa fa-calendar-alt"></i> Monthly Change</span>
                            <span id="monthlyChange" style="color: <?= $monthly_change['value'] > 0 ? '#e74c3c' : ($monthly_change['value'] < 0 ? '#27ae60' : '#f39c12'); ?>; font-weight: bold;">
                                <i class="fa <?= $monthly_change['icon']; ?>"></i>
                                <?= $monthly_change['value'] > 0 ? '+' : '' ?><?= number_format($monthly_change['value'], 1); ?> kg
                            </span>
                        </div>
                    </div>
                </div>

                <div class="weight-log" style="margin-top: 20px;">
                    <h5 style="color: #27ae60; margin-bottom: 15px;">
                        <i class="fa fa-history"></i> Recent Weigh-ins
                    </h5>
                    <div class="weight-list" id="recentWeighins">
                        <?php if (!empty($recent_logs)): ?>
                            <?php foreach ($recent_logs as $log): ?>
                                <div class="weight-item" data-weight-id="<?= $log->weight_id; ?>" 
                                     style="display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px solid #eee;">
                                    <div>
                                        <strong><?= date('M d, Y', strtotime($log->date)); ?></strong>
                                        <div style="font-size: 12px; color: #666;"><?= $log->time; ?></div>
                                        <?php if (!empty($log->notes)): ?>
                                            <div style="font-size: 11px; color: #888; margin-top: 3px;">
                                                <i class="fa fa-sticky-note"></i> <?= substr($log->notes, 0, 30); ?>...
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div style="display: flex; align-items: center;">
                                        <span style="color: #27ae60; font-weight: bold;">
                                            <?= number_format($log->weight, 1); ?> kg
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-data" style="text-align: center; padding: 20px; color: #777;">
                                <i class="fa fa-weight fa-3x" style="margin-bottom: 10px;"></i>
                                <p>No weight entries found. Add your first weight entry!</p>
                                <button class="btn btn-primary btn-sm" onclick="showAddWeightForm()">
                                    <i class="fa fa-plus"></i> Add First Entry
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <!-- Log Weight removed for admin view -->
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Weight Form Modal -->
<div class="modal fade" id="weightFormModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="weightFormTitle">
                    <i class="fa fa-weight"></i> Log Weight
                </h4>
            </div>
            <form id="weightForm">
                <div class="modal-body">
                    <input type="hidden" name="weight_id" id="weightId">
                    <input type="hidden" name="client_id" id="clientId" value="<?= $client_id; ?>">
                    
                    <div class="form-group">
                        <label for="weight">Weight (kg) *</label>
                        <input type="number" class="form-control" id="weight" name="weight" 
                               step="0.1" min="0" max="300" required 
                               placeholder="Enter weight in kilograms">
                    </div>
                    
                    <div class="form-group">
                        <label for="target_weight">Target Weight (kg)</label>
                        <input type="number" class="form-control" id="target_weight" name="target_weight" 
                               step="0.1" min="0" max="300"
                               placeholder="Leave empty to use current target">
                    </div>
                    
                    <div class="form-group">
                        <label for="date">Date & Time *</label>
                        <input type="datetime-local" class="form-control" id="date" name="date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Add any notes about this weigh-in..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="deleteBtn" style="display: none;" 
                            onclick="deleteWeightEntry()">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="fa fa-save"></i> Save Weight
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Removed redundant ApexCharts load -->
<script>
// Global variables
let weightChart = null;
let currentClientId = <?= $client_id; ?>;
let currentDaysRange = 30;
let weightDataPoints = [];

// Base URL - FIXED: using proper PHP echo
const BASE_URL = '<?= base_url(); ?>';

// Check if we have a valid client ID
if (currentClientId <= 0) {
    console.error('Invalid client ID:', currentClientId);
    // Try to get client ID from URL or other sources
    const urlParams = new URLSearchParams(window.location.search);
    const urlClientId = urlParams.get('client_id');
    if (urlClientId) {
        currentClientId = urlClientId;
        document.getElementById('clientId').value = urlClientId;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    $('#weightGraphModal').on('shown.bs.modal', function () {
        if (currentClientId > 0) {
            loadWeightData(currentDaysRange);
        } else {
            console.error('Cannot load weight data: No client ID available');
            // Show error in chart container
            document.getElementById('weightChart').innerHTML = 
                '<div style="text-align: center; padding: 50px; color: #e74c3c;">' +
                '<i class="fa fa-exclamation-triangle fa-3x" style="margin-bottom: 20px;"></i>' +
                '<p>Client ID not found. Please refresh the page.</p>' +
                '</div>';
        }
    });
    
    // Weight form submission
    $('#weightForm').submit(function(e) {
        e.preventDefault();
        saveWeightEntry();
    });
    
    // Set default date to now
    document.getElementById('date').value = new Date().toISOString().slice(0, 16);
});

// Load weight data
function loadWeightData(days) {
    if (currentClientId <= 0) {
        console.error('Cannot load weight data: Invalid client ID');
        return;
    }
    
    currentDaysRange = days;

    // Update active button
    $('.weight-time-btn').removeClass('wt-active');
    $(`.weight-time-btn[data-days="${days}"]`).addClass('wt-active');

    // Fetch graph data
    fetch(`${BASE_URL}weight/graph-data/${currentClientId}/${days}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.series && data.series.length > 0) {
                weightDataPoints = data.dataPoints || [];
                initializeWeightChart(data);
            } else {
                // No data available
                if (chartElement) {
                    chartElement.innerHTML = 
                        '<div style="text-align: center; padding: 50px; color: #777;">' +
                        '<i class="fa fa-chart-line fa-3x" style="margin-bottom: 20px;"></i>' +
                        '<p>No weight data available for this period.</p>' +
                        '<p><button class="btn btn-primary btn-sm" onclick="showAddWeightForm()">Add First Weight Entry</button></p>' +
                        '</div>';
                }
            }
        })
        .catch(error => {
            console.error('Error loading weight data:', error);
            if (chartElement) {
                chartElement.innerHTML = 
                    '<div style="text-align: center; padding: 50px; color: #e74c3c;">' +
                    '<i class="fa fa-exclamation-triangle fa-3x" style="margin-bottom: 20px;"></i>' +
                    '<p>Error loading weight data. Please try again.</p>' +
                    '</div>';
            }
        });
}

// Initialize weight chart
function initializeWeightChart(data) {
    const chartElement = document.getElementById("weightChart");
    if (!chartElement) return;
    
    const options = {
        series: [
            {
                name: 'Weight',
                data: data.series,
                type: 'line',
                color: '#27ae60'
            },
            {
                name: 'Target Weight',
                data: data.targetSeries,
                type: 'line',
                color: '#e74c3c',
                strokeDashArray: 5
            }
        ],
        chart: {
            type: 'line',
            height: 300,
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: true,
                    zoom: true,
                    zoomin: true,
                    zoomout: true,
                    pan: true,
                    reset: true
                }
            },
            events: {
                // Actions removed
            }
        },
        stroke: {
            curve: 'smooth',
            width: [3, 2]
        },
        markers: {
            size: 5,
            hover: {
                size: 7
            }
        },
        xaxis: {
            categories: data.categories,
            labels: {
                style: {
                    colors: '#666',
                    fontSize: '11px'
                }
            }
        },
        yaxis: {
            title: {
                text: 'Weight (kg)',
                style: {
                    color: '#666',
                    fontSize: '12px'
                }
            },
            labels: {
                formatter: function(value) {
                    return value.toFixed(1) + ' kg';
                },
                style: {
                    colors: '#666',
                    fontSize: '11px'
                }
            }
        },
        tooltip: {
            shared: true,
            intersect: false,
            x: {
                show: true,
                formatter: function(value, { series, seriesIndex, dataPointIndex, w }) {
                    if (data.tooltipDates && data.tooltipDates[dataPointIndex]) {
                        return data.tooltipDates[dataPointIndex];
                    }
                    return value;
                }
            },
            y: {
                formatter: function(value) {
                    return value.toFixed(1) + ' kg';
                }
            },
            custom: function({ series, seriesIndex, dataPointIndex, w }) {
                const weight = weightDataPoints[dataPointIndex];
                if (weight && weight.notes) {
                    return '<div class="tooltip-custom" style="padding: 10px;">' + 
                           '<small><i>Note: ' + weight.notes + '</i></small>' +
                           '</div>';
                }
                return '<div class="tooltip-custom" style="padding: 10px;"><small><i>Weight Entry</i></small></div>';
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'left',
            fontSize: '12px'
        },
        grid: {
            borderColor: '#f1f1f1',
            strokeDashArray: 5
        }
    };
    
    if (weightChart) {
        weightChart.destroy();
        weightChart = null;
    }
    chartElement.innerHTML = '';

    weightChart = new ApexCharts(chartElement, options);
    weightChart.render();
}

// Change time range
function changeWeightTimeRange(days) {
    loadWeightData(days);
}

// Show add weight form
function showAddWeightForm() {
    $('#weightGraphModal').modal('hide');
    $('#weightFormModal').modal('show');
    
    // Reset form
    document.getElementById('weightForm').reset();
    document.getElementById('weightId').value = '';
    document.getElementById('weightFormTitle').innerHTML = '<i class="fa fa-weight"></i> Log Weight';
    document.getElementById('deleteBtn').style.display = 'none';
    document.getElementById('date').value = new Date().toISOString().slice(0, 16);
}

// Show edit weight form
function editWeightEntry(weightId) {
    $('#weightGraphModal').modal('hide');
    
    // FIXED: using BASE_URL variable
    fetch(`${BASE_URL}weight/get-entry/${weightId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const weight = data.data;
                document.getElementById('weightId').value = weight.weight_id;
                document.getElementById('weight').value = weight.weight;
                document.getElementById('target_weight').value = weight.target_weight || '';
                document.getElementById('date').value = weight.formatted_date;
                document.getElementById('notes').value = weight.notes || '';
                
                document.getElementById('weightFormTitle').innerHTML = '<i class="fa fa-edit"></i> Edit Weight Entry';
                document.getElementById('deleteBtn').style.display = 'inline-block';
                
                $('#weightFormModal').modal('show');
            } else {
                alert(data.message || 'Error loading weight entry');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading weight entry');
        });
}

// Save weight entry
function saveWeightEntry() {
    const formData = new FormData(document.getElementById('weightForm'));
    const isEdit = formData.get('weight_id') !== '';
    
    // FIXED: using BASE_URL variable with proper string concatenation
    fetch(BASE_URL + (isEdit ? 'weight/update' : 'weight/add'), {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            $('#weightFormModal').modal('hide');
            
            // Reload weight data
            loadWeightData(currentDaysRange);
            
            // Update recent logs - FIXED: using BASE_URL
            fetch(`${BASE_URL}weight/get-data/${currentClientId}`)
                .then(response => response.json())
                .then(weightData => {
                    if (weightData.success) {
                        // Update the recent weigh-ins section
                        updateRecentWeighins(weightData.data.recent_logs);
                        // Update stats if needed
                        updateWeightStats(weightData.data.weight_data);
                    }
                });
            
            // Show weight modal again
            $('#weightGraphModal').modal('show');
        } else {
            alert(data.message || 'Error saving weight entry');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving weight entry');
    });
}

// Delete weight entry
function deleteWeightEntry() {
    if (!confirm('Are you sure you want to delete this weight entry?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('weight_id', document.getElementById('weightId').value);
    
    // FIXED: using BASE_URL variable
    fetch(BASE_URL + 'weight/delete', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            $('#weightFormModal').modal('hide');
            
            // Reload weight data
            loadWeightData(currentDaysRange);
            
            // Show weight modal again
            $('#weightGraphModal').modal('show');
        } else {
            alert(data.message || 'Error deleting weight entry');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting weight entry');
    });
}

// Helper function to update recent weigh-ins
function updateRecentWeighins(logs) {
    const container = document.getElementById('recentWeighins');
    if (!container) return;
    
    if (logs && logs.length > 0) {
        let html = '';
        logs.forEach(log => {
            html += `
                <div class="weight-item" data-weight-id="${log.weight_id}" 
                     style="display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px solid #eee;">
                    <div>
                        <strong>${new Date(log.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</strong>
                        <div style="font-size: 12px; color: #666;">${log.time}</div>
                        ${log.notes ? `
                            <div style="font-size: 11px; color: #888; margin-top: 3px;">
                                <i class="fa fa-sticky-note"></i> ${log.notes.substring(0, 30)}...
                            </div>
                        ` : ''}
                    </div>
                    <div style="display: flex; align-items: center;">
                        <span style="color: #27ae60; font-weight: bold;">
                            ${parseFloat(log.weight).toFixed(1)} kg
                        </span>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    } else {
        container.innerHTML = `
            <div class="no-data" style="text-align: center; padding: 20px; color: #777;">
                <i class="fa fa-weight fa-3x" style="margin-bottom: 10px;"></i>
                <p>No weight entries found. Add your first weight entry!</p>
                <button class="btn btn-primary btn-sm" onclick="showAddWeightForm()">
                    <i class="fa fa-plus"></i> Add First Entry
                </button>
            </div>
        `;
    }
}

// Helper function to update weight stats
function updateWeightStats(weightData) {
    // Update current weight
    const currentWeightEl = document.getElementById('currentWeight');
    if (currentWeightEl) {
        currentWeightEl.textContent = parseFloat(weightData.current).toFixed(1) + ' kg';
    }
    
    // Update start weight
    const startWeightEl = document.getElementById('startWeight');
    if (startWeightEl) {
        startWeightEl.textContent = parseFloat(weightData.start).toFixed(1) + ' kg';
    }
    
    // Update target weight
    const targetWeightEl = document.getElementById('targetWeight');
    if (targetWeightEl) {
        targetWeightEl.textContent = parseFloat(weightData.target).toFixed(1) + ' kg';
    }
    
    // Update weight change
    const weightChangeEl = document.getElementById('weightChange');
    if (weightChangeEl) {
        const change = weightData.change;
        weightChangeEl.textContent = (change > 0 ? '+' : '') + parseFloat(change).toFixed(1) + ' kg';
        
        // Update color classes
        weightChangeEl.className = 'metric-stat-value';
        if (change > 0) {
            weightChangeEl.classList.add('weight-up');
        } else if (change < 0) {
            weightChangeEl.classList.add('weight-down');
        } else {
            weightChangeEl.classList.add('weight-same');
        }
    }
}
</script>

<style>
/* Weight Modal Styles */
.weight-modal .modal-content {
    border-radius: 10px;
    overflow: hidden;
}

.weight-modal .modal-header {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    color: white;
    border-bottom: none;
}

.weight-modal .modal-header .close {
    color: white;
    opacity: 0.8;
    text-shadow: none;
}

.weight-modal .modal-header .close:hover {
    opacity: 1;
}

/* Stats Cards */
.metric-stats.weight-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.metric-stat-item.weight-stat {
    background: white;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: 1px solid #eaeaea;
    transition: all 0.3s ease;
}

.metric-stat-item.weight-stat:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.metric-stat-value {
    font-size: 24px;
    font-weight: bold;
    color: #27ae60;
    margin-bottom: 5px;
}

.metric-stat-label {
    font-size: 13px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Color coding for weight changes */
.weight-up {
    color: #e74c3c !important;
}

.weight-down {
    color: #27ae60 !important;
}

.weight-same {
    color: #f39c12 !important;
}

/* Chart Container */
.metric-graph-container {
    background: white;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

/* Time Range Selector */
.time-range-selector {
    text-align: center;
}

.time-range-selector .btn-group {
    border-radius: 6px;
    overflow: hidden;
}

.weight-time-btn {
    border: none !important;
    background: transparent !important;
    color: #555 !important;
    font-weight: 500;
    padding: 6px 15px;
    border-radius: 3px;
    transition: all 0.2s ease;
}

.weight-time-btn.wt-active {
    background: #27ae60 !important;
    color: white !important;
    box-shadow: 0 2px 4px rgba(39, 174, 96, 0.3);
}

.weight-time-btn:hover:not(.wt-active) {
    background: #e9ecef !important;
    color: #333 !important;
}

/* Weight Items */
.weight-item {
    transition: all 0.2s ease;
    border-radius: 5px;
}

.weight-item:hover {
    background: #f8f9fa;
    transform: translateX(5px);
}

/* Form Modal */
#weightFormModal .modal-content {
    border-radius: 8px;
}

#weightFormModal .modal-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

#weightFormModal .modal-title {
    color: #333;
    font-weight: 600;
}

/* Progress Bar */
.progress-container {
    animation: fadeIn 0.5s ease;
}

.progress-bar {
    transition: width 1s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .metric-stats.weight-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .metric-stat-value {
        font-size: 20px;
    }
    
    .modal-dialog {
        margin: 10px;
    }
}

@media (max-width: 480px) {
    .metric-stats.weight-stats {
        grid-template-columns: 1fr;
    }
    
    .trend-item {
        flex-direction: column;
        text-align: center;
    }
}

/* Custom Tooltip */
.apexcharts-tooltip {
    background: white !important;
    border: 1px solid #e0e0e0 !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
}

.apexcharts-tooltip-title {
    background: #27ae60 !important;
    color: white !important;
    border-bottom: none !important;
    padding: 8px 12px !important;
    font-weight: 600 !important;
    border-radius: 8px 8px 0 0 !important;
}

.tooltip-click-note {
    padding: 4px 8px !important;
    background: #f8f9fa !important;
    border-radius: 4px !important;
    margin-top: 5px !important;
    border-top: 1px solid #e0e0e0 !important;
}

/* Loading Animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-spinner {
    border: 3px solid #f3f3f3;
    border-top: 3px solid #27ae60;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 20px auto;
}

/* Button Styles */
.btn-primary {
    background: #27ae60;
    border-color: #27ae60;
}

.btn-primary:hover {
    background: #219653;
    border-color: #219653;
}

.btn-danger {
    background: #e74c3c;
    border-color: #e74c3c;
}

.btn-danger:hover {
    background: #c0392b;
    border-color: #c0392b;
}

/* Form Styles */
.form-control:focus {
    border-color: #27ae60;
    box-shadow: 0 0 0 0.2rem rgba(39, 174, 96, 0.25);
}
</style>