<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="max-w-xl mx-auto space-y-6">
    <!-- Back Link -->
    <a href="/creator/dashboard" class="inline-flex items-center space-x-2 text-xs font-bold text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        <span>Back to Dashboard</span>
    </a>

    <!-- Form card -->
    <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 shadow-xl">
        <h2 class="text-xl font-bold font-geist text-on-background mb-4">Edit Product: <?= Security::e($product['name']) ?></h2>

        <form action="/creator/products/<?= (int)$product['id'] ?>/edit" method="POST" enctype="multipart/form-data" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Product Name</label>
                    <input type="text" id="name" name="name" value="<?= Security::e($product['name']) ?>" required class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-medium"/>
                </div>
                <div>
                    <label for="price" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Selling Price ($)</label>
                    <input type="number" step="0.01" min="0.01" id="price" name="price" value="<?= Security::e($product['price']) ?>" required class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-medium"/>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="category_id" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Category</label>
                    <select id="category_id" name="category_id" required class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-medium">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= (int)$product['category_id'] === (int)$cat['id'] ? 'selected' : '' ?>><?= Security::e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="type" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Product Asset Type</label>
                    <select id="type" name="type" required class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-medium">
                        <?php
                        $types = ['PDF', 'ZIP', 'Template', 'Course', 'Source Code', 'Software', 'E-book', 'Audio', 'Video', 'Digital Download'];
                        foreach ($types as $t):
                        ?>
                            <option value="<?= $t ?>" <?= $product['type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Product Description / Pitch</label>
                <textarea id="description" name="description" rows="5" required class="w-full p-4 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs leading-relaxed resize-none"><?= Security::e($product['description']) ?></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Update Digital File Binaries (Optional)</label>
                <input type="file" name="product_file" class="block w-full text-xs text-on-surface-variant file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 file:cursor-pointer"/>
                <p class="text-[10px] text-on-surface-variant mt-1.5">Current attached file: <span class="text-on-background font-mono text-[9px]"><?= Security::e($product['file_name']) ?></span>. Leave blank if you do not want to re-upload files.</p>
            </div>

            <div class="flex space-x-3 pt-4 border-t border-white/5">
                <button type="submit" class="flex-1 py-3.5 bg-primary text-on-primary font-bold rounded-full hover:opacity-90 transition shadow-lg shadow-primary/20 flex justify-center items-center space-x-2 text-xs">
                    <span class="material-symbols-outlined text-sm">save</span>
                    <span>Save Changes</span>
                </button>
                <a href="/creator/dashboard" class="px-6 py-3.5 bg-surface-container-high hover:bg-white/5 text-on-surface-variant hover:text-on-background text-xs font-bold rounded-full transition flex items-center justify-center">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
