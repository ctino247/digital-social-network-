<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-6">
    <!-- Back Link -->
    <a href="/admin/dashboard" class="inline-flex items-center space-x-2 text-xs font-bold text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        <span>Back to Admin</span>
    </a>

    <!-- Main edit settings panel form -->
    <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 shadow-xl">
        <h2 class="text-xl font-bold font-geist text-on-background mb-6">System Configuration & Commission Rates</h2>

        <form action="/admin/settings" method="POST" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>

            <!-- General Platform details -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">General Branding & Toggles</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="platform_name" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Platform Name</label>
                        <input type="text" id="platform_name" name="platform_name" value="<?= Security::e($settings['platform_name'] ?? 'Mimshack') ?>" required class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-semibold"/>
                    </div>
                    <div>
                        <label for="platform_brand_color" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Accent Color (Hex)</label>
                        <input type="text" id="platform_brand_color" name="platform_brand_color" value="<?= Security::e($settings['platform_brand_color'] ?? '#10B981') ?>" required class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-semibold"/>
                    </div>
                    <div>
                        <label for="maintenance_mode" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Maintenance Mode</label>
                        <select id="maintenance_mode" name="maintenance_mode" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-semibold">
                            <option value="0" <?= ($settings['maintenance_mode'] ?? '0') === '0' ? 'selected' : '' ?>>Offline (Normal Operations)</option>
                            <option value="1" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'selected' : '' ?>>Online (Maintenance Banner Active)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Multi-level referral commission percentages -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">Multi-Level Commission percentages (%)</h3>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Level 1 (Direct)</label>
                        <input type="number" step="0.1" name="level_1_percent" value="<?= (float)($commissions['level_1_percent'] ?? 10.00) ?>" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-semibold"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Level 2</label>
                        <input type="number" step="0.1" name="level_2_percent" value="<?= (float)($commissions['level_2_percent'] ?? 5.00) ?>" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-semibold"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Level 3</label>
                        <input type="number" step="0.1" name="level_3_percent" value="<?= (float)($commissions['level_3_percent'] ?? 3.00) ?>" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-semibold"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Level 4</label>
                        <input type="number" step="0.1" name="level_4_percent" value="<?= (float)($commissions['level_4_percent'] ?? 2.00) ?>" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-semibold"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Level 5</label>
                        <input type="number" step="0.1" name="level_5_percent" value="<?= (float)($commissions['level_5_percent'] ?? 1.00) ?>" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-semibold"/>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Platform Fee (%)</label>
                        <input type="number" step="0.1" name="platform_fee_percent" value="<?= (float)($commissions['platform_fee_percent'] ?? 5.00) ?>" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-semibold"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Attribution Window (Days)</label>
                        <input type="number" name="attribution_window_days" value="<?= (int)($commissions['attribution_window_days'] ?? 30) ?>" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-semibold"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Min Payout limit ($)</label>
                        <input type="number" step="0.1" name="min_withdrawal_amount" value="<?= (float)($commissions['min_withdrawal_amount'] ?? 50.00) ?>" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-semibold"/>
                    </div>
                </div>
            </div>

            <!-- SMTP Details Configuration -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">SMTP Mailer Settings</h3>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="sm:col-span-2">
                        <label for="smtp_host" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">SMTP Server Host</label>
                        <input type="text" id="smtp_host" name="smtp_host" value="<?= Security::e($settings['smtp_host'] ?? '') ?>" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-semibold"/>
                    </div>
                    <div>
                        <label for="smtp_port" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">SMTP Port</label>
                        <input type="text" id="smtp_port" name="smtp_port" value="<?= Security::e($settings['smtp_port'] ?? '') ?>" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-semibold"/>
                    </div>
                    <div>
                        <label for="smtp_secure" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Encryption type</label>
                        <select id="smtp_secure" name="smtp_secure" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-semibold">
                            <option value="none" <?= ($settings['smtp_secure'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                            <option value="ssl" <?= ($settings['smtp_secure'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                            <option value="tls" <?= ($settings['smtp_secure'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="smtp_user" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">SMTP User</label>
                        <input type="text" id="smtp_user" name="smtp_user" value="<?= Security::e($settings['smtp_user'] ?? '') ?>" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-semibold"/>
                    </div>
                    <div>
                        <label for="smtp_pass" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">SMTP Password</label>
                        <input type="password" id="smtp_pass" name="smtp_pass" value="<?= Security::e($settings['smtp_pass'] ?? '') ?>" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-semibold"/>
                    </div>
                </div>
            </div>

            <!-- SEO Configuration -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">SEO Configurations</h3>
                <div class="space-y-3">
                    <div>
                        <label for="seo_title" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">SEO Meta Title</label>
                        <input type="text" id="seo_title" name="seo_title" value="<?= Security::e($settings['seo_title'] ?? '') ?>" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-semibold"/>
                    </div>
                    <div>
                        <label for="seo_description" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">SEO Meta Description</label>
                        <textarea id="seo_description" name="seo_description" rows="3" class="w-full p-4 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs leading-relaxed resize-none"><?= Security::e($settings['seo_description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Form submission Buttons -->
            <div class="flex space-x-3 pt-4 border-t border-white/5">
                <button type="submit" class="flex-1 py-3.5 bg-primary text-on-primary font-bold rounded-full hover:opacity-90 transition shadow-lg shadow-primary/20 flex justify-center items-center space-x-2 text-xs">
                    <span class="material-symbols-outlined text-sm">save</span>
                    <span>Save System Settings</span>
                </button>
                <a href="/admin/dashboard" class="px-6 py-3.5 bg-surface-container-high hover:bg-white/5 text-on-surface-variant hover:text-on-background text-xs font-bold rounded-full transition flex items-center justify-center">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
