<?php
namespace frontend\modules\client\models;

use Yii;
use frontend\models\Ref;
use yii\base\NotSupportedException;

class Client extends \frontend\components\hiresource\ActiveRecord
{
    /**
     * @return array the list of attributes for this record
     */
    public function attributes()
    {
        return [
            // search
            'id',
            'ids',
            'client',
            'clients',
            'seller',
            'sellers',
            'seller_id',
            'seller_ids',
            'type',
            'types',
            'state',
            'states',
            'hide_blocked',
            'show_deleted',
            'show',
            'tariff_id',
            'balance',
            'credit',
            'tariff_name',
            'create_time',
            'direct_only',
            'with_domains_count',
            'with_servers_count',
            'with_contact',
            'manager_only',
            'view',
            'language',
            'priority',

            // getlist
            'client_like',
            'direct_only',
            'clients',

            // create/update
            'password',
            'email',
            'referer',
            'language',
            'confirm_url',
            // with_contact
            'login',
            'name',
            'first_name',
            'last_name',
            // count
            'count',
            'contact',
            //
            'comment',
        ];
    }

    public function rules()
    {
        return [
            //[[ 'name', 'type', ], 'required'],
            // [[ 'name'], 'integer'],
            //[[ 'type', ],'safe'],
            //[[ 'id', 'credit', ], 'integer', 'required', 'on' => 'setcredit' ],
            [[ 'id', 'type', 'comment', ], 'safe', 'on' => 'setblock' ],
            [[ 'id', 'language', ], 'safe', 'on' => 'setlanguage' ],
        ];
    }

    public function rest()
    {
        return \yii\helpers\ArrayHelper::merge(parent::rest(),['resource'=>'article']);
    }

}
