<?php
use App\Core\Security;
?>
<?php if ($isOwnProfile && $profileUser['role'] === 'member'): ?>
    <div id="content-apply-creator" class="tab-content space-y-6 hidden">
        <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] shadow-sm">
            <h3 class="text-lg font-bold text-[#004D40] mb-2">Join the Creator Program</h3>
            <p class="text-xs text-on-surface-variant font-semibold leading-relaxed mb-6">Unleash your digital commercial potential. Sell PDF books, ZIP software, custom templates, courses, source code, and video downloads directly to our vibrant, affiliate-powered community!</p>

            <?php if ($creatorApplication): ?>
                <div class="p-4 rounded-2xl border mb-6 <?= $creatorApplication['status'] === 'pending' ? 'bg-amber-50 border-amber-200 text-amber-800' : ($creatorApplication['status'] === 'approved' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800') ?>">
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

            <form action="/profile/apply-creator" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>

                <div>
                    <label for="creator_bio" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">What type of products will you upload?</label>
                    <textarea id="creator_bio" name="creator_bio" rows="4" placeholder="Briefly describe your digital products (e.g. templates, software, e-books)..." required class="w-full px-4 py-3 bg-white border border-[#E0E6E2] rounded-2xl text-on-background focus:border-[#004D40] focus:ring-1 focus:ring-[#004D40] outline-none text-xs leading-relaxed font-bold shadow-sm"></textarea>
                </div>

                <div>
                    <label for="portfolio_url" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Portfolio or Website URL (Optional)</label>
                    <input type="url" id="portfolio_url" name="portfolio_url" placeholder="https://example.com" class="w-full px-4 py-3 bg-white border border-[#E0E6E2] rounded-2xl text-on-background focus:border-[#004D40] focus:ring-1 focus:ring-[#004D40] outline-none text-xs font-bold shadow-sm"/>
                </div>

                <button type="submit" class="px-6 py-3 bg-[#004D40] text-white font-bold rounded-full hover:bg-[#00332A] transition-all text-xs shadow-md">
                    Submit Creator Application
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>
