<?php

namespace App\Console\Commands;

use App\Models\CityDetail;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Sunra\PhpSimple\HtmlDomParser;

class ImportCityData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse websites and import data into DB';

    protected const URL = 'https://www.e-obce.sk/kraj/NR.html';

    /**
     * Execute the console command.
     * @throws Exception
     */
    public function handle(): void
    {
        $url = self::URL;
        $html = HtmlDomParser::file_get_html($url);

        $citiesData = [];
        $this->info('Starting parsing website...');
        foreach ($html->find('div.okres a') as $municipality) {
            $municipalityUrl = $municipality->href;
            $citiesData[] = $this->importMunicipalityData($municipalityUrl);
        }

        $cityUrls = array_merge(...$citiesData);
        $this->info('I have ' . count($cityUrls) . ' detail urls for parsing...');
        foreach ($cityUrls as $index => $cityUrl) {
            $this->info('Processing city detail number ' . $index . '.');
            $cityData = $this->fetchCityData($cityUrl);
            $this->saveCityData($cityData);
        }

        $this->info('City data imported successfully!');
    }

    function importMunicipalityData($municipalityUrl): array
    {
        $response = Http::get($municipalityUrl);
        $html = HtmlDomParser::str_get_html($response->body());

        $cityData = [];
        foreach ($html->find('table[cellspacing="3"]') as $row) {
            $cityData = [];
            foreach ($row->find('a') as $column) {
                if ($column->href !== '#') {
                    $cityData[] = $column->href;
                }
            }
        }
        return $cityData;
    }

    function fetchCityData($cityUrl): array
    {
        if (!Storage::disk('public')->exists('images')) {
            Storage::disk('public')->makeDirectory('images');
        }

        try {
            $response = Http::timeout(30)->get($cityUrl);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return [];
        }

        $html = HtmlDomParser::str_get_html($response->body());

        $cityData = [
            'mayor_name' => '',
            'address' => '',
            'phone' => '',
            'mobile' => '',
            'fax' => '',
            'email' => '',
            'website' => '',
            'imagePath' => '',
        ];

        $cityName = trim($html->find('h1', 0)->plaintext);
        $cityData['name'] = trim(preg_replace('/\b(Obec|Mesto)\b/i', '', $cityName));

        foreach ($html->find('img[alt*=Erb]') as $img) {
            $src = $img->src;
            $cityName = preg_replace('/\([^)]*\)/', '', $cityData['name']);
            $citySlug = Str::slug($cityName);
            $imageContent = file_get_contents($src);
            if ($imageContent !== false) {
                $filename = $citySlug . '.jpg';
                Storage::disk('public')->put('images/' . $filename, $imageContent);
                $cityData['imagePath'] = 'images/' . $filename;
            } else {
                $cityData['imagePath'] = null;
            }
        }

        foreach ($html->find('table[cellspacing="3"]') as $table) {
            $rows = $table->find('tr');
            $numRows = count($rows);

            for ($i = 0; $i < $numRows; $i++) {
                $row = $rows[$i];
                $cells = $row->find('td');

                if (count($cells) === 2) {
                    $label = trim($cells[0]->plaintext);
                    $value = trim($cells[1]->plaintext);

                    if ($label === 'Primátor:') {
                        $cityData['mayor_name'] = $value;
                    }

                    if ($label === 'Mobil:') {
                        $cityData['mobile'] = $value;
                    }

                    if ($label === 'Starosta:') {
                        $cityData['mayor_name'] = $value;
                    }

                } elseif (count($cells) >= 3) {
                    $secondCell = $cells[1];
                    $label = trim($secondCell->plaintext);
                    $value = isset($cells[2]) ? trim($cells[2]->plaintext) : '';

                    if ($value === "Tel:") {
                        $cityData['phone'] = trim($cells[3]->plaintext);
                    }

                    if (stripos($label, 'Email') !== false) {
                        $cityData['email'] = $value;
                        $nextRowIndex = $i + 1;
                        if ($nextRowIndex < $numRows) {
                            $nextRow = $rows[$nextRowIndex];
                            $mainAddressCell = $nextRow->find('td', 0);
                            if ($mainAddressCell) {
                                $cityAddress = trim($mainAddressCell->plaintext);
                                $addressIndex = array_search($secondCell, $cells) - 1;
                                $address = isset($cells[$addressIndex]) ? trim($cells[$addressIndex]->plaintext) : '';
                                $cityData['address'] = trim($address . ', ' . $cityAddress);
                            }
                        }
                    } elseif (stripos($label, 'Fax') !== false) {
                        $cityData['fax'] = $value;
                    } elseif (stripos($label, 'Web') !== false) {
                        $cityData['website'] = $value;
                    }
                }
            }
        }
        return $cityData;
    }

    protected function saveCityData($cityData): void
    {
        $existingCity = CityDetail::where('name', $cityData['name'])->first();

        if (!$existingCity) {
            CityDetail::create($cityData);
            $this->info('City "' . $cityData['name'] . '" saved.');
        } else {
            $this->info('City "' . $cityData['name'] . '" already exists, skipping.');
        }
    }
}
