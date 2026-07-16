(function() {
    'use strict';

    // Cache common DOM selectors and config
    let config = null;

    function init() {
        const configEl = document.getElementById('profilePageConfig');
        if (!configEl) return;

        config = {
            csrfToken: configEl.dataset.csrfToken,
            username: configEl.dataset.username
        };

        initTabs();
        initFollow();
        initUploads();
        initPostInteractions();
        initProfileMenu();
    }

    // Safe initialization check
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    /**
     * 1. Brand New Tab System (Simplified for Posts, Products, Recommendations)
     */
    function initTabs() {
        const container = document.getElementById('profileTabsContainer');
        if (!container) return;

        // Active tab styling classes
        const activeClasses = ['border-[#004D40]', 'text-[#004D40]'];
        const inactiveClasses = ['border-transparent', 'text-on-surface-variant'];

        function switchTab(tabId, updateUrl = true) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));

            // Show target tab content if exists
            const targetContent = document.getElementById('content-' + tabId);
            if (targetContent) {
                targetContent.classList.remove('hidden');
            }

            // Update tab button classes
            const tabButtons = container.querySelectorAll('[data-tab]');
            tabButtons.forEach(btn => {
                if (btn.dataset.tab === tabId) {
                    btn.classList.add(...activeClasses);
                    btn.classList.remove(...inactiveClasses);
                    btn.classList.remove('border-transparent', 'text-on-surface-variant');
                } else {
                    btn.classList.remove(...activeClasses);
                    btn.classList.add(...inactiveClasses);
                }
            });

            // Update Browser URL state
            if (updateUrl) {
                const newUrl = window.location.pathname + '?tab=' + tabId;
                window.history.pushState({ tab: tabId }, '', newUrl);
            }
        }

        // Event delegation on tabs container
        container.addEventListener('click', (e) => {
            const button = e.target.closest('[data-tab]');
            if (!button) return;

            e.preventDefault();
            const tabId = button.dataset.tab;
            switchTab(tabId, true);
        });

        // Parse initial URL tab parameter or default to 'posts'
        const urlParams = new URLSearchParams(window.location.search);
        let activeTab = urlParams.get('tab') || 'posts';

        // Check if tab button actually exists on the DOM for this role
        if (!container.querySelector(`[data-tab="${activeTab}"]`)) {
            activeTab = 'posts';
        }

        switchTab(activeTab, false);

        // Listen for history popstate events (Back & Forward buttons)
        window.addEventListener('popstate', (e) => {
            const stateTab = (e.state && e.state.tab) ? e.state.tab : 'posts';
            if (container.querySelector(`[data-tab="${stateTab}"]`)) {
                switchTab(stateTab, false);
            }
        });
    }

    /**
     * 2. Profile Menu Dropdown Toggle Handler
     */
    function initProfileMenu() {
        const trigger = document.getElementById('profileMenuTriggerBtn');
        const panel = document.getElementById('profileMenuPanel');
        const container = document.getElementById('profileMenuDropdownContainer');

        if (!trigger || !panel) return;

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            panel.classList.toggle('hidden');
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (container && !container.contains(e.target)) {
                panel.classList.add('hidden');
            }
        });
    }

    /**
     * 3. Reusable AJAX Image Upload from Header Camera buttons
     */
    function initUploads() {
        // Direct Header uploads
        const coverInput = document.getElementById('directCoverInput');
        const changeCoverBtn = document.getElementById('changeCoverBtn');
        const avatarInput = document.getElementById('directAvatarInput');
        const changeAvatarBtn = document.getElementById('changeAvatarBtn');

        if (changeCoverBtn && coverInput) {
            changeCoverBtn.addEventListener('click', () => coverInput.click());
        }
        if (changeAvatarBtn && avatarInput) {
            changeAvatarBtn.addEventListener('click', () => avatarInput.click());
        }

        // Handlers for direct profile page header clicks
        if (coverInput) {
            coverInput.addEventListener('change', () => {
                uploadImageFile(coverInput.files[0], 'cover', (url) => {
                    const banner = document.getElementById('profileCoverBanner');
                    if (banner) {
                        banner.style.background = `url('${url}') center/cover no-repeat`;
                    }
                    const blurBg = document.getElementById('coverBlurBg');
                    if (blurBg) blurBg.remove();
                });
            });
        }

        if (avatarInput) {
            avatarInput.addEventListener('change', () => {
                uploadImageFile(avatarInput.files[0], 'avatar', (url) => {
                    const container = document.getElementById('avatarImgContainer');
                    if (container) {
                        container.innerHTML = `<img src="${url}" class="w-full h-full object-cover" id="directAvatarPreviewImg"/>`;
                    }
                    // Update header/nav avatars if any
                    const navAvatar = document.getElementById('navbarAvatarImg');
                    if (navAvatar) navAvatar.src = url;
                });
            });
        }
    }

    /**
     * Performs standard size/type validation and fires the AJAX endpoint
     */
    async function uploadImageFile(file, type, successCallback) {
        if (!file) return;

        const allowedMimes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
        if (!allowedMimes.includes(file.type)) {
            alert('Invalid image format. Please select a PNG, JPG, JPEG, or WEBP image.');
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            alert('The selected image is too large. Maximum file size allowed is 2MB.');
            return;
        }

        // Show loading state
        document.body.style.cursor = 'wait';

        const formData = new FormData();
        formData.append('csrf_token', config.csrfToken);
        formData.append(type, file);

        const url = type === 'cover' ? '/profile/update-cover-ajax' : '/profile/update-avatar-ajax';

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': config.csrfToken
                },
                body: formData
            });

            const data = await res.json();
            document.body.style.cursor = 'default';

            if (data.success) {
                if (successCallback) successCallback(data.url);
                showToast(`Successfully updated ${type} photo!`);
            } else {
                alert(data.error || 'An error occurred during upload.');
            }
        } catch (e) {
            document.body.style.cursor = 'default';
            console.error(e);
            alert('Upload failed. Please try again.');
        }
    }

    /**
     * 4. Follow / Unfollow Toggles
     */
    function initFollow() {
        const followBtn = document.getElementById('follow-btn');
        if (!followBtn) return;

        followBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            const userId = followBtn.dataset.userId;
            if (!userId) return;

            try {
                const res = await fetch(`/profile/${userId}/follow`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': config.csrfToken
                    },
                    body: 'csrf_token=' + encodeURIComponent(config.csrfToken)
                });
                const data = await res.json();

                if (data.success) {
                    if (data.action === 'followed') {
                        followBtn.innerText = 'Following';
                        followBtn.className = "px-6 py-2.5 rounded-full font-bold text-sm transition-all shadow-sm bg-[#F5F7F4] text-[#004D40] border border-[#E0E6E2] hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200";
                    } else {
                        followBtn.innerText = 'Follow';
                        followBtn.className = "px-6 py-2.5 rounded-full font-bold text-sm transition-all shadow-sm bg-[#004D40] text-white hover:bg-[#00332A]";
                    }
                    const followersSpan = document.getElementById('followers-count');
                    if (followersSpan) {
                        followersSpan.innerText = data.followersCount;
                    }
                } else {
                    alert(data.error || 'An error occurred.');
                }
            } catch (err) {
                console.error(err);
            }
        });
    }

    /**
     * 5. Likes & Bookmarks using event delegation
     */
    function initPostInteractions() {
        document.body.addEventListener('click', async (e) => {
            // Find if a button with data-action is clicked
            const btn = e.target.closest('[data-action]');
            if (!btn) return;

            const action = btn.dataset.action;
            const postId = btn.dataset.postId;
            if (!postId) return;

            e.preventDefault();

            if (action === 'like') {
                try {
                    const res = await fetch(`/post/${postId}/like`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-Token': config.csrfToken
                        },
                        body: 'csrf_token=' + encodeURIComponent(config.csrfToken)
                    });
                    const data = await res.json();
                    if (data.success) {
                        const countSpan = btn.querySelector('.like-count') || btn.querySelector('span:last-child');
                        const iconSpan = btn.querySelector('span:first-child');
                        if (countSpan) countSpan.innerText = data.likesCount;

                        if (data.action === 'liked') {
                            btn.classList.add('text-[#004D40]');
                            if (iconSpan) iconSpan.style.fontVariationSettings = "'FILL' 1";
                        } else {
                            btn.classList.remove('text-[#004D40]');
                            if (iconSpan) iconSpan.style.fontVariationSettings = "'FILL' 0";
                        }
                    }
                } catch (err) {
                    console.error(err);
                }
            } else if (action === 'bookmark') {
                try {
                    const res = await fetch(`/post/${postId}/bookmark`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-Token': config.csrfToken
                        },
                        body: 'csrf_token=' + encodeURIComponent(config.csrfToken)
                    });
                    const data = await res.json();
                    if (data.success) {
                        const iconSpan = btn.querySelector('span:first-child');
                        if (data.action === 'bookmarked') {
                            btn.classList.add('text-[#004D40]');
                            if (iconSpan) iconSpan.style.fontVariationSettings = "'FILL' 1";
                        } else {
                            btn.classList.remove('text-[#004D40]');
                            if (iconSpan) iconSpan.style.fontVariationSettings = "'FILL' 0";
                        }
                    }
                } catch (err) {
                    console.error(err);
                }
            }
        });
    }

    /**
     * Custom Toast Message Helper
     */
    function showToast(message) {
        let toast = document.getElementById('profileToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'profileToast';
            toast.className = 'fixed bottom-5 right-5 z-50 px-5 py-3 bg-[#004D40] text-white text-xs font-bold rounded-2xl shadow-xl flex items-center space-x-2 transition-all opacity-0 translate-y-2 duration-300';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<span class="material-symbols-outlined text-sm text-[#FFE500]">check_circle</span><span>${message}</span>`;
        toast.classList.remove('opacity-0', 'translate-y-2');
        toast.classList.add('opacity-100', 'translate-y-0');

        setTimeout(() => {
            toast.classList.remove('opacity-100', 'translate-y-0');
            toast.classList.add('opacity-0', 'translate-y-2');
        }, 3000);
    }

})();
