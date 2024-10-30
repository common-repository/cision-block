<div class="wrap">
    <?php $this->displayTabs(); ?>
    <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST">
        <?php wp_nonce_field('cision-block-settings-action', 'cision-block-settings-nonce'); ?>
        <input type="hidden" name="action" value="cision_block_save_settings" />
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="show_filters"><?php _e('Show filters', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="hidden" id="hidden_show_filters" name="show_filters" value="0" />
                    <input type="checkbox" id="show_filters" name="show_filters"<?php checked($this->settings->get('show_filters')); ?> />
                    <p class="description"><?php _e('Enable filtering of feed items.', 'cision-block'); ?></p>
                    <p class="description"><?php _e(sprintf('You can use the special value <b>%s</b> to hide any of the buttons.', htmlspecialchars('<none>')), 'cision-block'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="filter_all_text"><?php _e('All item filter text', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="text" class="regular-text" id="filter_all_text" name="filter_all_text" value="<?php echo $this->settings->get('filter_all_text'); ?>"<?php disabled(!$this->settings->get('show_filters')); ?>>
                    <p class="description"><?php _e('Button text for \'all\' filter.', 'cision-block'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="filter_regulatory_text"><?php _e('Regulatory item filter text', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="text" class="regular-text" id="filter_regulatory_text" name="filter_regulatory_text" value="<?php echo $this->settings->get('filter_regulatory_text'); ?>"<?php disabled(!$this->settings->get('show_filters')); ?>>
                    <p class="description"><?php _e('Button text for \'regulatory\' filter.', 'cision-block'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="filter_non_regulatory_text"><?php _e('Non-regulatory item filter text', 'cision-block'); ?></label>
                </th>
                <td>
                    <input type="text" class="regular-text" id="filter_non_regulatory_text" name="filter_non_regulatory_text" value="<?php echo $this->settings->get('filter_non_regulatory_text'); ?>"<?php disabled(!$this->settings->get('show_filters')); ?>>
                    <p class="description"><?php _e('Button text for \'non-regulatory\' filter.', 'cision-block'); ?></p>
                </td>
            </tr>
        </table>
        <?php echo get_submit_button(__('Save settings', 'cision-block'), 'primary', 'cision-block-filters'); ?>
    </form>
</div>
