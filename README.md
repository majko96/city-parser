# City Parser Application

This is a Laravel application designed to parse data about cities and store it in a database. The frontend component of the application allows users to view this data.

## Requirements

- PHP >= 8.3.4
- Node.js >= 21.2.0

## Installation

1. Clone this repository to your local machine
2. Navigate to the project directory: `cd city-parser`
3. Install PHP dependencies using Composer: `composer install`
4. Install Node.js dependencies using npm: `npm install`
5. Create a new MySQL database named city_parser.
6. Run database migrations to create the necessary tables: `php artisan migrate`
7. Create a symbolic link from the public/storage directory to the storage/app/public directory: `php artisan storage:link`


## Usage

1. To import city data into the database, run the following command: `php artisan data:import`
2. To import geolocation data for cities, run the following command: `php artisan data:geolocation`
3. Once the data is imported, start the development server: `php artisan serve`
4. Access the application in your web browser at http://127.0.0.1:8000/search.
