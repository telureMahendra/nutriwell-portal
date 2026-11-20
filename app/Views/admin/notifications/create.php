<link href="<?= base_url();?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>
<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>

<style>
:root{
  --primary:#2f6ff6;
  --muted:#6b7280;
  --card-bg:#fff;
  --radius:10px;
}

/* Card */
.notification-card{
  background-color: #fff !important;
  padding:26px;
  border-radius:14px !important;
  max-width:900px !important;
  margin:20px auto !important;
  box-shadow:0 8px 22px rgba(16,24,40,0.06) !important;
  font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
  color:#111827;
}

/* Heading */
.notification-card h3{
  font-size:22px;
  font-weight:600;
  margin:0 0 16px;
  padding-bottom:8px;
  border-bottom:1px solid #f1f5f9;
}

/* Form rows */
.form-row{ margin-bottom:14px; }
.notification-card label{ display:block; font-weight:600; margin-bottom:8px; color:#374151; font-size:14px; }

/* Inputs */
.form-control{
  width:100%;
  padding:10px 12px;
  border-radius:10px !important;
  border:1px solid #e6e9ee;
  background:#fff;
  font-size:14px;
  box-sizing:border-box;
}
.form-control:focus{
  outline:none;
  box-shadow:0 0 0 4px rgba(47,111,246,0.08);
  border-color:var(--primary);
}

/* Image area */
.image-area{ display:flex; gap:14px; align-items:center; flex-wrap:wrap; }
.image-preview{
  width:150px; height:95px; border-radius:10px; background:#f3f4f6; border:1px solid #e6e9ee; object-fit:cover; display:flex; align-items:center; justify-content:center; color:#9aa4b2;
}
.upload-box{ width:190px; display:flex; flex-direction:column; gap:8px; }
.upload-btn{
  display:inline-flex; align-items:center; justify-content:center;
  padding:10px 12px; border-radius:10px; border:1px dashed #cfe0ff; background:#f8fbff; color:var(--primary);
  cursor:pointer; font-weight:600;
}
.upload-btn i{ margin-right:8px; }
.remove-link{
 color: var(--primary);
    cursor: pointer;
    font-size: 13px;
    text-align: center;
    border: 1px dashed #cfe0ff;
    background: #f8fbff;
    padding: 10px 12px; 
}

/* Radios grid */
.radio-grid{ display:grid; grid-template-columns:1fr 1fr; gap:10px 26px; margin-top:8px; }
.form-check{
 display:flex; 
 gap:10px;
  align-items:center; 
  padding:8px 10px; 
  border-radius:10px; 
  background: transparent; 
  border:none;
   }
.form-check input{ transform:scale(1.05); }

/* Search input (icon) */
.search-wrap{ position:relative; margin-top:12px; }
.search-wrap .search-icon{ position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--muted); }
.search-wrap .form-control.search-input{ padding-left:40px; }

/* Client select */
.client-select-box{ margin-top:10px; border-radius:8px; display:none; }

/* client details */
.client-details{ 
  margin-top:10px; 
  display:none; 
  padding:12px; 
  border-radius:8px; 
  background:#fbfdff; 
  border:1px solid #e9f0ff; 
  color:#1f2937; 
  max-height: 200px;
  overflow-y: auto;
}

.client-item{
  padding: 8px 0;
  border-bottom: 1px solid #f0f4ff;
}

.client-item:last-child{
  border-bottom: none;
}

.client-summary{
  font-size: 12px;
  color: var(--muted);
  margin-top: 10px;
}

/* Schedule */
#scheduleInput{ margin-top:10px; display:none; }

/* actions */
.actions{ margin-top:20px; display:flex; gap:16px; justify-content:center; }
.btn{ padding:11px 58px; border-radius:10px; border:none; cursor:pointer; font-weight:600; }
.btn-cancel{ background:transparent; border:1px solid #eef2f7; color:#374151; }
.btn-submit{ background-color: #f57f27 !important; color:white; }

/* Loading overlay */
.loading-overlay {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  z-index: 9999;
  justify-content: center;
  align-items: center;
}

.loading-spinner {
  background: white;
  padding: 20px;
  border-radius: 10px;
  text-align: center;
}

/* responsive */
@media(max-width:720px){
  .radio-grid{ grid-template-columns:1fr; }
  .actions{ flex-direction:column-reverse; }
  .image-area{ align-items:flex-start; }
}

/* Alert messages */
.alert {
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 16px;
}

.alert-success {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #a7f3d0;
}

.alert-error {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
}
</style>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
  <div class="loading-spinner">
    <i class="fas fa-spinner fa-spin"></i>
    <p>Saving Notification...</p>
  </div>
</div>

<div class="notification-card">
    <h3>Create Notification</h3>

    <!-- Display success/error messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <?php if (isset($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error">
                <?= $error ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form id="notificationForm" method="post" action="<?= base_url('notifications/store') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- Title -->
        <div class="form-row">
            <label for="title">Title</label>
            <input id="title" name="title" class="form-control" type="text" placeholder="Enter title" required value="<?= old('title') ?>">
        </div>

        <!-- Description -->
        <div class="form-row">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="2" placeholder="Enter description" required><?= old('description') ?></textarea>
        </div>

        <!-- Image -->
        <div class="form-row">
            <label>Image</label>
            <div class="image-area">
                <div id="previewContainer" class="image-preview">
                    <img id="previewImg" src="<?= base_url('public/assets/admin/pages/media/users/avatar80_3.jpg') ?>" alt="preview" style="width:100%;height:100%;object-fit:cover;border-radius:10px;display:block;">
                </div>

                <div class="upload-box">
                    <div id="uploadTrigger" class="upload-btn"><i class="fa fa-cloud-upload-alt"></i> Upload</div>
                    <input id="imageFile" name="image_file" type="file" accept="image/*" style="display:none;">
                    <div id="removeImage" class="remove-link">Remove</div>
                </div>
            </div>
        </div>

        <!-- Send To -->
        <div class="form-row">
            <label>Send To</label>

            <div class="radio-grid">
                <label class="form-check"><input type="radio" name="audience_type" value="0" checked> <span>All Clients</span></label>
                <label class="form-check"><input type="radio" name="audience_type" value="3"> <span>Specific Client(s)</span></label>
                <label class="form-check"><input type="radio" name="audience_type" value="1"> <span>Paid Clients</span></label>
                <label class="form-check"><input type="radio" name="audience_type" value="2"> <span>Free Clients</span></label>
            </div>

            <!-- Search area -->
            <div id="searchArea" class="search-wrap" style="display:none;">
                <i class="fa fa-search search-icon"></i>
                <input id="clientSearchInput" class="form-control search-input" type="text" placeholder="Search client">
            </div>

            <!-- Client select list (multi) -->
            <select id="clientSelectBox" name="client_ids[]" class="form-control client-select-box" multiple size="6">
                <?php if (!empty($clients)): ?>
                    <?php foreach ($clients as $c): ?>
                        <option data-email="<?= esc($c->client_email ?? '') ?>" data-mobile="<?= esc($c->client_mobile_number ?? '') ?>" value="<?= esc($c->client_id) ?>">
                            <?= esc($c->client_name) ?> (<?= esc($c->client_mobile_number) ?>)<?= isset($c->client_email) ? " - " . esc($c->client_email) : "" ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <!-- Client details (shows when one or more clients selected) -->
            <div id="clientDetails" class="client-details" aria-hidden="true">
                <div id="clientsListContainer">
                    <!-- Client items will be dynamically inserted here -->
                </div>
                <div id="clientSummary" class="client-summary">
                    <!-- Summary will be shown here -->
                </div>
            </div>
        </div>

        <!-- Schedule -->
        <div class="form-row">
            <label>Schedule Notification</label>
            <div class="radio-grid" style="margin-top:8px;">
                <label class="form-check"><input type="radio" name="schedule_type" value="now" checked> <span>Send Now</span></label>
                <label class="form-check"><input type="radio" name="schedule_type" value="schedule"> <span>Schedule</span></label>
            </div>

            <div id="scheduleInput" style="display:none; margin-top:10px;">
                <input id="scheduleDateTime" name="schedule_datetime" class="form-control" type="datetime-local" value="<?= old('schedule_datetime') ?>">
            </div>
        </div>

        <!-- Actions -->
        <div class="actions">
            <button type="button" class="btn btn-cancel" onclick="window.history.back()">Cancel</button>
            <button type="submit" class="btn btn-submit" id="submitBtn">Send Notification</button>
        </div>

    </form>
</div>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script>
// Elements
const uploadTrigger = document.getElementById('uploadTrigger');
const imageFile = document.getElementById('imageFile');
const previewImg = document.getElementById('previewImg');
const removeImageBtn = document.getElementById('removeImage');
const loadingOverlay = document.getElementById('loadingOverlay');
const submitBtn = document.getElementById('submitBtn');

const audienceRadios = document.getElementsByName('audience_type');
const searchArea = document.getElementById('searchArea');
const clientSearchInput = document.getElementById('clientSearchInput');
const clientSelectBox = document.getElementById('clientSelectBox');
const clientDetails = document.getElementById('clientDetails');
const clientsListContainer = document.getElementById('clientsListContainer');
const clientSummary = document.getElementById('clientSummary');

const scheduleRadios = document.getElementsByName('schedule_type');
const scheduleInput = document.getElementById('scheduleInput');
const scheduleDateTime = document.getElementById('scheduleDateTime');

// Upload handlers
uploadTrigger.addEventListener('click', () => imageFile.click());

imageFile.addEventListener('change', function(){
  if (this.files && this.files[0]) {
    const reader = new FileReader();
    reader.onload = e => previewImg.src = e.target.result;
    reader.readAsDataURL(this.files[0]);
    removeImageBtn.style.display = 'block';
  }
});

removeImageBtn.addEventListener('click', function(){
  // fallback to uploaded screenshot path
  previewImg.src = '<?= base_url('public/assets/admin/pages/media/users/avatar80_3.jpg') ?>';
  imageFile.value = '';
  this.style.display = 'none';
});

// Audience toggle
function updateAudienceUI(){
  const val = document.querySelector('input[name="audience_type"]:checked').value;
  if (val === '3'){
    searchArea.style.display = 'block';
    clientSelectBox.style.display = 'block';
    clientSelectBox.scrollIntoView({behavior:'smooth', block:'nearest'});
  } else {
    searchArea.style.display = 'none';
    clientSelectBox.style.display = 'none';
    clientDetails.style.display = 'none';
  }
}
Array.from(audienceRadios).forEach(r => r.addEventListener('change', updateAudienceUI));
updateAudienceUI(); // initial

// Schedule toggle
function updateScheduleUI(){
  const val = document.querySelector('input[name="schedule_type"]:checked').value;
  if (val === 'schedule'){
    scheduleInput.style.display = 'block';
    // set min to now
    const now = new Date();
    const y = now.getFullYear(), m = String(now.getMonth()+1).padStart(2,'0'), d = String(now.getDate()).padStart(2,'0');
    const hh = String(now.getHours()).padStart(2,'0'), mm = String(now.getMinutes()).padStart(2,'0');
    const minVal = `${y}-${m}-${d}T${hh}:${mm}`;
    scheduleDateTime.min = minVal;
  } else {
    scheduleInput.style.display = 'none';
  }
}
Array.from(scheduleRadios).forEach(r => r.addEventListener('change', updateScheduleUI));
updateScheduleUI();

// Client details when clients are selected
function updateClientDetails() {
  const selected = Array.from(clientSelectBox.selectedOptions);
  
  if (selected.length > 0) {
    // Clear previous details
    clientsListContainer.innerHTML = '';
    
    // Add each selected client to the details view
    selected.forEach(opt => {
      const clientItem = document.createElement('div');
      clientItem.className = 'client-item';
      
      const name = opt.textContent.split('(')[0].trim();
      const mobile = opt.dataset.mobile || 'No mobile';
      const email = opt.dataset.email || 'No email';
      
      clientItem.innerHTML = `
        <div><strong>${name}</strong></div>
        <div style="margin-top:4px;">
          <small style="color:var(--muted)">${mobile}</small>
        </div>
        <div>
          <small style="color:var(--muted)">${email}</small>
        </div>
      `;
      
      clientsListContainer.appendChild(clientItem);
    });
    
    // Update summary
    clientSummary.textContent = `${selected.length} client(s) selected`;
    
    // Show details section
    clientDetails.style.display = 'block';
  } else {
    // Hide details section if no clients selected
    clientDetails.style.display = 'none';
  }
}

// Attach event listener to client select box
clientSelectBox.addEventListener('change', updateClientDetails);


 // Search


// Store original <option> list (fallback)
const defaultOptions = clientSelectBox.innerHTML;

// In-memory search cache
const searchCache = {};

// Debounce Utility
function debounce(fn, delay = 350) {
  let timer = null;
  return function(...args) {
    clearTimeout(timer);
    timer = setTimeout(() => fn.apply(this, args), delay);
  };
}

// Loading Indicator
function showLoading() {
  clientSelectBox.innerHTML = `<option disabled>Searching...</option>`;
}

// No Results Indicator
function showNoResults() {
  clientSelectBox.innerHTML = `<option disabled>No clients found</option>`;
}

// Render list
function renderClientOptions(clients) {
  clientSelectBox.innerHTML = "";
  clients.forEach(c => {
    const opt = document.createElement("option");
    opt.value = c.client_id;
    opt.textContent =
      `${c.client_name} (${c.client_mobile_number})` +
      (c.client_email ? ` - ${c.client_email}` : "");

    opt.dataset.mobile = c.client_mobile_number || "";
    opt.dataset.email  = c.client_email || "";

    clientSelectBox.appendChild(opt);
  });
  
  // Update client details after rendering new options
  updateClientDetails();
}

// Simple client filtering for local search
function filterClientsLocally(searchTerm) {
  const allOptions = clientSelectBox.querySelectorAll('option');
  let hasVisibleOptions = false;
  
  allOptions.forEach(option => {
    const text = option.textContent.toLowerCase();
    if (text.includes(searchTerm.toLowerCase())) {
      option.style.display = '';
      hasVisibleOptions = true;
    } else {
      option.style.display = 'none';
    }
  });
  
  return hasVisibleOptions;
}

// Main Search Function (debounced)
const performSearch = debounce(async function () {
  const q = clientSearchInput.value.trim();

  // Too short → restore default
  if (q.length < 1) {
    clientSelectBox.innerHTML = defaultOptions;
    updateClientDetails(); // Update details after restoring options
    return;
  }

  // First try local filtering
  const hasLocalResults = filterClientsLocally(q);
  if (hasLocalResults) {
    updateClientDetails(); // Update details after filtering
    return;
  }

  // If no local results, try API search
  showLoading();

  const endpoint = `<?= base_url('admin/notifications/searchClients'); ?>?q=${encodeURIComponent(q)}`;

  // Prevent outdated responses
  const currentToken = Date.now();
  clientSearchInput.dataset.token = currentToken;

  try {
    const response = await fetch(endpoint, { cache: "no-store" });
    const json = await response.json();

    // Ignore outdated response
    if (clientSearchInput.dataset.token != currentToken) return;

    if (!json.clients || json.clients.length === 0) {
      showNoResults();
      updateClientDetails(); // Update details (will hide details section)
      return;
    }

    // Save in cache
    searchCache[q] = json.clients;

    // Render
    renderClientOptions(json.clients);

  } catch (err) {
    console.error("Client search error:", err);
    clientSelectBox.innerHTML = `<option disabled>Error loading results</option>`;
    updateClientDetails(); // Update details after error
  }

}, 400);

// Attach listener
clientSearchInput.addEventListener("input", performSearch);

// Form submit validation and loading
document.getElementById('notificationForm').addEventListener('submit', function(e){
  const aud = document.querySelector('input[name="audience_type"]:checked').value;
  
  // Validate specific clients selection
  if (aud === '3'){
    if (clientSelectBox.selectedOptions.length === 0){
      e.preventDefault();
      alert('Please select at least one client when "Specific Client(s)" is chosen.');
      clientSelectBox.focus();
      return;
    }
  }

  // Show loading overlay
  loadingOverlay.style.display = 'flex';
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

  // Form will continue to submit normally
});

// Handle page refresh to remove loading state
window.addEventListener('beforeunload', function() {
  if (loadingOverlay.style.display === 'flex') {
    loadingOverlay.style.display = 'none';
  }
});

</script>

<?= $this->endSection(); ?>