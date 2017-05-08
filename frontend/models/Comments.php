<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "Comments".
 *
 * @property integer $idComment
 * @property integer $idUser
 * @property string $comment
 * @property string $Created
 * @property string $Archived
 */
class Comments extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Comments';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['idUser', 'comment', 'Created', 'Archived'], 'required'],
            [['idUser'], 'integer'],
            [['Created', 'Archived'], 'safe'],
            [['comment'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'idComment' => 'Id Comment',
            'idUser' => 'Id User',
            'comment' => 'Comment',
            'Created' => 'Created',
            'Archived' => 'Archived',
        ];
    }
}
