'use strict';

(function initCatalog() {
    const form = document.getElementById('catalog-filter');
    if (!form) return;

    const grid = document.getElementById('room-grid');
    const cards = grid ? Array.from(grid.querySelectorAll('.room-card')) : [];
    const category = document.getElementById('filter-category');
    const price = document.getElementById('filter-price');
    const capacity = document.getElementById('filter-capacity');
    const count = document.getElementById('results-count');
    const hint = document.getElementById('results-hint');
    const empty = document.getElementById('empty-state');
    const reset = document.getElementById('filter-reset');
    const emptyReset = document.getElementById('empty-reset');

    function apply() {
        const categoryValue = category ? category.value : '';
        const priceValue = price ? Number(price.value) : 0;
        const capacityValue = capacity ? Number(capacity.value) : 0;
        let visible = 0;

        cards.forEach((card) => {
            const cardCategory = card.dataset.category || '';
            const cardPrice = Number(card.dataset.price || 0);
            const cardCapacity = Number(card.dataset.capacity || 0);
            const ok = (!categoryValue || cardCategory === categoryValue)
                && (!priceValue || cardPrice <= priceValue)
                && (!capacityValue || cardCapacity >= capacityValue);
            card.hidden = !ok;
            if (ok) visible += 1;
        });

        if (count) count.textContent = String(visible);
        if (hint) {
            const filtered = Boolean(categoryValue || priceValue || capacityValue);
            hint.textContent = filtered
                ? 'Показаны номера, подходящие под условия'
                : 'Случайная подборка из 6';
        }
        if (empty) empty.hidden = visible !== 0;
    }

    function resetForm() {
        form.reset();
        apply();
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        apply();
    });

    if (reset) reset.addEventListener('click', resetForm);
    if (emptyReset) emptyReset.addEventListener('click', resetForm);
    if (category) category.addEventListener('change', apply);
    if (capacity) capacity.addEventListener('change', apply);

    const params = new URLSearchParams(window.location.search);
    const initialCategory = params.get('category');
    if (initialCategory && category) {
        category.value = initialCategory;
    }

    apply();
})();

(function initBooking() {
    const form = document.getElementById('booking-form');
    if (!form) return;

    const ROOMS = {
        standard: {
            title: 'Стандарт «Сити»',
            category: 'Стандарт',
            price: 4500,
            priceLabel: '4 500 ₽',
            capacity: 'до 2',
            area: '18 м²',
            image: 'assets/img/standard.jpg',
            amenities: ['Wi-Fi', 'Кондиционер', 'Телевизор', 'Холодильник'],
        },
        studio: {
            title: 'Студия «Лофт»',
            category: 'Студия',
            price: 6900,
            priceLabel: '6 900 ₽',
            capacity: 'до 2',
            area: '28 м²',
            image: 'assets/img/studio.jpg',
            amenities: ['Wi-Fi', 'Кухня', 'Диван', 'Телевизор'],
        },
        lux: {
            title: 'Люкс «Гранд»',
            category: 'Люкс',
            price: 12500,
            priceLabel: '12 500 ₽',
            capacity: 'до 2',
            area: '45 м²',
            image: 'assets/img/lux.jpg',
            amenities: ['Wi-Fi', 'Ванна', 'Сейф', 'Халат и тапочки'],
        },
    };

    const params = new URLSearchParams(window.location.search);
    const roomKey = params.get('room') || 'standard';
    const room = ROOMS[roomKey] || ROOMS.standard;

    const setText = (id, text) => {
        const node = document.getElementById(id);
        if (node) node.textContent = text;
    };

    const image = document.getElementById('room-image');
    if (image) {
        image.src = room.image;
        image.alt = room.title;
    }
    setText('breadcrumb-room', room.title);
    setText('room-category', room.category);
    setText('room-category-2', room.category);
    setText('room-title', room.title);
    setText('room-price', room.priceLabel);
    setText('room-capacity', room.capacity);
    setText('room-area', room.area);

    const amenities = document.getElementById('room-amenities');
    if (amenities) {
        amenities.innerHTML = '';
        room.amenities.forEach((item) => {
            const li = document.createElement('li');
            li.textContent = item;
            amenities.appendChild(li);
        });
    }

    const formAlert = document.getElementById('form-alert');
    const success = document.getElementById('success-card');
    const total = document.getElementById('booking-total');
    const nightsLabel = document.getElementById('nights-label');
    const totalValue = document.getElementById('total-value');
    const fields = ['first_name', 'last_name', 'phone', 'email', 'check_in', 'check_out'];

    function fieldBox(name) {
        return form.querySelector(`[data-field="${name}"]`);
    }

    function setError(name, message) {
        const box = fieldBox(name);
        if (!box) return;
        const error = box.querySelector('[data-error]');
        if (error) {
            error.textContent = message;
            error.hidden = false;
        }
        box.classList.add('has-error');
    }

    function clearErrors() {
        fields.forEach((name) => {
            const box = fieldBox(name);
            if (!box) return;
            box.classList.remove('has-error');
            const error = box.querySelector('[data-error]');
            if (error) {
                error.textContent = '';
                error.hidden = true;
            }
        });
        if (formAlert) formAlert.hidden = true;
    }

    function parseRuDate(value) {
        const match = /^(\d{2})\.(\d{2})\.(\d{4})$/.exec(String(value || '').trim());
        if (!match) return null;
        const day = Number(match[1]);
        const month = Number(match[2]);
        const year = Number(match[3]);
        const date = new Date(year, month - 1, day);
        if (date.getFullYear() !== year || date.getMonth() !== month - 1 || date.getDate() !== day) {
            return null;
        }
        return date;
    }

    function today() {
        const date = new Date();
        date.setHours(0, 0, 0, 0);
        return date;
    }

    function nightsBetween(checkIn, checkOut) {
        return Math.round((checkOut.getTime() - checkIn.getTime()) / 86400000);
    }

    function plural(count, forms) {
        const n = Math.abs(count) % 100;
        const n1 = n % 10;
        if (n > 10 && n < 20) return forms[2];
        if (n1 > 1 && n1 < 5) return forms[1];
        if (n1 === 1) return forms[0];
        return forms[2];
    }

    function updateTotal() {
        const checkIn = parseRuDate(document.getElementById('check_in')?.value);
        const checkOut = parseRuDate(document.getElementById('check_out')?.value);
        if (!checkIn || !checkOut || checkOut <= checkIn) {
            if (total) total.hidden = true;
            return;
        }
        const nights = nightsBetween(checkIn, checkOut);
        const sum = nights * room.price;
        if (nightsLabel) {
            nightsLabel.textContent = `${nights} ${plural(nights, ['ночь', 'ночи', 'ночей'])}`;
        }
        if (totalValue) {
            totalValue.textContent = `${new Intl.NumberFormat('ru-RU').format(sum)} ₽`;
        }
        if (total) total.hidden = false;
    }

    ['check_in', 'check_out'].forEach((name) => {
        const input = document.getElementById(name);
        if (input) input.addEventListener('input', updateTotal);
    });

    function validate() {
        clearErrors();
        const values = {
            first_name: document.getElementById('first_name')?.value.trim() || '',
            last_name: document.getElementById('last_name')?.value.trim() || '',
            phone: document.getElementById('phone')?.value.trim() || '',
            email: document.getElementById('email')?.value.trim() || '',
            check_in: document.getElementById('check_in')?.value.trim() || '',
            check_out: document.getElementById('check_out')?.value.trim() || '',
        };
        let errors = 0;

        const namePattern = /^[А-Яа-яЁё][А-Яа-яЁё\s.-]{1,63}$/u;
        [['first_name', 'Имя'], ['last_name', 'Фамилия']].forEach(([name, label]) => {
            if (!values[name]) {
                setError(name, `${label}: обязательное поле`);
                errors += 1;
            } else if (!namePattern.test(values[name])) {
                setError(name, `${label}: допустимы кириллица, пробел, точка и тире`);
                errors += 1;
            }
        });

        if (!values.phone) {
            setError('phone', 'Телефон: обязательное поле');
            errors += 1;
        } else if (!/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/.test(values.phone)) {
            setError('phone', 'Телефон: формат +7(ХХХ)ХХХ-ХХ-ХХ');
            errors += 1;
        }

        if (!values.email) {
            setError('email', 'E-mail: обязательное поле');
            errors += 1;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(values.email)) {
            setError('email', 'E-mail: укажите адрес в формате name@example.ru');
            errors += 1;
        }

        const checkIn = parseRuDate(values.check_in);
        const checkOut = parseRuDate(values.check_out);

        if (!values.check_in) {
            setError('check_in', 'Дата заезда: обязательное поле');
            errors += 1;
        } else if (!checkIn) {
            setError('check_in', 'Дата заезда: формат ДД.ММ.ГГГГ');
            errors += 1;
        } else if (checkIn < today()) {
            setError('check_in', 'Дата заезда не может быть в прошлом');
            errors += 1;
        }

        if (!values.check_out) {
            setError('check_out', 'Дата выезда: обязательное поле');
            errors += 1;
        } else if (!checkOut) {
            setError('check_out', 'Дата выезда: формат ДД.ММ.ГГГГ');
            errors += 1;
        } else if (checkIn && checkOut <= checkIn) {
            setError('check_out', 'Дата выезда должна быть позже даты заезда');
            errors += 1;
        }

        if (errors > 0 && formAlert) {
            formAlert.hidden = false;
            formAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return { errors, values };
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const result = validate();
        if (result.errors > 0) return;

        setText('success-name', result.values.first_name);
        setText('success-phone', result.values.phone);
        setText('success-room', room.title);
        setText('success-checkin', result.values.check_in);
        setText('success-checkout', result.values.check_out);

        form.hidden = true;
        if (total) total.hidden = true;
        if (success) {
            success.hidden = false;
            success.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    const demo = document.getElementById('demo-fill');
    if (demo) {
        demo.addEventListener('click', () => {
            const fill = (id, value) => {
                const input = document.getElementById(id);
                if (input) input.value = value;
            };
            const inDate = new Date();
            inDate.setDate(inDate.getDate() + 10);
            const outDate = new Date();
            outDate.setDate(outDate.getDate() + 12);
            const pad = (n) => String(n).padStart(2, '0');
            const fmt = (date) => `${pad(date.getDate())}.${pad(date.getMonth() + 1)}.${date.getFullYear()}`;

            fill('first_name', 'Иван');
            fill('last_name', 'Петров');
            fill('phone', '+7(913)555-12-34');
            fill('email', 'ivan@example.ru');
            fill('check_in', fmt(inDate));
            fill('check_out', fmt(outDate));
            updateTotal();
        });
    }
})();
