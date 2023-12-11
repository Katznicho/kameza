<?php

namespace App\Filament\Resources\UssdSesionResource\Pages;

use App\Filament\Resources\UssdSesionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUssdSesion extends EditRecord
{
    protected static string $resource = UssdSesionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            // Actions\DeleteAction::make(),
            // Actions\ForceDeleteAction::make(),
            // Actions\RestoreAction::make(),
        ];
    }
}
