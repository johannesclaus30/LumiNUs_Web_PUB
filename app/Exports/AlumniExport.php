<?php

namespace App\Exports;

use App\Models\Alumni;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Response;

class AlumniExport implements Responsable
{
    protected string $scope;
    protected ?int $alumniId;

    public function __construct(string $scope = 'all', ?int $alumniId = null)
    {
        $this->scope = $scope;
        $this->alumniId = $alumniId;
    }

    public function toResponse($request)
    {
        $filename = 'LumiNUs_Alumni_Export_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = [
            'Student ID',
            'Alumni Type',
            'First Name',
            'Middle Name',
            'Last Name',
            'Email',
            'Phone Number',
            'Program',
            'Year Graduated',
            'Date of Birth',
            'Sex',
            'Verification Status',
            'Account Status',
            'Restriction Reason',
            'Created At',
        ];

        return Response::stream(function () use ($columns) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM so Excel opens it correctly (fixes special characters)
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, $columns);

            $this->getQuery()->chunk(500, function ($alumni) use ($file) {
                foreach ($alumni as $a) {
                    fputcsv($file, [
                        $a->student_id_number ?? '',
                        strtoupper($a->alumni_type ?? ''),
                        $a->first_name ?? '',
                        $a->middle_name ?? '',
                        $a->last_name ?? '',
                        $a->email ?? '',
                        $a->phone_number ?? '',
                        $a->program ?? '',
                        optional($a->year_graduated)->format('Y-m-d') ?? '',
                        optional($a->date_of_birth)->format('Y-m-d') ?? '',
                        $a->sex ?? '',
                        ucfirst($a->verification_status ?? 'pending'),
                        ($a->account_status ?? 1) == 1 ? 'Active' : 'Restricted',
                        $a->restriction_reason ?? '',
                        optional($a->created_at)->format('Y-m-d H:i:s') ?? '',
                    ]);
                }
            });

            fclose($file);
        }, 200, $headers);
    }

    protected function getQuery()
    {
        $query = Alumni::query()
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($this->scope === 'filtered' && $this->alumniId) {
            $query->where('id', $this->alumniId);
        }

        return $query;
    }
}