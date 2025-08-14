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
      path = `saved/${service}_${documentType}.php`;
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

function showToast(message, onSaveAndPreview, showOnlyPreview) {
  const toast = document.getElementById('customToast');
  document.getElementById('toastMsg').textContent = message;
  toast.style.display = 'block';

  // Add two buttons
  let btns = `
    <button id="toastSavePreviewBtn" style="margin:8px;background:#222;color:#fff;padding:7px 18px;border-radius:5px;cursor:pointer;font-weight:600;">Save & Preview</button>
    <button id="toastPreviewOnlyBtn" style="margin:8px;background:#fff;color:#222;padding:7px 18px;border-radius:5px;cursor:pointer;font-weight:600;border:1px solid #222;">Preview Only</button>
    <span id="toastCountdown" style="margin-left:10px;"></span>
  `;
  toast.querySelector('.toast-actions').innerHTML = btns;

  // Countdown animation
  const countdownSpan = document.getElementById('toastCountdown');
  let seconds = 5;
  countdownSpan.textContent = `(Closing in ${seconds}...)`;
  let countdown = setInterval(() => {
    seconds--;
    if (seconds > 0) {
      countdownSpan.textContent = `(Closing in ${seconds}...)`;
    } else {
      countdownSpan.textContent = '';
      toast.style.display = 'none';
      clearInterval(countdown);
    }
  }, 1000);

  document.getElementById('toastSavePreviewBtn').onclick = function() {
    toast.style.display = 'none';
    clearInterval(countdown);
    countdownSpan.textContent = '';
    if (typeof onSaveAndPreview === 'function') onSaveAndPreview();
  };
  document.getElementById('toastPreviewOnlyBtn').onclick = function() {
    toast.style.display = 'none';
    clearInterval(countdown);
    countdownSpan.textContent = '';
    if (typeof showOnlyPreview === 'function') showOnlyPreview();
  };
}

document.getElementById('preview').addEventListener('click', function() {
  const beneficiaryId = document.getElementById('client_id').value;
  if (!beneficiaryId) {
    alert('Please select a beneficiary first.');
    return;
  }

  fetchLastTempTran(beneficiaryId).then(() => {
    if (!lastFormData) {
      showToast('No temporary transaction found for this beneficiary.', null, true);
      return;
    }

    showToast(
      'Save to database and preview? or just preview',
      function saveAndPreview() {
        // 1. Save to transactions table
        fetch('save_transaction.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(lastFormData)
        })
        .then(response => response.json())
        .then(dbResult => {
          if (dbResult.success && dbResult.transaction_id) {
            // 2. Finalize transaction
            const finalizeData = {
              beneficiary_id: lastFormData.beneficiary_id,
              transaction_id: dbResult.transaction_id,
              request_amount: lastFormData.amount || lastFormData.request_amount || 0,
              patient_name: lastFormData.patient_name || ''
            };
            return fetch('finalize_transaction.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(finalizeData)
            })
            .then(res => res.json())
            .then(finalizeResult => {
              if (finalizeResult.success) {
                // 3. Preview PDF
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
        });
      },
      function previewOnly() {
        // Just preview
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
      }
    );
  });
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

  // 1. Save to temp_tran first
  fetch('save_temp_tran.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(lastFormData)
  })
  .then(response => response.json())
  .then(tempResult => {
    if (tempResult.success) {
      // 2. If success, proceed to generate_certificates.php
      fetch('generate_certificates.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Certificate generated successfully!');
          document.getElementById('detailsModal').style.display = 'none';
          console.log('Certificate data:', data.req_pur);
          if (data.req_pur) {
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
    } else {
      alert('Error saving temporary transaction: ' + (tempResult.error || 'Unknown error.'));
    }
  })
  .catch(error => {
    alert('An error occurred while saving temporary transaction: ' + error);
  });
});

function fetchLastTempTran(beneficiaryId) {
  return fetch('get_temp_tran.php?beneficiary_id=' + encodeURIComponent(beneficiaryId))
    .then(res => res.json())
    .then(data => {
      if (data.success && data.transaction) {
        lastFormData = data.transaction;
      } else {
        lastFormData = null;
      }
    });
}