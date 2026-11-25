<?php
// api.php: keeps the "not-a-website" state on the server (in the PHP session).
session_start();
header('Content-Type: application/json');

const PASSWORD = 'nogame';   // hidden in an HTML comment in index.php
const LAST_STAGE = 5;

if (!isset($_SESSION['stage'])) {
    $_SESSION = ['stage' => 0, 'started' => time(), 'clicks' => 0, 'wrong' => 0];
}

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $in['action'] ?? 'state';
$stage = $_SESSION['stage'];
$ok = null;
$msg = '';

switch ($action) {
    case 'advance':
        $_SESSION['clicks'] += max(0, min(500, (int)($in['clicks'] ?? 0)));
        $pass = false;
        if ($stage === 0)      $pass = ((int)($in['hits'] ?? 0)) >= 5;
        elseif ($stage === 1)  $pass = !empty($in['dragged']);
        elseif ($stage === 2) {
            $pass = strtolower(trim($in['password'] ?? '')) === PASSWORD;
            if (!$pass) { $_SESSION['wrong']++; $msg = 'wrong'; }
        }
        elseif ($stage === 3)  $pass = !empty($in['pressed']);
        elseif ($stage === 4) {
            $pass = !empty($in['disclaimed'])
                 && !empty($in['noCookies'])
                 && !empty($in['notAWebsite']);
        }

        $ok = $pass;
        if ($pass && $stage < LAST_STAGE) {
            $_SESSION['stage']++;
            if ($_SESSION['stage'] === LAST_STAGE) $_SESSION['finished'] = time();
        }
        break;

    case 'reset':
        $_SESSION = [];
        $_SESSION = ['stage' => 0, 'started' => time(), 'clicks' => 0, 'wrong' => 0];
        break;
}

$end = $_SESSION['finished'] ?? time();
echo json_encode([
    'ok'      => $ok,
    'msg'     => $msg,
    'stage'   => $_SESSION['stage'],
    'wrong'   => $_SESSION['wrong'],
    'clicks'  => $_SESSION['clicks'],
    'seconds' => $end - $_SESSION['started'],
]);
