document.addEventListener('DOMContentLoaded', function () {
    var list = document.querySelector('[data-etch-central-sortable]');

    if (!list) {
        return;
    }

    var draggedRow = null;

    function rows() {
        return Array.prototype.slice.call(list.querySelectorAll('[data-etch-central-row]'));
    }

    function renumberRows() {
        rows().forEach(function (row, index) {
            row.querySelectorAll('[data-etch-central-field]').forEach(function (field) {
                var fieldName = field.getAttribute('data-etch-central-field');
                field.name = 'etch_central_settings[community_links][' + index + '][' + fieldName + ']';
            });
        });
    }

    list.addEventListener('dragstart', function (event) {
        var row = event.target.closest('[data-etch-central-row]');

        if (!row) {
            return;
        }

        draggedRow = row;
        row.classList.add('is-dragging');
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', '');
    });

    list.addEventListener('dragover', function (event) {
        var targetRow = event.target.closest('[data-etch-central-row]');

        if (!draggedRow || !targetRow || draggedRow === targetRow) {
            return;
        }

        event.preventDefault();

        var rect = targetRow.getBoundingClientRect();
        var shouldInsertAfter = event.clientY > rect.top + rect.height / 2;

        list.insertBefore(draggedRow, shouldInsertAfter ? targetRow.nextSibling : targetRow);
    });

    list.addEventListener('dragend', function () {
        if (draggedRow) {
            draggedRow.classList.remove('is-dragging');
        }

        draggedRow = null;
        renumberRows();
    });

    list.addEventListener('click', function (event) {
        var button = event.target.closest('[data-etch-central-move]');

        if (!button) {
            return;
        }

        var row = button.closest('[data-etch-central-row]');
        var direction = button.getAttribute('data-etch-central-move');

        if (!row) {
            return;
        }

        if ('up' === direction && row.previousElementSibling) {
            list.insertBefore(row, row.previousElementSibling);
            row.focus({preventScroll: true});
        }

        if ('down' === direction && row.nextElementSibling) {
            list.insertBefore(row.nextElementSibling, row);
            row.focus({preventScroll: true});
        }

        renumberRows();
    });

    renumberRows();
});
