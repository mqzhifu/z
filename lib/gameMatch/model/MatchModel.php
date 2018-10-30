<?php
namespace app\Models;

use Server\CoreBase\Model;

class MatchModel extends Model {


    public function add($info) {
        $redis = get_instance()->getAsynPool('redisDao');
        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_match";
        yield $redis->getCoroutine()->LPUSH($cacheKey, json_encode($info));
        return true;
    }

    public function addSingle($info) {
        $redis = get_instance()->getAsynPool('redisDao');
        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_matchSingle";
        $tag = yield $redis->getCoroutine()->LPUSH($cacheKey, json_encode($info));

        return $tag;
    }

    public function addGame($info) {
        $redis = get_instance()->getAsynPool('redisDao');
        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_matchGame";
        $tag = yield $redis->getCoroutine()->LPUSH($cacheKey, json_encode($info));

        return $tag;
    }

    public function addLeaveRoom($roomId) {
        $redis = get_instance()->getAsynPool('redisDao');
        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_matchLeave_{$roomId}";
        yield $redis->getCoroutine()->setex($cacheKey, 60, 1);
        return true;
    }

    public function addLeaveSingle($uId) {
        $redis = get_instance()->getAsynPool('redisDao');
        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_matchLeaveSingle_{$uId}";
        yield $redis->getCoroutine()->setex($cacheKey, 60, 1);
        return true;
    }
}