<?php

$parentPid = posix_getpid();
$childList = [];
$queueId = ftok(__FILE__, 'm');
$msgQueue = msg_get_queue($queueId);
const MSG_TYPE = 1;

// пишем в очередь
function producer()
{
	global $msgQueue;
	$message = 'hello world';
	$pid = posix_getpid();
	echo "producer pid : {$pid} \n";
	echo "producer send message {$message} \n";
	msg_send($msgQueue,MSG_TYPE,$message);
	$rand = rand(1,3);
	sleep($rand);
}

// читаем очередь
function consumer()
{
	global $msgQueue;
	$pid = posix_getpid();
	echo "consumer pid: {$pid} \n";
	msg_receive($msgQueue,MSG_TYPE,$msgType,1024,$message);
	echo "consumer get message: {$message} \n";
	$rand = rand(1,3);
	sleep($rand);
}

function createProgress($callback)
{
	$pid = pcntl_fork();
	if ($pid == -1)
	{
		// не удалось создать
		exit("fork progress error!\n");
	}
	elseif ($pid == 0)
	{
		// программа выполнения дочернего процесса
		$pid = posix_getpid();
		$callback();
		exit("({$pid})child progress end!\n");
	}
	else
	{
		// Родительский процесс выполняет программу
		return $pid;
	}
}

// делаем продюсеров
for ($i = 0; $i < 1; $i ++ )
{
	$pid = createProgress('producer');
	$childList[$pid] = 1;
}

// делаем подписчиков
for ($i = 0; $i < 1; $i ++ )
{
	$pid = createProgress('consumer');
	$childList[$pid] = 1;
}

// Ожидание завершения всех дочерних процессов
while(!empty($childList))
{
	$childPid = pcntl_wait($status);
	if ($childPid > 0)
	{
		unset($childList[$childPid]);
	}
}
echo "({$parentPid})main progress end!\n";