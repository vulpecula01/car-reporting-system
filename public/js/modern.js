/* Mobile Planner — UI helpers (V2)
 * - Dark mode toggle (จำค่าไว้ใน localStorage)
 * - ไฮไลต์เมนูที่กำลังเปิดอยู่
 * - Toast แจ้งผล (window.notify)
 */
(function () {
    'use strict';

    // ---------- Theme ----------
    function getTheme() {
        try { return localStorage.getItem('theme') || 'light'; } catch (e) { return 'light'; }
    }
    function applyTheme(t) {
        document.documentElement.setAttribute('data-theme', t);
        if (document.body) document.body.classList.toggle('dark-mode', t === 'dark');
        document.querySelectorAll('[data-theme-icon]').forEach(function (el) {
            el.className = t === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        });
        document.dispatchEvent(new CustomEvent('themechange', { detail: t }));
    }
    window.toggleTheme = function () {
        var t = getTheme() === 'dark' ? 'light' : 'dark';
        try { localStorage.setItem('theme', t); } catch (e) {}
        applyTheme(t);
    };
    window.currentTheme = getTheme;

    document.addEventListener('DOMContentLoaded', function () {
        applyTheme(getTheme());

        document.querySelectorAll('[data-toggle-theme]').forEach(function (btn) {
            btn.addEventListener('click', function (e) { e.preventDefault(); window.toggleTheme(); });
        });

        // ---------- Active menu ----------
        var path = location.pathname.replace(/\/+$/, '') || '/';
        var best = null, bestLen = -1;
        document.querySelectorAll('.nav-sidebar a.nav-link[href]').forEach(function (a) {
            var href;
            try { href = new URL(a.href, location.origin).pathname.replace(/\/+$/, '') || '/'; } catch (e) { return; }
            if (href === '/' && path !== '/') return;
            var match = path === href || path.indexOf(href + '/') === 0 ||
                (a.dataset.match && new RegExp(a.dataset.match).test(path));
            if (match && href.length > bestLen) { best = a; bestLen = href.length; }
        });
        if (best) best.classList.add('active');

        // ---------- Bootstrap tooltips ----------
        if (window.jQuery && jQuery.fn.tooltip) {
            jQuery('[data-tip]').each(function () {
                jQuery(this).attr('title', this.getAttribute('data-tip')).tooltip({ container: 'body' });
            });
        }

        // ---------- Flash messages from server ----------
        var flash = document.getElementById('flash-data');
        if (flash) {
            var ok = flash.getAttribute('data-success');
            var err = flash.getAttribute('data-error');
            if (ok) window.notify(ok, 'success');
            if (err) window.notify(err, 'error');
        }
    });

    // ---------- FullCalendar ภาษาไทย ----------
    var TH_MONTHS = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
    var TH_MON = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    var TH_DAYS = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
    var TH_DY = ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'];
    window.fcThai = {
        buttonText: { today: 'วันนี้', month: 'เดือน', week: 'สัปดาห์', day: 'วัน', list: 'รายการ' },
        titleFormat: function (arg) {
            var s = arg.start || arg.date, e = arg.end;
            var sm = s.month, sy = s.year + 543;
            var monthView = e && s.day === 1 && e.month === s.month && e.day >= 28;
            if (e && !monthView && (e.day !== s.day || e.month !== s.month)) {
                // ช่วงสัปดาห์ (end เป็น exclusive แล้วในบาง view)
                if (e.month === s.month) return s.day + '–' + e.day + ' ' + TH_MON[sm] + ' ' + sy;
                return s.day + ' ' + TH_MON[sm] + ' – ' + e.day + ' ' + TH_MON[e.month] + ' ' + (e.year + 543);
            }
            return TH_MONTHS[sm] + ' ' + sy;
        },
        dayHeaderContent: function (arg) {
            var dt = arg.date;
            if (arg.view.type === 'dayGridMonth') return TH_DY[dt.getDay()];
            return TH_DY[dt.getDay()] + ' ' + dt.getDate() + '/' + (dt.getMonth() + 1);
        },
        listDayFormat: function (arg) { var x = arg.date; return 'วัน' + TH_DAYS[new Date(x.year, x.month, x.day).getDay()] + 'ที่ ' + x.day + ' ' + TH_MONTHS[x.month]; },
        listDaySideFormat: function (arg) { return String(arg.date.year + 543); },
        noEventsText: 'ไม่มีงานในช่วงนี้',
        moreLinkText: function (n) { return '+ อีก ' + n + ' งาน'; },
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        allDayText: 'ทั้งวัน',
        slotMinTime: '06:00:00',
        slotMaxTime: '21:00:00'
    };
    // ใช้: fcThaiApply(calendar) ก่อน calendar.render()
    window.fcThaiApply = function (cal) {
        Object.keys(window.fcThai).forEach(function (k) { cal.setOption(k, window.fcThai[k]); });
    };

    // ---------- Toast ----------
    window.notify = function (msg, icon) {
        if (window.Swal) {
            Swal.fire({
                toast: true, position: 'top-end', icon: icon || 'success', title: msg,
                showConfirmButton: false, timer: 3200, timerProgressBar: true
            });
        } else {
            alert(msg);
        }
    };
})();
