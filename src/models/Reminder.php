<?php

namespace hipanel\models;

use hipanel\base\Model;
use hipanel\base\ModelTrait;
use Yii;

class Reminder extends Model
{
    use ModelTrait;

    public static $i18nDictionary = 'hipanel/reminder';

    const REMINDER_TYPE_SITE = 'site';
    const REMINDER_TYPE_MAIL = 'mail';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'object_id', 'client_id', 'state_id', 'type_id'], 'integer'],
            [['periodicity', 'from_time', 'till_time', 'next_time'], 'string'],
            [['to_site'], 'boolean'],

            // Create
            [['object_id', 'type', 'periodicity', 'from_time', 'message'], 'required', 'on' => 'create'],

            // Update
            [['id'], 'required', 'on' => 'update'],
            [['object_id', 'state_id', 'type_id'], 'integer', 'on' => 'update'],
            [['from_time', 'next_time', 'till_time'], 'date', 'on' => 'update'],

            // Delete
            [['id'], 'required', 'on' => 'delete']
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
}
