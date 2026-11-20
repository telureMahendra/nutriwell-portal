<!-- BMI Modal with Edit/Add functionality -->
<div class="modal fade metric-modal bmi-modal" id="bmiGraphModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #9b59b6, #8e44ad); color: white; border-bottom: none; padding: 20px; border-radius: 6px 6px 0 0;">
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1; text-shadow: none; margin-top: -5px;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" style="font-weight: 700; margin: 0; font-size: 18px;">
                    <i class="fa fa-heartbeat" style="margin-right: 8px;"></i> BMI Tracking <span style="opacity: 0.8; font-weight: 400; font-size: 16px;">| <?= htmlspecialchars($clientDetails[0]->client_name ?? 'Client'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <!-- Stats Section -->
                <div class="row mb-4 mt-3">
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-info" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                            <i class="fa fa-user-md bsc-icon"></i>
                            <h2 id="currentBMI"><?= isset($bmi_data['current']) ? number_format($bmi_data['current'], 1) : '0.0'; ?></h2>
                            <p>Current BMI</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-warning" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">
                            <i class="fa fa-flag-checkered bsc-icon"></i>
                            <h2 id="startBMI"><?= isset($bmi_data['start']) ? number_format($bmi_data['start'], 1) : '0.0'; ?></h2>
                            <p>Start BMI</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-success">
                            <i class="fa fa-bullseye bsc-icon"></i>
                            <h2 id="targetBMI"><?= isset($bmi_data['target']) ? number_format($bmi_data['target'], 1) : '22.0'; ?></h2>
                            <p>Target BMI</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="burned-stat-card bsc-danger">
                            <i class="fa fa-heartbeat bsc-icon"></i>
                            <h2 id="bmiCategoryStat" style="font-size: 20px; margin-top: 5px;"><?= $bmi_data['category'] ?? 'No Data'; ?></h2>
                            <p>Category</p>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mb-4" style="background: #f8f9fa; padding: 20px; border-radius: 12px; box-shadow: inset 0 2px 5px rgba(0,0,0,0.03); margin-bottom: 25px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-weight: 700; color: #2d3436;">
                        <span><i class="fa fa-heartbeat" style="color: #9b59b6; margin-right: 6px;"></i> Progress to Target</span>
                        <span id="bmiProgressText" style="color: #9b59b6;"><?= isset($bmi_data['progress']) ? $bmi_data['progress'] : 0; ?>%</span>
                    </div>
                    <div class="progress" style="height: 14px; border-radius: 10px; background-color: #dfe6e9; box-shadow: none; margin-bottom: 10px;">
                        <div class="progress-bar progress-bar-striped active" role="progressbar" 
                             style="width: <?= isset($bmi_data['progress']) ? $bmi_data['progress'] : 0; ?>%; background: linear-gradient(90deg, #9b59b6, #8e44ad); border-radius: 10px;">
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; color: #636e72;" class="bmi-progress">
                        <small>Start: <?= isset($bmi_data['start']) ? number_format($bmi_data['start'], 1) : '0.0'; ?></small>
                        <small>Current: <?= isset($bmi_data['current']) ? number_format($bmi_data['current'], 1) : '0.0'; ?></small>
                        <small>Target: <?= isset($bmi_data['target']) ? number_format($bmi_data['target'], 1) : '22.0'; ?></small>
                    </div>
                </div>
                <!-- Time Range Selector -->
                <div class="time-range-selector text-center" style="margin-bottom: 25px;">
                    <div class="btn-group" style="background: #f8f9fa; border-radius: 4px; padding: 4px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.05); display: inline-block;">
                        <button type="button" class="btn btn-sm time-range-btn bmi-time-btn active" data-days="180" onclick="changeBmiTimeRange(180)" style="border: none; background: #9b59b6; color: white; font-weight: 500; padding: 6px 15px; border-radius: 3px; box-shadow: 0 2px 4px rgba(155, 89, 182, 0.3);">6 Months</button>
                        <button type="button" class="btn btn-sm time-range-btn bmi-time-btn" data-days="365" onclick="changeBmiTimeRange(365)" style="border: none; background: transparent; color: #555; font-weight: 500; padding: 6px 15px; border-radius: 3px;">1 Year</button>
                        <button type="button" class="btn btn-sm time-range-btn bmi-time-btn" data-days="730" onclick="changeBmiTimeRange(730)" style="border: none; background: transparent; color: #555; font-weight: 500; padding: 6px 15px; border-radius: 3px;">2 Years</button>
                    </div>
                </div>

                <!-- BMI Chart -->
                <div class="metric-graph-container" style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <h5 style="color: #9b59b6; margin-bottom: 15px; font-weight: bold;">
                        <i class="fa fa-chart-line"></i> BMI Progress Chart
                    </h5>
                    <div id="bmiChart" style="height: 300px;"></div>
                </div>

                <!-- BMI Categories Reference -->
                <div class="bmi-categories" style="margin-top: 20px;">

                    <h5 style="color: #9b59b6; margin-bottom: 15px; font-weight: bold;">
                        <i class="fa fa-info-circle"></i> BMI Categories
                    </h5>
                    <div class="category-list">
                        <div class="category-item" style="display: flex; justify-content: space-between; padding: 8px; background: <?= (($bmi_data['current'] ?? 0) < 18.5) ? '#e3f2fd' : '#f8f9fa'; ?>; border-radius: 5px; margin-bottom: 5px;">
                            <span>Underweight</span>
                            <span>&lt; 18.5</span>
                        </div>
                        <div class="category-item" style="display: flex; justify-content: space-between; padding: 8px; background: <?= (($bmi_data['current'] ?? 0) >= 18.5 && ($bmi_data['current'] ?? 0) < 25) ? '#e8f5e8' : '#f8f9fa'; ?>; border-radius: 5px; margin-bottom: 5px;">
                            <span style="color: #27ae60;"><strong>Normal</strong></span>
                            <span>18.5 - 24.9</span>
                        </div>
                        <div class="category-item" style="display: flex; justify-content: space-between; padding: 8px; background: <?= (($bmi_data['current'] ?? 0) >= 25 && ($bmi_data['current'] ?? 0) < 30) ? '#fff3cd' : '#f8f9fa'; ?>; border-radius: 5px; margin-bottom: 5px;">
                            <span>Overweight</span>
                            <span>25 - 29.9</span>
                        </div>
                        <div class="category-item" style="display: flex; justify-content: space-between; padding: 8px; background: <?= (($bmi_data['current'] ?? 0) >= 30) ? '#f8d7da' : '#f8f9fa'; ?>; border-radius: 5px; margin-bottom: 5px;">
                            <span>Obesity</span>
                            <span>≥ 30</span>
                        </div>
                    </div>
                </div>

                <!-- Recent BMI Records -->
                <div id="bmiHistoryContainer" style="margin-top: 20px;">
                    <h5 style="color: #9b59b6; margin-bottom: 15px; font-weight: bold;">
                        <i class="fa fa-history"></i> Recent BMI Records
                    </h5>
                    <div class="table-responsive" style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <table class="table table-hover" style="margin-bottom: 0;">
                            <thead style="background: #f8f9fa;">
                                <tr>
                                    <th style="border-bottom: none;">Date</th>
                                    <th style="border-bottom: none;">Weight (kg)</th>
                                    <th style="border-bottom: none;">Height</th>
                                    <th style="border-bottom: none;">Calories</th>
                                    <th style="border-bottom: none;">BMI</th>
                                    <th style="border-bottom: none;">Category</th>
                                </tr>
                            </thead>
                            <tbody id="bmiHistoryBody">
                                <!-- Will be loaded via AJAX -->
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
// Global variables - DECLARE ONLY ONCE
let bmiChart = null;
let currentBmiDays = 180;
currentClientId = <?= $clientDetails[0]->client_id ?? 0; ?>;

// Simple notification function
function showNotification(type, message) {
    // Remove any existing notifications
    $('.custom-notification').remove();
    
    // Create notification element
    const bgColor = type === 'success' ? '#27ae60' : '#e74c3c';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const notification = $(`
        <div class="custom-notification" 
             style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; 
                    background: ${bgColor}; color: white; padding: 15px 20px; border-radius: 5px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15); animation: slideIn 0.3s ease;">
            <i class="fa ${icon} mr-2"></i>
            <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> 
            <span style="margin-left: 5px;">${message}</span>
            <button type="button" class="close" style="color: white; opacity: 0.8; position: absolute; top: 10px; right: 10px;" 
                    onclick="$(this).parent().remove()">
                <span>&times;</span>
            </button>
        </div>
    `);
    
    // Add to body
    $('body').append(notification);
    
    // Auto remove after 5 seconds
    setTimeout(function() {
        notification.fadeOut(300, function() {
            $(this).remove();
        });
    }, 5000);
}

// Helper functions
function getBmiCategory(bmi) {
    bmi = parseFloat(bmi);
    if (isNaN(bmi) || bmi === 0) return 'No Data';
    if (bmi < 18.5) return 'Underweight';
    if (bmi < 25) return 'Normal';
    if (bmi < 30) return 'Overweight';
    return 'Obesity';
}

function getBmiColor(bmi) {
    bmi = parseFloat(bmi);
    if (isNaN(bmi) || bmi === 0) return '#95a5a6';
    if (bmi < 18.5) return '#3498db';
    if (bmi < 25) return '#27ae60';
    if (bmi < 30) return '#f39c12';
    return '#e74c3c';
}

function formatDate(dateString) {
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;
        
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    } catch (e) {
        return dateString;
    }
}

function calculateBMIFromRecord(record) {
    if (!record || !record.height || !record.weight) return 0;
    
    let heightInMeters;
    
    switch(record.height_unit) {
        case 'CM':
            heightInMeters = record.height / 100;
            break;
        case 'INCH':
            heightInMeters = record.height * 0.0254;
            break;
        case 'FIT':
            heightInMeters = record.height * 0.3048;
            break;
        default:
            heightInMeters = record.height / 100;
    }
    
    if (heightInMeters > 0 && record.weight > 0) {
        return parseFloat((record.weight / (heightInMeters * heightInMeters)).toFixed(2));
    }
    return 0;
}

function showBmiForm() {
    $('#bmiFormContainer').slideDown();
    $('#bmiHistoryContainer').slideUp();
    $('html, body').animate({
        scrollTop: $('#bmiFormContainer').offset().top - 20
    }, 300);
}

function hideBmiForm() {
    $('#bmiFormContainer').slideUp();
    $('#bmiHistoryContainer').slideDown();
}

function resetBmiForm() {
    $('#bmiForm')[0].reset();
    $('#bmi_id').val('');
    $('#date-bmi').val('<?= date("Y-m-d"); ?>');
    $('#calories-bmi').val('');
    $('#formTitle').text('Add BMI Record');
    $('#calculatedBmi').text('--');
    $('#bmiCategoryLabel').html('');
}

function validateBmiForm() {
    const weight = parseFloat($('#weight-bmi').val());
    const height = parseFloat($('#height-bmi').val());
    const date = $('#date-bmi').val();
    
    console.log('Validating form:', { 
        weight: weight, 
        height: height, 
        date: date,
        weightRaw: $('#weight-bmi').val(),
        heightRaw: $('#height-bmi').val()
    });
    
    if (isNaN(weight) || weight <= 0) {
        showNotification('error', 'Please enter a valid weight (must be greater than 0)');
        $('#weight-bmi').focus();
        return false;
    }
    
    if (isNaN(height) || height <= 0) {
        showNotification('error', 'Please enter a valid height (must be greater than 0)');
        $('#height-bmi').focus();
        return false;
    }
    
    if (!date) {
        showNotification('error', 'Please select a date');
        $('#date-bmi').focus();
        return false;
    }
    
    // Additional validation for height based on unit
    const unit = $('#height_unit-bmi').val();
    if (unit === 'CM' && height > 300) {
        showNotification('error', 'Height in CM should be less than 300cm');
        $('#height-bmi').focus();
        return false;
    }
    
    if (unit === 'INCH' && height > 120) {
        showNotification('error', 'Height in inches should be less than 120 inches');
        $('#height-bmi').focus();
        return false;
    }
    
    if (unit === 'FIT' && height > 10) {
        showNotification('error', 'Height in feet should be less than 10 feet');
        $('#height-bmi').focus();
        return false;
    }
    
    return true;
}

function calculateRealTimeBMI() {
    const weight = parseFloat($('#weight-bmi').val()) || 0;
    const height = parseFloat($('#height-bmi').val()) || 0;
    const unit = $('#height_unit-bmi').val();
    
    console.log('Calculating real-time BMI:', { weight, height, unit });
    
    if (weight > 0 && height > 0) {
        let heightInMeters;
        
        switch(unit) {
            case 'CM':
                heightInMeters = height / 100;
                break;
            case 'INCH':
                heightInMeters = height * 0.0254;
                break;
            case 'FIT':
                heightInMeters = height * 0.3048;
                break;
            default:
                heightInMeters = height / 100;
        }
        
        if (heightInMeters > 0) {
            const bmi = weight / (heightInMeters * heightInMeters);
            const roundedBmi = parseFloat(bmi.toFixed(2));
            const category = getBmiCategory(roundedBmi);
            const color = getBmiColor(roundedBmi);
            
            $('#calculatedBmi').text(roundedBmi);
            $('#bmiCategoryLabel').html(`
                <span class="badge" style="background-color: ${color}; color: white;">
                    ${category}
                </span>
            `);
        }
    } else {
        $('#calculatedBmi').text('--');
        $('#bmiCategoryLabel').html('');
    }
}

// Initialize when document is ready
$(document).ready(function() {
    console.log('Document ready - BMI modal script loaded');
    console.log('Client ID:', currentClientId);
    
    // Debug: Check if button exists
    if ($('#addNewBmiBtn').length) {
        console.log('Add New BMI button found in DOM');
    } else {
        console.error('Add New BMI button NOT FOUND in DOM');
    }
    
    // Initialize when modal opens
    $('#bmiGraphModal').on('shown.bs.modal', function (e) {
        if (e.target !== this) return;
        console.log('BMI Modal opened for client:', currentClientId);
        loadBmiHistory();
        initializeBMIChart();
    });
    
    // BMI form buttons
    $('#addNewBmiBtn').click(function() {
        console.log('Add New BMI button clicked');
        showBmiForm();
        resetBmiForm();
    });
    
    $('#cancelBmiBtn').click(function() {
        hideBmiForm();
    });
    
    // BMI form submission
    $('#bmiForm').submit(function(e) {
        console.log('BMI Form submitted');
        e.preventDefault();
        saveBmiRecord();
    });
    
    // Real-time BMI calculation - UPDATED SELECTORS
    $('#weight-bmi, #height-bmi, #height_unit-bmi').on('input change', function() {
        calculateRealTimeBMI();
    });
    
    // Debug button values
    $('#saveBmiBtn').click(function() {
        console.log('Save button clicked directly');
        console.log('Weight value:', $('#weight-bmi').val());
        console.log('Height value:', $('#height-bmi').val());
    });
    
    // Clean up when modal closes
    $('#bmiGraphModal').on('hidden.bs.modal', function() {
        if (bmiChart) {
            try {
                bmiChart.destroy();
            } catch (e) {
                console.error('Error destroying chart:', e);
            }
            bmiChart = null;
        }
    });
});

function changeBmiTimeRange(days) {
    // Update active button UI
    $('.bmi-time-btn').removeClass('active').css({
        'background': 'transparent',
        'color': '#555',
        'box-shadow': 'none'
    });
    $(`.bmi-time-btn[data-days="${days}"]`).addClass('active').css({
        'background': '#9b59b6',
        'color': 'white',
        'box-shadow': '0 2px 4px rgba(155, 89, 182, 0.3)'
    });
    
    currentBmiDays = days;
    initializeBMIChart(days);
}

// Load BMI History - UPDATED WITH DEBUG
function loadBmiHistory() {
    console.log('Loading BMI history for client:', currentClientId);
    
    $.ajax({
        url: '<?= base_url("bmi/getBmiHistory/"); ?>' + currentClientId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('BMI history response:', response);
            console.log('Response success:', response.success);
            console.log('Data received:', response.data);
            console.log('Data type:', typeof response.data);
            console.log('Data length:', response.data ? response.data.length : 0);
            
            if (response.success && response.data) {
                renderBmiHistory(response.data);
            } else {
                console.log('No valid data or success false');
                $('#bmiHistoryBody').html('<tr><td colspan="7" class="text-center text-muted">No BMI records found. Add your first record!</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading BMI history:', error);
            console.error('Response:', xhr.responseText);
            console.error('Status:', xhr.status);
            $('#bmiHistoryBody').html('<tr><td colspan="7" class="text-center text-danger">Error loading BMI history</td></tr>');
        }
    });
}

// Render BMI History Table
// Render BMI History Table - FIXED VERSION
function renderBmiHistory(data) {
    let html = '';
    
    if (data.length === 0) {
        html = '<tr><td colspan="7" class="text-center text-muted">No BMI records found. Add your first record!</td></tr>';
        $('#bmiHistoryBody').html(html);
        return;
    }
    
    // IMPORTANT: Sort data by date ASCENDING (oldest to newest) for proper calculations
    data.sort((a, b) => new Date(a.date) - new Date(b.date));
    
    console.log('Sorted data for calculations:', data);
    
    // Calculate statistics from the data
    let currentBmi = 0;
    let startBmi = 0;
    let totalBmi = 0;
    
    data.forEach(function(record, index) {
        let bmiValue = record.bmi_value || calculateBMIFromRecord(record);
        let category = getBmiCategory(bmiValue);
        
        // First record (oldest) is the start
        if (index === 0) {
            startBmi = bmiValue;
            console.log('Start BMI (oldest record):', startBmi, 'Date:', record.date);
        }
        
        // Last record (newest) is the current
        if (index === data.length - 1) {
            currentBmi = bmiValue;
            console.log('Current BMI (newest record):', currentBmi, 'Date:', record.date);
        }
        
        totalBmi += bmiValue;
        
        // But render in reverse order for display (newest first)
        html = `
            <tr>
                <td>${formatDate(record.date)}</td>
                <td>${parseFloat(record.weight).toFixed(1)} kg</td>
                <td>${parseFloat(record.height).toFixed(1)} ${record.height_unit}</td>
                <td>${record.calories || '0'}</td>
                <td>
                    <span class="badge" style="background-color: ${getBmiColor(bmiValue)}; color: white;">
                        ${parseFloat(bmiValue).toFixed(1)}
                    </span>
                </td>
                <td>${category}</td>
            </tr>
        ` + html; // Prepend to show newest first
    });
    
    $('#bmiHistoryBody').html(html);
    
    // Update statistics
    const avgBmi = totalBmi / data.length;
    const targetBmi = 22.0;
    
    console.log('For progress calculation:');
    console.log('Start BMI (oldest):', startBmi);
    console.log('Current BMI (newest):', currentBmi);
    console.log('Target BMI:', targetBmi);
    
    // Calculate progress - SIMPLIFIED FIX
    let progress = 0;
    
    if (startBmi > targetBmi) {
        // Need to lose weight (BMI needs to go DOWN)
        const totalChangeNeeded = startBmi - targetBmi;
        const progressMade = startBmi - currentBmi;
        
        console.log('Need to lose weight:');
        console.log('Total change needed:', totalChangeNeeded);
        console.log('Progress made:', progressMade);
        
        if (totalChangeNeeded > 0) {
            progress = (progressMade / totalChangeNeeded) * 100;
            console.log('Raw progress:', progress);
            
            // If we overshoot (go below target), show 100%
            if (currentBmi <= targetBmi) {
                progress = 100;
            }
            
            progress = Math.max(0, Math.min(100, Math.round(progress)));
        }
    } else if (startBmi < targetBmi) {
        // Need to gain weight (BMI needs to go UP)
        const totalChangeNeeded = targetBmi - startBmi;
        const progressMade = currentBmi - startBmi;
        
        console.log('Need to gain weight:');
        console.log('Total change needed:', totalChangeNeeded);
        console.log('Progress made:', progressMade);
        
        if (totalChangeNeeded > 0) {
            progress = (progressMade / totalChangeNeeded) * 100;
            console.log('Raw progress:', progress);
            
            // If we overshoot (go above target), show 100%
            if (currentBmi >= targetBmi) {
                progress = 100;
            }
            
            progress = Math.max(0, Math.min(100, Math.round(progress)));
        }
    } else {
        // Already at target
        progress = 100;
    }
    
    console.log('Final progress:', progress + '%');
    
    // Update UI
    $('#currentBMI').text(currentBmi.toFixed(1));
    $('#startBMI').text(startBmi.toFixed(1));
    $('#avgBMI').text(avgBmi.toFixed(1));
    $('#bmiCategoryStat').text(getBmiCategory(currentBmi));
    
    // Update progress bar - with animation
    $('.progress-bar').css('width', '0%'); // Reset first
    setTimeout(function() {
        $('.progress-bar').css('width', progress + '%');
        $('#bmiProgressText').text(progress + '%');
    }, 100);
    
    // Update progress labels
    $('.bmi-progress small:first-child').text('Start: ' + startBmi.toFixed(1));
    $('.bmi-progress small:nth-child(2)').text('Current: ' + currentBmi.toFixed(1));
    $('.bmi-progress small:last-child').text('Target: ' + targetBmi.toFixed(1));
    
    // Update records count
    $('.bmi-progress .text-muted').text(data.length + ' records tracked');
    
    // Update category highlights
    updateCategoryHighlights(currentBmi);
}
// Update BMI category highlights based on current BMI
function updateCategoryHighlights(currentBmi) {
    // Reset all category backgrounds to default
    $('.category-item').css('background-color', '#f8f9fa');
    
    // Parse BMI to ensure it's a number
    currentBmi = parseFloat(currentBmi);
    
    if (isNaN(currentBmi) || currentBmi === 0) {
        console.log('No valid BMI data for category highlights');
        return;
    }
    
    console.log('Updating category highlights for BMI:', currentBmi);
    
    // Determine which category the current BMI falls into
    if (currentBmi < 18.5) {
        // Underweight - blue highlight
        $('.category-item:first-child').css('background-color', '#e3f2fd');
        console.log('Highlighting: Underweight');
    } 
    else if (currentBmi >= 18.5 && currentBmi < 25) {
        // Normal - green highlight
        $('.category-item:nth-child(2)').css('background-color', '#e8f5e8');
        console.log('Highlighting: Normal');
    } 
    else if (currentBmi >= 25 && currentBmi < 30) {
        // Overweight - yellow highlight
        $('.category-item:nth-child(3)').css('background-color', '#fff3cd');
        console.log('Highlighting: Overweight');
    } 
    else if (currentBmi >= 30) {
        // Obesity - red highlight
        $('.category-item:nth-child(4)').css('background-color', '#f8d7da');
        console.log('Highlighting: Obesity');
    }
    
    // Also update the text color for better visibility
    $('.category-item').css('color', '#333');
    
    // Make the active category text bold
    if (currentBmi < 18.5) {
        $('.category-item:first-child').css('font-weight', 'bold');
    } else if (currentBmi < 25) {
        $('.category-item:nth-child(2)').css('font-weight', 'bold');
    } else if (currentBmi < 30) {
        $('.category-item:nth-child(3)').css('font-weight', 'bold');
    } else {
        $('.category-item:nth-child(4)').css('font-weight', 'bold');
    }
}

// Calculate progress - FIXED VERSION
function calculateProgress(startBmi, currentBmi, targetBmi) {
    if (startBmi <= 0 || targetBmi <= 0) return 0;
    
    // Determine direction
    const needToLose = startBmi > targetBmi; // Need to go down
    const needToGain = startBmi < targetBmi; // Need to go up
    
    if (needToLose) {
        // Going from higher to lower (losing weight)
        const totalChangeNeeded = startBmi - targetBmi;
        const progressMade = startBmi - currentBmi;
        
        if (totalChangeNeeded <= 0) return 0;
        
        let progress = (progressMade / totalChangeNeeded) * 100;
        
        // If we overshoot the target (go below), show 100%
        if (currentBmi <= targetBmi) return 100;
        
        return Math.max(0, Math.min(100, Math.round(progress)));
    } 
    else if (needToGain) {
        // Going from lower to higher (gaining weight)
        const totalChangeNeeded = targetBmi - startBmi;
        const progressMade = currentBmi - startBmi;
        
        if (totalChangeNeeded <= 0) return 0;
        
        let progress = (progressMade / totalChangeNeeded) * 100;
        
        // If we overshoot the target (go above), show 100%
        if (currentBmi >= targetBmi) return 100;
        
        return Math.max(0, Math.min(100, Math.round(progress)));
    } 
    else {
        // Already at target
        return 100;
    }
}
// Initialize BMI Chart
// Initialize BMI Chart - UPDATED
function initializeBMIChart(days) {
    days = days || currentBmiDays || 180;
    console.log('Initializing BMI chart for client:', currentClientId, 'days:', days);

    // Destroy existing chart if it exists
    if (bmiChart) {
        bmiChart.destroy();
    }

    // Show loading indicator
    $('#bmiChart').html('<div class="text-center" style="padding: 20px;"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading chart...</span></div></div>');

    // Load chart data via AJAX
    $.ajax({
        url: '<?= base_url("bmi/getBmiChartData/"); ?>' + currentClientId + '?days=' + days,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Chart data AJAX response:', response);
            
            if (response.success && response.data && response.data.series && response.data.series[0] && response.data.series[0].length > 0) {
                renderBmiChart(response.data);
            } else {
                console.log('No chart data available from AJAX');
                showNoChartDataMessage();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading chart data:', error);
            console.error('Response:', xhr.responseText);
            showNoChartDataMessage();
        }
    });
}

// Render BMI Chart with AJAX data
function renderBmiChart(chartData) {
    console.log('Rendering chart with AJAX data:', chartData);
    
    // Create chart options
    const bmiOptions = {
        series: [{
            name: 'BMI',
            data: chartData.series[0]
        }],
        chart: {
            height: 300,
            type: 'line',
            zoom: {
                enabled: true
            },
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
            }
        },
        colors: ['#9b59b6'],
        stroke: {
            curve: 'smooth',
            width: 3
        },
        markers: {
            size: 5,
            hover: {
                size: 7
            }
        },
        xaxis: {
            categories: chartData.categories || [],
            title: {
                text: 'Month',
                style: {
                    fontSize: '14px',
                    fontWeight: 'bold',
                    color: '#333'
                }
            },
            labels: {
                style: {
                    fontSize: '12px',
                    colors: '#666'
                }
            }
        },
        yaxis: {
            title: {
                text: 'BMI Value',
                style: {
                    fontSize: '14px',
                    fontWeight: 'bold',
                    color: '#333'
                }
            },
            labels: {
                formatter: function(val) {
                    return val.toFixed(1);
                },
                style: {
                    fontSize: '12px',
                    colors: '#666'
                }
            },
            min: function(min) {
                return Math.max(15, Math.floor(min) - 2);
            },
            max: function(max) {
                return Math.min(35, Math.ceil(max) + 2);
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    const category = getBmiCategory(val);
                    const color = getBmiColor(val);
                    return `<div>
                        <strong style="color: ${color}">${val.toFixed(1)}</strong>
                        <div style="margin-top: 5px; font-size: 12px; color: #666">${category}</div>
                    </div>`;
                }
            }
        },
        grid: {
            borderColor: '#f1f1f1',
            strokeDashArray: 5
        }
    };
    
    try {
        // Check if ApexCharts is loaded
        if (typeof ApexCharts === 'undefined') {
            console.error('ApexCharts is not loaded!');
            $('#bmiChart').html('<div class="text-center text-danger">Chart library not loaded. Please check console.</div>');
            return;
        }
        
        bmiChart = new ApexCharts(document.querySelector("#bmiChart"), bmiOptions);
        bmiChart.render();
        console.log('BMI chart rendered successfully');
        
    } catch (error) {
        console.error('Error rendering chart:', error);
        showNoChartDataMessage();
    }
}

// Show "No data" message
function showNoChartDataMessage() {
    $('#bmiChart').html('<div class="text-center" style="padding: 50px; color: #666;"><i class="fa fa-chart-bar fa-3x mb-3" style="color: #ddd;"></i><br>No BMI data available for chart<br><small>Add more BMI records to see your progress chart</small></div>');
}

// Save BMI Record
function saveBmiRecord() {
    console.log('saveBmiRecord called');
    
    // Validate form first
    if (!validateBmiForm()) {
        console.log('Form validation failed');
        return;
    }
    
    const formData = $('#bmiForm').serialize();
    console.log('Saving BMI data:', formData);
    
    // Show what data is being sent
    const weight = $('#weight-bmi').val();
    const height = $('#height-bmi').val();
    console.log('Weight:', weight, 'Height:', height);
    
    // Disable save button to prevent multiple clicks
    const saveBtn = $('#saveBmiBtn');
    const originalText = saveBtn.html();
    saveBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
    
    $.ajax({
        url: '<?= base_url("bmi/saveBmi"); ?>',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            console.log('Save response:', response);
            
            if (response.success) {
                // Show success message
                showNotification('success', response.message || 'BMI record saved successfully');
                
                // Update UI
                if (response.bmi_value) {
                    $('#calculatedBmi').text(parseFloat(response.bmi_value).toFixed(1));
                    const category = response.bmi_category || getBmiCategory(response.bmi_value);
                    const color = getBmiColor(response.bmi_value);
                    $('#bmiCategoryLabel').html(`
                        <span class="badge" style="background-color: ${color}; color: white;">
                            ${category}
                        </span>
                    `);
                }
                
                // Reload history
                loadBmiHistory();
                
                // Hide form
                hideBmiForm();
                
                // Reset form
                resetBmiForm();
                
                // Refresh the chart
                initializeBMIChart();
                
            } else {
                // Show error message
                showNotification('error', response.message || 'Error saving BMI record');
                
                // Display validation errors if any
                if (response.errors) {
                    let errorMessages = '';
                    for (let error in response.errors) {
                        errorMessages += response.errors[error] + '<br>';
                    }
                    if (errorMessages) {
                        showNotification('error', errorMessages);
                    }
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Save error:', error);
            console.error('Response:', xhr.responseText);
            
            let errorMsg = 'Server error occurred. Please try again.';
            
            try {
                // Try to parse the error response
                if (xhr.responseText) {
                    const errorResponse = JSON.parse(xhr.responseText);
                    errorMsg = errorResponse.message || errorResponse.error || errorMsg;
                }
            } catch (e) {
                // If we can't parse JSON, use the raw error
                if (xhr.status === 404) {
                    errorMsg = 'Endpoint not found. Check if the route is correct.';
                } else if (xhr.status === 500) {
                    errorMsg = 'Internal server error. Please contact administrator.';
                } else {
                    errorMsg = error || 'Unknown error occurred';
                }
            }
            
            showNotification('error', errorMsg);
        },
        complete: function() {
            // Re-enable save button
            saveBtn.prop('disabled', false).html(originalText);
        }
    });
}

// Edit BMI Record - UPDATED SELECTORS
function editBmiRecord(bmiId) {
    console.log('Editing BMI record:', bmiId);
    
    $.ajax({
        url: '<?= base_url("bmi/getBmiRecord/"); ?>' + bmiId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Edit response:', response);
            if (response.success && response.data) {
                const record = response.data;
                
                // Populate form - UPDATED SELECTORS
                $('#bmi_id').val(record.bmi_id);
                $('#date-bmi').val(record.date);
                $('#weight-bmi').val(record.weight);
                $('#height-bmi').val(record.height);
                $('#height_unit-bmi').val(record.height_unit);
                $('#age-bmi').val(record.age || '');
                $('#calories-bmi').val(record.calories || '');
                
                // Update form title
                $('#formTitle').text('Edit BMI Record');
                
                // Show form
                showBmiForm();
                
                // Calculate and display BMI
                calculateRealTimeBMI();
            } else {
                showNotification('error', 'Error loading record data');
            }
        },
        error: function(xhr, status, error) {
            console.error('Edit error:', error);
            showNotification('error', 'Error loading record');
        }
    });
}

// Delete BMI Record
function deleteBmiRecord(bmiId) {
    if (!confirm('Are you sure you want to delete this BMI record? This action cannot be undone.')) {
        return;
    }
    
    console.log('Deleting BMI record:', bmiId);
    
    $.ajax({
        url: '<?= base_url("bmi/deleteBmi/"); ?>' + bmiId,
        type: 'DELETE',
        dataType: 'json',
        success: function(response) {
            console.log('Delete response:', response);
            if (response.success) {
                showNotification('success', response.message || 'BMI record deleted successfully');
                
                // Reload history
                loadBmiHistory();
                
                // Refresh the modal
                setTimeout(function() {
                    $('#bmiGraphModal').modal('hide');
                    setTimeout(function() {
                        $('#bmiGraphModal').modal('show');
                    }, 500);
                }, 1000);
                
            } else {
                showNotification('error', response.message || 'Error deleting record');
            }
        },
        error: function(xhr, status, error) {
            console.error('Delete error:', error);
            showNotification('error', 'Error deleting record');
        }
    });
}

// Manual BMI Calculation
function calculateManualBMI() {
    const heightUnit = prompt('Enter height unit (CM, INCH, or FIT):', 'CM').toUpperCase();
    if (!['CM', 'INCH', 'FIT'].includes(heightUnit)) {
        alert('Invalid height unit. Please use CM, INCH, or FIT.');
        return;
    }
    
    const weight = parseFloat(prompt('Enter weight in kg:', '70'));
    if (!weight || weight <= 0 || isNaN(weight)) {
        alert('Please enter a valid weight.');
        return;
    }
    
    const height = parseFloat(prompt(`Enter height in ${heightUnit}:`, heightUnit === 'CM' ? '170' : heightUnit === 'INCH' ? '67' : '5.6'));
    if (!height || height <= 0 || isNaN(height)) {
        alert('Please enter a valid height.');
        return;
    }
    
    // Calculate BMI
    let heightInMeters;
    switch(heightUnit) {
        case 'CM':
            heightInMeters = height / 100;
            break;
        case 'INCH':
            heightInMeters = height * 0.0254;
            break;
        case 'FIT':
            heightInMeters = height * 0.3048;
            break;
    }
    
    if (heightInMeters <= 0) {
        alert('Invalid height calculation.');
        return;
    }
    
    const bmi = (weight / (heightInMeters * heightInMeters)).toFixed(2);
    const category = getBmiCategory(bmi);
    const color = getBmiColor(bmi);
    
    const result = `
        <div style="text-align: left; padding: 10px;">
            <h4 style="color: #9b59b6; margin-bottom: 15px; border-bottom: 2px solid #9b59b6; padding-bottom: 10px;">
                <i class="fa fa-calculator"></i> BMI Calculation Result
            </h4>
            <div style="margin-bottom: 10px;">
                <strong>Weight:</strong> ${weight} kg
            </div>
            <div style="margin-bottom: 10px;">
                <strong>Height:</strong> ${height} ${heightUnit}
            </div>
            <div style="margin-bottom: 15px;">
                <strong>BMI Value:</strong> 
                <span style="font-size: 28px; font-weight: bold; color: ${color}; margin-left: 10px;">
                    ${bmi}
                </span>
            </div>
            <div style="margin-bottom: 15px;">
                <strong>Category:</strong> 
                <span class="badge" style="background-color: ${color}; color: white; font-size: 14px; padding: 5px 15px; margin-left: 10px;">
                    ${category}
                </span>
            </div>
            <hr>
            <div style="font-size: 12px; color: #666; margin-top: 15px;">
                <strong>Formula:</strong> BMI = weight(kg) / height(m)²
            </div>
        </div>
    `;
    
    // Create custom modal for result
    const modalHtml = `
        <div class="modal fade" id="bmiResultModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background: #9b59b6; color: white;">
                        <h5 class="modal-title">
                            <i class="fa fa-calculator"></i> BMI Calculator
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" style="color: white;">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        ${result}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">
                            <i class="fa fa-check"></i> OK
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#bmiResultModal').remove();
    
    // Add modal to body and show it
    $('body').append(modalHtml);
    $('#bmiResultModal').modal('show');
}

// Debug helper
function debugBmiData() {
    <?php
    // Prepare the data in PHP first
    $debugData = [];
    if(isset($bmi_data['series']) && isset($bmi_data['categories'])) {
        $debugData = [
            'series' => $bmi_data['series'],
            'categories' => $bmi_data['categories']
        ];
    } else {
        $debugData = ['error' => 'No BMI data'];
    }
    ?>
    
    const bmiData = <?php echo json_encode($debugData); ?>;
    
    console.log('BMI Data Debug:', bmiData);
    console.log('Client ID:', currentClientId);
    console.log('ApexCharts loaded:', typeof ApexCharts !== 'undefined');
    return bmiData;
}

// Add notification animation CSS
if (!$('#notification-styles').length) {
    $('head').append(`
        <style id="notification-styles">
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
            .custom-notification {
                font-size: 14px;
            }
        </style>
    `);
}
</script>

<style>
/* BMI Modal Styles */
.bmi-modal .modal-content {
    border-radius: 10px;
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.bmi-modal .modal-header {
    background: linear-gradient(135deg, #9b59b6, #8e44ad);
    color: white;
    border-radius: 10px 10px 0 0;
    border-bottom: none;
    padding: 15px 20px;
}

.bmi-modal .modal-title {
    font-weight: 600;
    font-size: 18px;
}

.bmi-modal .modal-title i {
    margin-right: 10px;
    font-size: 20px;
}

.bmi-modal .modal-body {
    padding: 20px;
    max-height: 70vh;
    overflow-y: auto;
}

.bmi-modal .modal-footer {
    border-top: 1px solid #eee;
    padding: 15px 20px;
}

.metric-stats.bmi-stats {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    margin: 20px 0;
    padding: 20px;
    background: linear-gradient(to right, #f8f9fa, #eef2f7);
    border-radius: 10px;
    border: 1px solid #e9ecef;
}

.metric-stat-item.bmi-stat {
    text-align: center;
    padding: 10px 15px;
    min-width: 120px;
    flex: 1;
    margin: 5px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: transform 0.3s ease;
}

.metric-stat-item.bmi-stat:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.metric-stat-value {
    font-size: 28px;
    font-weight: bold;
    color: #9b59b6;
    margin-bottom: 5px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

.metric-stat-label {
    font-size: 13px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
}

.bmi-categories {
    background: white;
    border-radius: 10px;
    padding: 15px;
    border: 1px solid #e9ecef;
    margin-top: 20px;
}

.bmi-categories .category-list {
    border-radius: 5px;
    overflow: hidden;
}

.bmi-categories .category-item {
    transition: all 0.3s ease;
    padding: 10px 15px;
    border-bottom: 1px solid #f1f1f1;
}

.bmi-categories .category-item:last-child {
    border-bottom: none;
}

.bmi-categories .category-item:hover {
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.bmi-categories .category-item span:first-child {
    font-weight: 500;
}

.bmi-categories .category-item span:last-child {
    font-weight: 600;
    color: #333;
}

.progress {
    height: 20px;
    border-radius: 10px;
    overflow: hidden;
    background-color: #e9ecef;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
}

.progress-bar {
    background: linear-gradient(90deg, #9b59b6, #8e44ad);
    transition: width 1s ease-in-out;
    font-weight: 600;
    font-size: 12px;
    line-height: 20px;
    text-shadow: 0 1px 1px rgba(0,0,0,0.3);
}

/* BMI History Table */
#bmiHistoryTable {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e9ecef;
}

#bmiHistoryTable table {
    font-size: 14px;
    margin: 0;
}

#bmiHistoryTable thead {
    background: linear-gradient(135deg, #9b59b6, #8e44ad);
    color: white;
}

#bmiHistoryTable th {
    border: none;
    padding: 12px 15px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 13px;
}

#bmiHistoryTable tbody tr {
    transition: all 0.2s ease;
}

#bmiHistoryTable tbody tr:hover {
    background-color: #f8f9fa;
    transform: scale(1.002);
}

#bmiHistoryTable td {
    padding: 10px 15px;
    vertical-align: middle;
    border-color: #f1f1f1;
}

#bmiHistoryTable .badge {
    color: white;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    min-width: 60px;
    display: inline-block;
    text-align: center;
}

/* Form Styling */
#bmiFormContainer {
    box-shadow: 0 3px 10px rgba(155, 89, 182, 0.15);
    border: 1px solid #e0c8f1;
}

#bmiForm .form-control {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 8px 12px;
    font-size: 14px;
    transition: all 0.3s ease;
}

#bmiForm .form-control:focus {
    border-color: #9b59b6;
    box-shadow: 0 0 0 0.2rem rgba(155, 89, 182, 0.25);
    transform: translateY(-1px);
}

#bmiForm label {
    font-weight: 600;
    color: #555;
    font-size: 13px;
    margin-bottom: 5px;
}

#bmiForm .form-group {
    margin-bottom: 15px;
}

#saveBmiBtn {
    border: none;
    padding: 8px 20px;
    font-weight: 600;
    transition: all 0.3s ease;
}

#saveBmiBtn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(155, 89, 182, 0.3);
}

#cancelBmiBtn {
    background: #95a5a6;
    border: none;
    padding: 8px 20px;
    font-weight: 600;
}

/* Metric Graph Container */
.metric-graph-container {
    background: white;
    border-radius: 10px;
    padding: 20px;
    border: 1px solid #e9ecef;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

/* Spinner */
.spinner-border.text-primary {
    color: #9b59b6 !important;
}

/* Custom notification styles */
.custom-notification {
    animation: slideIn 0.3s ease;
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

/* Responsive adjustments */
@media (max-width: 768px) {
    .metric-stat-item.bmi-stat {
        min-width: calc(50% - 20px);
    }
    
    .bmi-modal .modal-dialog {
        margin: 10px;
    }
    
    #bmiHistoryTable table {
        font-size: 13px;
    }
    
    #bmiHistoryTable th, 
    #bmiHistoryTable td {
        padding: 8px 10px;
    }
    
    .modal-body {
        padding: 15px;
    }
}

@media (max-width: 576px) {
    .metric-stat-item.bmi-stat {
        min-width: 100%;
    }
    
    .bmi-modal .modal-dialog {
        margin: 5px;
    }
    
    #bmiForm .row > div {
        margin-bottom: 10px;
    }
}
</style>