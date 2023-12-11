<?php

namespace App\Filament\Resources\UssdSesionResource\Pages;

use App\Filament\Resources\UssdSesionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUssdSesion extends ViewRecord
{
    protected static string $resource = UssdSesionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }
}
