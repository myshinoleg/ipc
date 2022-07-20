<?php

$parentPid = posix_getpid();
echo "parent progress pid:{$parentPid}\n";

// Определяем функцию обработки сигнала
function signalHandler()
{
	$pid = posix_getpid();
	echo "{$pid} progress,oh no ,I'm killed!\n";
	exit(1);
}

$pid = pcntl_fork();

if ( $pid == -1)
{
	// не удалось создать
	exit("fork progress error!\n");
}
else if ($pid == 0)
{
	// программа выполнения дочернего процесса
	// Регистрация функции обработки сигнала
	declare(ticks=2);
	pcntl_signal(SIGINT, "signalHandler");
	$pid = posix_getpid();
	while (true)
	{
		echo "{$pid} child progress is running!\n";
		sleep(1);
	}

	exit("({$pid})child progress end!\n");
}
else
{
	// Родительский процесс выполняет программу
	$childList[$pid] = 1;
	// Через 5 секунд родительский процесс отправляет сигнал sigint дочернему процессу.
	sleep(5);
	posix_kill($pid, SIGINT);
	sleep(5);
}
echo "({$parentPid})main progress end!\n";