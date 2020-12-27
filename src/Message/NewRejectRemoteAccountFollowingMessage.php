<?php

namespace App\Message;

class NewRejectRemoteAccountFollowingMessage
{
    protected $account_rejecting_id;
    protected $want_to_be_follower_account_id;
    protected $activtypubFollowActivityData;

    public function __construct(string $account_rejecting_id, string $want_to_be_follower_account_id, array $activtypubFollowActivityData)
    {
        $this->account_rejecting_id = $account_rejecting_id;
        $this->want_to_be_follower_account_id = $want_to_be_follower_account_id;
        $this->activtypubFollowActivityData = $activtypubFollowActivityData;
    }

    public function getAccountRejectingId(): string
    {
        return $this->account_rejecting_id;
    }

    public function getWantToBeFollowerAccountId(): string
    {
        return $this->want_to_be_follower_account_id;
    }

    public function getActivtypubFollowActivityData(): array
    {
        return $this->activtypubFollowActivityData;
    }
}
