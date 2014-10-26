<?php

use yii\db\Schema;

class m140506_181144_user_tooltip extends \yii\db\Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user_tooltip}}', [
            'user_id' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'source_message_id' => Schema::TYPE_INTEGER . '(11) NOT NULL',
        ], $tableOptions);

        $this->addPrimaryKey('PK', '{{%user_tooltip}}', ['user_id' ,'source_message_id']);
    }

    public function down()
    {
        $this->dropTable('{{%user_tooltip}}');
    }
}
