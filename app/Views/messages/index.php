<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between border-b border-white/5 pb-4">
        <h1 class="text-2xl font-bold font-geist text-white flex items-center space-x-2">
            <span class="material-symbols-outlined text-primary text-3xl">chat</span>
            <span>Direct Messages</span>
        </h1>
        <a href="/messages/new" class="px-5 py-2.5 bg-primary text-on-primary font-bold rounded-full text-xs shadow-md shadow-primary/20 flex items-center space-x-1.5">
            <span class="material-symbols-outlined text-sm">chat_add_on</span>
            <span>New Chat</span>
        </a>
    </div>

    <!-- Conversations List -->
    <div class="space-y-4">
        <?php if (empty($conversations)): ?>
            <div class="text-center py-16 bg-surface-container-low rounded-3xl border border-white/5 text-on-surface-variant">
                <span class="material-symbols-outlined text-5xl mb-3 opacity-55">forum</span>
                <h3 class="text-lg font-bold font-geist text-on-background">No active conversations</h3>
                <p class="text-xs mt-1">Start chatting with other Sales Partners and Creators now!</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($conversations as $conv): ?>
                    <a href="/messages/<?= Security::e($conv['username']) ?>" class="block p-4 bg-surface-container-low rounded-3xl border border-white/5 hover:border-primary/10 transition-all flex justify-between items-center group">
                        <div class="flex items-center space-x-4 min-w-0">
                            <!-- Avatar -->
                            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold overflow-hidden shrink-0 text-sm relative border border-white/5">
                                <?php if (!empty($conv['avatar_url'])): ?>
                                    <img src="<?= Security::e($conv['avatar_url']) ?>" class="w-full h-full object-cover"/>
                                <?php else: ?>
                                    <?= strtoupper(substr($conv['username'], 0, 1)) ?>
                                <?php endif; ?>

                                <!-- Unread dot indicator -->
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <span class="absolute top-0 right-0 w-3.5 h-3.5 bg-red-500 rounded-full border-2 border-surface flex items-center justify-center text-[8px] font-bold text-white"><?= (int)$conv['unread_count'] ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="min-w-0">
                                <h4 class="font-bold text-sm text-white group-hover:text-primary transition-colors font-geist truncate"><?= Security::e($conv['full_name']) ?></h4>
                                <p class="text-[10px] text-gray-400">@<?= Security::e($conv['username']) ?></p>
                                <p class="text-xs text-gray-400 truncate mt-1 max-w-[200px]"><?= Security::e($conv['last_message'] ?? 'Start chatting...') ?></p>
                            </div>
                        </div>

                        <!-- Date -->
                        <span class="text-[9px] text-gray-500 font-medium whitespace-nowrap shrink-0"><?= $conv['last_message_time'] ? date('M d, g:i A', strtotime($conv['last_message_time'])) : '' ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
