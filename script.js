document.addEventListener('DOMContentLoaded', function () {
    var copyButtons = document.querySelectorAll('[data-copy-target]');
    for (var i = 0; i < copyButtons.length; i++) {
        copyButtons[i].addEventListener('click', function () {
            var input = document.getElementById(this.getAttribute('data-copy-target'));
            if (!input) { return; }
            var button = this;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(input.value).then(function () {
                    var original = button.textContent;
                    button.textContent = 'Скопировано';
                    setTimeout(function () { button.textContent = original; }, 1600);
                });
            } else {
                input.select(); document.execCommand('copy');
            }
        });
    }
    var confirmForms = document.querySelectorAll('form[data-confirm]');
    for (var j = 0; j < confirmForms.length; j++) {
        confirmForms[j].addEventListener('submit', function (event) {
            if (!confirm(this.getAttribute('data-confirm') || 'Подтвердите действие.')) { event.preventDefault(); }
        });
    }
    var reasonForms = document.querySelectorAll('form[data-require-reason]');
    for (var k = 0; k < reasonForms.length; k++) {
        reasonForms[k].addEventListener('submit', function (event) {
            var reason = prompt('Укажите причину отклонения:');
            if (!reason || !reason.trim()) { event.preventDefault(); return; }
            this.querySelector('input[name="rejection_reason"]').value = reason.trim();
        });
    }
    var commentForms = document.querySelectorAll('form[data-optional-comment]');
    for (var m = 0; m < commentForms.length; m++) {
        commentForms[m].addEventListener('submit', function () {
            var comment = prompt('Комментарий (необязательно):') || '';
            this.querySelector('input[name="admin_comment"]').value = comment;
        });
    }
    var validateForms = document.querySelectorAll('form[data-validate="true"]');
    for (var n = 0; n < validateForms.length; n++) {
        validateForms[n].addEventListener('submit', function (event) {
            if (!this.checkValidity()) { event.preventDefault(); this.reportValidity(); }
        });
    }
    var toggles = document.querySelectorAll('[data-notifications-toggle]');
    for (var t = 0; t < toggles.length; t++) {
        toggles[t].addEventListener('click', function () {
            var dropdown = this.parentNode.querySelector('.notification-dropdown');
            if (!dropdown) { return; }
            dropdown.hidden = !dropdown.hidden;
            var badge = this.querySelector('.badge');
            if (badge && this.getAttribute('data-read-url') && window.fetch) {
                fetch(this.getAttribute('data-read-url'), {method:'POST'});
                badge.parentNode.removeChild(badge);
            }
        });
    }
});
