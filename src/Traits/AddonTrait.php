<?php

namespace CisionBlock\Traits;

use CisionBlock\Addon\AddonInterface;

trait AddonTrait
{
    /**
     * @var AddonInterface[]
     */
    protected array $addons = [];

    /**
     * Registers and loads any addons.
     *
     * @param string $className
     * @return void
     */
    public function registerAddon(string $className): void
    {
        if (class_exists($className)) {
            $addon = new $className();
            if ($addon instanceof AddonInterface) {
                $this->addons[$addon->getModuleName()] = $addon;
            }
        }
    }
}
