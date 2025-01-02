# Snappy Shopper Postcode Import
## Joshua Jones

## Quick Start
The project is built with Laravel 11 and PHP 8.3.

1. Clone the repository
2. Run `composer install`
3. Run `php artisan migrate`
4. Run `php artisan app:import-postcodes`
5. Run `php artisan db:seed`
6. Run `php artisan app:generate-api-key 1`

## Postcode Import

As the postcode import is a lengthy process, I have created an optional argument to pass to the import command to only import postcodes for a specific area.

`php artisan app:import-postcodes --prefix=LE`

This will only import postcodes for the area specified by the area code. For example, `LE` will only import postcodes for the Leicester area. The area is defined by the file names in the multi_csv directory.

## API Documentation

The API documentation is available in the `docs` folder. The documentation was created using Postman, and can be imported into Postman using the 'Import' button in the top left of the Postman interface.

Authentication is handled using an API key, which is generated using the `php artisan app:generate-api-key` command. The API key (api_key) environment variable can then be set to ensure it is added to all requests headers as a Bearer token.

## Testing

To run the tests, use the `php artisan test` command.

**Note:** Tests for the postcode import functionality have been commented out as they were taking too long to run and test.

## Additional Information

Additional information can be found in the `docs/` directory.
