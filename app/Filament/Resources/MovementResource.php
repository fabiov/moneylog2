<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MovementResource\Pages;
use App\Filament\Resources\MovementResource\Widgets\MovementsStats;
use App\Models\Account;
use App\Models\Category;
use App\Models\Movement;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MovementResource extends Resource
{
    protected static ?string $model = Movement::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        /** @var User $user */
        $user = Auth::user();

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
                    ->default(Carbon::now())
                    ->required(),
                Forms\Components\Select::make('account_id')
                    ->options(Account::where('status', '<>', 'closed')->orderBy('name')->pluck('name', 'id'))
                    ->default(Movement::mostUsedAccountId($user->id))
                    ->required(),
                Forms\Components\Select::make('category_id')
                    ->options(Category::where('active', true)->orderBy('name')->pluck('name', 'id')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->actions([Tables\Actions\EditAction::make()])
            ->columns([
                Tables\Columns\TextColumn::make('date')->date('d/m/Y')->sortable()->width(105),
                Tables\Columns\TextColumn::make('amount')->sortable()->money('eur')->alignRight(),
                Tables\Columns\TextColumn::make('description')->wrap(),
                Tables\Columns\TextColumn::make('account.name'),
                Tables\Columns\TextColumn::make('category.name'),
            ])
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->placeholder(fn ($state): string => now()->subYear()->format('d/m/Y')),
                        Forms\Components\DatePicker::make('date_until')
                            ->placeholder(fn ($state): string => now()->format('d/m/Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['date_from'] ?? null) {
                            $indicators['date_from'] = 'Date from: ' . Carbon::parse($data['date_from'])->format('d/m/Y');
                        }
                        if ($data['date_until'] ?? null) {
                            $indicators['date_until'] = 'Date until: ' . Carbon::parse($data['date_until'])->format('d/m/Y');
                        }

                        return $indicators;
                    }),

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

                Tables\Filters\Filter::make('description')
                    ->form([
                        Forms\Components\TextInput::make('description'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['description'] ?? null,
                                fn (Builder $query, $value): Builder => $query->where('description', 'LIKE', "%$value%"),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if (($data['description'] ?? '') !== '') {
                            $indicators['description'] = 'Description: ' . $data['description'];
                        }

                        return $indicators;
                    }),

                Tables\Filters\SelectFilter::make('account')
                    ->relationship('account', 'name'),

                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
            ], Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultPaginationPageOption(25)
            ->defaultSort('date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMovements::route('/'),
            'create' => Pages\CreateMovement::route('/create'),
            'edit' => Pages\EditMovement::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var User $user */
        $user = Auth::user();

        return parent::getEloquentQuery()->whereIn('account_id', Account::where('user_id', $user->id)->pluck('id'));
    }

    public static function getWidgets(): array
    {
        return [
            MovementsStats::class,
        ];
    }
}
