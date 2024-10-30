<?php

namespace CisionBlock\Frontend;

use CisionBlock\Cision\Service;
use CisionBlock\Psr\Container\ContainerInterface;
use CisionBlock\Settings\Settings;
use CisionBlock\Traits\AddonTrait;
use CisionBlock\Widget\Widget;
use stdClass;

class Frontend
{
    use AddonTrait;

    const SETTINGS_NAME = 'cision_block_settings';
    const TRANSIENT_KEY = 'cision_block_data';
    const VERSION = '4.2.0';

    /** @var ContainerInterface  */
    protected ContainerInterface $container;

    /** @var Settings */
    protected Settings $settings;

    /** @var Widget */
    protected Widget $widget;

    /** @var string */
    protected string $current_block_id = 'cision-block';

    public function __construct(ContainerInterface $container, Settings $settings, Widget $widget)
    {
        $this->container = $container;
        $this->settings = $settings;
        $this->widget = $widget;

        // Add support for addons.
        do_action('cision-block/register/addon', [$this]);

        add_shortcode('cision-block', [$this, 'displayFeed']);

        $this->addActions();
        $this->addFilters();
        $this->localize();
    }

    /**
     * Clears transient based on block_id and page_id.
     *
     * @param int $post_ID
     * @param string $content
     */
    protected function checkTransient(int $post_ID, string $content): void
    {
        if (has_shortcode($content, 'cision-block')) {
            $regex = get_shortcode_regex();
            $matches = [];
            $block_id = 'cision_block';
            if (preg_match_all('/' . $regex . '/', $content, $matches) &&
                array_key_exists(2, $matches)
            ) {
                foreach ($matches[2] as $key => $match) {
                    if ($match === 'cision-block') {
                        if (array_key_exists(3, $matches)) {
                            $atts = shortcode_parse_atts($matches[3][$key]);
                            if (isset($atts['id'])) {
                                $block_id = $atts['id'];
                            }
                        }
                        delete_transient(self::TRANSIENT_KEY . '_' . $block_id . '_' . $post_ID);
                    }
                }
            }
        }
    }

    /**
     * Sets the page title when visiting a press release.
     *
     * @param string $title
     * @global stdClass $CisionItem
     *
     * @return string
     */
    public function setTitle(string $title): string
    {
        global $CisionItem;
        if (get_query_var('cision_release_id')) {
            return get_bloginfo('name') . ' | ' . ($CisionItem ? $CisionItem->Title : __('Not found', 'cision-block'));
        }

        return $title;
    }

    /**
     * Include custom template if needed.
     *
     * @global stdClass $CisionItem
     * @global WP_Query $wp_query
     */
    public function addTemplate(): void
    {
        global $CisionItem;
        global $displayFiles;
        global $attachmentField;
        global $wp_query;
        if (get_query_var('cision_release_id')) {
            $release_id = apply_filters('cision-block/filter/release/id', get_query_var('cision_release_id'));

            $response = $this->container->get(Service::class)->fetchReleaseById($release_id);
            if ($response) {
                $CisionItem = $response->Release;

                // We remove all inline styles here.
                $CisionItem->HtmlBody = preg_replace(
                    '/(<[^>]*) style=("[^"]+"|\'[^\']+\')([^>]*>)/i',
                    '$1$3',
                    $CisionItem->HtmlBody
                );
            } else {
                // Return a 404 page.
                $wp_query->set_404();
                status_header(404);
                return;
            }

            $displayFiles = $this->settings->get('show_files', false);
            $attachmentField = $this->settings->get('attachment_field', 'FileName');
            add_filter('template_include', function () {
                $template = locate_template([
                    'cision-block-post.php',
                    'templates/cision-block-post.php'
                ]);
                if ($template) {
                    // Include theme overridden template.
                    return $template;
                } else {
                    // Include the default plugin supplied template.
                    return __DIR__ . '/templates/cision-block-post.php';
                }
            });
        }
    }

    /**
     * Triggered when a post is updated.
     *
     * @param int $post_ID
     * @param \WP_Post $post_after
     * @param \WP_Post $post_before
     */
    public function postUpdated(int $post_ID, \WP_Post $post_after, \WP_Post $post_before)
    {
        $this->checkTransient($post_ID, $post_before->post_content);
        $this->checkTransient($post_ID, $post_after->post_content);
    }

    /**
     * Register actions.
     */
    protected function addActions(): void
    {
        add_action('init', [$this, 'addRewriteRules']);
        add_action('wp_enqueue_scripts', [$this, 'addStyles']);
        add_action('post_updated', [$this, 'postUpdated'], 10, 3);
        add_action('template_redirect', [$this, 'addTemplate']);
        add_action('after_setup_theme', [$this, 'setTheme']);
    }

    /**
     * Register filters.
     */
    protected function addFilters(): void
    {
        add_filter('query_vars', [$this, 'addQueryVars']);
        add_filter('pre_get_document_title', [$this, 'setTitle']);
        // The SEO Framework plugin removes any pre_get_document_title filter.
        if (defined('THE_SEO_FRAMEWORK_PRESENT')) {
            add_filter('the_seo_framework_title_from_custom_field', [$this, 'setTitle'], 10, 2);
        }
    }

    /**
     * Localize plugin.
     */
    protected function localize(): void
    {
        load_plugin_textdomain('cision-block');
    }

    public function addRewriteRules(): void
    {
        // Flush rewrite rules if needed.
        if (get_transient('cision_block_flush_rewrite_rules')) {
            if ($this->settings->get('internal_links')) {
                add_rewrite_endpoint(
                    $this->settings->get('base_slug'),
                    EP_ROOT,
                    'cision_release_id'
                );
            }
            flush_rewrite_rules();
            delete_transient('cision_block_flush_rewrite_rules');
        }
    }

    /**
     * Add custom query variables, used for pager.
     *
     * @param array $vars
     *   Array of available query variables.
     *
     * @return array
     *   Updated array of query variables.
     */
    public function addQueryVars(array $vars): array
    {
        return array_merge($vars, ['cb_id', 'cb_page', 'cb_filter']);
    }

    /**
     * Register stylesheet and scripts.
     */
    public function addStyles(): void
    {
        wp_register_style(
            'cision-block',
            $this->getPluginUrl('css/cision-block.css'),
            [],
            self::VERSION
        );
    }

    /**
     * @param string $path
     * @return string
     */
    public function getPluginUrl(string $path): string
    {
        return plugin_dir_url(__FILE__) . $path;
    }

    /**
     * Triggered after we have switched theme.
     */
    public function setTheme(): void
    {
        set_transient('cision_block_flush_rewrite_rules', 1);
    }

    /**
     * @return string
     */
    protected function getCacheKey(): string
    {
        global $post;
        global $widget_id;
        $settings = $this->settings->toOptionsArray();
        unset($settings['base_slug'], $settings['version'], $settings['notes']);
        $settings['id'] = $this->current_block_id . '_' . ($widget_id ?: $post->ID);
        return self::TRANSIENT_KEY . '_' . md5(\json_encode($settings));
    }

    /**
     * Returns the generated markup for the feed.
     *
     * @param mixed $atts
     *   Shortcode attributes.
     *
     * @return mixed
     */
    public function displayFeed($atts)
    {
        global $widget_id;

        // Reload settings since they might be overwritten.
        $this->settings->load();

        // TODO: Is this really needed at this point?
        $this->current_block_id = 'cision-block';

        // There is no need to check these values if no arguments is supplied.
        if (is_array($atts)) {
            // Remove any premium attributes.
            $atts = array_filter($atts, function ($value, string $key) {
                return !in_array($key, [
                    'attachment_field',
                    'categories',
                    'internal_links',
                    'mark_regulatory',
                    'search_term',
                    'show_files',
                    'tags'
                    ]);
            }, ARRAY_FILTER_USE_BOTH);
            $verified = $this->settings->verify($atts);
            $this->settings->setFromArray($verified);
            $this->current_block_id = $atts['id'] ?? $this->current_block_id;
            $widget_id = $atts['widget'] ?? null;
            if (filter_var($atts['flush'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $cacheKey = $this->getCacheKey();
                delete_transient($cacheKey);
            }
        }

        if (get_query_var('cb_filter')) {
            $viewMode = (int)get_query_var('cb_filter');
            $this->settings->set('view_mode', $viewMode);
        }

        // Add stylesheet.
        if (!$this->settings->get('exclude_css')) {
            wp_enqueue_style('cision-block');
        }

        $feed_items = $this->getFeed();
        $pager = $this->getPagination($feed_items);

        // Handle translations.
        $readMore = $this->settings->get('readmore');
        $regulatoryText = $this->settings->get('regulatory_text');
        $nonRegulatoryText = $this->settings->get('non_regulatory_text');
        $filterAllText = $this->settings->get('filter_all_text');
        $filterRegulatoryText = $this->settings->get('filter_regulatory_text');
        $filterNonRegulatoryText = $this->settings->get('filter_non_regulatory_text');
        if (has_filter('wpml_translate_single_string')) {
            $readMore =  apply_filters('wpml_translate_single_string', $this->settings->get('readmore'), 'cision-block', 'Read More Button Text');
            $regulatoryText =  apply_filters('wpml_translate_single_string', $this->settings->get('regulatory_text'), 'cision-block', 'Text For Regulatory Releases');
            $nonRegulatoryText = apply_filters('wpml_translate_single_string', $this->settings->get('non_regulatory_text'), 'cision-block', 'Text For Non-regulatory Releases');
            $filterAllText =  apply_filters('wpml_translate_single_string', $this->settings->get('filter_all_text'), 'cision-block', 'All Filter Button Text');
            $filterRegulatoryText =  apply_filters('wpml_translate_single_string', $this->settings->get('filter_regulatory_text'), 'cision-block', 'Regulatory Filter Button Text');
            $filterNonRegulatoryText = apply_filters('wpml_translate_single_string', $this->settings->get('filter_non_regulatory_text'), 'cision-block', 'Non-regulatory Filter Button Text');
        }

        // Add variables to symbol table.
        extract([
            'cision_feed' => $feed_items,
            'pager' => $pager,
            'id' => $this->current_block_id,
            'readmore' => $readMore,
            'mark_regulatory' => $this->settings->get('mark_regulatory'),
            'regulatory_text' => htmlspecialchars_decode($regulatoryText ?: ''),
            'non_regulatory_text' => htmlspecialchars_decode($nonRegulatoryText ?: ''),
            'show_filters' => $this->settings->get('show_filters'),
            'show_excerpt' => $this->settings->get('show_excerpt'),
            'filter_all_text' => htmlspecialchars_decode($filterAllText ?: ''),
            'filter_regulatory_text' => htmlspecialchars_decode($filterRegulatoryText ?: ''),
            'filter_non_regulatory_text' => htmlspecialchars_decode($filterNonRegulatoryText ?: ''),
            'prefix' => apply_filters('cision_block_prefix', '', $this->current_block_id),
            'suffix' => apply_filters('cision_block_suffix', '', $this->current_block_id),
            'attributes' => $this->parseAttributes(apply_filters('cision_block_media_attributes', [
                'class' => [
                    'cision-feed-item',
                ],
            ], $this->current_block_id)),
            'wrapper_attributes' => $this->parseAttributes(apply_filters('cision_block_wrapper_attributes', [
                'class' => [
                    'cision-feed-wrapper',
                ],
            ], $this->current_block_id)),
            'options' => [
                'date_format' => $this->settings->get('date_format'),
            ],
        ], EXTR_SKIP);

        ob_start();
        $templates = [
            'cision-block.php',
            'templates/cision-block.php',
        ];
        if ($this->settings->get('template')) {
            array_unshift($templates, $this->settings->get('template'));
        }
        $template = locate_template($templates);
        if ($template) {
            // Include theme overridden template.
            include $template;
        } else {
            // Include the default plugin supplied template.
            include __DIR__ . '/templates/cision-block.php';
        }
        return ob_get_clean();
    }

    /**
     * @param array $attributes
     *
     * @return string
     */
    protected function parseAttributes(array $attributes): string
    {
        $attributeString = '';
        foreach ($attributes as $key => $attribute) {
            if (is_array($attribute)) {
                $attributeString .= ' ' . $key . '="' . implode(' ', $attribute) . '" ';
            } else {
                $attributeString .= ' ' . $key . '="' . $attribute . '" ';
            }
        }
        return rtrim($attributeString);
    }

    /**
     * Creates and returns markup for a pager.
     *
     * @param array $items
     *   Array of all feed items.
     *
     * @return string
     *   Markup for the generated pager.
     */
    protected function getPagination(array &$items): string
    {
        $output = '';
        $attributes = [
            'class' => [
                'cision-feed-pager',
            ],
        ];
        $attributes = $this->parseAttributes(apply_filters('cision_block_pager_attributes', $attributes, $this->current_block_id));
        $active_class = apply_filters('cision_block_pager_active_class', 'active', $this->current_block_id);
        if ($this->settings->get('items_per_page') > 0) {
            $max = (int) ceil(count($items) / $this->settings->get('items_per_page'));
            $id = get_query_var('cb_id');
            $page = (int) get_query_var('cb_page', -1);
            $active = ($id === 'cision_block' ? $page : 0);
            if ($max > 1) {
                $output = '<ul' . $attributes . '>';
                for ($i = 0; $i < $max; $i++) {
                    $output .= '<li><a href="' . add_query_arg(['cb_id' => 'cision_block', 'cb_page' => $i]) . '"' .
                        ($active === $i ? ' class="' . $active_class . '"' : '') . '>' . ($i + 1) . '</a></li>';
                }
                if ($active >= 0 && $active < $max) {
                    $items = array_slice(
                        $items,
                        $active * $this->settings->get('items_per_page'),
                        $this->settings->get('items_per_page')
                    );
                }
                $output .= '</ul>';
            }
        }
        return $output;
    }

    public function setCurrentBlockId(string $id): void
    {
        $this->current_block_id = $id;
    }

    public function getCurrentBlockId(): string
    {
        return $this->current_block_id;
    }

    /**
     * Retrieve a feed from the specified source URL.
     *
     * @return array
     *   Returns an array of feed items.
     */
    protected function getFeed(): array
    {
        // Try to get data from transient.
        $cacheKey = $this->getCacheKey();
        $data = get_transient($cacheKey);
        if ($data === false) {
            $params = [
                'PageIndex' => Settings::DEFAULT_PAGE_INDEX,
                'PageSize' => $this->settings->get('count'),
                'DetailLevel' => Service::FEED_DETAIL_LEVEL,
                'Format' => Service::FEED_FORMAT,
                'Tags' => $this->settings->get('tags'),
                'StartDate' => $this->settings->get('start_date'),
                'EndDate' => $this->settings->get('end_date'),
                'SearchTerm' => $this->settings->get('search_term'),
                'Regulatory' =>
                    $this->settings->get('view_mode') === Settings::DISPLAY_MODE_REGULATORY ?
                        'true' :
                        ($this->settings->get('view_mode') === Settings::DISPLAY_MODE_NON_REGULATORY ? 'false' : null),
            ];
            $response = $this->container->get(Service::class)->fetchFeed($params);
            $data = ($response ? $this->mapSources($response) : null);

            // Store transient data.
            if ($data && $this->settings->get('cache_expire') > 0) {
                set_transient(
                    $cacheKey,
                    $data,
                    $this->settings->get('cache_expire')
                );
            }
        }
        return ($data ?: []);
    }

    /**
     * @param stdClass $release
     * @param string $image_style
     * @param bool $use_https
     *
     * @return stdClass
     */
    protected function mapFeedItem(stdClass $release, string $image_style, bool $use_https = false): stdClass
    {
        $item = [];

        // Clean up data.
        $item['Title'] = sanitize_text_field($release->Title);
        $item['PublishDate'] = strtotime($release->PublishDate);
        $item['Intro'] = sanitize_text_field($release->Intro);
        $item['Body'] = sanitize_text_field($release->Body);
        if ($this->settings->get('internal_links')) {
            $item['CisionWireUrl'] = apply_filters('cision-block/filter/internal/link', get_bloginfo('url') . '/' . $this->settings->get('base_slug') . '/' . $release->EncryptedId, $release);
            $item['LinkTarget'] = '_self';
        } else {
            $item['CisionWireUrl'] = esc_url_raw($release->CisionWireUrl);
            $item['LinkTarget'] = '_blank';
        }
        $item['IsRegulatory'] = (int) $release->IsRegulatory;
        if (!empty($image_style)) {
            foreach ($release->Images as $image) {
                if ($use_https) {
                    $image->{$image_style} = str_replace('http:', 'https:', $image->{$image_style});
                }
                $item['Images'][] = (object) [
                    'DownloadUrl' => esc_url_raw($image->{$image_style}),
                    'Description' => sanitize_text_field($image->Description),
                    'Title' => sanitize_text_field($image->Title),
                ];
            }
        }

        // Let user modify the data.
        return (object) apply_filters('cision_map_source_item', $item, $release, $this->current_block_id);
    }

    /**
     * Check if an item is connected to any category.
     *
     * @param stdClass $item
     * @param array $categories
     *
     * @return bool
     */
    protected function hasCategory(stdClass $item, array $categories): bool
    {
        foreach ($item->Categories as $category) {
            if (in_array(strtolower($category->Name), $categories)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Creates an array of feed items.
     *
     * @param stdClass $feed
     *   A cision feed object.
     *
     * @return array
     *   An array of mapped feed items.
     */
    protected function mapSources(stdClass $feed): ?array
    {
        $items = [];
        $image_style = $this->settings->get('image_style');
        $use_https = $this->settings->get('use_https');
        $language = $this->settings->get('language');
        $types = $this->settings->get('types');
        $categories = array_filter(array_map('trim', explode(',', $this->settings->get('categories'))));
        if (isset($feed->Releases) && count($feed->Releases)) {
            foreach ($feed->Releases as $release) {
                if (!is_object($release) || in_array($release->InformationType, $types) === false) {
                    continue;
                }
                if ($language && $release->LanguageCode !== $language) {
                    continue;
                }
                if ($categories && !$this->hasCategory($release, $categories)) {
                    continue;
                }
                $items[] = $this->mapFeedItem($release, $image_style, $use_https);
            }

            if ($items) {
                $items = apply_filters('cision_block_sort', $items, $this->current_block_id);
            }
        }

        return $items ?: null;
    }
}
