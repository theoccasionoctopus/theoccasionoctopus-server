<?php

namespace App\Message;

class NewAcceptRemoteAccountFollowingMessage
{
    protected $account_accepting_id;
    protected $want_to_be_follower_account_id;
    protected $activtypubFollowActivityData;

    public function __construct(string $account_accepting_id, string $want_to_be_follower_account_id, array $activtypubFollowActivityData)
    {
        $this->account_accepting_id = $account_accepting_id;
        $this->want_to_be_follower_account_id = $want_to_be_follower_account_id;
        $this->activtypubFollowActivityData = $activtypubFollowActivityData;
    }

    public function getAccountAcceptingId(): string
    {
        return $this->account_accepting_id;
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
