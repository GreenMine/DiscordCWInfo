<?php

date_default_timezone_set('Europe/Moscow');

include __DIR__.'/vendor/autoload.php';

$discord = new \Discord\Discord([
    'token' => 'NTU0MzQ1Nzc0OTk4MDkzODI1.D2bSQQ.qBztT9EkrfY_emPYPF36nwUFfxM',
]);

$GLOBALS['settings'] = [
  'onehoursnoti' => true,
  'twohoursnoti' => true,
  'startvote' => false
];

$GLOBALS['callvote'] = array();

$discord->on('ready', function ($discord){
    $discord->loop->addPeriodicTimer(15,function () use ($discord) {
        $timecw = [
            'Mon' => ['14:00'],
            'Tue' => ['20:00'],
            'Wed' => ['03:00'],
            'Thu' => ['20:00'],
            'Fri' => ['03:00'],
            'Sat' => ['14:00','20:00'],
            'Sun' => ['22:00', '14:00']
        ];
        foreach ($timecw[date('D')] as $item => $data) {
            $date = strtotime($timecw[date('D')][$item]) - time();
            $channel = $discord->factory(\Discord\Parts\Channel\Channel::class, ['id' => '512737563514109953']);
            $type = 'Для участия в голосовании выберите один из вариантов(цифра)' . PHP_EOL . '1. Да я иду.' . PHP_EOL . '2. Возможно буду.' . PHP_EOL . '3. Задержусь.' . PHP_EOL . '4. Нет.';
            if ($date > 0) {
                if ($date - 7200 < 0 and $date - 3600 > 0) {
                    if ($GLOBALS['settings']['twohoursnoti']) {
                        $channel->sendMessage('```css' . PHP_EOL . 'До Клановой Войны осталось 2 часа, начало голосования.' . PHP_EOL . $type . PHP_EOL . '```');
                        $GLOBALS['settings']['twohoursnoti'] = false;
                        $GLOBALS['settings']['startvote'] = true;
                        $GLOBALS['settings']['timestart'] = time();
                    }
                } elseif ($date - 3600 < 0 and $date > 0) {
                    if ($GLOBALS['settings']['onehoursnoti']) {
                        $channel->sendMessage('```css' . PHP_EOL . 'До Клановой Войны остался 1 час, начало голосования.' . PHP_EOL . $type . PHP_EOL . '```');
                        $GLOBALS['settings']['onehoursnoti'] = false;
                        $GLOBALS['settings']['startvote'] = true;
                        $GLOBALS['settings']['timestart'] = time();
                    }
                }
            }
        }
        if($GLOBALS['settings']['startvote']) {
            $ye = array();
            $mb = array();
            $li = array();
            $no = array();
            if (time() - $GLOBALS['settings']['timestart'] > 2*60) {
                foreach ($GLOBALS['callvote'] as $key => $value) {
                    switch ($value){
                        case '1':
                            $ye[] =  $key;
                            break;
                        case '2':
                            $mb[] =  $key;
                            break;
                        case '3':
                            $li[] =  $key;
                            break;
                        case '4':
                            $no[] =  $key;
                            break;
                    }
                }
                $list = 'Пойдут:' . PHP_EOL . implode(PHP_EOL, $ye) . PHP_EOL . PHP_EOL . 'Возможно будут:' . PHP_EOL . implode(PHP_EOL, $mb) . PHP_EOL . PHP_EOL . 'Задержаться:' . PHP_EOL . implode(PHP_EOL, $li) . PHP_EOL . PHP_EOL . 'Не пойдут:' . PHP_EOL . implode(PHP_EOL, $no);
                $channel->sendMessage('```css' . PHP_EOL . 'Голосование закончено, список участников: '. PHP_EOL . $list  . PHP_EOL . '```');
                $GLOBALS['settings']['startvote'] = false;
                $GLOBALS['callvote'] = [];
            }
        }
    });

    $discord->on('message', function ($message, $discord) {
        $variation = ['Да я иду.', 'Возможно буду.', 'Задержусь.', 'Нет.'];
        if($GLOBALS['settings']['startvote']) {
            $channel = $discord->factory(\Discord\Parts\Channel\Channel::class, ['id' => $message->channel_id]);
            if($message->content <= 4 and $message->content > 0) {
                if(array_key_exists($message->author->user->username, $GLOBALS['callvote'])) {
                    $channel->sendMessage('```css' . PHP_EOL . 'Ошибка! Вы уже голосовали в данном опросе.' . PHP_EOL . '```');
                } else {
                    $GLOBALS['callvote'][$message->author->user->username] = $message->content;
                    $channel->sendMessage('```css' . PHP_EOL . 'Вы успешно выбрали вариант "' . $variation[$message->content - 1] . '"' . PHP_EOL . '```');
                }
            }
        }
    });

});

$discord->run();
