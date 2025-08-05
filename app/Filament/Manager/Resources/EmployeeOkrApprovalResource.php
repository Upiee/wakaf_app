<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\EmployeeOkrApprovalResource\Pages;
use App\Filament\Manager\Resources\EmployeeOkrApprovalResource\RelationManagers;
use App\Models\RealisasiOkr;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class EmployeeOkrApprovalResource extends Resource
{
    protected static ?string $model = RealisasiOkr::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Team Management';
    protected static ?string $navigationLabel = 'OKR Approval';
    protected static ?int $navigationSort = 13;

    /**
     * Get navigation badge untuk menampilkan jumlah KPI
     */
    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        
        if (!$user || !$user->divisi_id) {
            return null;
        }
        
        $count = static::getEloquentQuery()->count();
        
        return $count > 0 ? (string) $count : null;
    }
    
    // Query untuk realisasi OKR employee yang perlu di-approve manager
    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        
        return parent::getEloquentQuery()
            ->where('divisi_id', $user->divisi_id) // Realisasi di divisi manager
            ->where('user_id', '!=', $user->id) // Bukan realisasi manager sendiri
            ->whereNotNull('user_id') // Yang sudah di-assign ke employee
            ->with(['okr', 'user']) // Load relasi
            ->orderBy('created_at', 'asc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Employee OKR Information')
                    ->schema([
                        Forms\Components\TextInput::make('okr.activity')
                            ->label('OKR Activity')
                            ->disabled(),

                        Forms\Components\TextInput::make('user.name')
                            ->label('Employee Name')
                            ->disabled(),

                        Forms\Components\Textarea::make('okr.output')
                            ->label('Target Output')
                            ->rows(2)
                            ->disabled(),

                        Forms\Components\TextInput::make('okr.bobot')
                            ->label('Weight (%)')
                            ->disabled()
                            ->suffix('%'),

                        Forms\Components\TextInput::make('periode')
                            ->label('Period')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Employee Realization')
                    ->schema([
                        Forms\Components\TextInput::make('nilai')
                            ->label('Employee Realization')
                            ->disabled()
                            ->suffix('%'),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Employee Notes')
                            ->rows(3)
                            ->disabled(),

                        Forms\Components\Toggle::make('is_cutoff')
                            ->label('Final Status')
                            ->disabled()
                            ->helperText('Employee has marked this as final'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Manager Approval')
                    ->schema([
                        Forms\Components\Hidden::make('approved_by')
                            ->default(fn () => Auth::user()->id),

                        Forms\Components\Hidden::make('approved_at')
                            ->default(fn () => now()),

                        Forms\Components\Textarea::make('manager_notes')
                            ->label('Manager Notes')
                            ->rows(3)
                            ->placeholder('Add your feedback or approval notes...')
                            ->required()
                            ->helperText('Required: Provide feedback for employee'),

                        Forms\Components\Select::make('approval_action')
                            ->label('Approval Decision')
                            ->options([
                                'approve' => '✅ Approve',
                                'reject' => '❌ Reject - Need Revision',
                                'clarification' => '❓ Request Clarification',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state === 'approve') {
                                    $set('approved_at', now());
                                    $set('approved_by', Auth::user()->id);
                                } else {
                                    $set('approved_at', null);
                                    $set('approved_by', null);
                                }
                            }),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('okr.code_id', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('okr.code_id')
                    ->label('ID OKR')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('okr.activity')
                    ->label('OKR Activity')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nilai')
                    ->label('Realization')
                    ->suffix('%')
                    ->sortable()
                    ->badge()
                    ->color(function ($state) {
                        if ($state >= 100) return 'success';
                        if ($state >= 75) return 'warning';
                        if ($state >= 50) return 'info';
                        return 'danger';
                    }),

                Tables\Columns\TextColumn::make('periode')
                    ->label('Period')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\BadgeColumn::make('approved_at')
                    ->label('Approval Status')
                    ->formatStateUsing(function ($record) {
                        if ($record->approved_at) {
                            return 'Approved';
                        }
                        if ($record->rejected_at) {
                            return 'Rejected';
                        }
                        return 'Pending';
                    })
                    ->colors([
                        'success' => fn ($record) => $record->approved_at !== null,
                        'danger' => fn ($record) => $record->rejected_at !== null,
                        'warning' => fn ($record) => $record->approved_at === null && $record->rejected_at === null,
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted Date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('pending_approval')
                    ->label('Pending Approval')
                    ->query(fn (Builder $query) => $query->whereNull('approved_at')->whereNull('rejected_at'))
                    ->toggle(),

                Tables\Filters\Filter::make('approved')
                    ->label('Approved')
                    ->query(fn (Builder $query) => $query->whereNotNull('approved_at'))
                    ->toggle(),

                Tables\Filters\Filter::make('rejected')
                    ->label('Rejected')
                    ->query(fn (Builder $query) => $query->whereNotNull('rejected_at'))
                    ->toggle(),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Employee')
                    ->options(function () {
                        $user = Auth::user();
                        if (!$user || !$user->divisi_id) {
                            return [];
                        }
                        
                        return \App\Models\User::where('divisi_id', $user->divisi_id)
                            ->where('id', '!=', $user->id) // Exclude manager sendiri
                            ->where('is_active', true)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable(),

                Tables\Filters\SelectFilter::make('periode')
                    ->label('Period')
                    ->options([
                        'Q1-2025' => 'Q1 2025',
                        'Q2-2025' => 'Q2 2025',
                        'Q3-2025' => 'Q3 2025',
                        'Q4-2025' => 'Q4 2025',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->label('Review & Approve')
                    ->visible(fn ($record) => is_null($record->approved_at)),

                Tables\Actions\Action::make('quick_approve')
                    ->label('Quick Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => is_null($record->approved_at) && is_null($record->rejected_at))
                    ->requiresConfirmation()
                    ->modalHeading('Approve OKR Realization')
                    ->modalDescription('Are you sure you want to approve this OKR realization?')
                    ->action(function ($record) {
                        $record->update([
                            'approved_by' => Auth::user()->id,
                            'approved_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->success()
                            ->title('OKR Approved')
                            ->body('OKR realization has been approved successfully.')
                            ->send();
                    }),

                Tables\Actions\Action::make('quick_reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => is_null($record->approved_at) && is_null($record->rejected_at))
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->placeholder('Please provide reason for rejection...')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'rejected_by' => Auth::user()->id,
                            'rejected_at' => now(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->warning()
                            ->title('OKR Rejected')
                            ->body('OKR realization has been rejected. Employee will be notified.')
                            ->send();
                    })
                    ->modalHeading('Reject OKR Realization')
                    ->modalDescription('Please provide a reason for rejecting this OKR realization:'),

                Tables\Actions\Action::make('view_approved')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn ($record) => !is_null($record->approved_at))
                    ->url(fn ($record) => static::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_approve')
                        ->label('Bulk Approve')
                        ->icon('heroicon-m-check')
                        ->color('success')
                        ->action(function ($records) {
                            $userId = Auth::user()->id;
                            $now = now();
                            
                            foreach ($records as $record) {
                                if (is_null($record->approved_at)) {
                                    $record->update([
                                        'approved_by' => $userId,
                                        'approved_at' => $now,
                                    ]);
                                }
                            }
                            
                            Notification::make()
                                ->success()
                                ->title('Bulk Approval Completed')
                                ->body('Selected OKR realizations have been approved.')
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Bulk Approve OKR Realizations')
                        ->modalDescription('Are you sure you want to approve all selected OKR realizations?'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeOkrApprovals::route('/'),
            'view' => Pages\ViewEmployeeOkrApproval::route('/{record}'),
            'edit' => Pages\EditEmployeeOkrApproval::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Manager tidak membuat approval, otomatis dari employee progress
    }
}
