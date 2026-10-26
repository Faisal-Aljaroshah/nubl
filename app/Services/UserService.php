<?php

namespace App\Services;

use App\Models\ProviderDocuments;
use App\Models\ProviderFinancialInfo;
use App\Models\ProviderOperatingInfo;
use App\Models\ProviderProfile;
use App\Models\RecipientKycDetails;
use App\Models\RecipientProfile;
use App\Models\User;
use App\Support\OperatingHoursNormalizer;
use App\Support\PhoneHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;

class UserService
{
    public function __construct(
        private AuditService $auditService,
        private PermissionRegistrar $permissionRegistrar
    ) {}

    /**
     * Create user with role and type-specific profiles.
     *
     * @throws \Throwable
     */
    public function createUser(array $data, Request $request): User
    {
        return DB::transaction(function () use ($data, $request) {
            $user = $this->createUserRecord($data, $request->user());
            $this->createProfileByType($user, $data, $request);

            $this->auditService->log('user', 'created', [
                'user_id' => $user->id,
                'email' => $user->email,
                'membership_type' => $user->membership_type,
                'roles' => $data['roles'] ?? [],
            ]);

            return $user;
        });
    }

    /**
     * Update user and type-specific profiles.
     *
     * @throws \Throwable
     */
    public function updateUser(User $user, array $data, Request $request): void
    {
        DB::transaction(function () use ($user, $data, $request) {
            $this->updateUserRecord($user, $data, $request->user());
            $this->updateProfileByType($user, $data, $request);

            $this->auditService->log('user', 'updated', [
                'user_id' => $user->id,
                'email' => $user->email,
                'roles' => $data['roles'] ?? [],
            ]);
        });
    }

    /**
     * Delete user. Guards: prevent self-delete, prevent deleting last active admin.
     *
     * @throws \InvalidArgumentException If target is current admin or last active admin
     */
    public function deleteUser(User $user, User $admin): void
    {
        $this->guardDelete($user, $admin);

        $this->auditService->log('user', 'deleted', [
            'deleted_user_id' => $user->id,
            'deleted_user_email' => $user->email,
        ], $admin->id);

        $user->delete();
    }

    /**
     * Deactivate user. Blocks login. Logs action.
     *
     * @throws \InvalidArgumentException If target is current admin or last admin
     */
    public function deactivate(User $user, User $admin): void
    {
        $this->guardDeactivate($user, $admin);

        $user->deactivate();

        $this->auditService->log('user', 'deactivated', [
            'user_id' => $user->id,
            'user_email' => $user->email,
        ], $admin->id);
    }

    /**
     * Reactivate user. Logs action.
     */
    public function reactivate(User $user, User $admin): void
    {
        $user->reactivate();

        $this->auditService->log('user', 'reactivated', [
            'user_id' => $user->id,
            'user_email' => $user->email,
        ], $admin->id);
    }

    /**
     * Prevent admin from deleting self or last active admin.
     */
    protected function guardDelete(User $user, User $admin): void
    {
        if ($user->id === $admin->id) {
            throw new \InvalidArgumentException(__('You cannot delete your own account.'));
        }

        if ($user->hasRole('admin')) {
            $activeAdminCount = User::role('admin')->where('is_active', true)->count();
            if ($activeAdminCount <= 1) {
                throw new \InvalidArgumentException(__('Cannot delete the last active administrator.'));
            }
        }
    }

    /**
     * Prevent admin from deactivating self or last admin.
     */
    protected function guardDeactivate(User $user, User $admin): void
    {
        if ($user->id === $admin->id) {
            throw new \InvalidArgumentException(__('You cannot deactivate your own account.'));
        }

        if ($user->hasRole('admin')) {
            $activeAdminCount = User::role('admin')->where('is_active', true)->count();
            if ($activeAdminCount <= 1) {
                throw new \InvalidArgumentException(__('Cannot deactivate the last active administrator.'));
            }
        }
    }

    protected function createUserRecord(array $data, ?User $actor): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'membership_type' => $data['membership_type'],
            'status' => User::STATUS_ACTIVE,
            'phone_number' => isset($data['phone_number']) ? PhoneHelper::normalize($data['phone_number']) : null,
            'is_active' => true,
            'accepting_orders' => true,
        ]);

        $this->syncUserRoles($user, $data['roles'], $actor);

        return $user;
    }

    /**
     * @param  list<string>  $roleNames
     */
    protected function syncUserRoles(User $user, array $roleNames, ?User $actor): void
    {
        $roleNames = array_values(array_unique($roleNames));
        sort($roleNames);

        if ($user->exists && $user->hasRole('admin') && ! in_array('admin', $roleNames, true)) {
            if ($actor && $actor->id === $user->id) {
                throw new \InvalidArgumentException(__('You cannot remove your own admin role.'));
            }
            $activeAdminCount = User::role('admin')->where('is_active', true)->count();
            if ($activeAdminCount <= 1) {
                throw new \InvalidArgumentException(__('Cannot remove the last active administrator role.'));
            }
        }

        $user->syncRoles($roleNames);
        $this->permissionRegistrar->forgetCachedPermissions();
    }

    protected function createProfileByType(User $user, array $data, Request $request): void
    {
        if ($user->membership_type === User::MEMBERSHIP_RECIPIENT) {
            $idPath = $request->file('id_photo')->store('recipient_id_photos', config('filesystems.private_disk'));

            RecipientProfile::create([
                'user_id' => $user->id,
                'nationality' => $data['nationality'],
                'short_address' => $data['short_address'],
                'id_type' => $data['id_type'],
                'id_number' => $data['id_number'] ?? null,
                'id_photo_path' => $idPath,
            ]);

            RecipientKycDetails::create([
                'user_id' => $user->id,
                'income_band' => $data['income_band'],
                'household_size' => (int) $data['household_size'],
                'marital_status' => $data['marital_status'],
                'is_student' => (bool) $data['is_student'],
                'employment_status' => $data['employment_status'] ?? null,
                'situation_description' => $data['situation_description'] ?? null,
            ]);
        }

        if ($user->membership_type === User::MEMBERSHIP_PROVIDER) {
            $licensePath = $request->file('business_license')->store('provider_documents', config('filesystems.private_disk'));
            $idPath = $request->file('id_or_iqama')->store('provider_documents', config('filesystems.private_disk'));

            $operatingHours = [];
            foreach (array_keys(config('provider.weekdays')) as $day) {
                $operatingHours[$day] = ['closed' => true];
            }

            ProviderProfile::create([
                'user_id' => $user->id,
                'full_name_ar' => $data['full_name_ar'],
                'full_name_en' => $data['full_name_en'],
                'phone_number' => $data['phone_number'],
                'email' => $data['email'],
                'business_name_ar' => $data['business_name_ar'],
                'business_name_en' => $data['business_name_en'],
                'unified_number' => $data['unified_number'],
                'business_category' => $data['business_category'],
                'address_ar' => $data['address_ar'],
                'address_en' => $data['address_en'],
                'city' => $data['city'],
                'region' => $data['region'],
                'location' => $data['location'] ?? null,
            ]);

            ProviderOperatingInfo::create([
                'user_id' => $user->id,
                'operating_hours' => $operatingHours,
                'daily_capacity' => $data['daily_capacity'],
                'service_type' => $data['service_type'],
                'estimated_preparation_order_time' => $data['estimated_preparation_order_time'],
                'adoption_support' => $data['adoption_support'],
            ]);

            ProviderFinancialInfo::create([
                'user_id' => $user->id,
                'bank_name' => $data['bank_name'],
                'iban' => $data['iban'],
                'account_holder_name' => $data['account_holder_name'],
            ]);

            ProviderDocuments::create([
                'user_id' => $user->id,
                'business_license_path' => $licensePath,
                'id_or_iqama_path' => $idPath,
            ]);
        }
    }

    protected function updateUserRecord(User $user, array $data, User $actor): void
    {
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'membership_type' => $data['membership_type'],
            'phone_number' => isset($data['phone_number']) ? PhoneHelper::normalize($data['phone_number']) : null,
        ];

        if (! empty($data['password'] ?? '')) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);
        $this->syncUserRoles($user, $data['roles'], $actor);
    }

    protected function updateProfileByType(User $user, array $data, Request $request): void
    {
        if ($user->membership_type === User::MEMBERSHIP_RECIPIENT && $user->recipientProfile) {
            $profile = $user->recipientProfile;
            $kyc = $user->recipientKycDetails;

            $profileData = [
                'nationality' => $data['nationality'],
                'short_address' => $data['short_address'],
                'id_type' => $data['id_type'],
                'id_number' => $data['id_number'] ?? null,
            ];
            if ($request->hasFile('id_photo')) {
                Storage::disk(config('filesystems.private_disk'))->delete($profile->id_photo_path ?? '');
                $profileData['id_photo_path'] = $request->file('id_photo')->store('recipient_id_photos', config('filesystems.private_disk'));
            }
            $profile->update($profileData);

            $kycData = [
                'income_band' => $data['income_band'],
                'household_size' => (int) $data['household_size'],
                'marital_status' => $data['marital_status'],
                'is_student' => (bool) $data['is_student'],
                'employment_status' => $data['employment_status'] ?? null,
                'situation_description' => $data['situation_description'] ?? null,
            ];
            if ($kyc) {
                $kyc->update($kycData);
            } else {
                RecipientKycDetails::create(array_merge($kycData, ['user_id' => $user->id]));
            }
        }

        if ($user->membership_type === User::MEMBERSHIP_PROVIDER && $user->providerProfile) {
            $profile = $user->providerProfile;
            $operating = $user->providerOperatingInfo;
            $financial = $user->providerFinancialInfo;
            $docs = $user->providerDocuments;

            $profile->update([
                'full_name_ar' => $data['full_name_ar'],
                'full_name_en' => $data['full_name_en'],
                'phone_number' => isset($data['phone_number']) ? PhoneHelper::normalize($data['phone_number']) : $profile->phone_number,
                'email' => $data['email'],
                'business_name_ar' => $data['business_name_ar'],
                'business_name_en' => $data['business_name_en'],
                'unified_number' => $data['unified_number'],
                'business_category' => $data['business_category'],
                'address_ar' => $data['address_ar'],
                'address_en' => $data['address_en'],
                'city' => $data['city'],
                'region' => $data['region'],
                'location' => $data['location'] ?? null,
            ]);

            if ($operating) {
                $operating->update([
                    'operating_hours' => OperatingHoursNormalizer::fromRequest($request),
                    'daily_capacity' => $data['daily_capacity'],
                    'service_type' => $data['service_type'],
                    'estimated_preparation_order_time' => $data['estimated_preparation_order_time'],
                    'adoption_support' => $data['adoption_support'],
                ]);
            }

            if ($financial) {
                $financial->update([
                    'bank_name' => $data['bank_name'],
                    'iban' => $data['iban'],
                    'account_holder_name' => $data['account_holder_name'],
                ]);
            }

            if ($docs) {
                if ($request->hasFile('business_license')) {
                    Storage::disk(config('filesystems.private_disk'))->delete($docs->business_license_path ?? '');
                    $docs->update(['business_license_path' => $request->file('business_license')->store('provider_documents', config('filesystems.private_disk'))]);
                }
                if ($request->hasFile('id_or_iqama')) {
                    Storage::disk(config('filesystems.private_disk'))->delete($docs->id_or_iqama_path ?? '');
                    $docs->update(['id_or_iqama_path' => $request->file('id_or_iqama')->store('provider_documents', config('filesystems.private_disk'))]);
                }
            }
        }
    }
}
