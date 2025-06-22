function validateForm() {
    const form = document.getElementById('beneficiaryForm');
    const requiredFields = form.querySelectorAll('[required]');
    
    for (let field of requiredFields) {
        if (!field.value.trim()) {
            alert(`Please fill in the ${field.getAttribute('name')} field`);
            field.focus();
            return false;
        }
    }

    // Validate age
    const age = document.getElementById('age');
    if (parseInt(age.value) < 0 || parseInt(age.value) > 120) {
        alert('Please enter a valid age between 0 and 120');
        age.focus();
        return false;
    }

    return true;
}

// Auto-calculate age when birthday changes
document.getElementById('birthday').addEventListener('change', function() {
    const birthday = new Date(this.value);
    const today = new Date();
    let age = today.getFullYear() - birthday.getFullYear();
    const monthDiff = today.getMonth() - birthday.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthday.getDate())) {
        age--;
    }
    
    document.getElementById('age').value = age;
});