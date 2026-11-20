<div class="modal fade metric-modal water-modal" id="waterGraphModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white; border-bottom: none; padding: 20px; border-radius: 6px 6px 0 0;">
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1; text-shadow: none; margin-top: -5px;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" style="font-weight: 700; margin: 0; font-size: 18px;">
                    <i class="fa fa-tint" style="margin-right: 8px;"></i> Water Intake <span style="opacity: 0.8; font-weight: 400; font-size: 16px;">| <?= htmlspecialchars($clientDetails[0]->client_name ?? 'Client'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <!-- Stats Section -->
                <div class="row mb-4 mt-3">
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-info">
                            <i class="fa fa-tint bsc-icon"></i>
                            <h2 id="todayWaterText">0 <small style="font-size: 18px; color: rgba(255,255,255,0.8);">L</small></h2>
                            <p>Today's Intake</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-warning">
                            <i class="fa fa-chart-bar bsc-icon"></i>
                            <h2 id="avgWaterText">0 <small style="font-size: 18px; color: rgba(255,255,255,0.8);">L</small></h2>
                            <p>Daily Average</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-success">
                            <i class="fa fa-bullseye bsc-icon"></i>
                            <h2 id="targetWaterText">3.0 <small style="font-size: 18px; color: rgba(255,255,255,0.8);">L</small></h2>
                            <p>Daily Target</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-danger">
                            <i class="fa fa-check-circle bsc-icon"></i>
                            <h2 id="waterCompletionText">0%</h2>
                            <p>Completion Rate</p>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mb-4" style="background: #f8f9fa; padding: 20px; border-radius: 12px; box-shadow: inset 0 2px 5px rgba(0,0,0,0.03); margin-bottom: 25px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-weight: 700; color: #2d3436;">
                        <span><i class="fa fa-tint" style="color: #3498db; margin-right: 6px;"></i> Progress to Target</span>
                        <span id="waterProgressText" style="color: #3498db;">0%</span>
                    </div>
                    <div class="progress" style="height: 14px; border-radius: 10px; background-color: #dfe6e9; box-shadow: none; margin-bottom: 10px;">
                        <div class="progress-bar progress-bar-striped active" role="progressbar" id="waterProgressBar"
                             style="width: 0%; background: linear-gradient(90deg, #3498db, #2980b9); border-radius: 10px;" 
                             aria-valuenow="0" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; color: #636e72;">
                        <span>Start: 0.0L</span>
                        <span id="waterProgressCurrentText">Current: 0L</span>
                        <span id="waterProgressTargetText">Target: 3.0L</span>
                    </div>
                </div>

                <!-- Time Range Selector -->
                <div class="time-range-selector text-center" style="margin-bottom: 25px;">
                    <div class="btn-group" style="background: #f8f9fa; border-radius: 4px; padding: 4px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.05); display: inline-block;">
                        <button type="button" class="btn btn-sm time-range-btn water-time-btn active" data-days="7" onclick="changeWaterTimeRange(7)" style="border: none; background: #3498db; color: white; font-weight: 500; padding: 6px 15px; border-radius: 3px; box-shadow: 0 2px 4px rgba(52, 152, 219, 0.3);">7 Days</button>
                        <button type="button" class="btn btn-sm time-range-btn water-time-btn" data-days="30" onclick="changeWaterTimeRange(30)" style="border: none; background: transparent; color: #555; font-weight: 500; padding: 6px 15px; border-radius: 3px;">30 Days</button>
                        <button type="button" class="btn btn-sm time-range-btn water-time-btn" data-days="90" onclick="changeWaterTimeRange(90)" style="border: none; background: transparent; color: #555; font-weight: 500; padding: 6px 15px; border-radius: 3px;">90 Days</button>
                        <button type="button" class="btn btn-sm time-range-btn water-time-btn" data-days="365" onclick="changeWaterTimeRange(365)" style="border: none; background: transparent; color: #555; font-weight: 500; padding: 6px 15px; border-radius: 3px;">1 Year</button>
                    </div>
                </div>

                <div class="metric-graph-container" style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <div id="waterChart" style="height: 300px;"></div>
                </div>

                <!-- View Only Water Log -->
                <div class="water-log" style="margin-top: 20px;">
                    <h5 style="color: #3498db; margin-bottom: 15px; font-weight: bold;">
                        <i class="fa fa-history"></i> Water Intake History
                    </h5>
                    <div class="water-list" id="waterLogList">
                        <div class="text-muted text-center">No water intake recorded for selected date</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let waterChart = null;
let currentWaterData = null;
let clientId = <?= $clientDetails[0]->client_id; ?>;

document.addEventListener('DOMContentLoaded', function() {
    waterUpdateDateDisplay();

    // Initialize chart when modal opens (ensures correct dimensions)
    $('#waterGraphModal').on('shown.bs.modal', function(e) {
        if (e.target !== this) return;
        loadWaterData();
        loadWaterRangeData(7);
        // Reset active button to 7 days
        $('.water-time-btn').removeClass('active').css({ 'background': 'transparent', 'color': '#555', 'box-shadow': 'none' });
        $('.water-time-btn[data-days="7"]').addClass('active').css({ background: '#3498db', color: 'white', 'box-shadow': '0 2px 4px rgba(52, 152, 219, 0.3)' });
    });

    // Destroy chart when modal closes
    $('#waterGraphModal').on('hidden.bs.modal', function() {
        if (waterChart) { try { waterChart.destroy(); } catch(e) {} waterChart = null; }
    });

    // Handle date changes
    $('#waterDate').on('change', function() {
        waterUpdateDateDisplay();
        loadWaterData();
    });
});

function changeWaterTimeRange(days) {
    // Update active button UI
    $('.water-time-btn').removeClass('active').css({
        'background': 'transparent',
        'color': '#555',
        'box-shadow': 'none'
    });
    $(`.water-time-btn[data-days="${days}"]`).addClass('active').css({
        'background': '#3498db',
        'color': 'white',
        'box-shadow': '0 2px 4px rgba(52, 152, 219, 0.3)'
    });

    console.log('Changing water time range to:', days);
    loadWaterRangeData(days);
}

function loadWaterRangeData(days) {
    const chartContainer = document.querySelector('#waterChart');
    if (chartContainer) {
        chartContainer.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;"><div class="text-center" style="color:#999;"><i class="fa fa-spinner fa-spin" style="font-size:24px;margin-bottom:10px;"></i><br>Loading water data...</div></div>';
    }

    fetch('<?= base_url("admin/water/getWaterRangeData"); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `client_id=${clientId}&days=${days}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (waterChart) { waterChart.destroy(); waterChart = null; }
            if (chartContainer) chartContainer.innerHTML = '';
            initializeWaterChartWithData(data.data.series, data.data.categories);
        } else {
            if (chartContainer) chartContainer.innerHTML = '<div class="text-center text-muted" style="padding:50px;">No data available for selected period</div>';
        }
    })
    .catch(error => {
        console.error('Error loading water range data:', error);
        if (chartContainer) chartContainer.innerHTML = '<div class="text-center text-danger" style="padding:50px;">Error loading data</div>';
    });
}

function initializeWaterChartWithData(series, categories) {
    const waterOptions = {
        series: [{ name: 'Water Intake', data: series }],
        chart: { height: 300, type: 'area', zoom: { enabled: false }, toolbar: { show: true } },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2, colors: ['#3498db'] },
        colors: ['#3498db'],
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.2, stops: [0, 90, 100] }
        },
        xaxis: { categories: categories, title: { text: 'Days' } },
        yaxis: {
            title: { text: 'Liters' },
            min: 0,
            labels: { formatter: function(val) { return val.toFixed(1) + ' L'; } }
        },
        tooltip: { y: { formatter: function(val) { return val.toFixed(1) + ' L'; } } },
        grid: { borderColor: '#f1f1f1' }
    };
    waterChart = new ApexCharts(document.querySelector('#waterChart'), waterOptions);
    waterChart.render();
}

function waterUpdateDateDisplay() {
    const dateInput = document.getElementById('waterDate');
    if (!dateInput) return;
    
    const selectedDate = dateInput.value;
    const dateObj = new Date(selectedDate);
    const formattedDate = dateObj.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    const displayElement = document.getElementById('currentDateDisplay');
    if (displayElement) {
        displayElement.textContent = ' - ' + formattedDate;
    }
}

function loadWaterData() {
    const dateInput = document.getElementById('waterDate');
    if (!dateInput) return;
    
    const selectedDate = dateInput.value;
    
    // Show loading state
    showLoadingState(true);
    
    // AJAX call to fetch water data
    fetch('<?= base_url("admin/water/getWaterData"); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `client_id=${clientId}&date=${selectedDate}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentWaterData = data.data;
            updateWaterUI(data.data);
        } else {
            initializeEmptyWaterData(selectedDate);
        }
        showLoadingState(false);
    })
    .catch(error => {
        console.error('Error loading water data:', error);
        showLoadingState(false);
        alert('Error loading water data');
    });
}

function updateWaterUI(data) {
    // Update text fields instead of inputs
    document.getElementById('todayWaterText').innerHTML = (data.today_intake || 0) + ' <small style="font-size: 18px; color: rgba(255,255,255,0.8);">L</small>';
    document.getElementById('targetWaterText').innerHTML = (data.daily_target || 3.0) + ' <small style="font-size: 18px; color: rgba(255,255,255,0.8);">L</small>';
    document.getElementById('avgWaterText').innerHTML = (data.weekly_average || 0) + ' <small style="font-size: 18px; color: rgba(255,255,255,0.8);">L</small>';
    
    // Update completion percentage
    const completion = data.today_intake && data.daily_target ? 
        Math.min(100, Math.round((data.today_intake / data.daily_target) * 100)) : 0;
    
    document.getElementById('waterCompletionText').textContent = completion + '%';
    document.getElementById('waterProgressText').textContent = completion + '%';
    document.getElementById('waterProgressBar').style.width = completion + '%';
    
    document.getElementById('waterProgressCurrentText').textContent = 'Current: ' + (data.today_intake || 0) + 'L';
    document.getElementById('waterProgressTargetText').textContent = 'Target: ' + (data.daily_target || 3.0) + 'L';
    
    // Update water log
    updateWaterLogList(data.logs || []);
}

function initializeEmptyWaterData(date) {
    const emptyData = {
        today_intake: 0,
        daily_target: 3.0,
        logs: [],
        weekly_data: [0, 0, 0, 0, 0, 0, 0],
        weekly_categories: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        weekly_average: 0
    };
    currentWaterData = emptyData;
    updateWaterUI(emptyData);
}

function updateWaterLogList(logs) {
    const logList = document.getElementById('waterLogList');
    logList.innerHTML = '';
    
    if (logs.length === 0) {
        logList.innerHTML = '<div class="text-muted text-center">No water intake recorded for selected date</div>';
        return;
    }
    
    // Calculate total from logs
    let totalFromLogs = 0;
    
    logs.forEach((log, index) => {
        totalFromLogs += log.amount;
        const logItem = document.createElement('div');
        logItem.className = 'water-item';
        logItem.style.cssText = 'display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px solid #eee; align-items: center;';
        logItem.innerHTML = `
            <div>
                <strong>${log.time}</strong>
                <div style="font-size: 12px; color: #666;">${log.amount} ml - ${log.type}</div>
            </div>
            <div>
                <span style="color: #3498db; font-weight: bold;">+${log.amount}ml</span>
            </div>
        `;
        logList.appendChild(logItem);
    });

    // We do NOT update inputs dynamically since this is view only, we just rely on what the server returned.
}

function addWaterLogEntry() {
    document.getElementById('addWaterForm').style.display = 'block';
}

function cancelWaterLogEntry() {
    document.getElementById('addWaterForm').style.display = 'none';
    // Reset form
    document.getElementById('newWaterTime').value = '<?= date("H:i"); ?>';
    document.getElementById('newWaterAmount').value = '500';
    document.getElementById('newWaterType').value = 'Glass';
}

function saveWaterLogEntry() {
    const time = document.getElementById('newWaterTime').value;
    const amount = parseInt(document.getElementById('newWaterAmount').value);
    const type = document.getElementById('newWaterType').value;
    
    if (!time || !amount || amount <= 0) {
        alert('Please fill all fields correctly');
        return;
    }
    
    // Add to current data
    if (!currentWaterData.logs) {
        currentWaterData.logs = [];
    }
    
    currentWaterData.logs.push({
        time: formatTime(time),
        amount: amount,
        type: type
    });
    
    // Update UI
    updateWaterLogList(currentWaterData.logs);
    
    // Hide form
    cancelWaterLogEntry();
}

function deleteWaterLogEntry(index) {
    if (confirm('Are you sure you want to delete this entry?')) {
        currentWaterData.logs.splice(index, 1);
        updateWaterLogList(currentWaterData.logs);
    }
}

function saveWaterData() {
    const selectedDate = document.getElementById('waterDate').value;
    const todayIntake = parseFloat(document.getElementById('todayWaterInput').value) || 0;
    const dailyTarget = parseFloat(document.getElementById('targetWaterInput').value) || 3.0;
    
    // Update current data
    currentWaterData.today_intake = todayIntake;
    currentWaterData.daily_target = dailyTarget;
    
    // Prepare data for saving
    const saveData = {
        client_id: clientId,
        date: selectedDate,
        today_intake: todayIntake,
        daily_target: dailyTarget,
        logs: currentWaterData.logs || []
    };
    
    // AJAX call to save water data
    fetch('<?= base_url("admin/water/saveWaterData"); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(saveData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Water data saved successfully!');
            loadWaterData(); // Reload to get updated data
        } else {
            alert('Error saving water data: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error saving water data:', error);
        alert('Error saving water data');
    });
}

function resetWaterData() {
    if (confirm('Are you sure you want to reset all water data for this date?')) {
        initializeEmptyWaterData(document.getElementById('waterDate').value);
    }
}

function formatTime(timeString) {
    const [hours, minutes] = timeString.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const formattedHour = hour % 12 || 12;
    return `${formattedHour.toString().padStart(2, '0')}:${minutes} ${ampm}`;
}

function showLoadingState(show) {
    if (show) {
        document.getElementById('waterLogList').innerHTML = '<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading water data...</div>';
    }
}

// CHART FUNCTIONS - UPDATED TO USE REAL DATA
function initializeWaterChart(waterData) {
    // Destroy existing chart if it exists
    if (waterChart) {
        waterChart.destroy();
        waterChart = null;
    }

    const chartData = generateChartData(waterData);

    const waterOptions = {
        series: [{
            name: 'Water Intake',
            data: chartData.series
        }],
        chart: {
            height: 300,
            type: 'area',
            zoom: { enabled: false },
            toolbar: { show: true }
        },
        dataLabels: { 
            enabled: false 
        },
        stroke: {
            curve: 'smooth',
            width: 2,
            colors: ['#3498db']
        },
        colors: ['#3498db'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.2,
                stops: [0, 90, 100]
            }
        },
        xaxis: {
            categories: chartData.categories,
            title: {
                text: 'Days'
            }
        },
        yaxis: {
            title: { 
                text: 'Liters' 
            },
            min: 0,
            labels: {
                formatter: function(val) {
                    return val.toFixed(1) + " L";
                }
            }
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val.toFixed(1) + " L";
                }
            }
        },
        grid: {
            borderColor: '#f1f1f1',
        }
    };

    waterChart = new ApexCharts(document.querySelector("#waterChart"), waterOptions);
    waterChart.render();
}

function generateChartData(waterData) {
    // Use weekly data from server if available
    if (waterData.weekly_data && waterData.weekly_data.length > 0) {
        return {
            series: waterData.weekly_data,
            categories: waterData.weekly_categories || ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
        };
    }
    
    // If we have today's data, incorporate it into the chart
    const todayIntake = waterData.today_intake || 0;
    const selectedDate = new Date(document.getElementById('waterDate').value);
    const todayIndex = selectedDate.getDay(); // 0-6 for Sunday-Saturday
    
    // Generate base weekly data
    let weeklyData = [0, 0, 0, 0, 0, 0, 0];
    
    // Insert today's data at the correct position
    if (todayIntake > 0) {
        weeklyData[todayIndex] = todayIntake;
    }
    
    return {
        series: weeklyData,
        categories: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
    };
}
</script>