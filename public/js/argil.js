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
