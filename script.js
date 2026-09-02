const toast = document.getElementById('toast');

function showToast(message) {
    if (!toast) return;
    toast.textContent = message;
    toast.classList.remove('hidden');
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 3500);
}

function handleFormSubmit(formId, successCallback) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        try {
            const response = await fetch('process.php', {
                method: 'POST',
                body: new FormData(form)
            });
            const result = await response.json();
            showToast(result.message);

            if (result.status === 'success') {
                form.reset();
                if (successCallback) successCallback(result);
            }
        } catch (error) {
            showToast('Unable to connect to the server.');
        }
    });
}

function deleteStaff(staffId) {
    if (!confirm('Are you sure you want to delete this staff member?')) return;
    const fd = new FormData();
    fd.append('action', 'admin_manage_staff');
    fd.append('sub_action', 'delete');
    fd.append('staff_id', staffId);

    fetch('process.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            showToast(d.message);
            setTimeout(() => window.location.reload(), 800);
        });
}

// Global Form Bindings
handleFormSubmit('loginForm', (data) => {
    if (data.redirect) {
        setTimeout(() => window.location.href = data.redirect, 600);
    }
});
handleFormSubmit('serviceForm', () => {
    setTimeout(() => window.location.href = 'index.php', 1000);
});
handleFormSubmit('complaintForm');
handleFormSubmit('profileForm');