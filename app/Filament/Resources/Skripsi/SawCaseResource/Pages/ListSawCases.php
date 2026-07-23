<?php

namespace App\Filament\Resources\Skripsi\SawCaseResource\Pages;

use App\Filament\Resources\Skripsi\SawCaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSawCases extends ListRecords
{
    protected static string $resource = SawCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
