<?php

namespace App\Filament\Resources\Books\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Book;
use App\Models\Loan;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class BooksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('judul')
                    ->searchable(),
                TextColumn::make('penerbit')
                    ->searchable(),
                TextColumn::make('penulis')
                    ->searchable(),
                TextColumn::make('tahun_rilis'),
                TextColumn::make('stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('pinjam')
                ->icon('heroicon-o-book-open')
                ->requiresConfirmation()
                ->visible(fn () => ! auth()->user()->isAdmin())
                ->disabled(fn (Book $record) => $record->stock <1)
                ->action(function (Book $record) {
                    Loan::create([
                            'user_id' => auth()->id(),
                            'book_id' => $record->id,
                            'tgl_pinjam' => now(),
                            'tgl_batas' => now()->addDays(7),
                        ]);

                        $record -> decrement('stock');
                        Notification::make()->title('Buku dipinjam olehmu!')->success()->send();
                }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
