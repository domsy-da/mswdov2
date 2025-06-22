tinymce.init({
  selector: '#certificate',
  height: 600,
  menubar: false,
  plugins: 'lists link image table hr',
  toolbar: 'undo redo | formatselect | fontsizeselect fontselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | hr | removeformat'
});
let lastFormData = null;

// Dropdowns
const serviceSelect = document.getElementById('service_select');
const documentSelect = document.getElementById('document_select');

const documentOptions = {
  medical: [
    { value: 'certificate_of_eligibility', text: 'Certificate of Eligibility' },
    { value: 'certificate', text: 'Certificate' },
    { value: 'social_case_study', text: 'Social Case Study' }
  ],
  burial: [
    { value: 'certificate_of_eligibility', text: 'Certificate of Eligibility' },
    { value: 'certificate', text: 'Certificate' },
    { value: 'social_case_study', text: 'Social Case Study' }
  ],
  educational: [
    { value: 'certificate_of_eligibility', text: 'Certificate of Eligibility' },
    { value: 'certificate', text: 'Certificate' },
    { value: 'social_case_study', text: 'Social Case Study' }
  ]
};

// Populate Document dropdown
serviceSelect.addEventListener('change', function() {
  const selectedService = this.value;
  documentSelect.innerHTML = '<option value="">-- Select Document --</option>';

  if (selectedService && documentOptions[selectedService]) {
    documentSelect.disabled = false;
    documentOptions[selectedService].forEach(opt => {
      const option = document.createElement('option');
      option.value = opt.value;
      option.textContent = opt.text;
      documentSelect.appendChild(option);
    });
  } else {
    documentSelect.disabled = true;
  }

  // Clear editor to default welcome message
  tinymce.get('certificate').setContent(`
    <div style="text-align: center; margin-bottom: 30px;">
      <img src="img/mswdo.jpg" alt="MSWDO Logo" style="width: 120px; height: auto; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.15); border: 2px solid #e0e0e0; background: #fff; padding: 8px;">
    </div>
    <h2 style="text-align: center;">Welcome to the Certificate & Document Generator</h2>
    <p style="text-align: center;">Use the dropdown menus above to select a <strong>Service</strong> and <strong>Document</strong> type.</p>
    <p style="text-align: center;">Once selected, the appropriate template will load here automatically for you to edit, preview, and fill out.</p>
    <p style="text-align: center; font-style: italic;">Start by choosing a service and document to generate your certificate or social case study report.</p>
  `);
});

// Load selected template OR saved version
documentSelect.addEventListener('change', function() {
  const service = serviceSelect.value;
  const documentType = this.value;
  const beneficiaryId = document.getElementById('client_id').value; // Get beneficiary ID

  if (service && documentType) {
    let path;
    
    // Check if we have a beneficiary ID
    if (beneficiaryId) {
      // Load from beneficiary-specific directory
      path = `gen_saved/${beneficiaryId}/${service}_${documentType}.php`;
    } else {
      // Load from templates directory
      path = `templates/${service}_${documentType}.php`;
    }

    fetch(path)
      .then(r => r.text())
      .then(data => {
        tinymce.get('certificate').setContent(data);
      })
      .catch(err => {
        console.error(err);
        alert('Error loading template. Please make sure the template exists.');
      });
  }
});
let lastTransactionId = null;

document.getElementById('preview').addEventListener('click', function() {
  if (!lastFormData) {
    alert('Please fill and submit the details form first.');
    return;
  }

  if (lastTransactionId) {
    // Only preview PDF on subsequent clicks
    const html = tinymce.get('certificate').getContent();
    fetch('generate_pdf.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'html=' + encodeURIComponent(html)
    })
    .then(res => res.blob())
    .then(blob => {
      const url = URL.createObjectURL(blob);
      window.open(url, '_blank');
    });
    return;
  }

  if (confirm('Previewing will also insert this data into the database. Continue?')) {
    // 1. Insert to database
    fetch('save_transaction.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(lastFormData)
    })
    .then(response => response.json())
    .then(dbResult => {
      if (dbResult.success && dbResult.transaction_id) {
        // 2. Finalize transaction (money status, budget, etc.)
        fetch('finalize_transaction.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            beneficiary_id: lastFormData.beneficiary_id,
            transaction_id: dbResult.transaction_id,
            request_amount: lastFormData.request_amount,
            patient_name: lastFormData.patient_name
          })
        })
        .then(res => res.json())
        .then(finalizeResult => {
          if (finalizeResult.success) {
            lastTransactionId = dbResult.transaction_id; // Store for next clicks
            // 3. Proceed to preview PDF
            const html = tinymce.get('certificate').getContent();
            fetch('generate_pdf.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: 'html=' + encodeURIComponent(html)
            })
            .then(res => res.blob())
            .then(blob => {
              const url = URL.createObjectURL(blob);
              window.open(url, '_blank');
            });
          } else {
            alert('Finalize transaction failed: ' + (finalizeResult.error || 'Unknown error.'));
          }
        });
      } else {
        alert('Database insert failed: ' + (dbResult.error || 'Unknown error.'));
      }
    })
    .catch(error => {
      alert('An error occurred while inserting to the database: ' + error);
    });
  }
});
// Show & Close modal & Insert details (unchanged)
document.getElementById('fillDetails').addEventListener('click', function() {
  document.getElementById('detailsModal').style.display = 'block';
});
document.getElementById('closeModal').addEventListener('click', function() {
  document.getElementById('detailsModal').style.display = 'none';
});

document.getElementById('detailsForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const form = e.target;
  const formData = new FormData(form);

  lastFormData = {};
  formData.forEach((value, key) => {
    lastFormData[key] = value;
  });

  fetch('generate_certificates.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    // Handle response (success/failure)
    if (data.success) {
      alert('Certificate generated successfully!');
      // Optionally close modal or update UI
      document.getElementById('detailsModal').style.display = 'none';
      console.log('Certificate data:', data.req_pur);
      if (data.req_pur) {
          // Map form value to service_select value
          let serviceValue = '';
          if (data.req_pur === 'med_exp') serviceValue = 'medical';
          else if (data.req_pur === 'educational') serviceValue = 'educational';
          else if (data.req_pur === 'burial') serviceValue = 'burial';

          document.getElementById('service_select').value = serviceValue;
          document.getElementById('service_select').dispatchEvent(new Event('change'));
      }
    } else {
      alert('Error: ' + (data.message || 'Could not generate certificate.'));
    }
  })
  .catch(error => {
    alert('An error occurred: ' + error);
  });
});