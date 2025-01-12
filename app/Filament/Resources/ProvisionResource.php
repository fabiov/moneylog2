<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProvisionResource\Pages;
use App\Models\Provision;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProvisionResource extends Resource
{
    protected static ?string $model = Provision::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->type('number')
                    ->step(0.01),
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('date')
                    ->default(now())
                    ->required()
                    ->maxDate(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->actions([Tables\Actions\EditAction::make()])
            ->columns([
                Tables\Columns\TextColumn::make('date')->date('d/m/Y')->sortable()->width(105),
                Tables\Columns\TextColumn::make('amount')->money('eur')->sortable()->alignRight(),
                Tables\Columns\TextColumn::make('description')->wrap(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->filters([
                Tables\Filters\Filter::make('amount')
                    ->form([
                        Forms\Components\TextInput::make('amount_from')->numeric()->step(0.01),
                        Forms\Components\TextInput::make('amount_to')->numeric()->step(0.01),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_from'] === '0' ? '0.0' : (float) $data['amount_from'],
                                fn (Builder $query, $value): Builder => $query->where('amount', '>=', (float) $value),
                            )
                            ->when(
                                $data['amount_to'] === '0' ? '0.0' : (float) $data['amount_to'],
                                fn (Builder $query, $value): Builder => $query->where('amount', '<=', (float) $value),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if (($data['amount_from'] ?? '') !== '') {
                            $indicators['amount_from'] = 'Amount from: ' . $data['amount_from'];
                        }
                        if (($data['amount_to'] ?? '') !== '') {
                            $indicators['amount_to'] = 'Amount to: ' . $data['amount_to'];
                        }

                        return $indicators;
                    }),
            ], Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->defaultPaginationPageOption(25)
            ->defaultSort('date', 'desc')
            ->persistSortInSession();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProvisions::route('/'),
            'create' => Pages\CreateProvision::route('/create'),
            'edit' => Pages\EditProvision::route('/{record}/edit'),
        ];
    }

    /**
     * @return Builder<Provision>
     */
    public static function getEloquentQuery(): Builder
    {
        /** @var User $user */
        $user = Auth::user();

        return parent::getEloquentQuery()->where('user_id', '=', $user->id);
    }
}
