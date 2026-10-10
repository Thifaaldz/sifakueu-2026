<?php

namespace App\Http\Controllers;

use App\Models\LetterVerificationToken;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Response;

class LetterVerificationController extends Controller
{
    public function show(string $token): Response
    {
        abort_unless(app(TenantContext::class)->id(), 404);

        $verification = LetterVerificationToken::query()
            ->with(['generatedLetter.surat.jenisSurat'])
            ->where('public_token', $token)
            ->first();

        $valid = $verification
            && $verification->active
            && (! $verification->expires_at || $verification->expires_at->isFuture());

        $letter = $verification?->generatedLetter;
        $surat = $letter?->surat;

        $title = $valid ? 'Surat Valid' : 'Surat Tidak Valid';
        $status = $valid ? 'VALID' : 'TIDAK VALID';
        $statusClass = $valid ? '' : 'invalid';
        $number = e($surat?->number ?? '-');
        $type = e($surat?->jenisSurat?->name ?? '-');
        $generatedAt = e($letter?->generated_at?->translatedFormat('d F Y H:i') ?? '-');
        $checksum = e($letter?->checksum ?? '-');

        return response(<<<HTML
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$title}</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 0; background: #f8fafc; color: #0f172a; }
        main { max-width: 720px; margin: 48px auto; padding: 28px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; }
        .status { display: inline-flex; padding: 6px 10px; border-radius: 999px; font-weight: 700; background: #dcfce7; color: #166534; }
        .invalid { background: #fee2e2; color: #991b1b; }
        dl { display: grid; grid-template-columns: 180px 1fr; gap: 12px 20px; margin-top: 24px; }
        dt { color: #475569; }
        dd { margin: 0; font-weight: 600; overflow-wrap: anywhere; }
    </style>
</head>
    <body>
    <main>
        <span class="status {$statusClass}">{$status}</span>
        <h1>{$title}</h1>
        <dl>
            <dt>Nomor Surat</dt><dd>{$number}</dd>
            <dt>Jenis Surat</dt><dd>{$type}</dd>
            <dt>Tanggal Generate</dt><dd>{$generatedAt}</dd>
            <dt>Checksum</dt><dd>{$checksum}</dd>
        </dl>
    </main>
</body>
</html>
HTML);
    }
}
