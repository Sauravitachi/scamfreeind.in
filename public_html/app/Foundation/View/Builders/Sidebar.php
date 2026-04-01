<?php

namespace App\Foundation\View\Builders;

use Illuminate\Foundation\Auth\User;
use stdClass;

abstract class Sidebar
{
    protected array $menu = [];

    protected string $currentMenuName = '';

    public function __construct(
        protected User $user
    ) {}

    public static function menu(User $user): self
    {
        return new static($user);
    }

    private function createData(string $title, ?string $icon = null, ?string $url = null): stdClass
    {
        $data = new stdClass;
        $data->title = $title;
        $data->icon = $icon;
        $data->url = $url ?? 'javascript:;';

        return $data;
    }

    protected function add(string $name, string $title, ?string $icon = null, ?string $url = null)
    {
        $this->currentMenuName = $name;

        $data = $this->createData($title, $icon, $url);
        $data->hasSubmenu = false;

        $this->menu[$name] = $data;

        return $this;
    }

    protected function addSubmenu(string $title, ?string $icon = null, ?string $url = null)
    {
        if (isset($this->menu[$this->currentMenuName])) {

            $data = $this->createData($title, $icon, $url);

            $this->menu[$this->currentMenuName]->submenu[] = $data;
            $this->menu[$this->currentMenuName]->hasSubmenu = true;
        }
    }

    protected function handle(): void {}

    public function get(): array
    {
        if (empty($this->menu)) {
            $this->handle();
        }

        return $this->menu;
    }
}
