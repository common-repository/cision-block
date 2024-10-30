<?php

namespace CisionBlock\Addon;

use CisionBlock\Plugin\Settings\Settings;

interface AddonInterface
{
    public function getModuleName(): string;
    public function getSettings(): Settings;
    public function getTabs(): array;
    public function getEndpoints(): array;
    public function saveSettings(array $args = []): void;
    public function renderSettings(): void;
    public function delete(): void;
}
