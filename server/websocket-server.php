<?php
use Swoole\WebSocket\Server;

use SimpleProctoring\Proctoring\ProctoringSession;

$server = new Server('0.0.0.0', 9501);

$server->on('start', function () {
    echo "WebSocket server started\n";
});

$server->on('open', function (Server $server, Swoole\Http\Request $request) {
    echo "WebSocket connection opened: {$request->fd}\n";
});

$server->on('message', function (Server $server, Swoole\WebSocket\Frame $frame) {
    $data = json_decode($frame->data, true);

    if ($data['type'] === 'join') {
        // Create a new ProctoringSession instance for the WebSocket connection
        $proctoringSession = new ProctoringSession($data['studentId'], $data['proctorId']);

        // Store the ProctoringSession instance in the data structure
        $proctoringSessions[$frame->fd] = $proctoringSession;

        // Send a message to the client indicating that the session has started
        $server->push($frame->fd, json_encode(['type' => 'session-started', 'sessionId' => $proctoringSession->getSessionId()]));
    } else if ($data['type'] === 'start-screen-sharing') {
        // Get the ProctoringSession instance for the WebSocket connection
        $proctoringSession = $proctoringSessions[$frame->fd];

        // Start screen sharing for the ProctoringSession instance
        $proctoringSession->startScreenSharing();

        // Send a message to the other client indicating that screen sharing has started
        $otherFd = ($frame->fd === $proctoringSession->getStudentId()) ? $proctoringSession->getProctorId() : $proctoringSession->getStudentId();
        $server->push($otherFd, json_encode(['type' => 'screen-sharing-started']));
    } else if ($data['type'] === 'stop-screen-sharing') {
        // Get the ProctoringSession instance for the WebSocket connection
        $proctoringSession = $proctoringSessions[$frame->fd];

        // Stop screen sharing for the ProctoringSession instance
        $proctoringSession->stopScreenSharing();

        // Send a message to the other client indicating that screen sharing has stopped
        $otherFd = ($frame->fd === $proctoringSession->getStudentId()) ? $proctoringSession->getProctorId() : $proctoringSession->getStudentId();
        $server->push($otherFd, json_encode(['type' => 'screen-sharing-stopped']));
    } else if ($data['type'] === 'start-webcam-sharing') {
        // Get the ProctoringSession instance for the WebSocket connection
        $proctoringSession = $proctoringSessions[$frame->fd];

        // Start webcam sharing for the ProctoringSession instance
        $proctoringSession->startWebcamSharing();

        // Send a message to the other client indicating that webcam sharing has started
        $otherFd = ($frame->fd === $proctoringSession->getStudentId()) ? $proctoringSession->getProctorId() : $proctoringSession->getStudentId();
        $server->push($otherFd, json_encode(['type' => 'webcam-sharing-started']));
    } else if ($data['type'] === 'stop-webcam-sharing') {
        // Get the ProctoringSession instance for the WebSocket connection
        $proctoringSession = $proctoringSessions[$frame->fd];

        // Stop webcam sharing for the ProctoringSession instance
        $proctoringSession->stopWebcamSharing();

        // Send a message to the other client indicating that webcam sharing has stopped
        $otherFd = ($frame->fd === $proctoringSession->getStudentId()) ? $proctoringSession->getProctorId() : $proctoringSession->getStudentId();
        $server->push($otherFd, json_encode(['type' => 'webcam-sharing-stopped']));
    }
});

$server->on('close', function (Server $server, int $fd) {
    echo "WebSocket connection closed: $fd\n";
});

$server->start();
?>