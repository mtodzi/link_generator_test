<?php

use yii\db\Migration;

class m260311_151732_short_links extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%short_links}}', [
            'id' => $this->primaryKey(),
            'original_url' => $this->string(2048)->notNull(),
            // Используем COLLATE utf8mb4_bin для чувствительности к регистру (aBc != abc)
            'short_code' => $this->string(20)->notNull()->append('COLLATE utf8mb4_bin'),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'visits' => $this->integer()->notNull()->defaultValue(0),
        ], $tableOptions);

        // Уникальный индекс для мгновенного поиска по короткому коду
        $this->createIndex(
            '{{%idx-short_links-short_code_unique}}',
            '{{%short_links}}',
            'short_code',
            true
        );

        // Префиксный индекс для оригинальной ссылки для ускорения поиска (оптимизация для MySQL)
        if ($this->db->driverName === 'mysql') {
            $this->execute("CREATE INDEX `idx-short_links-original_url` ON {{%short_links}} (`original_url`(255))");
        } else {
            // Для других СУБД создаем обычный индекс
            $this->createIndex(
                '{{%idx-short_links-original_url}}',
                '{{%short_links}}',
                'original_url'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
       $this->dropTable('{{%short_links}}');
    }

}
