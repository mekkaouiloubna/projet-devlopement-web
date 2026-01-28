document.addEventListener('submit', function (e) {
    const form = e.target;

    if (form.hasAttribute('data-ajax')) {
        e.preventDefault();

        fetch(form.action, {
            method: form.method || 'POST',
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Opération réussie');
            } else {
                showToast(data.message || 'Erreur', 'error');
            }
        })
        .catch(() => {
            showToast('Erreur serveur', 'error');
        });
    }
});
