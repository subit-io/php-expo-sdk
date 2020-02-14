<?php


namespace Subit\ExpoSdk;

use Subit\ExpoSdk\Exceptions\NotAValidPriorityException;
use Subit\ExpoSdk\Exceptions\InvalidNotificationDataException;

class ExpoMessage
{
    protected $to;
    protected $title;
    protected $subtitle;
    protected $body;
    protected $sound = 'default';
    protected $badge = -1;
    protected $ttl = -1;
    protected $expiration;
    protected $priority;
    protected $channelId;
    protected $jsonData;

    public function __construct(string $body = '')
    {
        $this->body = $body;
    }

    public static function create(string $body = '')
    {
        return new static($body);
    }

    public function to(string $value)
    {
        $this->to = $value;

        return $this;
    }

    public function title(string $value)
    {
        $this->title = $value;

        return $this;
    }

    public function subtitle(string $value)
    {
        $this->subtitle = $value;

        return $this;
    }

    public function body(string $value)
    {
        $this->body = $value;

        return $this;
    }

    public function enableSound()
    {
        $this->sound = 'default';

        return $this;
    }

    public function disableSound()
    {
        $this->sound = null;

        return $this;
    }

    public function badge(int $value)
    {
        $this->badge = $value;

        return $this;
    }

    public function ttl(int $ttl)
    {
        $this->ttl = $ttl;

        return $this;
    }

    public function expiration(int $value)
    {
        $this->expiration = $value;

        return $this;
    }

    public function priority(string $value)
    {
        switch($value) {
            case Priority::HIGH_PRIORITY:
            case Priority::NORMAL_PRIORITY:
            case Priority::DEFAULT_PRIORITY:
                $this->priority = $value;
                break;
            default:
                throw new InvalidNotificationDataException('Invalid priority passed to priority(). See Priority class for possibilities');
        }

        return $this;
    }

    public function channelId(string $channelId)
    {
        $this->channelId = $channelId;

        return $this;
    }

    public function jsonData($data)
    {
        if (is_array($data)) {
            $data = json_encode($data);
        } elseif (is_string($data)) {
            @json_decode($data);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidNotificationDataException('Invalid json format passed to the setJsonData().');
            }
        }

        $this->jsonData = $data;

        return $this;
    }

    public function toArray()
    {
        $data = [];

        if (! is_null($this->to)) {
            $data['to'] = $this->to;
        }

        if ($this->badge >= 0) {
            $data['badge'] = $this->badge;
        }

        if ($this->ttl >= 0) {
            $data['ttl'] = $this->ttl;
        }

        if (! is_null($this->title)) {
            $data['title'] = $this->title;
        }

        if (! is_null($this->subtitle)) {
            $data['subtitle'] = $this->subtitle;
        }

        if (! is_null($this->body)  && ! empty($this->body)) {
            $data['body'] = $this->body;
        }

        if (! is_null($this->sound)) {
            $data['sound'] = $this->sound;
        }

        if (! is_null($this->expiration)) {
            $data['expiration'] = $this->expiration;
        }

        if (! is_null($this->priority)) {
            $data['priority'] = $this->priority;
        }

        if (! is_null($this->channelId)) {
            $data['channelId'] = $this->channelId;
        }

        if (isset($this->jsonData)) {
            $data['data'] = $this->jsonData;
        }

        return $data;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getSubtitle()
    {
        return $this->subtitle;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getSound(): string
    {
        return $this->sound;
    }

    public function getBadge(): int
    {
        return $this->badge;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }

    public function getExpiration()
    {
        return $this->expiration;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function getChannelId()
    {
        return $this->channelId;
    }

    public function getJsonData()
    {
        return $this->jsonData;
    }
}
