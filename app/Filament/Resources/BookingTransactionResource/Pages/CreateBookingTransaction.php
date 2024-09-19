<?php

namespace App\Filament\Resources\BookingTransactionResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\DB;
use App\Models\WorkshopParticipant;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\BookingTransactionResource;

class CreateBookingTransaction extends CreateRecord
{
    protected static string $resource = BookingTransactionResource::class;

    // protected function afterCreate(): void 
    // {
    //     DB::transaction(function () {
    //         $record = $this->record;
    //         $participants = $this->form->getState()['participants'];

    //         // Iterate over each participant and create a record in the workshop_participants table
    //         foreach ($participants as $participant) {
    //             WorkshopParticipant::create([
    //                 'workshop_id' => $record->workshop_id,
    //                 'booking_transaction_id' => $record->id,
    //                 'name' => $participant['name'],
    //                 'occupation' => $participant['occupation'],
    //                 'email' => $participant['email'],
    //             ]);
    //         }

    //     });
    // }

    protected function afterCreate(): void
    {
        DB::transaction(function () {
            // Mengambil record yang baru saja dibuat
            $record = $this->record;

            // Ambil data participants dari form
            $participants = $this->form->getState()['participants'];

            // Periksa apakah data participants ada
            if (is_array($participants)) {
            // Dengan menambahkan pengecekan is_array($participants), Anda memastikan bahwa hanya jika $participants adalah array, operasi foreach akan dieksekusi. Ini mencegah error yang terjadi saat bentuk data tidak sesuai (misalnya, null atau string).

                // Iterasi melalui setiap peserta dan tambahkan ke tabel workshop_participants
                foreach ($participants as $participant) {
                    WorkshopParticipant::create([
                        'workshop_id' => $record->workshop_id,
                        'booking_transaction_id' => $record->id,
                        'name' => $participant['name'],
                        'occupation' => $participant['occupation'],
                        'email' => $participant['email'],
                    ]);
                }
            }
        });
    }

}
