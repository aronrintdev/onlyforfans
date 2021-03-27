<?php

namespace App\Models\Traits;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait ShareableTraits
{
    /**
     * Get the name of the shareablesTable
     *
     * @return string
     */
    public function getShareableTable(): string
    {
        return 'shareables';
    }

    /**
     * Grants access to this resource for a user
     *
     * @param User $user
     * @param string $accessLevel
     * @param array $cattrs  Custom attributes
     * @param array $meta  Metadata
     * @return void
     */
    public function grantAccess(User $user, string $accessLevel, $cattrs = [], $meta = []): void
    {
        $index = [
            'sharee_id' => $user->getKey(),
            'shareable_type' => $this->getMorphString(),
            'shareable_id' => $this->getKey(),
        ];

        $shareable = DB::table($this->getShareableTable())->where($index)->first();
        if (isset($shareable)) {
            $cattrs = array_merge(json_decode($shareable->cattrs, true), $cattrs);
            $meta = array_merge(json_decode($shareable->meta, true), $meta);
        }
        $data = [
            'is_approved' => true,
            'access_level' => $accessLevel,
            'cattrs' => json_encode($cattrs),
            'meta' => json_encode($meta),
            'created_at' => isset($shareable) ? $shareable->created_at : Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        DB::table($this->getShareableTable())->updateOrInsert($index, $data);
    }

    /**
     * Revokes access to this resource for a user
     *
     * @param User $user
     * @param array $cattrs  Custom attributes
     * @param array $meta  Metadata
     * @return void
     */
    public function revokeAccess(User $user, $cattrs = [], $meta = []): void
    {
        //
    }
}