<?php

namespace App\Filament\Resources\MessageSessionResource\Pages;

use App\Filament\Resources\MessageSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMessageSession extends EditRecord
{
    protected static string $resource = MessageSessionResource::class;

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
