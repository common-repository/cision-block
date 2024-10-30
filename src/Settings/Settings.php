<?php

namespace CisionBlock\Settings;

use CisionBlock\Frontend\Frontend;

class Settings extends \CisionBlock\Plugin\Settings\Settings
{
    const DEFAULT_ITEMS_PER_PAGE = 0;
    const DEFAULT_ITEM_COUNT = 50;
    const DEFAULT_CACHE_LIFETIME = 60 * 5;
    const DEFAULT_DATE_FORMAT = 'd-m-Y';
    const DEFAULT_IMAGE_STYLE = 'DownloadUrl';
    const MAX_ITEMS_PER_FEED =  50;
    const MAX_ITEMS_PER_PAGE = 50;
    const DEFAULT_FEED_TYPE = 'PRM';
    const DEFAULT_PAGE_INDEX = 1;
    const DEFAULT_LANGUAGE = '';
    const DEFAULT_READ_MORE_TEXT = 'Read more';
    const DISPLAY_MODE_ALL = 1;
    const DISPLAY_MODE_REGULATORY = 2;
    const DISPLAY_MODE_NON_REGULATORY = 3;
    const DEFAULT_DISPLAY_MODE = self::DISPLAY_MODE_ALL;
    const DEFAULT_MARK_REGULATORY_TEXT = 'Regulatory pressrelease';
    const DEFAULT_MARK_NON_REGULATORY_TEXT = 'Non-regulatory pressrelease';
    const DEFAULT_FILTER_ALL_TEXT = 'Show all';
    const DEFAULT_FILTER_REGULATORY_TEXT = 'Regulatory';
    const DEFAULT_FILTER_NON_REGULATORY_TEXT = 'Non-regulatory';
    const MIN_PHP_VERSION = '7.4';
    const MIN_WP_VERSION = '3.1.0';

    /**
     * @var array $default_settings
     */
    private array $defaultSettings = [
        'count' => self::DEFAULT_ITEM_COUNT,
        'source_uid' => '',
        'tags' => '',
        'categories' => '',
        'start_date' => '',
        'end_date' => '',
        'show_excerpt' => true,
        'mark_regulatory' => false,
        'regulatory_text' => self::DEFAULT_MARK_REGULATORY_TEXT,
        'non_regulatory_text' => self::DEFAULT_MARK_NON_REGULATORY_TEXT,
        'show_filters' => false,
        'filter_all_text' => self::DEFAULT_FILTER_ALL_TEXT,
        'filter_regulatory_text' => self::DEFAULT_FILTER_REGULATORY_TEXT,
        'filter_non_regulatory_text' => self::DEFAULT_FILTER_NON_REGULATORY_TEXT,
        'search_term' => '',
        'image_style' => self::DEFAULT_IMAGE_STYLE,
        'use_https' => false,
        'date_format' => self::DEFAULT_DATE_FORMAT,
        'view_mode' => self::DEFAULT_DISPLAY_MODE,
        'language' => '',
        'readmore' => self::DEFAULT_READ_MORE_TEXT,
        'show_files' => false,
        'attachment_field' => 'FileName',
        'items_per_page' => self::DEFAULT_ITEMS_PER_PAGE,
        'cache_expire' => self::DEFAULT_CACHE_LIFETIME,
        'types' => array(self::DEFAULT_FEED_TYPE),
        'internal_links' => false,
        'base_slug' => 'cision',
        'exclude_css' => false,
        'template' => null,
        'version' => Frontend::VERSION,
    ];

    /**
     * @return array
     */
    public function getDefaults(): array
    {
        return $this->defaultSettings;
    }

    /**
     * @param array $settings
     * @return array
     */
    public function verify(array $settings): array
    {
        $mapping = [
            // general
            'count' => 'count',
            'cache_expire' => 'cache_expire',
            'source_uid' => 'source_uid',
            'types' => 'types',
            'language' => 'language',
            'readmore' => 'readmore',
            'start' => 'start_date',
            'end' => 'end_date',
            'mark_regulatory' => 'mark_regulatory',
            'regulatory_text' => 'regulatory_text',
            'non_regulatory_text' => 'non_regulatory_text',
            'date_format' => 'date_format',
            'tags' => 'tags',
            'search_term' => 'search_term',
            'categories' => 'categories',
            'view' => 'view_mode',
            'use_https' => 'use_https',
            'image_style' => 'image_style',
            'show_excerpt' => 'show_excerpt',
            'show_files' => 'show_files',
            'attachment_field' => 'attachment_field',
            'items_per_page' => 'items_per_page',
            'exclude_css' => 'exclude_css',
            'template' => 'template',

            // permalinks
            'internal_links' => 'internal_links',
            'base_slug' => 'base_slug',

            // filters
            'show_filters' => 'show_filters',
            'filter_all_text' => 'filter_all_text',
            'filter_regulatory_text' => 'filter_regulatory_text',
            'filter_non_regulatory_text' => 'filter_non_regulatory_text',

            // special for shortcode attrib
            'regulatory' => 'view_mode',

            // special for widget
            'source' => 'source_uid',
            'view_mode' => 'view_mode',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
        ];

        $result = [
            'show_excerpt' => $this->get('show_excerpt'),
            'show_files' => $this->get('show_files'),
            'use_https' => $this->get('use_https'),
            'mark_regulatory' => $this->get('mark_regulatory'),
            'show_filters' => $this->get('show_filters'),
            'internal_links' => $this->get('internal_links'),
            'exclude_css' => $this->get('exclude_css'),
        ];

        foreach ($settings as $name => $value) {
            switch ($name) {
                case 'count':
                    $result[$mapping[$name]] = filter_var(
                        $value,
                        FILTER_VALIDATE_INT,
                        [
                            'options' => [
                                'default' => self::DEFAULT_ITEM_COUNT,
                                'min_range' => 1,
                                'max_range' => $this->get('max_items', self::MAX_ITEMS_PER_FEED),
                            ],
                        ]
                    );
                    break;
                case 'cache_expire':
                    $result[$mapping[$name]] = filter_var(
                        $value,
                        FILTER_SANITIZE_NUMBER_INT
                    );
                    break;
                case 'types':
                    if (is_string($value)) {
                        $result[$mapping[$name]] = explode(',', str_replace(' ', '', $value));
                    } else {
                        $result[$mapping[$name]] = filter_var(
                            $value,
                            FILTER_UNSAFE_RAW,
                            FILTER_REQUIRE_ARRAY
                        );
                    }
                    break;
                case 'template':
                    include_once ABSPATH . '/wp-admin/includes/theme.php';
                    $template = filter_var(
                        $value,
                        FILTER_UNSAFE_RAW
                    );
                    $templates = get_page_templates();
                    if (array_search($template, $templates) !== false) {
                        $result[$mapping[$name]] = $template;
                    } elseif (array_key_exists($template, $templates)) {
                        $result[$mapping[$name]] = $templates[$template];
                    } else {
                        $result[$mapping[$name]] = null;
                    }
                    break;
                case 'source':
                case 'source_uid':
                case 'readmore':
                case 'start':
                case 'end':
                case 'start_date':
                case 'end_date':
                case 'date_format':
                case 'tags':
                case 'search_term':
                case 'image_style':
                case 'base_slug':
                case 'attachment_field':
                    $result[$mapping[$name]] = filter_var(
                        $value,
                        FILTER_UNSAFE_RAW
                    );
                    break;
                case 'language':
                    $result[$mapping[$name]] = filter_var(
                        $value,
                        FILTER_UNSAFE_RAW
                    );
                    $result[$mapping[$name]] = strtolower($result[$mapping[$name]]);
                    break;
                case 'categories':
                    $result[$mapping[$name]] = filter_var(
                        $value,
                        FILTER_UNSAFE_RAW
                    );
                    $result[$mapping[$name]] = trim(strtolower($result[$mapping[$name]]));
                    break;
                case 'show_excerpt':
                case 'show_files':
                case 'mark_regulatory':
                case 'use_https':
                case 'internal_links':
                case 'show_filters':
                case 'exclude_css':
                    $result[$mapping[$name]] = filter_var(
                        $value,
                        FILTER_VALIDATE_BOOLEAN
                    );
                    break;
                case 'regulatory_text':
                    $text = filter_var(
                        $value,
                        FILTER_SANITIZE_SPECIAL_CHARS
                    );
                    $result[$mapping[$name]] = $text ?: self::DEFAULT_MARK_REGULATORY_TEXT;
                    break;
                case 'non_regulatory_text':
                    $text = filter_var(
                        $value,
                        FILTER_SANITIZE_SPECIAL_CHARS
                    );
                    $result[$mapping[$name]] = $text ?: self::DEFAULT_MARK_NON_REGULATORY_TEXT;
                    break;
                case 'filter_all_text':
                    $text = filter_var(
                        $value,
                        FILTER_SANITIZE_SPECIAL_CHARS
                    );
                    $result[$mapping[$name]] = $text ?: self::DEFAULT_FILTER_ALL_TEXT;
                    break;
                case 'filter_regulatory_text':
                    $text = filter_var(
                        $value,
                        FILTER_SANITIZE_SPECIAL_CHARS
                    );
                    $result[$mapping[$name]] = $text ?: self::DEFAULT_FILTER_REGULATORY_TEXT;
                    break;
                case 'filter_non_regulatory_text':
                    $text = filter_var(
                        $value,
                        FILTER_SANITIZE_SPECIAL_CHARS
                    );
                    $result[$mapping[$name]] = $text ?: self::DEFAULT_FILTER_NON_REGULATORY_TEXT;
                    break;
                case 'view':
                case 'view_mode':
                    $result[$mapping[$name]] = filter_var(
                        $value,
                        FILTER_VALIDATE_INT,
                        [
                            'options' => [
                                'default' => 1,
                                'min_range' => 1,
                                'max_range' => 3,
                            ],
                        ]
                    );
                    break;
                case 'items_per_page':
                    $result[$mapping[$name]] = filter_var(
                        $value,
                        FILTER_VALIDATE_INT,
                        [
                            'options' => [
                                'default' => self::DEFAULT_ITEMS_PER_PAGE,
                                'min_range' => 0,
                                'max_range' => $this->get('max_items_per_page', self::MAX_ITEMS_PER_PAGE),
                            ],
                        ]
                    );
                    break;
                case 'regulatory':
                    // This is a fallback for old argument
                    $result[$mapping[$name]] = self::DISPLAY_MODE_REGULATORY;
                    break;
            }
        }

        return $result;
    }
}
