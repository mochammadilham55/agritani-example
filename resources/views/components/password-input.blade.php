{{--
    Input password dengan tombol kecil "Lihat" untuk menampilkan/menyembunyikan isinya,
    supaya pengguna bisa memeriksa salah ketik. Atribut lain (id, name, required, ...) diteruskan ke <input>.
    Pemakaian: <x-password-input id="password" name="password" required />
--}}
<div class="input-group">
    <input type="password" {{ $attributes->merge(['class' => 'form-control']) }}>
    <button type="button" class="btn btn-outline-secondary btn-sm"
            aria-label="Tampilkan password" aria-pressed="false"
            onclick="const i = this.previousElementSibling, show = i.type === 'password'; i.type = show ? 'text' : 'password'; this.textContent = show ? 'Sembunyi' : 'Lihat'; this.setAttribute('aria-pressed', show); this.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');">Lihat</button>
</div>
