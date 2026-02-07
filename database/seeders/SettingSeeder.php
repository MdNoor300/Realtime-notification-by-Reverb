<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
           'title' => 'Gymstick', 
           'logo' => "https://appstick.s3.ap-southeast-1.amazonaws.com/one-ride-storage/files/a0QPNfneE6PFUT6p5h9ot7fBcSKBU4CrdNd7mOKa.jpg",
           'description' => 'Gymstick',
           'email' => 'gymstick@appstick.com.bd',
           'phone' => '+880 123 456 789',
           'address' => 'khulna, Bangladesh'
        ]);
    }
}
