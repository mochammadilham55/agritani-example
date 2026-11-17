{{--
    Input password dengan tombol kecil "Lihat" untuk menampilkan/menyembunyikan isinya,
    supaya pengguna bisa memeriksa salah ketik. Atribut lain (id, name, required, ...) diteruskan ke <input>.
    Pemakaian: <x-password-input id="password" name="password" required />
--}}
<div class="relative">
    <input type="password" {{ $attributes->merge(['class' => 'w-full rounded-lg border border-line py-2 pl-3 pr-16 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20']) }}>
    <button type="button"
            aria-label="Tampilkan password" aria-pressed="false"
            onclick="const i = this.previousElementSibling, show = i.type === 'password'; i.type = show ? 'text' : 'password'; this.textContent = show ? 'Sembunyi' : 'Lihat'; this.setAttribute('aria-pressed', show); this.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');"
            class="absolute inset-y-0 right-0 rounded-r-lg px-3 text-xs font-semibold text-brand hover:text-brand-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/40">Lihat</button>
</div>
