<!-- FIXED Calorie Graph Modal -->
<div class="modal fade metric-modal calorie-modal" id="calorieGraphModal" 
     data-client-id="<?= $clientDetails[0]->client_id ?? 1; ?>" 
     tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; border-bottom: none; padding: 20px; border-radius: 6px 6px 0 0;">
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1; text-shadow: none; margin-top: -5px;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" style="font-weight: 700; margin: 0; font-size: 18px;">
                    <i class="fa fa-fire" style="margin-right: 8px;"></i> Calorie Tracking <span style="opacity: 0.8; font-weight: 400; font-size: 16px;">| <?= htmlspecialchars($clientDetails[0]->client_name ?? 'Client'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <!-- Stats Section -->
                <div class="row mb-4 mt-3">
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-info" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                            <i class="fa fa-utensils bsc-icon"></i>
                            <h2 id="todayCalorieStat">0</h2>
                            <p>Today's Intake (cal)</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-warning" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                            <i class="fa fa-fire bsc-icon"></i>
                            <h2 id="todayBurnedStat">0</h2>
                            <p>Today's Burned (cal)</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-success">
                            <i class="fa fa-bullseye bsc-icon"></i>
                            <h2 id="targetCalorieStat">2000</h2>
                            <p>Intake Target (cal)</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-danger" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                            <i class="fa fa-calculator bsc-icon"></i>
                            <h2 id="netCaloriesStat">0</h2>
                            <p>Net Calories (cal)</p>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mb-4" style="background: #f8f9fa; padding: 20px; border-radius: 12px; box-shadow: inset 0 2px 5px rgba(0,0,0,0.03); margin-bottom: 25px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-weight: 700; color: #2d3436;">
                        <span><i class="fa fa-fire" style="color: #e74c3c; margin-right: 6px;"></i> Intake Progress</span>
                        <span id="intakeProgressText" style="color: #e74c3c;">0%</span>
                    </div>
                    <div class="progress" style="height: 14px; border-radius: 10px; background-color: #dfe6e9; box-shadow: none; margin-bottom: 10px;">
                        <div class="progress-bar progress-bar-striped active" role="progressbar" id="intakeProgressBar"
                             style="width: 0%; background: linear-gradient(90deg, #3498db, #2980b9); border-radius: 10px;">
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; color: #636e72;">
                        <span id="calorieStatusText">Remaining: 0 cal</span>
                    </div>
                </div>

                <!-- Time Range Selector -->
                <div class="time-range-selector text-center" style="margin-bottom: 25px;">
                    <div class="btn-group" style="background: #f8f9fa; border-radius: 4px; padding: 4px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.05); display: inline-block;">
                        <button type="button" class="btn btn-sm time-range-btn calorie-time-btn active" data-days="7" onclick="changeCalorieTimeRange(7)" style="border: none; background: #e74c3c; color: white; font-weight: 500; padding: 6px 15px; border-radius: 3px; box-shadow: 0 2px 4px rgba(231, 76, 60, 0.3);">7 Days</button>
                        <button type="button" class="btn btn-sm time-range-btn calorie-time-btn" data-days="30" onclick="changeCalorieTimeRange(30)" style="border: none; background: transparent; color: #555; font-weight: 500; padding: 6px 15px; border-radius: 3px;">30 Days</button>
                        <button type="button" class="btn btn-sm time-range-btn calorie-time-btn" data-days="90" onclick="changeCalorieTimeRange(90)" style="border: none; background: transparent; color: #555; font-weight: 500; padding: 6px 15px; border-radius: 3px;">90 Days</button>
                        <button type="button" class="btn btn-sm time-range-btn calorie-time-btn" data-days="365" onclick="changeCalorieTimeRange(365)" style="border: none; background: transparent; color: #555; font-weight: 500; padding: 6px 15px; border-radius: 3px;">1 Year</button>
                    </div>
                </div>

                <!-- Chart -->
                <div class="metric-graph-container" style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <div id="calorieChart" style="height: 350px; position: relative;">
                        <div class="text-center" style="padding-top: 150px; color: #999;">
                            <i class="fa fa-chart-line fa-3x"></i>
                            <p>Loading chart...</p>
                        </div>
                    </div>
                </div>

                <!-- Food and Activity Logs -->
                <div class="row" style="margin-top: 20px;">
                    <!-- Food Log -->
                    <div class="col-md-6">
                        <h5 style="color: #3498db; margin-bottom: 15px; font-weight: bold;">
                            <i class="fa fa-utensils"></i> Food Intake Log
                        </h5>
                        <div class="food-list table-responsive" style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); padding: 10px;" id="foodLogList">
                            <div class="text-muted text-center" style="padding: 10px;">No food intake recorded for selected date</div>
                        </div>
                    </div>
                    <!-- Activity Log -->
                    <div class="col-md-6">
                        <h5 style="color: #e74c3c; margin-bottom: 15px; font-weight: bold;">
                            <i class="fa fa-running"></i> Activities
                        </h5>
                        <div class="activity-list table-responsive" style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); padding: 10px;" id="activityLogList">
                            <div class="text-muted text-center" style="padding: 10px;">No activities recorded for selected date</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Removed redundant Chart.js load -->

<script>
// FIXED: Proper base URL handling with trailing slash check
const baseUrl = '<?= base_url(); ?>';
const normalizedBaseUrl = baseUrl.endsWith('/') ? baseUrl : baseUrl + '/';

console.log('Calorie Modal Initialized');
console.log('Base URL:', normalizedBaseUrl);

// Debug toggle function
function toggleDebug() {
    const debugAlert = document.getElementById('debugAlert');
    debugAlert.style.display = debugAlert.style.display === 'none' ? 'block' : 'none';
}

// Update debug info
function updateDebugInfo(info) {
    const debugContent = document.getElementById('debugContent');
    if (debugContent) {
        debugContent.innerHTML = `
            <small>
                <strong>Client ID:</strong> ${info.clientId}<br>
                <strong>Current Date:</strong> ${info.currentDate}<br>
                <strong>API Endpoint:</strong> ${info.endpoint}<br>
                <strong>Response Status:</strong> ${info.status || 'N/A'}<br>
                <strong>Data Received:</strong> ${info.dataReceived ? 'Yes' : 'No'}<br>
                <strong>Chart Status:</strong> ${info.chartStatus || 'Not initialized'}<br>
                <strong>Last Error:</strong> ${info.lastError || 'None'}
            </small>
        `;
    }
}

// FIXED: Calorie Tracker for Modal with enhanced error handling
class CalorieTrackerModal {
    constructor(clientId) {
        this.clientId = clientId;
        this.currentDate = new Date().toISOString().split('T')[0];
        this.currentDays = 7;
        this.chart = null;
        this.foods = [];
        this.activities = [];
        this.isLoading = false;
        this.debugMode = false;
        
        console.log('CalorieTrackerModal initialized with client ID:', this.clientId);
        this.init();
    }

    changeTimeRange(days) {
        // Update active button UI
        $('.calorie-time-btn').removeClass('active').css({
            'background': 'transparent',
            'color': '#555',
            'box-shadow': 'none'
        });
        $(`.calorie-time-btn[data-days="${days}"]`).addClass('active').css({
            'background': '#e74c3c',
            'color': 'white',
            'box-shadow': '0 2px 4px rgba(231, 76, 60, 0.3)'
        });
        
        this.currentDays = days;
        this.loadCalorieData();
    }
    
    init() {
        console.log('Initializing CalorieTrackerModal...');
        this.updateDateDisplay();
        this.setupEventListeners();
        
        // Load data after a short delay to ensure DOM is ready
        setTimeout(() => {
            this.loadCalorieData();
        }, 300);
    }
    
    setupEventListeners() {
        console.log('Setting up event listeners...');
        
        // Date change
        const dateInput = document.getElementById('calorieDate');
        if (dateInput) {
            dateInput.addEventListener('change', (e) => {
                console.log('Date changed to:', e.target.value);
                this.currentDate = e.target.value;
                this.updateDateDisplay();
                this.loadCalorieData();
            });
        } else {
            console.error('Date input not found!');
        }
    }
    
    updateDateDisplay() {
        const dateDisplay = document.getElementById('calorieDateDisplay');
        if (!dateDisplay) {
            console.warn('Date display element not found');
            return;
        }
        
        const date = new Date(this.currentDate);
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        dateDisplay.textContent = date.toLocaleDateString('en-US', options);
    }
    
    async loadCalorieData() {
        if (this.isLoading) {
            console.log('Already loading, skipping...');
            return;
        }
        
        this.isLoading = true;
        this.showLoading();
        
        const endpoint = normalizedBaseUrl + 'calorie/getCalorieData';
        console.log('Loading calorie data...');
        console.log('Client ID:', this.clientId);
        console.log('Date:', this.currentDate);
        console.log('Endpoint:', endpoint);
        
        try {
            const formData = new URLSearchParams({
                client_id: this.clientId,
                date: this.currentDate,
                days: this.currentDays
            });
            
            console.log('Request payload:', formData.toString());
            
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('Response data:', result);
            
            // Update debug info
            updateDebugInfo({
                clientId: this.clientId,
                currentDate: this.currentDate,
                endpoint: endpoint,
                status: response.status,
                dataReceived: !!result.data,
                chartStatus: this.chart ? 'Initialized' : 'Not initialized',
                lastError: 'None'
            });
            
            if (result.status === 'success') {
                this.updateUI(result.data);
                this.updateChart(result.data);
                
                // Only show success alert if debug mode is on
                if (this.debugMode) {
                    this.showAlert('Data loaded successfully', 'success');
                }
            } else {
                throw new Error(result.message || 'Failed to load data');
            }
            
        } catch (error) {
            console.error('Error loading calorie data:', error);
            console.error('Error stack:', error.stack);
            
            // Update debug info with error
            updateDebugInfo({
                clientId: this.clientId,
                currentDate: this.currentDate,
                endpoint: endpoint,
                status: 'Error',
                dataReceived: false,
                chartStatus: this.chart ? 'Initialized' : 'Not initialized',
                lastError: error.message
            });
            
            this.showAlert('Error loading data: ' + error.message, 'error');
            
            // Show empty state in chart
            this.showEmptyChart('Error loading data. Please try again.');
            
        } finally {
            this.isLoading = false;
            this.hideLoading();
        }
    }
    
    updateUI(data) {
        console.log('Updating UI with data:', data);
        
        try {
            const todayIntake = parseInt(data.today_intake || 0);
            const todayBurned = parseInt(data.today_burned || 0);
            const intakeTarget = parseInt(data.intake_target || 2000);
            const burnedTarget = parseInt(data.burned_target || 500);

            const todayStat = document.getElementById('todayCalorieStat');
            const burnedStat = document.getElementById('todayBurnedStat');
            const targetStat = document.getElementById('targetCalorieStat');
            
            if (todayStat) todayStat.textContent = todayIntake.toLocaleString();
            if (burnedStat) burnedStat.textContent = todayBurned.toLocaleString();
            if (targetStat) targetStat.textContent = intakeTarget.toLocaleString();

            this.currentIntake = todayIntake;
            this.currentBurned = todayBurned;
            this.currentIntakeTarget = intakeTarget;
            
            // FIXED: Ensure calories is total calories, not per unit
            this.foods = (data.foods || []).map(food => {
                const caloriesPerUnit = food.calories_per_unit || food.calories || 0;
                const quantity = food.quantity || 1;
                return {
                    ...food,
                    calories: caloriesPerUnit * quantity,
                    calories_per_unit: caloriesPerUnit
                };
            });
            
            this.activities = data.activities || [];
            
            console.log('Processed foods:', this.foods);
            console.log('Processed activities:', this.activities);
            
            this.updateFoodList();
            this.updateActivityList();
            this.updateCalculations();
            
        } catch (error) {
            console.error('Error updating UI:', error);
            this.showAlert('Error updating interface: ' + error.message, 'error');
        }
    }
    
    updateCalculations() {
        try {
            const intake = this.currentIntake || 0;
            const burned = this.currentBurned || 0;
            const intakeTarget = this.currentIntakeTarget || 2000;
            
            // Calculate net calories
            const netCalories = intake - burned;
            const remainingCalories = intakeTarget - intake;
            
            const netStat = document.getElementById('netCaloriesStat');
            if (netStat) netStat.textContent = netCalories.toLocaleString();
            
            // Update progress bar
            const intakeProgress = intakeTarget > 0 ? Math.min((intake / intakeTarget) * 100, 100) : 0;
            
            const intakeBar = document.getElementById('intakeProgressBar');
            const intakeText = document.getElementById('intakeProgressText');
            const statusText = document.getElementById('calorieStatusText');
            
            if (intakeBar) intakeBar.style.width = intakeProgress + '%';
            if (intakeText) intakeText.textContent = intakeProgress.toFixed(0) + '%';
            
            if (statusText) {
                statusText.textContent = remainingCalories >= 0 ? 
                    `Remaining: ${remainingCalories.toLocaleString()} cal` : 
                    `Exceeded: ${Math.abs(remainingCalories).toLocaleString()} cal`;
                statusText.style.color = remainingCalories >= 0 ? '#2ecc71' : '#e74c3c';
            }
            
        } catch (error) {
            console.error('Error updating calculations:', error);
        }
    }
    
    updateFoodList() {
        const list = document.getElementById('foodLogList');
        if (!list) {
            console.error('Food list element not found');
            return;
        }
        
        if (this.foods.length === 0) {
            list.innerHTML = '<div class="text-muted text-center" style="padding: 10px;">No food intake recorded for selected date</div>';
            return;
        }
        
        list.innerHTML = '';
        this.foods.forEach((food, index) => {
            const div = document.createElement('div');
            div.className = 'food-log-item';
            div.style.cssText = 'padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;';
            
            const time = food.time || '12:00';
            const name = food.name || food.food_name || 'Unknown Food';
            const quantity = food.quantity || 1;
            const calories = Math.round(food.calories || 0);
            const foodId = food.id || food.calorie_id || 'null';
            
            div.innerHTML = `
                <div style="display: flex; align-items: center;">
                    <span style="min-width: 70px; color: #666;">${time}</span>
                    <span style="margin-left: 15px; font-weight: 500;">${name}</span>
                    ${quantity > 1 ? `<span style="margin-left: 10px; color: #999; font-size: 12px;">(x${quantity})</span>` : ''}
                </div>
                <div style="display: flex; align-items: center;">
                    <span style="font-weight: bold; color: #3498db; margin-right: 15px;">${calories} cal</span>
                </div>
            `;
            list.appendChild(div);
        });
    }
    
    updateActivityList() {
        const list = document.getElementById('activityLogList');
        if (!list) {
            console.error('Activity list element not found');
            return;
        }
        
        if (this.activities.length === 0) {
            list.innerHTML = '<h6 style="color: #e74c3c;"><i class="fa fa-running"></i> Activities</h6><div class="text-muted text-center" style="padding: 10px;">No activities recorded for selected date</div>';
            return;
        }
        
        let html = '<h6 style="color: #e74c3c;"><i class="fa fa-running"></i> Activities</h6>';
        this.activities.forEach((activity, index) => {
            const time = activity.time || '12:00';
            const name = activity.name || activity.activity_name || 'Activity';
            const duration = activity.duration || (activity.duration_minutes ? activity.duration_minutes + ' min' : '');
            const calories = Math.round(activity.calories || 0);
            const activityId = activity.id || activity.calorie_burned_id || 'null';
            
            html += `
                <div class="activity-log-item" style="padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center;">
                        <span style="min-width: 70px; color: #666;">${time}</span>
                        <span style="margin-left: 15px; font-weight: 500;">${name}</span>
                        ${duration ? `<span style="margin-left: 10px; color: #999; font-size: 12px;">(${duration})</span>` : ''}
                    </div>
                    <div style="display: flex; align-items: center;">
                        <span style="font-weight: bold; color: #e74c3c; margin-right: 15px;">${calories} cal</span>
                    </div>
                </div>
            `;
        });
        
        list.innerHTML = html;
    }
    
    updateChart(data) {
        console.log('Updating chart with data:', data);
        
        const ctx = document.getElementById('calorieChart');
        
        if (!ctx) {
            console.error('Chart container not found');
            return;
        }
        
        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.error('Chart.js not loaded!');
            ctx.innerHTML = `
                <div class="text-center text-danger" style="padding: 50px;">
                    <i class="fa fa-exclamation-triangle fa-3x"></i>
                    <p>Chart library not loaded. Please refresh the page.</p>
                </div>
            `;
            return;
        }
        
        // Destroy existing chart
        if (this.chart) {
            try {
                this.chart.destroy();
                this.chart = null;
            } catch(e) {
                console.log('Error destroying chart:', e);
            }
        }
        
        // Check if data is available
        if (!data || !data.weekly_categories || !data.weekly_intake || !data.weekly_burned) {
            console.warn('Invalid or missing chart data:', data);
            this.showEmptyChart('No calorie data available yet. Start adding foods and activities!');
            return;
        }
        
        // Ensure we have arrays
        const categories = Array.isArray(data.weekly_categories) ? data.weekly_categories : [];
        const intake = Array.isArray(data.weekly_intake) ? data.weekly_intake : [];
        const burned = Array.isArray(data.weekly_burned) ? data.weekly_burned : [];
        const dates = Array.isArray(data.weekly_dates) ? data.weekly_dates : categories;
        
        console.log('Chart data prepared:', { categories, intake, burned, dates });
        
        // Create canvas if it doesn't exist
        let canvas = ctx.querySelector('canvas');
        if (!canvas) {
            canvas = document.createElement('canvas');
            canvas.id = 'calorieChartCanvas';
            ctx.innerHTML = '';
            ctx.appendChild(canvas);
        }
        
        try {
            // Create new chart
            const chartContext = canvas.getContext('2d');
            
            this.chart = new Chart(chartContext, {
                type: 'line',
                data: {
                    labels: categories,
                    datasets: [
                        {
                            label: 'Calorie Intake',
                            data: intake,
                            borderColor: '#3498db',
                            backgroundColor: 'rgba(52, 152, 219, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#3498db',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8
                        },
                        {
                            label: 'Calories Burned',
                            data: burned,
                            borderColor: '#e74c3c',
                            backgroundColor: 'rgba(231, 76, 60, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#e74c3c',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            padding: 12,
                            cornerRadius: 6,
                            displayColors: true,
                            callbacks: {
                                title: (context) => {
                                    const index = context[0].dataIndex;
                                    return `${dates[index] || categories[index]}`;
                                },
                                label: (context) => {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    const value = context.parsed.y;
                                    label += value.toLocaleString() + ' calories';
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Calories',
                                color: '#666',
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        }
                    }
                }
            });
            
            console.log('Chart created successfully');
            
            // Update debug info
            updateDebugInfo({
                clientId: this.clientId,
                currentDate: this.currentDate,
                endpoint: normalizedBaseUrl + 'calorie/getCalorieData',
                status: 'Success',
                dataReceived: true,
                chartStatus: 'Rendered',
                lastError: 'None'
            });
            
        } catch (error) {
            console.error('Error creating chart:', error);
            this.showEmptyChart('Error creating chart: ' + error.message);
        }
    }
    
    showEmptyChart(message) {
        const ctx = document.getElementById('calorieChart');
        if (ctx) {
            ctx.innerHTML = `
                <div class="text-center" style="padding-top: 120px; color: #999;">
                    <i class="fa fa-chart-line fa-3x mb-3"></i>
                    <p>${message}</p>
                </div>
            `;
        }
    }
    
    addFoodLogEntry() {
        const form = document.getElementById('addFoodForm');
        if (form) {
            form.style.display = 'block';
            const nameInput = document.getElementById('newFoodName');
            if (nameInput) nameInput.focus();
        }
    }
    
    saveFoodLogEntry() {
        try {
            const caloriesPerUnit = parseInt(document.getElementById('newFoodCalories')?.value || 0);
            const quantity = parseFloat(document.getElementById('newFoodQuantity')?.value || 1);
            const totalCalories = caloriesPerUnit * quantity;
            
            const food = {
                time: document.getElementById('newFoodTime')?.value || '12:00',
                name: document.getElementById('newFoodName')?.value.trim() || 'Unknown Food',
                calories_per_unit: caloriesPerUnit,
                calories: totalCalories,
                quantity: quantity,
                is_new: true
            };
            
            if (!food.name || caloriesPerUnit <= 0) {
                this.showAlert('Please enter valid food information', 'error');
                return;
            }
            
            this.foods.push(food);
            this.foods.sort((a, b) => a.time.localeCompare(b.time));
            this.updateFoodList();
            this.calculateTotalFromLists();
            this.cancelFoodLogEntry();
            this.showAlert('Food item added (click Save to save permanently)', 'success');
            
        } catch (error) {
            console.error('Error saving food entry:', error);
            this.showAlert('Error adding food: ' + error.message, 'error');
        }
    }
    
    cancelFoodLogEntry() {
        const form = document.getElementById('addFoodForm');
        if (form) form.style.display = 'none';
        
        const timeInput = document.getElementById('newFoodTime');
        const nameInput = document.getElementById('newFoodName');
        const caloriesInput = document.getElementById('newFoodCalories');
        const quantityInput = document.getElementById('newFoodQuantity');
        
        if (timeInput) timeInput.value = new Date().toTimeString().slice(0, 5);
        if (nameInput) nameInput.value = '';
        if (caloriesInput) caloriesInput.value = '100';
        if (quantityInput) quantityInput.value = '1';
    }
    
    addActivityLogEntry() {
        const form = document.getElementById('addActivityForm');
        if (form) {
            form.style.display = 'block';
            const nameInput = document.getElementById('newActivityName');
            if (nameInput) nameInput.focus();
        }
    }
    
    saveActivityLogEntry() {
        try {
            const activity = {
                time: document.getElementById('newActivityTime')?.value || '12:00',
                name: document.getElementById('newActivityName')?.value.trim() || 'Activity',
                calories: parseInt(document.getElementById('newActivityCalories')?.value || 0),
                duration: (document.getElementById('newActivityDuration')?.value || '30') + ' min',
                is_new: true
            };
            
            if (!activity.name || activity.calories <= 0) {
                this.showAlert('Please enter valid activity information', 'error');
                return;
            }
            
            this.activities.push(activity);
            this.activities.sort((a, b) => a.time.localeCompare(b.time));
            this.updateActivityList();
            this.calculateTotalFromLists();
            this.cancelActivityEntry();
            this.showAlert('Activity added (click Save to save permanently)', 'success');
            
        } catch (error) {
            console.error('Error saving activity entry:', error);
            this.showAlert('Error adding activity: ' + error.message, 'error');
        }
    }
    
    cancelActivityEntry() {
        const form = document.getElementById('addActivityForm');
        if (form) form.style.display = 'none';
        
        const timeInput = document.getElementById('newActivityTime');
        const nameInput = document.getElementById('newActivityName');
        const caloriesInput = document.getElementById('newActivityCalories');
        const durationInput = document.getElementById('newActivityDuration');
        
        if (timeInput) timeInput.value = new Date().toTimeString().slice(0, 5);
        if (nameInput) nameInput.value = '';
        if (caloriesInput) caloriesInput.value = '100';
        if (durationInput) durationInput.value = '30';
    }
    
    async deleteFood(index, foodId) {
        if (!confirm('Are you sure you want to delete this food item?')) return;
        
        try {
            // If food has an ID (exists in database), delete from server
            if (foodId && foodId !== 'null' && foodId !== null) {
                const response = await fetch(normalizedBaseUrl + 'calorie/deleteFoodItem', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({
                        calorie_id: foodId
                    })
                });
                
                const result = await response.json();
                if (result.status !== 'success') {
                    throw new Error(result.message || 'Failed to delete food');
                }
            }
            
            // Remove from local array
            this.foods.splice(index, 1);
            this.updateFoodList();
            this.calculateTotalFromLists();
            this.showAlert('Food item deleted', 'success');
            
        } catch (error) {
            console.error('Error deleting food:', error);
            this.showAlert('Error deleting food: ' + error.message, 'error');
        }
    }
    
    async deleteActivity(index, activityId) {
        if (!confirm('Are you sure you want to delete this activity?')) return;
        
        try {
            // If activity has an ID (exists in database), delete from server
            if (activityId && activityId !== 'null' && activityId !== null) {
                const response = await fetch(normalizedBaseUrl + 'calorie/deleteActivityItem', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({
                        activity_id: activityId
                    })
                });
                
                const result = await response.json();
                if (result.status !== 'success') {
                    throw new Error(result.message || 'Failed to delete activity');
                }
            }
            
            // Remove from local array
            this.activities.splice(index, 1);
            this.updateActivityList();
            this.calculateTotalFromLists();
            this.showAlert('Activity deleted', 'success');
            
        } catch (error) {
            console.error('Error deleting activity:', error);
            this.showAlert('Error deleting activity: ' + error.message, 'error');
        }
    }
    
    async saveTargets() {
        try {
            const intakeTarget = document.getElementById('targetCalorieInput')?.value;
            const burnedTarget = document.getElementById('burnedTargetInput')?.value;
            
            const response = await fetch(normalizedBaseUrl + 'calorie/updateTargets', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    client_id: this.clientId,
                    intake_target: intakeTarget,
                    burned_target: burnedTarget,
                    date: this.currentDate
                })
            });
            
            const result = await response.json();
            
            if (result.status === 'success') {
                this.showAlert('Targets updated successfully', 'success');
            } else {
                throw new Error(result.message || 'Failed to update targets');
            }
            
        } catch (error) {
            console.error('Error saving targets:', error);
            this.showAlert('Error saving targets: ' + error.message, 'error');
        }
    }
    
    calculateTotalFromLists() {
        const totalIntake = this.foods.reduce((sum, food) => {
            const calories = food.calories || 0;
            const quantity = food.quantity || 1;
            return sum + (calories * quantity);
        }, 0);
        
        const totalBurned = this.activities.reduce((sum, activity) => {
            return sum + (activity.calories || 0);
        }, 0);
        
        const todayInput = document.getElementById('todayCalorieInput');
        const burnedInput = document.getElementById('todayBurnedInput');
        
        if (todayInput) todayInput.value = Math.round(totalIntake);
        if (burnedInput) burnedInput.value = Math.round(totalBurned);
        
        this.updateCalculations();
    }
    
    resetCalorieData() {
        if (confirm('Are you sure you want to reset all calorie data for this date?')) {
            this.foods = [];
            this.activities = [];
            
            const todayInput = document.getElementById('todayCalorieInput');
            const burnedInput = document.getElementById('todayBurnedInput');
            const targetInput = document.getElementById('targetCalorieInput');
            const burnedTargetInput = document.getElementById('burnedTargetInput');
            
            if (todayInput) todayInput.value = 0;
            if (burnedInput) burnedInput.value = 0;
            if (targetInput) targetInput.value = 2000;
            if (burnedTargetInput) burnedTargetInput.value = 500;
            
            this.updateFoodList();
            this.updateActivityList();
            this.updateCalculations();
            this.showAlert('Data reset locally (click Save to update database)', 'success');
        }
    }
    
    async saveCalorieData() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoading();
        
        try {
            const response = await fetch(normalizedBaseUrl + 'calorie/saveCalorieData', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    client_id: this.clientId,
                    date: this.currentDate,
                    intake_target: document.getElementById('targetCalorieInput')?.value || 2000,
                    burned_target: document.getElementById('burnedTargetInput')?.value || 500,
                    foods: JSON.stringify(this.foods.filter(f => f.is_new)),
                    activities: JSON.stringify(this.activities.filter(a => a.is_new))
                })
            });
            
            const result = await response.json();
            
            if (result.status === 'success') {
                this.showAlert('Calorie data saved successfully!', 'success');
                
                // Clear the is_new flags after successful save
                this.foods.forEach(f => delete f.is_new);
                this.activities.forEach(a => delete a.is_new);
                
                // Reload data to show updated state
                setTimeout(() => {
                    this.loadCalorieData();
                }, 1000);
            } else {
                throw new Error(result.message || 'Failed to save data');
            }
            
        } catch (error) {
            console.error('Error saving data:', error);
            this.showAlert('Error saving data: ' + error.message, 'error');
        } finally {
            this.isLoading = false;
            this.hideLoading();
        }
    }
    
    showAlert(message, type) {
        // Remove existing alerts
        const existingAlert = document.querySelector('.calorie-alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        const alert = document.createElement('div');
        alert.className = `alert alert-${type === 'success' ? 'success' : 'danger'} calorie-alert`;
        alert.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
        alert.innerHTML = `
            <button type="button" class="close" onclick="this.parentElement.remove()">
                <span>&times;</span>
            </button>
            <strong>${type === 'success' ? '✓ Success!' : '✗ Error!'}</strong> ${message}
        `;
        
        document.body.appendChild(alert);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
    
    showLoading() {
        const modal = document.querySelector('#calorieGraphModal .modal-content');
        if (!modal) return;
        
        let overlay = document.getElementById('calorieLoadingOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'calorieLoadingOverlay';
            overlay.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.9);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
            `;
            overlay.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3">Loading calorie data...</p>
                </div>
            `;
            
            modal.appendChild(overlay);
        }
    }
    
    hideLoading() {
        const overlay = document.getElementById('calorieLoadingOverlay');
        if (overlay) {
            overlay.remove();
        }
    }
}

// Global functions for button onclick events
function changeCalorieTimeRange(days) {
    if (window.calorieTracker) {
        window.calorieTracker.changeTimeRange(days);
    }
}

function loadCalorieData() {
    if (window.calorieTracker) {
        window.calorieTracker.loadCalorieData();
    } else {
        console.error('Calorie tracker not initialized');
    }
}

function addFoodLogEntry() {
    if (window.calorieTracker) {
        window.calorieTracker.addFoodLogEntry();
    }
}

function addActivityLogEntry() {
    if (window.calorieTracker) {
        window.calorieTracker.addActivityLogEntry();
    }
}

function saveFoodLogEntry() {
    if (window.calorieTracker) {
        window.calorieTracker.saveFoodLogEntry();
    }
}

function cancelFoodLogEntry() {
    if (window.calorieTracker) {
        window.calorieTracker.cancelFoodLogEntry();
    }
}

function saveActivityLogEntry() {
    if (window.calorieTracker) {
        window.calorieTracker.saveActivityLogEntry();
    }
}

function cancelActivityEntry() {
    if (window.calorieTracker) {
        window.calorieTracker.cancelActivityEntry();
    }
}

function resetCalorieData() {
    if (window.calorieTracker) {
        window.calorieTracker.resetCalorieData();
    }
}

function saveCalorieData() {
    if (window.calorieTracker) {
        window.calorieTracker.saveCalorieData();
    }
}

// Initialize calorie tracker when modal is shown
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Setting up calorie modal');
    
    // Get client ID from the modal data attribute
    const modalElement = document.getElementById('calorieGraphModal');
    let clientId = modalElement?.getAttribute('data-client-id') || <?= $clientDetails[0]->client_id ?? 1; ?>;
    
    console.log('Client ID:', clientId);
    
    // Initialize when modal is shown
    $('#calorieGraphModal').on('shown.bs.modal', function() {
        console.log('Calorie modal shown, initializing tracker...');
        
        try {
            window.calorieTracker = new CalorieTrackerModal(clientId);
            
            // Set max date to today
            const today = new Date().toISOString().split('T')[0];
            const dateInput = document.getElementById('calorieDate');
            if (dateInput) {
                dateInput.max = today;
                dateInput.value = today;
            }
            
            console.log('Calorie tracker initialized successfully');
        } catch (error) {
            console.error('Error initializing calorie tracker:', error);
        }
    });
    
    // Clean up when modal is hidden
    $('#calorieGraphModal').on('hidden.bs.modal', function() {
        console.log('Calorie modal hidden, cleaning up...');
        
        if (window.calorieTracker && window.calorieTracker.chart) {
            try {
                window.calorieTracker.chart.destroy();
            } catch (e) {
                console.log('Error destroying chart:', e);
            }
        }
        window.calorieTracker = null;
    });
});
</script>

<style>
/* Additional styles for calorie modal */
.metric-stats.calorie-stats {
    display: flex;
    justify-content: space-between;
    margin: 20px 0;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.metric-stat-item.calorie-stat {
    text-align: center;
    flex: 1;
    padding: 0 10px;
}

.metric-stat-value input.metric-input {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 5px;
    text-align: center;
    font-weight: bold;
    font-size: 16px;
}

.metric-stat-value span {
    margin-left: 5px;
    color: #666;
    font-size: 14px;
}

.metric-stat-label {
    margin-top: 5px;
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
}

.calorie-progress .progress {
    height: 10px;
    margin-top: 5px;
}

.food-list, .activity-list {
    border: 1px solid #e9ecef;
    border-radius: 5px;
    padding: 10px;
    background: #fff;
}

.food-log-item:hover, .activity-log-item:hover {
    background: #f8f9fa;
}

.net-value {
    transition: color 0.3s ease;
}

/* Chart styling */
#calorieChart {
    min-height: 350px;
}

#calorieChart canvas {
    width: 100% !important;
    height: 350px !important;
}

/* Loading overlay */
#calorieLoadingOverlay {
    border-radius: 8px;
}

/* Alert animations */
.calorie-alert {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>