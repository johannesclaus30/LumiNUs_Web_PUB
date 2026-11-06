<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Preview: College Checkpoint</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f6; padding: 2rem; }
        .card { background: white; max-width: 800px; margin: 0 auto; padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151; }
        select { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; background: #fff; }
        .btn { background: #3b82f6; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer; width: 100%; }
        .btn:hover { background: #2563eb; }
    </style>
</head>
<body>

<div class="card">
    <h1 style="color: #111827; margin-bottom: 0.5rem;">College Checkpoint</h1>
    <p style="color: #6b7280; margin-bottom: 2rem;">Preview this form to test the automated alignment engine.</p>

    <form action="{{ route('checkpoints.college.submit') }}" method="POST">
        @csrf
        
        <!-- FOR TESTING ONLY: Hardcoded Alumni ID -->
        <input type="hidden" name="alumni_id" value="1">

        <div class="form-group">
            <label>C01: Which college program did you complete at NU Lipa?</label>
            <select name="c01_program" required>
                <option value="BSIT-MWA">BS Information Technology (BSIT-MWA)</option>
                <option value="BSN">BS Nursing (BSN)</option>
                <option value="BSA">BS Accountancy (BSA)</option>
                <option value="BSCE">BS Civil Engineering (BSCE)</option>
                <option value="BSBA-MM">BSBA Marketing Management (BSBA-MM)</option>
                <!-- Add the rest of the 14 programs here -->
            </select>
        </div>

        <div class="form-group">
            <label>C02: How satisfied are you with the college program you completed?</label>
            <select name="c02_satisfaction">
                <option value="1">1 - Very Dissatisfied</option>
                <option value="2">2 - Dissatisfied</option>
                <option value="3" selected>3 - Neutral</option>
                <option value="4">4 - Satisfied</option>
                <option value="5">5 - Very Satisfied</option>
            </select>
        </div>

        <div class="form-group">
            <label>C05: What is your current employment status?</label>
            <select name="c05_employment_status">
                <option value="Employed">Employed</option>
                <option value="Self-Employed">Self-Employed</option>
                <option value="Unemployed-Seeking">Unemployed and Seeking Work</option>
                <option value="Unemployed-NotSeeking">Unemployed and Not Seeking Work</option>
            </select>
        </div>

        <div class="form-group">
            <label>C10: What industry does your employer primarily operate in?</label>
            <select name="c10_industry">
                <option value="IND_01">Information Technology & Digital Services</option>
                <option value="IND_02">Healthcare & Medical Services</option>
                <option value="IND_03">Education & Academic Services</option>
                <!-- Add the 16 industries here -->
            </select>
        </div>

        <div class="form-group">
            <label>C12: What is your primary occupation?</label>
            <select name="c12_occupation" required>
                <option value="Software Developer">Software Developer</option>
                <option value="Registered Nurse">Registered Nurse</option>
                <option value="Accountant">Accountant</option>
                <option value="Sales Associate">Sales Associate</option>
                <option value="Civil Engineer">Civil Engineer</option>
                <!-- Add the 40 occupations here -->
            </select>
        </div>

        <div class="form-group">
            <label>C17: How related is your primary occupation to your degree? (Self-Perceived)</label>
            <select name="c17_perceived_relation">
                <option value="Directly Related">Directly Related</option>
                <option value="Somewhat Related">Somewhat Related</option>
                <option value="Not Related">Not Related</option>
                <option value="Unsure">Unsure</option>
            </select>
        </div>

        <hr style="border-top: 1px dashed #d1d5db; margin: 2rem 0;">
        <h3 style="color: #4b5563; margin-bottom: 1rem;">SHS Background (Optional)</h3>

        <div class="form-group">
            <label>S01: Which SHS strand did you complete?</label>
            <select name="shs_strand">
                <option value="">-- Skip if not applicable --</option>
                <option value="STEM">STEM</option>
                <option value="ABM">ABM</option>
                <option value="HUMSS">HUMSS</option>
                <option value="CT">Computing Technologies (CT)</option>
                <!-- Add the 8 strands here -->
            </select>
        </div>

        <button type="submit" class="btn">Submit Checkpoint</button>
    </form>
</div>

</body>
</html>