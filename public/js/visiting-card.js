// Read a visiting card in the browser, then ask the server which lead fields it found.

var readCardButton = document.getElementById('read-card-button');

if (readCardButton) {
    bindCardPreview('card_front', 'card-front-preview');
    bindCardPreview('card_back', 'card-back-preview');

    readCardButton.addEventListener('click', function () {
        var front = document.getElementById('card_front').files[0];
        var back = document.getElementById('card_back').files[0];

        if (!front || !back) {
            setCardStatus('Choose both the front and the back photo.');
            return;
        }

        readCardButton.disabled = true;
        setCardBusy(true);
        setCardStatus('Loading the card reader…');

        loadCardReader(function (reader) {
            if (!reader) {
                setCardBusy(false);
                readCardButton.disabled = false;
                setCardStatus('The card reader could not be loaded. Check your connection and try again.');
                return;
            }

            setCardStatus('Preparing the photos…');
            reader.createWorker('eng').then(function (worker) {
                return worker.setParameters({
                    tessedit_pageseg_mode: '4'
                }).then(function () {
                    return prepareCardImage(front);
                }).then(function (frontImage) {
                    setCardStatus('Reading the front…');

                    return worker.recognize(frontImage).then(function (frontResult) {
                        return prepareCardImage(back).then(function (backImage) {
                            setCardStatus('Reading the back…');

                            return worker.recognize(backImage).then(function (backResult) {
                                return worker.terminate().then(function () {
                                    return frontResult.data.text + '\n' + backResult.data.text;
                                });
                            });
                        });
                    });
                });
            }).then(function (text) {
                return sendCardText(text);
            }).then(function (payload) {
                fillCardFields(payload.fields || {});
                var labels = payload.labels || [];

                if (labels.length === 0) {
                    setCardStatus('No details could be read. Type them in, or use a clearer photo.');
                } else {
                    setCardStatus('Filled ' + labels.join(', ') + '. Check the fields, choose a department, then save the lead.');
                }
            }).catch(function () {
                setCardStatus('The card could not be read. Type the details, or try a clearer photo.');
            }).then(function () {
                setCardBusy(false);
                readCardButton.disabled = false;
            });
        });
    });
}

function prepareCardImage(file) {
    return new Promise(function (resolve) {
        var image = new Image();
        var url = URL.createObjectURL(file);

        image.onload = function () {
            var longSide = Math.max(image.width, image.height);
            var scale = 1;

            if (longSide < 1800) {
                scale = 1800 / longSide;
            }

            if (longSide * scale > 2200) {
                scale = 2200 / longSide;
            }

            var canvas = document.createElement('canvas');
            canvas.width = Math.round(image.width * scale);
            canvas.height = Math.round(image.height * scale);
            var context = canvas.getContext('2d');
            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, canvas.width, canvas.height);
            context.drawImage(image, 0, 0, canvas.width, canvas.height);

            var frame = context.getImageData(0, 0, canvas.width, canvas.height);
            var data = frame.data;
            var index = 0;

            for (index = 0; index < data.length; index += 4) {
                var gray = (data[index] * 0.3) + (data[index + 1] * 0.59) + (data[index + 2] * 0.11);
                gray = ((gray - 128) * 1.35) + 128;

                if (gray < 0) {
                    gray = 0;
                }

                if (gray > 255) {
                    gray = 255;
                }

                data[index] = gray;
                data[index + 1] = gray;
                data[index + 2] = gray;
            }

            context.putImageData(frame, 0, 0);
            URL.revokeObjectURL(url);
            canvas.toBlob(function (blob) {
                resolve(blob || file);
            }, 'image/png');
        };

        image.onerror = function () {
            URL.revokeObjectURL(url);
            resolve(file);
        };

        image.src = url;
    });
}

function bindCardPreview(inputId, previewId) {
    var input = document.getElementById(inputId);
    var preview = document.getElementById(previewId);

    if (!input || !preview) {
        return;
    }

    input.addEventListener('change', function () {
        var file = input.files[0];

        if (!file) {
            preview.classList.add('d-none');
            preview.removeAttribute('src');
            return;
        }

        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
    });
}

function loadCardReader(done) {
    if (window.Tesseract) {
        done(window.Tesseract);
        return;
    }

    var script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/tesseract.js@5.1.1/dist/tesseract.min.js';
    script.onload = function () {
        done(window.Tesseract || null);
    };
    script.onerror = function () {
        done(null);
    };
    document.head.appendChild(script);
}

function sendCardText(text) {
    var tokenInput = document.querySelector('input[name="_token"]');
    var token = tokenInput ? tokenInput.value : '';

    return fetch(readCardButton.getAttribute('data-url'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ text: text })
    }).then(function (response) {
        if (!response.ok) {
            throw new Error('read failed');
        }

        return response.json();
    });
}

function fillCardFields(fields) {
    var keys = [
        'full_name',
        'phone_country_code',
        'phone_number',
        'email',
        'website',
        'company_name',
        'job_title',
        'work_department',
        'address',
        'city',
        'country_id',
        'notes'
    ];

    for (var i = 0; i < keys.length; i++) {
        var key = keys[i];

        if (!fields[key]) {
            continue;
        }

        var field = document.getElementById(key);
        var value = String(fields[key]);

        if (!field) {
            continue;
        }

        if (field.tagName === 'SELECT' && !selectHasValue(field, value)) {
            continue;
        }

        field.value = value;
        field.classList.add('card-filled');
    }
}

function selectHasValue(field, value) {
    for (var i = 0; i < field.options.length; i++) {
        if (field.options[i].value === value) {
            return true;
        }
    }

    return false;
}

function setCardStatus(message) {
    var status = document.getElementById('card-read-status');

    if (status) {
        status.textContent = message;
    }
}

function setCardBusy(isBusy) {
    var spinner = document.getElementById('card-read-spinner');

    if (!spinner) {
        return;
    }

    if (isBusy) {
        spinner.classList.remove('d-none');
    } else {
        spinner.classList.add('d-none');
    }
}
