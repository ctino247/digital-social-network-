<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');

// Include global header
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Back to Profile Link -->
    <a href="/profile/<?= Security::e($profileUser['username']) ?>" class="inline-flex items-center space-x-2 text-xs font-bold text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-sm font-bold">arrow_back</span>
        <span>Back to @<?= Security::e($profileUser['username']) ?>'s Profile</span>
    </a>

    <div class="bg-white p-6 md:p-8 rounded-3xl border border-[#E0E6E2] shadow-sm">
        <h1 class="text-2xl font-extrabold text-[#004D40] mb-2">Join the Creator Program</h1>
        <p class="text-xs text-on-surface-variant font-bold leading-relaxed mb-8">Unleash your digital commercial potential. Sell PDF books, ZIP software, custom templates, courses, source code, and video downloads directly to our vibrant, affiliate-powered community!</p>

        <?php if ($creatorApplication): ?>
            <div class="p-5 rounded-2xl border mb-8 <?= $creatorApplication['status'] === 'pending' ? 'bg-amber-50 border-amber-200 text-amber-800' : ($creatorApplication['status'] === 'approved' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800') ?>">
                <div class="flex items-center space-x-2 font-bold text-sm mb-1">
                    <span class="material-symbols-outlined">info</span>
                    <span>Application Status: <?= ucfirst($creatorApplication['status']) ?></span>
                </div>
                <p class="text-xs opacity-80 pl-7">Submitted on <?= date('M d, Y', strtotime($creatorApplication['created_at'])) ?></p>
                <?php if (!empty($creatorApplication['admin_note'])): ?>
                    <p class="text-xs font-semibold pl-7 mt-2">Admin Review Note: "<?= Security::e($creatorApplication['admin_note']) ?>"</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form action="/profile/apply-creator" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>

            <div>
                <label for="creator_bio" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">What type of products do you plan to sell?</label>
                <textarea id="creator_bio" name="creator_bio" rows="4" placeholder="Briefly describe your digital products (e.g. templates, software, e-books)..." required class="w-full px-4 py-3 bg-[#FBFBF9] border border-[#E0E6E2] rounded-2xl text-on-background focus:border-[#004D40] focus:ring-1 focus:ring-[#004D40] outline-none text-xs leading-relaxed font-bold shadow-sm"></textarea>
            </div>

            <div>
                <label for="portfolio_url" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Portfolio or Website URL (Optional)</label>
                <input type="url" id="portfolio_url" name="portfolio_url" placeholder="https://example.com" class="w-full px-4 py-3 bg-[#FBFBF9] border border-[#E0E6E2] rounded-2xl text-on-background focus:border-[#004D40] focus:ring-1 focus:ring-[#004D40] outline-none text-xs font-bold shadow-sm"/>
            </div>

            <div class="border-t border-[#E0E6E2] pt-6 flex justify-end">
                <button type="submit" class="px-8 py-3.5 bg-[#004D40] text-white font-bold rounded-full hover:bg-[#00332A] transition-all text-xs shadow-md">
                    Submit Creator Application
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Include global footer
include ROOT_PATH . '/app/Views/partials/footer.php';
?>
