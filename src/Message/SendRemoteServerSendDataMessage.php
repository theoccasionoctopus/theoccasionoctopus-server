<?php

namespace App\Message;

class SendRemoteServerSendDataMessage
{
    protected $remote_server_send_data_id;


    public function __construct(string $remote_server_send_data_id)
    {
        $this->remote_server_send_data_id = $remote_server_send_data_id;
    }

    public function getRemoteServerSendDataId(): string
    {
        return $this->remote_server_send_data_id;
    }
}
