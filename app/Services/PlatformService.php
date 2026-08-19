<?php

namespace App\Services;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Config;

class PlatformService
{
    /**
     * Get the current platform mode (saas, single_license, white_label).
     */
    public function getMode(): string
    {
        return Config::get('platform.mode', 'saas');
    }

    /**
     * Check if the platform is in SaaS mode.
     */
    public function isSaaS(): bool
    {
        return $this->getMode() === 'saas';
    }

    /**
     * Check if the platform is in Single License mode.
     */
    public function isSingleLicense(): bool
    {
        return $this->getMode() === 'single_license';
    }

    /**
     * Get a branding value, checking database first then config.
     */
    public function getBrandValue(string $key, $default = null)
    {
        $dbKey = "brand.{$key}";
        $value = PlatformSetting::get($dbKey);
        
        if ($value !== null) {
            return $value;
        }

        return Config::get("platform.brand.{$key}", $default);
    }

    /**
     * Get all branding settings for the UI.
     */
    public function getBrandSettings(): array
    {
        return [
            'name'           => $this->getBrandValue('name'),
            'company_name'   => $this->getBrandValue('company_name'),
            'logo'           => $this->getBrandValue('logo'),
            'favicon'        => $this->getBrandValue('favicon'),
            'primary_color'  => $this->getBrandValue('colors.primary'),
            'secondary_color'=> $this->getBrandValue('colors.secondary'),
            'support_email'  => $this->getBrandValue('support_email'),
            'footer_text'    => $this->getBrandValue('footer_text'),
        ];
    }
}
