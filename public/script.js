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
                input.select();
                document.execCommand('copy');
            }
        });
    }

    var faqItems = document.querySelectorAll('.faq-item');
    for (var f = 0; f < faqItems.length; f++) {
        var question = faqItems[f].querySelector('.faq-question');
        var answer = faqItems[f].querySelector('.faq-answer');
        if (!question || !answer) { continue; }
        question.setAttribute('aria-expanded', 'false');
        answer.style.maxHeight = '0px';
        question.addEventListener('click', function () {
            var item = this.parentNode;
            var panel = item.querySelector('.faq-answer');
            var isOpen = item.className.indexOf('is-open') !== -1;
            for (var x = 0; x < faqItems.length; x++) {
                faqItems[x].className = faqItems[x].className.replace(' is-open', '').replace('is-open', '');
                var otherAnswer = faqItems[x].querySelector('.faq-answer');
                var otherButton = faqItems[x].querySelector('.faq-question');
                if (otherAnswer) { otherAnswer.style.maxHeight = '0px'; }
                if (otherButton) { otherButton.setAttribute('aria-expanded', 'false'); }
            }
            if (!isOpen) {
                item.className += ' is-open';
                panel.style.maxHeight = panel.scrollHeight + 'px';
                this.setAttribute('aria-expanded', 'true');
            }
        });
    }

    var sidebarToggles = document.querySelectorAll('[data-sidebar-toggle]');
    var sidebarClosers = document.querySelectorAll('[data-sidebar-close], [data-sidebar-backdrop]');
    for (var s = 0; s < sidebarToggles.length; s++) {
        sidebarToggles[s].addEventListener('click', function () {
            document.body.className += document.body.className.indexOf('sidebar-open') === -1 ? ' sidebar-open' : '';
        });
    }
    for (var c = 0; c < sidebarClosers.length; c++) {
        sidebarClosers[c].addEventListener('click', function () {
            document.body.className = document.body.className.replace(' sidebar-open', '').replace('sidebar-open', '');
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
                fetch(this.getAttribute('data-read-url'), { method: 'POST' });
                badge.parentNode.removeChild(badge);
            }
        });
    }
});
