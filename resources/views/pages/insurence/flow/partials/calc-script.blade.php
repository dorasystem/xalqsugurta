{{--
    JS for the sum-dates partial + summary sidebar. Defines window.xfCalc:
      xfCalc.reveal(objectLabel) — call once the insured object is found:
        shows the sum section, enables #submit_btn, fills the summary.
    Expects: $flow (rate in %)
--}}
<script>
window.xfCalc = (function () {
    var RATE     = {{ $flow['rate'] / 100 }};
    var CURRENCY = @json(__t('messages.currency'));

    function $(id) { return document.getElementById(id); }
    function money(n) { return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ' + CURRENCY; }
    function dmy(d) { return ('0' + d.getDate()).slice(-2) + '.' + ('0' + (d.getMonth() + 1)).slice(-2) + '.' + d.getFullYear(); }
    function revealed() { return !$('calc_section').hidden; }

    function setSummary(key, text) {
        document.querySelectorAll('[data-summary="' + key + '"]').forEach(function (el) {
            el.textContent = text;
            el.classList.remove('is-pending');
        });
    }

    // ── Sum: chips + slider ────────────────────────────────────────────────────
    var slider = $('amt_slider');

    function setAmount(val) {
        val = parseInt(val, 10);
        slider.value = val;
        $('h_insurance_amount').value = val;
        $('amt_display').textContent = money(val);
        document.querySelectorAll('[data-amount]').forEach(function (chip) {
            chip.setAttribute('aria-pressed', parseInt(chip.dataset.amount, 10) === val ? 'true' : 'false');
        });

        if (!revealed()) return;

        var premium = $('sidebar_premium');
        premium.textContent = money(val * RATE);
        premium.classList.remove('is-empty');
        setSummary('sum', money(val));
        setSummary('total', money(val * RATE));
    }

    slider.addEventListener('input', function () { setAmount(this.value); });
    document.querySelectorAll('[data-amount]').forEach(function (chip) {
        chip.addEventListener('click', function () { setAmount(this.dataset.amount); });
    });

    // ── Start date → end date (1 year) ─────────────────────────────────────────
    function updateDates() {
        var start = $('start_date').value;
        if (!start) return;
        var s = new Date(start + 'T00:00:00');
        var e = new Date(s);
        e.setFullYear(e.getFullYear() + 1);
        e.setDate(e.getDate() - 1);
        $('end_date').value = e.getFullYear() + '-' + ('0' + (e.getMonth() + 1)).slice(-2) + '-' + ('0' + e.getDate()).slice(-2);
        if (revealed()) setSummary('period', dmy(s) + ' – ' + dmy(e));
    }
    $('start_date').addEventListener('change', updateDates);

    setAmount(slider.value);
    updateDates();

    return {
        money: money,
        setSummary: setSummary,
        reveal: function (objectLabel) {
            $('calc_section').hidden = false;
            $('submit_btn').disabled = false;
            setSummary('object', objectLabel);
            setAmount(slider.value);
            updateDates();
        },
    };
})();
</script>
