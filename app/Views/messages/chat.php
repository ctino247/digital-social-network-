<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-4 max-w-xl mx-auto flex flex-col h-[75vh]">
    <!-- Conversation Header -->
    <div class="bg-surface-container-low p-4 rounded-3xl border border-white/5 flex items-center justify-between shadow-md shrink-0">
        <div class="flex items-center space-x-3 min-w-0">
            <a href="/messages" class="text-on-surface-variant hover:text-primary transition-colors shrink-0">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>

            <a href="/profile/<?= Security::e($targetUser['username']) ?>" class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold overflow-hidden shrink-0 text-sm">
                <?php if (!empty($targetUser['avatar_url'])): ?>
                    <img src="<?= Security::e($targetUser['avatar_url']) ?>" class="w-full h-full object-cover"/>
                <?php else: ?>
                    <?= strtoupper(substr($targetUser['username'], 0, 1)) ?>
                <?php endif; ?>
            </a>
            <div class="min-w-0">
                <a href="/profile/<?= Security::e($targetUser['username']) ?>" class="font-bold text-xs truncate text-white block hover:text-primary transition-colors font-geist"><?= Security::e($targetUser['full_name']) ?></a>
                <p class="text-[9px] text-gray-400">@<?= Security::e($targetUser['username']) ?> • <span class="uppercase text-primary font-bold text-[8px]"><?= Security::e($targetUser['role']) ?></span></p>
            </div>
        </div>

        <span class="text-xs px-2.5 py-1 bg-primary/10 text-primary font-bold rounded-full border border-primary/20">CHAT SECURED</span>
    </div>

    <!-- Scrollable Messages Area -->
    <div class="flex-1 bg-surface-container-low/50 rounded-3xl border border-white/5 p-5 overflow-y-auto space-y-4 shadow-inner" id="chat-messages-container">
        <?php if (empty($chatMessages)): ?>
            <div class="text-center py-12 text-on-surface-variant text-xs">
                <p>No messages in this chat yet.</p>
                <p class="text-[10px] mt-1 text-gray-500">Say hello to start the conversation!</p>
            </div>
        <?php else: ?>
            <?php foreach ($chatMessages as $msg): ?>
                <?php $isOwn = ((int)$msg['sender_id'] === (int)$currentUser['id']); ?>
                <div class="flex <?= $isOwn ? 'justify-end' : 'justify-start' ?>">
                    <div class="max-w-[70%] p-3.5 rounded-2xl text-xs font-medium leading-relaxed <?= $isOwn ? 'bg-primary text-on-primary rounded-tr-none' : 'bg-surface-container-high text-white rounded-tl-none border border-white/5' ?>">
                        <p class="whitespace-pre-line"><?= Security::e($msg['content']) ?></p>
                        <span class="block text-[8px] mt-1.5 text-right <?= $isOwn ? 'text-on-primary/60' : 'text-gray-400' ?>"><?= date('g:i A', strtotime($msg['created_at'])) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Message Sender Form -->
    <div class="bg-surface-container-low p-4 rounded-3xl border border-white/5 shadow-md shrink-0">
        <form action="/messages/send" method="POST" class="flex gap-3" id="message-send-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
            <input type="hidden" name="receiver_id" value="<?= (int)$targetUser['id'] ?>"/>

            <input type="text" autocomplete="off" required name="content" id="message-input-text" placeholder="Type your secure message..." class="flex-1 px-4 py-3 bg-background border border-white/10 rounded-2xl text-xs text-white focus:border-primary outline-none transition-all"/>

            <button type="submit" class="p-3 bg-primary text-on-primary font-bold rounded-2xl hover:opacity-90 shadow-sm shrink-0 flex items-center justify-center">
                <span class="material-symbols-outlined text-sm">send</span>
            </button>
        </form>
    </div>
</div>

<script>
    // Scroll chat messages to bottom on load
    const container = document.getElementById('chat-messages-container');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }

    // Real-time Message polling updates (highly reactive!)
    const targetUsername = '<?= Security::e($targetUser['username']) ?>';
    setInterval(async () => {
        try {
            const res = await fetch('/api/messages/' + targetUsername + '/updates');
            const data = await res.json();
            if (data.success && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    const bubble = document.createElement('div');
                    bubble.className = "flex justify-start";
                    bubble.innerHTML = `
                        <div class="max-w-[70%] p-3.5 rounded-2xl text-xs font-medium leading-relaxed bg-surface-container-high text-white rounded-tl-none border border-white/5">
                            <p class="whitespace-pre-line">${escapeHTML(msg.content)}</p>
                            <span class="block text-[8px] mt-1.5 text-right text-gray-400">${formatTime(msg.created_at)}</span>
                        </div>
                    `;
                    container.appendChild(bubble);
                });
                container.scrollTop = container.scrollHeight;
            }
        } catch (e) {
            console.error('Failed to poll chat updates:', e);
        }
    }, 3000); // Poll every 3 seconds

    function escapeHTML(str) {
        return str.replace(/[&<>'"]/g,
            tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag] || tag)
        );
    }

    function formatTime(dateStr) {
        const d = new Date(dateStr);
        let hours = d.getHours();
        let minutes = d.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        return hours + ':' + minutes + ' ' + ampm;
    }
</script>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
