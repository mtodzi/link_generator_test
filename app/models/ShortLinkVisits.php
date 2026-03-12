<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "short_link_visits".
 *
 * @property int $id
 * @property int $short_link_id
 * @property string $ip_address
 * @property int $visits
 * @property string $created_at
 * @property string $updated_at
 *
 * @property ShortLinks $shortLink
 */
class ShortLinkVisits extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%short_link_visits}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['short_link_id', 'ip_address'], 'required'],
            [['short_link_id', 'visits'], 'integer'],
            [['visits'], 'default', 'value' => 1],
            [['created_at', 'updated_at'], 'safe'],
            [['ip_address'], 'string', 'max' => 45],
            [['short_link_id', 'ip_address'], 'unique', 'targetAttribute' => ['short_link_id', 'ip_address']],
            [['short_link_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShortLinks::class, 'targetAttribute' => ['short_link_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'short_link_id' => 'Short Link ID',
            'ip_address' => 'Ip Address',
            'visits' => 'Visits',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[ShortLink]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShortLink()
    {
        return $this->hasOne(ShortLinks::class, ['id' => 'short_link_id']);
    }
}