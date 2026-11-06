<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NU Lipa – Alumni Tracer Admin Panel</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
  <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
  <script>
    tailwind.config = {
      theme: { extend: { colors: { nu: { navy: '#1f2b67', blue: '#32418c', gold: '#fbd117' } } } }
    };
  </script>
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body { margin: 0; font-family: 'Poppins', sans-serif; background: #f8fafc; }
    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: #f1f5f9; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
  </style>
</head>
<body>
<div id="root"></div>
<script type="text/babel">
const { useState } = React;

// ═══════════════════════════════════════════════════════
// DATA
// ═══════════════════════════════════════════════════════

const INITIAL_PHASES = [
  {
    id: 1, title: "Personal Profile", subtitle: "Basic & contact information",
    icon: "bi-person-fill", color: "#3b82f6",
    sections: [
      {
        id: "1-0", title: "Basic Information", description: "Personal details",
        questions: [
          { id: "1-0-1", label: "Full Name", type: "text", placeholder: "Juan dela Cruz", required: true },
          { id: "1-0-2", label: "Date of Birth", type: "text", placeholder: "MM/DD/YYYY", required: true },
          { id: "1-0-3", label: "Civil Status", type: "radio", options: ["Single","Married","Widowed","Separated"], required: true },
          { id: "1-0-4", label: "Gender", type: "radio", options: ["Male","Female","Prefer not to say"], required: true },
          { id: "1-0-5", label: "Region of Residence", type: "select", options: ["Region III – Central Luzon","Region IV-A – CALABARZON","NCR – Metro Manila","Others"], required: true },
        ]
      },
      {
        id: "1-1", title: "Contact Details", description: "How to reach you",
        questions: [
          { id: "1-1-1", label: "Mobile Number", type: "tel", placeholder: "09XX-XXX-XXXX", required: true },
          { id: "1-1-2", label: "Email Address", type: "email", placeholder: "juandelacruz@email.com", required: true },
          { id: "1-1-3", label: "Present Address", type: "textarea", placeholder: "Street, Barangay, City/Municipality", required: true },
          { id: "1-1-4", label: "Province / City", type: "text", placeholder: "e.g. Batangas / Lipa City", required: true },
        ]
      }
    ]
  },
  {
    id: 2, title: "Educational Background", subtitle: "Academic history at NU Lipa",
    icon: "bi-book-fill", color: "#10b981",
    sections: [
      {
        id: "2-0", title: "Academic History", description: "Your credentials",
        questions: [
          { id: "2-0-1", label: "College / Department", type: "select", options: ["College of Engineering","College of Business & Accountancy","College of Computing and IT","College of Arts and Sciences","Others"], required: true },
          { id: "2-0-2", label: "Degree Program", type: "text", placeholder: "e.g. BS Computer Science", required: true },
          { id: "2-0-3", label: "Year Graduated", type: "select", options: ["2024","2023","2022","2021","2020","2019","2018","2017","2016 or earlier"], required: true },
          { id: "2-0-4", label: "Academic Honors Received", type: "radio", options: ["Summa Cum Laude","Magna Cum Laude","Cum Laude","With Honors","None"], required: true },
          { id: "2-0-5", label: "Did you graduate on time?", type: "radio", options: ["Yes, on schedule","Extended by 1 semester","Extended by 1 year or more"], required: true },
        ]
      },
      {
        id: "2-1", title: "Further Studies", description: "Post-graduate education",
        questions: [
          { id: "2-1-1", label: "Are you pursuing graduate studies?", type: "radio", options: ["Yes, currently enrolled","Planning to enroll","Already finished","Not interested"], required: true },
          { id: "2-1-2", label: "Graduate Program (if applicable)", type: "text", placeholder: "e.g. Master in IT" },
          { id: "2-1-3", label: "Licensure Exams Passed", type: "checkbox", options: ["Board Exam (PRC)","Civil Service Exam","CPA Board Exam","Engineering Board Exam","None / Not Applicable"] },
        ]
      }
    ]
  },
  {
    id: 3, title: "Employment Profile", subtitle: "Career and work details",
    icon: "bi-briefcase-fill", color: "#f59e0b",
    sections: [
      {
        id: "3-0", title: "Current Employment", description: "Present work situation",
        questions: [
          { id: "3-0-1", label: "Employment Status", type: "radio", options: ["Employed (full-time)","Employed (part-time)","Self-employed / Freelance","Unemployed – seeking work","Continuing Education","OFW"], required: true },
          { id: "3-0-2", label: "Job Title / Position", type: "text", placeholder: "e.g. Software Developer" },
          { id: "3-0-3", label: "Company / Employer", type: "text", placeholder: "Company or organization name" },
          { id: "3-0-4", label: "Industry / Type of Work", type: "select", options: ["Information Technology","Business / Finance","Engineering","Healthcare","Education / Academe","Government","Tourism / Hospitality","Others"] },
          { id: "3-0-5", label: "Monthly Salary Range", type: "radio", options: ["Below ₱15,000","₱15,000 – ₱25,000","₱25,001 – ₱50,000","₱50,001 – ₱100,000","Above ₱100,000","Prefer not to disclose"] },
        ]
      },
      {
        id: "3-1", title: "First Job Details", description: "Journey to first employment",
        questions: [
          { id: "3-1-1", label: "How long to find your first job?", type: "radio", options: ["Before graduation","Within 1 month","1–6 months","6 months–1 year","More than 1 year","Still looking"], required: true },
          { id: "3-1-2", label: "How did you find your first job?", type: "radio", options: ["Online job portal","School placement / OJT","Referral from family or friends","Walk-in / direct application","Self-employed"], required: true },
          { id: "3-1-3", label: "Is your job related to your degree?", type: "radio", options: ["Yes, directly related","Somewhat related","Not related at all","Not yet employed"], required: true },
        ]
      }
    ]
  },
  {
    id: 4, title: "Professional Development", subtitle: "Skills, training & growth",
    icon: "bi-lightning-fill", color: "#8b5cf6",
    sections: [
      {
        id: "4-0", title: "Skills & Competencies", description: "What you learned and applied",
        questions: [
          { id: "4-0-1", label: "Technical skills you use most", type: "checkbox", options: ["Programming / Coding","Data Analysis","Design / Drawing","Engineering Calculation","Accounting / Bookkeeping","Project Management","Research / Writing"] },
          { id: "4-0-2", label: "Rate: Critical Thinking skills from NU Lipa", type: "scale", required: true },
          { id: "4-0-3", label: "Rate: Communication skills from NU Lipa", type: "scale", required: true },
          { id: "4-0-4", label: "Rate: Problem Solving skills from NU Lipa", type: "scale", required: true },
        ]
      },
      {
        id: "4-1", title: "Trainings & Certifications", description: "Professional development",
        questions: [
          { id: "4-1-1", label: "Attended professional trainings / seminars?", type: "radio", options: ["Yes, many times","Yes, once or twice","Not yet, but planning to","No"], required: true },
          { id: "4-1-2", label: "Types of development activities", type: "checkbox", options: ["Technical / skills training","Leadership / management","Industry certification","Online courses","Government-sponsored (TESDA)","None"] },
          { id: "4-1-3", label: "Remarks about your professional growth", type: "textarea", placeholder: "Share your career development journey..." },
        ]
      }
    ]
  },
  {
    id: 5, title: "Program Assessment", subtitle: "Evaluate your NU Lipa education",
    icon: "bi-clipboard-check-fill", color: "#ef4444",
    sections: [
      {
        id: "5-0", title: "Curriculum Evaluation", description: "Help us improve our programs",
        questions: [
          { id: "5-0-1", label: "Overall quality of your NU Lipa education", type: "scale", required: true },
          { id: "5-0-2", label: "Relevance of curriculum to your career", type: "scale", required: true },
          { id: "5-0-3", label: "Effectiveness of your instructors", type: "scale", required: true },
          { id: "5-0-4", label: "Adequacy of facilities and resources", type: "scale", required: true },
          { id: "5-0-5", label: "Quality of career guidance services", type: "scale", required: true },
        ]
      },
      {
        id: "5-1", title: "Suggestions & Recommendations", description: "Your feedback shapes our future",
        questions: [
          { id: "5-1-1", label: "Aspects of the curriculum to improve", type: "checkbox", options: ["More industry-relevant subjects","More practical/OJT exposure","Updated course materials","Better lab equipment","Stronger industry linkages","Better career counseling"] },
          { id: "5-1-2", label: "Would you recommend NU Lipa?", type: "radio", options: ["Definitely yes","Probably yes","Probably not","Definitely not"], required: true },
          { id: "5-1-3", label: "Other suggestions or comments", type: "textarea", placeholder: "Share your thoughts and recommendations..." },
        ]
      }
    ]
  }
];

const MOCK_RESPONSES = [
  { id: 1, name: "Maria Santos",    program: "BS IT",          year: 2023, completion: 100, date: "2025-06-14", status: "complete"    },
  { id: 2, name: "Jose Reyes",      program: "BS ME",          year: 2022, completion: 60,  date: "2025-06-13", status: "in-progress" },
  { id: 3, name: "Ana Cruz",        program: "BS Accountancy", year: 2023, completion: 40,  date: "2025-06-12", status: "in-progress" },
  { id: 4, name: "Carlo Mendoza",   program: "BS CS",          year: 2024, completion: 100, date: "2025-06-11", status: "complete"    },
  { id: 5, name: "Liza Bautista",   program: "BS Tourism",     year: 2022, completion: 20,  date: "2025-06-10", status: "in-progress" },
  { id: 6, name: "Ryan Villanueva", program: "BS CE",          year: 2021, completion: 100, date: "2025-06-09", status: "complete"    },
  { id: 7, name: "Grace Domingo",   program: "BS BA",          year: 2023, completion: 80,  date: "2025-06-08", status: "in-progress" },
  { id: 8, name: "Ken Flores",      program: "BS EE",          year: 2022, completion: 100, date: "2025-06-07", status: "complete"    },
];

const MOCK_ALUMNI = [
  { id: 1, name: "Maria Santos",    email: "maria.santos@email.com",    program: "BS Information Technology", year: 2023, completion: 100, status: "active"  },
  { id: 2, name: "Jose Reyes",      email: "jose.reyes@email.com",      program: "BS Mechanical Engineering",  year: 2022, completion: 60,  status: "active"  },
  { id: 3, name: "Ana Cruz",        email: "ana.cruz@email.com",        program: "BS Accountancy",             year: 2023, completion: 40,  status: "active"  },
  { id: 4, name: "Carlo Mendoza",   email: "carlo.mendoza@email.com",   program: "BS Computer Science",        year: 2024, completion: 100, status: "active"  },
  { id: 5, name: "Liza Bautista",   email: "liza.bautista@email.com",   program: "BS Tourism",                 year: 2022, completion: 20,  status: "pending" },
  { id: 6, name: "Ryan Villanueva", email: "ryan.v@email.com",          program: "BS Civil Engineering",       year: 2021, completion: 100, status: "active"  },
  { id: 7, name: "Grace Domingo",   email: "grace.domingo@email.com",   program: "BS Business Administration", year: 2023, completion: 80,  status: "active"  },
  { id: 8, name: "Ken Flores",      email: "ken.flores@email.com",      program: "BS Electrical Engineering",  year: 2022, completion: 100, status: "active"  },
];

const TYPE_LABELS = {
  text:"Short Text", email:"Email", tel:"Phone Number", textarea:"Paragraph",
  radio:"Multiple Choice", checkbox:"Checkboxes", select:"Dropdown", scale:"Rating (1–5)"
};
const TYPE_ICONS = {
  text:"bi-input-cursor-text", email:"bi-envelope", tel:"bi-telephone",
  textarea:"bi-text-paragraph", radio:"bi-ui-radios", checkbox:"bi-check2-square",
  select:"bi-chevron-expand", scale:"bi-star-half"
};
const PHASE_COMPLETION = [87, 72, 65, 48, 41];

// ═══════════════════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════════════════

function initials(name) { return name.split(" ").map(n => n[0]).join(""); }

function Toggle({ value, onChange }) {
  return (
    <button onClick={() => onChange(!value)}
      className="relative w-12 h-6 rounded-full transition-colors flex-none"
      style={{ backgroundColor: value ? "#1f2b67" : "#d1d5db" }}>
      <div className={"absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-all " + (value ? "left-6" : "left-0.5")} />
    </button>
  );
}

function ProgressBar({ value }) {
  const pct = Math.min(100, value);
  const color = pct === 100 ? "#10b981" : "#f59e0b";
  return (
    <div className="flex items-center gap-2">
      <div className="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
        <div className="h-full rounded-full" style={{ width: pct + "%", backgroundColor: color }} />
      </div>
      <span className="text-xs font-bold w-9 text-right" style={{ color }}>{pct}%</span>
    </div>
  );
}

function StatusBadge({ status }) {
  const map = { complete:"bg-green-100 text-green-700", "in-progress":"bg-amber-100 text-amber-700", active:"bg-blue-100 text-blue-700", pending:"bg-gray-100 text-gray-500" };
  const labels = { complete:"Complete", "in-progress":"In Progress", active:"Active", pending:"Pending" };
  return <span className={"text-xs font-semibold px-2.5 py-1 rounded-full " + (map[status] || "bg-gray-100 text-gray-500")}>{labels[status] || status}</span>;
}

// ═══════════════════════════════════════════════════════
// MODAL: QUESTION EDITOR
// ═══════════════════════════════════════════════════════

function QuestionEditorModal({ question, onSave, onClose }) {
  const [label,       setLabel]       = useState(question ? question.label                    : "");
  const [type,        setType]        = useState(question ? question.type                     : "text");
  const [options,     setOptions]     = useState(question && question.options ? question.options : []);
  const [placeholder, setPlaceholder] = useState(question && question.placeholder ? question.placeholder : "");
  const [required,    setRequired]    = useState(question ? !!question.required               : true);
  const [newOpt,      setNewOpt]      = useState("");

  const needsOptions = ["radio","checkbox","select"].includes(type);
  const needsPlaceholder = ["text","email","tel","textarea"].includes(type);
  const typeHelp = { radio:"Alumni pick exactly one answer.", checkbox:"Alumni can pick multiple answers.", select:"Alumni choose from a dropdown.", scale:"Alumni rate from 1 (Poor) to 5 (Excellent).", text:"Short single-line text input.", email:"Email address with validation.", tel:"Phone number input.", textarea:"Multi-line text for longer answers." };

  function addOption() { if (newOpt.trim()) { setOptions([...options, newOpt.trim()]); setNewOpt(""); } }
  function handleSave() {
    if (!label.trim()) return;
    onSave({ id: question ? question.id : "q-" + Date.now(), label: label.trim(), type, options: needsOptions ? options : undefined, placeholder: placeholder || undefined, required });
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4" style={{ backgroundColor: "rgba(0,0,0,0.55)" }} onClick={e => e.target === e.currentTarget && onClose()}>
      <div className="bg-white rounded-2xl w-full max-w-lg shadow-2xl" style={{ maxHeight: "90vh", overflowY: "auto" }}>
        <div className="px-6 py-4 border-b flex items-center justify-between sticky top-0 bg-white rounded-t-2xl z-10">
          <h3 className="font-bold text-lg" style={{ color: "#1f2b67" }}>{question ? "Edit Question" : "Add New Question"}</h3>
          <button onClick={onClose} className="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400"><i className="bi bi-x-lg"></i></button>
        </div>
        <div className="px-6 py-5 space-y-5">
          <div>
            <label className="block text-sm font-semibold text-gray-700 mb-1.5">Question Label <span className="text-red-400">*</span></label>
            <textarea value={label} onChange={e => setLabel(e.target.value)} rows={2} placeholder="Enter the question text..."
              className="w-full border-2 border-gray-200 rounded-xl px-3 py-2.5 text-sm resize-none outline-none focus:border-yellow-400" />
          </div>
          <div>
            <label className="block text-sm font-semibold text-gray-700 mb-1.5">Question Type</label>
            <select value={type} onChange={e => { setType(e.target.value); setOptions([]); }} className="w-full border-2 border-gray-200 rounded-xl px-3 py-2.5 text-sm outline-none">
              {Object.entries(TYPE_LABELS).map(([v, l]) => <option key={v} value={v}>{l}</option>)}
            </select>
            <p className="text-xs text-gray-400 mt-1.5 px-1">{typeHelp[type]}</p>
          </div>
          {needsOptions && (
            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-1.5">Answer Options <span className="text-gray-400 font-normal">({options.length})</span></label>
              {options.length === 0 && <p className="text-xs text-gray-400 italic mb-2">No options yet.</p>}
              <div className="space-y-1.5 mb-3">
                {options.map((opt, i) => (
                  <div key={i} className="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-xl px-3 py-2">
                    <i className="bi bi-grip-vertical text-gray-300"></i>
                    <span className="flex-1 text-sm text-gray-700">{opt}</span>
                    <button onClick={() => setOptions(options.filter((_, idx) => idx !== i))} className="text-red-400 hover:text-red-600"><i className="bi bi-x"></i></button>
                  </div>
                ))}
              </div>
              <div className="flex gap-2">
                <input value={newOpt} onChange={e => setNewOpt(e.target.value)} onKeyDown={e => { if (e.key === "Enter") { e.preventDefault(); addOption(); }}} placeholder="Type an option and press Enter..." className="flex-1 border-2 border-gray-200 rounded-xl px-3 py-2 text-sm outline-none" />
                <button onClick={addOption} className="px-4 py-2 text-white text-sm rounded-xl font-semibold" style={{ backgroundColor: "#1f2b67" }}>Add</button>
              </div>
            </div>
          )}
          {needsPlaceholder && (
            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-1.5">Placeholder Text</label>
              <input value={placeholder} onChange={e => setPlaceholder(e.target.value)} placeholder="e.g. Enter your answer here..." className="w-full border-2 border-gray-200 rounded-xl px-3 py-2.5 text-sm outline-none" />
            </div>
          )}
          <div className="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3">
            <div>
              <p className="text-sm font-semibold text-gray-800">Required Question</p>
              <p className="text-xs text-gray-400 mt-0.5">Alumni must answer before submitting</p>
            </div>
            <Toggle value={required} onChange={setRequired} />
          </div>
        </div>
        <div className="px-6 py-4 border-t flex gap-3 justify-end bg-gray-50 rounded-b-2xl">
          <button onClick={onClose} className="px-5 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-100 font-medium">Cancel</button>
          <button onClick={handleSave} disabled={!label.trim()} className="px-5 py-2.5 text-sm font-bold rounded-xl disabled:opacity-40" style={{ backgroundColor: "#fbd117", color: "#1f2b67" }}>Save Question</button>
        </div>
      </div>
    </div>
  );
}

function PhaseEditorModal({ phase, onSave, onClose }) {
  const ICONS = [{ key:"bi-person-fill",label:"Person" },{ key:"bi-book-fill",label:"Book" },{ key:"bi-briefcase-fill",label:"Briefcase" },{ key:"bi-lightning-fill",label:"Lightning" },{ key:"bi-clipboard-check-fill",label:"Clipboard" },{ key:"bi-mortarboard-fill",label:"Graduation" },{ key:"bi-graph-up",label:"Growth" },{ key:"bi-stars",label:"Stars" }];
  const COLORS = ["#3b82f6","#10b981","#f59e0b","#8b5cf6","#ef4444","#06b6d4","#1f2b67","#ec4899"];
  const [title, setTitle] = useState(phase ? phase.title : "");
  const [subtitle, setSubtitle] = useState(phase ? phase.subtitle : "");
  const [icon, setIcon] = useState(phase ? phase.icon : "bi-person-fill");
  const [color, setColor] = useState(phase ? phase.color : "#3b82f6");
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4" style={{ backgroundColor:"rgba(0,0,0,0.55)" }} onClick={e => e.target === e.currentTarget && onClose()}>
      <div className="bg-white rounded-2xl w-full max-w-md shadow-2xl">
        <div className="px-6 py-4 border-b flex items-center justify-between"><h3 className="font-bold text-lg" style={{ color:"#1f2b67" }}>{phase ? "Edit Phase" : "Add New Phase"}</h3><button onClick={onClose} className="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400"><i className="bi bi-x-lg"></i></button></div>
        <div className="px-6 py-5 space-y-4">
          <div><label className="block text-sm font-semibold text-gray-700 mb-1.5">Phase Title <span className="text-red-400">*</span></label><input value={title} onChange={e => setTitle(e.target.value)} placeholder="e.g. Personal Profile" className="w-full border-2 border-gray-200 rounded-xl px-3 py-2.5 text-sm outline-none" /></div>
          <div><label className="block text-sm font-semibold text-gray-700 mb-1.5">Subtitle</label><input value={subtitle} onChange={e => setSubtitle(e.target.value)} placeholder="e.g. Basic & contact info" className="w-full border-2 border-gray-200 rounded-xl px-3 py-2.5 text-sm outline-none" /></div>
          <div>
            <label className="block text-sm font-semibold text-gray-700 mb-2">Icon</label>
            <div className="grid grid-cols-4 gap-2">{ICONS.map(ic => (<button key={ic.key} onClick={() => setIcon(ic.key)} className={"flex flex-col items-center gap-1 p-2.5 rounded-xl border-2 " + (icon === ic.key ? "border-yellow-400 bg-yellow-50" : "border-gray-200 hover:border-gray-300")}><i className={ic.key + " text-lg"} style={{ color: icon === ic.key ? "#1f2b67" : "#6b7280" }}></i><span className="text-[10px] text-gray-500">{ic.label}</span></button>))}</div>
          </div>
          <div>
            <label className="block text-sm font-semibold text-gray-700 mb-2">Accent Color</label>
            <div className="flex gap-2 flex-wrap">{COLORS.map(c => (<button key={c} onClick={() => setColor(c)} className={"w-8 h-8 rounded-full " + (color === c ? "ring-2 ring-offset-2 ring-gray-500 scale-110" : "")} style={{ backgroundColor: c }} />))}</div>
          </div>
        </div>
        <div className="px-6 py-4 border-t flex gap-3 justify-end bg-gray-50 rounded-b-2xl">
          <button onClick={onClose} className="px-5 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-100 font-medium">Cancel</button>
          <button onClick={() => title.trim() && onSave({ title:title.trim(), subtitle, icon, color })} disabled={!title.trim()} className="px-5 py-2.5 text-sm font-bold rounded-xl disabled:opacity-40" style={{ backgroundColor:"#fbd117", color:"#1f2b67" }}>Save Phase</button>
        </div>
      </div>
    </div>
  );
}

function SectionEditorModal({ section, onSave, onClose }) {
  const [title, setTitle] = useState(section ? section.title : "");
  const [desc, setDesc] = useState(section ? section.description : "");
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4" style={{ backgroundColor:"rgba(0,0,0,0.55)" }} onClick={e => e.target === e.currentTarget && onClose()}>
      <div className="bg-white rounded-2xl w-full max-w-md shadow-2xl">
        <div className="px-6 py-4 border-b flex items-center justify-between"><h3 className="font-bold text-lg" style={{ color:"#1f2b67" }}>{section ? "Edit Section" : "Add New Section"}</h3><button onClick={onClose} className="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400"><i className="bi bi-x-lg"></i></button></div>
        <div className="px-6 py-5 space-y-4">
          <div><label className="block text-sm font-semibold text-gray-700 mb-1.5">Section Title <span className="text-red-400">*</span></label><input value={title} onChange={e => setTitle(e.target.value)} placeholder="e.g. Basic Information" className="w-full border-2 border-gray-200 rounded-xl px-3 py-2.5 text-sm outline-none" /></div>
          <div><label className="block text-sm font-semibold text-gray-700 mb-1.5">Description</label><input value={desc} onChange={e => setDesc(e.target.value)} placeholder="Brief description of this section" className="w-full border-2 border-gray-200 rounded-xl px-3 py-2.5 text-sm outline-none" /></div>
        </div>
        <div className="px-6 py-4 border-t flex gap-3 justify-end bg-gray-50 rounded-b-2xl">
          <button onClick={onClose} className="px-5 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-100 font-medium">Cancel</button>
          <button onClick={() => title.trim() && onSave({ title:title.trim(), description:desc })} disabled={!title.trim()} className="px-5 py-2.5 text-sm font-bold rounded-xl disabled:opacity-40" style={{ backgroundColor:"#fbd117", color:"#1f2b67" }}>Save Section</button>
        </div>
      </div>
    </div>
  );
}

// ═══════════════════════════════════════════════════════
// PAGES
// ═══════════════════════════════════════════════════════

function DashboardPage({ phases }) {
  const totalQ = phases.reduce((s, p) => s + p.sections.reduce((ss, sec) => ss + sec.questions.length, 0), 0);
  const totalSec = phases.reduce((s, p) => s + p.sections.length, 0);
  const empData = [
    { label:"Employed (Full-time)", value:124, pct:50, color:"#10b981" },
    { label:"Employed (Part-time)", value:28,  pct:11, color:"#3b82f6" },
    { label:"Self-employed",        value:18,  pct:7,  color:"#f59e0b" },
    { label:"Continuing Education", value:35,  pct:14, color:"#8b5cf6" },
    { label:"Unemployed",           value:20,  pct:8,  color:"#ef4444" },
    { label:"OFW",                  value:22,  pct:9,  color:"#06b6d4" },
  ];
  return (
    <div className="space-y-6">
      <div><h1 className="text-2xl font-bold text-gray-900">Dashboard</h1><p className="text-gray-500 text-sm mt-1">Overview of the Digital Alumni Tracer system</p></div>
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {[
          { label:"Total Alumni", value:"247", sub:"+12 this month", icon:"bi-people-fill", color:"#3b82f6" },
          { label:"Completed", value:"156", sub:"63.2% completion rate", icon:"bi-check-circle-fill", color:"#10b981" },
          { label:"In Progress", value:"91", sub:"36.8% still answering", icon:"bi-hourglass-split", color:"#f59e0b" },
          { label:"Total Questions", value:String(totalQ), sub:phases.length + " phases · " + totalSec + " sections", icon:"bi-question-circle-fill", color:"#8b5cf6" },
        ].map(stat => (
          <div key={stat.label} className="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div className="flex items-start justify-between">
              <div><p className="text-gray-500 text-sm font-medium">{stat.label}</p><p className="text-3xl font-bold text-gray-900 mt-1">{stat.value}</p><p className="text-xs text-gray-400 mt-1">{stat.sub}</p></div>
              <div className="w-10 h-10 rounded-xl flex items-center justify-center" style={{ backgroundColor: stat.color + "20" }}><i className={stat.icon + " text-lg"} style={{ color:stat.color }}></i></div>
            </div>
          </div>
        ))}
      </div>
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div className="lg:col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <h2 className="font-bold text-gray-900 mb-4">Phase Completion Rate</h2>
          <div className="flex items-end gap-3" style={{ height:"180px" }}>
            {phases.map((p, i) => {
              const pct = PHASE_COMPLETION[i] || 50;
              return (
                <div key={p.id} className="flex-1 flex flex-col items-center gap-1">
                  <span className="text-xs font-bold text-gray-700">{pct}%</span>
                  <div className="w-full relative rounded-t-lg overflow-hidden" style={{ height:"140px", backgroundColor:"#f1f5f9" }}>
                    <div className="absolute bottom-0 left-0 right-0 rounded-t-lg" style={{ height:pct+"%", backgroundColor:p.color }} />
                  </div>
                  <span className="text-[10px] text-gray-500 text-center">{p.title.split(" ")[0]}</span>
                </div>
              );
            })}
          </div>
        </div>
        <div className="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <h2 className="font-bold text-gray-900 mb-4">Employment Status</h2>
          <div className="space-y-2.5">
            {empData.map(item => (
              <div key={item.label}>
                <div className="flex items-center justify-between text-xs mb-1">
                  <div className="flex items-center gap-1.5"><div className="w-2 h-2 rounded-full flex-none" style={{ backgroundColor:item.color }} /><span className="text-gray-600">{item.label}</span></div>
                  <span className="font-bold text-gray-800">{item.value}</span>
                </div>
                <div className="h-1.5 bg-gray-100 rounded-full overflow-hidden"><div className="h-full rounded-full" style={{ width:item.pct+"%", backgroundColor:item.color }} /></div>
              </div>
            ))}
          </div>
        </div>
      </div>
      <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div className="px-5 py-4 border-b border-gray-100 flex items-center justify-between"><h2 className="font-bold text-gray-900">Recent Submissions</h2><button className="text-sm font-semibold hover:underline" style={{ color:"#1f2b67" }}>View All →</button></div>
        <div style={{ overflowX:"auto" }}>
          <table className="w-full">
            <thead><tr className="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider"><th className="px-5 py-3 text-left font-semibold">Alumni</th><th className="px-5 py-3 text-left font-semibold">Program</th><th className="px-5 py-3 text-left font-semibold">Year</th><th className="px-5 py-3 text-left font-semibold">Progress</th><th className="px-5 py-3 text-left font-semibold">Date</th><th className="px-5 py-3 text-left font-semibold">Status</th></tr></thead>
            <tbody>
              {MOCK_RESPONSES.slice(0,6).map((r,ri) => (
                <tr key={r.id} className={"hover:bg-gray-50 " + (ri < 5 ? "border-b border-gray-50" : "")}>
                  <td className="px-5 py-3"><div className="flex items-center gap-2.5"><div className="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-xs flex-none" style={{ backgroundColor:"#1f2b67" }}>{initials(r.name)}</div><span className="font-medium text-gray-900 text-sm">{r.name}</span></div></td>
                  <td className="px-5 py-3 text-sm text-gray-600">{r.program}</td>
                  <td className="px-5 py-3 text-sm text-gray-600">{r.year}</td>
                  <td className="px-5 py-3" style={{ minWidth:"140px" }}><ProgressBar value={r.completion} /></td>
                  <td className="px-5 py-3 text-sm text-gray-500">{r.date}</td>
                  <td className="px-5 py-3"><StatusBadge status={r.status} /></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

function TracerBuilderPage({ phases, setPhases }) {
  const [selectedId, setSelectedId] = useState(phases[0] ? phases[0].id : null);
  const [expanded, setExpanded] = useState(new Set());
  const [editQ, setEditQ] = useState(null);
  const [editPhase, setEditPhase] = useState(null);
  const [editSec, setEditSec] = useState(null);
  const selectedPhase = phases.find(p => p.id === selectedId) || null;
  function toggleExpand(id) { setExpanded(prev => { const n = new Set(prev); n.has(id) ? n.delete(id) : n.add(id); return n; }); }
  function addPhase(data) { const newId = Math.max(0, ...phases.map(p => p.id)) + 1; setPhases([...phases, { id:newId, ...data, sections:[] }]); setSelectedId(newId); }
  function updatePhase(id, data) { setPhases(phases.map(p => p.id === id ? { ...p, ...data } : p)); }
  function deletePhase(id) { if (!confirm("Delete this phase and all its content?")) return; setPhases(phases.filter(p => p.id !== id)); if (selectedId === id) setSelectedId(phases.find(p => p.id !== id) ? phases.find(p => p.id !== id).id : null); }
  function addSection(phaseId, data) { const sec = { id:phaseId+"-"+Date.now(), ...data, questions:[] }; setPhases(phases.map(p => p.id === phaseId ? { ...p, sections:[...p.sections, sec] } : p)); setExpanded(prev => new Set([...prev, sec.id])); }
  function updateSection(phaseId, secId, data) { setPhases(phases.map(p => p.id === phaseId ? { ...p, sections:p.sections.map(s => s.id === secId ? { ...s, ...data } : s) } : p)); }
  function deleteSection(phaseId, secId) { if (!confirm("Delete this section?")) return; setPhases(phases.map(p => p.id === phaseId ? { ...p, sections:p.sections.filter(s => s.id !== secId) } : p)); }
  function saveQuestion(phaseId, secId, q) { setPhases(phases.map(p => p.id === phaseId ? { ...p, sections:p.sections.map(s => s.id === secId ? { ...s, questions:s.questions.find(qq => qq.id === q.id) ? s.questions.map(qq => qq.id === q.id ? q : qq) : [...s.questions, q] } : s) } : p)); setEditQ(null); }
  function deleteQuestion(phaseId, secId, qId) { if (!confirm("Delete this question?")) return; setPhases(phases.map(p => p.id === phaseId ? { ...p, sections:p.sections.map(s => s.id === secId ? { ...s, questions:s.questions.filter(q => q.id !== qId) } : s) } : p)); }
  return (
    <div className="flex gap-5" style={{ minHeight:"calc(100vh - 140px)" }}>
      <div className="flex-none flex flex-col gap-3" style={{ width:"256px" }}>
        <div className="flex items-center justify-between">
          <h2 className="font-bold text-gray-900 text-lg">Phases</h2>
          <button onClick={() => setEditPhase("new")} className="flex items-center gap-1 text-sm font-bold px-3 py-1.5 rounded-xl" style={{ backgroundColor:"#fbd117", color:"#1f2b67" }}><i className="bi bi-plus-lg"></i> Add</button>
        </div>
        <div className="space-y-2 overflow-y-auto" style={{ maxHeight:"calc(100vh - 230px)" }}>
          {phases.map(phase => {
            const isSel = selectedId === phase.id;
            const totalQ = phase.sections.reduce((s, sec) => s + sec.questions.length, 0);
            return (
              <div key={phase.id} onClick={() => setSelectedId(phase.id)} className="rounded-2xl p-4 cursor-pointer border-2 transition-all" style={{ borderColor:isSel?"#fbd117":"#e5e7eb", backgroundColor:isSel?"#1f2b67":"white" }}>
                <div className="flex items-start justify-between gap-2">
                  <div className="flex items-center gap-2.5 min-w-0">
                    <div className="w-9 h-9 rounded-xl flex items-center justify-center flex-none" style={{ backgroundColor:isSel?"rgba(255,255,255,0.15)":phase.color+"20" }}><i className={phase.icon+" text-base"} style={{ color:isSel?"white":phase.color }}></i></div>
                    <div className="min-w-0"><p className="font-bold text-sm leading-tight truncate" style={{ color:isSel?"white":"#111827" }}>{phase.title}</p><p className="text-xs mt-0.5" style={{ color:isSel?"rgba(255,255,255,0.5)":"#9ca3af" }}>{phase.sections.length} sections · {totalQ} questions</p></div>
                  </div>
                  <div className="flex gap-0.5 flex-none">
                    <button onClick={e => { e.stopPropagation(); setEditPhase(phase); }} className="w-6 h-6 rounded-lg flex items-center justify-center" style={{ color:isSel?"rgba(255,255,255,0.7)":"#9ca3af" }}><i className="bi bi-pencil text-xs"></i></button>
                    <button onClick={e => { e.stopPropagation(); deletePhase(phase.id); }} className="w-6 h-6 rounded-lg flex items-center justify-center" style={{ color:isSel?"rgba(255,255,255,0.7)":"#f87171" }}><i className="bi bi-trash text-xs"></i></button>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      </div>
      {selectedPhase ? (
        <div className="flex-1 overflow-y-auto" style={{ maxHeight:"calc(100vh - 180px)" }}>
          <div className="flex items-center justify-between mb-4">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-2xl flex items-center justify-center" style={{ backgroundColor:selectedPhase.color+"20" }}><i className={selectedPhase.icon+" text-lg"} style={{ color:selectedPhase.color }}></i></div>
              <div><h2 className="font-bold text-gray-900 text-xl">{selectedPhase.title}</h2><p className="text-gray-400 text-sm">{selectedPhase.subtitle||"No subtitle"}</p></div>
            </div>
            <button onClick={() => setEditSec({ sec:null, phaseId:selectedPhase.id })} className="flex items-center gap-1.5 text-sm font-bold px-4 py-2 rounded-xl" style={{ backgroundColor:"#fbd117", color:"#1f2b67" }}><i className="bi bi-plus-lg"></i> Add Section</button>
          </div>
          {selectedPhase.sections.length === 0 ? (
            <div className="bg-white rounded-2xl border-2 border-dashed border-gray-200 p-12 text-center"><i className="bi bi-folder-plus text-5xl text-gray-300"></i><p className="text-gray-400 font-medium mt-3">No sections yet</p><p className="text-gray-300 text-sm mt-1">Click "Add Section" to get started.</p></div>
          ) : (
            <div className="space-y-3">
              {selectedPhase.sections.map((section, secIdx) => {
                const isOpen = expanded.has(section.id);
                return (
                  <div key={section.id} className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div className="flex items-center gap-3 px-5 py-4 cursor-pointer hover:bg-gray-50" onClick={() => toggleExpand(section.id)}>
                      <div className="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-sm flex-none" style={{ backgroundColor:selectedPhase.color }}>{secIdx+1}</div>
                      <div className="flex-1"><p className="font-bold text-gray-900">{section.title}</p><p className="text-xs text-gray-400 mt-0.5">{section.description && section.description+" · "}{section.questions.length} question{section.questions.length!==1?"s":""}</p></div>
                      <div className="flex items-center gap-1">
                        <button onClick={e => { e.stopPropagation(); setEditSec({ sec:section, phaseId:selectedPhase.id }); }} className="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-gray-100 text-gray-400"><i className="bi bi-pencil text-xs"></i></button>
                        <button onClick={e => { e.stopPropagation(); deleteSection(selectedPhase.id, section.id); }} className="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-red-50 text-red-400"><i className="bi bi-trash text-xs"></i></button>
                        <i className={"bi bi-chevron-"+(isOpen?"up":"down")+" text-gray-400 ml-1"}></i>
                      </div>
                    </div>
                    {isOpen && (
                      <div className="border-t border-gray-100 px-5 py-4">
                        {section.questions.length === 0 ? <p className="text-gray-400 text-sm text-center py-4">No questions yet.</p> : (
                          <div className="space-y-2 mb-3">
                            {section.questions.map((q, qi) => (
                              <div key={q.id} className="flex items-start gap-3 bg-gray-50 rounded-xl px-4 py-3 group">
                                <i className="bi bi-grip-vertical text-gray-300 mt-0.5 flex-none"></i>
                                <div className="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold text-white flex-none" style={{ backgroundColor:selectedPhase.color }}>{qi+1}</div>
                                <div className="flex-1 min-w-0">
                                  <p className="text-sm font-medium text-gray-800 leading-snug">{q.label}</p>
                                  <div className="flex items-center gap-1.5 mt-1 flex-wrap">
                                    <span className="flex items-center gap-1 text-[10px] font-semibold bg-white border border-gray-200 text-gray-500 px-2 py-0.5 rounded-full"><i className={TYPE_ICONS[q.type]+" text-[10px]"}></i>{TYPE_LABELS[q.type]}</span>
                                    {q.required && <span className="text-[10px] font-semibold text-red-500 bg-red-50 px-2 py-0.5 rounded-full">Required</span>}
                                    {q.options && <span className="text-[10px] text-gray-400">{q.options.length} options</span>}
                                  </div>
                                </div>
                                <div className="flex gap-1">
                                  <button onClick={() => setEditQ({ q, phaseId:selectedPhase.id, secId:section.id })} className="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-white hover:shadow text-gray-400"><i className="bi bi-pencil text-xs"></i></button>
                                  <button onClick={() => deleteQuestion(selectedPhase.id, section.id, q.id)} className="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-red-50 text-red-400"><i className="bi bi-trash text-xs"></i></button>
                                </div>
                              </div>
                            ))}
                          </div>
                        )}
                        <button onClick={() => setEditQ({ q:null, phaseId:selectedPhase.id, secId:section.id })} className="w-full flex items-center justify-center gap-2 py-2.5 border-2 border-dashed border-gray-200 rounded-xl text-sm font-semibold text-gray-400 hover:border-yellow-400 hover:text-blue-900 transition-colors"><i className="bi bi-plus-lg"></i> Add Question</button>
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          )}
        </div>
      ) : (
        <div className="flex-1 flex items-center justify-center"><div className="text-center"><i className="bi bi-layout-sidebar-reverse text-6xl text-gray-200"></i><p className="text-gray-400 mt-3">Select a phase to manage its content</p></div></div>
      )}
      {editQ && <QuestionEditorModal question={editQ.q} onSave={q => saveQuestion(editQ.phaseId, editQ.secId, q)} onClose={() => setEditQ(null)} />}
      {editPhase !== null && <PhaseEditorModal phase={editPhase==="new"?null:editPhase} onSave={data => { editPhase==="new" ? addPhase(data) : updatePhase(editPhase.id, data); setEditPhase(null); }} onClose={() => setEditPhase(null)} />}
      {editSec && <SectionEditorModal section={editSec.sec} onSave={data => { editSec.sec ? updateSection(editSec.phaseId, editSec.sec.id, data) : addSection(editSec.phaseId, data); setEditSec(null); }} onClose={() => setEditSec(null)} />}
    </div>
  );
}

function AnalyticsPage() {
  const monthly = [{ month:"Jan",count:12 },{ month:"Feb",count:18 },{ month:"Mar",count:25 },{ month:"Apr",count:31 },{ month:"May",count:28 },{ month:"Jun",count:42 }];
  const maxCount = Math.max(...monthly.map(m => m.count));
  return (
    <div className="space-y-6">
      <div><h1 className="text-2xl font-bold text-gray-900">Analytics</h1><p className="text-gray-500 text-sm mt-1">Aggregated insights from alumni responses</p></div>
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {[{ label:"Response Rate",value:"87.2%",icon:"bi-percent",color:"#10b981" },{ label:"Avg. Completion",value:"63%",icon:"bi-bar-chart-fill",color:"#3b82f6" },{ label:"Avg. Rating (Overall)",value:"4.1 / 5",icon:"bi-star-fill",color:"#f59e0b" },{ label:"Job Relevance",value:"68.4%",icon:"bi-briefcase-fill",color:"#8b5cf6" }].map(kpi => (
          <div key={kpi.label} className="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 text-center">
            <div className="w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-3" style={{ backgroundColor:kpi.color+"20" }}><i className={kpi.icon+" text-xl"} style={{ color:kpi.color }}></i></div>
            <p className="text-3xl font-bold" style={{ color:kpi.color }}>{kpi.value}</p>
            <p className="text-gray-500 text-sm mt-1">{kpi.label}</p>
          </div>
        ))}
      </div>
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div className="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <h2 className="font-bold text-gray-900 mb-4">Monthly Submissions</h2>
          <div className="flex items-end gap-2" style={{ height:"180px" }}>
            {monthly.map((m,i) => (
              <div key={i} className="flex-1 flex flex-col items-center gap-1">
                <span className="text-xs font-bold text-gray-700">{m.count}</span>
                <div className="w-full relative rounded-t-lg overflow-hidden" style={{ height:"150px", backgroundColor:"#f1f5f9" }}>
                  <div className="absolute bottom-0 left-0 right-0 rounded-t-lg" style={{ height:(m.count/maxCount*100)+"%", backgroundColor:"#1f2b67" }} />
                </div>
                <span className="text-[10px] text-gray-500">{m.month}</span>
              </div>
            ))}
          </div>
        </div>
        <div className="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <h2 className="font-bold text-gray-900 mb-5">Monthly Salary Distribution</h2>
          <div className="space-y-3">
            {[{ range:"Below ₱15k",pct:18 },{ range:"₱15–25k",pct:34 },{ range:"₱25–50k",pct:29 },{ range:"₱50–100k",pct:14 },{ range:"Above ₱100k",pct:5 }].map(item => (
              <div key={item.range}>
                <div className="flex items-center justify-between text-sm mb-1"><span className="text-gray-600">{item.range}</span><span className="font-bold text-gray-800">{item.pct}%</span></div>
                <div className="h-2.5 bg-gray-100 rounded-full overflow-hidden"><div className="h-full rounded-full" style={{ width:item.pct+"%", backgroundColor:"#fbd117" }} /></div>
              </div>
            ))}
          </div>
        </div>
        <div className="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <h2 className="font-bold text-gray-900 mb-5">Average Ratings by Criterion</h2>
          <div className="space-y-4">
            {[{ label:"Overall Education Quality",val:4.1 },{ label:"Curriculum Relevance",val:3.8 },{ label:"Teaching Effectiveness",val:4.3 },{ label:"Facilities & Resources",val:3.5 },{ label:"Career Guidance Services",val:3.2 }].map(item => (
              <div key={item.label}>
                <div className="flex items-center justify-between text-sm mb-1.5"><span className="text-gray-600">{item.label}</span><div className="flex items-center gap-1"><i className="bi bi-star-fill text-yellow-400 text-xs"></i><span className="font-bold text-gray-900">{item.val}</span><span className="text-gray-400 text-xs">/5</span></div></div>
                <div className="h-2 bg-gray-100 rounded-full overflow-hidden"><div className="h-full rounded-full" style={{ width:(item.val/5*100)+"%", backgroundColor:"#1f2b67" }} /></div>
              </div>
            ))}
          </div>
        </div>
        <div className="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <h2 className="font-bold text-gray-900 mb-5">Job Relevance to Degree</h2>
          <div className="space-y-3">
            {[{ label:"Directly related",pct:40,color:"#10b981" },{ label:"Somewhat related",pct:28,color:"#3b82f6" },{ label:"Not related",pct:22,color:"#f59e0b" },{ label:"Not yet employed",pct:10,color:"#ef4444" }].map(item => (
              <div key={item.label}>
                <div className="flex items-center justify-between text-sm mb-1"><div className="flex items-center gap-2"><div className="w-2.5 h-2.5 rounded-full flex-none" style={{ backgroundColor:item.color }} /><span className="text-gray-600">{item.label}</span></div><span className="font-bold" style={{ color:item.color }}>{item.pct}%</span></div>
                <div className="h-2 bg-gray-100 rounded-full overflow-hidden"><div className="h-full rounded-full" style={{ width:item.pct+"%", backgroundColor:item.color }} /></div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

function ResponsesPage() {
  const [search, setSearch] = useState(""); const [filterStatus, setFilterStatus] = useState("all");
  const filtered = MOCK_RESPONSES.filter(r => { const q = search.toLowerCase(); return (r.name.toLowerCase().includes(q)||r.program.toLowerCase().includes(q)) && (filterStatus==="all"||r.status===filterStatus); });
  return (
    <div className="space-y-5">
      <div className="flex items-start justify-between flex-wrap gap-3"><div><h1 className="text-2xl font-bold text-gray-900">Responses</h1><p className="text-gray-500 text-sm mt-1">All alumni tracer submissions</p></div><button className="flex items-center gap-2 text-white px-4 py-2.5 rounded-xl text-sm font-semibold" style={{ backgroundColor:"#1f2b67" }}><i className="bi bi-download"></i> Export CSV</button></div>
      <div className="flex gap-3 flex-wrap">
        <div className="relative flex-1" style={{ minWidth:"200px" }}><i className="bi bi-search absolute text-gray-400" style={{ left:"14px",top:"50%",transform:"translateY(-50%)" }}></i><input value={search} onChange={e => setSearch(e.target.value)} placeholder="Search by name or program..." className="w-full border border-gray-200 rounded-xl text-sm outline-none" style={{ paddingLeft:"40px",paddingRight:"16px",paddingTop:"10px",paddingBottom:"10px" }} /></div>
        <select value={filterStatus} onChange={e => setFilterStatus(e.target.value)} className="border border-gray-200 rounded-xl px-3 text-sm outline-none text-gray-600" style={{ paddingTop:"10px",paddingBottom:"10px" }}><option value="all">All Status</option><option value="complete">Complete</option><option value="in-progress">In Progress</option></select>
      </div>
      <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div style={{ overflowX:"auto" }}>
          <table className="w-full">
            <thead><tr className="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider border-b border-gray-100"><th className="px-5 py-3 text-left font-semibold">#</th><th className="px-5 py-3 text-left font-semibold">Alumni</th><th className="px-5 py-3 text-left font-semibold">Program</th><th className="px-5 py-3 text-left font-semibold">Year</th><th className="px-5 py-3 text-left font-semibold">Completion</th><th className="px-5 py-3 text-left font-semibold">Date</th><th className="px-5 py-3 text-left font-semibold">Status</th><th className="px-5 py-3 text-left font-semibold">Actions</th></tr></thead>
            <tbody>
              {filtered.map((r,ri) => (
                <tr key={r.id} className={"hover:bg-gray-50 "+(ri<filtered.length-1?"border-b border-gray-50":"")}>
                  <td className="px-5 py-3 text-xs text-gray-400">{r.id}</td>
                  <td className="px-5 py-3"><div className="flex items-center gap-2.5"><div className="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-xs flex-none" style={{ backgroundColor:"#1f2b67" }}>{initials(r.name)}</div><span className="font-semibold text-gray-900 text-sm">{r.name}</span></div></td>
                  <td className="px-5 py-3 text-sm text-gray-600">{r.program}</td><td className="px-5 py-3 text-sm text-gray-600">{r.year}</td>
                  <td className="px-5 py-3" style={{ minWidth:"140px" }}><ProgressBar value={r.completion} /></td>
                  <td className="px-5 py-3 text-sm text-gray-500">{r.date}</td><td className="px-5 py-3"><StatusBadge status={r.status} /></td>
                  <td className="px-5 py-3"><div className="flex gap-1"><button className="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-gray-100 text-gray-400"><i className="bi bi-eye text-sm"></i></button><button className="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-red-50 text-red-400"><i className="bi bi-trash text-sm"></i></button></div></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        {filtered.length===0 && <div className="py-12 text-center text-gray-400">No results found.</div>}
        <div className="px-5 py-3 border-t border-gray-100 text-sm text-gray-500">{filtered.length} result{filtered.length!==1?"s":""}</div>
      </div>
    </div>
  );
}

function AlumniPage() {
  const [search, setSearch] = useState("");
  const filtered = MOCK_ALUMNI.filter(a => { const q = search.toLowerCase(); return a.name.toLowerCase().includes(q)||a.email.toLowerCase().includes(q)||a.program.toLowerCase().includes(q); });
  return (
    <div className="space-y-5">
      <div className="flex items-start justify-between flex-wrap gap-3"><div><h1 className="text-2xl font-bold text-gray-900">Alumni Directory</h1><p className="text-gray-500 text-sm mt-1">Manage registered alumni</p></div><button className="flex items-center gap-2 font-bold px-4 py-2.5 rounded-xl text-sm" style={{ backgroundColor:"#fbd117",color:"#1f2b67" }}><i className="bi bi-person-plus"></i> Add Alumni</button></div>
      <div className="flex gap-3 flex-wrap">
        <div className="relative flex-1" style={{ minWidth:"200px" }}><i className="bi bi-search absolute text-gray-400" style={{ left:"14px",top:"50%",transform:"translateY(-50%)" }}></i><input value={search} onChange={e => setSearch(e.target.value)} placeholder="Search alumni..." className="w-full border border-gray-200 rounded-xl text-sm outline-none" style={{ paddingLeft:"40px",paddingRight:"16px",paddingTop:"10px",paddingBottom:"10px" }} /></div>
        <button className="flex items-center gap-2 border border-gray-200 rounded-xl px-4 py-2 text-sm text-gray-600 hover:bg-gray-50"><i className="bi bi-funnel"></i> Filter</button>
        <button className="flex items-center gap-2 border border-gray-200 rounded-xl px-4 py-2 text-sm text-gray-600 hover:bg-gray-50"><i className="bi bi-download"></i> Export</button>
      </div>
      <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div style={{ overflowX:"auto" }}>
          <table className="w-full">
            <thead><tr className="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider border-b border-gray-100"><th className="px-5 py-3 text-left font-semibold">Alumni</th><th className="px-5 py-3 text-left font-semibold">Program</th><th className="px-5 py-3 text-left font-semibold">Year</th><th className="px-5 py-3 text-left font-semibold">Tracer Progress</th><th className="px-5 py-3 text-left font-semibold">Status</th><th className="px-5 py-3 text-left font-semibold">Actions</th></tr></thead>
            <tbody>
              {filtered.map((a,ai) => (
                <tr key={a.id} className={"hover:bg-gray-50 "+(ai<filtered.length-1?"border-b border-gray-50":"")}>
                  <td className="px-5 py-3.5"><div className="flex items-center gap-3"><div className="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-xs flex-none" style={{ backgroundColor:"#1f2b67" }}>{initials(a.name)}</div><div><p className="font-semibold text-gray-900 text-sm">{a.name}</p><p className="text-xs text-gray-400">{a.email}</p></div></div></td>
                  <td className="px-5 py-3.5 text-sm text-gray-600">{a.program}</td><td className="px-5 py-3.5 text-sm text-gray-600">{a.year}</td>
                  <td className="px-5 py-3.5" style={{ minWidth:"140px" }}><ProgressBar value={a.completion} /></td>
                  <td className="px-5 py-3.5"><StatusBadge status={a.status} /></td>
                  <td className="px-5 py-3.5"><div className="flex gap-1"><button className="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-gray-100 text-gray-400"><i className="bi bi-eye text-sm"></i></button><button className="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-gray-100 text-gray-400"><i className="bi bi-pencil text-sm"></i></button><button className="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-red-50 text-red-400"><i className="bi bi-trash text-sm"></i></button></div></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        {filtered.length===0 && <div className="py-12 text-center text-gray-400">No alumni found.</div>}
        <div className="px-5 py-3 border-t border-gray-100 flex items-center justify-between"><span className="text-sm text-gray-500">{filtered.length} alumni</span><div className="flex gap-1">{[1,2,3].map(p => (<button key={p} className="w-7 h-7 rounded-lg text-xs font-semibold" style={{ backgroundColor:p===1?"#1f2b67":"transparent",color:p===1?"white":"#6b7280" }}>{p}</button>))}</div></div>
      </div>
    </div>
  );
}

function SettingsPage() {
  const [notifyEmail,setNotifyEmail]=useState(true); const [autoReminder,setAutoReminder]=useState(true); const [publicRes,setPublicRes]=useState(false);
  const [tracerTitle,setTracerTitle]=useState("Digital Alumni Tracer"); const [tracerYear,setTracerYear]=useState("AY 2024–2025");
  return (
    <div className="space-y-6" style={{ maxWidth:"640px" }}>
      <div><h1 className="text-2xl font-bold text-gray-900">Settings</h1><p className="text-gray-500 text-sm mt-1">Configure the alumni tracer system</p></div>
      <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div className="px-5 py-4 border-b border-gray-100 bg-gray-50"><h2 className="font-bold text-gray-900">Tracer Branding</h2><p className="text-xs text-gray-400 mt-0.5">Customize how the tracer appears to alumni</p></div>
        <div className="px-5 py-5 space-y-4">
          <div><label className="block text-sm font-semibold text-gray-700 mb-1.5">Tracer Title</label><input value={tracerTitle} onChange={e=>setTracerTitle(e.target.value)} className="w-full border-2 border-gray-200 rounded-xl px-3 py-2.5 text-sm outline-none" /></div>
          <div><label className="block text-sm font-semibold text-gray-700 mb-1.5">Academic Year</label><select value={tracerYear} onChange={e=>setTracerYear(e.target.value)} className="w-full border-2 border-gray-200 rounded-xl px-3 py-2.5 text-sm outline-none"><option>AY 2024–2025 (Current)</option><option>AY 2023–2024</option><option>AY 2022–2023</option></select></div>
        </div>
      </div>
      <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div className="px-5 py-4 border-b border-gray-100 bg-gray-50"><h2 className="font-bold text-gray-900">Notifications & Reminders</h2></div>
        <div className="px-5 divide-y divide-gray-50">
          {[{ label:"Email notifications for new submissions",sub:"Get notified when an alumni completes a section",val:notifyEmail,set:setNotifyEmail },{ label:"Automated reminders to incomplete alumni",sub:"Send reminder emails after 7 days of inactivity",val:autoReminder,set:setAutoReminder },{ label:"Publish aggregated results to alumni",sub:"Allow alumni to view anonymized statistics",val:publicRes,set:setPublicRes }].map(item => (
            <div key={item.label} className="flex items-center justify-between py-4"><div><p className="text-sm font-semibold text-gray-800">{item.label}</p><p className="text-xs text-gray-400 mt-0.5">{item.sub}</p></div><Toggle value={item.val} onChange={item.set} /></div>
          ))}
        </div>
      </div>
      <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div className="px-5 py-4 border-b border-gray-100 bg-gray-50"><h2 className="font-bold text-gray-900">Data Management</h2></div>
        <div className="px-5 py-4 space-y-2">
          {[{ icon:"bi-download",label:"Export All Responses",sub:"Download complete dataset as CSV",danger:false },{ icon:"bi-file-earmark-pdf",label:"Generate Summary Report",sub:"Create a PDF summary of tracer results",danger:false },{ icon:"bi-trash",label:"Reset All Responses",sub:"Permanently delete all submitted responses",danger:true }].map(item => (
            <button key={item.label} className={"flex items-center gap-3 w-full py-3 px-4 border rounded-xl text-left "+(item.danger?"border-red-100 hover:bg-red-50":"border-gray-200 hover:bg-gray-50")}>
              <i className={item.icon+" text-lg"} style={{ color:item.danger?"#ef4444":"#1f2b67" }}></i>
              <div><p className={"font-semibold text-sm "+(item.danger?"text-red-600":"text-gray-900")}>{item.label}</p><p className={"text-xs mt-0.5 "+(item.danger?"text-red-400":"text-gray-400")}>{item.sub}</p></div>
            </button>
          ))}
        </div>
      </div>
      <button className="font-bold px-6 py-3 rounded-xl text-sm" style={{ backgroundColor:"#fbd117",color:"#1f2b67" }}>Save Changes</button>
    </div>
  );
}

// ═══════════════════════════════════════════════════════
// ROOT APP
// ═══════════════════════════════════════════════════════

function AdminApp() {
  const [phases, setPhases] = useState(INITIAL_PHASES);
  const [page, setPage] = useState("dashboard");
  const [sidebar, setSidebar] = useState(true);
  const NAV = [{ id:"dashboard",label:"Dashboard",icon:"bi-grid-fill" },{ id:"builder",label:"Tracer Builder",icon:"bi-pencil-square" },{ id:"analytics",label:"Analytics",icon:"bi-bar-chart-fill" },{ id:"responses",label:"Responses",icon:"bi-inbox-fill" },{ id:"alumni",label:"Alumni",icon:"bi-people-fill" },{ id:"settings",label:"Settings",icon:"bi-gear-fill" }];
  return (
    <div className="flex overflow-hidden" style={{ height:"100vh", fontFamily:"'Poppins', sans-serif" }}>
      <div className="flex flex-col flex-none overflow-hidden" style={{ width:sidebar?"240px":"64px", backgroundColor:"#1f2b67", transition:"width 0.3s ease" }}>
        <div className="flex items-center px-4 border-b flex-none" style={{ height:"64px", borderColor:"rgba(255,255,255,0.1)" }}>
          <div className="flex items-center justify-center w-9 h-9 rounded-xl font-black text-sm flex-none" style={{ backgroundColor:"#fbd117",color:"#1f2b67" }}>NU</div>
          {sidebar && (<div className="ml-3 overflow-hidden"><p className="text-white font-bold text-sm leading-tight whitespace-nowrap">NU Lipa</p><p className="text-xs whitespace-nowrap" style={{ color:"rgba(255,255,255,0.4)" }}>Alumni Tracer Admin</p></div>)}
        </div>
        <button onClick={() => setSidebar(!sidebar)} className="flex items-center justify-center mx-3 mt-3 rounded-lg text-sm" style={{ height:"32px", color:"rgba(255,255,255,0.4)" }}><i className={"bi bi-layout-sidebar"+(sidebar?"-reverse":"")}></i></button>
        <nav className="flex-1 py-2 overflow-y-auto">
          {NAV.map(item => (
            <button key={item.id} onClick={() => setPage(item.id)} className="w-full flex items-center transition-all text-sm font-medium" style={{ gap:"12px",padding:"12px 16px",backgroundColor:page===item.id?"rgba(255,255,255,0.15)":"transparent",color:page===item.id?"white":"rgba(255,255,255,0.55)" }}>
              <i className={item.icon+" text-base flex-none "+(sidebar?"":"mx-auto")}></i>
              {sidebar && <span>{item.label}</span>}
              {sidebar && page===item.id && <div className="ml-auto w-1.5 h-1.5 rounded-full" style={{ backgroundColor:"#fbd117" }} />}
            </button>
          ))}
        </nav>
        {sidebar && (
          <div className="p-4 border-t flex-none" style={{ borderColor:"rgba(255,255,255,0.1)" }}>
            <div className="flex items-center gap-2.5">
              <div className="w-8 h-8 rounded-full flex items-center justify-center flex-none" style={{ backgroundColor:"rgba(255,255,255,0.2)" }}><i className="bi bi-person-fill text-white text-sm"></i></div>
              <div className="min-w-0 flex-1"><p className="text-white text-xs font-semibold truncate">Admin</p><p className="text-[10px] truncate" style={{ color:"rgba(255,255,255,0.4)" }}>AAO Office, NU Lipa</p></div>
              <button style={{ color:"rgba(255,255,255,0.4)" }}><i className="bi bi-box-arrow-right"></i></button>
            </div>
          </div>
        )}
      </div>
      <div className="flex flex-col flex-1 overflow-hidden">
        <div className="flex items-center justify-between px-6 flex-none bg-white border-b border-gray-200 shadow-sm" style={{ height:"64px" }}>
          <div><p className="font-bold text-sm" style={{ color:"#1f2b67" }}>{(NAV.find(n=>n.id===page)||{}).label||"Dashboard"}</p><p className="text-xs text-gray-400">NU Lipa · Digital Alumni Tracer</p></div>
          <div className="flex items-center gap-3">
            <button className="relative w-9 h-9 rounded-xl flex items-center justify-center hover:bg-gray-100 text-gray-500"><i className="bi bi-bell"></i><div className="absolute w-2 h-2 rounded-full border-2 border-white bg-red-500" style={{ top:"6px",right:"6px" }} /></button>
            <div className="flex items-center gap-2 pl-3 border-l border-gray-200">
              <div className="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-xs" style={{ backgroundColor:"#1f2b67" }}>AD</div>
              <div><p className="text-xs font-semibold text-gray-800">Admin</p><p className="text-[10px] text-gray-400">AAO Office</p></div>
            </div>
          </div>
        </div>
        <div className="flex-1 overflow-y-auto p-6">
          {page==="dashboard" && <DashboardPage phases={phases} />}
          {page==="builder"   && <TracerBuilderPage phases={phases} setPhases={setPhases} />}
          {page==="analytics" && <AnalyticsPage />}
          {page==="responses" && <ResponsesPage />}
          {page==="alumni"    && <AlumniPage />}
          {page==="settings"  && <SettingsPage />}
        </div>
      </div>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById("root")).render(<AdminApp />);
</script>
</body>
</html>