/*
 * Skrip jQuery AgriTani (dimuat setelah jquery.min.js di semua layout).
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
