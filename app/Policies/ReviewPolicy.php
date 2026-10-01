<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Any authenticated account may write a review (viewers can become
     * travellers without a separate account).
     */
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Review $review): bool
    {
        return $review->isOwnedBy($user);
    }

    public function delete(User $user, Review $review): bool
    {
        return $review->isOwnedBy($user);
    }
}
