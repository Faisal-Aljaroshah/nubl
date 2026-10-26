<?php

namespace App\Auth\Registration\Registrars;

use App\Auth\Registration\Data\RecipientRegistrationData;
use App\Auth\Registration\Data\RegistrationData;
use App\Auth\Support\Base64ImageStorage;
use App\Contracts\NotificationServiceInterface;
use App\Models\RecipientKycDetails;
use App\Models\RecipientProfile;
use App\Models\User;
use App\Services\AuditService;
use App\Services\OtpService;
use Illuminate\Support\Facades\Storage;

class RecipientRegistrar extends AbstractAccountRegistrar
{
    public function __construct(
        NotificationServiceInterface $notifications,
        AuditService $audit,
        OtpService $otp,
        private Base64ImageStorage $images,
    ) {
        parent::__construct($notifications, $audit, $otp);
    }

    protected function membershipType(): string
    {
        return User::MEMBERSHIP_RECIPIENT;
    }

    protected function initialStatus(): string
    {
        return User::STATUS_PENDING_APPROVAL;
    }

    protected function roleName(): string
    {
        return 'recipient';
    }

    protected function prepareArtifacts(RegistrationData $data): array
    {
        /** @var RecipientRegistrationData $data */
        return [
            'id_photo' => $this->images->store($data->idPhotoBase64, 'recipient_id_photos', 'id_photo_'),
        ];
    }

    protected function createTypeSpecificData(User $user, RegistrationData $data, array $artifacts): void
    {
        /** @var RecipientRegistrationData $data */
        RecipientProfile::create([
            'user_id' => $user->id,
            'nationality' => $data->nationality,
            'short_address' => $data->shortAddress,
            'location' => json_encode(['lat' => $data->locationLat, 'lng' => $data->locationLng]),
            'id_type' => $data->idType,
            'id_number' => $data->idNumber,
            'id_photo_path' => $artifacts['id_photo'],
        ]);

        RecipientKycDetails::create([
            'user_id' => $user->id,
            'income_band' => $data->incomeBand,
            'household_size' => $data->householdSize,
            'marital_status' => $data->maritalStatus,
            'is_student' => $data->isStudent,
            'employment_status' => $data->employmentStatus,
            'situation_description' => $data->situationDescription,
        ]);
    }

    protected function cleanupArtifacts(array $artifacts): void
    {
        Storage::disk(config('filesystems.private_disk'))->delete($artifacts['id_photo']);
    }
}
