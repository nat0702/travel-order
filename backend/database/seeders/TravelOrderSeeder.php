<?php

namespace Database\Seeders;

use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Database\Seeder;

class TravelOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $user1 = User::where('email', 'user@example.com')->first();
        $user2 = User::where('email', 'user2@example.com')->first();
        $user3 = User::where('email', 'user3@example.com')->first();

        if (! $admin || ! $user1 || ! $user2 || ! $user3) {
            return;
        }

        $travelOrders = [
            [
                'user_id' => $user1->id,
                'destination' => 'São Paulo',
                'departure_date' => '2026-03-25',
                'return_date' => '2026-03-28',
                'status' => 'solicitado',
            ],
            [
                'user_id' => $user1->id,
                'destination' => 'Rio de Janeiro',
                'departure_date' => '2026-04-10',
                'return_date' => '2026-04-15',
                'status' => 'aprovado',
            ],
            [
                'user_id' => $user1->id,
                'destination' => 'Curitiba',
                'departure_date' => '2026-05-05',
                'return_date' => '2026-05-09',
                'status' => 'cancelado',
            ],
            [
                'user_id' => $user2->id,
                'destination' => 'Salvador',
                'departure_date' => '2026-03-26',
                'return_date' => '2026-03-30',
                'status' => 'solicitado',
            ],
            [
                'user_id' => $user2->id,
                'destination' => 'Brasília',
                'departure_date' => '2026-04-02',
                'return_date' => '2026-04-06',
                'status' => 'aprovado',
            ],
            [
                'user_id' => $user2->id,
                'destination' => 'Recife',
                'departure_date' => '2026-05-12',
                'return_date' => '2026-05-16',
                'status' => 'cancelado',
            ],
            [
                'user_id' => $user3->id,
                'destination' => 'Belo Horizonte',
                'departure_date' => '2026-07-12',
                'return_date' => '2026-08-16',
                'status' => 'cancelado',
            ],
            [
                'user_id' => $user3->id,
                'destination' => 'Florianópolis',
                'departure_date' => '2026-06-12',
                'return_date' => '2026-06-16',
                'status' => 'cancelado',
            ],
        ];

        foreach ($travelOrders as $travelOrder) {
            TravelOrder::updateOrCreate(
                [
                    'user_id' => $travelOrder['user_id'],
                    'destination' => $travelOrder['destination'],
                    'departure_date' => $travelOrder['departure_date'],
                    'return_date' => $travelOrder['return_date'],
                ],
                [
                    'status' => $travelOrder['status'],
                ]
            );
        }
    }
}
