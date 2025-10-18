<?php

namespace UserSpace\Core\Queue;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Управляет состоянием и логами воркера очереди.
 */
class QueueStatus
{
	private const OPTION_NAME = 'userspace_queue_status';
	private const LOG_LIMIT = 20;

	/**
	 * Получает текущий статус и логи.
	 *
	 * @return array{state: string, last_start: int, last_finish: int, last_duration: int, jobs_processed: int, log: array}
	 */
	public function getStatus(): array
	{
		$default = [
			'state'          => 'idle',
			'last_start'     => 0,
			'last_finish'    => 0,
			'last_duration'  => 0,
			'jobs_processed' => 0,
			'log'            => [],
		];

		$status = get_option(self::OPTION_NAME, $default);

		// Если воркер "завис" (работает дольше 5 минут), считаем его остановившимся
		if ($status['state'] === 'running' && (time() - $status['last_start']) > 300) {
			$status['state'] = 'stalled';
			$this->log('Worker seems to be stalled. Marking as stopped.');
		}

		return array_merge($default, $status);
	}

	/**
	 * Записывает сообщение в лог.
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function log(string $message): void
	{
		$status = $this->getStatus();

		// Добавляем сообщение в начало массива
		array_unshift($status['log'], sprintf('[%s] %s', wp_date('Y-m-d H:i:s'), $message));

		// Ограничиваем размер лога
		$status['log'] = array_slice($status['log'], 0, self::LOG_LIMIT);

		update_option(self::OPTION_NAME, $status, false);
	}

	/**
	 * Отмечает начало работы воркера.
	 *
	 * @return void
	 */
	public function startRun(): void
	{
		$status = $this->getStatus();
		$status['state'] = 'running';
		$status['last_start'] = time();
		$status['jobs_processed'] = 0;

		update_option(self::OPTION_NAME, $status, false);
		$this->log('Worker batch started.');
	}

	/**
	 * Отмечает завершение работы воркера.
	 *
	 * @param int $jobsProcessed
	 *
	 * @return void
	 */
	public function endRun(int $jobsProcessed): void
	{
		$status = $this->getStatus();
		$status['state'] = 'idle';
		$status['last_finish'] = time();
		$status['last_duration'] = $status['last_finish'] - $status['last_start'];
		$status['jobs_processed'] = $jobsProcessed;

		update_option(self::OPTION_NAME, $status, false);
		$this->log(sprintf('Worker batch finished. Processed %d job(s) in %d sec.', $jobsProcessed, $status['last_duration']));
	}
}