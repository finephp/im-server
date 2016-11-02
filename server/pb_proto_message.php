<?php
/**
 * Auto generated from message.proto at 2016-10-13 14:10:05
 */

/**
 * CommandType enum
 */
final class CommandType
{
    const session = 0;
    const conv = 1;
    const direct = 2;
    const ack = 3;
    const rcp = 4;
    const unread = 5;
    const logs = 6;
    const error = 7;
    const login = 8;
    const data = 9;
    const room = 10;
    const read = 11;
    const presence = 12;
    const report = 13;
    const echo_a = 14;

    /**
     * Returns defined enum values
     *
     * @return int[]
     */
    public function getEnumValues()
    {
        return array(
            'session' => self::session,
            'conv' => self::conv,
            'direct' => self::direct,
            'ack' => self::ack,
            'rcp' => self::rcp,
            'unread' => self::unread,
            'logs' => self::logs,
            'error' => self::error,
            'login' => self::login,
            'data' => self::data,
            'room' => self::room,
            'read' => self::read,
            'presence' => self::presence,
            'report' => self::report,
            'echo_a' => self::echo_a,
        );
    }
}

/**
 * OpType enum
 */
final class OpType
{
    const open = 1;
    const add = 2;
    const remove = 3;
    const close = 4;
    const opened = 5;
    const closed = 6;
    const query = 7;
    const query_result = 8;
    const conflict = 9;
    const added = 10;
    const removed = 11;
    const start = 30;
    const started = 31;
    const joined = 32;
    const members_joined = 33;
    const left = 39;
    const members_left = 40;
    const results = 42;
    const count = 43;
    const result = 44;
    const update = 45;
    const updated = 46;
    const mute = 47;
    const unmute = 48;
    const status = 49;
    const members = 50;
    const join = 80;
    const invite = 81;
    const leave = 82;
    const kick = 83;
    const reject = 84;
    const invited = 85;
    const kicked = 86;
    const upload = 100;
    const uploaded = 101;

    /**
     * Returns defined enum values
     *
     * @return int[]
     */
    public function getEnumValues()
    {
        return array(
            'open' => self::open,
            'add' => self::add,
            'remove' => self::remove,
            'close' => self::close,
            'opened' => self::opened,
            'closed' => self::closed,
            'query' => self::query,
            'query_result' => self::query_result,
            'conflict' => self::conflict,
            'added' => self::added,
            'removed' => self::removed,
            'start' => self::start,
            'started' => self::started,
            'joined' => self::joined,
            'members_joined' => self::members_joined,
            'left' => self::left,
            'members_left' => self::members_left,
            'results' => self::results,
            'count' => self::count,
            'result' => self::result,
            'update' => self::update,
            'updated' => self::updated,
            'mute' => self::mute,
            'unmute' => self::unmute,
            'status' => self::status,
            'members' => self::members,
            'join' => self::join,
            'invite' => self::invite,
            'leave' => self::leave,
            'kick' => self::kick,
            'reject' => self::reject,
            'invited' => self::invited,
            'kicked' => self::kicked,
            'upload' => self::upload,
            'uploaded' => self::uploaded,
        );
    }
}

/**
 * StatusType enum
 */
final class StatusType
{
    const on = 1;
    const off = 2;

    /**
     * Returns defined enum values
     *
     * @return int[]
     */
    public function getEnumValues()
    {
        return array(
            'on' => self::on,
            'off' => self::off,
        );
    }
}

/**
 * JsonObjectMessage message
 */
class JsonObjectMessage extends \ProtobufMessage
{
    /* Field index constants */
    const DATA = 1;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::DATA => array(
            'name' => 'data',
            'required' => true,
            'type' => 7,
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     *
     * @return null
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::DATA] = null;
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Sets value of 'data' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setData($value)
    {
        return $this->set(self::DATA, $value);
    }

    /**
     * Returns value of 'data' property
     *
     * @return string
     */
    public function getData()
    {
        return $this->get(self::DATA);
    }
}

/**
 * UnreadTuple message
 */
class UnreadTuple extends \ProtobufMessage
{
    /* Field index constants */
    const CID = 1;
    const UNREAD = 2;
    const MID = 3;
    const TIMESTAMP = 4;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::CID => array(
            'name' => 'cid',
            'required' => true,
            'type' => 7,
        ),
        self::UNREAD => array(
            'name' => 'unread',
            'required' => true,
            'type' => 5,
        ),
        self::MID => array(
            'name' => 'mid',
            'required' => false,
            'type' => 7,
        ),
        self::TIMESTAMP => array(
            'name' => 'timestamp',
            'required' => false,
            'type' => 5,
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     *
     * @return null
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::CID] = null;
        $this->values[self::UNREAD] = null;
        $this->values[self::MID] = null;
        $this->values[self::TIMESTAMP] = null;
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Sets value of 'cid' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setCid($value)
    {
        return $this->set(self::CID, $value);
    }

    /**
     * Returns value of 'cid' property
     *
     * @return string
     */
    public function getCid()
    {
        return $this->get(self::CID);
    }

    /**
     * Sets value of 'unread' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setUnread($value)
    {
        return $this->set(self::UNREAD, $value);
    }

    /**
     * Returns value of 'unread' property
     *
     * @return int
     */
    public function getUnread()
    {
        return $this->get(self::UNREAD);
    }

    /**
     * Sets value of 'mid' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setMid($value)
    {
        return $this->set(self::MID, $value);
    }

    /**
     * Returns value of 'mid' property
     *
     * @return string
     */
    public function getMid()
    {
        return $this->get(self::MID);
    }

    /**
     * Sets value of 'timestamp' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setTimestamp($value)
    {
        return $this->set(self::TIMESTAMP, $value);
    }

    /**
     * Returns value of 'timestamp' property
     *
     * @return int
     */
    public function getTimestamp()
    {
        return $this->get(self::TIMESTAMP);
    }
}

/**
 * LogItem message
 */
class LogItem extends \ProtobufMessage
{
    /* Field index constants */
    const FROM = 1;
    const DATA = 2;
    const TIMESTAMP = 3;
    const MSGID = 4;
    const ACKAT = 5;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::FROM => array(
            'name' => 'from',
            'required' => false,
            'type' => 7,
        ),
        self::DATA => array(
            'name' => 'data',
            'required' => false,
            'type' => 7,
        ),
        self::TIMESTAMP => array(
            'name' => 'timestamp',
            'required' => false,
            'type' => 5,
        ),
        self::MSGID => array(
            'name' => 'msgId',
            'required' => false,
            'type' => 7,
        ),
        self::ACKAT => array(
            'name' => 'ackAt',
            'required' => false,
            'type' => 5,
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     *
     * @return null
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::FROM] = null;
        $this->values[self::DATA] = null;
        $this->values[self::TIMESTAMP] = null;
        $this->values[self::MSGID] = null;
        $this->values[self::ACKAT] = null;
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Sets value of 'from' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setFrom($value)
    {
        return $this->set(self::FROM, $value);
    }

    /**
     * Returns value of 'from' property
     *
     * @return string
     */
    public function getFrom()
    {
        return $this->get(self::FROM);
    }

    /**
     * Sets value of 'data' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setData($value)
    {
        return $this->set(self::DATA, $value);
    }

    /**
     * Returns value of 'data' property
     *
     * @return string
     */
    public function getData()
    {
        return $this->get(self::DATA);
    }

    /**
     * Sets value of 'timestamp' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setTimestamp($value)
    {
        return $this->set(self::TIMESTAMP, $value);
    }

    /**
     * Returns value of 'timestamp' property
     *
     * @return int
     */
    public function getTimestamp()
    {
        return $this->get(self::TIMESTAMP);
    }

    /**
     * Sets value of 'msgId' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setMsgId($value)
    {
        return $this->set(self::MSGID, $value);
    }

    /**
     * Returns value of 'msgId' property
     *
     * @return string
     */
    public function getMsgId()
    {
        return $this->get(self::MSGID);
    }

    /**
     * Sets value of 'ackAt' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setAckAt($value)
    {
        return $this->set(self::ACKAT, $value);
    }

    /**
     * Returns value of 'ackAt' property
     *
     * @return int
     */
    public function getAckAt()
    {
        return $this->get(self::ACKAT);
    }
}

/**
 * LoginCommand message
 */
class LoginCommand extends \ProtobufMessage
{
    /* Field index constants */

    /* @var array Field descriptors */
    protected static $fields = array(
    );

    /**
     * Constructs new message container and clears its internal state
     *
     * @return null
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }
}

/**
 * DataCommand message
 */
class DataCommand extends \ProtobufMessage
{
    /* Field index constants */
    const IDS = 1;
    const MSG = 2;
    const OFFLINE = 3;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::IDS => array(
            'name' => 'ids',
            'repeated' => true,
            'type' => 7,
        ),
        self::MSG => array(
            'name' => 'msg',
            'repeated' => true,
            'type' => 'JsonObjectMessage'
        ),
        self::OFFLINE => array(
            'name' => 'offline',
            'required' => false,
            'type' => 8,
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     *
     * @return null
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::IDS] = array();
        $this->values[self::MSG] = array();
        $this->values[self::OFFLINE] = null;
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Appends value to 'ids' list
     *
     * @param string $value Value to append
     *
     * @return null
     */
    public function appendIds($value)
    {
        return $this->append(self::IDS, $value);
    }

    /**
     * Clears 'ids' list
     *
     * @return null
     */
    public function clearIds()
    {
        return $this->clear(self::IDS);
    }

    /**
     * Returns 'ids' list
     *
     * @return string[]
     */
    public function getIds()
    {
        return $this->get(self::IDS);
    }

    /**
     * Returns 'ids' iterator
     *
     * @return ArrayIterator
     */
    public function getIdsIterator()
    {
        return new \ArrayIterator($this->get(self::IDS));
    }

    /**
     * Returns element from 'ids' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return string
     */
    public function getIdsAt($offset)
    {
        return $this->get(self::IDS, $offset);
    }

    /**
     * Returns count of 'ids' list
     *
     * @return int
     */
    public function getIdsCount()
    {
        return $this->count(self::IDS);
    }

    /**
     * Appends value to 'msg' list
     *
     * @param JsonObjectMessage $value Value to append
     *
     * @return null
     */
    public function appendMsg(JsonObjectMessage $value)
    {
        return $this->append(self::MSG, $value);
    }

    /**
     * Clears 'msg' list
     *
     * @return null
     */
    public function clearMsg()
    {
        return $this->clear(self::MSG);
    }

    /**
     * Returns 'msg' list
     *
     * @return JsonObjectMessage[]
     */
    public function getMsg()
    {
        return $this->get(self::MSG);
    }

    /**
     * Returns 'msg' iterator
     *
     * @return ArrayIterator
     */
    public function getMsgIterator()
    {
        return new \ArrayIterator($this->get(self::MSG));
    }

    /**
     * Returns element from 'msg' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return JsonObjectMessage
     */
    public function getMsgAt($offset)
    {
        return $this->get(self::MSG, $offset);
    }

    /**
     * Returns count of 'msg' list
     *
     * @return int
     */
    public function getMsgCount()
    {
        return $this->count(self::MSG);
    }

    /**
     * Sets value of 'offline' property
     *
     * @param bool $value Property value
     *
     * @return null
     */
    public function setOffline($value)
    {
        return $this->set(self::OFFLINE, $value);
    }

    /**
     * Returns value of 'offline' property
     *
     * @return bool
     */
    public function getOffline()
    {
        return $this->get(self::OFFLINE);
    }
}

/**
 * SessionCommand message
 */
class SessionCommand extends \ProtobufMessage
{
    /* Field index constants */
    const T = 1;
    const N = 2;
    const S = 3;
    const UA = 4;
    const R = 5;
    const TAG = 6;
    const DEVICEID = 7;
    const SESSIONPEERIDS = 8;
    const ONLINESESSIONPEERIDS = 9;
    const ST = 10;
    const STTTL = 11;
    const CODE = 12;
    const REASON = 13;
    const DEVICETOKEN = 14;
    const SP = 15;
    const DETAIL = 16;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::T => array(
            'name' => 't',
            'required' => false,
            'type' => 5,
        ),
        self::N => array(
            'name' => 'n',
            'required' => false,
            'type' => 7,
        ),
        self::S => array(
            'name' => 's',
            'required' => false,
            'type' => 7,
        ),
        self::UA => array(
            'name' => 'ua',
            'required' => false,
            'type' => 7,
        ),
        self::R => array(
            'name' => 'r',
            'required' => false,
            'type' => 8,
        ),
        self::TAG => array(
            'name' => 'tag',
            'required' => false,
            'type' => 7,
        ),
        self::DEVICEID => array(
            'name' => 'deviceId',
            'required' => false,
            'type' => 7,
        ),
        self::SESSIONPEERIDS => array(
            'name' => 'sessionPeerIds',
            'repeated' => true,
            'type' => 7,
        ),
        self::ONLINESESSIONPEERIDS => array(
            'name' => 'onlineSessionPeerIds',
            'repeated' => true,
            'type' => 7,
        ),
        self::ST => array(
            'name' => 'st',
            'required' => false,
            'type' => 7,
        ),
        self::STTTL => array(
            'name' => 'stTtl',
            'required' => false,
            'type' => 5,
        ),
        self::CODE => array(
            'name' => 'code',
            'required' => false,
            'type' => 5,
        ),
        self::REASON => array(
            'name' => 'reason',
            'required' => false,
            'type' => 7,
        ),
        self::DEVICETOKEN => array(
            'name' => 'deviceToken',
            'required' => false,
            'type' => 7,
        ),
        self::SP => array(
            'name' => 'sp',
            'required' => false,
            'type' => 8,
        ),
        self::DETAIL => array(
            'name' => 'detail',
            'required' => false,
            'type' => 7,
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     *
     * @return null
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::T] = null;
        $this->values[self::N] = null;
        $this->values[self::S] = null;
        $this->values[self::UA] = null;
        $this->values[self::R] = null;
        $this->values[self::TAG] = null;
        $this->values[self::DEVICEID] = null;
        $this->values[self::SESSIONPEERIDS] = array();
        $this->values[self::ONLINESESSIONPEERIDS] = array();
        $this->values[self::ST] = null;
        $this->values[self::STTTL] = null;
        $this->values[self::CODE] = null;
        $this->values[self::REASON] = null;
        $this->values[self::DEVICETOKEN] = null;
        $this->values[self::SP] = null;
        $this->values[self::DETAIL] = null;
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Sets value of 't' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setT($value)
    {
        return $this->set(self::T, $value);
    }

    /**
     * Returns value of 't' property
     *
     * @return int
     */
    public function getT()
    {
        return $this->get(self::T);
    }

    /**
     * Sets value of 'n' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setN($value)
    {
        return $this->set(self::N, $value);
    }

    /**
     * Returns value of 'n' property
     *
     * @return string
     */
    public function getN()
    {
        return $this->get(self::N);
    }

    /**
     * Sets value of 's' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setS($value)
    {
        return $this->set(self::S, $value);
    }

    /**
     * Returns value of 's' property
     *
     * @return string
     */
    public function getS()
    {
        return $this->get(self::S);
    }

    /**
     * Sets value of 'ua' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setUa($value)
    {
        return $this->set(self::UA, $value);
    }

    /**
     * Returns value of 'ua' property
     *
     * @return string
     */
    public function getUa()
    {
        return $this->get(self::UA);
    }

    /**
     * Sets value of 'r' property
     *
     * @param bool $value Property value
     *
     * @return null
     */
    public function setR($value)
    {
        return $this->set(self::R, $value);
    }

    /**
     * Returns value of 'r' property
     *
     * @return bool
     */
    public function getR()
    {
        return $this->get(self::R);
    }

    /**
     * Sets value of 'tag' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setTag($value)
    {
        return $this->set(self::TAG, $value);
    }

    /**
     * Returns value of 'tag' property
     *
     * @return string
     */
    public function getTag()
    {
        return $this->get(self::TAG);
    }

    /**
     * Sets value of 'deviceId' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setDeviceId($value)
    {
        return $this->set(self::DEVICEID, $value);
    }

    /**
     * Returns value of 'deviceId' property
     *
     * @return string
     */
    public function getDeviceId()
    {
        return $this->get(self::DEVICEID);
    }

    /**
     * Appends value to 'sessionPeerIds' list
     *
     * @param string $value Value to append
     *
     * @return null
     */
    public function appendSessionPeerIds($value)
    {
        return $this->append(self::SESSIONPEERIDS, $value);
    }

    /**
     * Clears 'sessionPeerIds' list
     *
     * @return null
     */
    public function clearSessionPeerIds()
    {
        return $this->clear(self::SESSIONPEERIDS);
    }

    /**
     * Returns 'sessionPeerIds' list
     *
     * @return string[]
     */
    public function getSessionPeerIds()
    {
        return $this->get(self::SESSIONPEERIDS);
    }

    /**
     * Returns 'sessionPeerIds' iterator
     *
     * @return ArrayIterator
     */
    public function getSessionPeerIdsIterator()
    {
        return new \ArrayIterator($this->get(self::SESSIONPEERIDS));
    }

    /**
     * Returns element from 'sessionPeerIds' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return string
     */
    public function getSessionPeerIdsAt($offset)
    {
        return $this->get(self::SESSIONPEERIDS, $offset);
    }

    /**
     * Returns count of 'sessionPeerIds' list
     *
     * @return int
     */
    public function getSessionPeerIdsCount()
    {
        return $this->count(self::SESSIONPEERIDS);
    }

    /**
     * Appends value to 'onlineSessionPeerIds' list
     *
     * @param string $value Value to append
     *
     * @return null
     */
    public function appendOnlineSessionPeerIds($value)
    {
        return $this->append(self::ONLINESESSIONPEERIDS, $value);
    }

    /**
     * Clears 'onlineSessionPeerIds' list
     *
     * @return null
     */
    public function clearOnlineSessionPeerIds()
    {
        return $this->clear(self::ONLINESESSIONPEERIDS);
    }

    /**
     * Returns 'onlineSessionPeerIds' list
     *
     * @return string[]
     */
    public function getOnlineSessionPeerIds()
    {
        return $this->get(self::ONLINESESSIONPEERIDS);
    }

    /**
     * Returns 'onlineSessionPeerIds' iterator
     *
     * @return ArrayIterator
     */
    public function getOnlineSessionPeerIdsIterator()
    {
        return new \ArrayIterator($this->get(self::ONLINESESSIONPEERIDS));
    }

    /**
     * Returns element from 'onlineSessionPeerIds' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return string
     */
    public function getOnlineSessionPeerIdsAt($offset)
    {
        return $this->get(self::ONLINESESSIONPEERIDS, $offset);
    }

    /**
     * Returns count of 'onlineSessionPeerIds' list
     *
     * @return int
     */
    public function getOnlineSessionPeerIdsCount()
    {
        return $this->count(self::ONLINESESSIONPEERIDS);
    }

    /**
     * Sets value of 'st' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setSt($value)
    {
        return $this->set(self::ST, $value);
    }

    /**
     * Returns value of 'st' property
     *
     * @return string
     */
    public function getSt()
    {
        return $this->get(self::ST);
    }

    /**
     * Sets value of 'stTtl' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setStTtl($value)
    {
        return $this->set(self::STTTL, $value);
    }

    /**
     * Returns value of 'stTtl' property
     *
     * @return int
     */
    public function getStTtl()
    {
        return $this->get(self::STTTL);
    }

    /**
     * Sets value of 'code' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setCode($value)
    {
        return $this->set(self::CODE, $value);
    }

    /**
     * Returns value of 'code' property
     *
     * @return int
     */
    public function getCode()
    {
        return $this->get(self::CODE);
    }

    /**
     * Sets value of 'reason' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setReason($value)
    {
        return $this->set(self::REASON, $value);
    }

    /**
     * Returns value of 'reason' property
     *
     * @return string
     */
    public function getReason()
    {
        return $this->get(self::REASON);
    }

    /**
     * Sets value of 'deviceToken' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setDeviceToken($value)
    {
        return $this->set(self::DEVICETOKEN, $value);
    }

    /**
     * Returns value of 'deviceToken' property
     *
     * @return string
     */
    public function getDeviceToken()
    {
        return $this->get(self::DEVICETOKEN);
    }

    /**
     * Sets value of 'sp' property
     *
     * @param bool $value Property value
     *
     * @return null
     */
    public function setSp($value)
    {
        return $this->set(self::SP, $value);
    }

    /**
     * Returns value of 'sp' property
     *
     * @return bool
     */
    public function getSp()
    {
        return $this->get(self::SP);
    }

    /**
     * Sets value of 'detail' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setDetail($value)
    {
        return $this->set(self::DETAIL, $value);
    }

    /**
     * Returns value of 'detail' property
     *
     * @return string
     */
    public function getDetail()
    {
        return $this->get(self::DETAIL);
    }
}

/**
 * ErrorCommand message
 */
class ErrorCommand extends \ProtobufMessage
{
    /* Field index constants */
    const CODE = 1;
    const REASON = 2;
    const APPCODE = 3;
    const DETAIL = 4;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::CODE => array(
            'name' => 'code',
            'required' => true,
            'type' => 5,
        ),
        self::REASON => array(
            'name' => 'reason',
            'required' => true,
            'type' => 7,
        ),
        self::APPCODE => array(
            'name' => 'appCode',
            'required' => false,
            'type' => 5,
        ),
        self::DETAIL => array(
            'name' => 'detail',
            'required' => false,
            'type' => 7,
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     *
     * @return null
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::CODE] = null;
        $this->values[self::REASON] = null;
        $this->values[self::APPCODE] = null;
        $this->values[self::DETAIL] = null;
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Sets value of 'code' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setCode($value)
    {
        return $this->set(self::CODE, $value);
    }

    /**
     * Returns value of 'code' property
     *
     * @return int
     */
    public function getCode()
    {
        return $this->get(self::CODE);
    }

    /**
     * Sets value of 'reason' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setReason($value)
    {
        return $this->set(self::REASON, $value);
    }

    /**
     * Returns value of 'reason' property
     *
     * @return string
     */
    public function getReason()
    {
        return $this->get(self::REASON);
    }

    /**
     * Sets value of 'appCode' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setAppCode($value)
    {
        return $this->set(self::APPCODE, $value);
    }

    /**
     * Returns value of 'appCode' property
     *
     * @return int
     */
    public function getAppCode()
    {
        return $this->get(self::APPCODE);
    }

    /**
     * Sets value of 'detail' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setDetail($value)
    {
        return $this->set(self::DETAIL, $value);
    }

    /**
     * Returns value of 'detail' property
     *
     * @return string
     */
    public function getDetail()
    {
        return $this->get(self::DETAIL);
    }
}

/**
 * DirectCommand message
 */
class DirectCommand extends \ProtobufMessage
{
    /* Field index constants */
    const MSG = 1;
    const UID = 2;
    const FROMPEERID = 3;
    const TIMESTAMP = 4;
    const OFFLINE = 5;
    const HASMORE = 6;
    const TOPEERIDS = 7;
    const R = 10;
    const CID = 11;
    const ID = 12;
    const TRANSIENT = 13;
    const DT = 14;
    const ROOMID = 15;
    const PUSHDATA = 16;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::MSG => array(
            'name' => 'msg',
            'required' => false,
            'type' => 7,
        ),
        self::UID => array(
            'name' => 'uid',
            'required' => false,
            'type' => 7,
        ),
        self::FROMPEERID => array(
            'name' => 'fromPeerId',
            'required' => false,
            'type' => 7,
        ),
        self::TIMESTAMP => array(
            'name' => 'timestamp',
            'required' => false,
            'type' => 5,
        ),
        self::OFFLINE => array(
            'name' => 'offline',
            'required' => false,
            'type' => 8,
        ),
        self::HASMORE => array(
            'name' => 'hasMore',
            'required' => false,
            'type' => 8,
        ),
        self::TOPEERIDS => array(
            'name' => 'toPeerIds',
            'repeated' => true,
            'type' => 7,
        ),
        self::R => array(
            'name' => 'r',
            'required' => false,
            'type' => 8,
        ),
        self::CID => array(
            'name' => 'cid',
            'required' => false,
            'type' => 7,
        ),
        self::ID => array(
            'name' => 'id',
            'required' => false,
            'type' => 7,
        ),
        self::TRANSIENT => array(
            'name' => 'transient',
            'required' => false,
            'type' => 8,
        ),
        self::DT => array(
            'name' => 'dt',
            'required' => false,
            'type' => 7,
        ),
        self::ROOMID => array(
            'name' => 'roomId',
            'required' => false,
            'type' => 7,
        ),
        self::PUSHDATA => array(
            'name' => 'pushData',
            'required' => false,
            'type' => 7,
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     *
     * @return null
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::MSG] = null;
        $this->values[self::UID] = null;
        $this->values[self::FROMPEERID] = null;
        $this->values[self::TIMESTAMP] = null;
        $this->values[self::OFFLINE] = null;
        $this->values[self::HASMORE] = null;
        $this->values[self::TOPEERIDS] = array();
        $this->values[self::R] = null;
        $this->values[self::CID] = null;
        $this->values[self::ID] = null;
        $this->values[self::TRANSIENT] = null;
        $this->values[self::DT] = null;
        $this->values[self::ROOMID] = null;
        $this->values[self::PUSHDATA] = null;
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Sets value of 'msg' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setMsg($value)
    {
        return $this->set(self::MSG, $value);
    }

    /**
     * Returns value of 'msg' property
     *
     * @return string
     */
    public function getMsg()
    {
        return $this->get(self::MSG);
    }

    /**
     * Sets value of 'uid' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setUid($value)
    {
        return $this->set(self::UID, $value);
    }

    /**
     * Returns value of 'uid' property
     *
     * @return string
     */
    public function getUid()
    {
        return $this->get(self::UID);
    }

    /**
     * Sets value of 'fromPeerId' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setFromPeerId($value)
    {
        return $this->set(self::FROMPEERID, $value);
    }

    /**
     * Returns value of 'fromPeerId' property
     *
     * @return string
     */
    public function getFromPeerId()
    {
        return $this->get(self::FROMPEERID);
    }

    /**
     * Sets value of 'timestamp' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setTimestamp($value)
    {
        return $this->set(self::TIMESTAMP, $value);
    }

    /**
     * Returns value of 'timestamp' property
     *
     * @return int
     */
    public function getTimestamp()
    {
        return $this->get(self::TIMESTAMP);
    }

    /**
     * Sets value of 'offline' property
     *
     * @param bool $value Property value
     *
     * @return null
     */
    public function setOffline($value)
    {
        return $this->set(self::OFFLINE, $value);
    }

    /**
     * Returns value of 'offline' property
     *
     * @return bool
     */
    public function getOffline()
    {
        return $this->get(self::OFFLINE);
    }

    /**
     * Sets value of 'hasMore' property
     *
     * @param bool $value Property value
     *
     * @return null
     */
    public function setHasMore($value)
    {
        return $this->set(self::HASMORE, $value);
    }

    /**
     * Returns value of 'hasMore' property
     *
     * @return bool
     */
    public function getHasMore()
    {
        return $this->get(self::HASMORE);
    }

    /**
     * Appends value to 'toPeerIds' list
     *
     * @param string $value Value to append
     *
     * @return null
     */
    public function appendToPeerIds($value)
    {
        return $this->append(self::TOPEERIDS, $value);
    }

    /**
     * Clears 'toPeerIds' list
     *
     * @return null
     */
    public function clearToPeerIds()
    {
        return $this->clear(self::TOPEERIDS);
    }

    /**
     * Returns 'toPeerIds' list
     *
     * @return string[]
     */
    public function getToPeerIds()
    {
        return $this->get(self::TOPEERIDS);
    }

    /**
     * Returns 'toPeerIds' iterator
     *
     * @return ArrayIterator
     */
    public function getToPeerIdsIterator()
    {
        return new \ArrayIterator($this->get(self::TOPEERIDS));
    }

    /**
     * Returns element from 'toPeerIds' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return string
     */
    public function getToPeerIdsAt($offset)
    {
        return $this->get(self::TOPEERIDS, $offset);
    }

    /**
     * Returns count of 'toPeerIds' list
     *
     * @return int
     */
    public function getToPeerIdsCount()
    {
        return $this->count(self::TOPEERIDS);
    }

    /**
     * Sets value of 'r' property
     *
     * @param bool $value Property value
     *
     * @return null
     */
    public function setR($value)
    {
        return $this->set(self::R, $value);
    }

    /**
     * Returns value of 'r' property
     *
     * @return bool
     */
    public function getR()
    {
        return $this->get(self::R);
    }

    /**
     * Sets value of 'cid' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setCid($value)
    {
        return $this->set(self::CID, $value);
    }

    /**
     * Returns value of 'cid' property
     *
     * @return string
     */
    public function getCid()
    {
        return $this->get(self::CID);
    }

    /**
     * Sets value of 'id' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setId($value)
    {
        return $this->set(self::ID, $value);
    }

    /**
     * Returns value of 'id' property
     *
     * @return string
     */
    public function getId()
    {
        return $this->get(self::ID);
    }

    /**
     * Sets value of 'transient' property
     *
     * @param bool $value Property value
     *
     * @return null
     */
    public function setTransient($value)
    {
        return $this->set(self::TRANSIENT, $value);
    }

    /**
     * Returns value of 'transient' property
     *
     * @return bool
     */
    public function getTransient()
    {
        return $this->get(self::TRANSIENT);
    }

    /**
     * Sets value of 'dt' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setDt($value)
    {
        return $this->set(self::DT, $value);
    }

    /**
     * Returns value of 'dt' property
     *
     * @return string
     */
    public function getDt()
    {
        return $this->get(self::DT);
    }

    /**
     * Sets value of 'roomId' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setRoomId($value)
    {
        return $this->set(self::ROOMID, $value);
    }

    /**
     * Returns value of 'roomId' property
     *
     * @return string
     */
    public function getRoomId()
    {
        return $this->get(self::ROOMID);
    }

    /**
     * Sets value of 'pushData' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setPushData($value)
    {
        return $this->set(self::PUSHDATA, $value);
    }

    /**
     * Returns value of 'pushData' property
     *
     * @return string
     */
    public function getPushData()
    {
        return $this->get(self::PUSHDATA);
    }
}

/**
 * AckCommand message
 */
class AckCommand extends \ProtobufMessage
{
    /* Field index constants */
    const CODE = 1;
    const REASON = 2;
    const MID = 3;
    const CID = 4;
    const T = 5;
    const UID = 6;
    const FROMTS = 7;
    const TOTS = 8;
    const TYPE = 9;
    const IDS = 10;
    const APPCODE = 11;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::CODE => array(
            'name' => 'code',
            'required' => false,
            'type' => 5,
        ),
        self::REASON => array(
            'name' => 'reason',
            'required' => false,
            'type' => 7,
        ),
        self::MID => array(
            'name' => 'mid',
            'required' => false,
            'type' => 7,
        ),
        self::CID => array(
            'name' => 'cid',
            'required' => false,
            'type' => 7,
        ),
        self::T => array(
            'name' => 't',
            'required' => false,
            'type' => 5,
        ),
        self::UID => array(
            'name' => 'uid',
            'required' => false,
            'type' => 7,
        ),
        self::FROMTS => array(
            'name' => 'fromts',
            'required' => false,
            'type' => 5,
        ),
        self::TOTS => array(
            'name' => 'tots',
            'required' => false,
            'type' => 5,
        ),
        self::TYPE => array(
            'name' => 'type',
            'required' => false,
            'type' => 7,
        ),
        self::IDS => array(
            'name' => 'ids',
            'repeated' => true,
            'type' => 7,
        ),
        self::APPCODE => array(
            'name' => 'appCode',
            'required' => false,
            'type' => 5,
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     *
     * @return null
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::CODE] = null;
        $this->values[self::REASON] = null;
        $this->values[self::MID] = null;
        $this->values[self::CID] = null;
        $this->values[self::T] = null;
        $this->values[self::UID] = null;
        $this->values[self::FROMTS] = null;
        $this->values[self::TOTS] = null;
        $this->values[self::TYPE] = null;
        $this->values[self::IDS] = array();
        $this->values[self::APPCODE] = null;
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Sets value of 'code' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setCode($value)
    {
        return $this->set(self::CODE, $value);
    }

    /**
     * Returns value of 'code' property
     *
     * @return int
     */
    public function getCode()
    {
        return $this->get(self::CODE);
    }

    /**
     * Sets value of 'reason' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setReason($value)
    {
        return $this->set(self::REASON, $value);
    }

    /**
     * Returns value of 'reason' property
     *
     * @return string
     */
    public function getReason()
    {
        return $this->get(self::REASON);
    }

    /**
     * Sets value of 'mid' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setMid($value)
    {
        return $this->set(self::MID, $value);
    }

    /**
     * Returns value of 'mid' property
     *
     * @return string
     */
    public function getMid()
    {
        return $this->get(self::MID);
    }

    /**
     * Sets value of 'cid' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setCid($value)
    {
        return $this->set(self::CID, $value);
    }

    /**
     * Returns value of 'cid' property
     *
     * @return string
     */
    public function getCid()
    {
        return $this->get(self::CID);
    }

    /**
     * Sets value of 't' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setT($value)
    {
        return $this->set(self::T, $value);
    }

    /**
     * Returns value of 't' property
     *
     * @return int
     */
    public function getT()
    {
        return $this->get(self::T);
    }

    /**
     * Sets value of 'uid' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setUid($value)
    {
        return $this->set(self::UID, $value);
    }

    /**
     * Returns value of 'uid' property
     *
     * @return string
     */
    public function getUid()
    {
        return $this->get(self::UID);
    }

    /**
     * Sets value of 'fromts' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setFromts($value)
    {
        return $this->set(self::FROMTS, $value);
    }

    /**
     * Returns value of 'fromts' property
     *
     * @return int
     */
    public function getFromts()
    {
        return $this->get(self::FROMTS);
    }

    /**
     * Sets value of 'tots' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setTots($value)
    {
        return $this->set(self::TOTS, $value);
    }

    /**
     * Returns value of 'tots' property
     *
     * @return int
     */
    public function getTots()
    {
        return $this->get(self::TOTS);
    }

    /**
     * Sets value of 'type' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setType($value)
    {
        return $this->set(self::TYPE, $value);
    }

    /**
     * Returns value of 'type' property
     *
     * @return string
     */
    public function getType()
    {
        return $this->get(self::TYPE);
    }

    /**
     * Appends value to 'ids' list
     *
     * @param string $value Value to append
     *
     * @return null
     */
    public function appendIds($value)
    {
        return $this->append(self::IDS, $value);
    }

    /**
     * Clears 'ids' list
     *
     * @return null
     */
    public function clearIds()
    {
        return $this->clear(self::IDS);
    }

    /**
     * Returns 'ids' list
     *
     * @return string[]
     */
    public function getIds()
    {
        return $this->get(self::IDS);
    }

    /**
     * Returns 'ids' iterator
     *
     * @return ArrayIterator
     */
    public function getIdsIterator()
    {
        return new \ArrayIterator($this->get(self::IDS));
    }

    /**
     * Returns element from 'ids' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return string
     */
    public function getIdsAt($offset)
    {
        return $this->get(self::IDS, $offset);
    }

    /**
     * Returns count of 'ids' list
     *
     * @return int
     */
    public function getIdsCount()
    {
        return $this->count(self::IDS);
    }

    /**
     * Sets value of 'appCode' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setAppCode($value)
    {
        return $this->set(self::APPCODE, $value);
    }

    /**
     * Returns value of 'appCode' property
     *
     * @return int
     */
    public function getAppCode()
    {
        return $this->get(self::APPCODE);
    }
}

/**
 * UnreadCommand message
 */
class UnreadCommand extends \ProtobufMessage
{
    /* Field index constants */
    const CONVS = 1;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::CONVS => array(
            'name' => 'convs',
            'repeated' => true,
            'type' => 'UnreadTuple'
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     *
     * @return null
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::CONVS] = array();
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Appends value to 'convs' list
     *
     * @param UnreadTuple $value Value to append
     *
     * @return null
     */
    public function appendConvs(UnreadTuple $value)
    {
        return $this->append(self::CONVS, $value);
    }

    /**
     * Clears 'convs' list
     *
     * @return null
     */
    public function clearConvs()
    {
        return $this->clear(self::CONVS);
    }

    /**
     * Returns 'convs' list
     *
     * @return UnreadTuple[]
     */
    public function getConvs()
    {
        return $this->get(self::CONVS);
    }

    /**
     * Returns 'convs' iterator
     *
     * @return ArrayIterator
     */
    public function getConvsIterator()
    {
        return new \ArrayIterator($this->get(self::CONVS));
    }

    /**
     * Returns element from 'convs' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return UnreadTuple
     */
    public function getConvsAt($offset)
    {
        return $this->get(self::CONVS, $offset);
    }

    /**
     * Returns count of 'convs' list
     *
     * @return int
     */
    public function getConvsCount()
    {
        return $this->count(self::CONVS);
    }
}

/**
 * ConvCommand message
 */
class ConvCommand extends \ProtobufMessage
{
    /* Field index constants */
    const M = 1;
    const TRANSIENT = 2;
    const UNIQUE = 3;
    const CID = 4;
    const CDATE = 5;
    const INITBY = 6;
    const SORT = 7;
    const LIMIT = 8;
    const SKIP = 9;
    const FLAG = 10;
    const COUNT = 11;
    const UDATE = 12;
    const T = 13;
    const N = 14;
    const S = 15;
    const STATUSSUB = 16;
    const STATUSPUB = 17;
    const STATUSTTL = 18;
    const MEMBERS = 19;
    const RESULTS = 100;
    const WHERE = 101;
    const ATTR = 103;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::M => array(
            'name' => 'm',
            'repeated' => true,
            'type' => 7,
        ),
        self::TRANSIENT => array(
            'name' => 'transient',
            'required' => false,
            'type' => 8,
        ),
        self::UNIQUE => array(
            'name' => 'unique',
            'required' => false,
            'type' => 8,
        ),
        self::CID => array(
            'name' => 'cid',
            'required' => false,
            'type' => 7,
        ),
        self::CDATE => array(
            'name' => 'cdate',
            'required' => false,
            'type' => 7,
        ),
        self::INITBY => array(
            'name' => 'initBy',
            'required' => false,
            'type' => 7,
        ),
        self::SORT => array(
            'name' => 'sort',
            'required' => false,
            'type' => 7,
        ),
        self::LIMIT => array(
            'name' => 'limit',
            'required' => false,
            'type' => 5,
        ),
        self::SKIP => array(
            'name' => 'skip',
            'required' => false,
            'type' => 5,
        ),
        self::FLAG => array(
            'name' => 'flag',
            'required' => false,
            'type' => 5,
        ),
        self::COUNT => array(
            'name' => 'count',
            'required' => false,
            'type' => 5,
        ),
        self::UDATE => array(
            'name' => 'udate',
            'required' => false,
            'type' => 7,
        ),
        self::T => array(
            'name' => 't',
            'required' => false,
            'type' => 5,
        ),
        self::N => array(
            'name' => 'n',
            'required' => false,
            'type' => 7,
        ),
        self::S => array(
            'name' => 's',
            'required' => false,
            'type' => 7,
        ),
        self::STATUSSUB => array(
            'name' => 'statusSub',
            'required' => false,
            'type' => 8,
        ),
        self::STATUSPUB => array(
            'name' => 'statusPub',
            'required' => false,
            'type' => 8,
        ),
        self::STATUSTTL => array(
            'name' => 'statusTTL',
            'required' => false,
            'type' => 5,
        ),
        self::MEMBERS => array(
            'name' => 'members',
            'repeated' => true,
            'type' => 7,
        ),
        self::RESULTS => array(
            'name' => 'results',
            'required' => false,
            'type' => 'JsonObjectMessage'
        ),
        self::WHERE => array(
            'name' => 'where',
            'required' => false,
            'type' => 'JsonObjectMessage'
        ),
        self::ATTR => array(
            'name' => 'attr',
            'required' => false,
            'type' => 'JsonObjectMessage'
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     *
     * @return null
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::M] = array();
        $this->values[self::TRANSIENT] = null;
        $this->values[self::UNIQUE] = null;
        $this->values[self::CID] = null;
        $this->values[self::CDATE] = null;
        $this->values[self::INITBY] = null;
        $this->values[self::SORT] = null;
        $this->values[self::LIMIT] = null;
        $this->values[self::SKIP] = null;
        $this->values[self::FLAG] = null;
        $this->values[self::COUNT] = null;
        $this->values[self::UDATE] = null;
        $this->values[self::T] = null;
        $this->values[self::N] = null;
        $this->values[self::S] = null;
        $this->values[self::STATUSSUB] = null;
        $this->values[self::STATUSPUB] = null;
        $this->values[self::STATUSTTL] = null;
        $this->values[self::MEMBERS] = array();
        $this->values[self::RESULTS] = null;
        $this->values[self::WHERE] = null;
        $this->values[self::ATTR] = null;
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Appends value to 'm' list
     *
     * @param string $value Value to append
     *
     * @return null
     */
    public function appendM($value)
    {
        return $this->append(self::M, $value);
    }

    /**
     * Clears 'm' list
     *
     * @return null
     */
    public function clearM()
    {
        return $this->clear(self::M);
    }

    /**
     * Returns 'm' list
     *
     * @return string[]
     */
    public function getM()
    {
        return $this->get(self::M);
    }

    /**
     * Returns 'm' iterator
     *
     * @return ArrayIterator
     */
    public function getMIterator()
    {
        return new \ArrayIterator($this->get(self::M));
    }

    /**
     * Returns element from 'm' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return string
     */
    public function getMAt($offset)
    {
        return $this->get(self::M, $offset);
    }

    /**
     * Returns count of 'm' list
     *
     * @return int
     */
    public function getMCount()
    {
        return $this->count(self::M);
    }

    /**
     * Sets value of 'transient' property
     *
     * @param bool $value Property value
     *
     * @return null
     */
    public function setTransient($value)
    {
        return $this->set(self::TRANSIENT, $value);
    }

    /**
     * Returns value of 'transient' property
     *
     * @return bool
     */
    public function getTransient()
    {
        return $this->get(self::TRANSIENT);
    }

    /**
     * Sets value of 'unique' property
     *
     * @param bool $value Property value
     *
     * @return null
     */
    public function setUnique($value)
    {
        return $this->set(self::UNIQUE, $value);
    }

    /**
     * Returns value of 'unique' property
     *
     * @return bool
     */
    public function getUnique()
    {
        return $this->get(self::UNIQUE);
    }

    /**
     * Sets value of 'cid' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setCid($value)
    {
        return $this->set(self::CID, $value);
    }

    /**
     * Returns value of 'cid' property
     *
     * @return string
     */
    public function getCid()
    {
        return $this->get(self::CID);
    }

    /**
     * Sets value of 'cdate' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setCdate($value)
    {
        return $this->set(self::CDATE, $value);
    }

    /**
     * Returns value of 'cdate' property
     *
     * @return string
     */
    public function getCdate()
    {
        return $this->get(self::CDATE);
    }

    /**
     * Sets value of 'initBy' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setInitBy($value)
    {
        return $this->set(self::INITBY, $value);
    }

    /**
     * Returns value of 'initBy' property
     *
     * @return string
     */
    public function getInitBy()
    {
        return $this->get(self::INITBY);
    }

    /**
     * Sets value of 'sort' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setSort($value)
    {
        return $this->set(self::SORT, $value);
    }

    /**
     * Returns value of 'sort' property
     *
     * @return string
     */
    public function getSort()
    {
        return $this->get(self::SORT);
    }

    /**
     * Sets value of 'limit' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setLimit($value)
    {
        return $this->set(self::LIMIT, $value);
    }

    /**
     * Returns value of 'limit' property
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->get(self::LIMIT);
    }

    /**
     * Sets value of 'skip' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setSkip($value)
    {
        return $this->set(self::SKIP, $value);
    }

    /**
     * Returns value of 'skip' property
     *
     * @return int
     */
    public function getSkip()
    {
        return $this->get(self::SKIP);
    }

    /**
     * Sets value of 'flag' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setFlag($value)
    {
        return $this->set(self::FLAG, $value);
    }

    /**
     * Returns value of 'flag' property
     *
     * @return int
     */
    public function getFlag()
    {
        return $this->get(self::FLAG);
    }

    /**
     * Sets value of 'count' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setCount($value)
    {
        return $this->set(self::COUNT, $value);
    }

    /**
     * Returns value of 'count' property
     *
     * @return int
     */
    public function getCount()
    {
        return $this->get(self::COUNT);
    }

    /**
     * Sets value of 'udate' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setUdate($value)
    {
        return $this->set(self::UDATE, $value);
    }

    /**
     * Returns value of 'udate' property
     *
     * @return string
     */
    public function getUdate()
    {
        return $this->get(self::UDATE);
    }

    /**
     * Sets value of 't' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setT($value)
    {
        return $this->set(self::T, $value);
    }

    /**
     * Returns value of 't' property
     *
     * @return int
     */
    public function getT()
    {
        return $this->get(self::T);
    }

    /**
     * Sets value of 'n' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setN($value)
    {
        return $this->set(self::N, $value);
    }

    /**
     * Returns value of 'n' property
     *
     * @return string
     */
    public function getN()
    {
        return $this->get(self::N);
    }

    /**
     * Sets value of 's' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setS($value)
    {
        return $this->set(self::S, $value);
    }

    /**
     * Returns value of 's' property
     *
     * @return string
     */
    public function getS()
    {
        return $this->get(self::S);
    }

    /**
     * Sets value of 'statusSub' property
     *
     * @param bool $value Property value
     *
     * @return null
     */
    public function setStatusSub($value)
    {
        return $this->set(self::STATUSSUB, $value);
    }

    /**
     * Returns value of 'statusSub' property
     *
     * @return bool
     */
    public function getStatusSub()
    {
        return $this->get(self::STATUSSUB);
    }

    /**
     * Sets value of 'statusPub' property
     *
     * @param bool $value Property value
     *
     * @return null
     */
    public function setStatusPub($value)
    {
        return $this->set(self::STATUSPUB, $value);
    }

    /**
     * Returns value of 'statusPub' property
     *
     * @return bool
     */
    public function getStatusPub()
    {
        return $this->get(self::STATUSPUB);
    }

    /**
     * Sets value of 'statusTTL' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setStatusTTL($value)
    {
        return $this->set(self::STATUSTTL, $value);
    }

    /**
     * Returns value of 'statusTTL' property
     *
     * @return int
     */
    public function getStatusTTL()
    {
        return $this->get(self::STATUSTTL);
    }

    /**
     * Appends value to 'members' list
     *
     * @param string $value Value to append
     *
     * @return null
     */
    public function appendMembers($value)
    {
        return $this->append(self::MEMBERS, $value);
    }

    /**
     * Clears 'members' list
     *
     * @return null
     */
    public function clearMembers()
    {
        return $this->clear(self::MEMBERS);
    }

    /**
     * Returns 'members' list
     *
     * @return string[]
     */
    public function getMembers()
    {
        return $this->get(self::MEMBERS);
    }

    /**
     * Returns 'members' iterator
     *
     * @return ArrayIterator
     */
    public function getMembersIterator()
    {
        return new \ArrayIterator($this->get(self::MEMBERS));
    }

    /**
     * Returns element from 'members' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return string
     */
    public function getMembersAt($offset)
    {
        return $this->get(self::MEMBERS, $offset);
    }

    /**
     * Returns count of 'members' list
     *
     * @return int
     */
    public function getMembersCount()
    {
        return $this->count(self::MEMBERS);
    }

    /**
     * Sets value of 'results' property
     *
     * @param JsonObjectMessage $value Property value
     *
     * @return null
     */
    public function setResults(JsonObjectMessage $value)
    {
        return $this->set(self::RESULTS, $value);
    }

    /**
     * Returns value of 'results' property
     *
     * @return JsonObjectMessage
     */
    public function getResults()
    {
        return $this->get(self::RESULTS);
    }

    /**
     * Sets value of 'where' property
     *
     * @param JsonObjectMessage $value Property value
     *
     * @return null
     */
    public function setWhere(JsonObjectMessage $value)
    {
        return $this->set(self::WHERE, $value);
    }

    /**
     * Returns value of 'where' property
     *
     * @return JsonObjectMessage
     */
    public function getWhere()
    {
        return $this->get(self::WHERE);
    }

    /**
     * Sets value of 'attr' property
     *
     * @param JsonObjectMessage $value Property value
     *
     * @return null
     */
    public function setAttr(JsonObjectMessage $value)
    {
        return $this->set(self::ATTR, $value);
    }

    /**
     * Returns value of 'attr' property
     *
     * @return JsonObjectMessage
     */
    public function getAttr()
    {
        return $this->get(self::ATTR);
    }
}

/**
 * RoomCommand message
 */
class RoomCommand extends \ProtobufMessage
{
    /* Field index constants */
    const ROOMID = 1;
    const S = 2;
    const T = 3;
    const N = 4;
    const TRANSIENT = 5;
    const ROOMPEERIDS = 6;
    const BYPEERID = 7;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::ROOMID => array(
            'name' => 'roomId',
            'required' => false,
            'type' => 7,
        ),
        self::S => array(
            'name' => 's',
            'required' => false,
            'type' => 7,
        ),
        self::T => array(
            'name' => 't',
            'required' => false,
            'type' => 5,
        ),
        self::N => array(
            'name' => 'n',
            'required' => false,
            'type' => 7,
        ),
        self::TRANSIENT => array(
            'name' => 'transient',
            'required' => false,
            'type' => 8,
        ),
        self::ROOMPEERIDS => array(
            'name' => 'roomPeerIds',
            'repeated' => true,
            'type' => 7,
        ),
        self::BYPEERID => array(
            'name' => 'byPeerId',
            'required' => false,
            'type' => 7,
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     *
     * @return null
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::ROOMID] = null;
        $this->values[self::S] = null;
        $this->values[self::T] = null;
        $this->values[self::N] = null;
        $this->values[self::TRANSIENT] = null;
        $this->values[self::ROOMPEERIDS] = array();
        $this->values[self::BYPEERID] = null;
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Sets value of 'roomId' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setRoomId($value)
    {
        return $this->set(self::ROOMID, $value);
    }

    /**
     * Returns value of 'roomId' property
     *
     * @return string
     */
    public function getRoomId()
    {
        return $this->get(self::ROOMID);
    }

    /**
     * Sets value of 's' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setS($value)
    {
        return $this->set(self::S, $value);
    }

    /**
     * Returns value of 's' property
     *
     * @return string
     */
    public function getS()
    {
        return $this->get(self::S);
    }

    /**
     * Sets value of 't' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setT($value)
    {
        return $this->set(self::T, $value);
    }

    /**
     * Returns value of 't' property
     *
     * @return int
     */
    public function getT()
    {
        return $this->get(self::T);
    }

    /**
     * Sets value of 'n' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setN($value)
    {
        return $this->set(self::N, $value);
    }

    /**
     * Returns value of 'n' property
     *
     * @return string
     */
    public function getN()
    {
        return $this->get(self::N);
    }

    /**
     * Sets value of 'transient' property
     *
     * @param bool $value Property value
     *
     * @return null
     */
    public function setTransient($value)
    {
        return $this->set(self::TRANSIENT, $value);
    }

    /**
     * Returns value of 'transient' property
     *
     * @return bool
     */
    public function getTransient()
    {
        return $this->get(self::TRANSIENT);
    }

    /**
     * Appends value to 'roomPeerIds' list
     *
     * @param string $value Value to append
     *
     * @return null
     */
    public function appendRoomPeerIds($value)
    {
        return $this->append(self::ROOMPEERIDS, $value);
    }

    /**
     * Clears 'roomPeerIds' list
     *
     * @return null
     */
    public function clearRoomPeerIds()
    {
        return $this->clear(self::ROOMPEERIDS);
    }

    /**
     * Returns 'roomPeerIds' list
     *
     * @return string[]
     */
    public function getRoomPeerIds()
    {
        return $this->get(self::ROOMPEERIDS);
    }

    /**
     * Returns 'roomPeerIds' iterator
     *
     * @return ArrayIterator
     */
    public function getRoomPeerIdsIterator()
    {
        return new \ArrayIterator($this->get(self::ROOMPEERIDS));
    }

    /**
     * Returns element from 'roomPeerIds' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return string
     */
    public function getRoomPeerIdsAt($offset)
    {
        return $this->get(self::ROOMPEERIDS, $offset);
    }

    /**
     * Returns count of 'roomPeerIds' list
     *
     * @return int
     */
    public function getRoomPeerIdsCount()
    {
        return $this->count(self::ROOMPEERIDS);
    }

    /**
     * Sets value of 'byPeerId' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setByPeerId($value)
    {
        return $this->set(self::BYPEERID, $value);
    }

    /**
     * Returns value of 'byPeerId' property
     *
     * @return string
     */
    public function getByPeerId()
    {
        return $this->get(self::BYPEERID);
    }
}

/**
 * LogsCommand message
 */
class LogsCommand extends \ProtobufMessage
{
    /* Field index constants */
    const CID = 1;
    const L = 2;
    const LIMIT = 3;
    const T = 4;
    const TT = 5;
    const TMID = 6;
    const MID = 7;
    const CHECKSUM = 8;
    const STORED = 9;
    const LOGS = 105;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::CID => array(
            'name' => 'cid',
            'required' => false,
            'type' => 7,
        ),
        self::L => array(
            'name' => 'l',
            'required' => false,
            'type' => 5,
        ),
        self::LIMIT => array(
            'name' => 'limit',
            'required' => false,
            'type' => 5,
        ),
        self::T => array(
            'name' => 't',
            'required' => false,
            'type' => 5,
        ),
        self::TT => array(
            'name' => 'tt',
            'required' => false,
            'type' => 5,
        ),
        self::TMID => array(
            'name' => 'tmid',
            'required' => false,
            'type' => 7,
        ),
        self::MID => array(
            'name' => 'mid',
            'required' => false,
            'type' => 7,
        ),
        self::CHECKSUM => array(
            'name' => 'checksum',
            'required' => false,
            'type' => 7,
        ),
        self::STORED => array(
            'name' => 'stored',
            'required' => false,
            'type' => 8,
        ),
        self::LOGS => array(
            'name' => 'logs',
            'repeated' => true,
            'type' => 'LogItem'
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     *
     * @return null
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::CID] = null;
        $this->values[self::L] = null;
        $this->values[self::LIMIT] = null;
        $this->values[self::T] = null;
        $this->values[self::TT] = null;
        $this->values[self::TMID] = null;
        $this->values[self::MID] = null;
        $this->values[self::CHECKSUM] = null;
        $this->values[self::STORED] = null;
        $this->values[self::LOGS] = array();
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Sets value of 'cid' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setCid($value)
    {
        return $this->set(self::CID, $value);
    }

    /**
     * Returns value of 'cid' property
     *
     * @return string
     */
    public function getCid()
    {
        return $this->get(self::CID);
    }

    /**
     * Sets value of 'l' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setL($value)
    {
        return $this->set(self::L, $value);
    }

    /**
     * Returns value of 'l' property
     *
     * @return int
     */
    public function getL()
    {
        return $this->get(self::L);
    }

    /**
     * Sets value of 'limit' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setLimit($value)
    {
        return $this->set(self::LIMIT, $value);
    }

    /**
     * Returns value of 'limit' property
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->get(self::LIMIT);
    }

    /**
     * Sets value of 't' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setT($value)
    {
        return $this->set(self::T, $value);
    }

    /**
     * Returns value of 't' property
     *
     * @return int
     */
    public function getT()
    {
        return $this->get(self::T);
    }

    /**
     * Sets value of 'tt' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setTt($value)
    {
        return $this->set(self::TT, $value);
    }

    /**
     * Returns value of 'tt' property
     *
     * @return int
     */
    public function getTt()
    {
        return $this->get(self::TT);
    }

    /**
     * Sets value of 'tmid' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setTmid($value)
    {
        return $this->set(self::TMID, $value);
    }

    /**
     * Returns value of 'tmid' property
     *
     * @return string
     */
    public function getTmid()
    {
        return $this->get(self::TMID);
    }

    /**
     * Sets value of 'mid' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setMid($value)
    {
        return $this->set(self::MID, $value);
    }

    /**
     * Returns value of 'mid' property
     *
     * @return string
     */
    public function getMid()
    {
        return $this->get(self::MID);
    }

    /**
     * Sets value of 'checksum' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setChecksum($value)
    {
        return $this->set(self::CHECKSUM, $value);
    }

    /**
     * Returns value of 'checksum' property
     *
     * @return string
     */
    public function getChecksum()
    {
        return $this->get(self::CHECKSUM);
    }

    /**
     * Sets value of 'stored' property
     *
     * @param bool $value Property value
     *
     * @return null
     */
    public function setStored($value)
    {
        return $this->set(self::STORED, $value);
    }

    /**
     * Returns value of 'stored' property
     *
     * @return bool
     */
    public function getStored()
    {
        return $this->get(self::STORED);
    }

    /**
     * Appends value to 'logs' list
     *
     * @param LogItem $value Value to append
     *
     * @return null
     */
    public function appendLogs(LogItem $value)
    {
        return $this->append(self::LOGS, $value);
    }

    /**
     * Clears 'logs' list
     *
     * @return null
     */
    public function clearLogs()
    {
        return $this->clear(self::LOGS);
    }

    /**
     * Returns 'logs' list
     *
     * @return LogItem[]
     */
    public function getLogs()
    {
        return $this->get(self::LOGS);
    }

    /**
     * Returns 'logs' iterator
     *
     * @return ArrayIterator
     */
    public function getLogsIterator()
    {
        return new \ArrayIterator($this->get(self::LOGS));
    }

    /**
     * Returns element from 'logs' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return LogItem
     */
    public function getLogsAt($offset)
    {
        return $this->get(self::LOGS, $offset);
    }

    /**
     * Returns count of 'logs' list
     *
     * @return int
     */
    public function getLogsCount()
    {
        return $this->count(self::LOGS);
    }
}

/**
 * RcpCommand message
 */
class RcpCommand extends \ProtobufMessage
{
    /* Field index constants */
    const ID = 1;
    const CID = 2;
    const T = 3;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::ID => array(
            'name' => 'id',
            'required' => false,
            'type' => 7,
        ),
        self::CID => array(
            'name' => 'cid',
            'required' => false,
            'type' => 7,
        ),
        self::T => array(
            'name' => 't',
            'required' => false,
            'type' => 5,
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     *
     * @return null
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::ID] = null;
        $this->values[self::CID] = null;
        $this->values[self::T] = null;
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Sets value of 'id' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setId($value)
    {
        return $this->set(self::ID, $value);
    }

    /**
     * Returns value of 'id' property
     *
     * @return string
     */
    public function getId()
    {
        return $this->get(self::ID);
    }

    /**
     * Sets value of 'cid' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setCid($value)
    {
        return $this->set(self::CID, $value);
    }

    /**
     * Returns value of 'cid' property
     *
     * @return string
     */
    public function getCid()
    {
        return $this->get(self::CID);
    }

    /**
     * Sets value of 't' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setT($value)
    {
        return $this->set(self::T, $value);
    }

    /**
     * Returns value of 't' property
     *
     * @return int
     */
    public function getT()
    {
        return $this->get(self::T);
    }
}

/**
 * ReadTuple message
 */
class ReadTuple extends \ProtobufMessage
{
    /* Field index constants */
    const CID = 1;
    const TIMESTAMP = 2;
    const MID = 3;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::CID => array(
            'name' => 'cid',
            'required' => true,
            'type' => 7,
        ),
        self::TIMESTAMP => array(
            'name' => 'timestamp',
            'required' => false,
            'type' => 5,
        ),
        self::MID => array(
            'name' => 'mid',
            'required' => false,
            'type' => 7,
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     *
     * @return null
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::CID] = null;
        $this->values[self::TIMESTAMP] = null;
        $this->values[self::MID] = null;
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Sets value of 'cid' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setCid($value)
    {
        return $this->set(self::CID, $value);
    }

    /**
     * Returns value of 'cid' property
     *
     * @return string
     */
    public function getCid()
    {
        return $this->get(self::CID);
    }

    /**
     * Sets value of 'timestamp' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setTimestamp($value)
    {
        return $this->set(self::TIMESTAMP, $value);
    }

    /**
     * Returns value of 'timestamp' property
     *
     * @return int
     */
    public function getTimestamp()
    {
        return $this->get(self::TIMESTAMP);
    }

    /**
     * Sets value of 'mid' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setMid($value)
    {
        return $this->set(self::MID, $value);
    }

    /**
     * Returns value of 'mid' property
     *
     * @return string
     */
    public function getMid()
    {
        return $this->get(self::MID);
    }
}

/**
 * ReadCommand message
 */
class ReadCommand extends \ProtobufMessage
{
    /* Field index constants */
    const CID = 1;
    const CIDS = 2;
    const CONVS = 3;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::CID => array(
            'name' => 'cid',
            'required' => false,
            'type' => 7,
        ),
        self::CIDS => array(
            'name' => 'cids',
            'repeated' => true,
            'type' => 7,
        ),
        self::CONVS => array(
            'name' => 'convs',
            'repeated' => true,
            'type' => 'ReadTuple'
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     *
     * @return null
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::CID] = null;
        $this->values[self::CIDS] = array();
        $this->values[self::CONVS] = array();
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Sets value of 'cid' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setCid($value)
    {
        return $this->set(self::CID, $value);
    }

    /**
     * Returns value of 'cid' property
     *
     * @return string
     */
    public function getCid()
    {
        return $this->get(self::CID);
    }

    /**
     * Appends value to 'cids' list
     *
     * @param string $value Value to append
     *
     * @return null
     */
    public function appendCids($value)
    {
        return $this->append(self::CIDS, $value);
    }

    /**
     * Clears 'cids' list
     *
     * @return null
     */
    public function clearCids()
    {
        return $this->clear(self::CIDS);
    }

    /**
     * Returns 'cids' list
     *
     * @return string[]
     */
    public function getCids()
    {
        return $this->get(self::CIDS);
    }

    /**
     * Returns 'cids' iterator
     *
     * @return ArrayIterator
     */
    public function getCidsIterator()
    {
        return new \ArrayIterator($this->get(self::CIDS));
    }

    /**
     * Returns element from 'cids' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return string
     */
    public function getCidsAt($offset)
    {
        return $this->get(self::CIDS, $offset);
    }

    /**
     * Returns count of 'cids' list
     *
     * @return int
     */
    public function getCidsCount()
    {
        return $this->count(self::CIDS);
    }

    /**
     * Appends value to 'convs' list
     *
     * @param ReadTuple $value Value to append
     *
     * @return null
     */
    public function appendConvs(ReadTuple $value)
    {
        return $this->append(self::CONVS, $value);
    }

    /**
     * Clears 'convs' list
     *
     * @return null
     */
    public function clearConvs()
    {
        return $this->clear(self::CONVS);
    }

    /**
     * Returns 'convs' list
     *
     * @return ReadTuple[]
     */
    public function getConvs()
    {
        return $this->get(self::CONVS);
    }

    /**
     * Returns 'convs' iterator
     *
     * @return ArrayIterator
     */
    public function getConvsIterator()
    {
        return new \ArrayIterator($this->get(self::CONVS));
    }

    /**
     * Returns element from 'convs' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return ReadTuple
     */
    public function getConvsAt($offset)
    {
        return $this->get(self::CONVS, $offset);
    }

    /**
     * Returns count of 'convs' list
     *
     * @return int
     */
    public function getConvsCount()
    {
        return $this->count(self::CONVS);
    }
}

/**
 * PresenceCommand message
 */
class PresenceCommand extends \ProtobufMessage
{
    /* Field index constants */
    const STATUS = 1;
    const SESSIONPEERIDS = 2;
    const CID = 3;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::STATUS => array(
            'name' => 'status',
            'required' => false,
            'type' => 5,
        ),
        self::SESSIONPEERIDS => array(
            'name' => 'sessionPeerIds',
            'repeated' => true,
            'type' => 7,
        ),
        self::CID => array(
            'name' => 'cid',
            'required' => false,
            'type' => 7,
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     *
     * @return null
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::STATUS] = null;
        $this->values[self::SESSIONPEERIDS] = array();
        $this->values[self::CID] = null;
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Sets value of 'status' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setStatus($value)
    {
        return $this->set(self::STATUS, $value);
    }

    /**
     * Returns value of 'status' property
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->get(self::STATUS);
    }

    /**
     * Appends value to 'sessionPeerIds' list
     *
     * @param string $value Value to append
     *
     * @return null
     */
    public function appendSessionPeerIds($value)
    {
        return $this->append(self::SESSIONPEERIDS, $value);
    }

    /**
     * Clears 'sessionPeerIds' list
     *
     * @return null
     */
    public function clearSessionPeerIds()
    {
        return $this->clear(self::SESSIONPEERIDS);
    }

    /**
     * Returns 'sessionPeerIds' list
     *
     * @return string[]
     */
    public function getSessionPeerIds()
    {
        return $this->get(self::SESSIONPEERIDS);
    }

    /**
     * Returns 'sessionPeerIds' iterator
     *
     * @return ArrayIterator
     */
    public function getSessionPeerIdsIterator()
    {
        return new \ArrayIterator($this->get(self::SESSIONPEERIDS));
    }

    /**
     * Returns element from 'sessionPeerIds' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return string
     */
    public function getSessionPeerIdsAt($offset)
    {
        return $this->get(self::SESSIONPEERIDS, $offset);
    }

    /**
     * Returns count of 'sessionPeerIds' list
     *
     * @return int
     */
    public function getSessionPeerIdsCount()
    {
        return $this->count(self::SESSIONPEERIDS);
    }

    /**
     * Sets value of 'cid' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setCid($value)
    {
        return $this->set(self::CID, $value);
    }

    /**
     * Returns value of 'cid' property
     *
     * @return string
     */
    public function getCid()
    {
        return $this->get(self::CID);
    }
}

/**
 * ReportCommand message
 */
class ReportCommand extends \ProtobufMessage
{
    /* Field index constants */
    const INITIATIVE = 1;
    const TYPE = 2;
    const DATA = 3;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::INITIATIVE => array(
            'name' => 'initiative',
            'required' => false,
            'type' => 8,
        ),
        self::TYPE => array(
            'name' => 'type',
            'required' => false,
            'type' => 7,
        ),
        self::DATA => array(
            'name' => 'data',
            'required' => false,
            'type' => 7,
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     *
     * @return null
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::INITIATIVE] = null;
        $this->values[self::TYPE] = null;
        $this->values[self::DATA] = null;
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Sets value of 'initiative' property
     *
     * @param bool $value Property value
     *
     * @return null
     */
    public function setInitiative($value)
    {
        return $this->set(self::INITIATIVE, $value);
    }

    /**
     * Returns value of 'initiative' property
     *
     * @return bool
     */
    public function getInitiative()
    {
        return $this->get(self::INITIATIVE);
    }

    /**
     * Sets value of 'type' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setType($value)
    {
        return $this->set(self::TYPE, $value);
    }

    /**
     * Returns value of 'type' property
     *
     * @return string
     */
    public function getType()
    {
        return $this->get(self::TYPE);
    }

    /**
     * Sets value of 'data' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setData($value)
    {
        return $this->set(self::DATA, $value);
    }

    /**
     * Returns value of 'data' property
     *
     * @return string
     */
    public function getData()
    {
        return $this->get(self::DATA);
    }
}

/**
 * GenericCommand message
 */
class GenericCommand extends \ProtobufMessage
{
    /* Field index constants */
    const CMD = 1;
    const OP = 2;
    const APPID = 3;
    const PEERID = 4;
    const I = 5;
    const INSTALLATIONID = 6;
    const PRIORITY = 7;
    const LOGINMESSAGE = 100;
    const DATAMESSAGE = 101;
    const SESSIONMESSAGE = 102;
    const ERRORMESSAGE = 103;
    const DIRECTMESSAGE = 104;
    const ACKMESSAGE = 105;
    const UNREADMESSAGE = 106;
    const READMESSAGE = 107;
    const RCPMESSAGE = 108;
    const LOGSMESSAGE = 109;
    const CONVMESSAGE = 110;
    const ROOMMESSAGE = 111;
    const PRESENCEMESSAGE = 112;
    const REPORTMESSAGE = 113;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::CMD => array(
            'name' => 'cmd',
            'required' => true,
            'type' => 5,
        ),
        self::OP => array(
            'name' => 'op',
            'required' => false,
            'type' => 5,
        ),
        self::APPID => array(
            'name' => 'appId',
            'required' => false,
            'type' => 7,
        ),
        self::PEERID => array(
            'name' => 'peerId',
            'required' => false,
            'type' => 7,
        ),
        self::I => array(
            'name' => 'i',
            'required' => false,
            'type' => 5,
        ),
        self::INSTALLATIONID => array(
            'name' => 'installationId',
            'required' => false,
            'type' => 7,
        ),
        self::PRIORITY => array(
            'name' => 'priority',
            'required' => false,
            'type' => 5,
        ),
        self::LOGINMESSAGE => array(
            'name' => 'loginMessage',
            'required' => false,
            'type' => 'LoginCommand'
        ),
        self::DATAMESSAGE => array(
            'name' => 'dataMessage',
            'required' => false,
            'type' => 'DataCommand'
        ),
        self::SESSIONMESSAGE => array(
            'name' => 'sessionMessage',
            'required' => false,
            'type' => 'SessionCommand'
        ),
        self::ERRORMESSAGE => array(
            'name' => 'errorMessage',
            'required' => false,
            'type' => 'ErrorCommand'
        ),
        self::DIRECTMESSAGE => array(
            'name' => 'directMessage',
            'required' => false,
            'type' => 'DirectCommand'
        ),
        self::ACKMESSAGE => array(
            'name' => 'ackMessage',
            'required' => false,
            'type' => 'AckCommand'
        ),
        self::UNREADMESSAGE => array(
            'name' => 'unreadMessage',
            'required' => false,
            'type' => 'UnreadCommand'
        ),
        self::READMESSAGE => array(
            'name' => 'readMessage',
            'required' => false,
            'type' => 'ReadCommand'
        ),
        self::RCPMESSAGE => array(
            'name' => 'rcpMessage',
            'required' => false,
            'type' => 'RcpCommand'
        ),
        self::LOGSMESSAGE => array(
            'name' => 'logsMessage',
            'required' => false,
            'type' => 'LogsCommand'
        ),
        self::CONVMESSAGE => array(
            'name' => 'convMessage',
            'required' => false,
            'type' => 'ConvCommand'
        ),
        self::ROOMMESSAGE => array(
            'name' => 'roomMessage',
            'required' => false,
            'type' => 'RoomCommand'
        ),
        self::PRESENCEMESSAGE => array(
            'name' => 'presenceMessage',
            'required' => false,
            'type' => 'PresenceCommand'
        ),
        self::REPORTMESSAGE => array(
            'name' => 'reportMessage',
            'required' => false,
            'type' => 'ReportCommand'
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     *
     * @return null
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::CMD] = null;
        $this->values[self::OP] = null;
        $this->values[self::APPID] = null;
        $this->values[self::PEERID] = null;
        $this->values[self::I] = null;
        $this->values[self::INSTALLATIONID] = null;
        $this->values[self::PRIORITY] = null;
        $this->values[self::LOGINMESSAGE] = null;
        $this->values[self::DATAMESSAGE] = null;
        $this->values[self::SESSIONMESSAGE] = null;
        $this->values[self::ERRORMESSAGE] = null;
        $this->values[self::DIRECTMESSAGE] = null;
        $this->values[self::ACKMESSAGE] = null;
        $this->values[self::UNREADMESSAGE] = null;
        $this->values[self::READMESSAGE] = null;
        $this->values[self::RCPMESSAGE] = null;
        $this->values[self::LOGSMESSAGE] = null;
        $this->values[self::CONVMESSAGE] = null;
        $this->values[self::ROOMMESSAGE] = null;
        $this->values[self::PRESENCEMESSAGE] = null;
        $this->values[self::REPORTMESSAGE] = null;
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Sets value of 'cmd' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setCmd($value)
    {
        return $this->set(self::CMD, $value);
    }

    /**
     * Returns value of 'cmd' property
     *
     * @return int
     */
    public function getCmd()
    {
        return $this->get(self::CMD);
    }

    /**
     * Sets value of 'op' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setOp($value)
    {
        return $this->set(self::OP, $value);
    }

    /**
     * Returns value of 'op' property
     *
     * @return int
     */
    public function getOp()
    {
        return $this->get(self::OP);
    }

    /**
     * Sets value of 'appId' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setAppId($value)
    {
        return $this->set(self::APPID, $value);
    }

    /**
     * Returns value of 'appId' property
     *
     * @return string
     */
    public function getAppId()
    {
        return $this->get(self::APPID);
    }

    /**
     * Sets value of 'peerId' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setPeerId($value)
    {
        return $this->set(self::PEERID, $value);
    }

    /**
     * Returns value of 'peerId' property
     *
     * @return string
     */
    public function getPeerId()
    {
        return $this->get(self::PEERID);
    }

    /**
     * Sets value of 'i' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setI($value)
    {
        return $this->set(self::I, $value);
    }

    /**
     * Returns value of 'i' property
     *
     * @return int
     */
    public function getI()
    {
        return $this->get(self::I);
    }

    /**
     * Sets value of 'installationId' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setInstallationId($value)
    {
        return $this->set(self::INSTALLATIONID, $value);
    }

    /**
     * Returns value of 'installationId' property
     *
     * @return string
     */
    public function getInstallationId()
    {
        return $this->get(self::INSTALLATIONID);
    }

    /**
     * Sets value of 'priority' property
     *
     * @param int $value Property value
     *
     * @return null
     */
    public function setPriority($value)
    {
        return $this->set(self::PRIORITY, $value);
    }

    /**
     * Returns value of 'priority' property
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->get(self::PRIORITY);
    }

    /**
     * Sets value of 'loginMessage' property
     *
     * @param LoginCommand $value Property value
     *
     * @return null
     */
    public function setLoginMessage(LoginCommand $value)
    {
        return $this->set(self::LOGINMESSAGE, $value);
    }

    /**
     * Returns value of 'loginMessage' property
     *
     * @return LoginCommand
     */
    public function getLoginMessage()
    {
        return $this->get(self::LOGINMESSAGE);
    }

    /**
     * Sets value of 'dataMessage' property
     *
     * @param DataCommand $value Property value
     *
     * @return null
     */
    public function setDataMessage(DataCommand $value)
    {
        return $this->set(self::DATAMESSAGE, $value);
    }

    /**
     * Returns value of 'dataMessage' property
     *
     * @return DataCommand
     */
    public function getDataMessage()
    {
        return $this->get(self::DATAMESSAGE);
    }

    /**
     * Sets value of 'sessionMessage' property
     *
     * @param SessionCommand $value Property value
     *
     * @return null
     */
    public function setSessionMessage(SessionCommand $value)
    {
        return $this->set(self::SESSIONMESSAGE, $value);
    }

    /**
     * Returns value of 'sessionMessage' property
     *
     * @return SessionCommand
     */
    public function getSessionMessage()
    {
        return $this->get(self::SESSIONMESSAGE);
    }

    /**
     * Sets value of 'errorMessage' property
     *
     * @param ErrorCommand $value Property value
     *
     * @return null
     */
    public function setErrorMessage(ErrorCommand $value)
    {
        return $this->set(self::ERRORMESSAGE, $value);
    }

    /**
     * Returns value of 'errorMessage' property
     *
     * @return ErrorCommand
     */
    public function getErrorMessage()
    {
        return $this->get(self::ERRORMESSAGE);
    }

    /**
     * Sets value of 'directMessage' property
     *
     * @param DirectCommand $value Property value
     *
     * @return null
     */
    public function setDirectMessage(DirectCommand $value)
    {
        return $this->set(self::DIRECTMESSAGE, $value);
    }

    /**
     * Returns value of 'directMessage' property
     *
     * @return DirectCommand
     */
    public function getDirectMessage()
    {
        return $this->get(self::DIRECTMESSAGE);
    }

    /**
     * Sets value of 'ackMessage' property
     *
     * @param AckCommand $value Property value
     *
     * @return null
     */
    public function setAckMessage(AckCommand $value)
    {
        return $this->set(self::ACKMESSAGE, $value);
    }

    /**
     * Returns value of 'ackMessage' property
     *
     * @return AckCommand
     */
    public function getAckMessage()
    {
        return $this->get(self::ACKMESSAGE);
    }

    /**
     * Sets value of 'unreadMessage' property
     *
     * @param UnreadCommand $value Property value
     *
     * @return null
     */
    public function setUnreadMessage(UnreadCommand $value)
    {
        return $this->set(self::UNREADMESSAGE, $value);
    }

    /**
     * Returns value of 'unreadMessage' property
     *
     * @return UnreadCommand
     */
    public function getUnreadMessage()
    {
        return $this->get(self::UNREADMESSAGE);
    }

    /**
     * Sets value of 'readMessage' property
     *
     * @param ReadCommand $value Property value
     *
     * @return null
     */
    public function setReadMessage(ReadCommand $value)
    {
        return $this->set(self::READMESSAGE, $value);
    }

    /**
     * Returns value of 'readMessage' property
     *
     * @return ReadCommand
     */
    public function getReadMessage()
    {
        return $this->get(self::READMESSAGE);
    }

    /**
     * Sets value of 'rcpMessage' property
     *
     * @param RcpCommand $value Property value
     *
     * @return null
     */
    public function setRcpMessage(RcpCommand $value)
    {
        return $this->set(self::RCPMESSAGE, $value);
    }

    /**
     * Returns value of 'rcpMessage' property
     *
     * @return RcpCommand
     */
    public function getRcpMessage()
    {
        return $this->get(self::RCPMESSAGE);
    }

    /**
     * Sets value of 'logsMessage' property
     *
     * @param LogsCommand $value Property value
     *
     * @return null
     */
    public function setLogsMessage(LogsCommand $value)
    {
        return $this->set(self::LOGSMESSAGE, $value);
    }

    /**
     * Returns value of 'logsMessage' property
     *
     * @return LogsCommand
     */
    public function getLogsMessage()
    {
        return $this->get(self::LOGSMESSAGE);
    }

    /**
     * Sets value of 'convMessage' property
     *
     * @param ConvCommand $value Property value
     *
     * @return null
     */
    public function setConvMessage(ConvCommand $value)
    {
        return $this->set(self::CONVMESSAGE, $value);
    }

    /**
     * Returns value of 'convMessage' property
     *
     * @return ConvCommand
     */
    public function getConvMessage()
    {
        return $this->get(self::CONVMESSAGE);
    }

    /**
     * Sets value of 'roomMessage' property
     *
     * @param RoomCommand $value Property value
     *
     * @return null
     */
    public function setRoomMessage(RoomCommand $value)
    {
        return $this->set(self::ROOMMESSAGE, $value);
    }

    /**
     * Returns value of 'roomMessage' property
     *
     * @return RoomCommand
     */
    public function getRoomMessage()
    {
        return $this->get(self::ROOMMESSAGE);
    }

    /**
     * Sets value of 'presenceMessage' property
     *
     * @param PresenceCommand $value Property value
     *
     * @return null
     */
    public function setPresenceMessage(PresenceCommand $value)
    {
        return $this->set(self::PRESENCEMESSAGE, $value);
    }

    /**
     * Returns value of 'presenceMessage' property
     *
     * @return PresenceCommand
     */
    public function getPresenceMessage()
    {
        return $this->get(self::PRESENCEMESSAGE);
    }

    /**
     * Sets value of 'reportMessage' property
     *
     * @param ReportCommand $value Property value
     *
     * @return null
     */
    public function setReportMessage(ReportCommand $value)
    {
        return $this->set(self::REPORTMESSAGE, $value);
    }

    /**
     * Returns value of 'reportMessage' property
     *
     * @return ReportCommand
     */
    public function getReportMessage()
    {
        return $this->get(self::REPORTMESSAGE);
    }
}
