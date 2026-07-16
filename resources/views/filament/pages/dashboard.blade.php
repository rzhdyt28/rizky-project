<x-filament-panels::page>
    {{-- Halaman Dashboard kustom — bebas didesain di sini.
         Widget header sudah dirender otomatis oleh Filament di atas konten ini. --}}

    <div class="grid gap-4 md:grid-cols-3">
        <a href="{{ url('/admin/invitations') }}" class="rounded-xl border p-4 hover:bg-gray-50 dark:hover:bg-white/5">
            <div class="text-sm text-gray-500">Modul</div>
            <div class="text-lg font-semibold">Undangan Online</div>
            <p class="mt-1 text-sm text-gray-500">Kelola undangan, tema, RSVP pelanggan.</p>
        </a>
        <a href="{{ url('/admin/plans') }}" class="rounded-xl border p-4 hover:bg-gray-50 dark:hover:bg-white/5">
            <div class="text-sm text-gray-500">Komersial</div>
            <div class="text-lg font-semibold">Paket &amp; Pembayaran</div>
            <p class="mt-1 text-sm text-gray-500">Plans, kupon, transaksi Midtrans.</p>
        </a>
        <a href="{{ url('/pulse') }}" class="rounded-xl border p-4 hover:bg-gray-50 dark:hover:bg-white/5">
            <div class="text-sm text-gray-500">Kesehatan</div>
            <div class="text-lg font-semibold">Laravel Pulse</div>
            <p class="mt-1 text-sm text-gray-500">Request lambat, exception, resource.</p>
        </a>
    </div>
</x-filament-panels::page>
