<?php

namespace CisionBlock\Plugin\Widget;

abstract class Widget extends \WP_Widget
{
    /**
     * @var string
     */
    protected string $description;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(
            $this->id_base,
            $this->name,
            [
                'description' => $this->description,
            ]
        );

        $this->register();
    }

    /**
     * @param array $instance
     * @return mixed
     */
    abstract public function render(array $instance);

    /**
     * Register the widget.
     */
    private function register(): void
    {
        $class = get_called_class();
        add_action('widgets_init', function () use ($class) {
            register_widget($class);
        });
    }

    /**
     *
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance): void
    {
        /**
         * Add arguments to widget.
         *
         * @var $before_widget string
         * @var $before_title string
         * @var $after_title string
         * @var $after_widget string
         */
        extract($args);

        echo $before_widget;

        // Display title if not empty.
        if (!empty($instance['title'])) {
            echo $before_title . apply_filters('widgets_title', $instance['title']) . $after_title;
        }

        // Render the widget content.
        $this->render($instance);

        // After widget.
        echo $after_widget;
    }
}
