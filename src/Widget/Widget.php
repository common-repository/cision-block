<?php

namespace CisionBlock\Widget;

use CisionBlock\Backend\Backend;
use CisionBlock\Cision\Service;
use CisionBlock\Psr\Container\ContainerInterface;
use CisionBlock\Settings\Settings;
use function cb_get_container;

class Widget extends \CisionBlock\Plugin\Widget\Widget
{
    /** @var ContainerInterface */
    protected ContainerInterface $container;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->container = cb_get_container();
        $this->id_base = 'cision_block_widget';
        $this->description = __('Display pressreleases from Cision.', 'cision-block');
        $this->name = __('Cision Block', 'cision-block');
        parent::__construct();
    }

    /**
     * @inheridoc
     */
    public function render(array $instance): void
    {
        if (!$instance) {
            return;
        }
        $args = [
            'source_uid' => $instance['source'],
            'count' => $instance['count'],
            'items_per_page' => $instance['items_per_page'],
            'flush' => var_export($instance['cache_expire'] === 0, true),
            'view' => $instance['view_mode'],
            'types' => implode(', ', $instance['types']),
            'start' => $instance['start_date'],
            'end' => $instance['end_date'],
            'image_style' => $instance['image_style'],
            'show_excerpt' => $instance['show_excerpt'],
            'language' => $instance['language'],
            'readmore' => $instance['readmore'] ?: null,
            'date_format' => $instance['date_format'],
            'widget' => $this->id,
            'id' => $this->id,
            'show_filters' => $instance['show_filters'],
            'filter_all_text' => $instance['filter_all_text'],
            'filter_regulatory_text' => $instance['filter_regulatory_text'],
            'filter_non_regulatory_text' => $instance['filter_non_regulatory_text'],
        ];

        $shortcode_args = '';
        foreach ($args as $key => $value) {
            $shortcode_args .= $key . '="' . $value . '" ';
        }
        $shortcode_args = rtrim($shortcode_args);

        echo do_shortcode('[cision-block ' . $shortcode_args . ']');
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form($instance): string
    {
        // Make sure so keys are set.
        $defaults = [
            'count' => Settings::DEFAULT_ITEM_COUNT,
            'source' => '',
            'types' => [Settings::DEFAULT_FEED_TYPE],
            'items_per_page' => Settings::DEFAULT_ITEMS_PER_PAGE,
            'view_mode' => Settings::DEFAULT_DISPLAY_MODE,
            'cache_expire' => Settings::DEFAULT_CACHE_LIFETIME,
            'start_date' => '',
            'end_date' => '',
            'image_style' => Settings::DEFAULT_IMAGE_STYLE,
            'show_excerpt' => true,
            'language' => '',
            'readmore' => Settings::DEFAULT_READ_MORE_TEXT,
            'date_format' => Settings::DEFAULT_DATE_FORMAT,
            'show_filters' => false,
            'filter_all_text' => Settings::DEFAULT_FILTER_ALL_TEXT,
            'filter_regulatory_text' => Settings::DEFAULT_FILTER_REGULATORY_TEXT,
            'filter_non_regulatory_text' => Settings::DEFAULT_FILTER_NON_REGULATORY_TEXT,
        ];
        foreach ($defaults as $key => $value) {
            if (!isset($instance[$key])) {
                $instance[$key] = $value;
            }
        }

        $feed_types = $this->container->get(Service::class)->getFeedTypes();
        $type_options = '';
        foreach ($feed_types as $key => $value) {
            $type_options .= '<option value="' . $key . '"' .
                selected(in_array($key, $instance['types']), true, false) . '>' .
                $value . '</option>';
        }

        $image_styles = $this->container->get(Service::class)->getImageStyles();
        $image_style_options = '';
        foreach ($image_styles as $key => $image_style) {
            $image_style_options .= '<option value="' . $key . '"' .
                selected($key === $instance['image_style'], true, false) .
                '>' . $image_style['label'] . '</option>';
        }

        $output = '';
        $output .= '<p>';
        $output .= '<label for="' . $this->get_field_id('source') . '">' . __('Cision Feed source id', 'cision-block') . '</label>';
        $output .= '<input type="text" class="widefat" name="' . $this->get_field_name('source') . '" value="' . sanitize_text_field($instance['source']) . '"/>';
        $output .= '</p>';
        $output .= '<p>';
        $output .= '<label for="' . $this->get_field_id('count') . '">' . __('Number of feed items', 'cision-block') . ': </label>';
        $output .= '<input class="tiny-text" type="number" min="1" max="' . Settings::MAX_ITEMS_PER_FEED . '" name="' . $this->get_field_name('count') . '" value="' . $instance['count'] . '" />';
        $output .= '</p>';
        $output .= '<p>';
        $output .= '<label for="' . $this->get_field_id('items_per_page') . '">' . __('Items per page', 'cision-block') . ': </label>';
        $output .= '<input class="tiny-text" type="number" min="0" max="' . Settings::MAX_ITEMS_PER_PAGE . '" name="' . $this->get_field_name('items_per_page') . '" value="' . $instance['items_per_page'] . '" />';
        $output .= '</p>';
        $output .= '<p>';
        $output .= '<label for="' . $this->get_field_id('types') . '">' . __('Type of feed items', 'cision-block') . '</label>';
        $output .= '<select class="widefat" name="' . $this->get_field_name('types') . '[]" multiple>';
        $output .= $type_options;
        $output .= '</select>';
        $output .= '</p>';
        $output .= '<p>';
        $output .= '<label for="' . $this->get_field_id('start_date') . '">' . __('Start date', 'cision-block') . ': </label>';
        $output .= '<input type="date" name="' . $this->get_field_name('start_date') . '" value="' . $instance['start_date'] . '" />';
        $output .= '</p>';
        $output .= '<p>';
        $output .= '<label for="' . $this->get_field_id('end_date') . '">' . __('End date', 'cision-block') . ': </label>';
        $output .= '<input type="date" name="' . $this->get_field_name('end_date') . '" value="' . $instance['end_date'] . '" />';
        $output .= '</p>';
        $output .= '<p>';
        $output .= '<label for="' . $this->get_field_id('show_filters') . '">' . __('Show filters', 'cision-block') . ': </label>';
        $output .= '<input type="hidden" name="' . $this->get_field_name('show_filters') . '" value="off" />';
        $output .= '<input type="checkbox" name="' . $this->get_field_name('show_filters') . '"' . checked($instance['show_filters'], true, false) . ' />';
        $output .= '</p>';
        $output .= '<p>';
        $output .= '<label for="' . $this->get_field_id('filter_all_text') . '">' . __('All item filter text', 'cision-block') . ': </label>';
        $output .= '<input type="text" name="' . $this->get_field_name('filter_all_text') . '" value="' . $instance['filter_all_text'] . '" />';
        $output .= '</p>';
        $output .= '<p>';
        $output .= '<p>';
        $output .= '<label for="' . $this->get_field_id('filter_regulatory_text') . '">' . __('Regulatory item filter text', 'cision-block') . ': </label>';
        $output .= '<input type="text" name="' . $this->get_field_name('filter_regulatory_text') . '" value="' . $instance['filter_regulatory_text'] . '" />';
        $output .= '</p>';
        $output .= '<p>';
        $output .= '<p>';
        $output .= '<label for="' . $this->get_field_id('filter_non_regulatory_text') . '">' . __('Non-regulatory item filter text', 'cision-block') . ': </label>';
        $output .= '<input type="text" name="' . $this->get_field_name('filter_non_regulatory_text') . '" value="' . $instance['filter_non_regulatory_text'] . '" />';
        $output .= '</p>';
        $output .= '<p>';
        $output .= '<label for="' . $this->get_field_id('readmore') . '">' . __('Read more text', 'cision-block') . ': </label>';
        $output .= '<input type="text" name="' . $this->get_field_name('readmore') . '" value="' . $instance['readmore'] . '" />';
        $output .= '</p>';
        $output .= '<p>';
        $output .= '<label for="' . $this->get_field_id('language') . '">' . __('Language', 'cision-block') . ': </label>';
        $output .= '<select name="' . $this->get_field_name('language') . '">';
        $output .= '<option value="">' . __('Select', 'cision-block') . '</option>';
        foreach ($this->container->get(Service::class)->getLanguages() as $key => $language) :
            $output .= '<option value="' . $key . '"' . selected($instance['language'] === $key, true, false) . '>' . $language . '</option>';
        endforeach;
        $output .= '</select>';
        $output .= '</p>';
        $output .= '<p>';
        $output .= '<label for="' . $this->get_field_id('view_mode') . '">' . __('View mode', 'cision-block') . ': </label>';
        $output .= '<select class="widefat" name=' . $this->get_field_name('view_mode') . '">';
        $output .= '<option value="' . Settings::DISPLAY_MODE_ALL . '"' . selected($instance['view_mode'] === Settings::DISPLAY_MODE_ALL, true, false) . '>' . __('All', 'cision-block') . '</option>';
        $output .= '<option value="' . Settings::DISPLAY_MODE_REGULATORY . '"' . selected($instance['view_mode'] === Settings::DISPLAY_MODE_REGULATORY, true, false) . '>' . __('Regulatory', 'cision-block') . '</option>';
        $output .= '<option value="' . Settings::DISPLAY_MODE_NON_REGULATORY . '"' . selected($instance['view_mode'] === Settings::DISPLAY_MODE_NON_REGULATORY, true, false) . '>' . __('Non-regulatory', 'cision-block') . '</option>';
        $output .= '</select>';
        $output .= '</p>';
        $output .= '<p>';
        $output .= '<label for="' . $this->get_field_id('date_format') . '">' . __('Date format', 'cision-block') . ': </label>';
        $output .= '<input type="text" name="' . $this->get_field_name('date_format') . '" value="' . $instance['date_format'] . '" />';
        $output .= '</p>';
        $output .= '<p>';
        $output .= '<label for="' . $this->get_field_id('image_style') . '">' . __('Image style', 'cision-block') . ': </label>';
        $output .= '<select class="widefat" name=' . $this->get_field_name('image_style') . '">';
        $output .= '<option value="">' . __('Select', 'cision-block') . '</option>';
        $output .= $image_style_options;
        $output .= '</select>';
        $output .= '</p>';
        $output .= '<p>';
        $output .= '<label for="' . $this->get_field_id('show_excerpt') . '">' . __('Show excerpt', 'cision-block') . ': <label>';
        $output .= '<input type="hidden" name="' . $this->get_field_name('show_excerpt') . '" value="off" />';
        $output .= '<input type="checkbox" name="' . $this->get_field_name('show_excerpt') . '"' . checked($instance['show_excerpt'], true, false) . ' />';
        $output .= '</p>';
        $output .= '<p>';
        $output .= '<label for="' . $this->get_field_id('cache_expire') . '">' . __('Cache lifetime', 'cision-block') . ': </label>';
        $output .= '<input class="tiny-text" type="number" min="0" name="' . $this->get_field_name('cache_expire') . '" value="' . $instance['cache_expire'] . '" />';
        $output .= '</p>';
        echo $output;
        return 'form';
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance): array
    {
        $config = $this->container->get(Settings::class)->verify($new_instance);
        $config['source'] = $config['source_uid'];
        return $config;
    }
}
