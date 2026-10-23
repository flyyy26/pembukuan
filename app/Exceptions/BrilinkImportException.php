<?php

namespace App\Exceptions;

use Exception;

class BrilinkImportException extends Exception
{
    protected string $userMessage;
    protected array $details;

    public function __construct(
        string $userMessage,
        array $details = [],
        string $technicalMessage = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $this->userMessage = $userMessage;
        $this->details = $details;

        parent::__construct(
            $technicalMessage ?: $userMessage,
            $code,
            $previous
        );
    }

    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * Format pesan lengkap untuk ditampilkan di UI.
     */
    public function toDisplayMessage(): string
    {
        $msg = $this->userMessage;

        if (!empty($this->details)) {
            $msg .= "\n\n• " . implode("\n• ", $this->details);
        }

        return $msg;
    }
}