<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

use App\Models\Entry;
use App\Models\Mrss;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

$factory->define(User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => Hash::make($faker->password(6)),
    ];
});

$factory->define(Mrss::class, function (Faker\Generator $faker) {
    return [
        'user_id' => function () {
            return factory(User::class)->create()->id;
        },
        'title' => $faker->title,
        'url' => $faker->url,
        'status' => Mrss::STATUS_STARTED,
    ];
});

$factory->define(Entry::class, function (Faker\Generator $faker) {
    return [
        'mrss_id' => function () {
            return factory(Mrss::class)->create()->id;
        },
        'guid' => $faker->uuid,
        'title' => $faker->title,
        'description' => $faker->text(50),
        'download_url' => $faker->url,
        'thumbnail_url' => $faker->url,
        'category' => '',
        'lang' => $faker->languageCode,
        'keywords' => '',
        'width' => strval($faker->randomNumber()),
        'height'=> strval($faker->randomNumber()),
        'file_size' => strval($faker->randomNumber()),
        'duration' => strval($faker->randomNumber()),
        'media_type' => $faker->mimeType,
        'status' => 'ready',
        'published_at' => Carbon::now()->toDateTimeString()
    ];
});

