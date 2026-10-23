<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/functions.php";

$search = trim($_GET["search"] ?? "");
$course = trim($_GET["course"] ?? "");
$status = $_GET["status"] ?? "active";
$page = max(1, (int)($_GET["page"] ?? 1));
$per_page = 8;

if (!in_array($status, ["active", "deleted", "all"], true)) {
    $status = "active";
}

$where = [];
$params = [];
$types = "";

if ($status === "active") {
    $where[] = "is_deleted = 0";
} elseif ($status === "deleted") {
    $where[] = "is_deleted = 1";
}

if ($search !== "") {
    $where[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR course LIKE ?)";
    $term = "%" . $search . "%";
    array_push($params, $term, $term, $term, $term);
    $types .= "ssss";
}

if ($course !== "") {
    $where[] = "course = ?";
    $params[] = $course;
    $types .= "s";
}

$where_sql = $where ? " WHERE " . implode(" AND ", $where) : "";

$count_sql = "SELECT COUNT(*) AS total FROM students" . $where_sql;
$stmt = $conn->prepare($count_sql);

if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()["total"];
$total_pages = max(1, (int)ceil($total / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$sql = "SELECT id, first_name, last_name, email, phone, course,
               year_level, is_deleted
        FROM students" . $where_sql . "
        ORDER BY id ASC LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$list_params = $params;
$list_types = $types . "ii";
$list_params[] = $per_page;
$list_params[] = $offset;
$stmt->bind_param($list_types, ...$list_params);
$stmt->execute();
$students = $stmt->get_result();

$page_title = "Student Management";
require_once __DIR__ . "/header.php";
?>

<h2>Student Management</h2>

<div class="toolbar">
    <a class="button" href="add.php">+ Add Student</a>
    <a class="button" href="export_csv.php?<?= h(http_build_query($_GET)) ?>">Export CSV</a>
</div>

<form method="get" class="filter-form">
    <label for="search">Search name, email or course</label>
    <input type="text" id="search" name="search"
           value="<?= h($search) ?>" placeholder="e.g. aarav">

    <label for="course">Course</label>
    <select name="course" id="course">
        <option value="">All Courses</option>
        <?php foreach (["BCA", "B.Tech", "BBA", "MCA", "MBA", "B.Sc"] as $item): ?>
            <option value="<?= h($item) ?>"
                <?= $course === $item ? "selected" : "" ?>>
                <?= h($item) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="status">Status</label>
    <select name="status" id="status">
        <option value="active" <?= $status === "active" ? "selected" : "" ?>>Active</option>
        <option value="deleted" <?= $status === "deleted" ? "selected" : "" ?>>Deleted</option>
        <option value="all" <?= $status === "all" ? "selected" : "" ?>>All</option>
    </select>

    <button type="submit">Search</button>
    <a href="index.php">Reset</a>
</form>

<p><?= $total ?> record(s) found.</p>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Course</th>
                <th>Year</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($students->num_rows > 0): ?>
            <?php while ($student = $students->fetch_assoc()): ?>
                <tr class="<?= $student["is_deleted"] ? "deleted-row" : "" ?>">
                    <td><?= h($student["id"]) ?></td>
                    <td><?= h($student["first_name"] . " " . $student["last_name"]) ?></td>
                    <td><?= h($student["email"]) ?></td>
                    <td><?= h($student["phone"]) ?></td>
                    <td><?= h($student["course"]) ?></td>
                    <td><?= h($student["year_level"]) ?></td>
                    <td><?= $student["is_deleted"] ? "Deleted" : "Active" ?></td>
                    <td class="actions">
                        <?php if (!$student["is_deleted"]): ?>
                            <a href="edit.php?id=<?= (int)$student["id"] ?>">Edit</a>
                            <a href="delete.php?id=<?= (int)$student["id"] ?>&action=delete"
                               onclick="return confirm('Move this student to the deleted list?')">
                                Delete
                            </a>
                        <?php else: ?>
                            <a href="delete.php?id=<?= (int)$student["id"] ?>&action=restore"
                               onclick="return confirm('Restore this student?')">
                                Restore
                            </a>
                            <a href="delete.php?id=<?= (int)$student["id"] ?>&action=permanent"
                               onclick="return confirm('Permanently delete this student? This cannot be undone.')">
                                Delete Forever
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No students found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<nav class="pagination" aria-label="Pagination">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <?php
        $query = $_GET;
        $query["page"] = $i;
        ?>
        <a class="<?= $i === $page ? "current-page" : "" ?>"
           href="?<?= h(http_build_query($query)) ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>
</nav>

<?php require_once __DIR__ . "/footer.php"; ?>