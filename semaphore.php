<?php
$parentPid = posix_getpid();
echo "parent progress pid:{$parentPid}\n";
$childList = array();

// Создать общую память, создать семафор, определить общий ключ
$shmId = ftok(__FILE__,'m');
$semId = ftok(__FILE__,'s');

$shareMemory = shm_attach($shmId);
$signal = sem_get($semId);
const SHARE_KEY = 1;

// Режиссер
function producer()
{
	global $shareMemory;
	global $signal;
	$pid = posix_getpid();
	$repeatNum = 5;

	for ($i = 1; $i <= $repeatNum; $i++)
	{
		// Получить семафор
		sem_acquire($signal);

		if (shm_has_var($shareMemory, SHARE_KEY))
		{
			// оценивается плюс один
			$count = shm_get_var($shareMemory, SHARE_KEY);
			$count++;
			shm_put_var($shareMemory, SHARE_KEY, $count);
			echo "({$pid}) count: {$count}\n";
		}
		else
		{
			// Нет значения, инициализировать
			shm_put_var($shareMemory, SHARE_KEY, 0);
			echo "({$pid}) count: 0\n";
		}
		// закончиться
		sem_release($signal);

		$rand = rand(1, 3);
		sleep($rand);
	}
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
// 3 процесса записи
for ($i = 0; $i < 3; $i ++ )
{
	$pid = createProgress('producer');
	$childList[$pid] = 1;
	echo "create producer child progress: {$pid} \n";
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
// Освободим разделяемую память и семафор
shm_remove($shareMemory);
sem_remove($signal);
echo "({$parentPid})main progress end!\n";