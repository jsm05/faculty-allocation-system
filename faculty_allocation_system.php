<?php
session_start();
if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit;
}
$role      = $_SESSION['user_role'];
$user_name = $_SESSION['user_name'];
$user_email= $_SESSION['user_email'];

// Role capabilities
$can_insert = in_array($role, ['admin', 'editor']);
$can_delete = in_array($role, ['admin']);
$can_ddl    = ($role === 'admin');   // CREATE TABLE, DROP TABLE
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Faculty Course Allocation System</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700&family=Outfit:wght@300;400;500&display=swap" rel="stylesheet"/>
<style>
:root {
  --bg:#f4f2ee; --surface:#ffffff; --ink:#1a1814; --ink2:#6b6860; --ink3:#a8a59e;
  --accent:#2a6ef5; --accent-bg:#eef3ff;
  --green:#1d6b4e; --green-bg:#e8f6f0;
  --red:#b84a18;   --red-bg:#fdf0ea;
  --purple:#6a3db8;--purple-bg:#f2eeff;
  --amber:#92600a; --amber-bg:#fef3dc;
  --teal:#0f6e56;  --teal-bg:#e1f5ee;
  --border:#e2dfd8; --radius:12px; --radius-sm:8px;
  --ff-h:'Syne',sans-serif; --ff-b:'Outfit',sans-serif;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:var(--ff-b);background:var(--bg);color:var(--ink);min-height:100vh}

/* HEADER */
header{background:var(--ink);color:#fff;padding:0 36px;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:200}
.hlogo{width:34px;height:34px;background:var(--accent);border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:var(--ff-h);font-weight:700;font-size:14px;color:#fff;flex-shrink:0}
.htitle{font-family:var(--ff-h);font-size:16px;font-weight:600}
.hsub{font-size:11px;color:rgba(255,255,255,0.4);margin-top:1px}
.hbrand{display:flex;align-items:center;gap:12px}
.hright{display:flex;align-items:center;gap:8px}
.hbadge{padding:5px 12px;background:rgba(255,255,255,0.08);border-radius:20px;font-size:11px;color:rgba(255,255,255,0.6)}

/* SIDEBAR + MAIN */
.layout{display:flex;min-height:calc(100vh - 60px)}
.sidebar{width:230px;flex-shrink:0;background:var(--surface);border-right:1px solid var(--border);padding:20px 0;position:sticky;top:60px;height:calc(100vh - 60px);overflow-y:auto}
.sidebar-section{margin-bottom:6px}
.sidebar-label{font-size:10px;font-weight:600;color:var(--ink3);text-transform:uppercase;letter-spacing:0.1em;padding:8px 20px 4px}
.nav-item{display:flex;align-items:center;gap:10px;padding:9px 20px;cursor:pointer;color:var(--ink2);font-size:13px;border-left:2px solid transparent;transition:all 0.15s}
.nav-item:hover{background:#faf9f7;color:var(--ink)}
.nav-item.active{color:var(--accent);border-left-color:var(--accent);background:var(--accent-bg);font-weight:500}
.nav-icon{width:20px;height:20px;border-radius:5px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0}
.nav-badge{margin-left:auto;font-size:10px;background:var(--bg);padding:1px 7px;border-radius:10px;color:var(--ink3)}
.main{flex:1;overflow-x:hidden}

/* PAGES */
.page{display:none;padding:28px 32px}
.page.active{display:block}

/* PAGE HEADER */
.page-hd{margin-bottom:24px;padding-bottom:18px;border-bottom:1px solid var(--border)}
.page-title{font-family:var(--ff-h);font-size:22px;font-weight:700}
.page-sub{font-size:13px;color:var(--ink2);margin-top:4px}
.page-person{display:inline-block;margin-top:8px;padding:3px 12px;border-radius:20px;font-size:11px;font-weight:600}

/* STATS */
.stats-grid{display:grid;gap:12px;margin-bottom:24px}
.g4{grid-template-columns:repeat(4,1fr)}
.g3{grid-template-columns:repeat(3,1fr)}
.g2{grid-template-columns:repeat(2,1fr)}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:18px 18px 16px;position:relative;overflow:hidden;transition:transform 0.15s,box-shadow 0.15s}
.stat-card:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,0.06)}
.stat-bar{position:absolute;bottom:0;left:0;right:0;height:3px}
.stat-lbl{font-size:10px;font-weight:600;color:var(--ink3);text-transform:uppercase;letter-spacing:0.08em}
.stat-num{font-family:var(--ff-h);font-size:32px;font-weight:700;margin:5px 0 2px;line-height:1}
.stat-desc{font-size:11px;color:var(--ink2)}

/* TABLE */
.table-wrap{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:22px}
table{width:100%;border-collapse:collapse}
thead th{padding:11px 16px;text-align:left;font-size:10px;font-family:var(--ff-h);text-transform:uppercase;letter-spacing:0.07em;color:var(--ink3);background:var(--bg);border-bottom:1px solid var(--border);font-weight:600}
tbody td{padding:12px 16px;border-bottom:1px solid var(--border);font-size:13px}
tbody tr:last-child td{border-bottom:none}
tbody tr:hover{background:#faf9f7}

/* BADGES */
.badge{display:inline-block;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:500}
.b-theory{background:var(--green-bg);color:var(--green)}
.b-lab{background:var(--red-bg);color:var(--red)}
.b-both{background:var(--purple-bg);color:var(--purple)}
.b-blue{background:var(--accent-bg);color:var(--accent)}
.b-green{background:var(--green-bg);color:var(--green)}
.b-red{background:var(--red-bg);color:var(--red)}
.b-amber{background:var(--amber-bg);color:var(--amber)}
.b-purple{background:var(--purple-bg);color:var(--purple)}
.b-teal{background:var(--teal-bg);color:var(--teal)}
.b-gray{background:var(--bg);color:var(--ink2)}

/* CARDS GRID */
.card-grid{display:grid;gap:12px;margin-bottom:22px}
.cg3{grid-template-columns:repeat(3,1fr)}
.cg2{grid-template-columns:repeat(2,1fr)}
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:18px;transition:transform 0.15s,box-shadow 0.15s}
.card:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,0.06)}
.card-code{font-family:var(--ff-h);font-size:12px;font-weight:700;background:var(--ink);color:#fff;padding:3px 9px;border-radius:5px;letter-spacing:0.03em}
.card-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.card-title{font-family:var(--ff-h);font-size:14px;font-weight:600;margin-bottom:6px;line-height:1.3}
.card-meta{font-size:11px;color:var(--ink2)}
.credit-bar{margin-top:10px;height:4px;background:var(--bg);border-radius:2px;overflow:hidden}
.credit-fill{height:100%;border-radius:2px;background:var(--accent)}

/* SECTION */
.sec-hd{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:14px}
.sec-title{font-family:var(--ff-h);font-size:16px;font-weight:600}
.sec-sub{font-size:12px;color:var(--ink2);margin-top:2px}
.sec-count{font-size:12px;color:var(--ink2)}

/* FILTERS */
.filters{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.chip{padding:5px 13px;border-radius:20px;font-size:11.5px;font-weight:500;cursor:pointer;border:1.5px solid var(--border);background:var(--surface);color:var(--ink2);transition:all 0.15s;font-family:var(--ff-b)}
.chip:hover{border-color:var(--accent);color:var(--accent)}
.chip.act{background:var(--accent);border-color:var(--accent);color:#fff}

/* SEARCH */
.search-input{width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-family:var(--ff-b);font-size:13px;color:var(--ink);background:var(--surface);outline:none;transition:border-color 0.15s;margin-bottom:14px}
.search-input:focus{border-color:var(--accent)}
.search-input::placeholder{color:var(--ink3)}

/* FORM */
.form-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:28px;max-width:640px;margin-bottom:22px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.fg{display:flex;flex-direction:column;gap:5px}
.fg.full{grid-column:1/-1}
label{font-size:11px;font-weight:600;color:var(--ink2);text-transform:uppercase;letter-spacing:0.06em}
input,select,textarea{padding:9px 13px;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-family:var(--ff-b);font-size:13px;color:var(--ink);background:var(--surface);outline:none;transition:border-color 0.15s;width:100%}
input:focus,select:focus,textarea:focus{border-color:var(--accent)}
.btn{padding:10px 22px;background:var(--ink);color:#fff;border:none;border-radius:var(--radius-sm);font-family:var(--ff-h);font-size:13px;font-weight:600;cursor:pointer;transition:background 0.15s;margin-top:16px}
.btn:hover{background:var(--accent)}
.btn-sm{padding:6px 14px;font-size:12px;margin-top:0}
.btn-outline{background:transparent;color:var(--ink);border:1.5px solid var(--border)}
.btn-outline:hover{background:var(--bg);border-color:var(--ink)}
.form-note{margin-top:12px;padding:10px 13px;background:var(--accent-bg);border-radius:var(--radius-sm);font-size:12px;color:var(--accent)}
.err{font-size:11px;color:var(--red);margin-top:3px;display:none}

/* BAR CHARTS */
.bar-section{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px;margin-bottom:16px}
.bar-title{font-family:var(--ff-h);font-size:14px;font-weight:600;margin-bottom:16px}
.bar-rows{display:flex;flex-direction:column;gap:12px}
.bar-row{}
.bar-lbl{display:flex;justify-content:space-between;margin-bottom:5px;font-size:12px}
.bar-name{font-weight:500;color:var(--ink)}
.bar-val{color:var(--ink2)}
.bar-track{height:8px;background:var(--bg);border-radius:4px;overflow:hidden}
.bar-fill{height:100%;border-radius:4px;transition:width 0.8s cubic-bezier(.23,1,.32,1)}

/* AVATAR */
.avatar{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:13px;flex-shrink:0}

/* ALERT */
.alert{padding:12px 16px;border-radius:var(--radius-sm);font-size:13px;margin-bottom:14px;border-left:3px solid}
.alert-warn{background:var(--amber-bg);color:var(--amber);border-color:var(--amber)}
.alert-green{background:var(--green-bg);color:var(--green);border-color:var(--green)}
.alert-red{background:var(--red-bg);color:var(--red);border-color:var(--red)}

/* OVERVIEW CARDS */
.overview-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:28px}
.ov-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px;cursor:pointer;transition:all 0.15s;text-align:center}
.ov-card:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,0.08);border-color:var(--accent)}
.ov-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-family:var(--ff-h);font-weight:700;font-size:15px}
.ov-num{font-family:var(--ff-h);font-size:28px;font-weight:700;line-height:1}
.ov-label{font-size:12px;color:var(--ink2);margin-top:4px}
.ov-person{font-size:10px;color:var(--ink3);margin-top:2px}

/* WORKLOAD METER */
.wl-bar{height:12px;background:var(--bg);border-radius:6px;overflow:hidden;margin-top:6px}
.wl-fill{height:100%;border-radius:6px;transition:width 0.6s ease}

/* TIMELINE / LOG */
.log-list{display:flex;flex-direction:column;gap:0}
.log-item{display:flex;gap:14px;padding:12px 0;border-bottom:1px solid var(--border)}
.log-item:last-child{border-bottom:none}
.log-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;margin-top:4px}
.log-title{font-size:13px;font-weight:500}
.log-sub{font-size:12px;color:var(--ink2);margin-top:2px}

/* EMPTY */
.empty{text-align:center;padding:48px 20px;color:var(--ink3);font-size:13px}

/* ANNOUNCE */
.announce-item{display:flex;gap:12px;padding:12px 14px;background:var(--bg);border-radius:var(--radius-sm);margin-bottom:8px;border-left:3px solid var(--accent)}
.a-title{font-size:13px;font-weight:500}
.a-sub{font-size:11px;color:var(--ink2);margin-top:2px}

@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.fu{animation:fadeUp 0.35s ease both}
</style>
</head>
<body>

<header>
  <div class="hbrand">
    <div class="hlogo">FA</div>
    <div>
      <div class="htitle">Faculty Course Allocation System</div>
      <div class="hsub">DBMS Group Project — 7 Modules</div>
    </div>
  </div>
  <div class="hright">
    <span class="hbadge" id="role-badge">Loading…</span>
    <span class="hbadge"><?= htmlspecialchars($user_name) ?> &nbsp;·&nbsp; <?= htmlspecialchars($user_email) ?></span>
    <a href="logout.php" style="padding:5px 14px;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);border-radius:20px;font-size:11px;color:rgba(255,255,255,0.7);text-decoration:none;font-family:var(--ff-h);font-weight:600;transition:background 0.15s;" onmouseover="this.style.background='rgba(184,74,24,0.5)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">Sign Out</a>
  </div>
</header>

<div class="layout">
<!-- SIDEBAR -->
<nav class="sidebar">
  <div class="sidebar-section">
    <div class="sidebar-label">Overview</div>
    <div class="nav-item active" onclick="nav('overview')">
      <div class="nav-icon" style="background:#eef3ff;color:var(--accent)">◈</div>
      Dashboard
    </div>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-label">Modules</div>
    <div class="nav-item" onclick="nav('p1')">
      <div class="nav-icon" style="background:var(--green-bg);color:var(--green)">P1</div>
      Faculty Info
      <span class="nav-badge" id="nb-p1">—</span>
    </div>
    <div class="nav-item" onclick="nav('p2')">
      <div class="nav-icon" style="background:var(--accent-bg);color:var(--accent)">P2</div>
      Courses
      <span class="nav-badge" id="nb-p2">—</span>
    </div>
    <div class="nav-item" onclick="nav('p3')">
      <div class="nav-icon" style="background:var(--amber-bg);color:var(--amber)">P3</div>
      Dept &amp; Semesters
      <span class="nav-badge" id="nb-p3">—</span>
    </div>
    <div class="nav-item" onclick="nav('p4')">
      <div class="nav-icon" style="background:var(--purple-bg);color:var(--purple)">P4</div>
      Allocation
      <span class="nav-badge" id="nb-p4">—</span>
    </div>
    <div class="nav-item" onclick="nav('p5')">
      <div class="nav-icon" style="background:var(--red-bg);color:var(--red)">P5</div>
      Workload
      <span class="nav-badge" id="nb-p5">—</span>
    </div>
    <div class="nav-item" onclick="nav('p6')">
      <div class="nav-icon" style="background:var(--teal-bg);color:var(--teal)">P6</div>
      Triggers &amp; Rules
      <span class="nav-badge">3</span>
    </div>
    <div class="nav-item" onclick="nav('p7')">
      <div class="nav-icon" style="background:var(--purple-bg);color:var(--purple)">P7</div>
      Reports
      <span class="nav-badge">5</span>
    </div>
  </div>
</nav>

<!-- MAIN CONTENT -->
<main class="main">

<!-- ══════════════════════════════════════════
     OVERVIEW
══════════════════════════════════════════ -->
<div class="page active" id="page-overview">
  <div class="page-hd">
    <div class="page-title">System Overview</div>
    <div class="page-sub">Faculty Course Allocation System — complete project dashboard</div>
  </div>

  <div class="overview-grid">
    <div class="ov-card" onclick="nav('p1')">
      <div class="ov-icon" style="background:var(--green-bg);color:var(--green)">P1</div>
      <div class="ov-num" style="color:var(--green)" id="ov-faculty">—</div>
      <div class="ov-label">Faculty Members</div>
      <div class="ov-person">Faculty Info Management</div>
    </div>
    <div class="ov-card" onclick="nav('p2')">
      <div class="ov-icon" style="background:var(--accent-bg);color:var(--accent)">P2</div>
      <div class="ov-num" style="color:var(--accent)" id="ov-courses">—</div>
      <div class="ov-label">Courses Offered</div>
      <div class="ov-person">Course Management</div>
    </div>
    <div class="ov-card" onclick="nav('p3')">
      <div class="ov-icon" style="background:var(--amber-bg);color:var(--amber)">P3</div>
      <div class="ov-num" style="color:var(--amber)" id="ov-depts">—</div>
      <div class="ov-label">Departments</div>
      <div class="ov-person">Dept &amp; Semester Mgmt</div>
    </div>
    <div class="ov-card" onclick="nav('p4')">
      <div class="ov-icon" style="background:var(--purple-bg);color:var(--purple)">P4</div>
      <div class="ov-num" style="color:var(--purple)" id="ov-allocs">—</div>
      <div class="ov-label">Allocations Made</div>
      <div class="ov-person">Faculty–Course Allocation</div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:22px">
    <div class="bar-section">
      <div class="bar-title">Faculty workload overview</div>
      <div class="bar-rows" id="ov-workload"></div>
    </div>
    <div class="bar-section">
      <div class="bar-title">Courses by department</div>
      <div class="bar-rows" id="ov-deptcourses"></div>
    </div>
  </div>

  <div class="table-wrap">
    <table>
      <thead><tr><th>Module</th><th>Person</th><th>Focus</th><th>Key DBMS Concepts</th><th>Status</th></tr></thead>
      <tbody>
        <tr><td><strong>Faculty Info</strong></td><td>Person 1</td><td>Master faculty data, CRUD</td><td>PK, constraints, basic queries</td><td><span class="badge b-green">Complete</span></td></tr>
        <tr><td><strong>Course Mgmt</strong></td><td>Person 2</td><td>Course catalog, type mapping</td><td>Normalization, CHECK constraints</td><td><span class="badge b-green">Complete</span></td></tr>
        <tr><td><strong>Dept &amp; Semester</strong></td><td>Person 3</td><td>Academic structure</td><td>Relationship tables, FKs</td><td><span class="badge b-green">Complete</span></td></tr>
        <tr><td><strong>Allocation</strong></td><td>Person 4</td><td>Faculty–course assignment</td><td>Composite keys, joins</td><td><span class="badge b-green">Complete</span></td></tr>
        <tr><td><strong>Workload</strong></td><td>Person 5</td><td>Load calculation, overload detect</td><td>GROUP BY, HAVING, aggregates</td><td><span class="badge b-green">Complete</span></td></tr>
        <tr><td><strong>Triggers &amp; Rules</strong></td><td>Person 6</td><td>Automation, validation</td><td>Triggers, stored procedures</td><td><span class="badge b-green">Complete</span></td></tr>
        <tr><td><strong>Reports &amp; Admin</strong></td><td>Person 7</td><td>Reporting, admin views</td><td>Views, complex SELECT</td><td><span class="badge b-green">Complete</span></td></tr>
      </tbody>
    </table>
  </div>
</div>

<!-- ══════════════════════════════════════════
     P1 — FACULTY INFO
══════════════════════════════════════════ -->
<div class="page" id="page-p1">
  <div class="page-hd">
    <div class="page-title">Faculty Information Management</div>
    <div class="page-sub">Add, update, view faculty records — master data for the entire system</div>
    <span class="page-person badge b-green">Person 1 · PK, Constraints, CRUD</span>
  </div>

  <div class="stats-grid g4">
    <div class="stat-card"><div class="stat-lbl">Total Faculty</div><div class="stat-num" style="color:var(--green)" id="p1-total">—</div><div class="stat-desc">registered members</div><div class="stat-bar" style="background:var(--green)"></div></div>
    <div class="stat-card"><div class="stat-lbl">Professors</div><div class="stat-num" id="p1-prof">—</div><div class="stat-desc">senior designation</div><div class="stat-bar" style="background:var(--accent)"></div></div>
    <div class="stat-card"><div class="stat-lbl">Asst. Professors</div><div class="stat-num" id="p1-asst">—</div><div class="stat-desc">mid-level designation</div><div class="stat-bar" style="background:var(--amber)"></div></div>
    <div class="stat-card"><div class="stat-lbl">Avg Max Workload</div><div class="stat-num" id="p1-wl">—</div><div class="stat-desc">hours / semester</div><div class="stat-bar" style="background:var(--purple)"></div></div>
  </div>

  <div class="sec-hd"><div><div class="sec-title">Faculty Directory</div><div class="sec-sub">All registered faculty with department and workload</div></div></div>
  <input class="search-input" placeholder="Search faculty by name, email or department…" oninput="searchP1(this.value)"/>
  <div class="table-wrap"><table>
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Designation</th><th>Department</th><th>Max Workload</th></tr></thead>
    <tbody id="p1-tbody"></tbody>
  </table></div>

  <?php if ($can_insert): ?>
  <div class="sec-hd" style="margin-top:8px"><div><div class="sec-title">Add Faculty</div></div></div>
  <div class="form-card">
    <div class="form-grid">
      <div class="fg"><label>Full Name</label><input id="p1-fname" placeholder="Dr. Firstname Lastname"/></div>
      <div class="fg"><label>Email</label><input id="p1-femail" type="email" placeholder="name@college.edu"/></div>
      <div class="fg"><label>Designation</label>
        <select id="p1-fdesig">
          <option>Professor</option><option>Associate Professor</option><option>Assistant Professor</option><option>Lecturer</option>
        </select>
      </div>
      <div class="fg"><label>Department</label>
        <select id="p1-fdept">
          <option value="1">Computer Science</option><option value="2">Electronics</option><option value="3">Mechanical</option>
        </select>
      </div>
      <div class="fg"><label>Max Workload (hrs)</label><input id="p1-fwl" type="number" min="4" max="30" placeholder="e.g. 18"/></div>
    </div>
    <div class="form-note">⚑ Email must be unique (UNIQUE constraint). Max workload enforced between 4–30 hours.</div>
    <button class="btn" onclick="addFaculty()">Insert Faculty Record</button>
  </div>
  <?php else: ?>
  <div class="alert alert-warn" style="max-width:640px">🔒 <strong>Viewer access:</strong> You can view faculty records but cannot add or modify them.</div>
  <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════
     P2 — COURSE MANAGEMENT
══════════════════════════════════════════ -->
<div class="page" id="page-p2">
  <div class="page-hd">
    <div class="page-title">Course Management</div>
    <div class="page-sub">Course catalog mapped to departments and semesters</div>
    <span class="page-person badge b-blue">Person 2 · Normalization, CHECK Constraints</span>
  </div>

  <div class="stats-grid g4">
    <div class="stat-card"><div class="stat-lbl">Total Courses</div><div class="stat-num" style="color:var(--accent)" id="p2-total">—</div><div class="stat-desc">across all departments</div><div class="stat-bar" style="background:var(--accent)"></div></div>
    <div class="stat-card"><div class="stat-lbl">Theory</div><div class="stat-num" style="color:var(--green)" id="p2-theory">—</div><div class="stat-desc">lecture courses</div><div class="stat-bar" style="background:var(--green)"></div></div>
    <div class="stat-card"><div class="stat-lbl">Lab</div><div class="stat-num" style="color:var(--red)" id="p2-lab">—</div><div class="stat-desc">practical courses</div><div class="stat-bar" style="background:var(--red)"></div></div>
    <div class="stat-card"><div class="stat-lbl">Theory+Lab</div><div class="stat-num" style="color:var(--purple)" id="p2-both">—</div><div class="stat-desc">integrated</div><div class="stat-bar" style="background:var(--purple)"></div></div>
  </div>

  <div class="sec-hd"><div><div class="sec-title">Course Catalog</div></div><span id="p2-count" class="sec-count">— courses</span></div>
  <input class="search-input" placeholder="Search courses…" oninput="filterP2(this.value)"/>
  <div class="filters" id="p2-chips">
    <button class="chip act" onclick="setP2Filter('all',this)">All</button>
    <button class="chip" onclick="setP2Filter('Theory',this)">Theory</button>
    <button class="chip" onclick="setP2Filter('Lab',this)">Lab</button>
    <button class="chip" onclick="setP2Filter('Theory+Lab',this)">Theory+Lab</button>
  </div>
  <div class="card-grid cg3" id="p2-grid"></div>

  <?php if ($can_insert): ?>
  <div class="sec-hd" style="margin-top:4px"><div><div class="sec-title">Add Course</div></div></div>
  <div class="form-card">
    <div class="form-grid">
      <div class="fg"><label>Course Name</label><input id="p2-cn" placeholder="e.g. Data Structures"/><span class="err" id="p2-en">Required</span></div>
      <div class="fg"><label>Course Code</label><input id="p2-cc" placeholder="e.g. CS101" oninput="this.value=this.value.toUpperCase()"/><span class="err" id="p2-ec">Format: 2–4 letters + 3–4 digits</span></div>
      <div class="fg"><label>Credits (1–6)</label><input id="p2-cr" type="number" min="1" max="6"/><span class="err" id="p2-ecr">Must be 1–6</span></div>
      <div class="fg"><label>Type</label><select id="p2-ct"><option>Theory</option><option>Lab</option><option>Theory+Lab</option></select></div>
      <div class="fg"><label>Department</label><select id="p2-cd"><option value="1">Computer Science</option><option value="2">Electronics</option><option value="3">Mechanical</option></select></div>
      <div class="fg"><label>Semester</label><select id="p2-cs"><option value="1">Sem 1 2023-24</option><option value="2">Sem 2 2023-24</option><option value="3">Sem 3 2023-24</option></select></div>
    </div>
    <div class="form-note">⚑ CHECK: credits 1–6 · course_code must match pattern (CS101, EC202…) · UNIQUE code enforced</div>
    <button class="btn" onclick="addCourse()">Insert Course</button>
  </div>
  <?php else: ?>
  <div class="alert alert-warn" style="max-width:640px">🔒 <strong>Viewer access:</strong> You can view courses but cannot add or modify them.</div>
  <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════
     P3 — DEPT & SEMESTER
══════════════════════════════════════════ -->
<div class="page" id="page-p3">
  <div class="page-hd">
    <div class="page-title">Department &amp; Semester Management</div>
    <div class="page-sub">Academic structure — departments, semesters, and their course mappings</div>
    <span class="page-person badge b-amber" style="background:var(--amber-bg);color:var(--amber)">Person 3 · Relationship Tables, FKs</span>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:22px">
    <div>
      <div class="sec-hd"><div><div class="sec-title">Departments</div></div></div>
      <div class="table-wrap"><table>
        <thead><tr><th>ID</th><th>Department Name</th><th>Courses</th><th>Faculty</th></tr></thead>
        <tbody id="p3-dept-tbody"></tbody>
      </table></div>
      <?php if ($can_insert): ?>
      <div class="form-card" style="margin-top:14px">
        <div class="form-grid" style="grid-template-columns:1fr">
          <div class="fg"><label>Department Name</label><input id="p3-dname" placeholder="e.g. Information Technology"/></div>
        </div>
        <button class="btn btn-sm" onclick="addDept()">Add Department</button>
      </div>
      <?php endif; ?>
    </div>
    <div>
      <div class="sec-hd"><div><div class="sec-title">Semesters</div></div></div>
      <div class="table-wrap"><table>
        <thead><tr><th>ID</th><th>Sem No.</th><th>Academic Year</th><th>Courses</th></tr></thead>
        <tbody id="p3-sem-tbody"></tbody>
      </table></div>
      <?php if ($can_insert): ?>
      <div class="form-card" style="margin-top:14px">
        <div class="form-grid">
          <div class="fg"><label>Semester Number</label><input id="p3-sno" type="number" min="1" max="8" placeholder="1–8"/></div>
          <div class="fg"><label>Academic Year</label><input id="p3-syear" placeholder="e.g. 2023-24"/></div>
        </div>
        <button class="btn btn-sm" onclick="addSem()">Add Semester</button>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="sec-hd"><div><div class="sec-title">Department–Semester Course Mapping</div><div class="sec-sub">Courses per department per semester (GROUP BY query result)</div></div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Department</th><th>Semester</th><th>Academic Year</th><th>Courses</th><th>Total Credits</th></tr></thead>
    <tbody id="p3-mapping-tbody"></tbody>
  </table></div>
</div>

<!-- ══════════════════════════════════════════
     P4 — ALLOCATION
══════════════════════════════════════════ -->
<div class="page" id="page-p4">
  <div class="page-hd">
    <div class="page-title">Faculty–Course Allocation</div>
    <div class="page-sub">Assign faculty to courses per semester — core allocation logic</div>
    <span class="page-person badge b-purple">Person 4 · Composite Keys, Joins</span>
  </div>

  <div class="stats-grid g3">
    <div class="stat-card"><div class="stat-lbl">Total Allocations</div><div class="stat-num" style="color:var(--purple)" id="p4-total">—</div><div class="stat-desc">assignments made</div><div class="stat-bar" style="background:var(--purple)"></div></div>
    <div class="stat-card"><div class="stat-lbl">Courses Allocated</div><div class="stat-num" id="p4-alloc">—</div><div class="stat-desc" id="p4-alloc-desc">out of — courses</div><div class="stat-bar" style="background:var(--accent)"></div></div>
    <div class="stat-card"><div class="stat-lbl">Unallocated</div><div class="stat-num" style="color:var(--red)" id="p4-unalloc">—</div><div class="stat-desc">need assignment</div><div class="stat-bar" style="background:var(--red)"></div></div>
  </div>

  <div id="p4-overload-alert" class="alert alert-warn" style="display:none">⚠ One or more faculty members have exceeded their maximum workload. Review workload module.</div>

  <div class="sec-hd"><div><div class="sec-title">Current Allocations</div></div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Alloc ID</th><th>Faculty</th><th>Course</th><th>Department</th><th>Semester</th><th>Hours</th><th>Action</th></tr></thead>
    <tbody id="p4-tbody"></tbody>
  </table></div>

  <?php if ($can_insert): ?>
  <div class="sec-hd"><div><div class="sec-title">New Allocation</div><div class="sec-sub">Duplicate allocations prevented by UNIQUE constraint</div></div></div>
  <div class="form-card">
    <div class="form-grid">
      <div class="fg"><label>Faculty</label><select id="p4-fac"></select></div>
      <div class="fg"><label>Course</label><select id="p4-crs"></select></div>
      <div class="fg"><label>Semester</label><select id="p4-sem"></select></div>
      <div class="fg"><label>Assigned Hours</label><input id="p4-hrs" type="number" min="1" max="6" placeholder="e.g. 4"/></div>
    </div>
    <div class="form-note">⚑ Composite UNIQUE key on (faculty_id, course_id, semester_id) prevents double allocation. Trigger checks max workload.</div>
    <button class="btn" onclick="addAlloc()">Create Allocation</button>
    <button class="btn btn-outline" onclick="smartSuggest()">Smart Allocation (AI)</button>
    <div id="ai-suggestions" style="margin-top:16px;"></div>
  </div>
  <?php else: ?>
  <div class="alert alert-warn">🔒 <strong>Viewer access:</strong> You can view allocations but cannot create or delete them.</div>
  <?php endif; ?>

  <div class="sec-hd" style="margin-top:8px"><div><div class="sec-title">Unallocated Courses</div><div class="sec-sub">LEFT JOIN result — courses with no faculty assigned</div></div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Course Code</th><th>Course Name</th><th>Type</th><th>Department</th></tr></thead>
    <tbody id="p4-unalloc-tbody"></tbody>
  </table></div>
</div>

<!-- ══════════════════════════════════════════
     P5 — WORKLOAD
══════════════════════════════════════════ -->
<div class="page" id="page-p5">
  <div class="page-hd">
    <div class="page-title">Faculty Workload Management</div>
    <div class="page-sub">Total credits/hours per faculty — overload detection and semester load report</div>
    <span class="page-person badge b-red" style="background:var(--red-bg);color:var(--red)">Person 5 · GROUP BY, HAVING, Aggregate Functions</span>
  </div>

  <div class="stats-grid g3">
    <div class="stat-card"><div class="stat-lbl">Avg Load</div><div class="stat-num" id="p5-avg">—</div><div class="stat-desc">hours per faculty</div><div class="stat-bar" style="background:var(--accent)"></div></div>
    <div class="stat-card"><div class="stat-lbl">Overloaded</div><div class="stat-num" style="color:var(--red)" id="p5-over">—</div><div class="stat-desc">exceeding max workload</div><div class="stat-bar" style="background:var(--red)"></div></div>
    <div class="stat-card"><div class="stat-lbl">Under-utilized</div><div class="stat-num" style="color:var(--amber)" id="p5-under">—</div><div class="stat-desc">below 8 hours</div><div class="stat-bar" style="background:var(--amber)"></div></div>
  </div>

  <div class="sec-hd"><div><div class="sec-title">Workload per Faculty</div><div class="sec-sub">GROUP BY faculty_id — aggregate hours from Allocation table</div></div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Faculty</th><th>Department</th><th>Assigned Hours</th><th>Max Workload</th><th>Load %</th><th>Status</th></tr></thead>
    <tbody id="p5-tbody"></tbody>
  </table></div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:4px">
    <div class="bar-section">
      <div class="bar-title">Workload distribution</div>
      <div class="bar-rows" id="p5-bars"></div>
    </div>
    <div class="bar-section">
      <div class="bar-title">Faculty load by department (HAVING &gt; 0)</div>
      <div class="bar-rows" id="p5-dept-bars"></div>
    </div>
  </div>
</div>
<!-- ══════════════════════════════════════════
     P6 — TRIGGERS & RULES
══════════════════════════════════════════ -->
<div class="page" id="page-p6">
  <div class="page-hd">
    <div class="page-title">Constraints, Triggers &amp; Validation</div>
    <div class="page-sub">Automation rules — overload prevention, duplicate blocking, stored procedures</div>
    <span class="page-person badge b-teal" style="background:var(--teal-bg);color:var(--teal)">Person 6 · Triggers, Procedures, Transactions</span>
  </div>

  <div class="stats-grid g3">
    <div class="stat-card"><div class="stat-lbl">Triggers Defined</div><div class="stat-num" style="color:var(--teal)">2</div><div class="stat-desc">BEFORE INSERT active</div><div class="stat-bar" style="background:var(--teal)"></div></div>
    <div class="stat-card"><div class="stat-lbl">Stored Procedures</div><div class="stat-num">1</div><div class="stat-desc">sp_allocate_faculty</div><div class="stat-bar" style="background:var(--accent)"></div></div>
    <div class="stat-card"><div class="stat-lbl">Blocked Attempts</div><div class="stat-num" style="color:var(--red)" id="p6-blocked">0</div><div class="stat-desc">violations caught</div><div class="stat-bar" style="background:var(--red)"></div></div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:22px">
    <div class="card" style="padding:22px">
      <div class="sec-title" style="margin-bottom:12px">Trigger 1 — Overload prevention</div>
      <div style="background:var(--bg);border-radius:var(--radius-sm);padding:14px;font-family:monospace;font-size:12px;color:var(--ink);line-height:1.7;white-space:pre-wrap">BEFORE INSERT ON Allocation
FOR EACH ROW
BEGIN
  SET @total = (
    SELECT COALESCE(SUM(assigned_hours),0)
    FROM Allocation
    WHERE faculty_id = NEW.faculty_id
  );
  IF @total + NEW.assigned_hours >
     (SELECT max_workload FROM Faculty
      WHERE faculty_id = NEW.faculty_id)
  THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT =
    'Workload exceeds faculty limit';
  END IF;
END</div>
    </div>
    <div class="card" style="padding:22px">
      <div class="sec-title" style="margin-bottom:12px">Trigger 2 — Duplicate allocation block</div>
      <div style="background:var(--bg);border-radius:var(--radius-sm);padding:14px;font-family:monospace;font-size:12px;color:var(--ink);line-height:1.7;white-space:pre-wrap">BEFORE INSERT ON Allocation
FOR EACH ROW
BEGIN
  IF EXISTS (
    SELECT 1 FROM Allocation
    WHERE faculty_id  = NEW.faculty_id
    AND   course_id   = NEW.course_id
    AND   semester_id = NEW.semester_id
  ) THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT =
    'Duplicate allocation exists';
  END IF;
END</div>
    </div>
  </div>

  <div class="card" style="padding:22px;margin-bottom:22px">
    <div class="sec-title" style="margin-bottom:12px">Stored procedure — sp_allocate_faculty</div>
    <div style="background:var(--bg);border-radius:var(--radius-sm);padding:14px;font-family:monospace;font-size:12px;color:var(--ink);line-height:1.7;white-space:pre-wrap">CREATE PROCEDURE sp_allocate_faculty(
  IN p_faculty_id INT,
  IN p_course_id  INT,
  IN p_semester_id INT,
  IN p_hours INT
)
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;
  START TRANSACTION;
    INSERT INTO Allocation
      (faculty_id, course_id, semester_id, assigned_hours)
    VALUES
      (p_faculty_id, p_course_id, p_semester_id, p_hours);
  COMMIT;
END</div>
  </div>

  <div class="sec-hd"><div><div class="sec-title">Live trigger demo</div><div class="sec-sub">Simulate an allocation attempt — triggers fire in real-time</div></div></div>
  <div class="form-card">
    <div class="form-grid">
      <div class="fg"><label>Faculty</label><select id="p6-fac"></select></div>
      <div class="fg"><label>Course</label><select id="p6-crs"></select></div>
      <div class="fg"><label>Hours to assign</label><input id="p6-hrs" type="number" min="1" max="20" placeholder="e.g. 4"/></div>
    </div>
    <button class="btn" onclick="triggerDemo()">Simulate Allocation</button>
  </div>
  <div id="p6-result" style="display:none;margin-top:4px"></div>
  <div class="sec-hd" style="margin-top:8px"><div><div class="sec-title">Trigger log</div></div></div>
  <div class="card" style="padding:0 18px"><div class="log-list" id="p6-log"><div class="empty">No trigger events yet. Run a simulation above.</div></div></div>
</div>

<!-- ══════════════════════════════════════════
     P7 — REPORTS & ADMIN
══════════════════════════════════════════ -->
<div class="page" id="page-p7">
  <div class="page-hd">
    <div class="page-title">Reporting &amp; Admin Dashboard</div>
    <div class="page-sub">Faculty-wise, course-wise and department-wise reports — admin views</div>
    <span class="page-person badge b-purple">Person 7 · Views, Complex SELECT Queries</span>
  </div>

  <div class="filters" style="margin-bottom:20px">
    <button class="chip act" onclick="setP7Report('faculty',this)">Faculty-wise Report</button>
    <button class="chip" onclick="setP7Report('course',this)">Course-wise Report</button>
    <button class="chip" onclick="setP7Report('dept',this)">Department Summary</button>
    <button class="chip" onclick="setP7Report('unalloc',this)">Unallocated Courses</button>
    <button class="chip" onclick="setP7Report('views',this)">DB Views</button>
  </div>

  <div id="p7-faculty" class="p7-report">
    <div class="sec-hd"><div><div class="sec-title">Faculty-wise allocation report</div><div class="sec-sub">VIEW: vw_faculty_report — JOIN Faculty, Allocation, Course, Department</div></div></div>
    <div class="table-wrap"><table>
      <thead><tr><th>Faculty</th><th>Designation</th><th>Department</th><th>Courses Assigned</th><th>Total Hours</th><th>Max Workload</th><th>Load Status</th></tr></thead>
      <tbody id="p7-f-tbody"></tbody>
    </table></div>
  </div>

  <div id="p7-course" class="p7-report" style="display:none">
    <div class="sec-hd"><div><div class="sec-title">Course-wise allocation report</div><div class="sec-sub">VIEW: vw_course_report — JOIN Course, Allocation, Faculty, Semester</div></div></div>
    <div class="table-wrap"><table>
      <thead><tr><th>Code</th><th>Course</th><th>Type</th><th>Credits</th><th>Assigned Faculty</th><th>Semester</th><th>Hours</th></tr></thead>
      <tbody id="p7-c-tbody"></tbody>
    </table></div>
  </div>

  <div id="p7-dept" class="p7-report" style="display:none">
    <div class="sec-hd"><div><div class="sec-title">Department-wise summary</div><div class="sec-sub">GROUP BY dept_id — aggregate courses, credits, faculty count</div></div></div>
    <div class="table-wrap"><table>
      <thead><tr><th>Department</th><th>Total Courses</th><th>Total Credits</th><th>Faculty Count</th><th>Avg Load (hrs)</th></tr></thead>
      <tbody id="p7-d-tbody"></tbody>
    </table></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
      <div class="bar-section"><div class="bar-title">Courses per department</div><div class="bar-rows" id="p7-dept-bars"></div></div>
      <div class="bar-section"><div class="bar-title">Credits per department</div><div class="bar-rows" id="p7-credit-bars"></div></div>
    </div>
  </div>

  <div id="p7-unalloc" class="p7-report" style="display:none">
    <div class="alert alert-warn">These courses returned by LEFT JOIN Allocation WHERE allocation_id IS NULL</div>
    <div class="table-wrap"><table>
      <thead><tr><th>Code</th><th>Course</th><th>Type</th><th>Department</th><th>Semester</th></tr></thead>
      <tbody id="p7-u-tbody"></tbody>
    </table></div>
  </div>

  <div id="p7-views" class="p7-report" style="display:none">
    <div class="sec-hd"><div><div class="sec-title">Database views defined</div></div></div>
    <div class="stats-grid g3" style="margin-bottom:16px">
      <div class="stat-card"><div class="stat-lbl">Views Created</div><div class="stat-num" style="color:var(--purple)">3</div><div class="stat-desc">admin access views</div><div class="stat-bar" style="background:var(--purple)"></div></div>
      <div class="stat-card"><div class="stat-lbl">Tables Joined</div><div class="stat-num">5</div><div class="stat-desc">per view avg</div><div class="stat-bar" style="background:var(--accent)"></div></div>
      <div class="stat-card"><div class="stat-lbl">Aggregates</div><div class="stat-num">4</div><div class="stat-desc">SUM, COUNT, AVG, MAX</div><div class="stat-bar" style="background:var(--teal)"></div></div>
    </div>
    <div style="display:flex;flex-direction:column;gap:14px">
      <div class="card" style="padding:20px">
        <div style="font-family:var(--ff-h);font-size:14px;font-weight:600;margin-bottom:10px;color:var(--purple)">vw_faculty_report</div>
        <div style="background:var(--bg);border-radius:var(--radius-sm);padding:12px;font-family:monospace;font-size:12px;line-height:1.7;white-space:pre-wrap">SELECT f.faculty_id, f.faculty_name, f.designation,
       d.dept_name, COUNT(a.course_id) AS courses_assigned,
       COALESCE(SUM(a.assigned_hours),0) AS total_hours,
       f.max_workload
FROM Faculty f
JOIN Department d ON f.dept_id = d.dept_id
LEFT JOIN Allocation a ON f.faculty_id = a.faculty_id
GROUP BY f.faculty_id</div>
      </div>
      <div class="card" style="padding:20px">
        <div style="font-family:var(--ff-h);font-size:14px;font-weight:600;margin-bottom:10px;color:var(--purple)">vw_course_report</div>
        <div style="background:var(--bg);border-radius:var(--radius-sm);padding:12px;font-family:monospace;font-size:12px;line-height:1.7;white-space:pre-wrap">SELECT c.course_id, c.course_code, c.course_name,
       c.course_type, c.credits, f.faculty_name,
       s.semester_no, s.academic_year, a.assigned_hours
FROM Course c
LEFT JOIN Allocation a  ON c.course_id  = a.course_id
LEFT JOIN Faculty f     ON a.faculty_id = f.faculty_id
LEFT JOIN Semester s    ON a.semester_id= s.semester_id</div>
      </div>
      <div class="card" style="padding:20px">
        <div style="font-family:var(--ff-h);font-size:14px;font-weight:600;margin-bottom:10px;color:var(--purple)">vw_dept_summary</div>
        <div style="background:var(--bg);border-radius:var(--radius-sm);padding:12px;font-family:monospace;font-size:12px;line-height:1.7;white-space:pre-wrap">SELECT d.dept_name,
       COUNT(DISTINCT c.course_id)  AS total_courses,
       SUM(c.credits)               AS total_credits,
       COUNT(DISTINCT f.faculty_id) AS faculty_count,
       ROUND(AVG(a.assigned_hours),1) AS avg_load
FROM Department d
LEFT JOIN Course c     ON d.dept_id = c.dept_id
LEFT JOIN Faculty f    ON d.dept_id = f.dept_id
LEFT JOIN Allocation a ON f.faculty_id = a.faculty_id
GROUP BY d.dept_id</div>
      </div>
    </div>
  </div>
</div>

</main>
</div><!-- /layout -->

<script>
const API = "http://localhost/faculty/api.php";
const USER_ROLE  = "<?= $role ?>";
const USER_NAME  = "<?= htmlspecialchars($user_name) ?>";
const USER_EMAIL = "<?= htmlspecialchars($user_email) ?>";
const CAN_INSERT = <?= $can_insert ? 'true' : 'false' ?>;
const CAN_DELETE = <?= $can_delete ? 'true' : 'false' ?>;
const CAN_DDL    = <?= $can_ddl    ? 'true' : 'false' ?>;

// ═══════════════════════════════════════════
// GLOBAL DATA STORE  (all populated from API in init())
// ═══════════════════════════════════════════
let faculty     = [];
let courses     = [];
let allocations = [];
let departments = [];
let semesters   = [];

let p6Log     = [];
let p6Blocked = 0;

// ═══════════════════════════════════════════
// NAVIGATION
// ═══════════════════════════════════════════
function nav(id) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('page-' + id).classList.add('active');
  event.currentTarget.classList.add('active');
  renderPage(id);
}

// ═══════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════
// BUG FIX: was using x.id / a.fac_id / a.hrs — now uses correct API field names
function fName(id) { const f = faculty.find(x => x.faculty_id == id); return f ? f.faculty_name : '—'; }
function cName(id) { const c = courses.find(x => x.course_id == id); return c ? c.course_name : '—'; }
function dName(id) { const d = departments.find(x => x.dept_id == id); return d ? d.dept_name : '—'; }
function sName(id) { const s = semesters.find(x => x.semester_id == id); return s ? `Sem ${s.semester_no} ${s.academic_year}` : '—'; }
function typeClass(t) { return t === 'Theory' ? 'b-theory' : t === 'Lab' ? 'b-lab' : 'b-both'; }
function allocHours(facId) {
  // BUG FIX: was a.fac_id / a.hrs — API returns faculty_id / assigned_hours
  return allocations.filter(a => a.faculty_id == facId).reduce((s, a) => s + parseInt(a.assigned_hours), 0);
}

function renderBars(containerId, rows, colorVar) {
  const max = Math.max(...rows.map(r => r.val), 1);
  document.getElementById(containerId).innerHTML = rows.map(r => `
    <div class="bar-row">
      <div class="bar-lbl"><span class="bar-name">${r.label}</span><span class="bar-val">${r.val}${r.unit || ''}</span></div>
      <div class="bar-track"><div class="bar-fill" style="width:${Math.round(r.val / max * 100)}%;background:${colorVar}"></div></div>
    </div>`).join('');
}

// ═══════════════════════════════════════════
// RENDER DISPATCH
// BUG FIX: was missing p3, p6, p7 — navigating to those pages did nothing
// ═══════════════════════════════════════════
function renderPage(id) {
  if (id === 'overview') { renderOverview(); return; }
  if (id === 'p1') renderP1();
  if (id === 'p2') renderP2();
  if (id === 'p3') renderP3();
  if (id === 'p4') renderP4();
  if (id === 'p5') renderP5();
  if (id === 'p6') renderP6();
  if (id === 'p7') renderP7();
}

function renderOverview() {
  // BUG FIX: was f.name / f.id / DEPTS[DEPT_CODES[c.dept]] — now uses API field names
  if (!faculty.length) return;

  // Update overview cards
  document.getElementById('ov-faculty').textContent = faculty.length;
  document.getElementById('ov-courses').textContent  = courses.length;
  document.getElementById('ov-depts').textContent    = departments.length;
  document.getElementById('ov-allocs').textContent   = allocations.length;

  // Update sidebar nav badges
  document.getElementById('nb-p1').textContent = faculty.length;
  document.getElementById('nb-p2').textContent = courses.length;
  document.getElementById('nb-p3').textContent = departments.length + semesters.length;
  document.getElementById('nb-p4').textContent = allocations.length;
  document.getElementById('nb-p5').textContent = faculty.length;

  renderBars('ov-workload',
    faculty.map(f => ({ label: f.faculty_name.split(' ').slice(-1)[0], val: allocHours(f.faculty_id), unit: ' hrs' })),
    'var(--accent)');
  const deptCount = {};
  courses.forEach(c => { const n = dName(c.dept_id); deptCount[n] = (deptCount[n] || 0) + 1; });
  renderBars('ov-deptcourses', Object.entries(deptCount).map(([label, val]) => ({ label, val })), 'var(--green)');
}

// ═══════════════════════════════════════════
// P1 — FACULTY
// ═══════════════════════════════════════════
let p1Search = '';
async function renderP1(search) {
  if (search !== undefined) p1Search = search;
  const res = await fetch(`${API}?action=get_faculty`);
  faculty = await res.json(); // keep global in sync

  // Update P1 stat cards
  document.getElementById('p1-total').textContent = faculty.length;
  document.getElementById('p1-prof').textContent  = faculty.filter(f => f.designation === 'Professor').length;
  document.getElementById('p1-asst').textContent  = faculty.filter(f => f.designation !== 'Professor').length;
  const avgWl = faculty.length ? Math.round(faculty.reduce((s,f) => s + parseInt(f.max_workload), 0) / faculty.length) : 0;
  document.getElementById('p1-wl').textContent    = avgWl;
  document.getElementById('nb-p1').textContent    = faculty.length;

  const filtered = faculty.filter(f =>
    f.faculty_name.toLowerCase().includes(p1Search) ||
    f.email.toLowerCase().includes(p1Search) ||
    f.dept_name.toLowerCase().includes(p1Search)
  );

  document.getElementById('p1-tbody').innerHTML = filtered.map(f => `
    <tr>
      <td>${f.faculty_id}</td>
      <td>${f.faculty_name}</td>
      <td>${f.email}</td>
      <td>${f.designation}</td>
      <td>${f.dept_name}</td>
      <td>${f.max_workload}</td>
    </tr>`).join('');

  populateSelects();
}
function searchP1(v) { renderP1(v.toLowerCase()); }

async function addFaculty() {
  const fname   = document.getElementById("p1-fname").value.trim();
  const femail  = document.getElementById("p1-femail").value.trim();
  const fdesig  = document.getElementById("p1-fdesig").value;
  const fwl     = parseInt(document.getElementById("p1-fwl").value);
  const fdept   = parseInt(document.getElementById("p1-fdept").value);

  // Frontend validation
  if (!fname)               { alert("Please enter the faculty name.");         return; }
  if (!femail)              { alert("Please enter the email address.");         return; }
  if (!femail.includes("@")){ alert("Please enter a valid email address.");    return; }
  if (isNaN(fwl) || fwl < 4 || fwl > 30) { alert("Max workload must be between 4 and 30."); return; }
  if (isNaN(fdept))         { alert("Please select a department.");            return; }

  const payload = {
    faculty_name: fname,
    email:        femail,
    designation:  fdesig,
    max_workload: fwl,
    dept_id:      fdept
  };

  try {
    const res  = await fetch(`${API}?action=add_faculty`, {
      method: "POST", headers: {"Content-Type":"application/json"}, body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.status === "error") {
      // Make MySQL duplicate key error human-readable
      if (data.message && data.message.toLowerCase().includes("duplicate")) {
        alert("❌ Error: A faculty member with this email already exists.\nEmail must be unique.");
      } else {
        alert("❌ Error: " + (data.message || "Something went wrong. Please try again."));
      }
    } else {
      alert("✅ Faculty added successfully!");
      // Clear the form
      document.getElementById("p1-fname").value  = "";
      document.getElementById("p1-femail").value = "";
      document.getElementById("p1-fwl").value    = "16";
      await renderP1();
    }
  } catch (e) {
    alert("❌ Network error — is XAMPP running?\n" + e.message);
  }
}

// ═══════════════════════════════════════════
// P2 — COURSES
// ═══════════════════════════════════════════
let p2Filter = 'all', p2Search = '';

async function renderP2() {
  const res  = await fetch(`${API}?action=get_courses`);
  courses = await res.json(); // keep global in sync

  // Update P2 stat cards
  document.getElementById('p2-total').textContent  = courses.length;
  document.getElementById('p2-theory').textContent = courses.filter(c => c.course_type === 'Theory').length;
  document.getElementById('p2-lab').textContent    = courses.filter(c => c.course_type === 'Lab').length;
  document.getElementById('p2-both').textContent   = courses.filter(c => c.course_type === 'Theory+Lab').length;
  document.getElementById('nb-p2').textContent     = courses.length;

  // Rebuild department filter chips dynamically
  const chipsEl = document.getElementById('p2-chips');
  const deptChips = departments.map(d =>
    `<button class="chip" onclick="setP2Filter('dept-${d.dept_id}',this)">${d.dept_name}</button>`
  ).join('');
  chipsEl.innerHTML = `
    <button class="chip${p2Filter === 'all' ? ' act' : ''}" onclick="setP2Filter('all',this)">All</button>
    <button class="chip${p2Filter === 'Theory' ? ' act' : ''}" onclick="setP2Filter('Theory',this)">Theory</button>
    <button class="chip${p2Filter === 'Lab' ? ' act' : ''}" onclick="setP2Filter('Lab',this)">Lab</button>
    <button class="chip${p2Filter === 'Theory+Lab' ? ' act' : ''}" onclick="setP2Filter('Theory+Lab',this)">Theory+Lab</button>
    ${deptChips}`;

  let filtered = courses;
  if (p2Filter !== 'all') {
    if (p2Filter.startsWith('dept-')) {
      const deptId = parseInt(p2Filter.replace('dept-', ''));
      filtered = filtered.filter(c => c.dept_id == deptId);
    } else {
      filtered = filtered.filter(c => c.course_type === p2Filter);
    }
  }
  if (p2Search) {
    filtered = filtered.filter(c =>
      c.course_name.toLowerCase().includes(p2Search) ||
      c.course_code.toLowerCase().includes(p2Search)
    );
  }

  document.getElementById('p2-grid').innerHTML = filtered.map(c => `
    <div class="card">
      <div class="card-top">
        <span class="card-code">${c.course_code}</span>
        <span class="badge ${typeClass(c.course_type)}">${c.course_type}</span>
      </div>
      <div class="card-title">${c.course_name}</div>
      <div class="card-meta">${dName(c.dept_id)} · ${c.credits} credits</div>
      <div class="credit-bar"><div class="credit-fill" style="width:${c.credits/6*100}%"></div></div>
    </div>`).join('');

  document.getElementById('p2-count').textContent = `${filtered.length} courses`;
  populateSelects();
}
function filterP2(v) { p2Search = v.toLowerCase(); renderP2(); }
function setP2Filter(f, el) {
  p2Filter = f;
  document.querySelectorAll('#page-p2 .chip').forEach(b => b.className = 'chip');
  el.classList.add('act');
  renderP2();
}
async function addCourse() {
  const payload = {
    course_name: document.getElementById('p2-cn').value,
    course_code: document.getElementById('p2-cc').value,
    credits:     parseInt(document.getElementById('p2-cr').value),
    course_type: document.getElementById('p2-ct').value,
    dept_id:     parseInt(document.getElementById('p2-cd').value),
    semester_id: parseInt(document.getElementById('p2-cs').value)
  };
  const res = await fetch(`${API}?action=add_course`, {
    method: "POST", headers: {"Content-Type":"application/json"}, body: JSON.stringify(payload)
  });
  const data = await res.json();
  if (data.status === "error") { alert("Error: " + data.message); }
  else { alert("Course added successfully!"); await renderP2(); renderOverview(); }
}

// ═══════════════════════════════════════════
// P3 — DEPARTMENTS & SEMESTERS
// BUG FIX: was using local static arrays with wrong field names
// ═══════════════════════════════════════════
function renderP3() {
  document.getElementById('p3-dept-tbody').innerHTML = departments.map(d => `
    <tr>
      <td style="color:var(--ink3)">${d.dept_id}</td>
      <td><strong>${d.dept_name}</strong></td>
      <td>${courses.filter(c => c.dept_id == d.dept_id).length}</td>
      <td>${faculty.filter(f => f.dept_id == d.dept_id).length}</td>
    </tr>`).join('');

  document.getElementById('p3-sem-tbody').innerHTML = semesters.map(s => `
    <tr>
      <td style="color:var(--ink3)">${s.semester_id}</td>
      <td>Sem ${s.semester_no}</td>
      <td>${s.academic_year}</td>
      <td>${courses.filter(c => c.semester_id == s.semester_id).length}</td>
    </tr>`).join('');

  const mapping = [];
  departments.forEach(d => {
    semesters.forEach(s => {
      const cs = courses.filter(c => c.dept_id == d.dept_id && c.semester_id == s.semester_id);
      if (cs.length) mapping.push({
        dept: d.dept_name, sem: `Sem ${s.semester_no}`, year: s.academic_year,
        count: cs.length, credits: cs.reduce((a, c) => a + parseInt(c.credits), 0)
      });
    });
  });
  document.getElementById('p3-mapping-tbody').innerHTML = mapping.map(m => `
    <tr><td><strong>${m.dept}</strong></td><td>${m.sem}</td><td>${m.year}</td><td>${m.count}</td><td><strong>${m.credits}</strong></td></tr>`).join('');
}

async function addDept() {
  const name = document.getElementById('p3-dname').value.trim();
  if (!name) { alert('Enter department name.'); return; }
  const res = await fetch(`${API}?action=add_department`, {
    method: "POST", headers: {"Content-Type":"application/json"}, body: JSON.stringify({dept_name: name})
  });
  const data = await res.json();
  if (data.status === "error") { alert("Error: " + data.message); return; }
  document.getElementById('p3-dname').value = '';
  const r = await fetch(`${API}?action=get_departments`);
  departments = await r.json();
  document.getElementById('nb-p3').textContent = departments.length + semesters.length;
  renderP3();
  populateSelects(); // refresh dept dropdowns in P1/P2 forms
  // P2 chips will auto-rebuild next time P2 is visited via renderP2
}

async function addSem() {
  const no   = parseInt(document.getElementById('p3-sno').value);
  const year = document.getElementById('p3-syear').value.trim();
  if (isNaN(no) || no < 1 || no > 8 || !year) { alert('Enter valid semester number (1-8) and academic year.'); return; }
  const res = await fetch(`${API}?action=add_semester`, {
    method: "POST", headers: {"Content-Type":"application/json"}, body: JSON.stringify({semester_no: no, academic_year: year})
  });
  const data = await res.json();
  if (data.status === "error") { alert("Error: " + data.message); return; }
  document.getElementById('p3-sno').value = '';
  document.getElementById('p3-syear').value = '';
  const r = await fetch(`${API}?action=get_semesters`);
  semesters = await r.json();
  document.getElementById('nb-p3').textContent = departments.length + semesters.length;
  renderP3();
  populateSemSelect();
}

// ═══════════════════════════════════════════
// P4 — ALLOCATION
// BUG FIX: table only showed 3 columns; delete used local array; semester_id sent as text
// ═══════════════════════════════════════════
async function renderP4() {
  const res = await fetch(`${API}?action=get_allocations`);
  allocations = await res.json(); // BUG FIX: update global so P5/P6/P7 have fresh data

  // Update P4 stat cards
  const allocatedIds = new Set(allocations.map(a => parseInt(a.course_id)));
  const unallocCount = courses.filter(c => !allocatedIds.has(c.course_id)).length;
  document.getElementById('p4-total').textContent     = allocations.length;
  document.getElementById('p4-alloc').textContent     = allocatedIds.size;
  document.getElementById('p4-alloc-desc').textContent = `out of ${courses.length} courses`;
  document.getElementById('p4-unalloc').textContent   = unallocCount;
  document.getElementById('nb-p4').textContent        = allocations.length;

  document.getElementById('p4-tbody').innerHTML = allocations.map(a => `
    <tr>
      <td style="color:var(--ink3)">${a.allocation_id}</td>
      <td>${a.faculty_name}</td>
      <td>${a.course_name}</td>
      <td>${a.dept_name}</td>
      <td>Sem ${a.semester_no} ${a.academic_year}</td>
      <td>${a.assigned_hours} hrs</td>
      <td><button class="btn btn-sm btn-outline" onclick="deleteAlloc(${a.allocation_id})" style="color:var(--red);border-color:var(--red);margin-top:0">Delete</button></td>
    </tr>`).join('');

  const unalloc = courses.filter(c => !allocatedIds.has(c.course_id));
  document.getElementById('p4-unalloc-tbody').innerHTML = unalloc.length
    ? unalloc.map(c => `
        <tr>
          <td><strong>${c.course_code}</strong></td><td>${c.course_name}</td>
          <td><span class="badge ${typeClass(c.course_type)}">${c.course_type}</span></td>
          <td>${dName(c.dept_id)}</td>
        </tr>`).join('')
    : '<tr><td colspan="4" style="text-align:center;color:var(--green);padding:20px">✓ All courses allocated!</td></tr>';
}

async function deleteAlloc(id) {
  if (!confirm('Delete this allocation?')) return;
  const res = await fetch(`${API}?action=delete_allocation`, {
    method: "POST", headers: {"Content-Type":"application/json"}, body: JSON.stringify({allocation_id: id})
  });
  const data = await res.json();
  if (data.status === "error") { alert("Error: " + data.message); }
  else { await renderP4(); renderOverview(); }
}

async function addAlloc() {
  const res = await fetch(`${API}?action=add_allocation`, {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify({
      faculty_id:     parseInt(document.getElementById("p4-fac").value),
      course_id:      parseInt(document.getElementById("p4-crs").value),
      semester_id:    parseInt(document.getElementById("p4-sem").value), // BUG FIX: now numeric
      assigned_hours: parseInt(document.getElementById("p4-hrs").value)
    })
  });
  const data = await res.json();
  if (data.status === "error") { alert("Error: " + data.message); }
  else { alert("Allocation created successfully!"); await renderP4(); renderOverview(); }
}

async function suggestFaculty() {
  const res = await fetch(`${API}?action=get_workload`);
  const data = await res.json();
  let best = data[0];
  data.forEach(f => { if (f.total_hours < best.total_hours) best = f; });
  alert("Assign to: " + best.faculty_name);
}

async function smartSuggest() {
  const course_id = document.getElementById("p4-crs").value;
  if (!course_id) { alert("Please select a course first"); return; }
  const res = await fetch(`${API}?action=get_smart_suggestion`, {
    method: "POST", headers: {"Content-Type":"application/json"}, body: JSON.stringify({ course_id })
  });
  const data = await res.json();
  if (data.length > 0) { document.getElementById("p4-fac").value = data[0].faculty_id; }
  alert(`Auto-selected best faculty: ${data[0].faculty_name}`);
  document.getElementById("ai-suggestions").innerHTML = `
    <div class="card">
      <div class="sec-title" style="margin-bottom:12px;">🤖 Smart Allocation Recommendations</div>
      ${data.map((f, i) => `
        <div style="padding:12px;border:1px solid var(--border);border-radius:8px;margin-bottom:10px;background:${i===0?'var(--accent-bg)':'var(--surface)'};">
          <strong>#${i+1} ${f.faculty_name}</strong><br>
          <span style="font-size:12px;color:var(--ink2)">${f.designation} · Capacity: ${f.max_workload - f.total_hours} hrs · Score: ${f.score}</span>
        </div>`).join('')}
    </div>`;
}

// ═══════════════════════════════════════════
// POPULATE SELECTS (shared dropdowns)
// BUG FIX: semester select now populated from DB instead of hardcoded text options
// ═══════════════════════════════════════════
function populateSelects() {
  const fs  = faculty.map(f => `<option value="${f.faculty_id}">${f.faculty_name}</option>`).join('');
  const cs  = courses.map(c => `<option value="${c.course_id}">${c.course_code} — ${c.course_name}</option>`).join('');
  const ds  = departments.map(d => `<option value="${d.dept_id}">${d.dept_name}</option>`).join('');
  const ss2 = semesters.map(s => `<option value="${s.semester_id}">Sem ${s.semester_no} ${s.academic_year}</option>`).join('');
  ['p4-fac','p6-fac'].forEach(id => { const el=document.getElementById(id); if(el) el.innerHTML=fs; });
  ['p4-crs','p6-crs'].forEach(id => { const el=document.getElementById(id); if(el) el.innerHTML=cs; });
  ['p1-fdept','p2-cd'].forEach(id => { const el=document.getElementById(id); if(el) el.innerHTML=ds; });
  ['p2-cs','p4-sem'].forEach(id => { const el=document.getElementById(id); if(el) el.innerHTML=ss2; });
}
function populateSemSelect() {
  const ss = semesters.map(s => `<option value="${s.semester_id}">Sem ${s.semester_no} ${s.academic_year}</option>`).join('');
  const el = document.getElementById('p4-sem');
  if (el) el.innerHTML = ss;
}

// ═══════════════════════════════════════════
// P5 — WORKLOAD
// BUG FIX: mixed max_wl (undefined) and max_workload; dept bar used f.id instead of f.faculty_id
// ═══════════════════════════════════════════
function renderP5() {
  if (!faculty.length) return;
  const rows = faculty.map(f => {
    const hrs = allocHours(f.faculty_id);
    const max = parseInt(f.max_workload) || 1;
    const pct = Math.round((hrs / max) * 100);
    const status = hrs > max ? 'Overloaded' : hrs >= max*0.7 ? 'Normal' : hrs > 0 ? 'Under-utilized' : 'Unassigned';
    const sc    = hrs > max ? 'b-red'       : hrs >= max*0.7 ? 'b-green' : hrs > 0 ? 'b-amber'        : 'b-gray';
    return { f, hrs, max, pct, status, sc };
  });

  document.getElementById('p5-avg').textContent   = Math.round(rows.reduce((s,r)=>s+r.hrs,0)/rows.length);
  document.getElementById('p5-over').textContent  = rows.filter(r => r.hrs > r.max).length;
  document.getElementById('p5-under').textContent = rows.filter(r => r.hrs > 0 && r.hrs < 8).length;

  document.getElementById('p5-tbody').innerHTML = rows.map(r => `
    <tr>
      <td><strong>${r.f.faculty_name}</strong></td>
      <td>${r.f.dept_name}</td>
      <td>${r.hrs} hrs</td>
      <td>${r.max} hrs</td>
      <td>
        <div style="display:flex;align-items:center;gap:8px">
          <div class="wl-bar" style="width:80px">
            <div class="wl-fill" style="width:${Math.min(r.pct,100)}%;background:${r.hrs>r.max?'var(--red)':r.hrs>=r.max*0.7?'var(--green)':'var(--amber)'}"></div>
          </div>
          <span style="font-size:12px;color:var(--ink2)">${r.pct}%</span>
        </div>
      </td>
      <td><span class="badge ${r.sc}">${r.status}</span></td>
    </tr>`).join('');

  renderBars('p5-bars', rows.map(r=>({label:r.f.faculty_name.split(' ').slice(-1)[0], val:r.hrs, unit:' hrs'})), 'var(--accent)');
  const deptLoad = {};
  faculty.forEach(f => { const d=f.dept_name; deptLoad[d]=(deptLoad[d]||0)+allocHours(f.faculty_id); }); // BUG FIX: was f.id
  renderBars('p5-dept-bars', Object.entries(deptLoad).map(([label,val])=>({label,val,unit:' hrs'})), 'var(--purple)');
}

// ═══════════════════════════════════════════
// P6 — TRIGGERS
// BUG FIX: was using x.id / a.fac_id / f.max_wl — all undefined with API data
// ═══════════════════════════════════════════
function renderP6() {
  document.getElementById('p6-blocked').textContent = p6Blocked;
  populateSelects();
  renderP6Log();
}
function renderP6Log() {
  const el = document.getElementById('p6-log');
  if (!p6Log.length) { el.innerHTML='<div class="empty">No trigger events yet. Run a simulation above.</div>'; return; }
  el.innerHTML = p6Log.map(l => `
    <div class="log-item">
      <div class="log-dot" style="background:${l.color}"></div>
      <div><div class="log-title">${l.title}</div><div class="log-sub">${l.sub} · ${l.time}</div></div>
    </div>`).join('');
}
function triggerDemo() {
  const fac_id    = parseInt(document.getElementById('p6-fac').value);
  const course_id = parseInt(document.getElementById('p6-crs').value);
  const hrs       = parseInt(document.getElementById('p6-hrs').value);
  const resEl     = document.getElementById('p6-result');
  if (!fac_id || !course_id || isNaN(hrs)) { alert('Fill all fields.'); return; }

  const f       = faculty.find(x => x.faculty_id == fac_id);      // BUG FIX: was x.id
  const c       = courses.find(x => x.course_id  == course_id);   // BUG FIX: was x.id
  const current = allocHours(fac_id);
  const maxWl   = parseInt(f.max_workload);                        // BUG FIX: was f.max_wl
  resEl.style.display = 'block';

  if (allocations.find(a => a.faculty_id == fac_id && a.course_id == course_id)) { // BUG FIX: was a.fac_id
    resEl.innerHTML = `<div class="alert alert-red">⛔ Trigger 2 fired — Duplicate allocation blocked. ${f.faculty_name} is already assigned to ${c.course_name}.</div>`;
    p6Blocked++;
    p6Log.unshift({title:'Trigger 2: Duplicate blocked', sub:`${f.faculty_name} → ${c.course_name}`, color:'var(--red)', time:new Date().toLocaleTimeString()});
  } else if (current + hrs > maxWl) {
    resEl.innerHTML = `<div class="alert alert-red">⛔ Trigger 1 fired — Workload exceeded. ${f.faculty_name} has ${current} hrs, adding ${hrs} hrs would exceed max ${maxWl} hrs.</div>`;
    p6Blocked++;
    p6Log.unshift({title:'Trigger 1: Workload exceeded', sub:`${f.faculty_name} — ${current}+${hrs} > ${maxWl}`, color:'var(--red)', time:new Date().toLocaleTimeString()});
  } else {
    resEl.innerHTML = `<div class="alert alert-green">✓ Allocation valid — Triggers passed. ${f.faculty_name} can be assigned to ${c.course_name} (${hrs} hrs). Total would be ${current+hrs}/${maxWl} hrs.</div>`;
    p6Log.unshift({title:'Trigger check passed', sub:`${f.faculty_name} → ${c.course_name} (${hrs} hrs)`, color:'var(--green)', time:new Date().toLocaleTimeString()});
  }
  document.getElementById('p6-blocked').textContent = p6Blocked;
  renderP6Log();
}

// ═══════════════════════════════════════════
// P7 — REPORTS
// BUG FIX: was using f.name / f.desig / f.max_wl / a.fac_id — all wrong API field names
// ═══════════════════════════════════════════
let p7Report = 'faculty';
function renderP7() {
  // Faculty report
  document.getElementById('p7-f-tbody').innerHTML = faculty.map(f => {
    const hrs      = allocHours(f.faculty_id);
    const assigned = allocations.filter(a => a.faculty_id == f.faculty_id).length;
    const max      = parseInt(f.max_workload);
    const pct      = Math.round(hrs / max * 100);
    const status   = hrs > max ? 'Overloaded' : hrs >= max*0.7 ? 'Normal' : 'Under-utilized';
    const sc       = hrs > max ? 'b-red'      : hrs >= max*0.7 ? 'b-green' : 'b-amber';
    return `<tr><td><strong>${f.faculty_name}</strong></td><td><span class="badge b-gray">${f.designation}</span></td><td>${f.dept_name}</td><td>${assigned}</td><td>${hrs} hrs</td><td>${max} hrs</td><td><span class="badge ${sc}">${status}</span></td></tr>`;
  }).join('');

  // Course report
  document.getElementById('p7-c-tbody').innerHTML = courses.map(c => {
    const a = allocations.find(x => x.course_id == c.course_id);
    return `<tr><td><strong>${c.course_code}</strong></td><td>${c.course_name}</td><td><span class="badge ${typeClass(c.course_type)}">${c.course_type}</span></td><td>${c.credits}</td><td>${a?a.faculty_name:'<span style="color:var(--ink3)">Unassigned</span>'}</td><td style="color:var(--ink2)">${a?`Sem ${a.semester_no}`:'—'}</td><td>${a?a.assigned_hours:'—'}</td></tr>`;
  }).join('');

  // Dept summary
  const deptRows = departments.map(d => {
    const dc    = courses.filter(c => c.dept_id == d.dept_id);
    const df    = faculty.filter(f => f.dept_id == d.dept_id);
    const dAlloc= allocations.filter(a => df.some(f => f.faculty_id == a.faculty_id));
    const avgLoad = df.length ? Math.round(dAlloc.reduce((s,a)=>s+parseInt(a.assigned_hours),0)/df.length) : 0;
    return { name:d.dept_name, courses:dc.length, credits:dc.reduce((s,c)=>s+parseInt(c.credits),0), fac:df.length, avgLoad };
  });
  document.getElementById('p7-d-tbody').innerHTML = deptRows.map(r => `
    <tr><td><strong>${r.name}</strong></td><td>${r.courses}</td><td>${r.credits}</td><td>${r.fac}</td><td>${r.avgLoad} hrs</td></tr>`).join('');
  renderBars('p7-dept-bars',   deptRows.map(r=>({label:r.name, val:r.courses})),  'var(--accent)');
  renderBars('p7-credit-bars', deptRows.map(r=>({label:r.name, val:r.credits})), 'var(--purple)');

  // Unallocated
  const allocIds = new Set(allocations.map(a => parseInt(a.course_id)));
  const unalloc  = courses.filter(c => !allocIds.has(c.course_id));
  document.getElementById('p7-u-tbody').innerHTML = unalloc.length
    ? unalloc.map(c => `<tr><td><strong>${c.course_code}</strong></td><td>${c.course_name}</td><td><span class="badge ${typeClass(c.course_type)}">${c.course_type}</span></td><td>${dName(c.dept_id)}</td><td style="color:var(--ink2)">${sName(c.semester_id)}</td></tr>`).join('')
    : '<tr><td colspan="5" style="text-align:center;color:var(--green);padding:20px">✓ All courses have been allocated!</td></tr>';
}
function setP7Report(id, el) {
  p7Report = id;
  document.querySelectorAll('#page-p7 .chip').forEach(b => b.className='chip');
  el.classList.add('act');
  document.querySelectorAll('.p7-report').forEach(r => r.style.display='none');
  document.getElementById('p7-'+id).style.display='block';
}

// ═══════════════════════════════════════════
// INIT
// BUG FIX: only fetched faculty + courses; allocations/departments/semesters were never loaded
// ═══════════════════════════════════════════
async function init() {
  try {
    const [r1, r2, r3, r4, r5] = await Promise.all([
      fetch(`${API}?action=get_faculty`),
      fetch(`${API}?action=get_courses`),
      fetch(`${API}?action=get_allocations`),
      fetch(`${API}?action=get_departments`),
      fetch(`${API}?action=get_semesters`)
    ]);
    faculty     = await r1.json();
    courses     = await r2.json();
    allocations = await r3.json();
    departments = await r4.json();
    semesters   = await r5.json();

    populateSelects();
    renderOverview();  // seeds overview cards + sidebar badges

    // Seed P1 stats immediately so they show correct values before user visits P1
    document.getElementById('p1-total').textContent = faculty.length;
    document.getElementById('p1-prof').textContent  = faculty.filter(f => f.designation === 'Professor').length;
    document.getElementById('p1-asst').textContent  = faculty.filter(f => f.designation !== 'Professor').length;
    const avgWl = faculty.length ? Math.round(faculty.reduce((s,f) => s + parseInt(f.max_workload), 0) / faculty.length) : 0;
    document.getElementById('p1-wl').textContent = avgWl;

    // Seed P2 stat cards and build department chips
    document.getElementById('p2-total').textContent  = courses.length;
    document.getElementById('p2-theory').textContent = courses.filter(c => c.course_type === 'Theory').length;
    document.getElementById('p2-lab').textContent    = courses.filter(c => c.course_type === 'Lab').length;
    document.getElementById('p2-both').textContent   = courses.filter(c => c.course_type === 'Theory+Lab').length;
    document.getElementById('p2-count').textContent  = `${courses.length} courses`;
    const deptChips = departments.map(d =>
      `<button class="chip" onclick="setP2Filter('dept-${d.dept_id}',this)">${d.dept_name}</button>`
    ).join('');
    document.getElementById('p2-chips').innerHTML += deptChips;

    // Seed P4 stat cards
    const allocatedIds = new Set(allocations.map(a => parseInt(a.course_id)));
    document.getElementById('p4-total').textContent      = allocations.length;
    document.getElementById('p4-alloc').textContent      = allocatedIds.size;
    document.getElementById('p4-alloc-desc').textContent = `out of ${courses.length} courses`;
    document.getElementById('p4-unalloc').textContent    = courses.filter(c => !allocatedIds.has(c.course_id)).length;

  } catch (e) {
    console.error("Init failed — is XAMPP running?", e);
  }
}
init();

// ═══════════════════════════════════════════
// ROLE-BASED UI ADJUSTMENTS
// ═══════════════════════════════════════════
(function applyRoleUI() {
  const roleColors = {
    admin:  { bg: '#fdf0ea', color: '#b84a18', label: '⚙ Admin — Full Access' },
    editor: { bg: '#eef3ff', color: '#2a6ef5', label: '✏ Editor — Insert & Update' },
    viewer: { bg: '#e8f6f0', color: '#1d6b4e', label: '👁 Viewer — Read Only' }
  };
  const r = roleColors[USER_ROLE] || roleColors.viewer;
  const badge = document.getElementById('role-badge');
  if (badge) {
    badge.textContent = r.label;
    badge.style.background = r.bg;
    badge.style.color = r.color;
    badge.style.fontWeight = '600';
  }

  // Hide delete buttons for non-admin
  if (!CAN_DELETE) {
    document.querySelectorAll('.btn-outline').forEach(btn => {
      if (btn.textContent.trim() === 'Delete') btn.style.display = 'none';
    });
    // Also intercept delete in P4 rendered table
    window.deleteAlloc = function() {
      alert('🔒 Your role does not have permission to delete allocations.');
    };
  }
})();

</script>
</body>
</html>