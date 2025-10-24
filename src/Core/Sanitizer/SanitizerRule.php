<?php

namespace UserSpace\Core\Sanitizer;

class SanitizerRule
{
    public const TEXT_FIELD = 'text_field';
    public const EMAIL = 'email';
    public const URL = 'url';
    public const INT = 'int';
    public const FLOAT = 'float';
    public const BOOL = 'bool';
    public const KSES_POST = 'kses_post';
    public const KSES_DATA = 'kses_data';
    public const NO_HTML = 'no_html'; // For wp_strip_all_tags
    public const SLUG = 'slug'; // For sanitize_title
    public const KEY = 'key'; // For sanitize_key
    public const FILE_NAME = 'file_name'; // For sanitize_file_name
    public const HTML_CLASS = 'html_class'; // For sanitize_html_class
    public const USER = 'user'; // For sanitize_user
}