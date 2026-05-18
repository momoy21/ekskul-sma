/**
 * public/js/app.js
 * JavaScript global untuk Sistem Ekstrakurikuler SMA Global Indonesia
 *
 * Modul:
 * 1. Sidebar toggle (mobile)
 * 2. Live search dengan debounce
 * 3. Auto-dismiss Bootstrap Toast
 * 4. SweetAlert2- konfirmasi hapus / aksi destruktif
 * 5. Checkbox select-all + sticky action bar (bulk action)
 * 6. E-Signature Canvas
 * 7. Countdown timer pendaftaran
 * 8. Preview foto sebelum upload
 */

document.addEventListener('DOMContentLoaded', () => {

    // ── 0. Restore Sidebar Scroll Position ──────────────────────────────────
    // Restore scroll position dari sessionStorage setelah page load
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        const savedScrollPos = sessionStorage.getItem('sidebarScrollPos');
        if (savedScrollPos !== null) {
            // Gunakan requestAnimationFrame supaya scroll terjadi setelah render selesai
            requestAnimationFrame(() => {
                sidebar.scrollTop = parseInt(savedScrollPos, 10);
            });
            // Hapus dari sessionStorage supaya tidak dipakai lagi
            sessionStorage.removeItem('sidebarScrollPos');
        }
    }

    // ── 1. Sidebar Toggle (Mobile) ────────────────────────────────────────
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContent   = document.getElementById('mainContent');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });

        // Tutup sidebar saat klik di luar (mobile)
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 992 &&
                sidebar.classList.contains('show') &&
                ! sidebar.contains(e.target) &&
                ! sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });

        // Simpan scroll position sidebar sebelum navigate ke menu lain
        const navLinks = sidebar.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                // Hanya simpan jika link menuju halaman lain (ada href dan bukan #)
                if (link.href && !link.href.endsWith('#')) {
                    sessionStorage.setItem('sidebarScrollPos', sidebar.scrollTop.toString());
                }
            });
        });
    }

    // ── 2. Live Search dengan Debounce ───────────────────────────────────
    // Semua input dengan [data-search] akan trigger reload setelah 400ms berhenti mengetik
    const searchInputs = document.querySelectorAll('[data-live-search]');
    searchInputs.forEach(input => {
        let timer;
        input.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(() => loadFiltersAjax(), 400);
        });
    });

    // Filter select yang langsung reload saat berubah
    const filterSelects = document.querySelectorAll('[data-filter-select]');
    filterSelects.forEach(select => {
        select.addEventListener('change', () => loadFiltersAjax());
    });

    /**
     * Load data dengan AJAX tanpa page reload.
     * Kursor tetap di search input, data update realtime.
     */
    function loadFiltersAjax() {
        const params = new URLSearchParams();

        // Kumpulkan semua filter values (kecuali yang kosong)
        document.querySelectorAll('[data-live-search], [data-filter-select]').forEach(el => {
            const name = el.dataset.filterName || el.name;
            if (name && el.value !== '' && el.value !== null) {
                params.append(name, el.value);
            }
        });

        // SELALU reset ke halaman 1 saat filter atau search berubah
        // Jangan append 'page' parameter- biarkan Laravel default ke page 1

        // Fetch dengan AJAX
        const url = window.location.pathname + '?' + params.toString();

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            // Parse response HTML
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');

            // Update table content (cari tabel di response)
            const newTable = newDoc.querySelector('.table-responsive');
            const oldTable = document.querySelector('.table-responsive');

            if (newTable && oldTable) {
                oldTable.innerHTML = newTable.innerHTML;
            }

            // Update pagination footer
            const newFooter = newDoc.querySelector('.card-footer');
            const oldFooter = document.querySelector('.card-footer');

            if (newFooter && oldFooter) {
                oldFooter.innerHTML = newFooter.innerHTML;
            }

            // Update URL tanpa reload
            window.history.pushState({}, '', url);

            // Re-attach event listeners untuk checkbox jika ada
            updateCheckboxListeners();
        })
        .catch(err => {
            console.error('Filter AJAX error:', err);
            // Fallback ke page reload jika AJAX gagal
            window.location.href = url;
        });
    }

    /**
     * Re-attach event listeners ke checkbox setelah AJAX update
     */
    function updateCheckboxListeners() {
        const checkboxAll = document.getElementById('checkboxAll');
        const rowCheckboxes = document.querySelectorAll('[data-row-checkbox]');

        if (checkboxAll) {
            // Reset listener
            checkboxAll.removeEventListener('change', handleCheckboxAll);
            checkboxAll.addEventListener('change', handleCheckboxAll);
        }

        rowCheckboxes.forEach(cb => {
            cb.removeEventListener('change', updateStickyBar);
            cb.addEventListener('change', updateStickyBar);
        });

        // Attach pagination link handlers
        attachPaginationHandlers();
    }

    function handleCheckboxAll() {
        document.querySelectorAll('[data-row-checkbox]').forEach(cb => {
            cb.checked = document.getElementById('checkboxAll').checked;
        });
        updateStickyBar();
    }

    /**
     * Attach AJAX handlers ke pagination links
     */
    function attachPaginationHandlers() {
        const paginationLinks = document.querySelectorAll('.pagination a[href]');
        paginationLinks.forEach(link => {
            link.removeEventListener('click', handlePaginationClick);
            link.addEventListener('click', handlePaginationClick);
        });
    }

    function handlePaginationClick(e) {
        e.preventDefault();
        const url = this.getAttribute('href');

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');

            const newTable = newDoc.querySelector('.table-responsive');
            const oldTable = document.querySelector('.table-responsive');
            if (newTable && oldTable) {
                oldTable.innerHTML = newTable.innerHTML;
            }

            const newFooter = newDoc.querySelector('.card-footer');
            const oldFooter = document.querySelector('.card-footer');
            if (newFooter && oldFooter) {
                oldFooter.innerHTML = newFooter.innerHTML;
            }

            window.history.pushState({}, '', url);
            updateCheckboxListeners();
        })
        .catch(err => {
            console.error('Pagination AJAX error:', err);
            window.location.href = url;
        });
    }

    // ── 3. Auto-Dismiss Bootstrap Toast ──────────────────────────────────
    document.querySelectorAll('.toast').forEach(toastEl => {
        const bsToast = new bootstrap.Toast(toastEl, { delay: 3500 });
        bsToast.show();
    });

    // ── 4. SweetAlert2- Konfirmasi Aksi Destruktif ───────────────────────
    /**
     * Semua form/link dengan [data-confirm] akan menampilkan dialog konfirmasi
     * SweetAlert2 sebelum dieksekusi.
     *
     * Atribut yang didukung:
     * data-confirm           = teks pesan konfirmasi
     * data-confirm-title     = judul dialog (default: "Konfirmasi")
     * data-confirm-type      = "danger" | "warning" (default: "warning")
     * data-confirm-btn       = teks tombol konfirmasi (default: "Ya, Lanjutkan")
     */
    document.querySelectorAll('[data-confirm]').forEach(el => {
        const eventType = el.tagName === 'FORM' ? 'submit' : 'click';

        el.addEventListener(eventType, function (e) {
            e.preventDefault();

            const message   = this.dataset.confirm || 'Apakah kamu yakin?';
            const title     = this.dataset.confirmTitle || 'Konfirmasi';
            const type      = this.dataset.confirmType || 'warning';
            const btnText   = this.dataset.confirmBtn || 'Ya, Lanjutkan';

            Swal.fire({
                title,
                text: message,
                icon: type,
                showCancelButton: true,
                confirmButtonText: btnText,
                cancelButtonText: 'Batal',
                confirmButtonColor: type === 'danger' ? '#ef4444' : '#3b82f6',
                cancelButtonColor: '#94a3b8',
                reverseButtons: true,
            }).then(result => {
                if (result.isConfirmed) {
                    if (el.tagName === 'FORM') {
                        el.removeEventListener('submit', arguments.callee);
                        el.submit();
                    } else if (this.tagName === 'BUTTON' && this.form) {
                        this.form.submit();
                    } else if (this.href) {
                        window.location.href = this.href;
                    } else if (this.dataset.targetForm) {
                        document.getElementById(this.dataset.targetForm)?.submit();
                    }
                }
            });
        });
    });

    // ── 5. Checkbox Select-All + Sticky Action Bar ────────────────────────
    const checkboxAll  = document.getElementById('checkboxAll');
    const stickyBar    = document.getElementById('stickyActionBar');
    const selectedCount = document.getElementById('selectedCount');

    function updateStickyBar() {
        const checked = document.querySelectorAll('[data-row-checkbox]:checked');
        const count   = checked.length;

        if (stickyBar) {
            stickyBar.classList.toggle('show', count > 0);
        }

        if (selectedCount) {
            selectedCount.textContent = `${count} siswa dipilih`;
        }

        // Sync state "select all" checkbox
        if (checkboxAll) {
            const all = document.querySelectorAll('[data-row-checkbox]');
            checkboxAll.indeterminate = count > 0 && count < all.length;
            checkboxAll.checked = count > 0 && count === all.length;
        }
    }

    if (checkboxAll) {
        checkboxAll.addEventListener('change', handleCheckboxAll);
    }

    document.querySelectorAll('[data-row-checkbox]').forEach(cb => {
        cb.addEventListener('change', updateStickyBar);
    });

    // Tombol batal di sticky bar
    document.getElementById('btnBatalBulk')?.addEventListener('click', () => {
        document.querySelectorAll('[data-row-checkbox]').forEach(cb => {
            cb.checked = false;
        });
        if (checkboxAll) checkboxAll.checked = false;
        updateStickyBar();
    });

    // Kumpulkan siswa_ids yang dicentang lalu submit form bulk action
    document.getElementById('btnPindahkanSekarang')?.addEventListener('click', () => {
        const ids       = Array.from(document.querySelectorAll('[data-row-checkbox]:checked'))
            .map(cb => cb.value);
        const kelasTujuan = document.getElementById('kelasTujuanBulk')?.value;

        if (! kelasTujuan) {
            Swal.fire('Oops!', 'Pilih kelas tujuan terlebih dahulu.', 'warning');
            return;
        }

        const form = document.getElementById('formBulkPindah');
        if (! form) return;

        // Hapus hidden inputs lama
        form.querySelectorAll('[name="siswa_ids[]"]').forEach(el => el.remove());
        form.querySelectorAll('[name="kelas_tujuan_id"]').forEach(el => el.remove());

        // Tambah hidden inputs baru
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = 'siswa_ids[]';
            input.value = id;
            form.appendChild(input);
        });

        const kelasInput = document.createElement('input');
        kelasInput.type  = 'hidden';
        kelasInput.name  = 'kelas_tujuan_id';
        kelasInput.value = kelasTujuan;
        form.appendChild(kelasInput);

        // Konfirmasi dulu
        Swal.fire({
            title: 'Pindah Kelas',
            text: `Apakah kamu yakin ingin memindahkan ${ids.length} siswa ke kelas yang dipilih?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Pindahkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3b82f6',
        }).then(result => {
            if (result.isConfirmed) form.submit();
        });
    });

    // ── 6. E-Signature Canvas ─────────────────────────────────────────────
    initSignatureCanvas();

    function initSignatureCanvas() {
        const canvas = document.getElementById('signature-canvas');
        if (! canvas) return;

        const ctx       = canvas.getContext('2d');
        const wrapper   = canvas.closest('.signature-wrapper');
        const hidden    = document.getElementById('tanda_tangan_ortu');
        const btnHapus  = document.getElementById('btnHapusTtd');

        let isDrawing  = false;
        let lastX      = 0;
        let lastY      = 0;
        let hasDrawn   = false;

        // Sesuaikan resolusi canvas dengan ukuran CSS (agar tidak blur)
        function resizeCanvas() {
            const rect = canvas.getBoundingClientRect();
            const ratio = window.devicePixelRatio || 1;
            canvas.width  = rect.width  * ratio;
            canvas.height = rect.height * ratio;
            ctx.scale(ratio, ratio);
            ctx.strokeStyle = '#1e293b';
            ctx.lineWidth   = 2;
            ctx.lineCap     = 'round';
            ctx.lineJoin    = 'round';
        }

        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            if (e.touches) {
                return {
                    x: e.touches[0].clientX - rect.left,
                    y: e.touches[0].clientY - rect.top,
                };
            }
            return { x: e.clientX - rect.left, y: e.clientY - rect.top };
        }

        function startDraw(e) {
            isDrawing = true;
            const { x, y } = getPos(e);
            lastX = x; lastY = y;
        }

        function draw(e) {
            if (! isDrawing) return;
            e.preventDefault();

            const { x, y } = getPos(e);
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(x, y);
            ctx.stroke();

            lastX = x; lastY = y;
            hasDrawn = true;

            if (wrapper) wrapper.classList.add('has-signature');

            // Simpan ke hidden input sebagai base64
            if (hidden) hidden.value = canvas.toDataURL('image/png');
        }

        function stopDraw() { isDrawing = false; }

        canvas.addEventListener('mousedown',  startDraw);
        canvas.addEventListener('mousemove',  draw);
        canvas.addEventListener('mouseup',    stopDraw);
        canvas.addEventListener('mouseleave', stopDraw);
        canvas.addEventListener('touchstart', startDraw, { passive: true });
        canvas.addEventListener('touchmove',  draw,      { passive: false });
        canvas.addEventListener('touchend',   stopDraw);

        // Tombol hapus tanda tangan
        btnHapus?.addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            if (hidden) hidden.value = '';
            if (wrapper) wrapper.classList.remove('has-signature');
            hasDrawn = false;
        });
    }

    // ── 7. Countdown Timer Pendaftaran ────────────────────────────────────
    const countdownEl   = document.getElementById('countdownTimer');
    const targetDateStr = countdownEl?.dataset.targetDate;

    if (countdownEl && targetDateStr) {
        // Ganti spasi dengan 'T' supaya konsisten di semua browser
        // "2025-07-03 11:00:00" → "2025-07-03T11:00:00"
        const targetDate = new Date(targetDateStr.replace(' ', 'T')).getTime();

        function updateCountdown() {
            const now  = Date.now();
            const diff = targetDate - now;

            if (diff <= 0) {
                countdownEl.textContent = 'Sudah ditutup';
                clearInterval(countdownInterval);
                return;
            }

            const d = Math.floor(diff / (1000 * 60 * 60 * 24));
            const h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const s = Math.floor((diff % (1000 * 60)) / 1000);

            const parts = [];
            if (d > 0) parts.push(`${d} hari`);
            if (h > 0) parts.push(`${h} jam`);
            if (m > 0) parts.push(`${m} menit`);
            parts.push(`${s} detik`);

            countdownEl.textContent = parts.join(' ');
        }

        updateCountdown();
        const countdownInterval = setInterval(updateCountdown, 1000);
    }

    // ── 8. Preview Foto Sebelum Upload ───────────────────────────────────
    const fotoInput   = document.getElementById('foto-input');
    const fotoPreview = document.getElementById('foto-preview');

    fotoInput?.addEventListener('change', function () {
        const file = this.files[0];
        if (! file) return;

        // Validasi ukuran file (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            Swal.fire('Ukuran Terlalu Besar', 'Ukuran foto maksimal 5MB.', 'error');
            this.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = e => {
            if (fotoPreview) {
                fotoPreview.src = e.target.result;
                fotoPreview.classList.remove('d-none');
            }
        };
        reader.readAsDataURL(file);
    });

});
