<?php

namespace UserSpace\Common\Module\Queue\Src\Infrastructure;

// Защита от прямого доступа к файлу
use UserSpace\Common\Module\Queue\Src\Domain\JobRepositoryInterface;
use UserSpace\Common\Module\Queue\Src\Domain\MessageInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Отправляет задачи в очередь.
 */
class QueueDispatcher
{

    private JobRepositoryInterface $jobRepository;

    public function __construct(JobRepositoryInterface $jobRepository)
    {
        $this->jobRepository = $jobRepository;
    }

    /**
     * Добавляет задачу в очередь.
     *
     * @param MessageInterface $message Объект сообщения для постановки в очередь.
     * @param int $delay_seconds Задержка перед выполнением в секундах.
     *
     * @return int|null ID созданной задачи или null в случае ошибки.
     */
    public function dispatch(MessageInterface $message, int $delay_seconds = 0): ?int
    {
        // Этот метод нужно будет реализовать в JobRepository
        return $this->jobRepository->create(get_class($message), $message->toArray(), $delay_seconds);
    }
}