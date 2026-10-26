<?php

namespace App\Auth\Registration\Registrars;

use App\Auth\Registration\Data\ProviderRegistrationData;
use App\Auth\Registration\Data\RegistrationData;
use App\Models\ProviderDocuments;
use App\Models\ProviderFinancialInfo;
use App\Models\ProviderOperatingInfo;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ProviderRegistrar extends AbstractAccountRegistrar
{
    protected function membershipType(): string
    {
        return User::MEMBERSHIP_PROVIDER;
    }

    protected function initialStatus(): string
    {
        return User::STATUS_PENDING_APPROVAL;
    }

    protected function roleName(): string
    {
        return 'provider';
    }

    protected function prepareArtifacts(RegistrationData $data): array
    {
        /** @var ProviderRegistrationData $data */
        $artifacts = [
            'license_path' => $data->businessLicense->store('provider_documents', config('filesystems.private_disk')),
            'id_path' => $data->idOrIqama->store('provider_documents', config('filesystems.private_disk')),
            'logo_path' => null,
            'logo_disk' => null,
        ];

        if ($data->profileLogo !== null) {
            $artifacts['logo_path'] = $data->profileLogo->store('provider-logos', 'public');
            $artifacts['logo_disk'] = 'public';
        }

        return $artifacts;
    }

    protected function createTypeSpecificData(User $user, RegistrationData $data, array $artifacts): void
    {
        /** @var ProviderRegistrationData $data */
        ProviderProfile::create([
            'user_id' => $user->id,
            'full_name_ar' => $data->fullNameAr,
            'full_name_en' => $data->fullNameEn,
            'phone_number' => $user->phone_number,
            'email' => $data->email,
            'business_name_ar' => $data->businessNameAr,
            'business_name_en' => $data->businessNameEn,
            'unified_number' => $data->unifiedNumber,
            'business_category' => $data->businessCategory,
            'address_ar' => $data->addressAr,
            'address_en' => $data->addressEn,
            'city' => $data->city,
            'region' => $data->region,
            'location' => $data->location,
            'logo_path' => $artifacts['logo_path'],
        ]);

        ProviderOperatingInfo::create([
            'user_id' => $user->id,
            'operating_hours' => $data->operatingHours,
            'daily_capacity' => $data->dailyCapacity,
            'service_type' => $data->serviceType,
            'estimated_preparation_order_time' => $data->estimatedPreparationOrderTime,
            'adoption_support' => $data->adoptionSupport,
        ]);

        ProviderFinancialInfo::create([
            'user_id' => $user->id,
            'bank_name' => $data->bankName,
            'iban' => $data->iban,
            'account_holder_name' => $data->accountHolderName,
        ]);

        ProviderDocuments::create([
            'user_id' => $user->id,
            'business_license_path' => $artifacts['license_path'],
            'id_or_iqama_path' => $artifacts['id_path'],
        ]);
    }

    protected function cleanupArtifacts(array $artifacts): void
    {
        Storage::disk(config('filesystems.private_disk'))->delete($artifacts['license_path']);
        Storage::disk(config('filesystems.private_disk'))->delete($artifacts['id_path']);

        if ($artifacts['logo_path'] !== null) {
            Storage::disk('public')->delete($artifacts['logo_path']);
        }
    }
}
