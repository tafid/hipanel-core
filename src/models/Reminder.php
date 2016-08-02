<?php

namespace hipanel\models;

use DateTime;
use DateTimeZone;
use hipanel\base\Model;
use hipanel\base\ModelTrait;
use Yii;

class Reminder extends Model
{
    use ModelTrait;

    public static $i18nDictionary = 'hipanel/reminder';

    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_DELETE = 'delete';

    const TYPE_SITE = 'site';
    const TYPE_MAIL = 'mail';

    public $offset;
    public $reminderChange;

    public function init()
    {
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, 'changeNextTime']);
        $this->on(self::EVENT_BEFORE_INSERT, [$this, 'insertWithClientOffset']);
    }

    public static function reminderNextTimeOptions()
    {
        return [
            '+15 minutes' => Yii::t('hipanel/reminder', '15m'),
            '+30 minutes' => Yii::t('hipanel/reminder', '30m'),
            '+1 hour' => Yii::t('hipanel/reminder', '1h'),
            '+12 hour' => Yii::t('hipanel/reminder', '12h'),
            '+1 day' => Yii::t('hipanel/reminder', '1d'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'object_id', 'client_id', 'state_id', 'type_id'], 'integer'],
            [['class_name', 'periodicity', 'from_time', 'till_time', 'next_time'], 'string'],
            [['to_site'], 'boolean'],

            // Create
            [['object_id', 'type', 'periodicity', 'from_time', 'message', 'offset'], 'required', 'on' => self::SCENARIO_CREATE],

            // Update
            [['id'], 'required', 'on' => 'update'],
            [['object_id', 'state_id', 'type_id'], 'integer', 'on' => self::SCENARIO_UPDATE],
            [['from_time', 'next_time', 'till_time', 'reminderChange', 'offset'], 'string', 'on' => self::SCENARIO_UPDATE],

            // Delete
            [['id'], 'required', 'on' => self::SCENARIO_DELETE]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return $this->mergeAttributeLabels([
            'periodicity' => Yii::t('hipanel/reminder', 'Periodicity'),
            'from_time' => Yii::t('hipanel/reminder', 'When the recall?'),
            'next_time' => Yii::t('hipanel/reminder', 'Next remind'),
            'till_time' => Yii::t('hipanel/reminder', 'Remind till'),
            'message' => Yii::t('hipanel/reminder', 'Message'),
        ]);
    }

    public function getObjectName()
    {
        $result = '';
        if ($this->class_name) {
            switch ($this->class_name) {
                case 'thread':
                    $result = 'ticket';
                    break;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     * @return ReminderQuery
     */
    public static function find($options = [])
    {
        return new ReminderQuery(get_called_class(), [
            'options' => $options,
        ]);
    }

    /**
     * @return bool
     */
    public function changeNextTime()
    {
        if ($this->scenario == self::SCENARIO_UPDATE) {
            $this->next_time = (new DateTime($this->next_time))->modify($this->reminderChange)->format('Y-m-d H:i:s');
        }
    }

    public function insertWithClientOffset()
    {
        if ($this->scenario == self::SCENARIO_CREATE) {
            $this->offset = strpos($this->offset, '-') !== false ? '+' . $this->offset : '-' . $this->offset;
            $this->from_time = (new DateTime($this->from_time))->modify($this->offset . ' minutes')->format('Y-m-d H:i:s');
        }
    }

    public function calculateClientNextTime($offset)
    {
        $offset = strpos($offset, '-') !== false ? $offset : '+' . $offset;
        $next_time = (new DateTime($this->next_time))->modify($offset . ' minutes');
        return Yii::$app->formatter->asDatetime($next_time, 'short');
    }
}