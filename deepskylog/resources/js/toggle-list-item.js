/**
 * Handle toggle list item forms via AJAX to preserve scroll position and show toast notifications.
 */
export function initToggleListItemForms() {
    document.addEventListener('submit', async (e) => {
        const form = e.target;
        if (!form.classList.contains('toggle-list-form')) {
            return;
        }

        e.preventDefault();

        const formData = new FormData(form);
        const button = form.querySelector('button[type="submit"]');
        const originalInnerHTML = button.innerHTML;
        const token = formData.get('_token');
        const objectName = formData.get('object_name');

        try {
            // Disable button during submission
            button.disabled = true;
            button.style.opacity = '0.5';

            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    object_name: objectName,
                    _token: token,
                }),
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Toggle button appearance based on action
                if (data.action === 'added') {
                    // Switch to remove state (red, minus icon)
                    button.classList.remove('text-green-400', 'hover:text-green-300');
                    button.classList.add('text-red-400', 'hover:text-red-300');
                    button.title = button.dataset.removedTitle || 'Remove from active observing list';
                    button.innerHTML = `
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    `;
                } else if (data.action === 'removed') {
                    // Switch to add state (green, plus icon)
                    button.classList.remove('text-red-400', 'hover:text-red-300');
                    button.classList.add('text-green-400', 'hover:text-green-300');
                    button.title = button.dataset.addedTitle || 'Add to active observing list';
                    button.innerHTML = `
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    `;
                }

                // Show success toast
                showToast(data.message, 'success');
            } else {
                // Show error toast
                showToast(data.message || 'An error occurred', 'error');
                button.innerHTML = originalInnerHTML;
            }
        } catch (error) {
            console.error('Error toggling list item:', error);
            showToast('An error occurred. Please try again.', 'error');
            button.innerHTML = originalInnerHTML;
        } finally {
            button.disabled = false;
            button.style.opacity = '1';
        }
    });
}

/**
 * Show a toast notification.
 * @param {string} message - The message to display
 * @param {string} type - The type of toast: 'success' or 'error'
 */
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'polite');

    const container = document.getElementById('toast-container') || createToastContainer();
    container.appendChild(toast);

    // Remove toast after 4 seconds
    setTimeout(() => {
        toast.classList.add('toast-fade-out');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

/**
 * Create the toast container if it doesn't exist.
 */
function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initToggleListItemForms);
} else {
    initToggleListItemForms();
}
