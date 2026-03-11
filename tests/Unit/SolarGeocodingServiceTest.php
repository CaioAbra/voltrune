<?php

namespace Tests\Unit;

use App\Modules\Solar\Services\SolarGeocodingService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SolarGeocodingServiceTest extends TestCase
{
    public function test_it_geocodes_with_zip_street_and_number(): void
    {
        Http::fake([
            '*' => Http::response([
                [
                    'lat' => '-23.5505200',
                    'lon' => '-46.6333080',
                ],
            ]),
        ]);

        $service = new SolarGeocodingService();
        $result = $service->resolveCoordinates([
            'zip_code' => '01001000',
            'street' => 'Praca da Se',
            'number' => '100',
            'district' => 'Se',
            'city' => 'Sao Paulo',
            'state' => 'SP',
        ]);

        $this->assertSame(-23.55052, $result['latitude']);
        $this->assertSame(-46.633308, $result['longitude']);
        $this->assertSame('ready', $result['status']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'nominatim.openstreetmap.org/search')
                && $request['street'] === '100 Praca da Se'
                && $request['postalcode'] === '01001000'
                && $request['city'] === 'Sao Paulo'
                && $request['state'] === 'SP';
        });
    }

    public function test_it_returns_not_found_when_geocoding_does_not_match(): void
    {
        Http::fake([
            '*' => Http::response([], 200),
        ]);

        $service = new SolarGeocodingService();
        $result = $service->resolveCoordinates([
            'zip_code' => '01001000',
            'street' => 'Rua Inexistente',
            'number' => '9999',
            'district' => 'Centro',
            'city' => 'Sao Paulo',
            'state' => 'SP',
        ]);

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertSame('not_found', $result['status']);
    }

    public function test_it_detects_when_address_changed(): void
    {
        $service = new SolarGeocodingService();

        $this->assertTrue($service->shouldRefreshCoordinates([
            'zip_code' => '01001000',
            'street' => 'Praca da Se',
            'number' => '100',
            'district' => 'Se',
            'city' => 'Sao Paulo',
            'state' => 'SP',
        ], '01001000 | Praca da Se | 99 | Se | Sao Paulo | SP'));
    }

    public function test_it_refreshes_when_coordinates_are_missing_even_if_address_did_not_change(): void
    {
        $service = new SolarGeocodingService();

        $this->assertTrue($service->shouldRefreshCoordinates([
            'zip_code' => '01001000',
            'street' => 'Praca da Se',
            'number' => '100',
            'district' => 'Se',
            'city' => 'Sao Paulo',
            'state' => 'SP',
        ], '01001000 | Praca da Se | 100 | Se | Sao Paulo | SP', true));
    }
}
