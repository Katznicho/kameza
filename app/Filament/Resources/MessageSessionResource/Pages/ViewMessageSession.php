<?php

namespace App\Filament\Resources\MessageSessionResource\Pages;

use App\Filament\Resources\MessageSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMessageSession extends ViewRecord
{
    protected static string $resource = MessageSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }
}
