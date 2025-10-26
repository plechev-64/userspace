<?php

namespace UserSpace\Common\Module\Settings\App;

/**
 * Enum для хранения ключей (идентификаторов) всех настроек плагина.
 */
enum SettingsEnum: string
{
    // General
    case API_KEY = 'api_key';
    case ENABLE_FEATURE_X = 'enable_feature_x';
    case DEFAULT_AVATAR_ID = 'default_avatar_id';
    case FILES = 'files';
    case ENABLE_USER_BAR = 'enable_user_bar';
    case REQUIRE_EMAIL_CONFIRMATION = 'require_email_confirmation';
    case PREFER_COLOR = 'prefer_color';

    // Advanced
    case INTEGRATION_MODE = 'integration_mode';
    case WEBHOOK_URL = 'webhook_url';
    case USER_ROLE = 'user_role';
    case CUSTOM_CSS = 'custom_css';

    // Page Assignment
    case LOGIN_PAGE_ID = 'login_page_id';
    case REGISTRATION_PAGE_ID = 'registration_page_id';
    case REDIRECT_AFTER_LOGIN_PAGE_ID = 'redirect_after_login_page_id';
    case PASSWORD_RESET_PAGE_ID = 'password_reset_page_id';
    case PROFILE_PAGE_ID = 'profile_page_id';
    case PROFILE_USER_QUERY_VAR = 'profile_user_query_var';
    case PROFILE_TAB_QUERY_VAR = 'profile_tab_query_var';

    // Dependency Examples
    case PARENT_SELECT_FIELD = 'parent_select_field';
    case PARENT_CHECKBOX_FIELD = 'parent_checkbox_field';
    case PARENT_RADIO_FIELD = 'parent_radio_field';
    case DEPENDENT_TEXT_FIELD = 'dependent_text_field';
    case DEPENDENT_CHECKBOX_FIELD = 'dependent_checkbox_field';
    case DEPENDENT_RADIO_FIELD = 'dependent_radio_field';
    case DEPENDENT_TEXTAREA_FIELD = 'dependent_textarea_field';
    case DEPENDENT_URL_FIELD = 'dependent_url_field';
    case DEPENDENT_UPLOADER_FIELD = 'dependent_uploader_field';

    // Appearance
    case ACCOUNT_THEME = 'account_theme';
}