<?php

namespace App\Filament\Employee\Pages;

use Filament\Pages\Page;

class Setting extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Setting';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.employee.pages.setting';
}
