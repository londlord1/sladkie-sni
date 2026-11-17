(() => {
    const form = document.querySelector('[data-booking-form]');
    if (!form) {
        return;
    }

    const phoneInput = form.querySelector('#phone');
    const checkIn = form.querySelector('#check_in');
    const checkOut = form.querySelector('#check_out');
    const calendar = document.querySelector('[data-calendar]');
    const totalBox = form.querySelector('[data-total]');
    const nightsLabel = form.querySelector('[data-nights-label]');
    const totalValue = form.querySelector('[data-total-value]');
    const clientError = form.querySelector('[data-client-error]');

    let blocked = [];
    try {
        blocked = JSON.parse(form.dataset.blocked || '[]');
    } catch (error) {
        blocked = [];
    }

    function formatPhone(raw) {
        let digits = raw.replace(/\D/g, '');

        if (digits.startsWith('8')) {
            digits = '7' + digits.slice(1);
        }
        if (digits.startsWith('7')) {
            digits = digits.slice(1);
        }

        digits = digits.slice(0, 10);

        if (digits === '') {
            return '';
        }

        let result = '+7(' + digits.slice(0, 3);

        if (digits.length >= 3) {
            result += ')';
        }
        if (digits.length > 3) {
            result += digits.slice(3, 6);
        }
        if (digits.length > 6) {
            result += '-' + digits.slice(6, 8);
        }
        if (digits.length > 8) {
            result += '-' + digits.slice(8, 10);
        }

        return result;
    }

    function formatDate(raw) {
        const digits = raw.replace(/\D/g, '').slice(0, 8);
        let result = digits.slice(0, 2);

        if (digits.length >= 3) {
            result += '.' + digits.slice(2, 4);
        }
        if (digits.length >= 5) {
            result += '.' + digits.slice(4, 8);
        }

        return result;
    }

    function isoToRu(iso) {
        const parts = iso.split('-');
        return parts[2] + '.' + parts[1] + '.' + parts[0];
    }

    function ruToIso(value) {
        const match = /^(\d{2})\.(\d{2})\.(\d{4})$/.exec(value);
        return match ? match[3] + '-' + match[2] + '-' + match[1] : null;
    }

    function isRealDate(iso) {
        if (!iso) {
            return false;
        }

        const parts = iso.split('-').map(Number);
        const date = new Date(Date.UTC(parts[0], parts[1] - 1, parts[2]));

        return date.getUTCFullYear() === parts[0]
            && date.getUTCMonth() === parts[1] - 1
            && date.getUTCDate() === parts[2];
    }

    function todayIso() {
        const now = new Date();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');

        return now.getFullYear() + '-' + month + '-' + day;
    }

    function overlapsBlocked(start, end) {
        return blocked.some((range) => start < range[1] && end > range[0]);
    }

    function isBlockedDate(iso) {
        return blocked.some((range) => iso >= range[0] && iso < range[1]);
    }

    function plural(number, one, few, many) {
        const mod100 = number % 100;
        const mod10 = number % 10;

        if (mod100 >= 11 && mod100 <= 14) {
            return many;
        }
        if (mod10 === 1) {
            return one;
        }
        if (mod10 >= 2 && mod10 <= 4) {
            return few;
        }

        return many;
    }

    function nightsBetween(start, end) {
        const startTime = Date.parse(start + 'T00:00:00Z');
        const endTime = Date.parse(end + 'T00:00:00Z');

        return Math.round((endTime - startTime) / 86400000);
    }

    function updateTotal() {
        if (!totalBox || !nightsLabel || !totalValue) {
            return;
        }

        const start = ruToIso(checkIn.value);
        const end = ruToIso(checkOut.value);

        if (!isRealDate(start) || !isRealDate(end) || end <= start) {
            totalBox.hidden = true;
            return;
        }

        const nights = nightsBetween(start, end);
        const price = Number(form.dataset.price || 0);
        const total = nights * price;

        nightsLabel.textContent = nights + ' ' + plural(nights, 'ночь', 'ночи', 'ночей');
        totalValue.textContent = new Intl.NumberFormat('ru-RU').format(total) + ' ₽';
        totalBox.hidden = false;
    }

    function paintSelection() {
        if (!calendar) {
            return;
        }

        const start = ruToIso(checkIn.value);
        const end = ruToIso(checkOut.value);

        calendar.querySelectorAll('button[data-date]').forEach((button) => {
            const iso = button.dataset.date;
            button.classList.toggle('is-selected', iso === start || iso === end);
            button.classList.toggle('in-range', Boolean(start && end && iso > start && iso < end));
        });
    }

    function showError(message) {
        if (!clientError) {
            return;
        }

        clientError.textContent = message;
        clientError.hidden = false;
    }

    function hideError() {
        if (clientError) {
            clientError.hidden = true;
        }
    }

    if (phoneInput) {
        phoneInput.addEventListener('input', () => {
            phoneInput.value = formatPhone(phoneInput.value);
        });

        phoneInput.addEventListener('blur', () => {
            if (phoneInput.value === '+7' || phoneInput.value === '+7(') {
                phoneInput.value = '';
            }
        });
    }

    [checkIn, checkOut].forEach((input) => {
        if (!input) {
            return;
        }

        input.addEventListener('input', () => {
            input.value = formatDate(input.value);
            hideError();
            updateTotal();
            paintSelection();
        });
    });

    if (calendar) {
        calendar.addEventListener('click', (event) => {
            const button = event.target.closest('button[data-date]');
            if (!button) {
                return;
            }

            const iso = button.dataset.date;

            if (isBlockedDate(iso)) {
                return;
            }

            const start = ruToIso(checkIn.value);

            if (checkIn.value === '' || (start && checkOut.value !== '')) {
                checkIn.value = isoToRu(iso);
                checkOut.value = '';
            } else if (!start || iso <= start) {
                checkIn.value = isoToRu(iso);
                checkOut.value = '';
            } else {
                checkOut.value = isoToRu(iso);
            }

            hideError();
            updateTotal();
            paintSelection();
        });
    }

    form.addEventListener('submit', (event) => {
        const start = ruToIso(checkIn.value);
        const end = ruToIso(checkOut.value);
        const today = todayIso();
        let message = '';

        if (!isRealDate(start)) {
            message = 'Укажите дату заезда в формате ДД.ММ.ГГГГ.';
        } else if (start < today) {
            message = 'Дата заезда не может быть раньше сегодняшней.';
        } else if (!isRealDate(end)) {
            message = 'Укажите дату выезда в формате ДД.ММ.ГГГГ.';
        } else if (end <= start) {
            message = 'Дата выезда должна быть позже даты заезда.';
        } else if (overlapsBlocked(start, end)) {
            message = 'Выбранный период пересекается с уже подтверждённой бронью. Выберите другие даты.';
        }

        if (message !== '') {
            event.preventDefault();
            showError(message);
            updateTotal();
        }
    });

    updateTotal();
    paintSelection();
})();
