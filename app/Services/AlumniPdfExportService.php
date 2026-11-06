<?php

namespace App\Services;

use App\Models\Alumni;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class AlumniPdfExportService
{
    public function __construct(
        protected string $scope = 'all',
        protected ?array $ids = null,
        protected ?string $filterLabel = null,
    ) {}

    public function download()
    {
        $pdf = $this->build();
        $filename = 'LumiNUs_Alumni_Directory_' . now()->format('Y-m-d_His') . '.pdf';

        return $pdf->download($filename);
    }

    public function stream()
    {
        return $this->build()->stream(
            'LumiNUs_Alumni_Directory_' . now()->format('Y-m-d') . '.pdf'
        );
    }

    protected function build()
    {
        $alumni = $this->getQuery()->get();

        // Group by program for the summary page
        $byProgram = $alumni->groupBy(fn ($a) => $a->program ?: 'Not Specified')
            ->map->count()
            ->sortDesc();

        $byType = [
            'College' => $alumni->where('alumni_type', 'college')->count(),
            'SHS'     => $alumni->where('alumni_type', 'shs')->count(),
            'Unknown' => $alumni->whereNotIn('alumni_type', ['college', 'shs'])->count(),
        ];

        $byYear = $alumni->filter(fn ($a) => $a->year_graduated)
            ->groupBy(fn ($a) => $a->year_graduated->format('Y'))
            ->map->count()
            ->sortKeysDesc();

        $logoLuminus = $this->encodeLogo(public_path('assets/logos/LumiNUs_Logo_Landscape_Blue.png'));
        $logoNu       = $this->encodeLogo(public_path('assets/logos/NU_Logo.png'));
        $logoIcon     = $this->encodeLogo(public_path('assets/logos/LumiNUs_Icon.png'));

        return Pdf::loadView('exports.alumni-pdf', [
            'alumni'        => $alumni,
            'totalAlumni'   => $alumni->count(),
            'byProgram'     => $byProgram,
            'byType'        => $byType,
            'byYear'        => $byYear,
            'generatedAt'   => now(),
            'generatedBy'   => session('admin_name', 'System Administrator'),
            'filterLabel'   => $this->filterLabel,
            'logoLuminus'   => $logoLuminus,
            'logoNu'        => $logoNu,
            'logoIcon'      => $logoIcon,
            'stats' => [
                'withEmail'    => $alumni->whereNotNull('email_hash')->where('email_hash', '!=', '')->count(),
                'active'       => $alumni->where('account_status', 1)->count(),
                'restricted'   => $alumni->where('account_status', 0)->count(),
            ],
        ])
        ->setPaper('a4', 'portrait')
        ->setOption([
            'isRemoteEnabled'      => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont'          => 'DejaVu Sans',
            'dpi'                  => 150,
        ]);
    }

    protected function getQuery()
    {
        $query = Alumni::query()
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($this->scope === 'selected' && !empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        return $query;
    }

    protected function encodeLogo(?string $path): ?string
    {
        if (!$path || !file_exists($path)) return null;
        $mime = mime_content_type($path) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }
}