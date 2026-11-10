<?php

namespace App\Filament\Resources\Loans\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LoanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('book_id')
                    ->relationship('book', 'id')
                    ->required(),
                DatePicker::make('tgl_pinjam')
                    ->required(),
                DatePicker::make('tgl_batas')
                    ->required(),
                DatePicker::make('tgl_kembali'),
                TextInput::make('status')
                    ->required()
                    ->default('dipinjam'),
            ]);
    }
}
