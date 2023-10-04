<?php

namespace SimpleProctoring\Proctoring;

class ProctoringSession
{
    private string $sessionId;
    private string $studentId;
    private string $proctorId;
    private bool $isScreenSharing = false;
    private bool $isWebcamSharing = false;

    public function __construct(string $studentId, string $proctorId)
    {
        $this->sessionId = uniqid("",true);
        $this->studentId = $studentId;
        $this->proctorId = $proctorId;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function startScreenSharing(): void
    {
        // Start screen sharing
        $this->isScreenSharing = true;
    }

    public function stopScreenSharing(): void
    {
        // Stop screen sharing
        $this->isScreenSharing = false;
    }

    public function startWebcamSharing(): void
    {
        // Start webcam sharing
        $this->isWebcamSharing = true;
    }

    public function stopWebcamSharing(): void
    {
        // Stop webcam sharing
        $this->isWebcamSharing = false;
    }

    public function isScreenSharing(): bool
    {
        return $this->isScreenSharing;
    }

    public function isWebcamSharing(): bool
    {
        return $this->isWebcamSharing;
    }
}