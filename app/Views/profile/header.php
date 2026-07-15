<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');

$isCreator = in_array($profileUser['role'], ['creator', 'admin']);
$isSalesPartner = (int)$profileUser['is_sales_partner'] === 1;
?>

<!-- Cover Photo Banner -->
<div id="profileCoverBanner" class="h-48 rounded-3xl border border-[#E0E6E2] relative overflow-hidden flex items-end p-6 shadow-inner" style="background: <?= !empty($profileUser['cover_url']) ? "url('" . Security::e($profileUser['cover_url']) . "') center/cover no-repeat" : "linear-gradient(to top right, #E7EDE8, #F3F4F1)" ?>;">
    <?php if (empty($profileUser['cover_url'])): ?>
        <div id="coverBlurBg" class="absolute right-0 top-0 w-64 h-64 bg-[#FFE500]/10 rounded-full blur-3xl pointer-events-none"></div>
    <?php endif; ?>
    <span class="px-3 py-1 bg-white/90 backdrop-blur border border-[#E0E6E2] rounded-full text-[9px] font-bold text-[#004D40] uppercase tracking-wider shadow-sm">Premium Partner space</span>

    <?php if ($isOwnProfile): ?>
        <!-- Cover Photo Upload Button -->
        <button id="changeCoverBtn" class="absolute top-4 right-4 p-2 bg-white/80 hover:bg-white backdrop-blur border border-[#E0E6E2] rounded-full text-[#004D40] hover:text-[#00332A] shadow-md transition flex items-center justify-center cursor-pointer group z-10" title="Upload Cover Banner">
            <span class="material-symbols-outlined text-sm font-bold">photo_camera</span>
            <span class="max-w-0 overflow-hidden group-hover:max-w-xs transition-all duration-300 ease-in-out text-[9px] font-extrabold uppercase tracking-wider ml-0 group-hover:ml-1">Change Banner</span>
        </button>
        <input type="file" id="directCoverInput" accept="image/png, image/jpeg, image/jpg, image/webp" class="hidden" />
    <?php endif; ?>
</div>

<!-- Profile Card details -->
<div class="liquid-glass p-6 rounded-3xl relative overflow-hidden -mt-20 shadow-md">
    <div class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-6">
        <!-- Profile Photo/Avatar with direct upload overlay -->
        <div class="relative shrink-0">
            <div class="w-24 h-24 rounded-full bg-[#004D40]/10 flex items-center justify-center text-[#004D40] text-4xl font-bold border-4 border-white overflow-hidden shadow-md" id="avatarImgContainer">
                <?php if (!empty($profileUser['avatar_url'])): ?>
                    <img src="<?= Security::e($profileUser['avatar_url']) ?>" class="w-full h-full object-cover" id="directAvatarPreviewImg"/>
                <?php else: ?>
                    <span id="directAvatarText"><?= strtoupper(substr($profileUser['username'], 0, 1)) ?></span>
                <?php endif; ?>
            </div>
            <?php if ($isOwnProfile): ?>
                <button id="changeAvatarBtn" class="absolute bottom-0 right-0 p-1.5 bg-white border border-[#E0E6E2] rounded-full text-[#004D40] hover:bg-[#F5F7F4] shadow-md transition flex items-center justify-center cursor-pointer z-10" title="Upload Profile Picture">
                    <span class="material-symbols-outlined text-xs font-extrabold">photo_camera</span>
                </button>
                <input type="file" id="directAvatarInput" accept="image/png, image/jpeg, image/jpg, image/webp" class="hidden" />
            <?php endif; ?>
        </div>

        <!-- Profile Info -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center space-x-2 flex-wrap">
                <h1 class="text-2xl font-extrabold text-[#004D40] truncate"><?= Security::e($profileUser['full_name']) ?></h1>
                <?php if ($profileUser['role'] === 'admin'): ?>
                    <span class="px-2.5 py-0.5 bg-rose-50 border border-rose-200 text-rose-800 text-[10px] font-bold uppercase rounded-full">Admin</span>
                <?php elseif ($profileUser['role'] === 'creator'): ?>
                    <span class="px-2.5 py-0.5 bg-[#004D40]/10 border border-[#004D40]/20 text-[#004D40] text-[10px] font-bold uppercase rounded-full">Creator Partner</span>
                <?php endif; ?>
                <?php if ($isSalesPartner): ?>
                    <span class="px-2.5 py-0.5 bg-[#FFE500]/20 border border-[#FFE500]/40 text-[#004D40] text-[10px] font-extrabold uppercase rounded-full">Sales Partner</span>
                <?php endif; ?>
            </div>
            <p class="text-xs text-on-surface-variant font-bold mt-1">@<?= Security::e($profileUser['username']) ?></p>
            <p class="text-xs text-on-surface-variant mt-3 whitespace-pre-line leading-relaxed font-semibold"><?= !empty($profileUser['bio']) ? Security::e($profileUser['bio']) : 'This user hasn\'t written a bio yet.' ?></p>

            <!-- Clickable Follow stats -->
            <div class="flex space-x-6 mt-4 text-xs font-bold text-[#004D40]">
                <a href="/profile/<?= Security::e($profileUser['username']) ?>/followers" class="hover:underline">
                    <span class="font-extrabold text-sm text-[#004D40]" id="followers-count"><?= $followersCount ?></span> Followers
                </a>
                <a href="/profile/<?= Security::e($profileUser['username']) ?>/following" class="hover:underline">
                    <span class="font-extrabold text-sm text-[#004D40]" id="following-count"><?= $followingCount ?></span> Following
                </a>
            </div>
        </div>

        <!-- Follow / Action Button -->
        <div class="shrink-0 flex space-x-2 pt-2 md:pt-0">
            <?php if ($currentUser && $currentUser['id'] !== $profileUser['id']): ?>
                <button id="follow-btn" data-user-id="<?= (int)$profileUser['id'] ?>" class="px-6 py-2.5 rounded-full font-bold text-sm transition-all shadow-sm <?= $isFollowing ? 'bg-[#F5F7F4] text-[#004D40] border border-[#E0E6E2] hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200' : 'bg-[#004D40] text-white hover:bg-[#00332A]' ?>">
                    <?= $isFollowing ? 'Following' : 'Follow' ?>
                </button>
                <a href="/messages/<?= Security::e($profileUser['username']) ?>" class="p-2.5 rounded-full bg-white text-[#004D40] hover:bg-[#F5F7F4] border border-[#E0E6E2] flex items-center justify-center transition-colors">
                    <span class="material-symbols-outlined font-bold">mail</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
