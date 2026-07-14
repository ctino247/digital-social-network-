<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-6">
    <!-- Back link -->
    <a href="/admin/dashboard" class="inline-flex items-center space-x-2 text-xs font-bold text-on-surface-variant hover:text-[#004D40] transition-colors">
        <span class="material-symbols-outlined text-sm font-bold">arrow_back</span>
        <span>Back to Admin</span>
    </a>

    <!-- Applications list -->
    <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] space-y-4 shadow-sm">
        <h3 class="text-sm font-extrabold uppercase tracking-wider text-[#004D40] border-b border-[#E0E6E2] pb-2">Creator Program Applications</h3>

        <?php if (empty($applications)): ?>
            <p class="text-xs text-on-surface-variant py-4 text-center font-bold">No applications found.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-[#E0E6E2] text-on-surface-variant font-bold uppercase tracking-wider">
                            <th class="py-3">Applicant</th>
                            <th class="py-3">Portfolio URL</th>
                            <th class="py-3">Pitch Note</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E0E6E2] font-semibold text-on-background">
                        <?php foreach ($applications as $app): ?>
                            <tr class="hover:bg-[#FBFBF9]/50 transition-colors">
                                <td class="py-3.5">
                                    <p class="text-xs font-bold text-[#004D40]">@<?= Security::e($app['username']) ?></p>
                                    <p class="text-[10px] text-on-surface-variant font-bold"><?= Security::e($app['full_name']) ?></p>
                                </td>
                                <td class="py-3.5 text-xs">
                                    <?php if ($app['portfolio_url']): ?>
                                        <a href="<?= Security::e($app['portfolio_url']) ?>" target="_blank" class="text-primary hover:underline truncate block max-w-[150px] font-bold"><?= Security::e($app['portfolio_url']) ?></a>
                                    <?php else: ?>
                                        <span class="text-on-surface-variant font-normal">None</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 text-xs text-on-surface-variant leading-relaxed max-w-[200px] truncate font-medium"><?= Security::e($app['bio']) ?></td>
                                <td class="py-3.5">
                                    <span class="px-2.5 py-0.5 rounded text-[10px] font-extrabold uppercase <?= $app['status'] === 'pending' ? 'bg-amber-100 text-amber-800' : ($app['status'] === 'approved' ? 'bg-[#004D40]/10 text-[#004D40]' : 'bg-rose-100 text-rose-800') ?>">
                                        <?= Security::e($app['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 text-right space-x-2">
                                    <?php if ($app['status'] === 'pending'): ?>
                                        <form action="/admin/creator-applications/<?= (int)$app['id'] ?>/approve" method="POST" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
                                            <button type="submit" class="px-3.5 py-1.5 bg-[#004D40] text-white hover:bg-[#00332A] rounded-full text-[10px] font-bold uppercase transition-all shadow-sm">Approve</button>
                                        </form>
                                        <button type="button" onclick="rejectApp(<?= (int)$app['id'] ?>)" class="px-3.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-full text-[10px] font-bold uppercase transition-all border border-rose-200">Reject</button>
                                    <?php else: ?>
                                        <span class="text-[10px] text-on-surface-variant uppercase font-bold">Processed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Rejection form Modal logic -->
<div id="reject-modal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden flex items-center justify-center p-6">
    <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] shadow-2xl max-w-sm w-full space-y-4">
        <h3 class="text-base font-extrabold text-[#004D40]">Reject Application</h3>
        <form action="" id="reject-form" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
            <div>
                <label for="admin_note" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Reason for Rejection</label>
                <textarea id="admin_note" name="admin_note" required rows="3" class="w-full px-4 py-3 bg-[#FBFBF9] border border-[#E0E6E2] rounded-2xl text-on-background focus:border-[#004D40] outline-none text-xs leading-relaxed resize-none font-bold"></textarea>
            </div>
            <div class="flex space-x-2 justify-end">
                <button type="submit" class="px-5 py-2.5 bg-rose-600 text-white hover:bg-rose-700 font-bold rounded-full text-xs shadow-md">Submit Rejection</button>
                <button type="button" onclick="closeReject()" class="px-4 py-2.5 bg-[#F5F7F4] hover:bg-[#EBF0EC] border border-[#E0E6E2] text-on-surface-variant rounded-full text-xs font-bold">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    function rejectApp(id) {
        document.getElementById('reject-form').action = '/admin/creator-applications/' + id + '/reject';
        document.getElementById('reject-modal').classList.remove('hidden');
    }
    function closeReject() {
        document.getElementById('reject-modal').classList.add('hidden');
    }
</script>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
