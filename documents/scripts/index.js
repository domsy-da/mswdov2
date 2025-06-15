document.addEventListener("DOMContentLoaded", function() {
    let today = new Date().toISOString().split('T')[0];
    document.querySelector('input[name="request_date"]').value = today;
});
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("request_type").addEventListener("change", function () {
        let clientId = document.getElementById("client_id").value;
        console.log(clientId);
        if (this.value === "client_patient") {
            document.getElementById("patient_name").value = document.getElementById("client_name").value;
            document.getElementById("patient_age").value = document.getElementById("client_age").value;
            document.getElementById("patient_gender").value = document.getElementById("client_gender").value;
            document.getElementById("patient_civil_status").value = document.getElementById("client_civil_status").value;
            document.getElementById("patient_birthday").value = document.getElementById("client_birthday").value;
            document.getElementById("patient_birthplace").value = document.getElementById("client_birthplace").value;
            document.getElementById("patient_education").value = document.getElementById("client_education").value;
            document.getElementById("patient_occupation").value = document.getElementById("client_occupation").value;
            document.getElementById("patient_religion").value = document.getElementById("client_religion").value;
            document.getElementById("patient_address").value = document.getElementById("client_address").value;
        } else {
            let clientId = document.getElementById("client_id").value; 

            if (clientId) {
                fetch("get_patient_info.php?id=" + clientId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById("patient_name").value = data.patient_name || "";
                            document.getElementById("relation").value = data.relation || "";
                            document.getElementById("patient_age").value = data.patient_age || "";
                            document.getElementById("patient_gender").value = data.patient_gender || "";
                            document.getElementById("patient_civil_status").value = data.patient_civil_status || "";
                            document.getElementById("patient_birthday").value = data.patient_birthday || "";
                            document.getElementById("patient_birthplace").value = data.patient_birthplace || "";
                            document.getElementById("patient_education").value = data.patient_education || "";
                            document.getElementById("patient_occupation").value = data.patient_occupation || "";
                            document.getElementById("patient_religion").value = data.patient_religion || "";
                            document.getElementById("patient_address").value = data.patient_address || "";
                            document.getElementById("amount").value = data.amount || "";
                            document.getElementById("diagnosis_school").value = data.diagnosis_school || "";
                            document.getElementById("prep_by").value = data.prep_by || "";
                            document.getElementById("pos_prep").value = data.pos_prep || "";
                            document.getElementById("not_by").value = data.not_by || "";
                            document.getElementById("pos_not").value = data.pos_not || "";
                        } else {
                            showToast("No patient data found.");
                            showAdditionalInputs();
                        }
                    })
                    .catch(error => console.error("Error:", error));
            } else {
                alert("Client ID not found.");
                showAdditionalInputs();
            }
        }
    });
});
document.addEventListener("DOMContentLoaded", function () { 
    const ageInput = document.getElementById('patient_age');
    const dateInput = document.getElementById('patient_birthday');

    ageInput.addEventListener('input', function() {
    const age = parseInt(this.value);
    if (!isNaN(age)) {
        const today = new Date();
        const birthYear = today.getFullYear() - age;
        const birthDate = new Date(birthYear, today.getMonth(), today.getDate());
        const formattedDate = birthDate.toISOString().split('T')[0]; // Format to YYYY-MM-DD
        dateInput.value = formattedDate;
    } else {
        dateInput.value = "";
    }
    });
});

function calculateAge() {
    let birthday = document.getElementById("patient_birthday").value;
    if (birthday) {
        let birthDate = new Date(birthday);
        let today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        let monthDiff = today.getMonth() - birthDate.getMonth();
        
        // Adjust age if birth month hasn't occurred yet this year
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        document.getElementById("patient_age").value = age;
    }
}
function putRelation(){
    let relation = document.getElementById("relationQ").value;
    document.getElementById("relation").value= relation;
}
function showToast(message) {
    const toast = document.getElementById("toast");
    toast.textContent = message;
    toast.className = "toast show";
    setTimeout(() => {
        toast.className = toast.className.replace("show", "");
    }, 3000);
}

function showAdditionalInputs() {
    document.getElementById("patient_gender_select").style.display = "block";
    document.getElementById("patient_civil_status_select").style.display = "block";

}

function fillPatientGender() {
    const genderSelect = document.getElementById("patient_gender_select");
    const genderInput = document.getElementById("patient_gender");
    genderInput.value = genderSelect.value;
}

function fillPatientCivilStatus() {
    const civilStatusSelect = document.getElementById("patient_civil_status_select");
    const civilStatusInput = document.getElementById("patient_civil_status");
    civilStatusInput.value = civilStatusSelect.value;
}

function fillPatientAddress() {
    const sameAddressCheckbox = document.getElementById('same_address_checkbox');
    const clientSitio = document.getElementById('client_sitio');
    const clientBarangay = document.getElementById('client_barangay');
    const clientCompleteAddress = document.getElementById('client_complete_address');
    const patientSitio = document.getElementById('patient_sitio');
    const patientBarangay = document.getElementById('patient_barangay');
    const patientCompleteAddress = document.getElementById('patient_complete_address');

    if (sameAddressCheckbox.checked) {
        // Copy values from client to patient fields
        patientSitio.value = clientSitio.value;
        patientBarangay.value = clientBarangay.value;
        patientCompleteAddress.value = clientCompleteAddress.value;
        
        // Make the fields readonly when checked
        patientSitio.readOnly = true;
        patientBarangay.readOnly = true;
        patientCompleteAddress.readOnly = true;
    } else {
        // Clear the patient address fields
        patientSitio.value = '';
        patientBarangay.value = '';
        patientCompleteAddress.value = '';
        
        // Make the fields editable when unchecked
        patientSitio.readOnly = false;
        patientBarangay.readOnly = false;
        patientCompleteAddress.readOnly = false;
    }
}

// Add event listeners for patient address fields to auto-generate complete address
document.getElementById('patient_sitio').addEventListener('input', updatePatientCompleteAddress);
document.getElementById('patient_barangay').addEventListener('input', updatePatientCompleteAddress);

function updatePatientCompleteAddress() {
    const sitio = document.getElementById('patient_sitio').value;
    const barangay = document.getElementById('patient_barangay').value;
    const completeAddress = document.getElementById('patient_complete_address');
    
    if (sitio && barangay) {
        completeAddress.value = `${sitio}, ${barangay}, Gloria, Oriental Mindoro`;
    }
}

function checkFolder() {
    fetch('check_folder.php')
        .then(response => response.json())
        .then(data => {
            let messageDiv = document.getElementById('message');
            let scanButton = document.getElementById('scanButton');
            let fileInput = document.getElementById('fileInput');
            
            if (data.hasFile) {
                messageDiv.innerHTML = `<p style='color: green;'>File found: ${data.fileName}</p>`;
                fileInput.value = data.filePath;
                console.log(data.filePath);
                // scanButton.style.display = 'none';
            } else {
                messageDiv.innerHTML = `<p style='color: red;'>No file found. Please scan.</p>`;
                scanButton.style.display = 'inline-block';
            }
        });
}
