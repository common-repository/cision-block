<?php

/**
 * Template name: Cision Block
 * Template Post Type: cision-block-post
 *
 * Template used to display a feed from Cision.
 *
 * @var string $id
 *   String id of block.
 * @var string $readmore
 *   Read more button text.
 * @var boolean $mark_regulatory
 *   Indicates if the items should be marked as regulatory and non-regulatory.
 * @var string $regulatory_text
 *   Text to display for regulatory feed items.
 * @var string $non_regulatory_text
 *   Text to display for non-regulatory feed items.
 * @var boolean $show_excerpt
 *   Display excerpts or not.
 * @var boolean $show_filters
 *   States if we are to display filters for the feed items or not.
 * @var string $filter_all_text
 *   Text for the 'all' filter button.
 * @var string $filter_regulatory_text
 *   Text for the 'regulatory' filter button.
 * @var string $filter_non_regulatory_text
 *   Text for the 'non-regulatory' filter button.
 * @var array $cision_feed
 *   An array of all feed items.
 * @var string $pager
 *   Markup for pager.
 * @var string $wrapper_attributes
 *   Attributes for wrapper element.
 * @var string $prefix
 *   String prefix to add to start of wrapper.
 * @var string $suffix
 *   String suffix to add to end of wrapper.
 * @var string $attributes
 *   Attributes for article wrapper element.
 * @var array $options
 *   Array of options.
 *
 * @package Cision Block
 * @since   1.0
 * @version 2.7.2
 */

?>
<?php if ($cision_feed) : ?>
    <section data-block-id="<?php echo $id; ?>"<?php echo $wrapper_attributes; ?>>
    <?php echo $prefix; ?>
        <?php if ($show_filters) : ?>
            <ul class="cision-feed-filters">
                <?php if ($filter_all_text !== '<none>') : ?>
                <li>
                    <button class="all">
                        <a href="?cb_id=<?php echo $id; ?>&cb_filter=1"><?php echo $filter_all_text; ?></a>
                    </button>
                </li>
                <?php endif; ?>
                <?php if ($filter_regulatory_text !== '<none>') : ?>
                <li>
                    <button class="regulatory">
                        <a href="?cb_id=<?php echo $id; ?>&cb_filter=2"><?php echo $filter_regulatory_text; ?></a>
                    </button>
                </li>
                <?php endif; ?>
                <?php if ($filter_non_regulatory_text !== '<none>') : ?>
                <li>
                    <button class="non-regulatory">
                        <a href="?cb_id=<?php echo $id; ?>&cb_filter=3"><?php echo $filter_non_regulatory_text; ?></a>
                    </button>
                </li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
        <?php foreach ($cision_feed as $item) : ?>
        <article data-regulatory="<?php echo $item->IsRegulatory ?: 0; ?>"<?php echo $attributes; ?>>
            <h2><?php echo esc_html($item->Title); ?></h2>
            <time><?php echo date($options['date_format'], $item->PublishDate); ?></time>
            <?php if ($mark_regulatory && (($item->IsRegulatory && $regulatory_text !== '<none>') || (!$item->IsRegulatory && $non_regulatory_text !== '<none>'))) : ?>
            <span class="cision-feed-regulatory"><?php echo $item->IsRegulatory ? $regulatory_text : $non_regulatory_text; ?></span>
            <?php endif; ?>
            <?php if (isset($item->Images[0])) : ?>
            <span class="cision-feed-item-media">
            <img
                    src="<?php echo esc_url($item->Images[0]->DownloadUrl); ?>"
                    alt="<?php echo esc_html($item->Images[0]->Description); ?>"
                    title="<?php echo esc_html($item->Images[0]->Title); ?>"
            />
            </span>
            <?php endif; ?>
            <?php if ($show_excerpt) : ?>
            <p>
                <?php echo wp_trim_words(esc_html($item->Intro ?: $item->Body)); ?>
            </p>
            <?php endif; ?>
            <?php if (isset($item->CisionWireUrl, $item->LinkTarget) && $readmore) : ?>
            <a
                    href="<?php echo esc_url($item->CisionWireUrl); ?>"
                    target="<?php echo $item->LinkTarget; ?>"
            ><?php echo $readmore; ?></a>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
    <?php echo $pager; ?>
    <?php echo $suffix; ?>
    </section>
<?php endif; ?>