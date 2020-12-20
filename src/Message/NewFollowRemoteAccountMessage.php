<?php

namespace App\Message;

class NewFollowRemoteAccountMessage
{
    protected $account_id;
    protected $follows_account_id;

    /**
     * NewFollowRemoteAccountMessage constructor.
     * @param $account_id
     * @param $follows_account_id
     */
    public function __construct(string $account_id, string $follows_account_id)
    {
        $this->account_id = $account_id;
        $this->follows_account_id = $follows_account_id;
    }

    /**
     * @return string
     */
    public function getAccountId(): string
    {
        return $this->account_id;
    }

    /**
     * @return string
     */
    public function getFollowsAccountId(): string
    {
        return $this->follows_account_id;
    }
}
