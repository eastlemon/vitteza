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
            // Раздел только для администратора (EmployeeResource::canViewAny),
            // create — тоже явно через override canCreate в ресурсе.
            CreateAction::make(),
        ];
    }
}
