<?php

namespace App\Filament\Personal\Resources\TimesheetResource\Pages;

use App\Filament\Personal\Resources\TimesheetResource;
use App\Models\Timesheet;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Livewire\Component;

class ListTimesheets extends ListRecords
{
    protected static string $resource = TimesheetResource::class;

    protected function getHeaderActions(): array
    {

        $lastTimeSheet = Timesheet::where('user_id', Auth::user()->id)->orderBy('id', 'desc')->first();
        
        //dd($lastTimeSheet->day_out);
        
        if ($lastTimeSheet == null){
            return [
                Action::make('inWork')
                ->label('Entrar a trabajar')
                ->color('success')
                ->requiresConfirmation()
                ->action(function (){
                    $user = Auth::user();
                    $timesheet = new Timesheet();
                    $timesheet->calendar_id = 1;
                    $timesheet->user_id = $user->id;
                    $timesheet->day_in = Carbon::now();
                    $timesheet->type = 'work';
                    $timesheet->save();
                }),
                Actions\CreateAction::make(),
            ];
        }

        return [
            Action::make('inWork')
            ->label('Entrar a trabajar')
            ->color('success')
            ->visible(!$lastTimeSheet->day_out == null)
            ->disabled($lastTimeSheet->day_out == null)
            ->requiresConfirmation()
            ->action(function (){
                $user = Auth::user();
                $timesheet = new Timesheet();
                $timesheet->calendar_id = 1;
                $timesheet->user_id = $user->id;
                $timesheet->day_in = Carbon::now();
                $timesheet->type = 'work';
                $timesheet->save();

                Notification::make()
                    ->title('Has entrado a trabajar!')
                    ->success()
                    ->color('success')
                    ->send();
            }),

            Action::make('stopWork')
            ->label('Detener trabajo')
            ->color('success')
            ->visible($lastTimeSheet->day_out == null && $lastTimeSheet->type != 'pause')
            ->disabled(!$lastTimeSheet->day_out == null)
            ->requiresConfirmation()
            ->action(function () use ($lastTimeSheet){
                $lastTimeSheet->day_out = Carbon::now();
                $lastTimeSheet->save();
                Notification::make()
                    ->title('Has parado de trabajar!')
                    ->success()
                    ->color('success')
                    ->send();
            }),
            Action::make('inPause')
            ->label('Comenzar pausa')
            ->color('info')
            ->visible($lastTimeSheet->day_out == null && $lastTimeSheet->type != 'pause')
            ->disabled(!$lastTimeSheet->day_out == null)
            ->requiresConfirmation()
            ->action(function () use ($lastTimeSheet){
                $lastTimeSheet->day_out = Carbon::now();
                $lastTimeSheet->save();
                $timesheet = new Timesheet();
                $timesheet->calendar_id = 1;
                $timesheet->user_id = Auth::user()->id;
                $timesheet->day_in = Carbon::now();
                $timesheet->type = 'pause';
                $timesheet->save();

                Notification::make()
                    ->title('Has comenzado tu pausa!')
                    ->success()
                    ->color('info')
                    ->send();
            }),
            Action::make('stopPause')
            ->label('Detener pausa')
            ->color('info')
            ->visible($lastTimeSheet->day_out == null && $lastTimeSheet->type == 'pause')
            ->disabled(!$lastTimeSheet->day_out == null)
            ->requiresConfirmation()
            ->action(function () use ($lastTimeSheet){
                $lastTimeSheet->day_out = Carbon::now();
                $lastTimeSheet->save();
                $timesheet = new Timesheet();
                $timesheet->calendar_id = 1;
                $timesheet->user_id = Auth::user()->id;
                $timesheet->day_in = Carbon::now();
                $timesheet->type = 'work';
                $timesheet->save();

                Notification::make()
                    ->title('Has detenido tu pausa!')
                    ->success()
                    ->color('info')
                    ->send();
            }),
            Actions\CreateAction::make(),
        ];
    }
}
