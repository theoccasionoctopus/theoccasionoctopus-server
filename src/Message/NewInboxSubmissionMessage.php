<?php

namespace App\Message;

class NewInboxSubmissionMessage
{
    protected $inbox_submission_id;


    public function __construct(string $inbox_submission_id)
    {
        $this->inbox_submission_id = $inbox_submission_id;
    }

    /**
     * @return string
     */
    public function getInboxSubmissionId(): string
    {
        return $this->inbox_submission_id;
    }
}
