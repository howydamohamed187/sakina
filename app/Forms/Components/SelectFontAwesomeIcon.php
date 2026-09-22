<?php

namespace App\Forms\Components;

use App\Support\SocialIcons;
use Filament\Forms\Components\Select;

class SelectFontAwesomeIcon extends Select
{
    protected string $mode = 'social';

    protected function setUp(): void
    {
        parent::setUp();

        $this->native(false)
            ->searchable()
            ->allowHtml()
            ->options(fn (): array => SocialIcons::toSelect());
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): static
    {
        $this->mode = $mode;

        return $this;
    }
}
