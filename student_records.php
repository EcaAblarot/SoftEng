<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Records</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .search-panel { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .search-row { display:flex; gap:12px; align-items:center; }
        .search-row input, .search-row select { padding:10px; border-radius:8px; border:1px solid #e6e6e6; }
        .search-btn { background:#0aa64a; color:#fff; border:none; padding:12px 18px; border-radius:8px; cursor:pointer; width:100%; }
        .records-table { margin-top:20px; width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; }
        .records-table th, .records-table td { padding:14px 12px; text-align:left; border-bottom:1px solid #eee; }
        .records-table thead { background:#fafafa; }
        .actions { display:flex; gap:8px; }
        .pill { display:inline-block; padding:6px 8px; border-radius:12px; background:#e6f7ea; color:#1b8f3a; font-weight:600; }
    </style>
</head>
<body>
<div class="dashboard-container">

    <!-- Sidebar (included) -->
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <h1>Student Records</h1>
                <p id="datetime"></p>
            </div>
        </header>

        <section style="margin-top:18px;">
            <div class="search-panel">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <div style="display:flex; gap:8px;">
                        <button class="tab active">Search & Filter</button>
                        <button class="tab">Student Profile</button>
                        <button class="tab">Payment History</button>
                        <button class="tab">Update Records</button>
                    </div>
                </div>

                <div class="search-row">
                    <input id="q" type="text" placeholder="Enter student ID or name" style="flex:1">
                    <select id="grade">
                        <option value="">All grades</option>
                        <option>Grade 1</option>
                        <option>Grade 2</option>
                        <option>Grade 3</option>
                    </select>
                    <select id="section">
                        <option value="">All sections</option>
                        <option>Faith</option>
                        <option>Hope</option>
                        <option>Grace</option>
                    </select>
                </div>
                <div style="margin-top:14px;">
                    <button id="searchBtn" class="search-btn">🔍 Search Students</button>
                </div>

                <table class="records-table" id="studentsTable">
                    <thead>
                        <tr>
                            <th data-sort="id" class="sortable">Student ID ▾</th>
                            <th data-sort="name" class="sortable">Name</th>
                            <th data-sort="grade" class="sortable">Grade</th>
                            <th data-sort="section" class="sortable">Section</th>
                            <th data-sort="status" class="sortable">Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- rows will be loaded via AJAX -->
                    </tbody>
                </table>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px;">
                    <div id="pager"></div>
                    <div style="width:120px; text-align:right;">Per page: <select id="perPage"><option>5</option><option selected>10</option><option>25</option></select></div>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
function updateDateTime() {
  const now = new Date();
  const el = document.getElementById('datetime');
  if (el) el.textContent = now.toLocaleString();
}
setInterval(updateDateTime, 1000);
window.onload = updateDateTime;

// AJAX-driven loading
let state = { page:1, per_page:10, sort_by:'id', sort_dir:'asc' };
function fetchStudents() {
    const q = document.getElementById('q').value.trim();
    const grade = document.getElementById('grade').value;
    const section = document.getElementById('section').value;
    const params = new URLSearchParams({ page: state.page, per_page: state.per_page, sort_by: state.sort_by, sort_dir: state.sort_dir });
    if (q) params.set('q', q);
    if (grade) params.set('grade', grade);
    if (section) params.set('section', section);
    fetch('students_api.php?' + params.toString())
        .then(r=>r.json())
        .then(renderStudents)
        .catch(err=>{ console.error(err); alert('Failed to load students'); });
}

function renderStudents(payload) {
    const tbody = document.querySelector('#studentsTable tbody');
    tbody.innerHTML = '';
    (payload.data || []).forEach(s => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td class="sid">${escapeHtml(s.id)}</td>`+
                       `<td class="sname">${escapeHtml(s.name)}</td>`+
                       `<td class="sgrade">${escapeHtml(s.grade)}</td>`+
                       `<td class="ssection">${escapeHtml(s.section)}</td>`+
                       `<td><span class="pill">${escapeHtml(s.status)}</span></td>`+
                       `<td class="actions"><button title="View">👁️</button> <button title="Edit">✏️</button></td>`;
        tbody.appendChild(tr);
    });
    renderPager(payload.page, payload.per_page, payload.total);
}

function renderPager(page, per_page, total) {
    const pager = document.getElementById('pager');
    pager.innerHTML = '';
    const totalPages = Math.max(1, Math.ceil(total / per_page));
    const createBtn = (p, txt) => { const b = document.createElement('button'); b.textContent = txt; b.disabled = p===page; b.addEventListener('click', ()=>{ state.page = p; fetchStudents(); }); return b; };
    pager.appendChild(createBtn(1,'«1'));
    if (page>1) pager.appendChild(createBtn(page-1,'‹'));
    const start = Math.max(1, page-2);
    const end = Math.min(totalPages, page+2);
    for (let p=start;p<=end;p++) pager.appendChild(createBtn(p,p));
    if (page<totalPages) pager.appendChild(createBtn(page+1,'›'));
    pager.appendChild(createBtn(totalPages, totalPages+'»'));
}


document.getElementById('searchBtn').addEventListener('click', function(e){ e.preventDefault(); state.page=1; fetchStudents(); });
document.getElementById('perPage').addEventListener('change', function(){ state.per_page = parseInt(this.value,10)||10; state.page=1; fetchStudents(); });

// Auto-refresh on input change with debounce
function debounce(fn, wait=300){ let t; return function(...args){ clearTimeout(t); t = setTimeout(()=>fn.apply(this,args), wait); }; }
const qInput = document.getElementById('q');
const gradeInput = document.getElementById('grade');
const sectionInput = document.getElementById('section');
qInput.addEventListener('input', debounce(function(){ state.page=1; fetchStudents(); }, 100));
gradeInput.addEventListener('change', function(){ state.page=1; fetchStudents(); });
sectionInput.addEventListener('change', function(){ state.page=1; fetchStudents(); });

// sortable headers
document.querySelectorAll('.sortable').forEach(h => {
    h.style.cursor='pointer';
    h.addEventListener('click', ()=>{
        const sort = h.getAttribute('data-sort');
        if (state.sort_by === sort) state.sort_dir = state.sort_dir === 'asc' ? 'desc' : 'asc';
        else { state.sort_by = sort; state.sort_dir = 'asc'; }
        state.page = 1;
        fetchStudents();
    });
});

function escapeHtml(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

// initial load
fetchStudents();
</script>
</body>
</html>
