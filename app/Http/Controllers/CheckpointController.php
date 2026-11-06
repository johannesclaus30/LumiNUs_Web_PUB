<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AlumniCheckpoint;
use App\Models\Alumni;
use App\Models\AlumniProgram;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckpointController extends Controller
{
    // ═══════════════════════════════════════════════════════
    // 1. HARDCODED ALIGNMENT MATRICES
    // ═══════════════════════════════════════════════════════

    private function getProgramOccupationMatrix()
    {
        $D = 'DIRECT'; $R = 'RELATED'; $N = 'NOT_DIRECT';

        return [
            'BSA' => ['accountant'=>$D, 'audit'=>$D, 'finance'=>$D, 'bank'=>$R, 'sales'=>$N, 'software'=>$N, 'developer'=>$N, 'nurse'=>$N, 'teacher'=>$N],
            'BSMA' => ['accountant'=>$D, 'audit'=>$R, 'finance'=>$D, 'bank'=>$R, 'sales'=>$N, 'software'=>$N, 'developer'=>$N, 'nurse'=>$N, 'teacher'=>$N],
            'BSBA-FM' => ['accountant'=>$R, 'audit'=>$R, 'finance'=>$D, 'bank'=>$D, 'sales'=>$R, 'software'=>$N, 'developer'=>$N, 'nurse'=>$N, 'teacher'=>$N],
            'BSBA-MM' => ['market'=>$D, 'sales'=>$D, 'brand'=>$D, 'finance'=>$N, 'accountant'=>$N, 'software'=>$N, 'developer'=>$N, 'nurse'=>$N, 'teacher'=>$N],
            'BSTM' => ['tourism'=>$D, 'hotel'=>$D, 'event'=>$D, 'travel'=>$D, 'market'=>$R, 'sales'=>$R, 'accountant'=>$N, 'software'=>$N, 'nurse'=>$N, 'teacher'=>$N],
            'BSMT' => ['medical tech'=>$D, 'medtech'=>$D, 'laboratory'=>$D, 'health'=>$D, 'nurse'=>$N, 'software'=>$N, 'accountant'=>$N, 'teacher'=>$N],
            'BSN' => ['nurs'=>$D, 'clinical'=>$D, 'health'=>$D, 'medical'=>$D, 'teacher'=>$R, 'software'=>$N, 'accountant'=>$N],
            'BSPSY' => ['psychology'=>$D, 'hr'=>$D, 'human resource'=>$D, 'research'=>$D, 'teacher'=>$D, 'nurse'=>$R, 'software'=>$N, 'accountant'=>$N],
            'BSARCH' => ['architect'=>$D, 'cad'=>$D, 'bim'=>$D, 'engineer'=>$R, 'construction'=>$R, 'software'=>$N, 'accountant'=>$N, 'nurse'=>$N],
            'BSCS-ML' => ['software'=>$D, 'developer'=>$D, 'data'=>$D, 'ml'=>$D, 'cyber'=>$D, 'it'=>$D, 'engineer'=>$R, 'accountant'=>$N, 'nurse'=>$N],
            'BSIT-MWA' => ['software'=>$D, 'developer'=>$D, 'web'=>$D, 'mobile'=>$D, 'it'=>$D, 'cyber'=>$D, 'support'=>$D, 'president'=>$R, 'engineer'=>$R, 'accountant'=>$N, 'nurse'=>$N],
            'BSCE' => ['civil'=>$D, 'site'=>$D, 'structural'=>$D, 'engineer'=>$D, 'construction'=>$D, 'cad'=>$D, 'software'=>$N, 'accountant'=>$N, 'nurse'=>$N],
            'BSCPE' => ['computer engineer'=>$D, 'hardware'=>$D, 'it'=>$D, 'software'=>$R, 'developer'=>$R, 'network'=>$D, 'engineer'=>$R, 'accountant'=>$N, 'nurse'=>$N],
            'BMMA' => ['multimedia'=>$D, 'graphic'=>$D, 'design'=>$D, 'video'=>$D, 'animation'=>$D, 'market'=>$R, 'software'=>$N, 'accountant'=>$N, 'nurse'=>$N],
        ];
    }

    private function getShsCollegeMatrix()
    {
        $D = 'DIRECT'; $R = 'RELATED'; $N = 'NOT_DIRECT';
        return [
            'STEM' => ['BSA'=>$R, 'BSMA'=>$R, 'BSBA-FM'=>$R, 'BSBA-MM'=>$N, 'BSTM'=>$N, 'BSMT'=>$D, 'BSN'=>$D, 'BSPSY'=>$R, 'BSARCH'=>$D, 'BSCS-ML'=>$D, 'BSIT-MWA'=>$D, 'BSCE'=>$D, 'BSCPE'=>$D, 'BMMA'=>$R],
            'ABM'  => ['BSA'=>$D, 'BSMA'=>$D, 'BSBA-FM'=>$D, 'BSBA-MM'=>$D, 'BSTM'=>$D, 'BSMT'=>$N, 'BSN'=>$N, 'BSPSY'=>$R, 'BSARCH'=>$N, 'BSCS-ML'=>$N, 'BSIT-MWA'=>$R, 'BSCE'=>$N, 'BSCPE'=>$N, 'BMMA'=>$R],
            'HUMSS'=> ['BSA'=>$R, 'BSMA'=>$R, 'BSBA-FM'=>$N, 'BSBA-MM'=>$R, 'BSTM'=>$D, 'BSMT'=>$N, 'BSN'=>$R, 'BSPSY'=>$D, 'BSARCH'=>$N, 'BSCS-ML'=>$N, 'BSIT-MWA'=>$N, 'BSCE'=>$N, 'BSCPE'=>$N, 'BMMA'=>$D],
            'CT'   => ['BSA'=>$N, 'BSMA'=>$R, 'BSBA-FM'=>$N, 'BSBA-MM'=>$R, 'BSTM'=>$N, 'BSMT'=>$N, 'BSN'=>$N, 'BSPSY'=>$N, 'BSARCH'=>$N, 'BSCS-ML'=>$D, 'BSIT-MWA'=>$D, 'BSCE'=>$R, 'BSCPE'=>$D, 'BMMA'=>$R],
            'EASS' => ['BSA'=>$R, 'BSMA'=>$N, 'BSBA-FM'=>$N, 'BSBA-MM'=>$R, 'BSTM'=>$R, 'BSMT'=>$N, 'BSN'=>$R, 'BSPSY'=>$D, 'BSARCH'=>$N, 'BSCS-ML'=>$N, 'BSIT-MWA'=>$N, 'BSCE'=>$N, 'BSCPE'=>$N, 'BMMA'=>$D],
            'EA'   => ['BSA'=>$N, 'BSMA'=>$N, 'BSBA-FM'=>$N, 'BSBA-MM'=>$N, 'BSTM'=>$N, 'BSMT'=>$N, 'BSN'=>$N, 'BSPSY'=>$N, 'BSARCH'=>$D, 'BSCS-ML'=>$R, 'BSIT-MWA'=>$R, 'BSCE'=>$D, 'BSCPE'=>$D, 'BMMA'=>$R],
            'MA'   => ['BSA'=>$D, 'BSMA'=>$D, 'BSBA-FM'=>$D, 'BSBA-MM'=>$D, 'BSTM'=>$R, 'BSMT'=>$N, 'BSN'=>$N, 'BSPSY'=>$R, 'BSARCH'=>$N, 'BSCS-ML'=>$N, 'BSIT-MWA'=>$R, 'BSCE'=>$N, 'BSCPE'=>$N, 'BMMA'=>$N],
            'SAH'  => ['BSA'=>$N, 'BSMA'=>$N, 'BSBA-FM'=>$N, 'BSBA-MM'=>$N, 'BSTM'=>$N, 'BSMT'=>$D, 'BSN'=>$D, 'BSPSY'=>$D, 'BSARCH'=>$N, 'BSCS-ML'=>$R, 'BSIT-MWA'=>$R, 'BSCE'=>$N, 'BSCPE'=>$R, 'BMMA'=>$N],
        ];
    }

    // ═══════════════════════════════════════════════════════
    // 2. SAVE COLLEGE CHECKPOINT (Mobile App or Admin Preview)
    // ═══════════════════════════════════════════════════════

    public function submitCollegeCheckpoint(Request $request)
    {
        // Note: In production, replace with Auth::guard('alumni')->id()
        $alumniId = $request->input('alumni_id', 1); 
        
        $data = $request->all();
        
        // 1. Get the Program Code (e.g., 'BSIT-MWA') from the submitted form
        $progCode = $data['c01_program'] ?? 'UNKNOWN';
        
        // 2. Determine College -> Occupation Alignment
        $occupationAlignment = 'UNDETERMINED';
        $jobTitle = $data['c12_occupation'] ?? '';
        
        if (!empty($jobTitle) && $progCode !== 'UNKNOWN') {
            $matrix = $this->getProgramOccupationMatrix();
            $jobTitleLower = strtolower($jobTitle);

            if (isset($matrix[$progCode])) {
                foreach ($matrix[$progCode] as $keyword => $level) {
                    if (str_contains($jobTitleLower, $keyword)) {
                        $occupationAlignment = $level;
                        break;
                    }
                }
            }
        }

        // 3. Determine SHS -> College Alignment (If they answered SHS questions too)
        $shsAlignment = 'UNDETERMINED';
        if (!empty($data['shs_strand']) && !empty($progCode)) {
            $shsMatrix = $this->getShsCollegeMatrix();
            $shsCode = $data['shs_strand'];
            
            if (isset($shsMatrix[$shsCode][$progCode])) {
                $shsAlignment = $shsMatrix[$shsCode][$progCode];
            }
        }

        // 4. Save the Checkpoint
        AlumniCheckpoint::create([
            'alumni_id' => $alumniId,
            'checkpoint_type' => 'college',
            
            // Academic
            'college_program' => $progCode,
            'college_satisfaction' => $data['c02_satisfaction'] ?? null,
            'college_prepared' => $data['c03_prepared'] ?? null,
            'college_would_choose_again' => $data['c04_would_choose_again'] ?? null,
            
            // Employment
            'college_occupation' => $jobTitle,
            'college_industry' => $data['c10_industry'] ?? null,
            'college_perceived_relation' => $data['c17_perceived_relation'] ?? null,
            
            // SHS Reflections (if they filled them out)
            'shs_strand' => $data['shs_strand'] ?? null,
            'shs_perceived_relation' => $data['shs_perceived_relation'] ?? null,
            
            // AUTOMATED RESULTS
            'college_occupation_alignment' => $occupationAlignment,
            'shs_college_alignment' => $shsAlignment,
        ]);

        return response()->json([
            'message' => 'Checkpoint saved successfully!', 
            'job_alignment' => $occupationAlignment,
            'shs_alignment' => $shsAlignment
        ]);
    }
}