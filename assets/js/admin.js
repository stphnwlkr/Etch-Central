document.addEventListener('DOMContentLoaded', function () {
    function setupRepeater(list) {
        var fieldKey = list.getAttribute('data-etch-central-repeater') || 'shortcut_links';
        var draggedRow = null;
        function rows() { return Array.prototype.slice.call(list.querySelectorAll('[data-etch-central-row]')); }
        function renumberRows() {
            rows().forEach(function (row, index) {
                row.querySelectorAll('[data-etch-central-field]').forEach(function (field) {
                    field.name = 'etch_central_settings[' + fieldKey + '][' + index + '][' + field.getAttribute('data-etch-central-field') + ']';
                });
            });
        }
        function blankRow() {
            var source = rows()[0];
            if (!source) { return null; }
            var row = source.cloneNode(true);
            row.querySelectorAll('input').forEach(function (input) { input.value = ''; });
            row.classList.remove('is-dragging');
            return row;
        }
        list.addEventListener('dragstart', function (event) {
            var row = event.target.closest('[data-etch-central-row]');
            if (!row) { return; }
            draggedRow = row; row.classList.add('is-dragging');
            event.dataTransfer.effectAllowed = 'move'; event.dataTransfer.setData('text/plain', '');
        });
        list.addEventListener('dragover', function (event) {
            var targetRow = event.target.closest('[data-etch-central-row]');
            if (!draggedRow || !targetRow || draggedRow === targetRow) { return; }
            event.preventDefault();
            var rect = targetRow.getBoundingClientRect();
            list.insertBefore(draggedRow, event.clientY > rect.top + rect.height / 2 ? targetRow.nextSibling : targetRow);
        });
        list.addEventListener('dragend', function () { if (draggedRow) { draggedRow.classList.remove('is-dragging'); } draggedRow = null; renumberRows(); });
        list.addEventListener('click', function (event) {
            var removeButton = event.target.closest('[data-etch-central-remove]');
            if (removeButton) {
                var removeRow = removeButton.closest('[data-etch-central-row]');
                if (removeRow && rows().length > 1) { removeRow.remove(); } else if (removeRow) { removeRow.querySelectorAll('input').forEach(function (input) { input.value = ''; }); }
                renumberRows(); return;
            }
            var button = event.target.closest('[data-etch-central-move]');
            if (!button) { return; }
            var row = button.closest('[data-etch-central-row]');
            if (!row) { return; }
            if ('up' === button.getAttribute('data-etch-central-move') && row.previousElementSibling) { list.insertBefore(row, row.previousElementSibling); row.focus({preventScroll: true}); }
            if ('down' === button.getAttribute('data-etch-central-move') && row.nextElementSibling) { list.insertBefore(row.nextElementSibling, row); row.focus({preventScroll: true}); }
            renumberRows();
        });
        document.querySelectorAll('[data-etch-central-add-row="' + fieldKey + '"]').forEach(function (button) {
            button.addEventListener('click', function () { var row = blankRow(); if (!row) { return; } list.appendChild(row); renumberRows(); var input = row.querySelector('input'); if (input) { input.focus(); } });
        });
        renumberRows();
    }
    document.querySelectorAll('[data-etch-central-sortable]').forEach(setupRepeater);
});

(function () {
    function setAppearance(root, value) {
        if (!root) { return; }
        root.classList.remove('etch-central-admin--auto', 'etch-central-admin--light', 'etch-central-admin--dark');
        root.classList.add('etch-central-admin--' + value);
    }

    function saveAppearance(value, status) {
        if (!window.etchCentralAdmin || !window.etchCentralAdmin.ajaxUrl) { return; }
        if (status) { status.textContent = 'Saving…'; }
        var body = new URLSearchParams();
        body.set('action', 'etch_central_save_appearance');
        body.set('nonce', window.etchCentralAdmin.nonce || '');
        body.set('appearance', value);
        fetch(window.etchCentralAdmin.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            body: body.toString()
        }).then(function (response) {
            if (!response.ok) { throw new Error('Request failed'); }
            return response.json();
        }).then(function () {
            if (status) { status.textContent = 'Saved'; window.setTimeout(function () { status.textContent = ''; }, 1600); }
        }).catch(function () {
            if (status) { status.textContent = 'Could not save automatically. Use Save Settings.'; }
        });
    }

    document.addEventListener('change', function (event) {
        var input = event.target.closest('[data-etch-central-appearance-control] input[type="radio"]');
        if (!input) { return; }
        var root = input.closest('[data-etch-central-admin]');
        var status = root ? root.querySelector('[data-etch-central-save-status]') : null;
        setAppearance(root, input.value);
        saveAppearance(input.value, status);
    });
}());
