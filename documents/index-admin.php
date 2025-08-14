<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>ADMIN Editable Certificate</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="css/index.css" />
</head>
<body>

<div style="display: flex; align-items: center; gap: 18px; margin-bottom: 2px; flex-wrap: wrap; justify-content: center;">
  <span style="display: flex; align-items: center; gap: 8px;">
    <span style="background:rgb(15, 15, 24); color: #fff; font-weight: bold; font-size: 1.2rem; padding: 6px 16px; border-radius: 8px; letter-spacing: 2px; box-shadow: 0 2px 8px rgba(25,118,210,0.12); font-family: 'Segoe UI', Arial, sans-serif;">
      <svg width="22" height="22" viewBox="0 0 22 22" style="vertical-align: middle; margin-right: 6px;">
        <circle cx="11" cy="11" r="10" fill="#fff" stroke="#26200E" stroke-width="2"/>
        <text x="12" y="15" text-anchor="middle" font-size="12" fill="#1976d2" font-family="Arial" font-weight="bold">A</text>
      </svg>
      ADMINPAGE
    </span>
  </span>
  <!-- Service Dropdown -->
  <label for="service_select" style="font-weight: bold; color: #333;">Select Service:</label>
  <select id="service_select" style="padding: 6px 12px; border-radius: 6px; border: 1px solid #bbb; background: #fafbfc; font-size: 1rem;">
    <option value="">-- Select Service --</option>
    <option value="medical">Medical Expenses</option>
    <option value="burial">Burial</option>
    <option value="educational">Educational</option>
  </select>

  <!-- Document Dropdown -->
  <label for="document_select" style="font-weight: bold; color: #333;">Select Document:</label>
  <select id="document_select" disabled style="padding: 6px 12px; border-radius: 6px; border: 1px solid #bbb; background: #fafbfc; font-size: 1rem;">
    <option value="">-- Select Document --</option>
  </select>
  <a href="javascript:window.history.back();" style="margin-left: 12px; color:rgb(78, 78, 78); text-decoration: none; font-weight: bold;">&larr; Go Back</a>
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

<!-- Buttons -->
<button id="save">💾 Save Certificate</button>
<button id="preview">👁️ Preview PDF</button>

<script src="tinymce/tinymce.min.js"></script>

<script>
  tinymce.init({
  selector: '#certificate',
  height: 600,
  menubar: false,
  plugins: 'lists link image table hr',
  toolbar: 'undo redo | formatselect | fontsizeselect fontselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | hr | removeformat'
});

// Document options for each service
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

const serviceSelect = document.getElementById('service_select');
const documentSelect = document.getElementById('document_select');

// Populate Document dropdown when service changes
serviceSelect.addEventListener('change', function() {
  const selectedService = this.value;
  documentSelect.innerHTML = '<option value="">-- Select Document --</option>';

  if (selectedService && documentOptions[selectedService]) {
    documentSelect.disabled = false;
    documentOptions[selectedService].forEach(opt => {
      // Build filename for display
      const filename = `${selectedService}_${opt.value}`;
      const option = document.createElement('option');
      option.value = opt.value;
      option.textContent = filename;
      documentSelect.appendChild(option);
    });
  } else {
    documentSelect.disabled = true;
  }

  // Optionally clear the editor
  tinymce.get('certificate').setContent('');
});

documentSelect.addEventListener('change', function() {
  const service = serviceSelect.value;
  const documentType = this.value;

  if (service && documentType) {
    const savedFilename = `${service}_${documentType}.html`;
    const savedPath = `saved/${savedFilename}?v=${Date.now()}`;

    fetch(savedPath)
      .then(response => {
        if (response.ok) {
          return response.text();
        } else {
          // If not found, fallback to templates/
          const templateFilename = `${service}_${documentType}.php`;
          const templatePath = `templates/${templateFilename}?v=${Date.now()}`;
          return fetch(templatePath).then(templateResponse => {
            if (templateResponse.ok) {
              return templateResponse.text();
            } else {
              throw new Error('Template not found in templates/ either.');
            }
          });
        }
      })
      .then(data => {
        tinymce.get('certificate').setContent(data);
      })
      .catch(err => {
        tinymce.get('certificate').setContent('<p style="color:red;text-align:center;">Template not found in saved/ or templates/.</p>');
      });
  }
});

// Save Certificate button
document.getElementById('save').addEventListener('click', function() {
  const service = serviceSelect.value;
  const documentType = documentSelect.value;

  if (!service || !documentType) {
    alert('Please select Service and Document first.');
    return;
  }

  const html = tinymce.get('certificate').getContent();
  const filename = `${service}_${documentType}.html`;

  fetch('save_certificate.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'html=' + encodeURIComponent(html) + '&filename=' + encodeURIComponent(filename)
  })
  .then(res => res.text())
  .then(msg => {
    alert(msg);
  });
});

// Preview PDF button
document.getElementById('preview').addEventListener('click', function() {
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
});
</script>

</body>
</html>
