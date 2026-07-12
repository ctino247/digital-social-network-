# Mimshack Social Commerce Platform - Design Document

Mimshack is a high-performance, commercial-grade social commerce web application built with pure, Object-Oriented PHP 8.3+ and MySQL 8.0+. This document details the mapping of the UI screens, the custom MVC architecture, and the database schema.

---

## 1. UI Screen Inventory & Architecture Mapping

The 11 UI screens from the design files are integrated into dynamic PHP views using a custom MVC structure:

1. **Home Feed** (`/home_feed`) -> `HomeController@index`
   - Displays a dynamic social feed (text posts, replies, quote posts, polls).
   - Shows active stories/trending topics.
   - Model interactions: `Post`, `User`, `Follow`, `Poll`, `Like`, `Bookmark`.

2. **Explore** (`/explore`) -> `ExploreController@index`
   - Unified search and discover system for both posts/hashtags and marketplace products.
   - Shows trending topics and active recommendations.
   - Model interactions: `Post`, `Product`, `Category`.

3. **Create Post** (`/post/create`) -> `PostController@create` & `PostController@store`
   - UI for text posts with a 500-character limit, poll creation, mentions, and hashtags.
   - Model interactions: `Post`, `Poll`, `PollOption`.

4. **Profile** (`/profile/{username}`) -> `ProfileController@index`
   - Displays user details, bio, avatar, tabs for Posts, Replies, Products, and Wallet.
   - Handles the follow/unfollow actions and application to be a Creator.
   - Model interactions: `User`, `Post`, `Product`, `Follow`, `CreatorApplication`.

5. **Marketplace** (`/marketplace`) -> `MarketplaceController@index`
   - Browse digital products (PDF, ZIP, Templates, Courses, etc.) with search and category filters.
   - Model interactions: `Product`, `Category`.

6. **Product Detail** (`/product/{slug}`) -> `MarketplaceController@detail`
   - Deep dive into a single digital product. Includes pricing, ratings, reviews, affiliate link generation, and social sharing.
   - Model interactions: `Product`, `Review`, `ReferralLink`.

7. **Cart & Checkout** (`/cart`) -> `CartController@index` & `CartController@checkout`
   - Displays selected items, lets users apply coupons, calculates platform fees (absorbed vs. passed-on), and offers gateway selections.
   - Model interactions: `Product`, `Coupon`, `Order`.

8. **Messages** (`/messages`) -> `MessageController@index`
   - Lists active chat conversations/direct messages for the authenticated user.
   - Model interactions: `Message`, `User`.

9. **Chat Conversation** (`/messages/{username}`) -> `MessageController@chat`
   - Direct message window with a specific user. Supports live message polling.
   - Model interactions: `Message`, `User`.

10. **New Message** (`/messages/new`) -> `MessageController@new`
    - Form to search for users and start a new direct message conversation.
    - Model interactions: `User`.

11. **Notifications** (`/notifications`) -> `NotificationController@index`
    - Integrated activity stream: Likes, comments, follows, messages, referrals, commissions, and system alerts.
    - Model interactions: `Notification`.

---

## 2. Custom MVC Framework Core

To ensure high performance, security, and scalability, we avoid bloated third-party frameworks and use a custom, streamlined MVC architecture:

- **Router**: High-speed regex router with support for wildcards (`/profile/{username}`) and middleware hooks (`AuthMiddleware`, `AdminMiddleware`).
- **Controller**: Base controller with CSRF verification, input parsing, view rendering engine, and standard redirect mechanics.
- **Model & Database**: PDO-powered active-record-like abstraction. All SQL queries use fully parameterized prepared statements for 100% SQL injection immunity.
- **Session Manager**: Manages session variables securely, handles "Remember Me" tokens in MySQL, rate limits actions, and handles secure cookies.
- **Security Engine**: Comprehensive protection (XSS sanitization, CSRF token validation, upload mime-type verification, and strong bcrypt hashing).

---

## 3. Database Schema Design (MySQL 8.0+)

The database schema is fully normalized, indexed on key query paths, and enforces referential integrity through foreign keys.

- `users`: Identity storage with roles (`member`, `creator`, `admin`).
- `posts`: Direct posts, quotes (`quote_id`), or replies (`parent_id`).
- `post_likes` / `post_bookmarks`: Social interactions.
- `follows`: Social graphs.
- `polls` / `poll_options` / `poll_votes`: Poll mechanics.
- `messages`: Private chat history.
- `notifications`: Activity alerts.
- `categories` / `products`: Digital marketplace catalogs.
- `product_reviews`: Dynamic ratings & reviews.
- `coupons`: Price adjustment rules.
- `orders`: Transactions and digital download unlocks.
- `sales_partners`: Affiliate state trackers.
- `referral_links` / `referral_clicks`: Last-click attribution engines.
- `wallet` / `transactions` / `withdrawals`: Ledger and financial balance engines.
- `creator_applications`: Creator approval pipeline.
- `commission_settings`: System-wide settings for affiliate splits.
- `activity_logs`: Audit logs for secure tracking.
