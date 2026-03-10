<?php

namespace Database\Seeders;

use App\Modules\Solar\Models\EnergyUtility;
use Illuminate\Database\Seeder;

class SolarEnergyUtilitySeeder extends Seeder
{
    public function run(): void
    {
        $utilities = [
            ['name' => 'ENEL SP', 'state' => 'SP', 'cities_json' => ['Sao Paulo', 'Caieiras', 'Franco da Rocha', 'Mairipora']],
            ['name' => 'CPFL Paulista', 'state' => 'SP', 'cities_json' => ['Campinas', 'Ribeirao Preto', 'Bauru']],
            ['name' => 'CPFL Piratininga', 'state' => 'SP', 'cities_json' => ['Sorocaba', 'Jundiai', 'Santos']],
            ['name' => 'Neoenergia Coelba', 'state' => 'BA', 'cities_json' => ['Salvador', 'Feira de Santana', 'Vitoria da Conquista']],
            ['name' => 'Energisa Mato Grosso', 'state' => 'MT', 'cities_json' => ['Cuiaba', 'Varzea Grande', 'Sinop']],
            ['name' => 'Equatorial Para', 'state' => 'PA', 'cities_json' => ['Belem', 'Ananindeua', 'Castanhal']],
            ['name' => 'CEMIG', 'state' => 'MG', 'cities_json' => ['Belo Horizonte', 'Contagem', 'Uberlandia']],
            ['name' => 'Light', 'state' => 'RJ', 'cities_json' => ['Rio de Janeiro', 'Nova Iguacu', 'Duque de Caxias']],
            ['name' => 'Celesc', 'state' => 'SC', 'cities_json' => ['Florianopolis', 'Joinville', 'Blumenau']],
            ['name' => 'Neoenergia Pernambuco', 'state' => 'PE', 'cities_json' => ['Recife', 'Olinda', 'Jaboatao dos Guararapes']],
        ];

        foreach ($utilities as $utility) {
            EnergyUtility::query()->updateOrCreate(
                [
                    'name' => $utility['name'],
                    'state' => $utility['state'],
                ],
                [
                    'cities_json' => $utility['cities_json'],
                ],
            );
        }
    }
}
