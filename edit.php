<?php
require_once 'db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    try {
        // Get image name before deleting (to remove the file)
        $stmt = $pdo->prepare("SELECT image FROM `" . TABLE_PETS . "` WHERE id = ?");
        $stmt->execute([$id]);
        $pet = $stmt->fetch();

        if ($pet) {
            // Delete the image file if it exists
            if (!empty($pet['image']) && file_exists('uploads/' . $pet['image'])) {
                unlink('uploads/' . $pet['image']);
            }

            // Delete the record
            $stmt = $pdo->prepare("DELETE FROM `" . TABLE_PETS . "` WHERE id = ?");
            $stmt->execute([$id]);

            header('Location: index.php?success=deleted');
            exit;
        } else {
            header('Location: index.php');
            exit;
        }
    } catch (Exception $e) {
        die("Delete error: " . htmlspecialchars($e->getMessage()));
    }
} else {
    header('Location: index.php');
    exit;
}
?>