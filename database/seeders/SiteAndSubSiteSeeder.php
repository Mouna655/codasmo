<?php
namespace Database\Seeders;

use App\Models\Site;
use App\Models\SubSite;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        // Data sites beserta sub-sites berdasarkan file xlsx
        $sites = [
            [
                'code' => 'IMM',
                'name' => 'IMM (Indominco Mandiri)',
                'sub_sites' => [
                    ['code' => 'WB LS', 'name' => 'West Block - Low Sulphur'],
                    ['code' => 'WB HS', 'name' => 'West Block - High Sulphur'],
                    ['code' => 'EB LS', 'name' => 'East Block - Low Sulphur'],
                    ['code' => 'EB MS', 'name' => 'East Block - Medium Sulphur'],
                    ['code' => 'EB HS', 'name' => 'East Block - High Sulphur'],
                ],
            ],
            [
                'code' => 'TCM',
                'name' => 'TCM (Trubaindo Coal Mining)',
                'sub_sites' => [
                    ['code' => 'LS', 'name' => 'Low Sulphur'],
                    ['code' => 'HS', 'name' => 'High Sulphur'],
                    ['code' => 'MS', 'name' => 'Medium Sulphur'],
                ],
            ],
            [
                'code' => 'BEK',
                'name' => 'BEK (Bharinto Ekatama)',
                'sub_sites' => [
                    ['code' => 'LS', 'name' => 'Low Sulphur'],
                    ['code' => 'HS', 'name' => 'High Sulphur'],
                    ['code' => 'MCV LS', 'name' => 'Medium Sulphur'],
                ],
            ],
            [
                'code' => 'GPK',
                'name' => 'GPK (Graha Panca Karsa)',
                'sub_sites' => [
                    ['code' => 'GPK', 'name' => 'GPK'],
                ],
            ],
            [
                'code' => 'JBG',
                'name' => 'JBG (Jorong Barutama Greston)',
                'sub_sites' => [
                    ['code' => 'JBG', 'name' => 'JBG'],
                ],
            ],
            [
                'code' => 'TIS',
                'name' => 'TIS (Tepian Indah Sukses)',
                'sub_sites' => [
                    ['code' => 'TIS', 'name' => 'TIS'],
                ],
            ],
        ];

        foreach ($sites as $siteData) {
            $site = Site::updateOrCreate(
                ['code' => $siteData['code']],
                ['name' => $siteData['name'], 'is_active' => true]
            );

            foreach ($siteData['sub_sites'] as $subData) {
                SubSite::updateOrCreate(
                    ['site_id' => $site->id, 'code' => $subData['code']],
                    ['name' => $subData['name'], 'is_active' => true]
                );
            }
        }

        $this->command->info('Sites & Sub-Sites seeded successfully.');
    }
}