<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-8">
    <!-- Profile Card -->
    <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 relative overflow-hidden">
        <!-- Accent Glow background -->
        <div class="absolute top-0 right-0 w-48 h-48 bg-primary/5 rounded-full blur-3xl -mr-12 -mt-12 pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-6">
            <!-- Avatar -->
            <div class="w-20 h-20 rounded-full bg-primary-container/20 flex items-center justify-center text-primary text-3xl font-bold border-2 border-primary/20 shrink-0 overflow-hidden">
                <?php if (!empty($profileUser['avatar_url'])): ?>
                    <img src="<?= Security::e($profileUser['avatar_url']) ?>" class="w-full h-full object-cover"/>
                <?php else: ?>
                    <?= strtoupper(substr($profileUser['username'], 0, 1)) ?>
                <?php endif; ?>
            </div>

            <!-- Profile Info -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2 flex-wrap">
                    <h1 class="text-2xl font-bold font-geist truncate text-on-background"><?= Security::e($profileUser['full_name']) ?></h1>
                    <?php if ($profileUser['role'] === 'admin'): ?>
                        <span class="px-2.5 py-0.5 bg-red-500/10 text-red-400 border border-red-500/10 text-[10px] font-bold uppercase rounded-full">Admin</span>
                    <?php elseif ($profileUser['role'] === 'creator'): ?>
                        <span class="px-2.5 py-0.5 bg-primary/10 text-primary border border-primary/10 text-[10px] font-bold uppercase rounded-full">Creator</span>
                    <?php endif; ?>
                    <?php if ($profileUser['is_sales_partner']): ?>
                        <span class="px-2.5 py-0.5 bg-blue-500/10 text-blue-400 border border-blue-500/10 text-[10px] font-bold uppercase rounded-full">Partner</span>
                    <?php endif; ?>
                </div>
                <p class="text-sm text-on-surface-variant font-medium mt-0.5">@<?= Security::e($profileUser['username']) ?></p>
                <p class="text-sm text-on-surface/85 mt-3 whitespace-pre-line leading-relaxed"><?= !empty($profileUser['bio']) ? Security::e($profileUser['bio']) : 'This user hasn\'t written a bio yet.' ?></p>

                <!-- Follow stats -->
                <div class="flex space-x-6 mt-4 text-xs font-semibold text-on-surface-variant">
                    <div>
                        <span class="text-on-background font-bold text-sm" id="followers-count"><?= $followersCount ?></span> Followers
                    </div>
                    <div>
                        <span class="text-on-background font-bold text-sm"><?= $followingCount ?></span> Following
                    </div>
                </div>
            </div>

            <!-- Follow / Action Button -->
            <div class="shrink-0 flex space-x-2 pt-2 md:pt-0">
                <?php if ($currentUser && $currentUser['id'] !== $profileUser['id']): ?>
                    <button onclick="toggleFollow(<?= (int)$profileUser['id'] ?>)" id="follow-btn" class="px-6 py-2.5 rounded-full font-bold text-sm transition-all shadow-md <?= $isFollowing ? 'bg-surface-container-high text-on-background border border-white/5 hover:bg-red-500/10 hover:text-red-400' : 'bg-primary text-on-primary hover:opacity-90' ?>">
                        <?= $isFollowing ? 'Following' : 'Follow' ?>
                    </button>
                    <a href="/messages/<?= Security::e($profileUser['username']) ?>" class="p-2.5 rounded-full bg-surface-container-high text-on-surface-variant hover:text-primary transition-colors border border-white/5 flex items-center justify-center">
                        <span class="material-symbols-outlined">mail</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tab Selection -->
    <div class="border-b border-white/5 flex space-x-2 overflow-x-auto hide-scrollbar">
        <button onclick="switchTab('posts')" id="tab-posts" class="px-5 py-3 border-b-2 font-bold text-sm transition-all focus:outline-none border-primary text-primary">Posts</button>
        <?php if (in_array($profileUser['role'], ['creator', 'admin'])): ?>
            <button onclick="switchTab('products')" id="tab-products" class="px-5 py-3 border-b-2 font-bold text-sm transition-all focus:outline-none border-transparent text-on-surface-variant hover:text-primary">Products</button>
        <?php endif; ?>
        <?php if ($isOwnProfile): ?>
            <button onclick="switchTab('wallet')" id="tab-wallet" class="px-5 py-3 border-b-2 font-bold text-sm transition-all focus:outline-none border-transparent text-on-surface-variant hover:text-primary">Wallet & Earnings</button>
            <?php if ($profileUser['role'] === 'member'): ?>
                <button onclick="switchTab('apply-creator')" id="tab-apply-creator" class="px-5 py-3 border-b-2 font-bold text-sm transition-all focus:outline-none border-transparent text-on-surface-variant hover:text-primary">Apply Creator</button>
            <?php endif; ?>
            <button onclick="switchTab('settings')" id="tab-settings" class="px-5 py-3 border-b-2 font-bold text-sm transition-all focus:outline-none border-transparent text-on-surface-variant hover:text-primary">Settings</button>
        <?php endif; ?>
    </div>

    <!-- Tab Contents -->

    <!-- Posts Tab -->
    <div id="content-posts" class="tab-content space-y-4">
        <?php if (empty($posts)): ?>
            <div class="text-center py-12 bg-surface-container-low rounded-3xl border border-white/5 text-on-surface-variant">
                <span class="material-symbols-outlined text-4xl mb-2 opacity-55">article</span>
                <p class="text-sm">No posts yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <article class="bg-surface-container-low p-5 rounded-3xl border border-white/5 hover:border-white/10 transition-colors">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-primary-container/10 flex items-center justify-center text-primary font-bold overflow-hidden">
                            <?php if (!empty($post['avatar_url'])): ?>
                                <img src="<?= Security::e($post['avatar_url']) ?>" class="w-full h-full object-cover"/>
                            <?php else: ?>
                                <?= strtoupper(substr($post['username'], 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold font-geist">@<?= Security::e($post['username']) ?></h4>
                            <p class="text-[10px] text-on-surface-variant font-semibold uppercase"><?= date('M d, Y h:i A', strtotime($post['created_at'])) ?></p>
                        </div>
                    </div>
                    <p class="text-sm text-on-surface leading-relaxed mb-4 whitespace-pre-line"><?= Security::e($post['content']) ?></p>

                    <!-- Action Bar -->
                    <div class="flex items-center space-x-6 text-xs font-semibold text-on-surface-variant">
                        <button onclick="toggleLike(<?= (int)$post['id'] ?>, this)" class="flex items-center space-x-1.5 <?= $post['is_liked'] ? 'text-primary' : 'hover:text-primary' ?> transition-colors">
                            <span class="material-symbols-outlined text-lg" style="<?= $post['is_liked'] ? "font-variation-settings: 'FILL' 1;" : "" ?>">favorite</span>
                            <span><?= (int)$post['likes_count'] ?></span>
                        </button>
                        <a href="/post/<?= (int)$post['id'] ?>" class="flex items-center space-x-1.5 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-lg">chat_bubble</span>
                            <span><?= (int)$post['replies_count'] ?></span>
                        </a>
                        <button onclick="toggleBookmark(<?= (int)$post['id'] ?>, this)" class="flex items-center space-x-1.5 <?= $post['is_bookmarked'] ? 'text-primary' : 'hover:text-primary' ?> transition-colors">
                            <span class="material-symbols-outlined text-lg" style="<?= $post['is_bookmarked'] ? "font-variation-settings: 'FILL' 1;" : "" ?>">bookmark</span>
                            <span>Bookmark</span>
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Products Tab -->
    <?php if (in_array($profileUser['role'], ['creator', 'admin'])): ?>
        <div id="content-products" class="tab-content space-y-4 hidden">
            <?php if (empty($products)): ?>
                <div class="text-center py-12 bg-surface-container-low rounded-3xl border border-white/5 text-on-surface-variant">
                    <span class="material-symbols-outlined text-4xl mb-2 opacity-55">shopping_bag</span>
                    <p class="text-sm">No digital products uploaded yet.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <?php foreach ($products as $product): ?>
                        <div class="bg-surface-container-low rounded-3xl border border-white/5 overflow-hidden hover:border-primary/20 transition-all flex flex-col group">
                            <!-- Showcase Accent Box -->
                            <div class="h-32 bg-gradient-to-tr from-primary/10 to-primary/20 flex items-center justify-center text-primary relative">
                                <span class="material-symbols-outlined text-5xl">deployed_code</span>
                                <span class="absolute top-3 right-3 px-2.5 py-0.5 bg-surface/80 rounded-full text-[10px] font-bold text-primary border border-primary/20"><?= Security::e($product['type']) ?></span>
                            </div>
                            <div class="p-5 flex-1 flex flex-col justify-between">
                                <div>
                                    <h4 class="font-bold text-base text-on-background line-clamp-1 mb-1 font-geist"><?= Security::e($product['name']) ?></h4>
                                    <p class="text-xs text-on-surface-variant line-clamp-2 leading-relaxed mb-4"><?= Security::e(strip_tags($product['description'])) ?></p>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-white/5">
                                    <span class="text-lg font-bold text-primary font-geist">$<?= number_format($product['price'], 2) ?></span>
                                    <a href="/product/<?= Security::e($product['slug']) ?>" class="px-4 py-2 bg-primary/10 hover:bg-primary text-primary hover:text-on-primary text-xs font-bold rounded-full transition-all">View Detail</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Wallet & Earnings Tab -->
    <?php if ($isOwnProfile): ?>
        <div id="content-wallet" class="tab-content space-y-6 hidden">
            <!-- Wallet Dashboard Info cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-primary/5 p-6 rounded-3xl border border-primary/20 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-primary font-bold uppercase tracking-wider">Available Balance</p>
                        <h2 class="text-3xl font-bold font-geist text-primary mt-1">$<?= number_format($wallet['balance'] ?? 0.00, 2) ?></h2>
                    </div>
                    <span class="material-symbols-outlined text-4xl text-primary/55">account_balance_wallet</span>
                </div>
                <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-on-surface-variant font-bold uppercase tracking-wider">Pending Royalties/Commission</p>
                        <h2 class="text-3xl font-bold font-geist text-on-background mt-1">$<?= number_format($wallet['pending_balance'] ?? 0.00, 2) ?></h2>
                    </div>
                    <span class="material-symbols-outlined text-4xl text-on-surface-variant/55">hourglass_empty</span>
                </div>
            </div>

            <!-- Withdrawal Form -->
            <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5">
                <h3 class="text-lg font-bold font-geist text-on-background mb-4">Request Earnings Withdrawal</h3>
                <form action="/profile/withdraw" method="POST" id="withdrawal-form" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="withdraw_amount" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Amount ($)</label>
                            <input type="number" step="0.01" min="1" max="<?= $wallet['balance'] ?? 0 ?>" name="amount" id="withdraw_amount" required class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm"/>
                        </div>
                        <div>
                            <label for="destination" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Payout Destination (PayPal, Bank, etc.)</label>
                            <input type="text" name="destination" id="destination" placeholder="e.g. PayPal: payments@example.com" required class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm"/>
                        </div>
                    </div>
                    <button type="button" onclick="submitWithdrawal()" class="px-6 py-3 bg-primary text-on-primary font-bold rounded-full hover:opacity-90 transition-opacity text-sm shadow-md flex items-center space-x-2">
                        <span class="material-symbols-outlined text-lg">local_atm</span>
                        <span>Submit Request</span>
                    </button>
                </form>
            </div>

            <!-- Transaction ledger -->
            <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5">
                <h3 class="text-lg font-bold font-geist text-on-background mb-4">Transaction ledger</h3>
                <?php if (empty($transactions)): ?>
                    <p class="text-sm text-on-surface-variant">No transactions recorded yet.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr class="border-b border-white/5 text-on-surface-variant text-xs font-semibold uppercase">
                                    <th class="py-3">Type</th>
                                    <th class="py-3">Description</th>
                                    <th class="py-3">Amount</th>
                                    <th class="py-3">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 font-medium">
                                <?php foreach ($transactions as $tx): ?>
                                    <tr>
                                        <td class="py-3">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase <?= in_array($tx['type'], ['sale', 'commission', 'bonus']) ? 'bg-primary/10 text-primary' : 'bg-red-500/10 text-red-400' ?>">
                                                <?= Security::e($tx['type']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3 text-on-background text-xs"><?= Security::e($tx['description']) ?></td>
                                        <td class="py-3 text-sm font-bold font-geist <?= in_array($tx['type'], ['sale', 'commission', 'bonus']) ? 'text-primary' : 'text-red-400' ?>">
                                            <?= in_array($tx['type'], ['sale', 'commission', 'bonus']) ? '+' : '-' ?>$<?= number_format(abs($tx['amount']), 2) ?>
                                        </td>
                                        <td class="py-3 text-xs text-on-surface-variant"><?= date('M d, Y', strtotime($tx['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Apply Creator Tab -->
    <?php if ($isOwnProfile && $profileUser['role'] === 'member'): ?>
        <div id="content-apply-creator" class="tab-content space-y-6 hidden">
            <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5">
                <h3 class="text-lg font-bold font-geist text-on-background mb-2">Join the Creator Program</h3>
                <p class="text-sm text-on-surface-variant leading-relaxed mb-6">Unleash your digital commercial potential. Sell PDF books, ZIP software, custom templates, courses, source code, and video downloads directly to our vibrant, affiliate-powered community!</p>

                <?php if ($creatorApplication): ?>
                    <div class="p-4 rounded-2xl border mb-6 <?= $creatorApplication['status'] === 'pending' ? 'bg-yellow-500/10 border-yellow-500/20 text-yellow-400' : ($creatorApplication['status'] === 'approved' ? 'bg-primary/10 border-primary/20 text-primary' : 'bg-red-500/10 border-red-500/20 text-red-400') ?>">
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
                        <textarea id="creator_bio" name="creator_bio" rows="4" placeholder="Briefly describe your digital products (e.g. templates, software, e-books)..." required class="w-full px-4 py-3 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm leading-relaxed"></textarea>
                    </div>

                    <div>
                        <label for="portfolio_url" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Portfolio or Website URL (Optional)</label>
                        <input type="url" id="portfolio_url" name="portfolio_url" placeholder="https://example.com" class="w-full px-4 py-3 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm"/>
                    </div>

                    <button type="submit" class="px-6 py-3.5 bg-primary text-on-primary font-bold rounded-full hover:opacity-90 transition-all text-sm shadow-md">
                        Submit Creator Application
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Settings Tab -->
    <?php if ($isOwnProfile): ?>
        <div id="content-settings" class="tab-content space-y-6 hidden">
            <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5">
                <h3 class="text-lg font-bold font-geist text-on-background mb-4">Edit Profile Settings</h3>
                <form action="/profile/update" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>

                    <div>
                        <label for="edit_full_name" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Full Name</label>
                        <input type="text" id="edit_full_name" name="full_name" value="<?= Security::e($profileUser['full_name']) ?>" required class="w-full px-4 py-3 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm"/>
                    </div>

                    <div>
                        <label for="edit_bio" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Bio</label>
                        <textarea id="edit_bio" name="bio" rows="4" class="w-full px-4 py-3 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm leading-relaxed"><?= Security::e($profileUser['bio']) ?></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Profile Picture (Avatar)</label>
                        <input type="file" name="avatar" accept="image/*" class="block w-full text-xs text-on-surface-variant file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary-container/10 file:text-primary hover:file:bg-primary-container/20 file:cursor-pointer"/>
                        <p class="text-[10px] text-on-surface-variant mt-1.5">PNG, JPG, JPEG, WEBP are accepted.</p>
                    </div>

                    <button type="submit" class="px-6 py-3 bg-primary text-on-primary font-bold rounded-full hover:opacity-90 transition-all text-sm shadow-md">
                        Save Profile Changes
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- Ajax follow, likes, bookmarks handling script -->
<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('content-' + tabId).classList.remove('hidden');

        // Reset tab styling
        document.querySelectorAll("[id^='tab-']").forEach(btn => {
            btn.classList.remove('border-primary', 'text-primary');
            btn.classList.add('border-transparent', 'text-on-surface-variant');
        });

        // Add active styling
        const activeBtn = document.getElementById('tab-' + tabId);
        activeBtn.classList.add('border-primary', 'text-primary');
        activeBtn.classList.remove('border-transparent', 'text-on-surface-variant');
    }

    async function toggleFollow(userId) {
        const token = '<?= $csrf_token ?>';
        try {
            const res = await fetch('/profile/' + userId + '/follow', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': token
                },
                body: 'csrf_token=' + encodeURIComponent(token)
            });
            const data = await res.json();
            if (data.success) {
                const btn = document.getElementById('follow-btn');
                if (data.action === 'followed') {
                    btn.innerText = 'Following';
                    btn.classList.remove('bg-primary', 'text-on-primary');
                    btn.classList.add('bg-surface-container-high', 'text-on-background', 'border', 'border-white/5');
                } else {
                    btn.innerText = 'Follow';
                    btn.classList.add('bg-primary', 'text-on-primary');
                    btn.classList.remove('bg-surface-container-high', 'text-on-background', 'border', 'border-white/5');
                }
                document.getElementById('followers-count').innerText = data.followersCount;
            } else {
                alert(data.error || 'An error occurred.');
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function toggleLike(postId, btn) {
        const token = '<?= $csrf_token ?>';
        try {
            const res = await fetch('/post/' + postId + '/like', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': token },
                body: 'csrf_token=' + encodeURIComponent(token)
            });
            const data = await res.json();
            if (data.success) {
                const countSpan = btn.querySelector('span:last-child');
                const iconSpan = btn.querySelector('span:first-child');
                countSpan.innerText = data.likesCount;
                if (data.action === 'liked') {
                    btn.classList.add('text-primary');
                    iconSpan.style.fontVariationSettings = "'FILL' 1";
                } else {
                    btn.classList.remove('text-primary');
                    iconSpan.style.fontVariationSettings = "'FILL' 0";
                }
            }
        } catch (e) { console.error(e); }
    }

    async function toggleBookmark(postId, btn) {
        const token = '<?= $csrf_token ?>';
        try {
            const res = await fetch('/post/' + postId + '/bookmark', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': token },
                body: 'csrf_token=' + encodeURIComponent(token)
            });
            const data = await res.json();
            if (data.success) {
                const iconSpan = btn.querySelector('span:first-child');
                if (data.action === 'bookmarked') {
                    btn.classList.add('text-primary');
                    iconSpan.style.fontVariationSettings = "'FILL' 1";
                } else {
                    btn.classList.remove('text-primary');
                    iconSpan.style.fontVariationSettings = "'FILL' 0";
                }
            }
        } catch (e) { console.error(e); }
    }

    function submitWithdrawal() {
        const amount = parseFloat(document.getElementById('withdraw_amount').value);
        const destination = document.getElementById('destination').value.trim();
        const available = parseFloat('<?= $wallet['balance'] ?? 0 ?>');

        if (isNaN(amount) || amount <= 0) {
            alert('Please enter a valid amount.');
            return;
        }
        if (amount > available) {
            alert('You cannot request more than your available balance.');
            return;
        }
        if (!destination) {
            alert('Please provide your payout details.');
            return;
        }

        const form = document.getElementById('withdrawal-form');
        form.action = '/profile/withdraw';
        form.submit();
    }
</script>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
