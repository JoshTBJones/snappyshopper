<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Postcode;
use Illuminate\Support\Facades\DB;  

class ImportPostcodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-postcodes {--prefix= : The prefix to filter the incode file (e.g., LE)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import postcode data based on an incode prefix or process all files if no prefix is specified';

    /**
     * The temporary directory path for storing postcode files
     * 
     * @var string
     */
    protected $tempDir = 'app/temp/postcodes';

    /**
     * Number of rows to process at once
     * 
     * @var int
     */
    protected $batchSize = 250;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // step 1: download the postcode data
        $this->info('Starting postcode data import process');

        try
        {
            // step 2: download the postcode data if it doesn't already exist
            $this->downloadPostcodeDataIfNeeded();

            $prefix = $this->option('prefix');
            $directoryPath = storage_path("{$this->tempDir}/Data/multi_csv");

            // Scan the directory and retrieve all files with full paths
            $files = scandir($directoryPath);

            // Filter the files based on the provided prefix or default to all CSV files
            $files = array_filter($files, function ($file) use ($directoryPath, $prefix) {
                // Prepend the full directory path to the file name
                $filePath = "{$directoryPath}/{$file}";

                // Ensure it's a file (not a directory) and matches the required criteria
                return is_file($filePath) && ($prefix
                    ? str_starts_with($file, "ONSPD_NOV_2022_UK_{$prefix}") // Match prefix if provided
                    : str_ends_with($file, '.csv')); // Otherwise, include all .csv files
            });

            // Prepend the directory path to all filtered files
            $files = array_map(fn($file) => "{$directoryPath}/{$file}", $files);


            if (empty($files)) {
                $this->error($prefix
                    ? "No files found for prefix '{$prefix}' in directory: {$directoryPath}"
                    : "No CSV files found in directory: {$directoryPath}");
                return 1;
            }

            // step 3: process the postcode data
            $this->processCsvFiles($files);
            $this->info('Postcode data import process completed successfully!');
        }
        catch (\Exception $e)
        {
            $this->output->writeln("\b✗"); // Show X mark on failure
            $this->error($e->getMessage());
        }
    }

    /**
     * Downloads postcode data if it doesn't already exist in the temp directory.
     * 
     * Checks if postcode data file exists in the temp directory. If it doesn't exist,
     * downloads the data by calling downloadPostcodeData(). Provides progress information
     * via console output.
     *
     * @return void
     */
    protected function downloadPostcodeDataIfNeeded()
    {
        $this->info('Checking for existing postcode data file...');

        if ($this->checkIfFileExists()) {
            $this->info('File already exists, skipping download.');
            return;
        }

        $this->info('Downloading postcode data. Please wait...');
        $this->downloadPostcodeData();
        $this->info('Postcode data downloaded successfully.');
    }

    /**
     * Check if the postcodes CSV file exists in the temp directory
     *
     * @return bool True if file exists, false otherwise
     */
    protected function checkIfFileExists(): bool
    {
        return file_exists(storage_path($this->tempDir . '/Data/ONSPD_NOV_2022_UK.csv'));
    }

    /**
     * Downloads and extracts postcode data from a remote ZIP file
     * 
     * Downloads a ZIP file containing postcode data from mysociety.org,
     * saves it to a temporary location, extracts the contents,
     * and cleans up the temporary ZIP file.
     *
     * @throws \RuntimeException If download fails or ZIP extraction fails
     * @return void
     */
    protected function downloadPostcodeData(): void
    {
        $url = 'https://parlvid.mysociety.org/os/ONSPD/2022-11.zip';
        $tempFile = storage_path($this->tempDir . '/postcodes.zip');

        // Create temp directory if it doesn't exist
        if (!file_exists(dirname($tempFile)))
        {
            mkdir(dirname($tempFile), 0755, true);
        }

        // Stream download to temp file
        $fp = fopen($tempFile, 'w+');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);

        if (curl_errno($ch))
        {
            throw new \RuntimeException('Failed to download postcode data: ' . curl_error($ch));
        }

        curl_close($ch);
        fclose($fp);

        // Extract zip file
        $zip = new \ZipArchive();
        if ($zip->open($tempFile) === TRUE)
        {
            $zip->extractTo(dirname($tempFile));
            $zip->close();
        }
        else
        {
            throw new \RuntimeException('Failed to extract postcode data zip file');
        }

        // Clean up zip file
        unlink($tempFile);
    }

    /**
     * Process all CSV files containing postcode data.
     *
     * Iterates through each CSV file, parses the postcode data,
     * and imports it into the database. Shows progress feedback
     * during processing.
     *
     * @param array $files Array of file paths to CSV files
     * @return void
     */
    protected function processCsvFiles(array $files): void
    {
        $this->info('Processing CSV files...');
        $this->info("Found " . count($files) . " files to process.");
        $this->output->progressStart(count($files));

        foreach ($files as $file)
        {
            // Parse the postcode data
            $postcodes = $this->parsePostcodeData($file);

            // Import the postcode data
            $this->importPostcodeData($postcodes);

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->info('All CSV files processed.');
    }

    /**
     * Parse postcode data from a CSV file.
     *
     * Opens and reads a CSV file containing postcode data, processes it in batches,
     * and prepares the data for database insertion. The data is processed in batches
     * of 100 rows to optimize memory usage.
     *
     * @param string $csvFile Path to the CSV file to parse
     * @throws \RuntimeException If unable to open the CSV file
     * @return array Array of processed postcode data ready for database insertion
     */
    protected function parsePostcodeData(string $csvFile): array
    {
        // Open the CSV file
        $csv = fopen($csvFile, 'r');
        if (!$csv)
        {
            throw new \RuntimeException("Unable to open CSV file: $csvFile");
        }

        // Get headers from first row
        $headers = fgetcsv($csv);

        // Initialize batch storage
        $postcodes = [];

        // Read data rows
        while (($row = fgetcsv($csv)) !== false)
        {
            $postcodeData = array_combine($headers, $row);

            // Prepare data for insertion
            $pcd = trim($postcodeData['pcd']); // Ensure no leading or trailing spaces
            $incode = trim(substr($pcd, -3)); // Last 3 characters
            $outcode = trim(substr($pcd, 0, -3)); // All except the last 3 characters
            $postcode = $outcode . $incode; // Normalise postcode

            $postcodes[] = [
                'postcode'    => $postcode,
                'outcode'     => $outcode,
                'incode'      => $incode,
                'latitude'    => $postcodeData['lat'],
                'longitude'   => $postcodeData['long'],
                // 'coordinates' => DB::raw("ST_GeomFromText('POINT({$postcodeData['long']} {$postcodeData['lat']})', 4326)"),
                'country'     => $postcodeData['ctry'],
                'region'      => $postcodeData['rgn'],
                'district'    => $postcodeData['parish'],
                'active'      => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            // Process batch when it reaches the batch size
            if (count($postcodes) >= $this->batchSize)
            {
                $this->importPostcodeData($postcodes);
                $postcodes = []; // Reset batch
            }
        }

        // Insert any remaining rows
        if (!empty($postcodes))
        {
            $this->importPostcodeData($postcodes);
        }

        fclose($csv);

        return $postcodes;
    }

    /**
     * Import a batch of postcode data into the database.
     *
     * This method handles the database transaction for upserting postcode records.
     * It will either insert new records or update existing ones based on the postcode.
     * If an error occurs during the process, the transaction will be rolled back.
     *
     * @param array $postcodes Array of postcode data to import
     * @return void
     * @throws \Exception If database operation fails
     */
    protected function importPostcodeData(array $postcodes): void
    {
        try
        {
            // start transaction
            DB::beginTransaction();

            // Use upsert to handle duplicates
            Postcode::upsert(
                $postcodes,
                ['postcode'], // Columns to check for duplicates
                ['outcode', 'incode', 'latitude', 'longitude', 'country', 'region', 'district', 'active', 'updated_at'] // Columns to update if duplicate
            );

            // commit transaction
            DB::commit();
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            throw $e;
        }
    }
}
