<?php

namespace Database\Seeders;

use App\Actions\Inventory\RecordStockMovement;
use App\Enums\InventoryMovementType;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Database\Seeder;

class SparepartSeeder extends Seeder
{
    /**
     * Seed the sparepart catalog, compatibility data, and opening stock.
     */
    public function run(RecordStockMovement $recordStockMovement): void
    {
        $performedBy = User::query()
            ->where('email', 'inventory@motocare.test')
            ->first()
            ?? User::query()->where('role', 'admin')->first();

        if (! $performedBy) {
            return;
        }

        $spareparts = [
            [
                'sku' => 'OIL-10W40',
                'name' => 'Oli mesin 10W-40',
                'unit' => 'botol',
                'unit_price' => 55000,
                'minimum_stock' => 10,
                'opening_stock' => 24,
                'description' => 'Oli mesin sintetik untuk motor harian.',
                'compatibilities' => [
                    ['Honda', 'Beat'],
                    ['Honda', 'Vario 160'],
                    ['Yamaha', 'NMAX 155'],
                    ['Yamaha', 'Mio M3'],
                ],
            ],
            [
                'sku' => 'FLT-OIL-STD',
                'name' => 'Filter oli standar',
                'unit' => 'pcs',
                'unit_price' => 35000,
                'minimum_stock' => 8,
                'opening_stock' => 15,
                'description' => 'Filter oli pengganti untuk servis rutin.',
                'compatibilities' => [
                    ['Honda', 'Beat'],
                    ['Honda', 'PCX 160'],
                    ['Yamaha', 'Aerox 155'],
                ],
            ],
            [
                'sku' => 'SPK-IRD',
                'name' => 'Busi iridium',
                'unit' => 'pcs',
                'unit_price' => 85000,
                'minimum_stock' => 6,
                'opening_stock' => 4,
                'description' => 'Busi iridium untuk pembakaran lebih stabil.',
                'compatibilities' => [
                    ['Honda', 'Vario 160'],
                    ['Yamaha', 'NMAX 155'],
                    ['Suzuki', 'Satria F150'],
                ],
            ],
            [
                'sku' => 'BRK-FRT-STD',
                'name' => 'Kampas rem depan',
                'unit' => 'set',
                'unit_price' => 95000,
                'minimum_stock' => 5,
                'opening_stock' => 9,
                'description' => 'Kampas rem depan untuk pemakaian harian.',
                'compatibilities' => [
                    ['Honda', 'Beat'],
                    ['Yamaha', 'Mio M3'],
                    ['Suzuki', 'Nex II'],
                ],
            ],
            [
                'sku' => 'CHN-KIT-428',
                'name' => 'Rantai dan gir',
                'unit' => 'set',
                'unit_price' => 275000,
                'minimum_stock' => 3,
                'opening_stock' => 5,
                'description' => 'Set rantai dan gir untuk motor bebek.',
                'compatibilities' => [
                    ['Honda', 'Beat'],
                    ['Suzuki', 'Satria F150'],
                    ['Kawasaki', 'W175'],
                ],
            ],
        ];

        foreach ($spareparts as $data) {
            $compatibilities = $data['compatibilities'];
            $openingStock = $data['opening_stock'];

            unset($data['compatibilities'], $data['opening_stock']);

            $sparepart = Sparepart::query()->updateOrCreate(
                ['sku' => $data['sku']],
                [
                    ...$data,
                    'is_active' => true,
                ],
            );

            $sparepart->compatibilities()->delete();

            foreach ($compatibilities as [$brand, $model]) {
                $sparepart->compatibilities()->create([
                    'brand' => $brand,
                    'model' => $model,
                ]);
            }

            if (! $sparepart->movements()->exists()) {
                $recordStockMovement->handle(
                    $sparepart,
                    InventoryMovementType::In,
                    (float) $openingStock,
                    $performedBy,
                    'Saldo awal stok demo',
                );
            }
        }
    }
}
