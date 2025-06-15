<?php
include '../includes/db_connection.php'; 

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT * FROM beneficiaries WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $beneficiary = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$beneficiary) {
        die("Beneficiary not found.");
    }

    // Format the address
    $formatted_address = htmlspecialchars($beneficiary['sitio']) . ', ' . htmlspecialchars($beneficiary['barangay']) . ', Gloria, Oriental Mindoro';

} else {
    die("No beneficiary ID provided.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Assistance Form</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div id="toast" class="toast">No patient data found.</div>
    <div class="form-container">
        <h2>Request Assistance Form</h2> <a href="../beneficiaries/view.php?id=<?= $id ?>"
        style="display: block; text-align: center; margin-top: 20px; text-decoration: none; color: #007bff;">Go BAck</a>
        <form action="process.php" method="post" enctype="multipart/form-data">
            <h3>Client Information</h3>
            <label for="request_purpose">Request Purpose:</label>
            <select name="request_purpose" id="request_purpose" required>
                <option value="">-- Select Request Type --</option>
                <option value="med_exp_hel">Medical Expense with HealthCard</option>
                <option value="hos_bill_hel">Hospital Bill with HealthCard</option>
                <option value="med_exp">Medical Expense</option>
                <option value="hos_bill">Hospital Bill</option>
                <option value="burial">Burial</option>
                <option value="educational">Educational</option>
            </select>

            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>" id="client_id">
            <label>Name of Client:</label>
            <input type="text" id="client_name" name="client_name" value="<?= htmlspecialchars($beneficiary['full_name']) ?>" required>

            <label>Age:</label>
            <input type="number" id="client_age" name="client_age" value="<?= htmlspecialchars($beneficiary['age']) ?>" required>

            <label>Gender/Sex:</label>
            <input type="text" id="client_gender" name="client_gender" value="<?= htmlspecialchars($beneficiary['gender']) ?>" required>

            <label>Civil Status:</label>
            <input type="text" id="client_civil_status" name="client_civil_status" value="<?= htmlspecialchars($beneficiary['civil_status']) ?>" required>

            <label>Birthday:</label>
            <input type="date" id="client_birthday" name="client_birthday" value="<?= htmlspecialchars($beneficiary['birthday']) ?>" required>

            <label>Birthplace:</label>
            <input type="text" id="client_birthplace" name="client_birthplace" value="<?= htmlspecialchars($beneficiary['birthplace']) ?>" required>

            <label>Educational Attainment:</label>
            <input type="text" id="client_education" name="client_education" value="<?= htmlspecialchars($beneficiary['education']) ?>" required>

            <label>Occupation:</label>
            <input type="text" id="client_occupation" name="client_occupation" value="<?= htmlspecialchars($beneficiary['occupation']) ?>" required>

            <label>Religion:</label>
            <input type="text" id="client_religion" name="client_religion" value="<?= htmlspecialchars($beneficiary['religion']) ?>" required>

            <label>Sitio/Purok:</label>
            <input type="text" 
                   id="client_sitio" 
                   name="client_sitio" 
                   value="<?= htmlspecialchars($beneficiary['sitio']) ?>" 
                   required>

            <label>Barangay:</label>
            <input type="text" 
                   id="client_barangay" 
                   name="client_barangay" 
                   value="<?= htmlspecialchars($beneficiary['barangay']) ?>" 
                   required>

            <label>Complete Address:</label>
            <input type="text" 
                   id="client_complete_address" 
                   name="client_complete_address" 
                   value="<?= $formatted_address ?>" 
                   readonly>

            <label>Date:</label>
            <input type="date" name="request_date" required> <!-- Auto-filled via JS -->

            <h3>Patient Information</h3>
            <label for="request_type">Request Type:</label>
            <select name="request_type" id="request_type" required>
                <option value="">-- Select Request Type --</option>
                <option value="client_patient">Client is the Patient</option>
                <option value="client_requesting">Client Requesting for Patient</option>
            </select>

            <label for="patient_name">Name of Patient:</label>
            <input type="text" name="patient_name" id="patient_name">

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
            
            <label for="patient_birthday">Birthday:</label>
            <input type="date" name="patient_birthday" id="patient_birthday" onchange="calculateAge()">
            
            <label for="patient_age">Age:</label>
            <input type="number" name="patient_age" id="patient_age">

            <label for="patient_gender">Gender/Sex:</label>
            <input type="text" id="patient_gender" name="patient_gender" required>
            <select id="patient_gender_select" style="display:none;" onchange="fillPatientGender()">
                <option value="">-- Select Gender --</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>

            <label for="patient_civil_status">Civil Status:</label>
            <input type="text" name="patient_civil_status" id="patient_civil_status">
            <select id="patient_civil_status_select" style="display:none;" onchange="fillPatientCivilStatus()">
                <option value="">-- Select Civil Status --</option>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Widowed">Widowed</option>
                <option value="Divorced">Divorced</option>
            </select>


            <label for="patient_birthplace">Birthplace:</label>
            <input type="text" name="patient_birthplace" id="patient_birthplace">

            <label for="patient_education">Educational Attainment:</label>
            <input type="text" name="patient_education" id="patient_education">

            <label for="patient_occupation">Occupation:</label>
            <input type="text" name="patient_occupation" id="patient_occupation">

            <label for="patient_religion">Religion:</label>
            <input type="text" name="patient_religion" id="patient_religion">

            <label>Sitio/Purok:</label>
            <input type="text" name="patient_sitio" id="patient_sitio" required>

            <label>Barangay:</label>
            <input type="text" name="patient_barangay" id="patient_barangay" required>

            <label>Complete Address:</label>
            <input type="text" 
                   id="patient_complete_address" 
                   name="patient_complete_address" >

            <label>
                <input type="checkbox" id="same_address_checkbox" onchange="fillPatientAddress()">
                Same as Client Address
            </label><br>


            <label>Amount:</label>
            <input type="text" id="amount" name="amount">

            <label>Diagnosis:</label>
            <input type="text" id="diagnosis_school" name="diagnosis_school">
            
            <div>
            <label>ID Type to be attached:</label>
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

            <label>Prepared By:</label>
            <input type="text" name="prep_by" id="prep_by">

            <label>Position:</label>
            <input type="text" name="pos_prep"  id="pos_prep">
            <hr>
            <label>Noted By:</label>
            <input type="text" name="not_by"  id="not_by">
            <label>Position:</label>
            <input type="text" name="pos_not"  id="pos_not">

            
            <button type="submit">Generate Document</button>
        </form>
    </div>
    <script src="scripts/index.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Address handling
    const sameAddressCheckbox = document.getElementById('same_address_checkbox');
    const addressFields = {
        client: {
            sitio: document.getElementById('client_sitio'),
            barangay: document.getElementById('client_barangay'),
            complete: document.getElementById('client_complete_address')
        },
        patient: {
            sitio: document.getElementById('patient_sitio'),
            barangay: document.getElementById('patient_barangay'),
            complete: document.getElementById('patient_complete_address')
        }
    };

    function generateCompleteAddress(sitio, barangay) {
        return `${sitio}, ${barangay}, Gloria, Oriental Mindoro`;
    }

    function fillPatientAddress() {
        if (sameAddressCheckbox.checked) {
            // Copy values from client to patient
            addressFields.patient.sitio.value = addressFields.client.sitio.value;
            addressFields.patient.barangay.value = addressFields.client.barangay.value;
            addressFields.patient.complete.value = addressFields.client.complete.value;
            
            // Make patient fields readonly
            addressFields.patient.sitio.readOnly = true;
            addressFields.patient.barangay.readOnly = true;
            addressFields.patient.complete.readOnly = true;
        } else {
            // Clear patient fields
            addressFields.patient.sitio.value = '';
            addressFields.patient.barangay.value = '';
            addressFields.patient.complete.value = '';
            
            // Make patient fields editable
            addressFields.patient.sitio.readOnly = false;
            addressFields.patient.barangay.readOnly = false;
            addressFields.patient.complete.readOnly = false;
        }
    }

    // Update patient complete address when individual fields change
    function updatePatientCompleteAddress() {
        const sitio = addressFields.patient.sitio.value.trim();
        const barangay = addressFields.patient.barangay.value.trim();
        
        if (sitio && barangay) {
            addressFields.patient.complete.value = generateCompleteAddress(sitio, barangay);
        }
    }

    // Add event listeners
    if (sameAddressCheckbox) {
        sameAddressCheckbox.addEventListener('change', fillPatientAddress);
    }

    addressFields.patient.sitio.addEventListener('input', updatePatientCompleteAddress);
    addressFields.patient.barangay.addEventListener('input', updatePatientCompleteAddress);
});

// Set current date when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Get current date in YYYY-MM-DD format
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    const currentDate = `${year}-${month}-${day}`;

    // Set the date input value
    const dateInput = document.querySelector('input[name="request_date"]');
    if (dateInput) {
        dateInput.value = currentDate;
    }
});
</script>
</body>
</html>
