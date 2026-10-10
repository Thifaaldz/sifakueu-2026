<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LetterDocumentController extends Controller
{
    public function download(Surat $surat): StreamedResponse|Response
    {
        Gate::authorize('view', $surat);

        abort_if(blank($surat->generated_file_path), 404, 'Dokumen surat belum tersedia.');
        abort_unless(Storage::disk('local')->exists($surat->generated_file_path), 404, 'File surat tidak ditemukan.');

        $filename = str($surat->number ?: $surat->request_number ?: 'surat')->replace('/', '-')->slug('-')->append('.html')->value();

        return Storage::disk('local')->download($surat->generated_file_path, $filename);
    }
}
