// When the page loads, set request_date to today's date
window.addEventListener('DOMContentLoaded', function() {
  const today = new Date().toISOString().split('T')[0];
  document.getElementById('request_date').value = today;
});

function fillPatientCompleteAddress() {
  // Get client complete address value
  const clientAddress = document.getElementById('complete_address').value;
  const clientSitio = document.getElementById('sitio').value;
  const clientBarangay = document.getElementById('barangay').value;

  // Copy it to patient complete address
  document.getElementById('patient_complete_address').value = clientAddress;
  document.getElementById('patient_sitio').value = clientSitio;
  document.getElementById('patient_barangay').value = clientBarangay;
}

function calculateAgeFromBirthday() {
          const birthday = document.getElementById('patient_birthday').value;
          if (birthday) {
            const birthDate = new Date(birthday);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
              age--;
            }
            document.getElementById('patient_age').value = age;
          }
        }

        function calculateBirthdayFromAge() {
          const age = parseInt(document.getElementById('patient_age').value, 10);
          if (!isNaN(age)) {
            const today = new Date();
            const birthYear = today.getFullYear() - age;
            // Set birthday to today's month and day, but birthYear years ago
            const birthDate = new Date(birthYear, today.getMonth(), today.getDate());
            // Format as yyyy-mm-dd
            const yyyy = birthDate.getFullYear();
            const mm = String(birthDate.getMonth() + 1).padStart(2, '0');
            const dd = String(birthDate.getDate()).padStart(2, '0');
            document.getElementById('patient_birthday').value = `${yyyy}-${mm}-${dd}`;
          }
        }