<?php

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require_once 'vendor/autoload.php';
require_once 'ProctoringSession.php';

class ProctoringServer implements MessageComponentInterface
{
    protected $proctoringSessions;

    public function __construct()
    {
        $this->proctoringSessions = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        echo "WebSocket connection opened: {$conn->resourceId}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);

        if ($data['type'] === 'join') {
            // Create a new ProctoringSession instance for the WebSocket connection
            $proctoringSession = new ProctoringSession($data['studentId'], $data['proctorId']);

            // Store the ProctoringSession instance in the data structure
            $this->proctoringSessions->attach($from, $proctoringSession);

            // Send a message to the client indicating that the session has started
            $from->send(json_encode(['type' => 'session-started', 'sessionId' => $proctoringSession->getSessionId()]));
        } else if ($data['type'] === 'start-screen-sharing') {
            // Get the ProctoringSession instance for the WebSocket connection
            $proctoringSession = $this->proctoringSessions[$from];

            // Start screen sharing for the ProctoringSession instance
            $proctoringSession->startScreenSharing();

            // Send a message to the other client indicating that screen sharing has started
            $otherConn = ($from === $proctoringSession->getStudentConnection()) ? $proctoringSession->getProctorConnection() : $proctoringSession->getStudentConnection();
            $otherConn->send(json_encode(['type' => 'screen-sharing-started']));
        } else if ($data['type'] === 'stop-screen-sharing') {
            // Get the ProctoringSession instance for the WebSocket connection
            $proctoringSession = $this->proctoringSessions[$from];

            // Stop screen sharing for the ProctoringSession instance
            $proctoringSession->stopScreenSharing();

            // Send a message to the other client indicating that screen sharing has stopped
            $otherConn = ($from === $proctoringSession->getStudentConnection()) ? $proctoringSession->getProctorConnection() : $proctoringSession->getStudentConnection();
            $otherConn->send(json_encode(['type' => 'screen-sharing-stopped']));
        } else if ($data['type'] === 'start-webcam-sharing') {
            // Get the ProctoringSession instance for the WebSocket connection
            $proctoringSession = $this->proctoringSessions[$from];

            // Start webcam sharing for the ProctoringSession instance
            $proctoringSession->startWebcamSharing();

            // Send a message to the other client indicating that webcam sharing has started
            $otherConn = ($from === $proctoringSession->getStudentConnection()) ? $proctoringSession->getProctorConnection() : $proctoringSession->getStudentConnection();
            $otherConn->send(json_encode(['type' => 'webcam-sharing-started']));
        } else if ($data['type'] === 'stop-webcam-sharing') {
            // Get the ProctoringSession instance for the WebSocket connection
            $proctoringSession = $this->proctoringSessions[$from];

            // Stop webcam sharing for the ProctoringSession instance
            $proctoringSession->stopWebcamSharing();

            // Send a message to the other client indicating that webcam sharing has stopped
            $otherConn = ($from === $proctoringSession->getStudentConnection()) ? $proctoringSession->getProctorConnection() : $proctoringSession->getStudentConnection();
            $otherConn->send(json_encode(['type' => 'webcam-sharing-stopped']));
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        echo "WebSocket connection closed: {$conn->resourceId}\n";

        // Remove the ProctoringSession instance from the data structure
        $this->proctoringSessions->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "WebSocket error: {$e->getMessage()}\n";

        // Close the connection on error
        $conn->close();
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ProctoringServer()
        )
    ),
    9501
);

$server->run();