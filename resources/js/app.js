import './bootstrap';

// Bootstrap's JavaScript for dropdowns, modals, and the mobile menu.
import * as bootstrap from 'bootstrap';
import $ from 'jquery';
import select2 from 'select2';

window.bootstrap = bootstrap;
window.$ = window.jQuery = $;

// Select2 is a jQuery plugin. Calling the package attaches it to jQuery.
select2(window, $);

// Turn every form dropdown into a searchable Select2 box.
function enableSearchableSelects() {
    $('select.form-select').each(function () {
        var select = $(this);

        if (select.hasClass('select2-hidden-accessible')) {
            return;
        }

        select.select2({
            width: '100%',
            minimumResultsForSearch: 0
        });

        // Select2 announces a change through jQuery. Plain listeners, such as
        // the "Other" source field, only hear a real browser event.
        select.on('change', function (event) {
            if (event.originalEvent) {
                return;
            }

            this.dispatchEvent(new Event('change', { bubbles: true }));
        });

        select.on('select2:open', function () {
            var field = document.querySelector('.select2-container--open .select2-search__field');

            if (field) {
                field.setAttribute('placeholder', 'Search');
                field.focus();
            }
        });

        if (select.hasClass('is-invalid')) {
            select.next('.select2-container').addClass('is-invalid');
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', enableSearchableSelects);
} else {
    enableSearchableSelects();
}
