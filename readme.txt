=== UserSpace: Advanced User Management & Profile Toolkit ===
Contributors: Plechev Andrey
Tags: user, profile, registration, login, form builder, custom fields, tabs, sse, queue, framework
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

UserSpace is a powerful and extensible framework for creating advanced user-centric functionality in WordPress. It provides a solid foundation for building custom user profiles, registration forms, and authentication flows, all based on modern software architecture principles like Dependency Injection, Use Cases, and a clean separation of concerns.

== Description ==

UserSpace is more than just a plugin; it's a developer-focused toolkit designed to be the core of your user management system. Whether you need a simple modal login form or a complex, multi-tabbed user profile with custom fields, UserSpace provides the tools to build it efficiently and reliably.

**Key Features:**

*   **Visual Form Builder:**
    *   Create and manage **Profile** and **Registration** forms using a drag-and-drop interface.
    *   A wide range of built-in field types: Text, Textarea, Email, Password, Number, Select, Radio, Checkbox, and a secure File Uploader.
    *   Easily add custom fields to user profiles.

*   **Advanced User Profiles:**
    *   Display user profiles on any page using the `[usp_account]` shortcode.
    *   **Customizable Tab System:** Create and manage profile tabs (e.g., Profile, Edit, Activity, Security).
    *   **Theming System:** Comes with built-in themes for profile pages and allows developers to create their own themes as addons.

*   **Modern Authentication:**
    *   **Modal Forms:** Easily trigger modal login, registration, and password reset forms from any link on your site.
    *   **Shortcode Integration:** Display forms directly on pages using `[usp_form type="login"]`.
    *   **Email Confirmation:** Optional email confirmation flow for new user registrations.

*   **Developer-Friendly Architecture:**
    *   **Clean Architecture:** Logic is separated into Controllers, Use Cases, Repositories, and Adapters, making the code easy to understand, test, and extend.
    *   **Dependency Injection:** Built-in DI container for managing services and dependencies.
    *   **Extensibility:** A powerful Addon and Theme system allows for deep integration and customization. The `userspace_loaded` action hook ensures safe loading for your extensions.

*   **High-Performance Features:**
    *   **Background Task Queue:** Heavy tasks like sending emails are processed in the background, ensuring a fast user experience.
    *   **Real-Time Updates with SSE:** The admin area uses Server-Sent Events (SSE) for real-time updates (e.g., queue status) with an optimized adaptive long-polling mechanism to reduce server load.

*   **Robust Admin Area:**
    *   **Setup Wizard:** A step-by-step guide for initial plugin configuration.
    *   **Comprehensive Settings:** Manage page assignments, API keys, appearance, and more.
    *   **Queue Management:** Monitor and manage background tasks.
    *   **User Lists:** View registered users in a card or table format with search and pagination.

This plugin is ideal for developers who want to build custom, high-quality user management solutions without being locked into a rigid system.

== Installation ==

1.  Upload the `userspace` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  After activation, you will be prompted to run the **Setup Wizard**. It is highly recommended to complete the wizard to configure the essential pages (Login, Registration, Profile).
4.  Place the shortcodes on the pages you assigned in the settings.

== Usage Examples ==

**1. Displaying the User Profile Page**

Place the following shortcode on the page you designated as the "User Profile Page":
`[usp_account]`

**2. Displaying Forms on a Page**

*   Login Form: `[usp_form type="login"]`
*   Registration Form: `[usp_form type="registration"]`
*   Password Reset Form: `[usp_form type="forgot-password"]`

**3. Triggering Modal Forms**

Add the class `usp-modal-trigger` and a `data-form` attribute to any link to open a modal form.

*   **Login Modal:**
    `<a href="#" class="usp-modal-trigger" data-form="login">Log In</a>`

*   **Registration Modal:**
    `<a href="#" class="usp-modal-trigger" data-form="registration">Register</a>`

*   **Password Reset Modal:**
    `<a href="#" class="usp-modal-trigger" data-form="forgot-password">Lost your password?</a>`

== Frequently Asked Questions ==

= How do I create a custom theme for the profile page? =

You can create a new plugin that acts as a theme addon. Your main plugin class should implement `UserSpace\Core\Theme\ThemeInterface` and register itself using the `userspace_addons` filter. The `ThemeManager` will automatically discover and make it available in the settings.

= How do I add a new tab to the user profile? =

You can create a class that extends `UserSpace\Common\Module\Tabs\Src\Domain\AbstractTab` and register it using the `usp_tabs_register` filter. The tab will then appear in the "Tabs Configuration" admin page.

= Can I use the plugin's services in my own theme or plugin? =

Yes! The `userspace_loaded` action hook is fired once all services are initialized. It passes the DI container as an argument, allowing you to safely access any of the UserSpace services.

```php
add_action('userspace_loaded', function(UserSpace\Core\Container\ContainerInterface $container) {
    $mailer = $container->get(UserSpace\Common\Service\MailerServiceInterface::class);
    // Now you can use the mailer service
});
```

== Screenshots ==

1. The Setup Wizard for easy initial configuration.
2. The drag-and-drop Form Builder for the user profile.
3. The Tabs Configuration interface.
4. The main Settings page.
5. The Queue Management dashboard with real-time updates.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
* This is the first version of the plugin.