document.addEventListener('DOMContentLoaded', function () {
    function createEtchCentralFilter(wrapperSelector, inputId, labelText, placeholderText, itemSelector) {
        var wrapper = document.querySelector(wrapperSelector);

        if (!wrapper || document.getElementById(inputId)) {
            return;
        }

        var submenu = wrapper.querySelector('.ab-submenu');

        if (!submenu) {
            return;
        }

        var searchShell = document.createElement('div');
        searchShell.className = 'etch-central-menu-search-shell';
        searchShell.setAttribute('role', 'presentation');

        var label = document.createElement('label');
        label.className = 'etch-central-menu-search';
        label.setAttribute('for', inputId);

        var span = document.createElement('span');
        span.className = 'screen-reader-text';
        span.textContent = labelText;

        var input = document.createElement('input');
        input.id = inputId;
        input.className = 'etch-central-menu-search__input';
        input.type = 'search';
        input.placeholder = placeholderText;
        input.autocomplete = 'off';

        label.appendChild(span);
        label.appendChild(input);
        searchShell.appendChild(label);
        wrapper.insertBefore(searchShell, submenu);

        var items = Array.prototype.slice.call(document.querySelectorAll(itemSelector));

        input.addEventListener('click', function (event) {
            event.stopPropagation();
        });

        input.addEventListener('keydown', function (event) {
            event.stopPropagation();
        });

        input.addEventListener('input', function () {
            var query = input.value.trim().toLowerCase();

            items.forEach(function (item) {
                var title = item.textContent || '';
                item.hidden = query && !title.toLowerCase().includes(query);
            });
        });
    }



    function setupClickControlledEtchSubmenus() {
        var root = document.querySelector('#wpadminbar #wp-admin-bar-etch-central');

        if (!root) {
            return;
        }

        var submenuIds = [
            'wp-admin-bar-etch-central-all-templates',
            'wp-admin-bar-etch-central-all-patterns',
            'wp-admin-bar-etch-central-resources',
            'wp-admin-bar-etch-central-community'
        ];

        function getDisplayFor(item) {
            if (
                item.id === 'wp-admin-bar-etch-central-all-templates' ||
                item.id === 'wp-admin-bar-etch-central-all-patterns'
            ) {
                return 'flex';
            }

            return 'block';
        }

        function closeItem(item) {
            if (!item) {
                return;
            }

            item.classList.remove('etch-central-submenu-open');
            item.classList.remove('hover');

            var panel = item.querySelector(':scope > .ab-sub-wrapper');
            if (panel) {
                panel.style.setProperty('display', 'none', 'important');
                panel.style.setProperty('visibility', 'hidden', 'important');
                panel.style.setProperty('opacity', '0', 'important');
                panel.style.setProperty('pointer-events', 'none', 'important');
                panel.style.removeProperty('inline-size');
                panel.style.removeProperty('left');
                panel.style.removeProperty('top');
            }

            var link = item.querySelector(':scope > .ab-item');
            if (link) {
                link.setAttribute('aria-expanded', 'false');
            }
        }

        function openItem(item) {
            if (!item) {
                return;
            }

            var panel = item.querySelector(':scope > .ab-sub-wrapper');
            var link = item.querySelector(':scope > .ab-item');

            if (!panel || !link) {
                return;
            }

            submenuIds.forEach(function (id) {
                var other = document.getElementById(id);

                if (other && other !== item) {
                    closeItem(other);
                }
            });

            item.classList.add('etch-central-submenu-open');
            item.classList.add('hover');

            var rect = item.getBoundingClientRect();
            var panelWidth = Math.min(200, Math.floor(window.innerWidth * 0.9));
            var left = rect.right;
            var top = rect.top;

            if (left + panelWidth > window.innerWidth) {
                left = Math.max(0, rect.left - panelWidth);
            }

            panel.style.setProperty('display', getDisplayFor(item), 'important');
            panel.style.setProperty('visibility', 'visible', 'important');
            panel.style.setProperty('opacity', '1', 'important');
            panel.style.setProperty('pointer-events', 'auto', 'important');
            panel.style.setProperty('position', 'fixed', 'important');
            panel.style.setProperty('inline-size', panelWidth + 'px', 'important');
            panel.style.setProperty('left', left + 'px', 'important');
            panel.style.setProperty('top', top + 'px', 'important');

            link.setAttribute('aria-expanded', 'true');
        }

        function closeAll() {
            submenuIds.forEach(function (id) {
                closeItem(document.getElementById(id));
            });
        }

        function refreshOpenPanelPosition() {
            submenuIds.forEach(function (id) {
                var item = document.getElementById(id);

                if (item && item.classList.contains('etch-central-submenu-open')) {
                    openItem(item);
                }
            });
        }

        window.addEventListener('resize', refreshOpenPanelPosition);
        window.addEventListener('scroll', refreshOpenPanelPosition, true);

        submenuIds.forEach(function (id) {
            var item = document.getElementById(id);

            if (!item) {
                return;
            }

            var link = item.querySelector(':scope > .ab-item');
            var panel = item.querySelector(':scope > .ab-sub-wrapper');

            if (!link || !panel) {
                return;
            }

            item.classList.add('etch-central-click-submenu');
            link.setAttribute('aria-haspopup', 'true');
            link.setAttribute('aria-expanded', 'false');
            closeItem(item);

            item.addEventListener('mouseenter', function () {
                if (!item.classList.contains('etch-central-submenu-open')) {
                    item.classList.remove('hover');
                }
            });

            link.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                if (item.classList.contains('etch-central-submenu-open')) {
                    closeItem(item);
                    return;
                }

                openItem(item);
            });

            link.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();

                    if (item.classList.contains('etch-central-submenu-open')) {
                        closeItem(item);
                    } else {
                        openItem(item);
                    }
                }

                if (event.key === 'Escape') {
                    closeItem(item);
                    link.focus();
                }
            });

            panel.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        });

        document.addEventListener('click', function (event) {
            if (!root.contains(event.target)) {
                closeAll();
            }
        });

        root.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeAll();
            }
        });
    }

    setupClickControlledEtchSubmenus();

    createEtchCentralFilter(
        '#wpadminbar li#wp-admin-bar-etch-central-all-templates > .ab-sub-wrapper',
        'etch-central-template-search',
        'Search templates',
        'Search templates',
        '#wpadminbar #wp-admin-bar-etch-central-all-templates .etch-central-template-node'
    );

    createEtchCentralFilter(
        '#wpadminbar li#wp-admin-bar-etch-central-all-patterns > .ab-sub-wrapper',
        'etch-central-pattern-search',
        'Search patterns',
        'Search patterns',
        '#wpadminbar #wp-admin-bar-etch-central-all-patterns .etch-central-pattern-node'
    );
});
