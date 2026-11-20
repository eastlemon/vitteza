<?php

namespace App\Filament\Resources\TripResource\Pages;

use App\Filament\Resources\TripResource;
use App\Models\Cargo;
use App\Models\CargoTemplate;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTrip extends CreateRecord
{
    protected static string $resource = TripResource::class;

    /**
     * Создаёт рейс; если выбран шаблон груза — создаёт экземпляр груза из шаблона
     * (§14.5), привязывает его к рейсу и при необходимости подставляет вес/объём.
     * created_by = текущий пользователь.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $templateId = $data['cargo_template_id'] ?? null;
        unset($data['cargo_template_id']);

        $data['created_by'] = auth()->id();

        $trip = static::getModel()::create($data);

        if ($templateId) {
            $tpl = CargoTemplate::find($templateId);
            if ($tpl) {
                $cargo = Cargo::fromTemplate($tpl, $trip->id);

                $changed = false;
                if (! $trip->weight_t && $tpl->default_weight_t) {
                    $trip->weight_t = $tpl->default_weight_t;
                    $changed = true;
                }
                if (! $trip->volume_m3 && $tpl->default_volume_m3) {
                    $trip->volume_m3 = $tpl->default_volume_m3;
                    $changed = true;
                }

                $trip->cargo_id = $cargo->id;
                $trip->save();
            }
        }

        return $trip;
    }
}
