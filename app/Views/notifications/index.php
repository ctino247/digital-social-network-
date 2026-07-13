<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-6 max-w-2xl mx-auto">
    <div class="flex items-center justify-between border-b border-white/5 pb-4">
        <h1 class="text-2xl font-bold font-geist text-white flex items-center space-x-2">
            <span class="material-symbols-outlined text-primary text-3xl">notifications</span>
            <span>Notifications Feed</span>
        </h1>

        <?php if (!empty($notifications)): ?>
            <form action="/notifications/read-all" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
                <button type="submit" class="px-5 py-2.5 bg-surface-container-low hover:bg-white/5 border border-white/10 text-gray-300 font-bold rounded-full text-xs shadow-sm transition-all flex items-center space-x-1">
                    <span class="material-symbols-outlined text-sm">done_all</span>
                    <span>Mark all as Read</span>
                </button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Notifications List -->
    <div class="space-y-4">
        <?php if (empty($notifications)): ?>
            <div class="text-center py-16 bg-surface-container-low rounded-3xl border border-white/5 text-on-surface-variant">
                <span class="material-symbols-outlined text-5xl mb-3 opacity-55">notifications_none</span>
                <h3 class="text-lg font-bold font-geist text-on-background">Your feed is clean</h3>
                <p class="text-xs mt-1">We will alert you when you receive new follows, comments, DMs, or affiliate commissions!</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($notifications as $n): ?>
                    <div class="p-4 rounded-3xl border flex items-center justify-between gap-4 transition-all <?= $n['is_read'] ? 'bg-surface-container-low/40 border-white/5 opacity-70' : 'bg-surface-container-low border-primary/20 shadow-md' ?>">
                        <div class="flex items-center space-x-3.5 min-w-0">
                            <!-- Type Icon -->
                            <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center text-primary shrink-0 relative">
                                <?php if ($n['type'] === 'follow'): ?>
                                    <span class="material-symbols-outlined text-lg">person_add</span>
                                <?php elseif ($n['type'] === 'commission'): ?>
                                    <span class="material-symbols-outlined text-lg text-green-400">payments</span>
                                <?php elseif ($n['type'] === 'sale'): ?>
                                    <span class="material-symbols-outlined text-lg text-yellow-500">local_mall</span>
                                <?php elseif ($n['type'] === 'message'): ?>
                                    <span class="material-symbols-outlined text-lg">mail</span>
                                <?php else: ?>
                                    <span class="material-symbols-outlined text-lg">info</span>
                                <?php endif; ?>
                            </div>

                            <div class="min-w-0 text-xs">
                                <p class="text-white leading-relaxed font-semibold"><?= Security::e($n['content']) ?></p>
                                <span class="text-[9px] text-gray-500 font-medium uppercase tracking-wider block mt-1"><?= date('M d, Y h:i A', strtotime($n['created_at'])) ?></span>
                            </div>
                        </div>

                        <!-- Read Status Badge -->
                        <?php if (!$n['is_read']): ?>
                            <span class="w-2.5 h-2.5 bg-primary rounded-full shrink-0 shadow-[0_0_10px_rgba(16,185,129,0.5)]"></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
