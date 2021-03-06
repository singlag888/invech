<?php

namespace bong\service\queue\Driver;

use app\common\model\Job as JobModel;

use bong\service\queue\Queue;
use bong\service\queue\Contracts\Queue as QueueContract;
use bong\service\queue\Jobs\DatabaseJob;

class DatabaseQueue extends Queue implements QueueContract
{

    protected $table;

    protected $queue_name;//队列名

    protected $retryAfter;

    public function __construct($config)
    {
        $this->table = $config['table'];
        $this->queue_name = $config['queue_name']??'default';
        $this->retryAfter = $config['retry_after']??60;
    }

    public function getQueue($queue)
    {
        return $queue ?: $this->queue_name;
    }

    public function size($queue = null)
    {
        return db($this->table)->where('queue', $this->getQueue($queue))
                    ->count();
    }

    public function push($job, $data = '', $queue = null)
    {
        return $this->pushToDatabase($queue, $this->createPayload($job, $data));
    }

    protected function pushToDatabase($queue, $payload, $delay = 0, $attempts = 0)
    {
        $time = time();
        $data = [
            'queue' => $this->getQueue($queue),
            'payload' => $payload,
            'attempts' => $attempts,
            'reserved_at' => null,
            'available_at' => $time + $delay,
            'created_at' => $time,
        ];
        return db($this->table)->insertGetId($data);
    }

    
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        db()->startTrans();

        if ($job = $this->getNextAvailableJob($queue)) {
            return $this->marshalJob($queue, $job);
        }

        db()->commit();
    }

    protected function getNextAvailableJob($queue)
    {
        $job = db($this->table)->where('queue', $this->getQueue($queue))
                    //->lockForUpdate()                    
                    ->where(function ($query) {
                        $this->isAvailable($query);
                        $this->isReservedButExpired($query);
                    })
                    ->order('id asc')
                    ->find();

        return $job ? ((object) $job) : null;
    }

    protected function isAvailable($query)
    {
        $query->where(function ($query) {
            $query->whereNull('reserved_at')
                  ->where('available_at', '<=', time());
        });
    }

    protected function isReservedButExpired($query)
    {
        $expiration = time() + $this->retryAfter;

        $query->whereOr(function ($query) use ($expiration) {
            $query->where('reserved_at', '<=', $expiration);
        });
    }

    protected function marshalJob($queue, $job)
    {
        $job = $this->markJobAsReserved($job);

        db()->commit();

        return new DatabaseJob($this, $job, $queue);
    }

    protected function markJobAsReserved($job)
    {
        $job->reserved_at = time();
        $job->attempts++;
        db($this->table)->where('id', $job->id)->update([
            'reserved_at' => $job->reserved_at,
            'attempts' => $job->attempts,
        ]);

        return $job;
    }

    //后台处理完后删除
    public function deleteReserved($queue, $id)
    {
        db()->startTrans();

        if (db($this->table)->find($id)) {
            db($this->table)->where('id', $id)->delete();
        }

        db()->commit();
    }

}
