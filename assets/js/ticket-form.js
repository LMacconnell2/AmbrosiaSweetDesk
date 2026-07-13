(function () {
    const config = window.SweetDeskTicketForm || {};

    function showNotice(type, message) {
        const success = document.getElementById('sd-frontend-ticket-success');
        const error = document.getElementById('sd-frontend-ticket-error');
        const successText = document.getElementById('sd-frontend-ticket-success-text');
        const errorText = document.getElementById('sd-frontend-ticket-error-text');

        if (success) {
            success.hidden = type !== 'success';
        }

        if (error) {
            error.hidden = type !== 'error';
        }

        if (type === 'success' && successText) {
            successText.textContent = message;
        }

        if (type === 'error' && errorText) {
            errorText.textContent = message;
        }
    }

    function hideNotices() {
        showNotice('', '');
    }

    function api(path, options = {}) {
        return fetch(`${config.apiUrl}${path}`, {
            ...options,
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': config.nonce,
                ...(options.headers || {})
            }
        }).then(async (response) => {
            const data = await response.json().catch(() => null);

            if (!response.ok || data?.success === false) {
                throw new Error(data?.message || 'Request failed.');
            }

            return data;
        });
    }

    function resetForm(form) {
        form.reset();
        SweetDeskEditor.setContent('sd-frontend-ticket-body', '');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('sd-frontend-ticket-form');

        if (!form) {
            return;
        }

        SweetDeskEditor.init('sd-frontend-ticket-body', {
            onCtrlEnter: () => form.requestSubmit()
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            hideNotices();

            const titleInput = document.getElementById('sd-frontend-ticket-title');
            const submitButton = document.getElementById('sd-frontend-ticket-submit');

            const title = titleInput?.value.trim() || '';
            const message = SweetDeskEditor.getContent('sd-frontend-ticket-body');

            if (!title) {
                showNotice('error', 'Title is required.');
                titleInput?.focus();
                return;
            }

            if (SweetDeskEditor.isEmpty('sd-frontend-ticket-body')) {
                showNotice('error', 'Initial message is required.');
                SweetDeskEditor.focus('sd-frontend-ticket-body');
                return;
            }

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Submitting...';
            }

            try {
                const result = await api('/tickets', {
                    method: 'POST',
                    body: JSON.stringify({
                        title,
                        message
                    })
                });

                resetForm(form);
                showNotice(
                    'success',
                    result.message ||
                        `Ticket #${result.ticket_id} submitted successfully.`
                );
            } catch (error) {
                showNotice('error', error.message || 'Failed to submit ticket.');
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Submit Ticket';
                }
            }
        });
    });
})();
