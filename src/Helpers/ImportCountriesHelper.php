<?php

namespace Gogol\Invoices\Helpers;

use Gogol\Invoices\Model\Country;
use Localization;

class ImportCountriesHelper
{
    private $command;

    private $countries;
    private $useLocalization;

    public function __construct($command)
    {
        $this->command = $command;
    }

    public function handle()
    {
        if (!($countryCode = $this->askForLanguage())){
            return false;
        }

        $this->importCountries($countryCode);

        return true;
    }

    private function askForLanguage()
    {
        $language = $this->command->ask('What language code should be imported as default language?', 'sk');

        $path = base_path('vendor/umpirsky/country-list/data/'.$language);

        if ( !file_exists($path) ){
            $this->command->error('Language code '.$language.' does not exists');

            return false;
        }

        $countries = include_once $path.'/country.php';
        $countries = array_combine(array_map(function($code){
            return mb_strtolower($code);
        }, array_keys($countries)), array_values($countries));
        $this->countries = $countries;

        return $language;
    }

    private function importCountries($countryCode)
    {
        $countries = $this->countries;

        $availableCountries = Country::whereIn('code', array_keys($countries))->select(['code', 'id', 'name'])->get();
        $existingCodes = $availableCountries->pluck('code')->toArray();

        foreach ($countries as $code => $name) {
            //Country exists
            if ( in_array($code, $existingCodes) ){
                $country = $availableCountries->where('code', $code)->first();

                $country->name = $this->getLocalizedName($name, $countryCode, $country->getAttribute('name'));
                $country->save();
            } else {
                Country::create([
                    'name' => $this->getLocalizedName($name, $countryCode),
                    'code' => $code,
                ]);
            }
        }
    }

    private function getLocalizedName($name, $code, $add = [])
    {
        if ($localized = (new Country)->hasFieldParam('name', 'locale', true)) {
            $availableLocalizations = Localization::getLanguages()->pluck('slug')->toArray();

            //If given localization does not exists
            if ( !in_array($code, $availableLocalizations) && !$this->useLocalization ){
                //Into which localization do we want import this langs set?
                do {
                    $slug = $this->command->anticipate(
                        'Your language <error>'.$code.'</error> does not exists in your web languages list.'."\n ".
                        'Into which language mutation would you like to import given countries set? ['.implode(', ', $availableLocalizations).']',
                        $availableLocalizations
                    );
                } while (!in_array($slug, $availableLocalizations));

                $this->useLocalization = $slug;
            } else {
                $slug = $this->useLocalization ?: $code;
            }

            return [
                $slug => $name
            ] + $add;
        }

        return $name;
    }
}