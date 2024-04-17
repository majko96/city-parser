<?php

namespace App\Console\Commands;

use App\Models\CityDetail;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Sunra\PhpSimple\HtmlDomParser;
use Illuminate\Support\Facades\Log;


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

    protected const BASE_URL = 'https://www.e-obce.sk/kraj/';

    protected const REGIONS = ['BB', 'BA', 'KE', 'NR', 'PO', 'TN', 'TT', 'ZA'];


    public function handle(): void
    {
        $this->info('Parsing city detail command started...');
        $this->info('Errors can be found in Log file.');
        $urls = $this->generateUrls(self::REGIONS);

        $allCityUrls = [];
        foreach ($urls as $index => $url) {
            $allCityUrls = array_merge($allCityUrls, $this->getCityUrls($url, $index));
        }

        $totalCount = count($allCityUrls);
        $cityProgressBar = $this->output->createProgressBar($totalCount);
        foreach ($allCityUrls as $index => $cityUrl) {
            $cityProgressBar->advance();
            $cityData = $this->fetchCityData($cityUrl);
            $this->saveCityData($cityData);
        }
        $cityProgressBar->finish();
    }

    private function generateUrls(array $regions): array
    {
        $urls = [];
        foreach ($regions as $region) {
            $urls[] = self::BASE_URL . $region . '.html';
        }
        return $urls;
    }

    private function getCityUrls(string $url, $index): array
    {
        $this->info('Getting urls for parsing...' . '[' . ($index + 1) . ' of ' . count(self::REGIONS) . ']');
        $html = HtmlDomParser::file_get_html($url);
        $citiesData = [];
        foreach ($html->find('div.okres a') as $municipality) {
            $municipalityUrl = $municipality->href;
            $citiesData[] = $this->importMunicipalityData($municipalityUrl);
        }
        return array_merge(...$citiesData);
    }

    private function importMunicipalityData($municipalityUrl): array
    {
        $maxRetries = 3;
        $retryCount = 0;
        $success = false;
        $cityData = [];

        while (!$success && $retryCount < $maxRetries) {
            try {
                $response = Http::get($municipalityUrl);
                $html = HtmlDomParser::str_get_html($response->body());

                foreach ($html->find('table[cellspacing="3"]') as $row) {
                    foreach ($row->find('a') as $column) {
                        if ($column->href !== '#') {
                            $cityData[] = $column->href;
                        }
                    }
                }

                $success = true;
            } catch (RequestException $e) {
                $retryCount++;
                if ($retryCount < $maxRetries) {
                    Log::channel('city_details')->info('Failed to fetch data. Retrying after 5 seconds...');
                    sleep(5);
                } else {
                    Log::channel('city_details')->error('Failed to fetch data after multiple attempts: ' . $e->getMessage());
                }
            }
        }

        return $cityData;
    }

    private function fetchCityData($cityUrl): array
    {
        if (!Storage::disk('public')->exists('images')) {
            Storage::disk('public')->makeDirectory('images');
        }

        $maxRetries = 3;
        $retryCount = 0;
        $success = false;
        $responseData = null;

        while (!$success && $retryCount < $maxRetries) {
            try {
                $response = Http::timeout(15)->get($cityUrl);
                if ($response->successful()) {
                    $success = true;
                    $responseData = $response->json();
                } else {
                    $statusCode = $response->status();
                    Log::channel('city_details')->error("Request failed with status code: $statusCode");
                    break;
                }
            } catch (\Exception $e) {
                $retryCount++;
                if ($retryCount < $maxRetries) {
                    Log::channel('city_details')->info('Something went wrong, waiting 5 sec for another request...');
                    sleep(5);
                } else {
                    Log::channel('city_details')->error($e->getMessage());
                    break;
                }
            }
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

                    if ($label === 'Okres:') {
                        $district = $value;
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

        foreach ($html->find('img[alt*=Erb]') as $img) {
            $src = $img->src;
            $cityName = preg_replace('/\([^)]*\)/', '', $cityData['name']);
            $citySlug = Str::slug($cityName) . '_' . Str::slug($district);
        
            $imageContent = $this->retryWithTimeout(function () use ($src) {
                return file_get_contents($src);
            });
        
            if ($imageContent !== false) {
                $filename = $citySlug . '.jpg';
                Storage::disk('public')->put('images/' . $filename, $imageContent);
                $cityData['imagePath'] = 'images/' . $filename;
            } else {
                $cityData['imagePath'] = null;
            }
        }
        return $cityData;
    }

    private function retryWithTimeout(callable $callback, int $maxRetries = 3, int $timeout = 15)
    {
        $retryCount = 0;
        while ($retryCount < $maxRetries) {
            try {
                return $callback();
            } catch (\Exception $e) {
                $retryCount++;
                if ($retryCount < $maxRetries) {
                    Log::channel('city_details')->info('Retrying fetching image content after timeout...');
                    sleep($timeout);
                } else {
                    Log::channel('city_details')->info('Failed to fetch image content: ' . $e->getMessage());
                    return false;
                }
            }
        }
    }

    protected function saveCityData($cityData): void
    {
        if ($cityData === null) {
            return;
        }

        $existingCity = CityDetail::where('address', $cityData['address'])
            ->where('name', $cityData['name'])
            ->first();

        if (!$existingCity) {
            CityDetail::create($cityData);
            Log::channel('city_details')->info('City "' . $cityData['name'] . '" saved.');
        } else {
            Log::channel('city_details')->info('City "' . $cityData['name'] . '" already exists, skipping.');
        }
    }
}
