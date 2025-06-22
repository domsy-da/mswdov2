<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Editable Certificate</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="css/index.css" />
</head>
<body>

<?php
include 'config/db.php'; 
session_start();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $purpose = isset($_GET['purpose']) ? intval($_GET['purpose']) : null;
    
    try {
        // Fetch beneficiary data
        $stmt = $pdo->prepare("SELECT * FROM beneficiaries WHERE id = ?");
        $stmt->execute([$id]);
        $beneficiary = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Fetch latest transaction data for this beneficiary
        $stmt = $pdo->prepare("SELECT * FROM transactions WHERE beneficiary_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$id]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Add transaction data to beneficiary array
        $beneficiary['transaction'] = $transaction;
        
        // Map purpose numbers to values
        $purposeMap = [
            1 => 'educational',
            2 => 'med_exp',
            3 => 'burial'
        ];
        
        $selectedPurpose = isset($purposeMap[$purpose]) ? $purposeMap[$purpose] : '';
        $beneficiary['purpose'] = $selectedPurpose;
        
        $beneficiaryJson = json_encode($beneficiary);
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}
?>

<!-- Service & Document Selection Section -->
<div style="display: flex; align-items: center; gap: 24px; margin: 0 0 2px 0; padding: 20px 24px; background: #f8fafc; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">

  <!-- Back Button -->
  <button onclick="window.location.href='../beneficiaries/view.php?id=<?= $id;?>';" style="background: #fff; border: 1px solid #d1d5db; color: #374151; border-radius: 8px; padding: 8px 18px; font-size: 16px; font-weight: 500; cursor: pointer; transition: background 0.2s, border 0.2s;">
    ← Back
  </button>

  <!-- Admin Button (visible only to admin users) -->
  <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <button onclick="window.location.href='index-admin.php';" style="background: #fff; border: 1px solid #d97706; color: #b45309; border-radius: 8px; padding: 8px 18px; font-size: 16px; font-weight: 500; cursor: pointer; transition: background 0.2s, border 0.2s;">
      ⚙️ Admin Panel
    </button>
  <?php endif; ?>

  <!-- Service Dropdown -->
  <label for="service_select" style="font-weight: 500; margin-right: 8px; color: #374151;">Select Service:</label>
  <select id="service_select" style="padding: 8px 12px; border-radius: 8px; border: 1px solid #d1d5db; font-size: 16px; background: #fff; color: #374151;">
    <option value="">-- Select Service --</option>
    <option value="medical">Medical Expenses</option>
    <option value="burial">Burial</option>
    <option value="educational">Educational</option>
  </select>

  <!-- Document Dropdown -->
  <label for="document_select" style="font-weight: 500; margin-left: 16px; margin-right: 8px; color: #374151;">Select Document:</label>
  <select id="document_select" disabled style="padding: 8px 12px; border-radius: 8px; border: 1px solid #d1d5db; font-size: 16px; background: #fff; color: #374151;">
    <option value="">-- Select Document --</option>
  </select>
</div>

<textarea id="certificate">
  <!-- ✅ Default welcome message -->
  <div style="text-align: center; margin-bottom: 30px;">
    <img src="<?php include 'img/logo.php'; ?>" alt="MSWDO Logo" style="width: 120px; height: auto; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.15); border: 2px solid #e0e0e0; background: #fff; padding: 8px;">
  </div>
  <h2 style="text-align: center;">Welcome to the Certificate & Document Generator</h2>
  <p style="text-align: center;">Use the dropdown menus above to select a <strong>Service</strong> and <strong>Document</strong> type.</p>
  <p style="text-align: center;">Once selected, the appropriate template will load here automatically for you to edit, preview, and fill out.</p>
  <p style="text-align: center; font-style: italic;">Start by choosing a service and document to generate your certificate or social case study report.</p>
</textarea>

<button id="preview" style="background: #2563eb; color: #fff; border: none; border-radius: 8px; padding: 10px 22px; font-size: 16px; font-weight: 500; margin-right: 12px; cursor: pointer; box-shadow: 0 2px 8px rgba(37,99,235,0.08); transition: background 0.2s, box-shadow 0.2s;">
  👁️ Preview PDF
</button>
<button id="fillDetails" style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 10px 22px; font-size: 16px; font-weight: 500; cursor: pointer; box-shadow: 0 2px 8px rgba(16,185,129,0.08); transition: background 0.2s, box-shadow 0.2s;">
  📝 Fill Details
</button>

<!-- Add this just before the closing body tag in index.php -->
<div id="detailsModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Fill Certificate Details</h3>
      <span id="closeModal" class="close-button">&times;</span>
    </div>

    <form id="detailsForm">
      <!-- Client Information Section -->
      <div class="form-section">
        <h4>Client Information</h4>
        <div class="form-group">
            <div class="form-field">
            <label for="request_purpose">Request Purpose:</label>
            <select name="request_purpose" id="request_purpose" required onchange="toggleSpecialTypeInput()">
              <option value="">-- Select Request Type --</option>
              <option value="educational">Educational</option>
              <option value="med_exp">Medical Expense</option>
              <option value="burial">Burial</option>
            </select>
            </div>
            <div class="form-field" id="specialTypeField" style="display:none;">
            <label for="req_spe_type">Specify Medical Expense Type:</label>
            <input type="text" name="req_spe_type" id="req_spe_type" placeholder="e.g. Hospitalization, Medicines">
            </div>
            <script>
            function toggleSpecialTypeInput() {
              var purpose = document.getElementById('request_purpose').value;
              var field = document.getElementById('specialTypeField');
              if (purpose === 'med_exp') {
              field.style.display = 'block';
              document.getElementById('req_spe_type').required = true;
              } else {
              field.style.display = 'none';
              document.getElementById('req_spe_type').required = false;
              document.getElementById('req_spe_type').value = '';
              }
            }
            </script>

          <div class="form-field">
            <label for="client_name">Name of Client:</label>
            <input type="text" id="client_name" name="client_name" required>
            <input type="hidden" name="beneficiary_id" id="client_id">
          </div>

          <div class="form-field">
            <label for="client_age">Age:</label>
            <input type="number" id="client_age" name="client_age" required>
          </div>

          <div class="form-field">
            <label for="client_gender">Gender/Sex:</label>
            <select id="client_gender" name="client_gender" required>
              <option value="">-- Select Gender --</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>

          <div class="form-field">
            <label for="civil_status">Civil Status:</label>
            <select id="civil_status" name="civil_status" required>
              <option value="">-- Select Status --</option>
              <option value="Single">Single</option>
              <option value="Married">Married</option>
              <option value="Widowed">Widowed</option>
              <option value="Separated">Separated</option>
            </select>
          </div>

          <div class="form-field">
            <label for="birthday">Birthday:</label>
            <input type="date" id="birthday" name="birthday" required>
          </div>

          <div class="form-field">
            <label for="birthplace">Birthplace:</label>
            <input type="text" id="birthplace" name="birthplace" required>
          </div>

          <div class="form-field">
            <label for="educational">Educational Attainment:</label>
            <input type="text" id="educational" name="educational" required>
          </div>

          <div class="form-field">
            <label for="occupation">Occupation:</label>
            <input type="text" id="occupation" name="occupation" required>
          </div>

          <div class="form-field">
            <label for="religion">Religion:</label>
            <input type="text" id="religion" name="religion" required>
          </div>

          <div class="form-field">
            <label for="sitio">Sitio/Purok:</label>
            <input type="text" id="sitio" name="sitio" required>
          </div>

          <div class="form-field">
            <label for="barangay">Barangay:</label>
            <input type="text" id="barangay" name="barangay" required>
          </div>

          <div class="form-field">
            <label for="complete_address">Complete Address:</label>
            <input type="text" id="complete_address" name="complete_address" readonly>
          </div>
        </div>
      </div>

      <!-- Patient Information Section -->
      <div class="form-section">
        <h4>Patient Information</h4>
        <div class="form-group">
          <div class="form-field">
            <label>Request Type:</label>
            <div class="radio-group">
              <input type="radio" id="self" name="request_type" value="self">
              <label for="self">Client is the Patient</label>
              <input type="radio" id="other" name="request_type" value="other">
              <label for="other">Client Requesting for Patient</label>
            </div>
          </div>

          <div class="form-field">
            <label for="patient_name">Name of Patient:</label>
            <input type="text" id="patient_name" name="patient_name" required>
        </div>
        <div class="form-field">
          <label for="relation">Relation to patient:</label>
          <select name="relationQ" id="relationQ" onchange="putRelation()">
            <option value="">--please choose--</option>
            <option value="father">Father</option>
            <option value="mother">Mother</option>
            <option value="brother">Brother</option>
            <option value="sister">Sister</option>
            <option value="husband">Husband</option>
            <option value="wife">Wife</option>
            <option value="daughter">Daughter</option>
            <option value="son">Son</option>
            <option value="himself">Himself</option>
            <option value="herself">Herself</option>
          </select>
          <input type="text" name="relation" id="relation" required>
        </div>

        <div class="form-field">
          <label for="patient_birthday">Birthday:</label>
          <input type="date" name="patient_birthday" id="patient_birthday" onchange="calculateAgeFromBirthday()">
        </div>

        <div class="form-field">
          <label for="patient_age">Age:</label>
          <input type="number" name="patient_age" id="patient_age" onchange="calculateBirthdayFromAge()">
        </div>

        <div class="form-field">
          <label for="patient_gender">Gender/Sex:</label>
          <input type="text" id="patient_gender" name="patient_gender" required>
        </div>

        <div class="form-field">
          <label for="patient_civil_status">Civil Status:</label>
          <input type="text" name="patient_civil_status" id="patient_civil_status">
          <select id="patient_civil_status_select" style="display:none;" onchange="fillPatientCivilStatus()">
            <option value="">-- Select Civil Status --</option>
            <option value="Single">Single</option>
            <option value="Married">Married</option>
            <option value="Widowed">Widowed</option>
            <option value="Divorced">Divorced</option>
          </select>
        </div>

        <div class="form-field">
          <label for="patient_birthplace">Birthplace:</label>
          <input type="text" name="patient_birthplace" id="patient_birthplace">
        </div>

        <div class="form-field">
          <label for="patient_education">Educational Attainment:</label>
          <input type="text" name="patient_education" id="patient_education">
        </div>

        <div class="form-field">
          <label for="patient_occupation">Occupation:</label>
          <input type="text" name="patient_occupation" id="patient_occupation">
        </div>

        <div class="form-field">
          <label for="patient_religion">Religion:</label>
          <input type="text" name="patient_religion" id="patient_religion">
        </div>

        <div class="form-field">
          <label for="patient_sitio">Sitio/Purok:</label>
          <input type="text" name="patient_sitio" id="patient_sitio" required>
        </div>

        <div class="form-field">
          <label for="patient_barangay">Barangay:</label>
          <input type="text" name="patient_barangay" id="patient_barangay" required>
        </div>

        <div class="form-field">
          <label for="patient_complete_address">Complete Address: Same with Client Address? <span style="color: green; cursor: pointer;" onclick="fillPatientCompleteAddress()">Yes</span></label>
          <input type="text" id="patient_complete_address" name="patient_complete_address">
        </div>
      </div>
      <div class="form-section">
        <h4>Additional Information</h4>
        <div class="form-group">
          <div class="form-field">
            <label for="request_date">Date of Request:</label>
            <input type="date" id="request_date" name="request_date" required>
          </div>
          <div class="form-field">
            <label for="request_amount">Amount:</label>
            <input type="number" id="request_amount" name="request_amount" required>
          </div>
          <div class="form-field">
            <label for="request_diagnosis">Diagnosis:</label>
            <input type="text" id="request_diagnosis" name="request_diagnosis" required>
          </div>
          <div class="form-field">
            <label for="request_notes">Id type:</label>
            <select name="id_type" required>
                <option value="" disabled selected>Select an ID Type</option>
                <optgroup label="Government-Issued IDs">
                    <option value="passport">Passport</option>
                    <option value="driver_license">Driver’s License</option>
                    <option value="sss">SSS ID</option>
                    <option value="gsis">GSIS ID</option>
                    <option value="umid">UMID</option>
                    <option value="philhealth">PhilHealth ID</option>
                    <option value="pagibig">Pag-IBIG (HDMF) ID</option>
                    <option value="voters">Voter’s ID</option>
                    <option value="prc">PRC ID</option>
                    <option value="postal">Postal ID</option>
                    <option value="national">National ID (PhilSys ID)</option>
                </optgroup>
                <optgroup label="Other IDs">
                    <option value="student">Student ID</option>
                    <option value="senior_health">Senior Citizen Health Card</option>
                    <option value="pwd_health">PWD Health Card</option>
                </optgroup>
            </select>
          </div>
          <div class="form-field">
            <label for="prep_by">Prepared By:</label>
            <input type="text" name="prep_by" id="prep_by">
          </div>
          <div class="form-field">
            <label for="pos_prep">Position:</label>
            <input type="text" name="pos_prep" id="pos_prep">
          </div>
          <div class="form-field">
            <label for="not_by">Noted By:</label>
            <input type="text" name="not_by" id="not_by">
          </div>
          <div class="form-field">
            <label for="pos_not">Position:</label>
            <input type="text" name="pos_not" id="pos_not">
          </div>
        </div>
      </div>
      <div class="form-section" style="text-align: right; margin-top: 20px;">
        <button type="submit" id="submitDetails" class="btn btn-primary">Submit</button>
      </div>
    </form>
  </div>
</div>

<script src="tinymce/tinymce.min.js"></script>

<!-- Add this just before the closing </body> tag in index.php -->
<script>
// Check if beneficiary data exists
<?php if (isset($beneficiaryJson)): ?>
const beneficiary = <?php echo $beneficiaryJson; ?>;

// Function to fill form fields when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (beneficiary) {
        // Fill client information
        document.getElementById('client_name').value = beneficiary.full_name;
        document.getElementById('client_id').value = beneficiary.id;
        document.getElementById('client_age').value = beneficiary.age;
        document.getElementById('client_gender').value = beneficiary.gender;
        document.getElementById('civil_status').value = beneficiary.civil_status;
        document.getElementById('birthday').value = beneficiary.birthday;
        document.getElementById('birthplace').value = beneficiary.birthplace;
        document.getElementById('educational').value = beneficiary.education;
        document.getElementById('occupation').value = beneficiary.occupation;
        document.getElementById('religion').value = beneficiary.religion;
        document.getElementById('barangay').value = beneficiary.barangay;
        document.getElementById('sitio').value = beneficiary.sitio;
        
        // Update complete address
        updateCompleteAddress();
    }
});

// Function to update complete address
function updateCompleteAddress() {
    const sitio = document.getElementById('sitio').value;
    const barangay = document.getElementById('barangay').value;
    const completeAddress = `${sitio}, ${barangay}, Gloria, Oriental Mindoro`;
    document.getElementById('complete_address').value = completeAddress;
}
<?php endif; ?>
</script>

<script>

// Function to handle radio button changes
document.querySelectorAll('input[name="request_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'self') {
            // Fill patient information with client information
            document.getElementById('patient_name').value = document.getElementById('client_name').value;
            document.getElementById('patient_age').value = document.getElementById('client_age').value;
            document.getElementById('patient_gender').value = document.getElementById('client_gender').value;
            document.getElementById('patient_civil_status').value = document.getElementById('civil_status').value;
            document.getElementById('patient_birthday').value = document.getElementById('birthday').value;
            document.getElementById('patient_birthplace').value = document.getElementById('birthplace').value;
            document.getElementById('patient_education').value = document.getElementById('educational').value;
            document.getElementById('patient_occupation').value = document.getElementById('occupation').value;
            document.getElementById('patient_religion').value = document.getElementById('religion').value;
            document.getElementById('patient_sitio').value = document.getElementById('sitio').value;
            document.getElementById('patient_barangay').value = document.getElementById('barangay').value;
            document.getElementById('patient_complete_address').value = document.getElementById('complete_address').value;
            
            // Set relation to "himself/herself" based on gender
            const gender = document.getElementById('client_gender').value;
            document.getElementById('relationQ').value = gender === 'Male' ? 'himself' : 'herself';
            document.getElementById('relation').value = gender === 'Male' ? 'Himself' : 'Herself';
            
        } else if (this.value === 'other' && beneficiary.transaction) {
            // Fill from transaction data
            const transaction = beneficiary.transaction;
            document.getElementById('patient_name').value = transaction.patient_name;
            document.getElementById('patient_age').value = transaction.patient_age;
            document.getElementById('patient_gender').value = transaction.patient_gender;
            document.getElementById('patient_civil_status').value = transaction.patient_civil_status;
            document.getElementById('patient_birthday').value = transaction.patient_birthday;
            document.getElementById('patient_birthplace').value = transaction.patient_birthplace;
            document.getElementById('patient_education').value = transaction.patient_education;
            document.getElementById('patient_occupation').value = transaction.patient_occupation;
            document.getElementById('patient_religion').value = transaction.patient_religion;
            document.getElementById('patient_sitio').value = transaction.patient_sitio;
            document.getElementById('patient_barangay').value = transaction.patient_barangay;
            document.getElementById('patient_complete_address').value = transaction.patient_complete_address;
            document.getElementById('relationQ').value = transaction.relation.toLowerCase();
            document.getElementById('relation').value = transaction.relation;
            
            if (transaction.amount) document.getElementById('request_amount').value = transaction.amount;
            if (transaction.diagnosis_school) document.getElementById('request_diagnosis').value = transaction.diagnosis_school;
            if (transaction.id_type) document.querySelector('select[name="id_type"]').value = transaction.id_type;
            if (transaction.prep_by) document.getElementById('prep_by').value = transaction.prep_by;
            if (transaction.pos_prep) document.getElementById('pos_prep').value = transaction.pos_prep;
            if (transaction.not_by) document.getElementById('not_by').value = transaction.not_by;
            if (transaction.pos_not) document.getElementById('pos_not').value = transaction.pos_not;
        }
    });
});
</script>
<script src="js/script.js"></script>
<script src="js/script2.js"></script>
</body>
</html>
