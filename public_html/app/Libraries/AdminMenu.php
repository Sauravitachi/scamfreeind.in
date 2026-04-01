<?php

namespace App\Libraries;

class AdminMenu
{
    private array $menu = [];

    private string $currentMenuName = '';

    public function add(string $name, array $data)
    {
        $this->currentMenuName = $name;
        $this->menu[$name] = $data;

        return $this;
    }

    public function addSubmenu(array $data)
    {
        if (isset($this->menu[$this->currentMenuName])) {
            $this->menu[$this->currentMenuName]['submenu'][] = $data;
        }
    }

    public function array(): array
    {
        return $this->menu;
    }
}
