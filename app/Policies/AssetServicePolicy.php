<?php

namespace App\Policies;

use App\Models\AssetService;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AssetServicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isUser();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AssetService $assetService): bool
    {
        return $user->isAdmin() || $user->isUser();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isUser();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AssetService $assetService): bool
    {
        return $user->isAdmin() || $user->isUser();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AssetService $assetService): bool
    {
        return $user->isAdmin() || $user->isUser();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AssetService $assetService): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AssetService $assetService): bool
    {
        return false;
    }
}
