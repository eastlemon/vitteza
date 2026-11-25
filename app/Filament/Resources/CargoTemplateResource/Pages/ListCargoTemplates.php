<?php

namespace App\Filament\Resources\CargoTemplateResource\Pages;

use App\Filament\Resources\CargoTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCargoTemplates extends ListRecords
{
    protected static string $resource = CargoTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Visibility — via CargoTemplatePolicy::create (admin / cargo.manage).
            CreateAction::make(),
        ];
    }
}
