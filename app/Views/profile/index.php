<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');

// Include global header
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<!-- Embed Refactored Profile Page Assets (CSS and JS isolation) -->
<style>
    <?php include __DIR__ . '/assets/profile.css'; ?>
</style>

<!-- Profile Configuration for Javascript -->
<div id="profilePageConfig"
     data-csrf-token="<?= htmlspecialchars($csrf_token) ?>"
     data-username="<?= Security::e($profileUser['username']) ?>"
     class="hidden">
</div>

<div class="space-y-8">
    <!-- 1. Header (Banner, Avatar, Badges, Follow Actions, Menu Button) -->
    <?php include __DIR__ . '/header.php'; ?>

    <!-- 2. Navigation Tabs (data-tab handlers) -->
    <?php include __DIR__ . '/tabs.php'; ?>

    <!-- 3. Section Files (Tab content containers) -->

    <!-- Posts Tab Panel -->
    <?php include __DIR__ . '/sections/posts.php'; ?>

    <!-- Products Tab Panel -->
    <?php if (in_array($profileUser['role'], ['creator', 'admin'])): ?>
        <?php include __DIR__ . '/sections/products.php'; ?>
    <?php endif; ?>

    <!-- Recommendations Tab Panel -->
    <?php if ((int)$profileUser['is_sales_partner'] === 1 || in_array($profileUser['role'], ['creator', 'admin'])): ?>
        <?php include __DIR__ . '/sections/recommendations.php'; ?>
    <?php endif; ?>

</div>

<!-- Embed ES6 Vanilla JavaScript -->
<script>
    <?php include __DIR__ . '/assets/profile.js'; ?>
</script>

<?php
// Include global footer
include ROOT_PATH . '/app/Views/partials/footer.php';
?>
