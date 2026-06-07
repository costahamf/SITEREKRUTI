document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-copy-target]').forEach((button) => {
        button.addEventListener('click', async () => {
            const input = document.getElementById(button.dataset.copyTarget);
            if (!input) return;
            try {
                await navigator.clipboard.writeText(input.value);
                const original = button.textContent;
                button.textContent = 'Скопировано';
                setTimeout(() => { button.textContent = original; }, 1800);
            } catch (error) {
                input.select();
                document.execCommand('copy');
            }
        });
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!confirm(form.dataset.confirm || 'Подтвердите действие.')) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('form[data-validate="true"]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!form.checkValidity()) {
                event.preventDefault();
                form.reportValidity();
            }
        });
    });
});
