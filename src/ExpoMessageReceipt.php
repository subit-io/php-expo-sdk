<?php


namespace Subit\ExpoSdk;


class ExpoMessageReceipt
{
    protected $id;
    protected $status;
    protected $message;
    protected $details;

    public static function create()
    {
        return new static();
    }

    public function id(string $value)
    {
        $this->id = $value;

        return $this;
    }

    public function status(string $value)
    {
        $this->status = $value;

        return $this;
    }

    public function message(string $value)
    {
        $this->message = $value;

        return $this;
    }

    public function details(object $value)
    {
        $this->details = $value;

        return $this;
    }

    public function toArray()
    {
        $data = [
            'id' => $this->id,
            'status' => $this->status
        ];

        if (! is_null($this->message)) {
            $data['message'] = $this->message;
        }

        if (! is_null($this->details)) {
            $data['details'] = $this->details;
        }

        return $data;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getDetails()
    {
        return $this->details;
    }

}
