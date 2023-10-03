<?php

use SimpleProctoring\Authorization\Authorization;
use SimpleProctoring\Proctoring\ProctoringSession;

$authorization = new Authorization($studentId, $proctorId);
$proctoringSession = new ProctoringSession($studentId, $proctorId);

if (!$authorization->isAuthorized()) {
    // Redirect to the login page
    header('Location: login.php');
    exit;
}

if (!$authorization->hasPermission('view_live_stream')) {
    // Redirect to the dashboard page
    header('Location: dashboard.php');
    exit;
}

// Render the live view
echo renderLiveView($proctoringSession);

function renderLiveView(ProctoringSession $proctoringSession): string {
    // Render the live view
}