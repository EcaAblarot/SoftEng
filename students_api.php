<?php
// students_api.php
// Returns paginated, sortable, filterable JSON list of students.
header('Content-Type: application/json; charset=utf-8');
include 'db.php';

$defaultPerPage = 10;
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = intval($_GET['per_page'] ?? $defaultPerPage);
if ($per_page <= 0) $per_page = $defaultPerPage;
$offset = ($page - 1) * $per_page;

$q = trim($_GET['q'] ?? '');
$grade = trim($_GET['grade'] ?? '');
$section = trim($_GET['section'] ?? '');
$sort_by = $_GET['sort_by'] ?? 'id';
$allowed_sort = ['id','name','grade','section','status'];
if (!in_array($sort_by, $allowed_sort)) $sort_by = 'id';
$sort_dir = strtolower($_GET['sort_dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

// Helper: fallback sample data if no DB table exists or on error
function sample_students() {
    return [
        ['id'=>'2024-001','name'=>'Ace Toralba','grade'=>'Grade 1','section'=>'Faith','status'=>'Active'],
        ['id'=>'2024-002','name'=>'Cliff Lozada','grade'=>'Grade 2','section'=>'Hope','status'=>'Active'],
        ['id'=>'2024-003','name'=>'Antonio Chong','grade'=>'Grade 3','section'=>'Grace','status'=>'Active'],
    ];
}

// Check if students table exists
$useSample = false;
try {
    $res = $conn->query("SHOW TABLES LIKE 'students'");
    if (!$res || $res->num_rows === 0) {
        $useSample = true;
    }
} catch (Throwable $e) {
    $useSample = true;
}

if ($useSample) {
    $all = sample_students();
    // apply basic filtering, sorting, pagination in PHP
    $filtered = array_filter($all, function($r) use($q,$grade,$section){
        if ($q) {
            $qq = strtolower($q);
            if (strpos(strtolower($r['id']), $qq) === false && strpos(strtolower($r['name']), $qq) === false) return false;
        }
        if ($grade && $r['grade'] !== $grade) return false;
        if ($section && $r['section'] !== $section) return false;
        return true;
    });
    usort($filtered, function($a,$b) use($sort_by,$sort_dir){
        $av = strtolower($a[$sort_by] ?? '');
        $bv = strtolower($b[$sort_by] ?? '');
        if ($av === $bv) return 0;
        if ($sort_dir === 'ASC') return $av < $bv ? -1 : 1;
        return $av > $bv ? -1 : 1;
    });
    $total = count($filtered);
    $slice = array_slice($filtered, $offset, $per_page);
    echo json_encode(['data'=>array_values($slice),'page'=>$page,'per_page'=>$per_page,'total'=>$total]);
    exit;
}

// Build WHERE clause with prepared statements
$where = [];
$params = [];
$types = '';
if ($q !== '') {
    $where[] = "(id LIKE ? OR name LIKE ? OR CONCAT(id, ' ', name) LIKE ? )";
    $like = "%{$q}%";
    $params[] = $like; $params[] = $like; $params[] = $like; $types .= 'sss';
}
if ($grade !== '') { $where[] = 'grade = ?'; $params[] = $grade; $types .= 's'; }
if ($section !== '') { $where[] = 'section = ?'; $params[] = $section; $types .= 's'; }

$where_sql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

// count total
$count_sql = "SELECT COUNT(*) AS c FROM students $where_sql";
$stmt = $conn->prepare($count_sql);
if ($stmt === false) {
    echo json_encode(['error'=>'Server error preparing count']); exit;
}
if (count($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$cres = $stmt->get_result();
$total = ($cres && $cres->num_rows) ? intval($cres->fetch_assoc()['c']) : 0;
$stmt->close();

// data query
$sql = "SELECT id, name, grade, section, status FROM students $where_sql ORDER BY $sort_by $sort_dir LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['error'=>'Server error preparing data query']); exit;
}

// bind params + limit/offset
$bindParams = $params;
$bindTypes = $types;
$bindTypes .= 'ii';
$bindParams[] = $per_page;
$bindParams[] = $offset;
$stmt->bind_param($bindTypes, ...$bindParams);
$stmt->execute();
$res = $stmt->get_result();
$data = [];
while ($row = $res->fetch_assoc()) { $data[] = $row; }
$stmt->close();

echo json_encode(['data'=>$data,'page'=>$page,'per_page'=>$per_page,'total'=>$total]);
