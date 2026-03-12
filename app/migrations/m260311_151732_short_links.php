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

        $this->createTable('{{%short_link_visits}}', [
            'id' => $this->primaryKey(),
            'short_link_id' => $this->integer()->notNull(),
            'ip_address' => $this->string(45)->notNull(),
            'visits' => $this->integer()->notNull()->defaultValue(1),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->addForeignKey(
            '{{%fk-short_link_visits-short_link_id}}',
            '{{%short_link_visits}}',
            'short_link_id',
            '{{%short_links}}',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            '{{%idx-short_link_visits-link_id_ip_unique}}',
            '{{%short_link_visits}}',
            ['short_link_id', 'ip_address'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            '{{%fk-short_link_visits-short_link_id}}',
            '{{%short_link_visits}}'
        );

        $this->dropIndex('{{%idx-short_link_visits-link_id_ip_unique}}', '{{%short_link_visits}}');

        $this->dropTable('{{%short_link_visits}}');
        $this->dropTable('{{%short_links}}');
    }

}
