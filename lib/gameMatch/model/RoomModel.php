<?php
namespace app\Models;

use Server\CoreBase\Model;

class RoomModel extends Model {

    public function addLckList($lckDetail) {
        $redis = get_instance()->getAsynPool('redisDao');
        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_lckinfo";
        yield $redis->getCoroutine()->LPUSH($cacheKey, json_encode($lckDetail));

        return true;
    }

    public function getRoomId() {
        $id = md5(uniqid(md5(microtime(true)), true));
        return $id;
    }

    public function addLck($data, $insertId) {
        $dao = get_instance()->getAsynPool('dormDao');
        $time = time();
        foreach ($data as $row) {
            $suffix = $row['dormId'] % 10;
            $table = "dorm_lck_{$suffix}";

            $row['drlId'] = $insertId;
            yield $dao->dbQueryBuilder->insert($table)
                ->set('user_id', $row['uId'])
                ->set('dorm_id', $row['dormId'])
                ->set('game_id', $row['gameId'])
                ->set('dl_content', json_encode($row))
                ->set('dl_dateline', $time)
                ->coroutineSend();
        }

        return true;
    }

    public function addLckDetail($data) {
        $dao = get_instance()->getAsynPool('dormDao');
        $suffix = date('Y');
        $table = 'dorm_room_lck_' . $suffix;
        $time = time();
        $row = yield $dao->dbQueryBuilder->insert($table)->set('drl_content', json_encode($data))->set('drl_dateline', $time)->coroutineSend();
        $id = '';
        if ($row['result'] == 1) {
            $id = $row['insert_id'] . $suffix;
        }
        return $id;
    }

    public function getRoomInfo($roomId) {
        $redis = get_instance()->getAsynPool('redisDao');
        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_room_{$roomId}";
        $data = yield $redis->getCoroutine()->get($cacheKey);
        $data = json_decode($data, true);
        return $data;
    }

    public function getRoomInfoSynch($roomId) {
        $redis = new \Redis();
        $redis->connect($this->config['redis']['global']['ip'], $this->config['redis']['global']['port']);
        if (isset($this->config['redis']['global']['password'])) {
            $redis->auth($this->config['redis']['global']['password']);
        }
        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_room_{$roomId}";
        $data = $redis->get($cacheKey);
        $data = json_decode($data, true);
        return $data;
    }

    public function setRoomInfo($roomInfo) {
        $redis = get_instance()->getAsynPool('redisDao');
        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_room_{$roomInfo['roomId']}";
        yield $redis->getCoroutine()->setex($cacheKey, $this->config['main']['cacheTime']['roomCache'], json_encode($roomInfo));

        return true;
    }

    public function setRoomInfoSynch($roomInfo) {
        $redis = new \Redis();
        $redis->connect($this->config['redis']['global']['ip'], $this->config['redis']['global']['port']);
        if (isset($this->config['redis']['global']['password'])) {
            $redis->auth($this->config['redis']['global']['password']);
        }
        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_room_{$roomInfo['roomId']}";
        $redis->setex($cacheKey, $this->config['main']['cacheTime']['roomCache'], json_encode($roomInfo));
        return true;
    }

    public function delRoomInfo($roomInfo) {
        $redis = get_instance()->getAsynPool('redisDao');
        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_room_{$roomInfo['roomId']}";
        yield $redis->getCoroutine()->del($cacheKey);

        return true;
    }

    public function setDormMemberHonor($info) {
        $dao = get_instance()->getAsynPool('dormDao');
        foreach ($info as $row) {
            if ($row['win'] == 2) continue;
            $suffix = $row['uId'] % 10;
            $table = 'dorm_members_' . $suffix;
            $data = yield $dao->dbQueryBuilder->select('*')->from($table)->where('user_id', $row['uId'])->coroutineSend();
            if (empty($data['result'])) continue;
            $member = $data['result'][0];
            $honor = $member['dm_honor'] + $row['hNum'];
            $honor = max($honor, 0);

            if ($member['dm_honor'] == 0 && $honor == 0) continue;
            yield $dao->dbQueryBuilder->update($table)->set('dm_honor', $honor)->where('user_id', $row['uId'])->coroutineSend();
        }

        return true;
    }

    public function setDormHonor($info) {
        $dao = get_instance()->getAsynPool('dormDao');
        foreach ($info['list'] as $row) {
            if ($row['num'] == 0) continue;
            $table = 'dorm';
            $data = yield $dao->dbQueryBuilder->select('*')->from($table)->where('dorm_id', $row['dormId'])->coroutineSend();
            if (empty($data['result'])) continue;
            $dorm = $data['result'][0];
            $honor = $dorm['dorm_honor'] + $row['num'];
            $honor = max($honor, 0);

            if ($dorm['dorm_honor'] == 0 && $honor == 0) continue;
            yield $dao->dbQueryBuilder->update($table)->set('dorm_honor', $honor)->where('dorm_id', $row['dormId'])->coroutineSend();
        }

        return true;
    }

    public function getDormInfo($dormId) {
        $dormInfo = [];
        $dao = get_instance()->getAsynPool('dormDao');
        $table = 'dorm';
        $data = yield $dao->dbQueryBuilder->select('*')->from($table)->where('dorm_id', $dormId)->coroutineSend();
        if ($data['result']) {
            $row = $data['result'][0];
            $dormInfo['name'] = $row['dorm_name'];
            $dormInfo['dormId'] = $row['dorm_id'];
        }

        return $dormInfo;
    }
}