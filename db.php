<?php
require_once 'db.php';

$errors = [];
$name = $species = $breed = $age = $owner_name = '';
$image_name = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name       = trim($_POST['name'] ?? '');
    $species    = trim($_POST['species'] ?? '');
    $breed      = trim($_POST['breed'] ?? '');
    $age        = trim($_POST['age'] ?? '');
    $owner_name = trim($_POST['owner_name'] ?? '');

    // Validation
    if (empty($name))       $errors[] = 'Pet name is required.';
    if (empty($species))    $errors[] = 'Species is required.';
    if (empty($breed))      $errors[] = 'Breed is required.';
    if (empty($age)) {
        $errors[] = 'Age is required.';
    } elseif (!is_numeric($age) || $age < 0) {
        $errors[] = 'Age must be a valid positive number.';
    }
    if (empty($owner_name)) $errors[] = "Owner's name is required.";

    // Image Upload
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $errors[] = 'Invalid image format. Allowed: JPG, JPEG, PNG, GIF, WEBP.';
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Image must be less than 2MB.';
        } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error uploading the image.';
        } else {
            $image_name = uniqid('pet_', true) . '.' . $ext;
        }
    }

    if (empty($errors)) {
        try {
            if ($image_name) {
                $upload_path = 'uploads/' . $image_name;
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0755, true);
                }
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    throw new Exception('Failed to save uploaded image.');
                }
            }

            $stmt = $pdo->prepare("INSERT INTO `" . TABLE_PETS . "` 
                (name, species, breed, age, owner_name, image) 
                VALUES (?, ?, ?, ?, ?, ?)");

            $stmt->execute([$name, $species, $breed, (int)$age, $owner_name, $image_name]);

            header('Location: index.php?success=created');
            exit;

        } catch (Exception $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Pet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, #e83e8c, #c2185b); }
        .card { border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border-radius: 12px; }
        .form-label { font-weight: 600; }
        .btn-submit { background: linear-gradient(135deg, #e83e8c, #c2185b); border: none; }
        .btn-submit:hover { background: linear-gradient(135deg, #c2185b, #880e4f); }
        #imagePreview { max-height: 200px; border-radius: 8px; margin-top: 10px; display: none; object-fit: cover; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark px-4 py-3 mb-4">
    <span class="navbar-brand fs-4 fw-bold">
        <i class="bi bi-heart-fill me-2"></i>Pets Information
    </span>
    <a href="index.php" class="btn btn-outline-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
</nav>

<div class="container" style="max-width: 650px;">
    <div class="card p-4">
        <h4 class="fw-bold mb-4">
            <i class="bi bi-plus-circle-fill text-danger me-2"></i>Add New Pet
        </h4>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Pet Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Buddy"
                       value="<?= htmlspecialchars($name ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Species <span class="text-danger">*</span></label>
                <select name="species" class="form-select">
                    <option value="">-- Select Species --</option>
                    <?php
                    $speciesList = ['Dog','Cat','Bird','Rabbit','Fish','Hamster','Reptile','Other'];
                    foreach ($speciesList as $s):
                    ?>
                        <option value="<?= $s ?>" <?= ($species ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Breed <span class="text-danger">*</span></label>
                <input type="text" name="breed" class="form-control" placeholder="e.g. Golden Retriever"
                       value="<?= htmlspecialchars($breed ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Age (years) <span class="text-danger">*</span></label>
                <input type="number" name="age" class="form-control" placeholder="e.g. 3" min="0"
                       value="<?= htmlspecialchars($age ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Owner's Name <span class="text-danger">*</span></label>
                <input type="text" name="owner_name" class="form-control" placeholder="e.g. Juan dela Cruz"
                       value="<?= htmlspecialchars($owner_name ?? '') ?>">
            </div>

            <div class="mb-4">
                <label class="form-label">Pet Photo (Optional)</label>
                <input type="file" name="image" id="imageInput" class="form-control" accept="image/*">
                <div class="form-text">Accepted: JPG, JPEG, PNG, GIF, WEBP (max 2MB)</div>
                <img id="imagePreview" src="#" alt="Preview">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-submit text-white px-5">
                    <i class="bi bi-save me-1"></i> Save Pet
                </button>
                <a href="index.php" class="btn btn-secondary px-5">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('imageInput').addEventListener('change', function() {
        const preview = document.getElementById('imagePreview');
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });
</script>
</body>
</html>