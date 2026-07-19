<?php

namespace App\Modules\Invitation\Http\Controllers;

use App\Core\Services\PlanLimitService;
use App\Modules\Invitation\Models\Guest;
use App\Modules\Invitation\Models\Invitation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

/**
 * Manajemen tamu milik PEMILIK undangan (dipakai dashboard Vue).
 * Link personal & pesan WA dirakit di frontend dari data ini.
 */
class GuestController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private PlanLimitService $limits) {}

    public function index(Invitation $invitation)
    {
        $this->authorize('view', $invitation);

        return $invitation->guests()->latest()->get();
    }

    public function store(Request $request, Invitation $invitation)
    {
        $this->authorize('update', $invitation);

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'note'  => ['nullable', 'string', 'max:160'],
        ]);

        return response()->json($invitation->guests()->create($data), 201);
    }

    public function destroy(Invitation $invitation, Guest $guest)
    {
        $this->authorize('update', $invitation);
        abort_unless($guest->invitation_id === $invitation->id, 404);

        $guest->delete();

        return response()->noContent();
    }

    /**
     * Import tamu dari Excel (.xlsx) / CSV — kolom: Nama, No. WA (baris 1 =
     * header, dilewati). Maks 2000 baris. Baris tanpa nama dilewati & dicatat
     * di "skipped". Berhenti kalau kuota max_guests paket sudah tercapai.
     */
    public function import(Request $request, Invitation $invitation)
    {
        $this->authorize('update', $invitation);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:5120']]);

        $path = $request->file('file')->getRealPath();
        $ext = strtolower($request->file('file')->getClientOriginalExtension());
        $reader = $ext === 'csv' || $ext === 'txt' ? new CsvReader() : new XlsxReader();
        $reader->open($path);

        $max = $this->limits->planFor($invitation->tenant)?->max_guests ?? 0;
        $existing = $invitation->guests()->count();

        $created = 0;
        $skipped = [];
        $rowNum = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rowNum++;
                if ($rowNum === 1) {
                    continue; // header
                }
                if ($rowNum > 2001) {
                    break 2; // maks 2000 baris data
                }

                $cells = $row->toArray();
                $name = trim((string) ($cells[0] ?? ''));
                $phone = trim((string) ($cells[1] ?? '')) ?: null;

                if ($name === '') {
                    $skipped[] = "Baris {$rowNum}: nama kosong";
                    continue;
                }
                if ($existing + $created >= $max) {
                    $skipped[] = "Baris {$rowNum}: kuota tamu paket sudah penuh";
                    continue;
                }

                $invitation->guests()->create(['name' => $name, 'phone' => $phone]);
                $created++;
            }
        }
        $reader->close();

        return response()->json([
            'created' => $created,
            'skipped' => $skipped,
        ]);
    }
}
