<?php

namespace App\Filament\Resources\Portfolio\ContactMessageResource\Pages;

use App\Filament\Resources\Portfolio\ContactMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContactMessages extends ListRecords
{
    protected static string $resource = ContactMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
