<?php

namespace App\Filament\Resources\Portfolio\ContactMessageResource\Pages;

use App\Filament\Resources\Portfolio\ContactMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateContactMessage extends CreateRecord
{
    protected static string $resource = ContactMessageResource::class;
}
