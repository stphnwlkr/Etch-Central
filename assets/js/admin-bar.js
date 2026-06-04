document.addEventListener('DOMContentLoaded', function () {
    var root = document.querySelector('#wpadminbar #wp-admin-bar-etch-central');

    if (!root) {
        return;
    }

    var trigger = root.querySelector(':scope > .ab-item');
    var panelNode = root.querySelector('#wp-admin-bar-etch-central-panel');
    var panelWrapper = root.querySelector(':scope > .ab-sub-wrapper');

    if (!trigger || !panelNode || !panelWrapper) {
        return;
    }

    trigger.setAttribute('aria-haspopup', 'true');
    trigger.setAttribute('aria-expanded', 'false');

    function closePanel() {
        root.classList.remove('etch-central-panel-open');
        root.classList.remove('hover');
        trigger.setAttribute('aria-expanded', 'false');
    }

    function openPanel() {
        root.classList.add('etch-central-panel-open');
        root.classList.add('hover');
        trigger.setAttribute('aria-expanded', 'true');
    }

    function togglePanel() {
        if (root.classList.contains('etch-central-panel-open')) {
            closePanel();
        } else {
            openPanel();
        }
    }

    trigger.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        togglePanel();
    });

    trigger.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            togglePanel();
        }

        if (event.key === 'Escape') {
            closePanel();
            trigger.focus();
        }
    });

    panelWrapper.addEventListener('click', function (event) {
        event.stopPropagation();
    });

    panelWrapper.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closePanel();
            trigger.focus();
        }
    });

    document.addEventListener('click', function (event) {
        if (!root.contains(event.target)) {
            closePanel();
        }
    });

    root.querySelectorAll('[data-etch-central-pane-trigger]').forEach(function (button) {
        button.addEventListener('click', function () {
            var targetId = button.getAttribute('data-etch-central-pane-trigger');
            var target = targetId ? root.querySelector('#' + CSS.escape(targetId)) : null;

            if (!target) {
                return;
            }

            root.querySelectorAll('[data-etch-central-pane-trigger]').forEach(function (otherButton) {
                var isActive = otherButton === button;
                otherButton.classList.toggle('is-active', isActive);
                otherButton.setAttribute('aria-expanded', isActive ? 'true' : 'false');
            });

            root.querySelectorAll('.etch-central-panel__pane').forEach(function (pane) {
                var isActive = pane === target;
                pane.classList.toggle('is-active', isActive);
                pane.hidden = !isActive;
            });

            var input = target.querySelector('[data-etch-central-browser-search]');
            if (input) {
                input.focus();
            }
        });
    });

    root.querySelectorAll('[data-etch-central-browser-search]').forEach(function (input) {
        var pane = input.closest('.etch-central-panel__pane');
        var list = pane ? pane.querySelector('[data-etch-central-browser-results]') : null;

        if (!pane || !list) {
            return;
        }

        var rows = Array.prototype.slice.call(list.querySelectorAll('.etch-central-panel__item'));

        input.addEventListener('input', function () {
            var query = input.value.trim().toLowerCase();

            rows.forEach(function (row) {
                var text = row.textContent || '';
                row.hidden = query && !text.toLowerCase().includes(query);
            });
        });
    });
    root.querySelectorAll('[data-etch-central-wp-editor-url]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            var wpEditorUrl = link.getAttribute('data-etch-central-wp-editor-url');
            var wantsWpEditor = Boolean(wpEditorUrl && (event.metaKey || event.ctrlKey) && event.altKey);

            if (!wantsWpEditor) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            window.open(wpEditorUrl, '_blank', 'noopener');
        });
    });

});
