<?php

namespace App\Console\Commands;

use App\Models\CityDetail;
use Exception;
use Illuminate\Console\Command;
use OpenCage\Geocoder\Geocoder;

class DataGeocode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:geocode';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import geolocation by city address';

    /**
     * Execute the console command.
     * @throws Exception
     */
    public function handle(): void
    {
        $geocoder = new Geocoder(env('GEOCODER_API_KEY'));
        $cityDetails = CityDetail::all();

        foreach ($cityDetails as $cityDetail) {
            $this->updateGeocode($cityDetail, $geocoder);
        }
    }

    private function updateGeocode($cityDetail, $geocoder): void
    {
        $address = $cityDetail->address;
        $response = $geocoder->geocode($address);

        if ($response && isset($response['results'][0]['geometry']['lat'], $response['results'][0]['geometry']['lng'])) {
            $latitude = $response['results'][0]['geometry']['lat'];
            $longitude = $response['results'][0]['geometry']['lng'];
            $this->saveGeocode($cityDetail, $latitude, $longitude);
            $this->info("Geocode updated for city: $cityDetail->name");
        } else {
            $this->error("Failed to retrieve geocode for city: $cityDetail->name");
            $this->info("Another attempt...");
            $textAfterLastComma = $this->getTextAfterLastComma($address);
            if ($textAfterLastComma) {
                $response = $geocoder->geocode($textAfterLastComma);
                if ($response && isset($response['results'][0]['geometry']['lat'], $response['results'][0]['geometry']['lng'])) {
                    $latitude = $response['results'][0]['geometry']['lat'];
                    $longitude = $response['results'][0]['geometry']['lng'];
                    $this->saveGeocode($cityDetail, $latitude, $longitude);
                    $this->info("Geocode updated for city: $cityDetail->name");
                }
            }
        }
    }

    private function getTextAfterLastComma($address): ?string
    {
        $lastCommaPosition = strrpos($address, ',');
        if ($lastCommaPosition !== false) {
            return substr($address, $lastCommaPosition + 1);
        }
        return null;
    }

    private function saveGeocode($cityDetail, $latitude, $longitude): void
    {
        $cityDetail->lat = $latitude;
        $cityDetail->lng = $longitude;
        $cityDetail->save();
    }
}
