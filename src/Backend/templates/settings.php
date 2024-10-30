<?php

use CisionBlock\Cision\Service;
use CisionBlock\Settings\Settings;

$templates = get_page_templates(null, 'cision-block-post');
$this->settings = apply_filters('cision-block/filter/settings', $this->settings);

?>
<div class="wrap">
    <?php do_action('cision_block_admin_notices'); ?>
    <h1></h1>
    <?php $this->displayTabs(); ?>
    <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST">
        <?php wp_nonce_field('cision-block-settings-action', 'cision-block-settings-nonce'); ?>
        <input type="hidden" name="action" value="cision_block_save_settings" />
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="source_uid"><?php _e('Cision Feed source id', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="text" class="regular-text" id="source_uid" name="source_uid" value="<?php echo sanitize_text_field($this->settings->get('source_uid')); ?>"/>
                    <p class="description"><?php _e('A valid unique JSON identifier for your cision feed.', 'cision-block'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="types"><?php _e('Type of feed items', 'cision-block'); ?></label>
                </th>
                <td>
                    <select class="regular-text" id="types" name="types[]" multiple>
                        <?php foreach ($this->container->get(Service::class)->getFeedTypes() as $key => $value) : ?>
                            <option value="<?php echo $key; ?>"<?php selected(in_array($key, $this->settings->get('types'))); ?>><?php echo $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php _e('Type of feed items to include.', 'cision-block'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="count"><?php _e('Number of feed items', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="number" min="1" max="<?php echo $this->settings->get('max_items', Settings::MAX_ITEMS_PER_FEED); ?>" id="count" name="count" value="<?php echo intval($this->settings->get('count')); ?>" />
                    <p class="description"><?php _e('The maximum number of items in the feed.', 'cision-block'); ?></p>
                    <?php if (!isset($this->addons['fetch'])) : ?>
                        <p class="description"><?php _e(sprintf('Add support to display up to 200 news using the <a href="%s">fetch addon</a>.', cb_fs()->get_addons_url()), 'cision-block'); ?></p>
                        <p class="description"><?php _e(sprintf('You can also switch to the <a href="?page=cision-block&premium-info=1">%s</a> in order to displaying all existing news.', 'premium version'), 'cision-block'); ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="items_per_page"><?php _e('Items per page', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="number" min="0" max="<?php echo $this->settings->get('max_items_per_page', Settings::MAX_ITEMS_PER_PAGE); ?>" id="items_per_page" name="items_per_page" value="<?php echo $this->settings->get('items_per_page'); ?>" />
                    <p class="description"><?php _e('Number of items on each page (set to 0 to disable).', 'cision-block'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="language"><?php _e('Language', 'cision-block'); ?></label>
                </th>
                <td>
                    <select id="language" name="language">
                        <option value=""><?php _e('Select'); ?></option>
                        <?php foreach ($this->container->get(Service::class)->getLanguages() as $code => $name) : ?>
                            <option value="<?php echo $code; ?>"<?php selected($code === $this->settings->get('language')) ?>><?php echo $name; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php _e('The language for each feed item.'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="categories"><?php _e('Categories', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="text" class="regular-text" id="categories" name="categories" value="<?php echo $this->settings->get('categories'); ?>"<?php disabled(true); ?>>
                    <p class="description"><?php _e('Defines a filter on categories, this will return releases with connected to these categories. One or several
                            categories can be provided separated with a comma.', 'cision-block'); ?></p>
                    <p class="description"><?php _e(sprintf('This feature is available in the <a href="?page=cision-block&premium-info=1">%s</a>.', 'premium version'), 'cision-block'); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="tags"><?php _e('Tags', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="text" class="regular-text" id="tags" name="tags" value="<?php echo $this->settings->get('tags'); ?>"<?php disabled(true); ?>>
                    <p class="description"><?php _e('Defines a filter on tags, this will return releases with these tags. One or several
                            tags can be provided separated with a comma.', 'cision-block'); ?></p>
                    <p class="description"><?php _e(sprintf('This feature is available in the <a href="?page=cision-block&premium-info=1">%s</a>.', 'premium version'), 'cision-block'); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="start"><?php _e('Start date', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="date" id="start" name="start" value="<?php echo $this->settings->get('start_date'); ?>" />
                    <p class="description"><?php _e('Defines the start date of the date interval the press releases and/or reports are collected from. The format is 2001-12-31.', 'cision-block'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="end"><?php _e('End date', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="date" id="end" name="end" value="<?php echo $this->settings->get('end_date'); ?>" />
                    <p class="description"><?php _e('Defines the end date of the date interval the press releases and/or reports are collected from. The format is 2001-12-31.', 'cision-block'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mark_regulatory"><?php _e('Show regulatory/non-regulatory', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="hidden" id="hidden_mark_regulatory" name="mark_regulatory" value="0" />
                    <input type="checkbox" id="mark_regulatory" name="mark_regulatory"<?php checked($this->settings->get('mark_regulatory')); ?><?php disabled(true); ?> />
                    <p class="description"><?php _e('Emphasis if a release if regulatory or non-regulatory.', 'cision-block'); ?></p>
                    <p class="description"><?php _e(sprintf('Use the special value <b>%s</b> to skip marking a type of releases.', htmlspecialchars('<none>')), 'cision-block'); ?></p>
                    <p class="description"><?php _e(sprintf('This feature is available in the <a href="?page=cision-block&premium-info=1">%s</a>.', 'premium version'), 'cision-block'); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="regulatory_text"><?php _e('Regulatory item text', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="text" class="regular-text" id="regulatory_text" name="regulatory_text" value="<?php echo $this->settings->get('regulatory_text'); ?>"<?php disabled(!$this->settings->get('mark_regulatory')); ?>>
                    <p class="description"><?php _e('Text to display for regulatory items.', 'cision-block'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="non_regulatory_text"><?php _e('Non-regulatory item text', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="text" class="regular-text" id="non_regulatory_text" name="non_regulatory_text" value="<?php echo $this->settings->get('non_regulatory_text'); ?>"<?php disabled(!$this->settings->get('mark_regulatory')); ?>>
                    <p class="description"><?php _e('Text to display for non-regulatory items.', 'cision-block'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="search_term"><?php _e('Search term', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="text" class="regular-text" id="search_term" name="search_term" value="<?php echo $this->settings->get('search_term'); ?>"<?php disabled(true); ?>>
                    <p class="description"><?php _e('Free text search in release titles.', 'cision-block'); ?></p>
                    <p class="description"><?php _e(sprintf('This feature is available in the <a href="?page=cision-block&premium-info=1">%s</a>.', 'premium version'), 'cision-block'); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="readmore"><?php _e('Read more text', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="text" id="readmore" name="readmore" value="<?php echo $this->settings->get('readmore'); ?>" />
                    <p class="description"><?php _e('The \'Read more\' button text. If this value is empty then the button will not be visible.', 'cision-block'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="view"><?php _e('Display mode', 'cision-block'); ?></label>
                </th>
                <td>
                    <select id="view" name="view">
                        <option value="1"<?php selected($this->settings->get('view_mode') === Settings::DISPLAY_MODE_ALL); ?>><?php _e('All', 'cision-block'); ?></option>
                        <option value="2"<?php selected($this->settings->get('view_mode') === Settings::DISPLAY_MODE_REGULATORY); ?>><?php _e('Regulatory', 'cision-block'); ?></option>
                        <option value="3"<?php selected($this->settings->get('view_mode') === Settings::DISPLAY_MODE_NON_REGULATORY); ?>><?php _e('Non-regulatory', 'cision-block'); ?></option>
                    </select>
                    <p class="description"><?php _e('What kind of feed items to display.', 'cision-block'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="date_format"><?php _e('Date format', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="text" id="date_format" name="date_format" value="<?php echo $this->settings->get('date_format'); ?>" />
                    <p class="description"><?php _e('The format to use for dates.', 'cision-block'); ?></p>
                    <p class="description"><?php _e('You can read more about the date/time formats: ', 'cision-block'); ?><a target="_blank" href="http://php.net/manual/en/datetime.formats.php"><?php _e('Here', 'cision-block'); ?></a></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="image_style"><?php _e('Image style', 'cision-block'); ?></label>
                </th>
                <td>
                    <select id="image_style" name="image_style">
                        <option value=""><?php _e('Select', 'cision-block'); ?></option>
                        <?php foreach ($this->container->get(Service::class)->getImageStyles() as $key => $value) : ?>
                        <option value="<?php echo $key; ?>"<?php selected($this->settings->get('image_style') === $key); ?>><?php echo $value['label']; ?></option>
                        <?php endforeach; ?>
                        </select>
                    <p class="description"><?php _e('The image format to use. If not set no images will be displayed.', 'cision-block'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="show_excerpt"><?php _e('Show excerpt', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="hidden" id="hidden_show_excerpt" name="show_excerpt" value="0" />
                    <input type="checkbox" id="show_excerpt" name="show_excerpt"<?php checked($this->settings->get('show_excerpt')); ?> />
                    <p class="description"><?php _e('Display excerpt for each feed item.', 'cision-block'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="show_files"><?php _e('Show files', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="hidden" id="hidden_show_files" name="show_files" value="0" />
                    <input type="checkbox" id="show_files" name="show_files"<?php checked($this->settings->get('show_files')); ?><?php disabled(true); ?> />
                    <p class="description"><?php _e('Display attachments on article page.', 'cision-block'); ?></p>
                    <p class="description"><?php _e(sprintf('This feature is available in the <a href="?page=cision-block&premium-info=1">%s</a>.', 'premium version'), 'cision-block'); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="attachment_field"><?php _e('Attachment field', 'cision-block'); ?></label>
                </th>
                <td>
                    <select id="attachment_field" name="attachment_field"<?php disabled(true); ?>>
                        <option value="Description"<?php selected($this->settings->get('attachment_field') === 'Description'); ?>><?php _e('Description', 'cision-block'); ?></option>
                        <option value="FileName"<?php selected($this->settings->get('attachment_field') === 'FileName'); ?>><?php _e('FileName', 'cision-block'); ?></option>
                        <option value="Title"<?php selected($this->settings->get('attachment_field') === 'Title'); ?>><?php _e('Title', 'cision-block'); ?></option>
                    </select>
                    <p class="description"><?php _e('The field to use for attachment labels.', 'cision-block'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="use_https"><?php _e('Use https', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="hidden" id="hidden_use_https" name="use_https" value="0" />
                    <input type="checkbox" id="use_https" name="use_https"<?php checked($this->settings->get('use_https')); ?> />
                    <p class="description"><?php _e('Ensures that all images is handled over https.', 'cision-block'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="exclude_css"><?php _e('Exclude css', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="hidden" id="hidden_exclude_css" name="exclude_css" value="0" />
                    <input type="checkbox" id="exclude_css" name="exclude_css"<?php checked($this->settings->get('exclude_css')); ?> />
                    <p class="description"><?php _e('Do not load stylesheet.', 'cision-block'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="template"><?php _e('Template', 'cision-block'); ?></label>
                </th>
                <td>
                    <select id="template" name="template">
                        <option value="0"><?php _e('Select', 'cision-block'); ?></option>
                        <?php foreach ($templates as $key => $template) : ?>
                            <option value="<?php echo $template; ?>"<?php selected($this->settings->get('template') === $template); ?>><?php echo $key; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php _e('The template used for rendering the feed.', 'cision-block'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cache_expire"><?php _e('Cache lifetime', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="number" min="0" id="cache_expire" name="cache_expire" value="<?php echo $this->settings->get('cache_expire'); ?>" />
                    <p class="description"><?php _e('The cache lifetime.', 'cision-block'); ?></p>
                </td>
            </tr>
        </table>
        <?php echo get_submit_button(__('Save settings', 'cision-block'), 'primary', 'cision-block-settings'); ?>
    </form>
</div>
