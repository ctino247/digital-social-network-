<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');

// Include global header
include ROOT_PATH . '/app/Views/partials/header.php';

$social = json_decode($profileUser['social_links'] ?? '', true) ?: [];
$emailPref = json_decode($profileUser['email_preferences'] ?? '', true) ?: [];
$notifyPref = json_decode($profileUser['notification_settings'] ?? '', true) ?: [];
$privacyPref = json_decode($profileUser['privacy_settings'] ?? '', true) ?: [];
?>

<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Back to Profile Link -->
    <a href="/profile/<?= Security::e($profileUser['username']) ?>" class="inline-flex items-center space-x-2 text-xs font-bold text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-sm font-bold">arrow_back</span>
        <span>Back to @<?= Security::e($profileUser['username']) ?>'s Profile</span>
    </a>

    <div class="bg-white p-6 md:p-8 rounded-3xl border border-[#E0E6E2] shadow-sm">
        <h1 class="text-2xl font-extrabold text-[#004D40] mb-2">Edit Profile & Settings</h1>
        <p class="text-xs text-on-surface-variant font-bold mb-8">Manage your public identity, visual branding, social networks, and system notification preferences.</p>

        <form id="settingsForm" action="/profile/update" method="POST" enctype="multipart/form-data" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>

            <!-- Client-side error feedback banner -->
            <div id="clientErrorBanner" class="hidden p-4 bg-red-50 border border-red-200 text-red-800 text-xs font-bold rounded-2xl flex items-center space-x-2">
                <span class="material-symbols-outlined text-sm">error</span>
                <span id="clientErrorMessage"></span>
            </div>

            <!-- 1. Basic Profile Details -->
            <div class="space-y-4">
                <h4 class="text-xs font-extrabold uppercase tracking-widest text-[#004D40] opacity-75 border-b border-[#E0E6E2] pb-1.5">Basic Details</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_full_name" class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">Full Name</label>
                        <input type="text" id="edit_full_name" name="full_name" value="<?= Security::e($profileUser['full_name']) ?>" required class="w-full px-4 py-3 bg-[#FBFBF9] border border-[#E0E6E2] rounded-2xl text-on-background focus:border-[#004D40] focus:ring-1 focus:ring-[#004D40] outline-none text-xs font-bold shadow-sm"/>
                    </div>
                    <div>
                        <label for="edit_occupation" class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">Occupation</label>
                        <input type="text" id="edit_occupation" name="occupation" value="<?= Security::e($profileUser['occupation'] ?? '') ?>" placeholder="e.g. Digital Creator" class="w-full px-4 py-3 bg-[#FBFBF9] border border-[#E0E6E2] rounded-2xl text-on-background focus:border-[#004D40] focus:ring-1 focus:ring-[#004D40] outline-none text-xs font-bold shadow-sm"/>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_website" class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">Website URL</label>
                        <input type="url" id="edit_website" name="website" value="<?= Security::e($profileUser['website'] ?? '') ?>" placeholder="https://yourwebsite.com" class="w-full px-4 py-3 bg-[#FBFBF9] border border-[#E0E6E2] rounded-2xl text-on-background focus:border-[#004D40] focus:ring-1 focus:ring-[#004D40] outline-none text-xs font-bold shadow-sm"/>
                    </div>
                    <div>
                        <label for="edit_country" class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">Country</label>
                        <input type="text" id="edit_country" name="country" value="<?= Security::e($profileUser['country'] ?? '') ?>" placeholder="e.g. United States" class="w-full px-4 py-3 bg-[#FBFBF9] border border-[#E0E6E2] rounded-2xl text-on-background focus:border-[#004D40] focus:ring-1 focus:ring-[#004D40] outline-none text-xs font-bold shadow-sm"/>
                    </div>
                </div>

                <div>
                    <label for="edit_bio" class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">Bio</label>
                    <textarea id="edit_bio" name="bio" rows="3" class="w-full px-4 py-3 bg-[#FBFBF9] border border-[#E0E6E2] rounded-2xl text-on-background focus:border-[#004D40] focus:ring-1 focus:ring-[#004D40] outline-none text-xs leading-relaxed font-bold shadow-sm"><?= Security::e($profileUser['bio'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- 2. Visuals & Preview Container -->
            <div class="space-y-4">
                <h4 class="text-xs font-extrabold uppercase tracking-widest text-[#004D40] opacity-75 border-b border-[#E0E6E2] pb-1.5">Visual Branding</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Avatar Upload & Live Preview -->
                    <div class="p-4 bg-[#FBFBF9] rounded-2xl border border-[#E0E6E2] flex flex-col items-center">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-3 text-center">Avatar Photo (Max 2MB)</label>
                        <div class="w-20 h-20 rounded-full border-2 border-[#004D40]/20 overflow-hidden mb-3 shadow-inner bg-white flex items-center justify-center">
                            <img id="avatarPreview" src="<?= !empty($profileUser['avatar_url']) ? Security::e($profileUser['avatar_url']) : 'data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22 viewBox=%220 0 100 100%22><rect width=%22100%25%22 height=%22100%25%22 fill=%22%23004D40%22 opacity=%220.1%22/><text x=%2250%25%22 y=%2255%25%22 font-size=%2240%22 font-family=%22sans-serif%22 font-weight=%22bold%22 fill=%22%23004D40%22 text-anchor=%22middle%22>' . strtoupper(substr($profileUser['username'], 0, 1)) . '</text></svg>' ?>" class="w-full h-full object-cover" />
                        </div>
                        <input type="file" id="avatarInput" name="avatar" accept="image/png, image/jpeg, image/jpg, image/webp" class="block w-full text-xs text-on-surface-variant file:mr-3 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-bold file:bg-[#004D40]/10 file:text-[#004D40] hover:file:bg-[#004D40]/20 file:cursor-pointer"/>
                    </div>

                    <!-- Cover Photo Upload & Live Preview -->
                    <div class="p-4 bg-[#FBFBF9] rounded-2xl border border-[#E0E6E2] flex flex-col items-center">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-3 text-center">Cover Banner (Max 2MB)</label>
                        <div class="w-full h-20 rounded-xl border border-[#E0E6E2] overflow-hidden mb-3 shadow-inner bg-white relative flex items-center justify-center">
                            <img id="coverPreview" src="<?= !empty($profileUser['cover_url']) ? Security::e($profileUser['cover_url']) : 'data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22100%22 viewBox=%220 0 400 100%22><rect width=%22100%25%22 height=%22100%25%22 fill=%22%23004D40%22 opacity=%220.05%22/><text x=%2250%25%22 y=%2255%25%22 font-size=%2216%22 font-family=%22sans-serif%22 font-weight=%22bold%22 fill=%22%23004D40%22 opacity=%220.3%22 text-anchor=%22middle%22>No Cover Banner</text></svg>' ?>" class="w-full h-full object-cover" />
                        </div>
                        <input type="file" id="coverInput" name="cover" accept="image/png, image/jpeg, image/jpg, image/webp" class="block w-full text-xs text-on-surface-variant file:mr-3 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-bold file:bg-[#004D40]/10 file:text-[#004D40] hover:file:bg-[#004D40]/20 file:cursor-pointer"/>
                    </div>
                </div>
            </div>

            <!-- 3. Social Media Links -->
            <div class="space-y-4">
                <h4 class="text-xs font-extrabold uppercase tracking-widest text-[#004D40] opacity-75 border-b border-[#E0E6E2] pb-1.5">Social Profiles</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="social_twitter" class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">X / Twitter Username</label>
                        <input type="text" id="social_twitter" name="social_twitter" value="<?= Security::e($social['twitter'] ?? '') ?>" placeholder="username" class="w-full px-4 py-3 bg-[#FBFBF9] border border-[#E0E6E2] rounded-2xl text-on-background focus:border-[#004D40] focus:ring-1 focus:ring-[#004D40] outline-none text-xs font-bold shadow-sm"/>
                    </div>
                    <div>
                        <label for="social_instagram" class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">Instagram Username</label>
                        <input type="text" id="social_instagram" name="social_instagram" value="<?= Security::e($social['instagram'] ?? '') ?>" placeholder="username" class="w-full px-4 py-3 bg-[#FBFBF9] border border-[#E0E6E2] rounded-2xl text-on-background focus:border-[#004D40] focus:ring-1 focus:ring-[#004D40] outline-none text-xs font-bold shadow-sm"/>
                    </div>
                    <div>
                        <label for="social_linkedin" class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">LinkedIn Profile Link</label>
                        <input type="text" id="social_linkedin" name="social_linkedin" value="<?= Security::e($social['linkedin'] ?? '') ?>" placeholder="https://linkedin.com/in/username" class="w-full px-4 py-3 bg-[#FBFBF9] border border-[#E0E6E2] rounded-2xl text-on-background focus:border-[#004D40] focus:ring-1 focus:ring-[#004D40] outline-none text-xs font-bold shadow-sm"/>
                    </div>
                </div>
            </div>

            <!-- 4. Account Notifications & Preferences -->
            <div class="space-y-4">
                <h4 class="text-xs font-extrabold uppercase tracking-widest text-[#004D40] opacity-75 border-b border-[#E0E6E2] pb-1.5">Preferences & Privacy</h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Email Preferences -->
                    <div class="p-5 bg-[#FBFBF9] rounded-2xl border border-[#E0E6E2]">
                        <h5 class="text-[10px] font-extrabold text-[#004D40] uppercase tracking-wider mb-3">Email Preferences</h5>
                        <div class="space-y-2">
                            <label class="flex items-center space-x-2 text-xs font-bold text-on-surface-variant cursor-pointer">
                                <input type="checkbox" name="email_pref_marketing" value="1" <?= ($emailPref['marketing'] ?? 0) ? 'checked' : '' ?> class="w-4 h-4 text-primary bg-white border border-[#E0E6E2] rounded focus:ring-0" />
                                <span>Marketing & Updates</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs font-bold text-on-surface-variant cursor-pointer">
                                <input type="checkbox" name="email_pref_security" value="1" <?= ($emailPref['security'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 text-primary bg-white border border-[#E0E6E2] rounded focus:ring-0" />
                                <span>Security Alerts</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs font-bold text-on-surface-variant cursor-pointer">
                                <input type="checkbox" name="email_pref_transactions" value="1" <?= ($emailPref['transactions'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 text-primary bg-white border border-[#E0E6E2] rounded focus:ring-0" />
                                <span>Order Transactions</span>
                            </label>
                        </div>
                    </div>

                    <!-- In-App Notification Preferences -->
                    <div class="p-5 bg-[#FBFBF9] rounded-2xl border border-[#E0E6E2]">
                        <h5 class="text-[10px] font-extrabold text-[#004D40] uppercase tracking-wider mb-3">In-App Notifications</h5>
                        <div class="space-y-2">
                            <label class="flex items-center space-x-2 text-xs font-bold text-on-surface-variant cursor-pointer">
                                <input type="checkbox" name="notify_pref_likes" value="1" <?= ($notifyPref['likes'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 text-primary bg-white border border-[#E0E6E2] rounded focus:ring-0" />
                                <span>Likes on posts</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs font-bold text-on-surface-variant cursor-pointer">
                                <input type="checkbox" name="notify_pref_comments" value="1" <?= ($notifyPref['comments'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 text-primary bg-white border border-[#E0E6E2] rounded focus:ring-0" />
                                <span>Replies on posts</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs font-bold text-on-surface-variant cursor-pointer">
                                <input type="checkbox" name="notify_pref_messages" value="1" <?= ($notifyPref['messages'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 text-primary bg-white border border-[#E0E6E2] rounded focus:ring-0" />
                                <span>Direct Messages</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs font-bold text-on-surface-variant cursor-pointer">
                                <input type="checkbox" name="notify_pref_sales" value="1" <?= ($notifyPref['sales'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 text-primary bg-white border border-[#E0E6E2] rounded focus:ring-0" />
                                <span>Creator Sales alerts</span>
                            </label>
                        </div>
                    </div>

                    <!-- Privacy Preferences -->
                    <div class="p-5 bg-[#FBFBF9] rounded-2xl border border-[#E0E6E2]">
                        <h5 class="text-[10px] font-extrabold text-[#004D40] uppercase tracking-wider mb-3">Privacy Preferences</h5>
                        <div class="space-y-2">
                            <label class="flex items-center space-x-2 text-xs font-bold text-on-surface-variant cursor-pointer">
                                <input type="checkbox" name="privacy_search_visible" value="1" <?= ($privacyPref['search_visible'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 text-primary bg-white border border-[#E0E6E2] rounded focus:ring-0" />
                                <span>Search engine visibility</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs font-bold text-on-surface-variant cursor-pointer">
                                <input type="checkbox" name="privacy_show_earnings" value="1" <?= ($privacyPref['show_earnings'] ?? 0) ? 'checked' : '' ?> class="w-4 h-4 text-primary bg-white border border-[#E0E6E2] rounded focus:ring-0" />
                                <span>Display total sales publicly</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Security & Change Password -->
            <div class="space-y-4">
                <h4 class="text-xs font-extrabold uppercase tracking-widest text-rose-700 border-b border-rose-100 pb-1.5">Security & Password</h4>
                <div class="p-5 bg-rose-50/50 rounded-2xl border border-rose-100 space-y-4">
                    <p class="text-[10px] font-semibold text-rose-800 leading-relaxed">Leave password fields blank if you do not wish to change your password.</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="old_password" class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">Current Password</label>
                            <input type="password" id="old_password" name="old_password" class="w-full px-4 py-3 bg-white border border-[#E0E6E2] rounded-2xl focus:border-rose-300 outline-none text-xs font-bold shadow-sm"/>
                        </div>
                        <div>
                            <label for="new_password" class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="w-full px-4 py-3 bg-white border border-[#E0E6E2] rounded-2xl focus:border-rose-300 outline-none text-xs font-bold shadow-sm"/>
                        </div>
                        <div>
                            <label for="confirm_password" class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-3 bg-white border border-[#E0E6E2] rounded-2xl focus:border-rose-300 outline-none text-xs font-bold shadow-sm"/>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Actions -->
            <div class="border-t border-[#E0E6E2] pt-6 flex justify-end">
                <button type="submit" class="px-8 py-3.5 bg-[#004D40] text-white font-bold rounded-full hover:bg-[#00332A] transition-all text-xs shadow-md">
                    Save Profile Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const coverInput = document.getElementById('coverInput');
    const coverPreview = document.getElementById('coverPreview');

    function validateAndPreviewLocalFile(input, previewImgElement) {
        if (!input || !input.files || !input.files[0]) return;
        const file = input.files[0];
        const errorBanner = document.getElementById('clientErrorBanner');
        if (errorBanner) errorBanner.classList.add('hidden');

        const allowedMimes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
        if (!allowedMimes.includes(file.type)) {
            showError('Invalid image format. Please select a PNG, JPG, JPEG, or WEBP image.');
            input.value = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            showError('The selected image is too large. Maximum file size allowed is 2MB.');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            if (previewImgElement) {
                previewImgElement.src = e.target.result;
            }
        };
        reader.readAsDataURL(file);
    }

    if (avatarInput) {
        avatarInput.addEventListener('change', () => validateAndPreviewLocalFile(avatarInput, avatarPreview));
    }
    if (coverInput) {
        coverInput.addEventListener('change', () => validateAndPreviewLocalFile(coverInput, coverPreview));
    }

    const settingsForm = document.getElementById('settingsForm');
    if (settingsForm) {
        settingsForm.addEventListener('submit', (e) => {
            const newPwd = document.getElementById('new_password');
            const confirmPwd = document.getElementById('confirm_password');
            const oldPwd = document.getElementById('old_password');

            if (newPwd && newPwd.value.trim() !== '') {
                if (!oldPwd || oldPwd.value.trim() === '') {
                    e.preventDefault();
                    showError('Please enter your current password to authorize password changes.');
                    return;
                }
                if (newPwd.value.length < 6) {
                    e.preventDefault();
                    showError('Your new password must be at least 6 characters long.');
                    return;
                }
                if (newPwd.value !== confirmPwd.value) {
                    e.preventDefault();
                    showError('Your new password and confirmation password do not match.');
                    return;
                }
            }
        });
    }

    function showError(msg) {
        const errorBanner = document.getElementById('clientErrorBanner');
        const errorMessage = document.getElementById('clientErrorMessage');
        if (errorBanner && errorMessage) {
            errorMessage.innerText = msg;
            errorBanner.classList.remove('hidden');
            errorBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});
</script>

<?php
// Include global footer
include ROOT_PATH . '/app/Views/partials/footer.php';
?>
