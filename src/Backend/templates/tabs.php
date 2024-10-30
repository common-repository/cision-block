<h2 class="nav-tab-wrapper">
    <?php foreach ($this->getTabs() as $key => $item) : ?>
        <?php $active = ($key === $this->getCurrentTab() ? ' nav-tab-active' : ''); ?>
        <a class="nav-tab<?php echo $active; ?>"
           href="<?php echo add_query_arg(['page' => 'cision-block', 'tab' => $key], self::PARENT_MENU_SLUG); ?>"><?php echo $item; ?></a>
    <?php endforeach; ?>
</h2>
