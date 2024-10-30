<?php

/**
 * Template used to display a single feed item from Cision.
 *
 * @var stdClass $CisionItem
 *   A feed item. A reference of all available fields can be found here: https://websolutions.ne.cision.com/documents/P2_Feed.pdf
 * @var bool $displayFiles
 *   Display any attachments connected to the release.
 *
 * @package Cision Block
 * @since 2.0.0
 * @version 2.9.2
 */

get_header();

global $CisionItem;
global $displayFiles;
global $attachmentField;
?>
    <div class="wrap">
        <section id="primary" class="content-area">
            <main id="main" class="site-main">
                <article class="entry cision-block-post">
                    <header class="entry-header">
                        <?php echo '<h1 class="entry-title">' . $CisionItem->Title . '</h1>'; ?>
                    </header>

                    <div class="entry-content">
                        <?php echo $CisionItem->HtmlBody; ?>
                    </div><!-- .entry-content -->

                    <?php if ($displayFiles) : ?>
                        <?php if (count($CisionItem->Files)) : ?>
                            <ul class="attachments">
                            <?php foreach ($CisionItem->Files as $file) : ?>
                                <li><a href="<?php echo $file->Url; ?>"><?php echo $file->{$attachmentField}; ?></a></li>
                            <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    <?php endif; ?>
                </article>
            </main><!-- #main -->
        </section><!-- #primary -->
    </div><!-- .wrap -->
<?php
get_footer();
