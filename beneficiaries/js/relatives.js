function openModal(mode, id = null) {
    const modal = document.getElementById('relativeModal');
    const form = document.getElementById('relativeForm');
    const title = document.getElementById('modalTitle');

    if (mode === 'edit' && id) {
        title.textContent = 'Edit Relative';
        fetchRelative(id);
    } else {
        title.textContent = 'Add New Relative';
        form.reset();
    }

    modal.style.display = 'block';
}

function closeModal() {
    const modal = document.getElementById('relativeModal');
    modal.style.display = 'none';
}

async function fetchRelative(id) {
    try {
        const response = await fetch(`relative_actions.php?action=get&id=${id}`);
        const data = await response.json();
        if (data.success) {
            populateForm(data.relative);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function populateForm(relative) {
    document.getElementById('relative_id').value = relative.id;
    document.getElementById('name').value = relative.name;
    document.getElementById('age').value = relative.age;
    document.getElementById('civil_status').value = relative.civil_status;
    document.getElementById('relationship').value = relative.relationship;
    document.getElementById('educational_attainment').value = relative.educational_attainment || '';
    document.getElementById('occupation').value = relative.occupation || '';
}

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('relativeForm');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            try {
                const response = await fetch('relative_actions.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    closeModal();
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to save relative'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error saving relative');
            }
        });
    }
});

async function editRelative(id) {
    try {
        const response = await fetch(`relative_actions.php?action=get&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            openModal('edit', id);
            document.getElementById('relative_id').value = data.relative.id;
            document.getElementById('name').value = data.relative.name;
            document.getElementById('age').value = data.relative.age;
            document.getElementById('civil_status').value = data.relative.civil_status;
            document.getElementById('relationship').value = data.relative.relationship;
            document.getElementById('educational_attainment').value = data.relative.educational_attainment || '';
            document.getElementById('occupation').value = data.relative.occupation || '';
        } else {
            alert('Error fetching relative data');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error fetching relative data');
    }
}

async function deleteRelative(id) {
    if (confirm('Are you sure you want to delete this relative?')) {
        try {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            const response = await fetch('relative_actions.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting relative');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error deleting relative');
        }
    }
}