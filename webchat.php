<?php
$server = new swoole_websocket_server('0.0.0.0', 9501);

$server->on('open', function (swoole_websocket_server $server, $request) {
    echo "Server: Handshake success with fd{$request->fd}\n";
    echo $server->connection_info;
});

function getRandomColor() {
    $tmp = '0123456789abcdef';
    $color = '#' . substr(str_shuffle($tmp), 0, 6);
    return $color;
}

$server->on('message', function (swoole_websocket_server $server, $frame) {
    echo "Receive from {$frame->fd}: {$frame->data}, opcode: {$frame->opcode}, fin: {$frame->finish}\n";
    $res = json_decode($frame->data, TRUE);

    $data = [
        'uid' => $frame->fd,
        'msg' => $res['msg'],
        'nickname' => $res['nickname']
    ];
    
    if (!isset($_SESSION['bgcolor'])) {
        $_SESSION['bgcolor'] = getRandomColor();
    }
    $data['color'] = $_SESSION['bgcolor'];

    $data = json_encode($data);
    // $data = sprintf('%s SAY: %s', $frame->fd, $data);
    // https://wiki.swoole.com/wiki/page/427.html
    foreach ($server->connection_list() as $fd) {
        $server->push($fd, $data);
    }
});

$server->on('close', function ($ser, $fd) {
    echo "Client {$fd} closed\n";
    unset($_SESSION['bgcolor']);
});

$server->start();