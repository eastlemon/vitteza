<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // The section is admin-only (EmployeeResource::canViewAny),
            // create — also explicitly via the canCreate override in the resource.
            CreateAction::make(),
        ];
    }
}
