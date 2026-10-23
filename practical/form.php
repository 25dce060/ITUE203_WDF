<?php
$student = $student ?? [
    "first_name" => "",
    "last_name" => "",
    "email" => "",
    "phone" => "",
    "course" => "",
    "year_level" => ""
];

$errors = $errors ?? [];
$is_edit = $is_edit ?? false;
?>

<?php if (!empty($errors)): ?>
    <div class="error">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= h($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post">
    <label for="first_name">First Name</label>
    <input type="text" id="first_name" name="first_name"
           value="<?= h($student["first_name"]) ?>" required>

    <label for="last_name">Last Name</label>
    <input type="text" id="last_name" name="last_name"
           value="<?= h($student["last_name"]) ?>" required>

    <label for="email">Email</label>
    <input type="email" id="email" name="email"
           value="<?= h($student["email"]) ?>" required>

    <label for="phone">Phone</label>
    <input type="text" id="phone" name="phone"
           value="<?= h($student["phone"]) ?>" required>

    <label for="course">Course</label>
    <select id="course" name="course" required>
        <option value="">Select Course</option>
        <?php foreach (["BCA", "B.Tech", "BBA", "MCA", "MBA", "B.Sc"] as $course): ?>
            <option value="<?= h($course) ?>"
                <?= $student["course"] === $course ? "selected" : "" ?>>
                <?= h($course) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="year_level">Year</label>
    <select id="year_level" name="year_level" required>
        <option value="">Select Year</option>
        <?php foreach ([1, 2, 3, 4] as $year): ?>
            <option value="<?= $year ?>"
                <?= (string)$student["year_level"] === (string)$year ? "selected" : "" ?>>
                Year <?= $year ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">
        <?= $is_edit ? "Update Student" : "Add Student" ?>
    </button>

    <a href="index.php">Cancel</a>
</form>