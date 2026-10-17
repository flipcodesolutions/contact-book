// Small helpers used on every page: stop double submits, and the install button.

document.addEventListener('submit', function (event) {
    var form = event.target;

    if (!form.classList || !form.classList.contains('js-single-submit')) {
        return;
    }

    if (form.getAttribute('data-submitting') === 'yes') {
        event.preventDefault();
        return;
    }

    form.setAttribute('data-submitting', 'yes');

    // Disable the button on the next tick so the browser still sends the form.
    window.setTimeout(function () {
        var button = form.querySelector('[type="submit"]');

        if (button) {
            button.disabled = true;
        }
    }, 0);
});

var deferredInstallPrompt = null;

window.addEventListener('beforeinstallprompt', function (event) {
    event.preventDefault();
    deferredInstallPrompt = event;

    var button = document.getElementById('install-app-button');
    var note = document.getElementById('install-app-note');

    if (button) {
        button.hidden = false;
    }

    if (note) {
        note.hidden = true;
    }
});

document.addEventListener('click', function (event) {
    var button = event.target.closest ? event.target.closest('#install-app-button') : null;

    if (!button || !deferredInstallPrompt) {
        return;
    }

    deferredInstallPrompt.prompt();
    deferredInstallPrompt = null;
    button.hidden = true;
});

document.addEventListener('click', function (event) {
    var button = event.target.closest ? event.target.closest('.copy-btn') : null;

    if (!button) {
        return;
    }

    var text = button.getAttribute('data-copy');

    if (!text) {
        return;
    }

    copyText(text, function () {
        button.classList.add('is-copied');

        window.setTimeout(function () {
            button.classList.remove('is-copied');
        }, 1200);
    });
});

function copyText(text, done) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(done).catch(function () {
            copyTextWithTextarea(text, done);
        });

        return;
    }

    copyTextWithTextarea(text, done);
}

function copyTextWithTextarea(text, done) {
    var area = document.createElement('textarea');

    area.value = text;
    area.setAttribute('readonly', 'readonly');
    area.style.position = 'fixed';
    area.style.left = '-9999px';
    document.body.appendChild(area);
    area.select();

    try {
        document.execCommand('copy');
        done();
    } catch (error) {
        // The button stays as a copy icon when the browser blocks copying.
    }

    document.body.removeChild(area);
}

var iosHint = document.getElementById('ios-install-hint');
var isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
var isStandalone = window.navigator.standalone === true || window.matchMedia('(display-mode: standalone)').matches;

if (iosHint && isIos && !isStandalone) {
    iosHint.hidden = false;

    var installNote = document.getElementById('install-app-note');

    if (installNote) {
        installNote.hidden = true;
    }
}

// Toggle password visibility helper
window.togglePasswordVisibility = function (button) {
    if (!button) {
        return;
    }

    var group = button.closest ? button.closest('.input-group') : button.parentElement;
    if (!group) {
        return;
    }

    var input = group.querySelector('input[name="password"], input[type="password"], input[type="text"]');
    if (!input) {
        return;
    }

    var isPassword = input.getAttribute('type') === 'password' || input.type === 'password';
    var nextType = isPassword ? 'text' : 'password';

    input.type = nextType;
    input.setAttribute('type', nextType);

    var eye = button.querySelector('.eye-icon') || button.querySelector('#eye-icon');
    var eyeSlash = button.querySelector('.eye-slash-icon') || button.querySelector('#eye-slash-icon');

    if (eye) {
        eye.style.display = isPassword ? 'none' : 'block';
    }
    if (eyeSlash) {
        eyeSlash.style.display = isPassword ? 'block' : 'none';
    }

    button.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
};

// Delegated listener for password toggles (ignores buttons with inline onclick to prevent double toggling)
document.addEventListener('click', function (event) {
    var button = event.target.closest ? event.target.closest('.js-toggle-password') : null;

    if (!button) {
        return;
    }

    if (button.hasAttribute('onclick')) {
        return;
    }

    window.togglePasswordVisibility(button);
});

// Select-all synchronization for departments and countries on user forms
(function () {
    function updateChipClass(input) {
        var chip = input.closest ? input.closest('.dept-chip') : input.parentElement;
        if (chip && chip.classList) {
            if (input.checked) {
                chip.classList.add('is-checked');
            } else {
                chip.classList.remove('is-checked');
            }
        }
    }

    function initSelectAll(masterId, childSelector) {
        var master = document.getElementById(masterId);
        if (!master || master.dataset.syncBound === '1') return;
        master.dataset.syncBound = '1';

        var isSyncing = false;

        function getChildren() {
            return Array.from(document.querySelectorAll(childSelector));
        }

        function syncMaster() {
            var children = getChildren();
            if (children.length === 0) return;
            var allChecked = children.every(function (cb) { return cb.checked; });
            if (master.checked !== allChecked) {
                isSyncing = true;
                master.checked = allChecked;
                isSyncing = false;
            }
        }

        function setAllChildren(checked) {
            var children = getChildren();
            isSyncing = true;
            children.forEach(function (cb) {
                cb.checked = checked;
                updateChipClass(cb);
            });
            isSyncing = false;
        }

        master.addEventListener('change', function () {
            if (isSyncing) return;
            setAllChildren(master.checked);
        });

        document.addEventListener('change', function (e) {
            if (isSyncing) return;
            if (!e.target || !e.target.matches(childSelector)) return;
            updateChipClass(e.target);
            syncMaster();
        });

        // Initial setup on page load
        var children = getChildren();
        if (children.length > 0) {
            if (master.checked) {
                setAllChildren(true);
            } else {
                var allChecked = children.every(function (cb) { return cb.checked; });
                if (allChecked) {
                    master.checked = true;
                }
            }
            children.forEach(updateChipClass);
        }
    }

    function runInit() {
        initSelectAll('all_departments', 'input[name="department_ids[]"]');
        initSelectAll('all_countries', 'input[name="country_ids[]"]');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runInit);
    } else {
        runInit();
    }
})();

