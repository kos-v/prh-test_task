<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Handles the creation of table `{{%apple}}`.
 */
class m231015_134510_create_apple_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%apple}}', [
            'id' => $this->primaryKey(),
            'color' => $this->string(7)->notNull(),
            'germination_datetime' => $this->integer()->notNull(),
            'fell_datetime' => $this->integer(),
            'integrity' => $this->integer()->notNull(),
            'state' => $this->string()->notNull(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%apple}}');
    }
}
