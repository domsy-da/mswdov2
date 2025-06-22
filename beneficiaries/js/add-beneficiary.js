document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('beneficiaryForm');
    const inputs = form.querySelectorAll('.form-input, .form-select');

    // Add validation on blur (when field loses focus)
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        // Remove invalid state while typing/changing
        input.addEventListener(input.type === 'select-one' ? 'change' : 'input', function() {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });

    // Form submission validation
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
        }
    });

    function validateField(field) {
        if (!field.value && field.hasAttribute('required')) {
            field.classList.add('is-invalid');
            return false;
        } else {
            field.classList.remove('is-invalid');
            return true;
        }
    }
});