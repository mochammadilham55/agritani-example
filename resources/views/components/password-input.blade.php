{{--
    Input password dengan tombol kecil "Lihat" untuk menampilkan/menyembunyikan isinya,
    supaya pengguna bisa memeriksa salah ketik. Perilaku tombol ada di public/js/tanimart.js (jQuery).
    Atribut lain (id, name, required, ...) diteruskan ke <input>.
    Pemakaian: <x-password-input id="password" name="password" required />
--}}
<div class="input-group">
    <input type="password" {{ $attributes->merge(['class' => 'form-control']) }}>
    <button type="button" class="btn btn-outline-secondary btn-sm" data-toggle-password
            aria-label="Tampilkan password" aria-pressed="false">Lihat</button>
</div>
