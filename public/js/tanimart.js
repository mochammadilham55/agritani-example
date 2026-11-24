/*
 * Skrip jQuery Tanimart (dimuat setelah jquery.min.js di semua layout).
 *  1. Tombol "Lihat" password          → [data-toggle-password]
 *  2. Preview foto sebelum diunggah    → input[type=file][data-preview="#id-img"]
 *  3. Pencarian langsung pada tabel    → input[data-filter-table="#id-tabel"]
 *  4. Tombol cetak laporan             → [data-print]
 */
$(function () {
    // 1. Tampilkan / sembunyikan isi password.
    $(document).on('click', '[data-toggle-password]', function () {
        var $input = $(this).siblings('input');
        var show = $input.attr('type') === 'password';

        $input.attr('type', show ? 'text' : 'password');
        $(this)
            .text(show ? 'Sembunyi' : 'Lihat')
            .attr({
                'aria-pressed': show,
                'aria-label': show ? 'Sembunyikan password' : 'Tampilkan password'
            });
    });

    // 2. Preview gambar yang dipilih di input file.
    $('input[type=file][data-preview]').on('change', function () {
        var file = this.files && this.files[0];
        var $target = $($(this).data('preview'));

        if (!file || !/^image\//.test(file.type)) {
            $target.addClass('d-none');
            return;
        }

        var reader = new FileReader();
        reader.onload = function (e) {
            $target.attr('src', e.target.result).removeClass('d-none');
        };
        reader.readAsDataURL(file);
    });

    // 3. Saring baris tabel sambil mengetik (tanpa reload halaman).
    $('input[data-filter-table]').on('input', function () {
        var keyword = $(this).val().toLowerCase();

        $($(this).data('filter-table')).find('tbody tr').each(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(keyword) > -1);
        });
    });

    // 4. Cetak halaman (CSS @media print menyembunyikan menu dan filter).
    $(document).on('click', '[data-print]', function () {
        window.print();
    });
});

// Tampilkan isian rekening hanya saat mendaftar sebagai petani.
$(function () {
    var $bank = $('#bank-fields');
    if (!$bank.length) return;
    $('input[name=role]').on('change', function () {
        $bank.toggleClass('d-none', $('input[name=role]:checked').val() !== 'petani');
    });
});

// Tampilkan info rekening hanya saat metode transfer dipilih di checkout.
$(function () {
    var $info = $('#transfer-info');
    if (!$info.length) return;
    function sync() {
        $info.toggleClass('d-none', $('input[name=method]:checked').val() !== 'transfer');
    }
    $('input[name=method]').on('change', sync);
    sync();
});

// Tombol "Hubungi Admin via WhatsApp": sisipkan email yang diketik ke pesan otomatis.
$(function () {
    var $wa = $('#wa-admin');
    if (!$wa.length) return;
    var $email = $($wa.data('wa-email-input'));
    function sync() {
        var text = 'Halo Admin Tanimart, saya lupa password akun dengan email: ' + ($email.val() || '');
        $wa.attr('href', 'https://wa.me/' + $wa.data('wa-number') + '?text=' + encodeURIComponent(text));
    }
    $email.on('input', sync);
    sync();
});

// Tombol salin (mis. password sementara di Kelola Pengguna).
$(document).on('click', '[data-copy]', function () {
    var $btn = $(this);
    var text = $($btn.data('copy')).text().trim();
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function () {
            $btn.text('Tersalin');
        });
    }
});
