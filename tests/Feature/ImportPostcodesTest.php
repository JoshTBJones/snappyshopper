<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Console\Commands\ImportPostcodes;
use App\Models\Postcode;
use Mockery;

class ImportPostcodesTest extends TestCase
{
    // /**
    //  * Test handling of missing CSV file.
    //  */
    // public function testHandleWithMissingCsvFile()
    // {
    //     $command = Mockery::mock(ImportPostcodes::class)
    //         ->makePartial()
    //         ->shouldAllowMockingProtectedMethods(); // Enable mocking of protected methods

    //     $command->shouldReceive('checkIfFileExists')->andReturn(false);

    //     $this->artisan('app:import-postcodes')
    //         ->expectsOutput('File already exists, skipping download.')
    //         ->assertExitCode(0); // Exit code SUCCESS
    // }


    // /**
    //  * Test downloading postcode data when file does not exist.
    //  */
    // public function testDownloadPostcodeDataIfNeeded()
    // {
    //     $command = Mockery::mock(ImportPostcodes::class)->makePartial();
    //     $command->shouldReceive('checkIfFileExists')->andReturn(false);
    //     $command->shouldReceive('downloadPostcodeData')->once();

    //     $this->artisan('app:import-postcodes')
    //         ->expectsOutput('Checking for existing postcode data file...')
    //         ->expectsOutput('Downloading postcode data. Please wait...')
    //         ->expectsOutput('Postcode data downloaded successfully.')
    //         ->assertExitCode(0);
    // }

    // /**
    //  * Test filtering CSV files by prefix.
    //  */
    // public function testFilterCsvFilesByPrefix()
    // {
    //     $directoryPath = storage_path('app/temp/postcodes/Data/multi_csv');

    //     Storage::fake('local');
    //     Storage::disk('local')->put("app/temp/postcodes/Data/multi_csv/ONSPD_NOV_2022_UK_LE.csv", '');
    //     Storage::disk('local')->put("app/temp/postcodes/Data/multi_csv/ONSPD_NOV_2022_UK_LS.csv", '');
    //     Storage::disk('local')->put("app/temp/postcodes/Data/multi_csv/README.txt", '');

    //     $command = Mockery::mock(ImportPostcodes::class)->makePartial();
    //     $command->shouldReceive('processCsvFiles')
    //         ->with([
    //             "{$directoryPath}/ONSPD_NOV_2022_UK_LE.csv",
    //         ])
    //         ->once();

    //     $this->artisan('app:import-postcodes --prefix=LE')
    //         ->expectsOutput('Processing CSV files...')
    //         ->assertExitCode(0);
    // }

    // /**
    //  * Test parsing a single CSV file.
    //  */
    // public function testParseCsvFile()
    // {
    //     $csvFile = storage_path('app/temp/postcodes/test.csv');
    //     $csvData = [
    //         ['pcd', 'lat', 'long', 'ctry', 'rgn', 'parish'],
    //         ['LE1 1AB', '52.634', '-1.133', 'ENG', 'East Midlands', 'Leicester'],
    //         ['LE2 2CD', '52.620', '-1.100', 'ENG', 'East Midlands', 'Leicester'],
    //     ];

    //     Storage::fake('local');
    //     Storage::disk('local')->put('app/temp/postcodes/test.csv', implode("\n", array_map('str_putcsv', $csvData)));

    //     $command = Mockery::mock(ImportPostcodes::class)->makePartial();
    //     $postcodes = $command->parsePostcodeData($csvFile);

    //     $this->assertCount(2, $postcodes);
    //     $this->assertEquals('LE1 1AB', $postcodes[0]['postcode']);
    //     $this->assertEquals('LE2 2CD', $postcodes[1]['postcode']);
    // }

    // /**
    //  * Test importing postcodes into the database.
    //  */
    // public function testImportPostcodeData()
    // {
    //     $postcodes = [
    //         [
    //             'postcode' => 'LE1 1AB',
    //             'outcode' => 'LE1',
    //             'incode' => '1AB',
    //             'latitude' => '52.634',
    //             'longitude' => '-1.133',
    //             'country' => 'ENG',
    //             'region' => 'East Midlands',
    //             'district' => 'Leicester',
    //             'active' => 1,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ],
    //     ];

    //     Postcode::shouldReceive('upsert')
    //         ->with($postcodes, ['postcode'], Mockery::any())
    //         ->once();

    //     $command = Mockery::mock(ImportPostcodes::class)->makePartial();
    //     $command->importPostcodeData($postcodes);

    //     $this->assertTrue(true); // Verify no exceptions were thrown
    // }
}

/**
 * Helper function to convert array to CSV string.
 */
if (!function_exists('str_putcsv')) {
    function str_putcsv($fields)
    {
        $fh = fopen('php://temp', 'rw');
        fputcsv($fh, $fields);
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);
        return trim($csv);
    }
}
