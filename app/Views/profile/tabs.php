<?php
$isCreator = in_array($profileUser['role'], ['creator', 'admin']);
$isSalesPartner = (int)$profileUser['is_sales_partner'] === 1;
?>

<!-- Tab Selection -->
<div id="profileTabsContainer" class="border-b border-[#E0E6E2] flex space-x-2 overflow-x-auto hide-scrollbar pb-1">
    <button data-tab="posts" id="tab-posts" class="px-5 py-3 border-b-2 font-extrabold text-xs transition-all focus:outline-none border-[#004D40] text-[#004D40] whitespace-nowrap uppercase tracking-wider">
        Posts
    </button>
    <?php if ($isCreator): ?>
        <button data-tab="products" id="tab-products" class="px-5 py-3 border-b-2 font-extrabold text-xs transition-all focus:outline-none border-transparent text-on-surface-variant hover:text-[#004D40] whitespace-nowrap uppercase tracking-wider">
            Products
        </button>
    <?php endif; ?>
    <?php if ($isSalesPartner || $isCreator): ?>
        <button data-tab="recommendations" id="tab-recommendations" class="px-5 py-3 border-b-2 font-extrabold text-xs transition-all focus:outline-none border-transparent text-on-surface-variant hover:text-[#004D40] whitespace-nowrap uppercase tracking-wider">
            Recommendations
        </button>
    <?php endif; ?>
    <?php if ($isCreator): ?>
        <button data-tab="about" id="tab-about" class="px-5 py-3 border-b-2 font-extrabold text-xs transition-all focus:outline-none border-transparent text-on-surface-variant hover:text-[#004D40] whitespace-nowrap uppercase tracking-wider">
            About Creator
        </button>
    <?php endif; ?>
    <button data-tab="followers-tab" id="tab-followers-tab" class="px-5 py-3 border-b-2 font-extrabold text-xs transition-all focus:outline-none border-transparent text-on-surface-variant hover:text-[#004D40] whitespace-nowrap uppercase tracking-wider">
        Followers
    </button>
    <button data-tab="following-tab" id="tab-following-tab" class="px-5 py-3 border-b-2 font-extrabold text-xs transition-all focus:outline-none border-transparent text-on-surface-variant hover:text-[#004D40] whitespace-nowrap uppercase tracking-wider">
        Following
    </button>

    <?php if ($isOwnProfile): ?>
        <button data-tab="wallet" id="tab-wallet" class="px-5 py-3 border-b-2 font-extrabold text-xs transition-all focus:outline-none border-transparent text-on-surface-variant hover:text-[#004D40] whitespace-nowrap uppercase tracking-wider">
            Wallet
        </button>
        <?php if ($profileUser['role'] === 'member'): ?>
            <button data-tab="apply-creator" id="tab-apply-creator" class="px-5 py-3 border-b-2 font-extrabold text-xs transition-all focus:outline-none border-transparent text-on-surface-variant hover:text-[#004D40] whitespace-nowrap uppercase tracking-wider">
                Apply Creator
            </button>
        <?php endif; ?>
        <button data-tab="settings" id="tab-settings" class="px-5 py-3 border-b-2 font-extrabold text-xs transition-all focus:outline-none border-transparent text-on-surface-variant hover:text-[#004D40] whitespace-nowrap uppercase tracking-wider">
            Settings
        </button>
    <?php endif; ?>
</div>
